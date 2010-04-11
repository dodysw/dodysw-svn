#!/usr/bin/python
"""
What's new DC Bot
Copyright 2006, Dody Suria Wijaya <dodysw@gmail.com>

Goal:
To get list of new shares that are available in a hub. Output method:
* search result =>
    pro: reply can be faked to come from certain user, thus user can download immediately (or queue them if user not online)
- reply via PM =>
    pro: command to view new shares can be parameterised, e.g. new shares between 2 to 10 of october.
    con: interaction not simple, e.g. if user want to download the new shares, they have to copy-paste TTH to search form.
- file list =>
    pro: good overall view of all new items.
    con: users would download from bot, instead of directly from the sharers. But user can at least search the TTH.
Description:

This bot is composed of three workers.
1. TTH gatherer (run every 2 hours)
    - download all user's file list to get big list of share files. Shares without TTH is skipped
    - afterward, add job to #2 worker
2. List -> Database inserter (run every 1 minute)
    - store the list into database: [TTH(key) + share_path + file_size + last_nick + first_found_date + last_found_date]
    - first_found_date = datetime of file list download
    - if TTH already exist, overwrite last_nick with file list's nick, share_path with last nick's share_path, and update the last_found_date
3. Report generator:
    - display TTH on requested time range
    - time range is: last 1 hour, last 12 hours, last day, last 2 days, last week.
    - can be filtered by: extension, DC search extension type, file size
"""
import re, time, random, threading, dclib, urllib2, os, urllib
import MySQLdb as dbm

__version__ = '1.0.0'
__description__ = ''

DEFAULT_DC_HOST = 'darkmatrix.ath.cx'
DEFAULT_DC_PORT = 411
DEFAULT_NICK = '_whatsnew_'
DEFAULT_PASSWORD = '_whatsnew_'
DEFAULT_NICK_DESCRIPTION = 'PM this bot to see guide on viewing new stuffs available in DC.'

DCLIST_TO_DB_WORKER_PERIOD = 10 # in seconds

MYSQL_CONNECT_PARAM = dict(host='localhost', user='whatsnew_dcbot', passwd='', db='whatsnew_dcbot')
DEFAULT_QUERY_LIMIT = 20
CAP_QUERY_LIMIT = 100
INTERESTING_FILE_SIZE = 60*1024*1024

