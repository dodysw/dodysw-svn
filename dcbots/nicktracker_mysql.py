#!/usr/bin/python
"""
Nick Tracker
Copyright 2006, Dody Suria Wijaya <dodysw@gmail.com>

Goal:
To track a nick of its IP, hub, date/time when they log on, when they log off, tag ID, and their share size, record in database, and triggered by:
    - ip change
    - client change
log as event, or PM admin. Then when bot found a nick has the exact share size w/ another nick, log event as "possible matching".
Event type:
1- current (nicks present when bot logged in to hub, and recorded after IP query finish) -> $MyInfo and the bot is not yet logged in
2- login (recorded after IP query finish) -> $MyInfo and the nick is not yet in hub nick list
3- logout(recorded after IP query finish) -> $Query
4- active search (recorded after IP query finish, and if the IP has matching nick) -> $Search 150.203.222.95:17115
5- passive search (recorded after IP query finish, and if the nick has matching IP) -> $Search Hub:Schweppes
6- file list request?
7- public chat? (recorded after IP query finish, and the nick has matching IP, and the nick write something to public chat)
8- share/tag change (recorded after IP query finish, and the nick has matching IP) -> $MyInfo and the nick is already in hub nick list


- bot login
- WorkerA: handle myinfo, record now1
- WorkerA: send getfilelist
- WorkerB: incoming connection, got ip, record now2, give to WorkerA
- WorkerA: iterate job list for nick = nick, save the ip + now2

"""
import re, time, threading, dclib, socket, datetime
import MySQLdb as dbm

__version__ = '1.0.0'
__description__ = ''

from nicktracker_config import HUBS_ADDRESS, DEFAULT_NICK, DEFAULT_PASSWORD, DEFAULT_NICK_DESCRIPTION

from trackerfunc import *

MYSQL_CONNECT_PARAM = dict(host='localhost', user='nicktracker', passwd='nicktracker', db='nicktracker')

class Subject:
    _observers = {}
    def attach(self, observer):
        self._observers[observer] = 1
    def detach(self, observer):
        if observer in self._observers:
            del self._observers[observer]
    def notify(self, modifier=None):
        for observer in self._observers:
            if modifier != observer:
                observer.update(self)


class TheTrackerGuy(Subject):
    def __init__(self):
        self.dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
        self.last_sr_nick = None

    def run(self):
        pass