class WhatsNewBot(dclib.DCActionBot):
    """
    A bot which observe every nick's share files, and do comparation to get list of new stuff.
    """

    HELP_HEADER = """
**********************************
WHATSNEW BOT
**********************************
- Specify number of rows you want to retrieve.
    - E.g.: +video 10 => will retrieve 10 newest video files (capped at %(CAP_QUERY_LIMIT)s), otherwise it defaults to %(DEFAULT_QUERY_LIMIT)s
- Use DC++ search feature to access the same information. How: search for "showmenew" (without double quote) or begins with "new:".
    - E.g. "showmenew". Try using keyword search: "showmenew programming". Combine it with min/max size and file type combo boxes on DC++ Search window.
    - E.g. "new:programming" (shortcut for "showmenew programming")
    - E.g: You can type directly on chat bar. Try this: /search showmenew
    - The file's age is embedded on the path, and also uses the free slot column (in minute),
    - Click slot column to sort search result by age.
    - It currently fixed to cap the search result to %(CAP_QUERY_LIMIT)s files.
- Only shares with TTF are catalogued
- This bot's file list represents all nicks yesterday's new shares
Commands:
""" % dict(DEFAULT_QUERY_LIMIT=DEFAULT_QUERY_LIMIT, CAP_QUERY_LIMIT=CAP_QUERY_LIMIT)

    def init(self):
        self.share_size = 250*1024*1024
        self.list_path = 'files.xml.bz2'
        self.share_fs_path = '.'
        self.dclist_to_db_queue = []
        self.gather_list_queue = []
        self.dc_client_class = DCClientGetListNotify
        self.previous_nick_share_size = {}
        self.previous_nick_list_size = {}
        self.dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
        self.count_total_insert = 0
        self.startup_time = time.time()
        self.nicklist_optin = dclib.SimplePickledObject('whatsnew_dcbot')
        self.nicklist_dontpm = dclib.SimplePickledObject('whatsnew_dcbot_dontpm')

        self.pm_job = []


    def insert_xml_to_db(self, xml_path, found_time, nick):
        dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
        cur = dbcon.cursor()
        count_insert = count_update = count = 0
        first_found_time = last_found_time = found_time

        # localised access
        basename = os.path.basename
        execute = cur.execute
        search_type_id_ext = dclib.DCBot.SEARCH_TYPE_ID_EXT

        for name, size, tth in dclib.get_xml_list(xml_path):
            count += 1
            filename = basename(name)

            try:
                extension = filename[filename.rindex('.')+1:].lower()
                dc_file_type = search_type_id_ext.get(extension, 1)
            except ValueError:
                extension = ''
                dc_file_type = 1

            try:
                execute("insert into shares (tth, share_path, filename, extension, size, last_nick, first_found_time, last_found_time, dc_file_type) values (%s,%s,%s,%s,%s,%s,%s,%s,%s)",
                    (tth.encode('UTF-8'), name.encode('UTF-8'), filename.encode('UTF-8'), extension.encode('UTF-8'), size, nick, first_found_time, last_found_time, dc_file_type))
                count_insert += 1
                #~ if self.is_interesting_file(name, size, tth, filename, extension, dc_file_type, nick):
                    #~ self.notify_optin_people(name, size, tth, filename, extension, dc_file_type, nick)
            except dbm.IntegrityError:
                # this is very cpu intensive, disable if not needded
                if 0:
                    #~ self.log("Already exist row yet for tth %s, updating" % tth)
                    execute("update shares set share_path=%s, filename=%s, extension=%s, size=%s, last_nick=%s, last_found_time=%s where tth=%s",
                        (name.encode('UTF-8'), filename.encode('UTF-8'), extension.encode('UTF-8'), size, nick.encode('UTF-8'), last_found_time, tth.encode('UTF-8')))
                    count_update += 1
        return count_insert, count_update, count

    def fast_insert_xml_to_db(self, xml_path, found_time, nick):
        MAX_PACKET = 900*1024
        dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
        cur = dbcon.cursor()
        count_insert = count_update = count = datalen = 0
        values = []
        sql_prefix = "insert ignore into shares (tth, share_path, filename, extension, size, last_nick, first_found_time, last_found_time, dc_file_type) values "
        first_found_time = last_found_time = found_time

        # localised access
        basename = os.path.basename
        execute = cur.execute
        escape_string = dbm.escape_string
        search_type_id_ext = dclib.DCBot.SEARCH_TYPE_ID_EXT
        join_comma = ','.join

        for name, size, tth in dclib.get_xml_list(xml_path):  # this loop should be optimised
            if datalen > MAX_PACKET:
                datalen = 0
                count_insert += execute(sql_prefix + join_comma(values))
                values = []
            filename = basename(name)
            # normally file has dot, so try/except is more appropriate+faster than if '.' in filename...
            try:
                extension = filename[filename.rindex('.')+1:].lower()
                dc_file_type = search_type_id_ext.get(extension, 1)
            except ValueError:
                extension = ''
                dc_file_type = 1
            value = "('%s','%s','%s','%s','%s','%s','%s','%s','%s')" % (escape_string(tth.encode('UTF-8')), escape_string(name.encode('UTF-8')), escape_string(filename.encode('UTF-8')), escape_string(extension.encode('UTF-8')), size, escape_string(nick), first_found_time, last_found_time, dc_file_type)

            values.append(value)
            datalen += len(value)
            count += 1

        if values:  # remaining
            execute(sql_prefix + join_comma(values))
            count_insert += cur.rowcount
        return count_insert, count_update, count

    def super_fast_insert_xml_to_db(self, xml_path, found_time, nick):
        dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
        cur = dbcon.cursor()
        count_insert = count_update = count = datalen = new_tth_count = 0
        first_found_time = last_found_time = found_time

        # localised access
        basename = os.path.basename
        search_type_id_ext = dclib.DCBot.SEARCH_TYPE_ID_EXT

        tthdict = dclib.get_xml_dict(xml_path)
        sql, args = "", None
        try:
            # create heap table
            sql = "create temporary table tth_list (tth char(39) primary key) engine=MEMORY"
            cur.execute(sql)

            # populate the heap
            sql, args = "insert ignore into tth_list (tth) values (%s)", tthdict.keys()
            self.log("tth len=%s" % len(tthdict))
            # problem with MySQLdb module not being able to insert > 5000 rows to this table!!! whilst it's okay using normal mysql client
            while args:
                cur.executemany(sql, args[:5000])
                del args[:5000]

            # join with shares, and get list of new tth
            sql = "select tth_list.tth from tth_list left join shares on tth_list.tth = shares.tth where shares.tth is null"
            new_tth_count = cur.execute(sql)
            if new_tth_count > 0:
                args = []
                total_size = 0
                total_inserted = 0
                list_of_files = []
                tth_anti_dupes = {} # additional assertion to make that tth is unique
                for (tth,) in cur:
                    if tth in tth_anti_dupes:
                        continue
                    tth_anti_dupes[tth] = None

                    name = tthdict[tth][0]

                    if self.file_is_ignored(name):
                        continue

                    size = tthdict[tth][1]
                    total_size += size
                    total_inserted += 1
                    list_of_files.append([size, name])


                    filename = basename(name)
                    # normally file has dot, so try/except is more appropriate+faster than if '.' in filename...
                    try:
                        extension = filename[filename.rindex('.')+1:].lower()
                        dc_file_type = search_type_id_ext.get(extension, 1)
                    except ValueError:
                        extension = ''
                        dc_file_type = 1
                    args.append( (tth.encode('UTF-8'), name.encode('UTF-8'), filename.encode('UTF-8'), extension.encode('UTF-8'), size, nick, first_found_time, last_found_time, dc_file_type) )

                    self.interesting_file_gatherer(name, size, tth, filename, extension, dc_file_type, nick)

                #~ cur.execute('LOCK TABLES shares WRITE') # optimization

                sql = "insert into shares (tth, share_path, filename, extension, size, last_nick, first_found_time, last_found_time, dc_file_type) values (%s,%s,%s,%s,%s,%s,%s,%s,%s)"
                while args:
                    cur.executemany(sql, args[:500])
                    del args[:500]
                #~ cur.execute('UNLOCK TABLES')
                if total_inserted > 0:
                    self.thank_nick_for_sharing(nick, total_inserted, total_size, list_of_files)
                return new_tth_count, 0, len(tthdict)
            else:
                return 0, 0, len(tthdict)
        except dbm.OperationalError, e:
            self.log("WARNING: Database operational error [%s] Sql=%s" % (e, sql))
            #~ print "sql = %s\nargs = %s" % (repr(sql), repr(args))
            #~ sys.exit()
            return new_tth_count, 0, len(tthdict)
        except dbm.IntegrityError, e:
            self.log("WARNING: Database itegrity error [%s] Sql=%s" % (e, sql))
            #~ print "sql = %s\nargs = %s" % (repr(sql), repr(args))
            #~ sys.exit()
            return new_tth_count, 0, len(tthdict)

    def parse_dclist_to_db(self, file_path, nick):
        if file_path.endswith('files.xml.bz2'):
            try:
                xml_path = dclib.uncompress_bz2(file_path)
            except IOError, e:
                self.log("Can't extract [%s]:%s" % (file_path, e))
                try: os.unlink(file_path)
                except: pass
                return
            except EOFError, e:
                self.log("Can't extract [%s]:%s" % (file_path, e))
                try: os.unlink(file_path)
                except: pass
                return

            try:
                start_time = time.time()
                #~ count_insert, count_update, count = self.fast_insert_xml_to_db(xml_path, os.path.getmtime(file_path), nick)
                count_insert, count_update, count = self.super_fast_insert_xml_to_db(xml_path, os.path.getmtime(file_path), nick)
                dtime = time.time() - start_time
                self.count_total_insert += count_insert
                insert_rate = self.count_total_insert/((time.time()-self.startup_time)/float(60))
                self.log("%d insert, %d updates out of %d TTH from %s in %0.2f sec - since start: total %s insert (%0.2f insert/minutes)" % (count_insert, count_update, count, nick, dtime, self.count_total_insert, insert_rate))
            finally:
                try:
                    os.unlink(xml_path)
                except OSError:
                    self.log("Unable to deletel [%s]" % xml_path)
        else:
            #~ self.log("File [%s] doesn't end with files.xml.bz2, skipping" % file_path)
            # we're only interested in share list with TTH
            pass

    def handle_myinfo_parsed(self, nick, description, share_size, flag, connection_type, email):
        """Note this handle gets called BEFORE Hello, but the order is not specified in standard.
        """
        # query db of this nick's last share size

        if nick == self.nick:   # ignore own nick
            return

        #TEMPORARY
        #~ if nick != 'Io++':
            #~ return

        if (nick in self.previous_nick_share_size and self.previous_nick_share_size[nick] != share_size) or nick not in self.previous_nick_share_size:
            if nick not in self.previous_nick_share_size:
                self.log("Nick %s is new. I will try downloading his list." % nick)
                pass
            else:
                self.log("Nick %s has change share size (%s->%s). I will try downloading his list." % (nick, self.previous_nick_share_size[nick], share_size))
                pass
            self.previous_nick_share_size[nick] = share_size
            filename = os.path.join('FileLists', dclib.get_filesafe_name('%s.files.xml.bz2' % nick))
            filename2 = os.path.join('FileLists', dclib.get_filesafe_name('%s.MyList.DcLst' % nick))
            filename = os.path.exists(filename) and filename or os.path.exists(filename2) and filename2 or None
            if filename:
                #~ self.log("Nick %s list already downloaded. I will only download again if the list size changes." % nick)
                self.previous_nick_list_size[nick] = os.path.getsize(filename) # yes it's LIST_SIZE. we need to save the list file size because the downloading will overwrite the file.
            super(WhatsNewBot, self).handle_myinfo_parsed(nick, description, share_size, flag, connection_type, email)
            self.send_connecttome(nick)
        else:
            super(WhatsNewBot, self).handle_myinfo_parsed(nick, description, share_size, flag, connection_type, email)

    def handle_nicklist(self, nicks):
        super(WhatsNewBot, self).handle_nicklist(nicks)

        # if hub support nogetinfo protocol, hub does not need to be sent GetInfo to get all nicks MyINFO
        if 'NoGetINFO' not in self.remote_support:
            self.send_batch_begin()
            for nick in self.nicklist:
                if nick == self.nick:
                    continue
                self.send_getinfo(nick)
            self.send_batch_end()


    def handle_quit(self, nick):
        super(WhatsNewBot, self).handle_quit(nick)
        try:
            del self.nicklist[nick]
        except KeyError:
            pass

    def handle_hello(self, nick):
        super(WhatsNewBot, self).handle_hello(nick)
        #~ self.nicklist[nick] = None

    def on_loggedin(self):

        def dclist_to_db_worker():
            while self.running:
                if not self.dclist_to_db_queue:
                    time.sleep(DCLIST_TO_DB_WORKER_PERIOD)
                    continue
                while self.dclist_to_db_queue:
                    self.log("Dclist/dbworker: Got %d job. Parsing..." % len(self.dclist_to_db_queue))
                    o = self.dclist_to_db_queue[0]
                    del self.dclist_to_db_queue[0]
                    self.parse_dclist_to_db(*o)
                self.log("Dclist/dbworker: No more job, waiting...")
                self.notify_optin_people()

            sys.exit("DCLIST TO DB WORKER EXITED!!! contact developer!")

        # spawn dclist_to_db_worker, to parse list into database
        threading.Thread(target=dclist_to_db_worker).start()

    def get_magnet(self, tth, complete=False, size=0, filename="unknown"):
        if complete:
            return "magnet:?xt=urn:tree:tiger:%s&xl=%d&dn=%s" % (tth, size, filename.replace(' ', '%20'))
        else:
            return "magnet:?xt=urn:tree:tiger:%s" % tth

    def query_by_file_type(self, t=None, limit=DEFAULT_QUERY_LIMIT):
        if limit > CAP_QUERY_LIMIT:
            limit = CAP_QUERY_LIMIT  #cap limit to avoid get kicked by hub
        if t is not None:
            where_file_type = "where dc_file_type=%s"
        else:
            where_file_type = ""
        sql = "select tth, filename, size, last_nick, share_path, first_found_time from shares %s order by first_found_time desc " % where_file_type + "limit %s" % limit
        args = t
        return self.query_by_sql(sql, args=args)

    def parse_param_limit(self, data):
        #make sure data is a number
        if data != '':
            try:
                return int(data)
            except ValueError:
                return False

    def simple_search(self, nick, data, file_type_id):
        start_time = time.time()
        if len(self.dclist_to_db_queue) > 3:
            self.send_pm(nick, "I'm busy doing %s analysis jobs at the moment. Please wait..." % len(self.dclist_to_db_queue))
        else:
            self.send_pm(nick, 'Please wait...')
        limit = self.parse_param_limit(data) or DEFAULT_QUERY_LIMIT
        self.send_pm(nick, '\n' + self.query_by_file_type(file_type_id, limit))
        self.log("Simple search done in %0.2f sec" % (time.time() - start_time))

    def custom_search(self, nick, data, where, args):
        start_time = time.time()
        if len(self.dclist_to_db_queue) > 3:
            self.send_pm(nick, "I'm busy doing %s analysis jobs at the moment. Please wait..." % len(self.dclist_to_db_queue))
        else:
            self.send_pm(nick, 'Please wait...')
        limit = self.parse_param_limit(data) or DEFAULT_QUERY_LIMIT
        self.send_pm(nick, '\n' + self.query_by_where(where, args, limit))
        self.log("Custom search done in %0.2f sec" % (time.time() - start_time))

    def action_any(self, nick, data=''):
        """list any newest files"""
        self.simple_search(nick, data, None)

    def action_audio(self, nick, data=''):
        """list newest audio files"""
        self.simple_search(nick, data, dclib.DCBot.SEARCH_TYPE_AUDIO)

    def action_compressed(self, nick, data=''):
        """list newest compressed files"""
        self.simple_search(nick, data, dclib.DCBot.SEARCH_TYPE_COMPRESSED)

    def action_document(self, nick, data=''):
        """list newest document files"""
        self.simple_search(nick, data, dclib.DCBot.SEARCH_TYPE_DOCUMENT)

    def action_executable(self, nick, data=''):
        """list newest executeable files"""
        self.simple_search(nick, data, dclib.DCBot.SEARCH_TYPE_EXECUTEABLE)

    def action_picture(self, nick, data=''):
        """list newest picture files"""
        self.simple_search(nick, data, dclib.DCBot.SEARCH_TYPE_PICTURE)

    def action_video(self, nick, data=''):
        """list newest video files"""
        self.simple_search(nick, data, dclib.DCBot.SEARCH_TYPE_VIDEO)

    def action_tv(self, nick, data=''):
        """list newest tv video files"""
        self.custom_search(nick, data, "size > %s and size <= %s and dc_file_type=%s", [300*1024*1024, 400*1024*1024, dclib.DCBot.SEARCH_TYPE_VIDEO])

    def query_by_share_path(self, q="", limit=DEFAULT_QUERY_LIMIT):
        if limit > CAP_QUERY_LIMIT:
            limit = CAP_QUERY_LIMIT  #cap limit to avoid get kicked by hub
        sql = "select tth, filename, size, last_nick, share_path, first_found_time from shares where match(share_path) against(%s IN BOOLEAN MODE) order by first_found_time desc " + "limit %s" % limit
        args = q
        self.log("Sql:%s | Args:%s" % (sql,args))
        lines = []
        dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
        cur = dbcon.cursor()
        try:
            cur.execute(sql, args)
            if cur.rowcount == 0:
                return False
            for tth, filename, size, last_nick, share_path, first_found_time in cur:
                dtime = time.time() - first_found_time
                lines.append("%s - %0.2fMB - %s - by %s => %s" % (nice_time(dtime), size/(1024.0*1024.0), share_path, last_nick, self.get_magnet(tth)))
            return '\n'.join(lines)
        except dbm.OperationalError, e:
            self.log("WARNING: Database operational error [%s] Sql=%s, Args=%s" % (e, sql, args))
            return "Database operational problem. Please try again."

    def action_search(self, nick, data=''):
        """search files by keywords in its full path. E.g: +search avi   or   +search eureka"""
        start_time = time.time()
        self.send_pm(nick, 'Please wait, this will take a while.')
        res = self.query_by_share_path(data)
        if not res:
            self.send_pm(nick, '\n' + 'No results.')
        else:
            self.send_pm(nick, '\n' + res)
        self.log("Action search done in %0.2f sec" % (time.time() - start_time))

    def get_sql_by_search(self, search, limit=400):
        sql_wheres = []
        if search.datatype and search.datatype != dclib.DCBot.SEARCH_TYPE_ANY:
            #~ extensions = [key for key,val in DCBot.SEARCH_TYPE_ID_EXT.values() if val == DCBot.SEARCH_TYPE_ANY]
            #~ extensions
            sql_wheres.append("dc_file_type='%s'" % search.datatype)
        if search.size_restricted:
            if search.is_minimum_size:
                sql_wheres.append("size <= '%s'" % search.size)
            else:
                sql_wheres.append("size >= '%s'" % search.size)
        if search.search_pattern:
            sql_wheres.append("match(share_path) against ('%s' IN BOOLEAN MODE)" % search.search_pattern)

        if sql_wheres:
            sql_where_statement = ' where ' + ' and '.join(sql_wheres)
        else:
            sql_where_statement = ''

        sql = "select tth, filename, size, last_nick, share_path, first_found_time from shares %s order by first_found_time desc " % (sql_where_statement) + "limit %s" % limit
        return sql

    def handle_search(self, data):
        #~ self.log("Handle Search: [%s]" % data)
        try:
            s = self.parse_search(data)
        except dclib.ParseException:
            self.log("Warning: invalid search query:[%s]. Ignoring..." % data)
            return
        orig_search = s.search_pattern
        if 'showmenew' in s.search_pattern:
            self.log("A showmenew request [%s] from [%s]" % (s.search_pattern, s.nick))
            s.search_pattern = s.search_pattern.replace('showmenew', '').strip()
        elif s.search_pattern.startswith('new:'):
            self.log("A new: request [%s] from [%s]" % (s.search_pattern, s.nick))
            s.search_pattern = s.search_pattern[4:].strip()
        else:
            return

        dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
        cur = dbcon.cursor()
        active = (s.address != "" and s.nick == "")
        sql = self.get_sql_by_search(s)
        self.log("sql:[%s]" % sql)
        try:
            rowcount = cur.execute(sql)
        except dbm.OperationalError, e:
            self.log("WARNING: Database operational error [%s] Sql=%s" % (e, sql))
            return

        if not rowcount:
            path = '%s\\sorry\\no file matches "%s".txt' % (orig_search, s.search_pattern)
            if not active:
                self.send_search_response(path, file_size=1, to_nick=s.nick, from_nick=self.nick, free_slot=1)  # passive search response must use own nick, otherwise got kicked by hub
            else:
                self.send_search_response(path, file_size=1, to_address=s.address, from_nick=self.nick, free_slot=1)
        else:
            if not active:
                # send notification that this result must be "searched for alternative"
                path = '%s\\important note\\Note: Right click, and select "search for alternative".txt' % orig_search
                self.send_search_response(path, file_size=1, to_nick=s.nick, from_nick=self.nick, free_slot=1)  # passive search response must use own nick, otherwise got kicked by hub
            for tth, filename, size, last_nick, share_path, first_found_time in cur:
                dtime = time.time() - first_found_time
                # use the free slot column for age in minute so user can sort by age
                free_slot = int(dtime/60)
                path = '%s\\%s ago\\%s' % (orig_search, nice_time(time.time()-first_found_time), share_path.replace('/','\\'))
                if not active:
                    #~ self.send_search_response(path, file_size=size, to_nick=s.nick, tth=tth, from_nick=last_nick, free_slot=free_slot)
                    self.send_search_response(path, file_size=size, to_nick=s.nick, tth=tth, from_nick=self.nick, free_slot=free_slot)  # passive search response must use own nick, otherwise got kicked by hub
                else:
                    self.send_search_response(path, file_size=size, to_address=s.address, tth=tth, from_nick=last_nick, free_slot=free_slot)


    def action_optin(self, nick, data=''):
        """Bot will tell you if there's "interesting" new files that have just been shared --- realtime"""
        self.nicklist_optin.set(nick, None)
        self.send_pm(nick, 'You will be PM-ed when an "interesting file" has just been shared.')

    def action_optout(self, nick, data=''):
        """Don't tell me if there's "interesting" new files"""
        nicks = self.nicklist_optin.get_data()
        if nick in nicks:
            self.nicklist_optin.remove(nick)
            self.send_pm(nick, 'Thanks for using this service. Bye.')
        else:
            self.send_pm(nick, 'Your nick [%s] is not in the list.' % nick)

    def action_shutup(self, nick, data=''):
        """Ask whatsnewbot to shut up"""
        self.nicklist_dontpm.set(nick, None)
        self.send_pm(nick, "I'll be silent from now on :(")

    def action_list(self, nick, data=''):
        """List "interesting" new files member"""
        if not self.nicklist_optin.get_data():
            self.send_pm(nick, '\nEmpty')
        else:
            self.send_pm(nick, '\n' + '\n'.join(["%d. %s" % (i+1,n) for i,n in enumerate(self.nicklist_optin.get_data())]))

    def interesting_file_gatherer(self, name, size, tth, filename, extension, dc_file_type, owner_nick):
        #~ if size > 200*1024*1024:    #    ~_~   as simple as that
        if size > INTERESTING_FILE_SIZE:    #    ~_~   as simple as that
            msg = "<%s> %0.2f MB | %s | %s\n%s %s" % (owner_nick, size/(1024.0*1024.0), filename, os.path.dirname(name), " "*(len(owner_nick)*2 + 2), self.get_magnet(tth, complete=True, size=size, filename=filename))
            self.pm_job.append(msg)
        # from time to time, output and clear pm job to avoid outputting too much data in one go
        if len(self.pm_job) > 50:
            self.notify_optin_people()

    def query_by_sql(self, sql, args=None):
        self.log("Sql:%s" % sql)
        lines = []
        dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
        cur = dbcon.cursor()
        try:
            cur.execute(sql, args)
            for tth, filename, size, last_nick, share_path, first_found_time in cur:
                dtime = time.time() - first_found_time
                lines.append("%s - %0.2fMB - %s - by %s => %s" % (nice_time(dtime), size/(1024.0*1024.0), share_path, last_nick, self.get_magnet(tth)))
            return '\n'.join(lines)
        except dbm.OperationalError, e:
            self.log("WARNING: Database operational error [%s] Sql=%s, Args=%s" % (e, sql, args))
            return "Database operational problem. Please try again."

    def query_by_where(self, where=None, args=None, limit=DEFAULT_QUERY_LIMIT):
        if limit > CAP_QUERY_LIMIT:
            limit = CAP_QUERY_LIMIT  #cap limit to avoid get kicked by hub
        if where is not None:
            where_file_size = "where " + where
        else:
            where_file_size = ""
        sql = "select tth, filename, size, last_nick, share_path, first_found_time from shares %s order by first_found_time desc " % where_file_size + "limit %s" % limit
        return self.query_by_sql(sql, args=args)

    def query_by_file_size(self, size=None, limit=DEFAULT_QUERY_LIMIT):
        if limit > CAP_QUERY_LIMIT:
            limit = CAP_QUERY_LIMIT  #cap limit to avoid get kicked by hub
        if size is not None:
            where_file_size = "where size > %s"
        else:
            where_file_size = ""
        sql = "select tth, filename, size, last_nick, share_path, first_found_time from shares %s order by first_found_time desc " % where_file_size + "limit %s" % limit
        args = size
        return self.query_by_sql(sql, args=args)

    def action_interesting(self, nick, data=''):
        """See previous interesting files"""
        limit = self.parse_param_limit(data) or DEFAULT_QUERY_LIMIT
        self.send_pm(nick, '\n' + self.query_by_file_size(INTERESTING_FILE_SIZE, limit))

    def notify_optin_people(self):
        if not self.pm_job:
            return
        nicks = self.nicklist_optin.get_data()
        msg = '\n\n'.join(self.pm_job).encode('UTF-8')
        self.log("Sending Interesting files:\n%s\n" % msg)
        file('whatsnew_interesting_files.txt','a').write(msg)
        for nick in nicks:
            if nick in self.nicklist:   # only send to logged in people
                self.send_pm(nick, 'Interesting files detected!\n' + msg + '\n')
            # give a bit delay
            #~ time.sleep(1)
        self.pm_job = []

    def file_is_ignored(self, name):
        """will return False for any share name that match these patterns
        """
        # these are 3 files that frequently become "new" coz people shares their DC++ program folder
        name = name.lower()
        return (
            name.endswith(".xml") or
            name.endswith(".xml.bz2") or    #dc++ dowloaded filelist
            name.endswith("hashdata.dat") or    #dc++ file hash cache
            name.endswith(".dctmp") or      #dc++ temporary dowload file
            name.endswith("thumbs.db") or   #windows xp thumbanail cache
            name.endswith("itunes library.itl") or
            name.endswith(".log")
            )

    def thank_nick_for_sharing(self, nick, new_count, total_size, list_of_files):
        display_tops = min(5, new_count)
        if nick not in self.nicklist_dontpm.get_data():
            if nick in self.nicklist and total_size > (500*1024):
                list_of_files.sort(reverse=True)
                info_files = ''
                for sz,name in list_of_files[:display_tops]:
                    info_files += '%0.2f MB - %s\n' % (sz/(1024.0*1024.0), name.encode('UTF-8'))
                msg = 'Thanks for sharing %d new files totaling %0.2f MB. Share more! (+shutup to silent this message)\nTop %d files:\n%s' % (new_count, total_size/(1024.0*1024.0), display_tops, info_files)
                self.log("Sending [%s] to %s" % (msg,nick))
                self.send_pm(nick, msg)

def nice_time(sec):
    sec = int(sec)
    if sec < 60:
        return "%d s" % sec
    elif sec < 3600:
        return "%d m" % (sec/60)
    elif sec < 86400:
        return "%d h" % (sec/3600)
    else:
        return  "%d d" % (sec/86400)

class DCClientGetListNotify(dclib.DCClientClient):
    """Worker DC client to client that downloads file list and exit
    """
    def on_loggedin(self):
        """Called when login successful"""
        if not self.mode_upload:
            self.store_dir = "FileLists"
            self.download_list()

    def on_finished_download(self, e):
        #~ self.log("Downloading %s finish" % e.file_path)
        # check if the just downloaded list is actually newer than before
        if self.remote_nick in self.parent.previous_nick_list_size:
            curr_size = os.path.getsize(e.file_path)
            prev_size = self.parent.previous_nick_list_size[self.remote_nick]
            #~ self.log("Current list len is [%d], while previously [%d]" % (curr_size, prev_size))
            if curr_size != prev_size:

                nick_in_queue = [x[1] for x in self.parent.dclist_to_db_queue if x[1] == self.remote_nick]
                if not nick_in_queue:
                    # add to TTH gatherer parse list job queue, so he can parse the list and insert the TTH to database
                    self.parent.dclist_to_db_queue.append([e.file_path, self.remote_nick])
                    self.log("List changed, adding parsing job (%d pending now)..." % len(self.parent.dclist_to_db_queue))
                else:
                    self.log("Nick [%s] already in queue, not adding..." % self.remote_nick)

            else:
                #~ self.log("Size not changed, ignoring...")
                pass
        else:
            # dont add if that nick is still on the queue
            nick_in_queue = [x[1] for x in self.parent.dclist_to_db_queue if x[1] == self.remote_nick]
            if not nick_in_queue:
                self.parent.dclist_to_db_queue.append([e.file_path, self.remote_nick])
                #~ self.log("Adding parsing job (%d pending now)..." % len(self.parent.dclist_to_db_queue))
            else:
                self.log("Nick [%s] already in queue, not adding..." % self.remote_nick)


        # after finishes downloading (happens just after sending $Send message), close us
        self.running = False

BOT_CLASS = WhatsNewBot

if __name__ == '__main__':
    import optparse, sys, codecs
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("--dc_ip", dest="dc_server_ip", help="DC++ Server address (def:%s)" % DEFAULT_DC_HOST, default=DEFAULT_DC_HOST)
    parser.add_option("--dc_port", type="int", dest="dc_server_port", help="CS Server port (def:%s)" % DEFAULT_DC_PORT, default=DEFAULT_DC_PORT)
    parser.add_option("--nick", dest="nick", help="DC++ nick name (def:%s)" % DEFAULT_NICK, default=DEFAULT_NICK)
    options, args = parser.parse_args()

    bot = BOT_CLASS(address=(options.dc_server_ip, options.dc_server_port), nick=options.nick, password=DEFAULT_PASSWORD, description=DEFAULT_NICK_DESCRIPTION)

    try:
        import psyco
        psyco.profile()
    except ImportError:
        pass

    try: os.mkdir("FileLists");
    except: pass

    try:
        bot.run()
    finally:
        print "Finish, waiting for other threads..."