class NickTracker(dclib.DCActionBot):

    def init(self):
        self.share_size = 250*1024*1024
        self.dc_client_class = DCClientGetIp
        self.startup_time = time.time()
        self.nicks_with_ip = {}

    def on_loggedin(self):
        self.loggedin_time = time.time()


    def handle_myinfo_parsed(self, nick, description, share_size, flag, connection_type, email):
        """Note this handle gets called BEFORE Hello, but the order is not specified in standard.
        """
        super(NickTracker, self).handle_myinfo_parsed(nick, description, share_size, flag, connection_type, email)
        # query db of this nick's last share size
        if nick == self.nick:   # ignore own nick
            return

        if nick in self.nicks_with_ip:
            # we already know the ip, record the info
            # assume that if we know the ip, then he already logged in
            ip = self.nicks_with_ip[nick]
            self.log("%s: %s(%s, subnet %s, hostname %s ) changed tag info" % (self.address[0], nick, ip, getsubnetfromip(ip), gethostname(ip)))
        else:
            # ask nick's ip

            # if it's happen 3 second after bot login, consider this nick as logging in
            if self.loggedin_time - time.time() > 3:
                # mark here
                self.send_connecttome(nick)
            else:
                self.send_connecttome(nick)

    def has_permission(self, requesting_nick):
        # check permission
        if requesting_nick not in self.nicks_with_ip:
            self.send_connecttome(requesting_nick)
            return False
        if self.nicks_with_ip[requesting_nick] == self.sock.getsockname()[0]:
            return True
        else:
            return False


    def action_help(self, requesting_nick, data=''):
        if not self.has_permission(requesting_nick):
            self.send_pm(requesting_nick, 'Wang wang...')
            return
        super(NickTracker, self).action_help(requesting_nick, data)

    def action_where(self, requesting_nick, data=''):
        """Show you where the nicks access DC from"""

        if not self.has_permission(requesting_nick):
            self.send_pm(requesting_nick, 'Permission denied mwuhahahahahaha.')
            return

        subnets = {}
        for nick in self.nicklist:
            if nick in self.nicks_with_ip:
                ip = self.nicks_with_ip[nick]
                subnet_name = getsubnetfromip(ip)
                subnets.setdefault(subnet_name, [])
                subnets[subnet_name].append("%s (%s/%s)" % (nick,ip,gethostname(ip)))
            else:
                subnet_name = 'IP not known'
                subnets.setdefault(subnet_name, [])
                subnets[subnet_name].append(nick)

        output = []
        subnet_names = subnets.keys()
        subnet_names.sort()
        for subnet_name in subnet_names:
            nicks = subnets[subnet_name]
            nicks.sort()
            output.append('+ %s (%s nicks):' % (subnet_name, len(nicks)))
            for nick in nicks:
                output.append('      - %s' % nick)

        #~ self.log("sending:%s" % '\n'.join(output))

        self.send_pm(requesting_nick, '\n' + '\n'.join(output))


    def action_hist(self, requesting_nick, data=''):

        if not self.has_permission(requesting_nick):
            self.send_pm(requesting_nick, 'Permission denied mwuhahahahahaha.')
            return

        subnets = {}
        sql = "select ip from ips"
        cur.execute(sql)
        for (ip,) in cur.fetchall():
            subnet_name = getsubnetfromip(ip)
            subnets.setdefault(subnet_name, [])
            subnets[subnet_name].append(ip)


        subnet_names = subnets.keys()
        subnet_names.sort()

        output = []
        active_count = subnet_count = unique_ip_count = 0
        dbcon = cur = None
        for subnet_name in subnet_names:
            ips = subnets[subnet_name]
            ips.sort(ip_sorter)
            output.append('+ %s (%s ip):' % (subnet_name, len(ips)))
            unique_ip_count += len(ips)
            subnet_count += 1
            if 's' in data:
                continue
            for ip in ips:
                output.append('      %s (%s):' % (ip, gethostname(ip)))
                for nick in self.ip_history[ip]:
                    ts = self.ip_history[ip][nick]['timestamp']
                    ss = self.ip_history[ip][nick].get('sharesize',-1)
                    active = (self.nicks_with_ip.get(nick,None) == ip)
                    active_str = ''
                    if active:
                        active_count += 1
                        active_str = '[UP]'
                    searches = ''
                    if self.ip_history[ip][nick].get('search', False):
                        searches = ' searches:%s' % (','.join(self.ip_history[ip][nick]['search']))
                    output.append('            %s%s (%s/%s)%s' % (active_str,nick, ts.strftime('%H:%M %d%b%y'), ss,searches))

                    if 't' in data:
                        self.ip_history[ip][nick].setdefault('tthsearch', [])
                        if self.ip_history[ip][nick]['tthsearch']:
                            output.append('              tth searches:')
                            if not dbcon or not cur:
                                dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
                                cur = dbcon.cursor()
                            for tth in self.ip_history[ip][nick]['tthsearch']:
                                # resolve tth to file
                                if cur.execute('select share_path, size from shares where tth=%s', (tth,)):
                                    share_path,size = cur.fetchone()
                                    output.append('              - %s (%0.2f MB)' % (share_path, size/(1024.0*1024.0)))
                                else:
                                    output.append('              - %s' % tth)
        output.append('=============================================')
        output.append('%d subnets  %d unique ip' % (subnet_count, unique_ip_count))

        self.trackerguy.ip_history_db.save()
        # ah good time to save ip host cache
        hostnames_db.save()


        file('nicktracker_lasthist.txt','w').write('\n'.join(output))
        if data == 'o':
            self.send_pm(requesting_nick, '\n' + '\n'.join(output))
        else:
            self.send_pm(requesting_nick, 'Done.')

    def handle_nicklist(self, nicks):
        super(NickTracker, self).handle_nicklist(nicks)

        # if hub support nogetinfo protocol, hub does not need to be sent GetInfo to get all nicks MyINFO
        if 'NoGetINFO' not in self.remote_support:
            self.send_batch_begin()
            for nick in self.nicklist:
                if nick == self.nick:
                    continue
                self.send_getinfo(nick)
            self.send_batch_end()


    def handle_quit(self, nick):
        super(NickTracker, self).handle_quit(nick)
        try: del self.nicklist[nick]
        except KeyError: pass

        try: del self.nicks_with_ip[nick]
        except KeyError: pass

    def check_dupe_sr(self, s):
        # don't sent if last query to avoid duplicate SR
        if s.is_active():
            id = s.address
        else:
            id = s.nick

        isdupe = False

        if self.trackerguy.last_sr_nick:
            last_sr_id, age = self.trackerguy.last_sr_nick[0], time.time() - self.trackerguy.last_sr_nick[1]
            if last_sr_id == id and age < 3:
                isdupe = True

        self.trackerguy.last_sr_nick = id, time.time()
        return isdupe

    def handle_search(self, data):
        try:
            s = self.parse_search(data)
        except dclib.ParseException:
            self.log("Warning: invalid search query:[%s]. Ignoring..." % data)
            return

        if self.check_dupe_sr(s):
            self.log("Dupe search [%s]. Ignoring..." % s.search_pattern)
            return

        if s.is_active():
            # get its nick
            searcher_ip = s.address.split(':')[0]
            nick = [nick for nick,ip in self.nicks_with_ip.items() if ip == searcher_ip]
            if not nick:
                # can't find, probably we have not resolve the ip to nick
                self.log("Cant find [%s] in IP list, cant do anything" % (searcher_ip))
                return
            else:
                remote_nick = nick[0]
        else:
            remote_nick = s.nick
            # get its ip
            if remote_nick not in self.nicks_with_ip:
                self.log("Cant find [%s] in nick list, requesting IP..." % (remote_nick))
                self.send_connecttome(remote_nick)
                return
            searcher_ip = self.nicks_with_ip[remote_nick]

        self.log("%s: %s(%s, subnet %s, hostname %s ) searches: %s" % (self.address[0], remote_nick, searcher_ip, getsubnetfromip(searcher_ip), gethostname(searcher_ip), s.search_pattern))

        self.ip_history.setdefault(searcher_ip, {})
        self.ip_history[searcher_ip].setdefault(remote_nick, {})
        self.ip_history[searcher_ip][remote_nick]['timestamp'] = datetime.datetime.now()
        if remote_nick in self.nicklist:   #possible if search came from logged out user
            self.ip_history[searcher_ip][remote_nick]['sharesize'] = self.nicklist[remote_nick].share_size

        if s.search_pattern.startswith('TTH:'):
            self.ip_history[searcher_ip][remote_nick].setdefault('tthsearch', [])
            if s.search_pattern[4:] not in self.ip_history[searcher_ip][remote_nick]['tthsearch']:
                self.ip_history[searcher_ip][remote_nick]['tthsearch'].append(s.search_pattern[4:])
        else:
            self.ip_history[searcher_ip][remote_nick].setdefault('search', [])
            self.ip_history[searcher_ip][remote_nick]['search'].append(s.search_pattern)

    def after_getting_ip(self, remote_nick, remote_address):
        # find entry job queue that matches the nick, and complete the ip address
        for job in self.job_queue:
            if job.nick == remote_nick:
                job.ip = remote_address[0]
                self.process_job(job)
                return

        self.log("Nick %s (%s) not found in job queue" % (remote_nick, remote_address[0]))

    def process_job(self, job):
        # for now, just output the job info
        self.log("%s|%s|%s" % (job.eventid, job.nick, job.ip))

    def handle_userip(self,data):
        for part in data.split('$$'):
            remote_nick, ip = part.split(' ')
            self.log("%s: %s(%s, subnet %s, hostname %s ) told by hub" % (self.address[0], remote_nick, ip, getsubnetfromip(ip), gethostname(ip)))
            self.nicks_with_ip[remote_nick] = ip

            self.ip_history.setdefault(ip, {})
            self.ip_history[ip].setdefault(remote_nick, {})
            self.ip_history[ip][remote_nick]['timestamp'] = datetime.datetime.now()
            self.ip_history[ip][remote_nick].setdefault('search', [])

class DCClientGetIp(dclib.DCClientClient):
    """Get IP and quit
    """
    def handle_mynick(self, data):
        remote_nick = data
        remote_address = self.sock.getpeername()
        # do something with remote_nick and address
        ip = remote_address[0]
        self.log("%s: %s(%s, subnet %s, hostname %s ) logged in or changed tag info" % (self.parent.address[0], remote_nick, ip, getsubnetfromip(ip), gethostname(ip)))
        self.parent.nicks_with_ip[remote_nick] = ip

        self.parent.ip_history.setdefault(ip, {})
        self.parent.ip_history[ip].setdefault(remote_nick, {})
        self.parent.ip_history[ip][remote_nick]['timestamp'] = datetime.datetime.now()
        self.parent.ip_history[ip][remote_nick]['sharesize'] = self.parent.nicklist[remote_nick].share_size
        self.parent.ip_history[ip][remote_nick].setdefault('search', [])

        #~ self.parent.after_getting_ip(remote_nick, remote_address)
        self.running = False

BOT_CLASS = NickTracker

if __name__ == '__main__':
    import optparse, sys, codecs
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("--nick", dest="nick", help="DC++ nick name (def:%s)" % DEFAULT_NICK, default=DEFAULT_NICK)
    options, args = parser.parse_args()

    w = TheTrackerGuy()
    for i, hubconfig in enumerate(HUBS_ADDRESS):
        hub = BOT_CLASS(address=(hubconfig['host'], hubconfig['port']), password=hubconfig.get('password', DEFAULT_PASSWORD), nick=hubconfig.get('nick', options.nick), description=DEFAULT_NICK_DESCRIPTION)
        w.attach(hub)
        hub.trackerguy = w
        threading.Thread(target=hub.run, name="Hub @ %s:%s" % (hubconfig['host'], hubconfig['port'])).start()

    threading.Thread(target=w.run, name="Tracker Guy").start()

    # Python will wait until all threads complete.