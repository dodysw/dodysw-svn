"""DirectConnect bot-client library
Copyright 2005, 2006 Dody Suria Wijaya <dodysw@gmail.com>

DCBot provides and encapsulate Direct Connect protocol details of operation so you can make
a bot that do specific stuff you want easily and quickly. The goal is for subclass to be able
to provide custom service under 200 lines of code.

Some examples of use:
    - weather bot: pull local temperature data from website, display as fake shared size + nick description + spam in search result
    - game bot: accept query from PM of registered Counter Strike (and many of its derivatives) LAN sessions, and Warcraft III, displaying server IP, current players, and opt-in to receive PM when new session has been registered
    - trivia cheat bot: grab trivia question from public chat, and pull answer from trivia answer database, and type answer as if it was human who did it.
    - i am mentioned bot: grab public chat for someone typing any user's nick, and PM the line to mentioned user.
    - whats new bot (in progress): display new shares since last update in via [own filelist|search result|PM] by diff-ing daily share filelist of all nicks

If you want DirectConnect REAL client codes, check out these modules instead:
    - pyDC: the Python-based P2P file sharing client

To create bot, subclass from DCBot, and these methods are overrideable/extendable:
    handle_hubname = the hub tell its name
    handle_validatedenide = i forgot (ADVANCED)
    handle_getpass = hub wants your password (ADVANCED)
    handle_badpass = hub says your password is invalid (ADVANCED)
    handle_to = someone send you a PM (personal message)
    handle_hello = hub says this user just logged in (EXTEND, DONT OVERRIDE)
    handle_quit = hub says this user just logged out
    handle_forcemove = hub redirects your log in to that ip (ADVANCED)
    handle_search = hub distributed a search query from a user to me
    handle_sr = either hub (passive search) or client directly (active search) telling me their result of my search query.
    handle_myinfo = hub passed a user ID-ing him self
    handle_others = bot doesn't understand the message and let you work on it
    handle_nicklist = hub send you a list of nicks currently logged-in
    handle_oplist = hub send you a list of operator's nicks currently logged-in
    handle_hubtopic = hub tell you current hub topic
    handle_public_chat = hub distributes a public chat message to you
    handle_connecttome = hub passed a message from a user, requesting you to make a "Client to Client" connection to that user (ADVANCED)
    on_loggedin = you have just successfuly logged in
    **on_filelist_downloaded = a filelist has just been downloaded in response to download_filelist method (See below)

As everything is done syncrhonously, make sure your custom job don't take too long in order to avoid blocking the rest. If you estimate
a significant time of duration, here are some tips:
    - spawn a thread every time a work is needed.
    - spawn a working thread after logging in, then in each handler, route incoming data to that thread.

To help you send something to hub or client, use these methods:
    send = send any data to hub (ADVANCED)
    send_udp = send any data to a user via UDP (ADVANCED)
    send_pm = send PM (via hub) to a user
    send_connecttome = send a request (via hub) to a user, asking that user to make a direct "Client to Client" connection to me. Used to download file from a user. (ADVANCED)
    send_revconnecttome = send a request (via hub) to a user, asking that user to send me a connecttome request. Confused eh? Basically in DC view, a passive way of downloading file from a user. (ADVANCED)
    send_public_chat = send a public chat message to hub
    send_search = send a search request (via hub) to all users. Override handle_sr to get the search result.
    send_search_response = send a search result either directly to user (in active search came) or via hub (passive search). Very useful in handle_search.
    send_getnicklist = request hub to send you list of currenty logged in nicks.
    **download_filelist = (internally complex set of) command to download a user's filelist. This method return immediately, see on_filelist_downloaded to handle the file when it's finished
        - add pending DCClientFileListDownloader as X client TCP worker
        - send_connectome to X
        - spawn client TCP listener (if not yet)
            - accept X
            - delete X in pending TCP worker
            - spawn C2CBot filelist downloader
                - C2CCBot download filelist
                - C2CBot call on_filelist_downloaded w/ file path in filesystem

** = NOT YET IMPLEMENTED

Further references:
- http://dcpp.net/wiki/index.php/Main_Page

"""

import sys, socket, time, threading, re, os, random, md5, string, array, datetime, zlib

READ_BLOCK = 1024*64    # read file in 64KB block
NETWORK_READ_BLOCK = 4096

WIN32_INVALID_CHARS = ":*?/\\\"<>|"
#~ POSIX_INVALID_CHARS = "/"
#~ if os.name == 'nt':
    #~ TABLE_TRANSLATOR = string.maketrans(WIN32_INVALID_CHARS, '#'*len(WIN32_INVALID_CHARS))
#~ else:
    #~ TABLE_TRANSLATOR = string.maketrans(POSIX_INVALID_CHARS, '#'*len(POSIX_INVALID_CHARS))
    
# now just use win32 table since I possibly uses fat32 (which does not accept *) even in linux
TABLE_TRANSLATOR = string.maketrans(WIN32_INVALID_CHARS, '#'*len(WIN32_INVALID_CHARS))

def get_filesafe_name(filename):
    """parse filename for invalid characters, replace it with safe character(s)"""
    # replace with # + prefix with 5 char hash key of the original filename
    tf = filename.translate(TABLE_TRANSLATOR)
    if tf != filename:
        return '%s_%s' % (md5.new(filename).hexdigest()[:5], tf)
    return filename

def lock2key(lock):
    "Generates response to $Lock challenge from Direct Connect Servers -- by Benjamin Bruheim, optimized by Dody Suria Wijaya"
    lock = array.array('B', lock)
    ll = len(lock)
    key = list('0'*ll)
    for n in xrange(1,ll):
        key[n] = lock[n]^lock[n-1]
    key[0] = lock[0] ^ lock[-1] ^ lock[-2] ^ 5
    for n in xrange(ll):
        key[n] = ((key[n] << 4) | (key[n] >> 4)) & 255
    result = ""
    for c in key:
        if c in (0, 5, 36, 96, 124, 126):
            result += "/%%DCN%.3i%%/" % c
        else:
            result += chr(c)
    return result

class ParseException(Exception):
    pass

class SearchObject(object):
    def __init__(self, **kwargs):
        self.__dict__.update(kwargs)
    def is_active(self):
        return (self.address != "" and self.nick == "")

class SRObject(object):
    def __init__(self, **kwargs):
        self.__dict__.update(kwargs)

class TransferState(object):
    def __init__(self, sharename, file_path, size, start_pos, compressed=False):
        self.sharename = sharename
        self.file_path = file_path
        self.size = int(size)
        self.start_pos = int(start_pos)
        self.compressed = compressed
        self.compressed_obj = None
        self.compressed_size = 0

class DCUser(object):
    def __init__(self, nick):
        self.nick = nick
        self.share_size = None
        self.description = ''
        self.tag = ''
        self.connection_type = ''


class DCBot(object):
    SEARCH_TYPE_ANY = 1
    SEARCH_TYPE_AUDIO = 2         # ("mp3", "mp2", "wav", "au", "rm", "mid", "sm")
    SEARCH_TYPE_COMPRESSED = 3    # ("zip", "arj", "rar", "lzh", "gz", "z", "arc", "pak")
    SEARCH_TYPE_DOCUMENT = 4      # ("doc", "txt", "wri", "pdf", "ps", "tex")
    SEARCH_TYPE_EXECUTEABLE = 5   # ("pm", "exe", "bat", "com")
    SEARCH_TYPE_PICTURE = 6       # ("gif", "jpg", "jpeg", "bmp", "pcx", "png", "wmf", "psd")
    SEARCH_TYPE_VIDEO = 7         # ("mpg", "mpeg", "avi", "asf", "mov")
    SEARCH_TYPE_FOLDER = 8
    SEARCH_TYPE_ID = dict(mp3=2, mp2=2, wav=2, au=2, rm=2, mid=2, sm=2,
        zip=3, arj=3, rar=3, lzh=3, gz=3, z=3, arc=3, pak=3,
        doc=4, txt=4, wri=4, pdf=4, ps=4, tex=4,
        pm=5, exe=5, bat=5, com=5,
        gif=6, jpg=6, jpeg=6, bmp=6, pcx=6, png=6, wmf=6, psd=6,
        mpg=7, mpeg=7, avi=7, asf=7, mov=7
        )
    SEARCH_TYPE_ID_EXT = dict(mp3=2, mp2=2, wav=2, au=2, rm=2, mid=2, sm=2, wma=2, m4a=2,
        zip=3, arj=3, rar=3, lzh=3, gz=3, z=3, arc=3, pak=3, bz2=3,
        doc=4, txt=4, wri=4, pdf=4, ps=4, tex=4, chm=4,
        pm=5, exe=5, bat=5, com=5,
        gif=6, jpg=6, jpeg=6, bmp=6, pcx=6, png=6, wmf=6, psd=6,
        mpg=7, mpeg=7, avi=7, asf=7, mov=7, ram=7, rmv=7, rmvb=7, mkv=7, wmv=7, asx=7, ogm=7
        )

    MYINFO_FLAG_NORMAL, MYINFO_FLAG_NORMAL_AWAY, MYINFO_FLAG_SERVER, MYINFO_FLAG_SERVER_AWAY, MYINFO_FLAG_FIREBALL, MYINFO_FLAG_FIREBALL_AWAY = chr(1), chr(3), chr(4), chr(6), chr(8), chr(10)
    CLIENT_ACTIVE, CLIENT_PASSIVE, CLIENT_SOCKS5 = 'A', 'P', '5'

    C2C_STARTINGPORT = 40500
    ACTIVESR_STARTINGPORT = 40100
    RECONNECT_DELAY = 15 # seconds

    re_sr_parse = re.compile("(\S+) (.*?) (\d+)\/(\d+)\x05(.*?) \(([^\)]+)\)")
    re_search_parse = re.compile("(\S+) (\w)\?(\w)\?(\d+)\?(\d+)\?(.*)") # data = '$Search Hub:%s %s?%s?%d?%d?%s' % (self.nick, size_restricted and 'T' or 'F', is_minimum_size and 'T' or 'F', size, datatype, search_pattern)
    c2c_sock = c2cr_sock = activesr_sock = None
    running = retry_connection = True
    is_loggedin = send_batch = False
    share_size = 1
    free_slot = 5
    total_slot = 5
    list_store = ''    # path where downloaded FileList placed
    supports = ('NoGetINFO', 'NoHello', 'UserIP2', 'TTHSearch', 'QuickList')
    myinfo_flag = MYINFO_FLAG_NORMAL
    client_id = 'DCBOT++'
    def __init__(self, address, nick='g_bot', password='', description='', list_path='', share_dir=''):
        self.address = address
        self.nick = nick
        self.password = password
        self.description = description
        self.version = '1.0'
        self.connection_type = 'LAN(T3)' # ["56Kbps"|"Satellite"|"DSL"|"Cable"|"LAN(T1)"|"LAN(T3)"]
        self.email = ''
        self._lock_send = threading.RLock()
        self.list_path = list_path          # this must be filled if bot wish to "share" file
        self.share_dir = share_dir  # this must be filled if bot wish to "share" file
        self.dc_client_class = DCClientClient  # class of DCClientClient used when connecting to client
        self.nicklist = {}
        self._send_batches = []
        self.remote_supports = ()
        self.share_tth_db = {}  # tth to path resolver database
        self.already_init = False

    def init(self):
        pass

    def log(self, data):
        if __debug__:
            print '%s[%s]Hub: %s' % (time.strftime("%X", time.localtime()), self.address[0], data)

    def get_tag(self):
        """recompute client-id tag
            *  ++: indicates the client
            * V: tells you the version number
            * M: tells if the user is in active (A), passive (P), or SOCKS5 (5) mode
            * H: tells how many hubs the user is on and what is his status on the hubs. The first number means a normal user, second means VIP/registered hubs and the last one operator hubs (separated by the forward slash ['/']).
            * S: tells the number of slots user has opened
            * O: shows the value of the "Automatically open slot if speed is below xx KiB/s" setting, if non-zero
        Example:
        <++ V:0.673,M:P,H:0/1/0,S:2>
        """
        self.tag = '<%s V:%s,M:%s,H:1/0/0,S:%d>' % (self.client_id, self.version, DCBot.CLIENT_ACTIVE, self.total_slot)
        return self.tag

    def parse_sr(self, data):
        """
        $SR <source_nick> <result> <free_slots>/<total_slots><0x05><hub_name> (<hub_ip:listening_port>)[<0x05><target_nick>]|
            <result> =  <file_name><0x05><file_size> for file results, or <directory> for directory results

        see http://dcpp.net/wiki/index.php/$SR
        """
        m = self.re_sr_parse.search(data)
        if not m:
            raise ParseException
        nick, result, open_slot, total_slot, hubname_or_tth, hub_addr = m.groups()

        if chr(5) in result:
            # file
            file_path, file_size = result.split(chr(5))
        else:
            # directory
            file_path = result
            file_size = 0

        if ':' in hub_addr:
            hubip, hubport = hub_addr.split(':')
            hubport = int(hubport)
        else:
            hubip, hubport = hub_addr, 411

        if hubname_or_tth.startswith('TTH:'):
            hubname = None
            tth = hubname_or_tth[hubname_or_tth.index(':')+1:]
        else:
            hubname = hubname_or_tth
            tth = None

        return SRObject(nick=nick, file_path=file_path, file_size=int(file_size), open_slot=int(open_slot), total_slot=int(total_slot), hubname=hubname, tth=tth)


    def parse_search(self, data):
        m = self.re_search_parse.search(data)
        if m:
            nick_or_address, size_restricted, is_minimum_size, size, datatype, search_pattern = m.groups()
            nick = address = ''
            if nick_or_address.startswith('Hub:'):
                nick = nick_or_address[nick_or_address.index(':')+1:]
            else:
                address = nick_or_address
            #  invert of this: Spaces are replaced by '$',  '$' and '|' are escaped with "&#36;" and "&#124;", with '&' being further replaced with "&amp;"
            search_pattern = search_pattern.replace('$', ' ').replace('&#124;', '|').replace('&#36;', '$').replace('&', '&amp;')
            return SearchObject(nick=nick, address=address, size_restricted=(size_restricted == 'T'), is_minimum_size=(is_minimum_size == 'T'), size=int(size), datatype=int(datatype), search_pattern=search_pattern)
        raise ParseException

    def on_loggedin(self):
        """Called when login successful"""
        pass

    def get_myinfo(self):
        return '$MyINFO $ALL %s %s %s$ $%s%s$%s$%s$' % (self.nick, self.description, self.get_tag(), self.connection_type, self.myinfo_flag, self.email, self.share_size)

    def send_batch_begin(self):
        self.send_batch = True
        self._send_batches = []

    def send_batch_end(self):
        self.send_batch = False
        self.send(*self._send_batches)
        self._send_batches = []

    def send(self, *args):

        # this allows you to do many self.send and grouped them together, and send them in 1 batch using self.send_batch_end
        if self.send_batch:
            self._send_batches.extend(args)
            return

        # only 1 thread is allowed to send in a time
        self._lock_send.acquire()
        try:
            buffer = '|'.join([x.replace('|','&#124;') for x in args]) + '|'
            try:
                self.sock.send(buffer)
                #~ self.log('Sent:' + repr(buffer))
            except socket.error, e:
                self.log('Socket error [%s] when sending [%s]. Disonnecting...' % (e, repr(buffer[:200])))
                self.runnning = False
                return
        finally:
            self._lock_send.release()

    def send_udp(self, host, port, data):
        #~ self.log("Sending UDP data to [%s:%s]: %s" % (host,port, repr(data)))
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        try:
            return sock.sendto(data, (host,port))
        except socket.error, e:
            self.log("Sending UDP data to [%s:%s] FAIL. Ignoring.." % (host,port))
            return None

    def send_pm(self, nick, data):
        # send personal message @data to @nick
        return self.send('$To: %s From: %s $<%s> %s' % (nick, self.nick, self.nick, data))

    def c2c_init(self):
        if self.c2c_sock:
            #~ self.log('Client listener already spawned.')
            return
        #~ self.log('Initialising client listener thread.')
        self.c2c_sock = self._allocate_bind_port(self.C2C_STARTINGPORT, 100)
        # spawn it on different thread c2c listener
        def c2c_handler_thread(sock, addr):
            self.dc_client_class(self, sock, nick=self.nick, list_path=self.list_path, share_dir=self.share_dir).run()
        def c2c_listener_thread():
            while 1:
                conn, addr = self.c2c_sock.accept()
                #~ self.log('Client connection from %s:%s, spawning new handler.' % (addr[0], addr[1]))
                #which spawn child thread to handle the connection
                threading.Thread(target=c2c_handler_thread, args=(conn,addr), name="C2C Download@%s:%s" % addr).start()
        threading.Thread(target=c2c_listener_thread, name="ConnectToMe Listener").start()

    def c2cr_init(self):
        # special listener socket for reverse connect
        if self.c2cr_sock:
            #~ self.log('Client reverse connect listener already spawned.')
            return
        self.log('Initialising client reverse connect listener thread.')
        self.c2cr_sock = self._allocate_bind_port(self.C2C_STARTINGPORT, 100)
        # spawn it on different thread c2c listener
        def c2c_handler_thread(sock, addr):
            self.dc_client_class(self, sock, nick=self.nick, list_path=self.list_path, share_dir=self.share_dir, upload=True).run()
        def c2c_listener_thread():
            while 1:
                conn, addr = self.c2cr_sock.accept()
                #~ self.log('Client (reverse) connection from %s:%s, spawning new handler.' % (addr[0], addr[1]))
                #which spawn child thread to handle the connection
                threading.Thread(target=c2c_handler_thread, args=(conn,addr), name="C2C(Reverse+Upload) %s:%s" % addr).start()
        threading.Thread(target=c2c_listener_thread, name="ConnectToMe (for Reverse) Listener").start()

    def _allocate_bind_port(self, starting_port, range_port):
        myip = self.sock.getsockname()[0]
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        port = starting_port
        while 1:
            try:
                sock.bind((myip, port))
                sock.listen(1)
                break
            except socket.error:
                if (starting_port - port) > 100:
                    raise Exception, "Unable to allocate port"
                port += 1
        self.log('Binded client listener thread at %s:%s' % (myip, port))
        return sock

    def activesr_init(self):
        self.log('Initialising thread that listen active search result.')
        if self.activesr_sock:
            self.log('Active search result listener already spawned.')
            return
        myip = self.sock.getsockname()[0]
        port = self.ACTIVESR_STARTINGPORT
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        while 1:
            try:
                sock.bind((myip, port))
                break
            except socket.error:
                if (self.ACTIVESR_STARTINGPORT - port) > 100:
                    raise Exception, "Unable to allocate port for ACTIVESR listener"
                port += 1
        self.activesr_sock = sock

        def activesr_listener_thread():
            while 1:
                data, addr = self.activesr_sock.recvfrom(4096)
                self.log('Incoming client datagram from %s:%s :%s' % (addr[0], addr[1], repr(data)))
                if data.startswith('$SR '):
                    self.handle_sr(data[4:])

        self.log('Spawning active search result listener thread at %s:%s' % (myip, port))
        threading.Thread(target=activesr_listener_thread, name="SR Response Listener").start()

    def send_connecttome(self, nick, dc_client_class=None, revconnect=False):
        """send $ConnectToMe to make a nick connect to my computer
        @revconnect = indicates that this actually triggered by RevConnectToMe. Listen on special port to differentiate incoming connection (that connection will do upload session instead of normal download)
        """
        if dc_client_class:
            self.dc_client_class = dc_client_class

        # we have to create TCP listener 1st if it's not exist
        if revconnect:
            self.c2cr_init()
            myip, port = self.c2cr_sock.getsockname()
        else:
            self.c2c_init()
            myip, port = self.c2c_sock.getsockname()

        # then send the ip+port to server
        #~ self.log('Sending $ConnectToMe %s %s:%s' % (nick, myip, port))
        self.send('$ConnectToMe %s %s:%s' % (nick, myip, port))

    def send_revconnecttome(self, nick):
        """send $RevConnectToMe to cause target nick to send $ConnectToMe to me"""
        self.send('$RevConnectToMe %s %s' % (self.nick, nick))

    def send_public_chat(self, data):
        # send public chat message @data
        return self.send('<%s> %s' % (self.nick, data))

    def send_search(self, search_pattern='', size_restricted=False, is_minimum_size=True, size=0, datatype=None, active=True):
        """
        $Search <ip>:<port> <searchstring>
        """
        if datatype is None:
            datatype = DCBot.SEARCH_TYPE_ANY
        #  Spaces are replaced by '$',  '$' and '|' are escaped with "&#36;" and "&#124;", with '&' being further replaced with "&amp;"
        search_pattern = search_pattern.replace('&', '&amp;').replace('$', '&#36;').replace('|', '&#124;').replace(' ','$')
        if active:
            # create listening UDP port if not yet created
            self.activesr_init()
            myip, port = self.activesr_sock.getsockname()
            data = '$Search %s:%s %s?%s?%d?%d?%s' % (myip, port, size_restricted and 'T' or 'F', is_minimum_size and 'T' or 'F', size, datatype, search_pattern)
            #~ self.log("Sending active search [%s]." % data)
            self.send(data)
        else:
            data = '$Search Hub:%s %s?%s?%d?%d?%s' % (self.nick, size_restricted and 'T' or 'F', is_minimum_size and 'T' or 'F', size, datatype, search_pattern)
            #~ self.log("Sending passive search [%s]." % data)
            self.send(data)

    def send_search_response(self, file_path="", file_size=0, from_nick=None, to_nick="", to_address="", tth="", free_slot=None, total_slot=None):
        """
        active:
        $SR <nick> <searchresponse> <ip>:<port>
        passive:
        $SR <resultNick> <filepath>^E<filesize> <freeslots>/<totalslots>^E<hubname> (<hubhost>[:<hubport>])^E<searchingNick>

        E.g.
        active:
        $SR User1 mypathmotd.txt<0x05>437 3/4<0x05>Testhub (10.10.10.10:411)|
        passive:
        $SR User1 mypathmotd.txt<0x05>437 3/4<0x05>Testhub (10.10.10.10:411)<0x05>User2|
        """
        if from_nick is None:
            from_nick = self.nick
        # On UNIX the path delimiter / must be converted to \ for compatibility.
        file_path = file_path.replace('/', '\\')
        # files with TTH, the <hub_name> parameter is replaced with TTH:<base32_encoded_tth_hash>
        hubname = self.hubname
        if tth:
            hubname = "TTH:" + tth
        active = (to_address != "" and to_nick == "")

        if free_slot is None:
            free_slot = self.free_slot
        if total_slot is None:
            total_slot = self.total_slot

        if active:
            # send response directly to client
            host, port = to_address.split(':')
            self.send_udp(host, int(port), '$SR %s %s\x05%s %s/%s\x05%s (%s:%s)|' % (from_nick, file_path, file_size, free_slot, total_slot, hubname, self.hubip, self.address[1]))
        else:
            # If the $Search was a passive one, the $SR is returned via the hub connection (TCP). In this case, <0x05><target_nick> must be included on the end of the $SR.
            self.send('$SR %s %s\x05%s %s/%s\x05%s (%s:%s)\x05%s' % (from_nick, file_path, file_size, free_slot, total_slot, hubname, self.hubip, self.address[1], to_nick))

    def send_getnicklist(self):
        self.send('$GetNickList')

    def send_getinfo(self, nick):
        self.send('$GetINFO %s %s' % (nick, self.nick))

    def process_command(self, cmdline):
        #~ self.log("CMDLine:[%s]" % repr(cmdline))
        if cmdline == '':
            return
        elif cmdline[0] == '$':
            pos_space = cmdline.find(' ')
            if pos_space == -1:
                pos_space = None
            cmd = cmdline[1:pos_space].lower()
            if cmd == 'to:':
                cmd = 'to_'

            if hasattr(self, 'handle_' + cmd):
                if pos_space is None:
                    getattr(self, 'handle_' + cmd)()
                else:
                    getattr(self, 'handle_' + cmd)(cmdline[pos_space+1:])
            else:
                self.handle_others(cmdline)

        elif cmdline[0] == '<': # public chat
            pos = cmdline.find('>')
            self.handle_public_chat(cmdline[1:pos], cmdline[pos+2:])
        elif cmdline[0] == '*': # +me /me message
            self.handle_me(cmdline)
        else:
            self.handle_others(cmdline)

    def handle_me(self,cmdline):
        pass

    def send_supports(self):
        """
        This command is used to negotiate protocol extensions. To indicate that the client or hub has at least one protocol extension available,
        it must send a $Lock that begins with EXTENDEDPROTOCOL. If the remote side also supports protocol extensions, it may send $Support. It must, however, precede $Key.
        Notes:
            * EXTENDEDPROTOCOL should not be sent if the hub/client supports no extensions
            * A blank $Supports is not permitted
            * Spaces aren't allowed in feature names
            * For client extensions, the name of the feature should be the same as the command.
        Source: http://www.dcpp.net/wiki/index.php/%24Supports
        """
        if self.supports:   # A blank $Supports is not permitted
            self.send('$Supports %s' % ' '.join(self.supports))

    def handle_lock(self, data):
        """Don't override this unless you know what you're doing
        $Lock <lock> Pk=<pk>|
        - if <lock> starts with EXTENDEDPROTOCOL, then we assume the hub supports extended protocol
        """
        self.log("Handle lock [%s]." % data)
        lock = data.split(' ')[0]
        if lock.startswith('EXTENDEDPROTOCOL'):
            self.send_supports()
        self.send('$Key %s' % lock2key(lock), '$ValidateNick %s' % self.nick)

    def handle_supports(self, data):
        self.log("Handle $Supports: [%s]" % data)
        self.remote_supports = tuple(data.split(' '))

    def handle_hubname(self, data):
        self.log("Handle hubname [%s]." % data)
        self.hubname = data

    def handle_validatedenide(self):
        """response from hub when the nickname is already in use
        No. it's not a spelling mistake, it's denide!!
        """
        self.log("Handle Validate denIDE.")
        self.send('$ValidateNick %s' % self.nick)   # insist on logging in with this nick :D
        #~ self.running = False

    def handle_hubisfull(self):
        """response from hub when hub is full
        """
        self.log("Handle $HubIsFull.")
        self.running = False

    def handle_getpass(self):
        self.log("Handle getpass.")
        self.send('$MyPass %s' % self.password)

    def handle_badpass(self):
        self.log("Handle password not allowed.")
        self.running = False

    def handle_to_(self, buffer):
        pos = buffer.find('>')
        nick = buffer[buffer.find('<')+1:pos]
        data = buffer[pos+2:]
        self.handle_to(nick, data)

    def handle_to(self, nick, data):
        self.log("PM From [%s]: [%s]" % (nick,data))

    def handle_hello(self, nick):
        self.log("Handle Hello: [%s]" % nick)
        if not self.is_loggedin and nick == self.nick:
            self.is_loggedin = True
            self.send('$Version 1,0091', self.get_myinfo())
            self.send_getnicklist()
            self.on_loggedin()
        else:
            self.nicklist[nick] = DCUser(nick)

    def handle_userip(self,data):
        for part in data.split('$$'):
            nick, ip = part.split(' ')
            # do what u want


    def handle_quit(self, data):
        """sent by hub when a nick quit
        when NoHello is specified in supports and remote_supports
        """
        #~ self.log("Handle Quit: [%s]" % data)
        pass

    def handle_forcemove(self, data):
        """sent by hub when it wants dc client to connect to given address instead"""
        self.log("Handle forcemove: [%s]" % data)

    def handle_search(self, data):
        """sent by hub passed from client for a request to search a share name"""
        #~ self.log("Handle Search: [%s]" % data)
        pass

    def handle_sr(self, data):
        """sent by hub passed by passive client for a response of a search request"""
        #~ self.log("Handle Search response: [%s]" % repr(data))
        self.parse_sr(data)

    def handle_myinfo(self, data):
        """
        sent by hub when I login of ALL nicks info data, or when a nick login to hub
        $ALL skyy {2/2} [256K/256K] PeerWeb DC++<PWDC++ V:0.301,M:P,H:1/0/0,S:2>$ $Modem?$$8194578582$
        """
        #~ self.log("Handle MyINFO: [%s]" % data)
        fields = data.strip('$').split('$')
        dummy, nick, description = fields[0].split(' ',2)
        connection_type = flag = ''
        if len(fields[2]) == 1:
            flag = fields[2][-1]
        elif len(fields[2]) > 1:
            connection_type, flag = fields[2][:-1], fields[2][-1]
        email = fields[3]
        share_size = int(fields[4])
        self.handle_myinfo_parsed(nick, description, share_size, flag, connection_type, email)

    def handle_myinfo_parsed(self, nick, description, share_size, flag, connection_type, email):
        if nick not in self.nicklist:
            self.nicklist[nick] = DCUser(nick)
        self.nicklist[nick].description = description
        self.nicklist[nick].connection_type = connection_type
        self.nicklist[nick].share_size = share_size
        self.nicklist[nick].flag = flag
        self.nicklist[nick].email = email

    def handle_others(self, data):
        self.log("Handle Others: [%s]" % data)
        pass

    def handle_nicklist(self, data):
        #~ self.log("Handle NickList: [%s]" % data)
        for nick in data.split('$$')[:-1]:  # [:-1] required as a trailing $$ exist in nicks
            self.nicklist[nick] = DCUser(nick)

    def handle_oplist(self, data):
        #~ self.log("Handle OpList: [%s]" % data)
        pass

    def handle_hubtopic(self, data):
        #~ self.log("Handle HubTopic: [%s]" % data)
        pass

    def handle_public_chat(self, nick, data):
        if not self.is_loggedin:
            self.log("Hub message from [%s]: %s" % (nick, data))
        #~ self.log("Public chat from [%s]: %s" % (nick, data))
        pass

    def handle_connecttome(self, data):
        self.log("Handle Connect to me [%s]" % (data))

        # handle only if theres something to share
        if self.share_dir or self.list_path:
            mynick, addr = data.split(' ')
            remote_ip, remote_port = addr.split(':')
            addr = (remote_ip, int(remote_port))
            # spawn child2child and do as uploader


            # spawn it on different thread c2c connector
            def c2c_connector_thread(addr):
                try:
                    sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
                    sock.connect(addr)
                except socket.error:
                    self.log('Connector: Unable to connect to %s:%s.' % (addr[0],addr[1]))
                    return
                self.log('Paths:%s %s' % (self.list_path, self.share_dir))
                self.dc_client_class(self, sock, nick=self.nick, list_path=self.list_path, share_dir=self.share_dir, upload=True).run()

            self.log('Spawning client connector thread to %s:%s' % (addr[0],addr[1]))
            threading.Thread(target=c2c_connector_thread, args=(addr,), name="C2C Upload@%s:%s" % (addr[0],addr[1])).start()

    def handle_revconnecttome(self, data):
        self.log("Reverse Connect to me [%s]" % (data))
        remote_nick, requested_nick = data.split(' ')
        if requested_nick == self.nick:
            # record this nick as doing RevConnectToMe, as eventhough we're sending ConnecToMe, it's actually an upload session
            self.send_connecttome(remote_nick, revconnect=True)


    def run(self):
        if not self.already_init:
            self.init()
            self.already_init = True

        while self.retry_connection:
            self.sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            self.is_loggedin = False
            try:
                self.hubip = socket.gethostbyname(self.address[0])
                self.sock.connect(self.address)
            except socket.error, e:
                self.log('Connect problem [%s]' % e)
                self.log('Retrying connection...')
                time.sleep(DCBot.RECONNECT_DELAY)
                continue

            buffer = ''
            self.running = True
            try:
                while self.running:
                    end_pos = buffer.find('|')
                    if end_pos == -1: # no delimited found, then the data is not complete, request a data
                        buffer += self.sock.recv(NETWORK_READ_BLOCK)    # TODO: on raw data RECV, it is possible tht the raw data follows IMMEDIATELY to $ADSSND....|RAWDATAAAAA.
                        if not buffer:
                            self.log("Ending thread as socket has been disconnected.")
                            self.running = False
                            break
                        continue
                    self.process_command(buffer[:end_pos])
                    buffer = buffer[end_pos+1:]
            except socket.error, e:
                self.log("Socket error when receiving: %s, closing socket" % e)
            self.sock.close()
            self.log('Retrying connection in %s second...' % DCBot.RECONNECT_DELAY)
            time.sleep(DCBot.RECONNECT_DELAY)

    def stop(self):
        self.running = False
        self.retry_connection = False

class CCBaseWorker(object):
    """Basic client to client worker

    Standard support (DcLst). Typical scenario:
    -> $GetListLen|
    <- $ListLen <len>|
    -> $Get MyList.DcLst$<pos>|    ==> pos starts with 1
    <- $FileLength <len>| or $Error File Not Found|
    -> $Send|
    <- file content stream....
        (optional, not supported by DC++. Better + faster to just disconnect the link.)
        -> $Cancel    ==> note: without "|"
        <- some final bytes of stream....
        <- $Canceled

    if connecting client does not publish $support, then we assume it only support DcLst. Otherwise here's the order of get-protocol to use in case client support all type:
    1. Basic/no support: $Get, with "MyList.DcLst" for file list name
    2. BZlist: same as #1, but for list :
    2. XmlBZList: $UGetBlock
    3. XmlBZList + GetZBlock: $UGetZBlock
    4. ADCGet: $ADCGET
    5. ADCGet + ZLIG: $ADCGET file TTH/<base32 encoded TTH hash of the file> <pos> <size> ZL1|

    """
    RECV_NORMAL, RECV_RAW_FILE = 1, 2
    LIST_NAMES = ('MyList.DcLst', 'files.xml.bz2', 'MyList.bz2')
    STRICT_KEY = False  # set to true to disconnect client that reply with wrong key
    ZLIB_COMPRESSION_LEVEL = 9  # 1 (least) - 9 (most)

    def __init__(self, parent, sock, nick, list_path=None, share_dir=None, store_dir=None, upload=False):
        self.parent = parent
        self.sock = sock
        self.nick = nick
        self.mode_upload = upload

        # as uploader
        #~ if upload:
            #~ assert os.path.exists(share_dir), "I'm an uploader, but you have not defined the filesharing path, or [%s] not exists" % share_dir
            #~ assert os.path.exists(list_path), "I'm an uploader, but you have not defined the filelist name, or [%s] not exists" % list_path
        self.share_dir = share_dir  # path to share root in file system
        self.list_path = list_path  # path to xml file which contains the list of shares

        # as downloader
        #~ self.last_start_pos = None
        self.store_dir = ''  # directory to store downloaded file to
        self.transfer_state = None

        self.remote_nick = None
        self.is_loggedin = False
        self.lock_sent = False
        self.running = True
        self._lock_send = threading.RLock()
        self.remote_supports = () # MiniSlots XmlBZList ADCGet TTHL TTHF GetZBlock ZLIG

        self.supports = ['BZList', 'MiniSlots', 'XmlBZList', 'ADCGet', 'TTHF', 'GetZBlock', 'ZLIG']  # these almost supported: 'TTHL',

        # default DcLst protocol support
        self.download_list = self._default_download_list
        self.recv_mode = CCBaseWorker.RECV_NORMAL

    def parse_support(self):
        if 'ADCGet' in self.remote_supports:
            if 'ZLIG' in self.remote_supports:
                self.download_list = self._adcget_zlig_download_list
            else:
                self.download_list = self._adcget_download_list
        elif 'XmlBZList' in self.remote_supports:
            if 'GetZBlock' in self.remote_supports:
                self.download_list = self._zblock_download_list
            else:
                self.download_list = self._xmlbzlist_download_list
        elif 'BZList' in self.remote_supports:
            self.download_list = self._bzlist_download_list
        else:
            # use the default
            self.download_list = self._default_download_list

    def log(self, data):
        if __debug__:
            if type(data) == unicode:
                data = data.encode("utf-8")
            line = '%s [%s]<%s> %s' % (time.strftime("%X", time.localtime()), threading.currentThread().getName(), self.remote_nick is None and 'Unknown' or self.remote_nick, data)
            try:
                print line
            except UnicodeDecodeError, e:
                #~ print >> sys.stderr, "Cant print debug, error: %r. Full line: %r" % (e, line)
                print "Cant print debug, error: %r. Full line: %r" % (e, line)

    def run(self):
        self.send_mynick()
        self.send_lock()
        buffer = ''
        try:
            while self.running: # set this to False to disconnect
                if self.recv_mode == CCBaseWorker.RECV_NORMAL:
                    end_pos = buffer.find('|')
                    if end_pos == -1: # no delimited found, then the data is not complete, request again
                        data = self.sock.recv(NETWORK_READ_BLOCK)    # TODO: on raw data RECV, it is possible tht the raw data follows IMMEDIATELY to $ADSSND....|RAWDATAAAAA.
                        if not data:
                            self.log("Ending thread as socket has been disconnected.")
                            self.running = False
                            break
                        buffer += data
                        continue
                    #~ self.log("Buffer:[%s], EndPos:[%s], BuffPos[%s]" % (buffer, end_pos, buffer[:end_pos]))
                    self.process_command(buffer[:end_pos])
                    buffer = buffer[end_pos+1:]
                    #~ self.log("PostBuffer:[%s]" % (buffer))
                elif self.recv_mode == CCBaseWorker.RECV_RAW_FILE:
                    if self.transfer_state.remaining_size > 0:
                        data = self.sock.recv(min(NETWORK_READ_BLOCK,self.transfer_state.remaining_size))    # TODO: on raw data RECV, it is possible tht the raw data follows IMMEDIATELY to $ADSSND....|RAWDATAAAAA.
                        if not data:
                            self.log("Ending thread as socket has been disconnected.")
                            self.running = False
                            break
                        buffer += data
                        self.process_raw(buffer)
                        buffer = ''
                    else:
                        # all data has been received, notify and go back to normal mode
                        #~ self.log("Remaining size = [%s]. All data has been received, ending raw receive." % self.transfer_state.remaining_size)
                        self.process_raw_end()
        except socket.error, e:
            self.log("Socket error when receiving: %s, closing socket" % e)
        self.sock.close()
        #~ self.log("Thread finishes. Bye.")

    def process_raw_begin(self):
        #~ self.log("Begin downloading [%s] (%s)" % (self.transfer_state.file_path, self.transfer_state.size))
        self.transfer_state.remaining_size = int(self.transfer_state.size)
        self.recv_mode = CCBaseWorker.RECV_RAW_FILE
        if self.transfer_state.compressed:
            #~ self.log("Stream is compressed, decompressing on the fly...")
            self.transfer_state.compressed_obj = zlib.decompressobj()
            self.transfer_state.compressed_size = 0
        try:
            if self.transfer_state.start_pos == 0:
                self.transfer_state.local_file_fh = file(self.transfer_state.file_path, 'wb')
            else:
                if not (os.path.exists(self.transfer_state.file_path) and os.path.isfile(self.transfer_state.file_path)):
                    raise Exception, "Non zero starting pos assume continuing existing file"
                if pos > os.path.getsize(self.transfer_state.file_path):
                    raise Exception, "Non zero starting pos must not exceed file's edge"
                self.transfer_state.local_file_fh = file(self.transfer_state.file_path, 'r+b')
                self.transfer_state.local_file_fh.seek(self.transfer_state.start_pos)
        except IOError:
            print "IOError, file:%s" % self.transfer_state.file_path
            raise

    def process_raw(self, buffer):
        """Override this to provide other behaviour than saving file
        """
        if self.transfer_state.compressed:
            self.transfer_state.compressed_size += len(buffer)
            buffer = self.transfer_state.compressed_obj.decompress(buffer)
        self.transfer_state.local_file_fh.write(buffer)
        self.transfer_state.remaining_size -= len(buffer)

    def process_raw_end(self):
        if self.transfer_state.compressed:
            buffer = self.transfer_state.compressed_obj.flush()
            if buffer:
                self.transfer_state.local_file_fh.write(buffer)
                self.transfer_state.remaining_size -= len(buffer)
                self.log('Compressed state flushing: there is still [%s] byte remaining, decompressed and appended to file. Total remaining size: [%s]' % (len(buffer), self.transfer_state.remaining_size))
        self.transfer_state.local_file_fh.close()
        #~ self.log('Wrote %d byte to %s' % (self.transfer_state.size, self.transfer_state.file_path))
        if self.transfer_state.compressed:
            #~ self.log('Original compressed size is [%s] defalted to [%s], (%0.2f%%)' % (self.transfer_state.compressed_size, self.transfer_state.size, float(self.transfer_state.compressed_size * 100)/self.transfer_state.size))
            pass
        self.on_finished_download(self.transfer_state)
        self.recv_mode = CCBaseWorker.RECV_NORMAL

    def send_block(self, data):
        """send a block of data until all is really sent
        socket function is not wrapped in try/except in purpose, handle it in your calling routine
        """
        sent_left = len(data)
        pos = 0
        while sent_left > 0:
            sent = self.sock.send(data[pos:])
            sent_left -= sent
            pos += sent

    def send_rawsock_from_file(self):
        """memory efficient buffered read @source_path file, and buffered write socket
        Mode: Uploader
        """
        self.log("Sending file [%s]" % self.transfer_state.file_path)
        assert os.path.exists(self.transfer_state.file_path) and os.path.isfile(self.transfer_state.file_path)
        file_size = os.path.getsize(self.transfer_state.file_path)
        fh = file(self.transfer_state.file_path ,'rb')
        if self.transfer_state.compressed:
            compress_obj = zlib.compressobj(CCBaseWorker.ZLIB_COMPRESSION_LEVEL)
        try:
            assert self.transfer_state.start_pos <= file_size, "Non zero starting pos must not exceed file's edge"
            assert self.transfer_state.start_pos + self.transfer_state.size <= file_size, "Custom size must not exceed file size"
            fh.seek(self.transfer_state.start_pos)
            size_left = self.transfer_state.size

            # only 1 thread is allowed to send in a time
            self._lock_send.acquire()
            try:
                try:
                    while size_left > 0:
                        data = fh.read(READ_BLOCK)
                        if data == '': # file size has been troughly read...
                            break
                        size_left -= len(data)
                        if self.transfer_state.compressed:
                            data = compress_obj.compress(data)
                        self.send_block(data)

                    # check if there's remaining data in compress_obk
                    if self.transfer_state.compressed:
                        data = compress_obj.flush()
                        if data != '':
                            self.send_block(data)
                except socket.error, e:
                    self.log('Socket error [%s] when sending [%s] to [%s]' % (e, repr(data[:200]), repr(self.sock.getpeername())))
                    self.running = False
                    return

            finally:
                self._lock_send.release()
        finally:
            fh.close()

    def process_command(self, cmdline):
        #~ self.log("Got process data: [%s]" % cmdline)
        if cmdline == '':
            return
        elif cmdline[0] == '$':
            pos_space = cmdline.find(' ')
            if pos_space == -1:
                pos_space = None
            cmd = cmdline[1:pos_space].lower()
            if cmd == 'to:': # we want to wrap PM handler
                cmd = 'to_'
            attr_name = 'handle_' + cmd
            if hasattr(self, attr_name):
                if pos_space is None:
                    getattr(self, attr_name)()
                else:
                    getattr(self, attr_name)(cmdline[pos_space+1:])
                return
        self.handle_others(cmdline)

    def send(self, *args):
        # only 1 thread is allowed to send in a time
        self._lock_send.acquire()
        try:
            buffer = '|'.join([x.replace('|','&#124;') for x in args]) + '|'
            try:
                self.sock.send(buffer)
            except socket.error, e:
                self.log('Socket error [%s] when sending [%s] to [%s]. Disconnecting...' % (e, repr(buffer[:200]), repr(self.sock.getpeername())))
                self.running = False
                return
            #~ self.log('@Sent '+repr(buffer))
        finally:
            self._lock_send.release()

    def send_lock(self, data='EXTENDEDPROTOCOLABCABCABCABCABCABC Pk=DCPLUSPLUS0.674ABCABC'):
        # EXTENDEDPROTOCOL should not be sent if the hub/client supports no extensions -- http://www.dcpp.net/wiki/index.php/%24Supports
        if not self.supports and 'EXTENDEDPROTOCOL' in data:
            data = data.replace('EXTENDEDPROTOCOL','ABC')
        self.send('$Lock %s' % data)
        self.lock_sent = True
        self.lock_data = data

    def send_mynick(self):
        self.send('$MyNick %s' % self.nick)

    def send_supports(self):
        """
        This command is used to negotiate protocol extensions. To indicate that the client or hub has at least one protocol extension available,
        it must send a $Lock that begins with EXTENDEDPROTOCOL. If the remote side also supports protocol extensions, it may send $Support. It must, however, precede $Key.
        Notes:
            * EXTENDEDPROTOCOL should not be sent if the hub/client supports no extensions
            * A blank $Supports is not permitted
            * Spaces aren't allowed in feature names
            * For client extensions, the name of the feature should be the same as the command.
        Source: http://www.dcpp.net/wiki/index.php/%24Supports
        """
        if self.supports:   # A blank $Supports is not permitted
            self.send('$Supports %s' % ' '.join(self.supports))

    def send_direction(self):
        self.send('$Direction %s %d' % (self.mode_upload and 'Upload' or 'Download', random.randint(1000,99999)))

    def handle_lock(self, data):
        """Don't override this unless you know what you're doing"""
        #~ self.log("Handle lock [%s]." % data)
        self.send_direction()   # vital! must be sent before key
        self.send_supports()
        self.send('$Key %s' % lock2key(data))
        if not self.lock_sent:
            self.send_mynick()
            self.send_lock()

    def handle_mynick(self, data):
        #~ self.log("Handle $MyNick [%s]." % data)
        self.remote_nick = data

    def handle_key(self, data):
        """Dont override this unless you know what you are doing"""
        #~ self.log("Handle $Key [%s]." % repr(data))
        expected_key = lock2key(self.lock_data)
        if expected_key.startswith(data):
            if self.lock_sent:
                # this is a response of our lock request. consider login process finish
                self.is_loggedin = True
                self.on_loggedin()
            else:
                self.send_lock()
        elif CCBaseWorker.STRICT_KEY:   # invalid key, disconnect
            self.log("Invalid key accepted (expected: %s), disconnecting." % repr(expected_key))
            self.running = False

    def handle_direction(self, data):
        #~ self.log("Handle $Direction [%s]." % data)
        pass

    def handle_others(self, data):
        self.log("Handle Others: [%s]" % data)

    def handle_supports(self, data):
        #~ self.log("Handle $Supports: [%s]" % data)
        self.remote_supports = tuple(data.split(' '))
        self.parse_support()

    def on_loggedin(self):
        """Called when login successful"""
        pass

    def handle_error(self, data):
        self.log("Handle $Error [%s]." % data)
        self.log("Ending thread as got $Error")
        self.running = False

    def _default_download_list(self):
        """convinient function to download share list
        # Mode: Downloader
        """
        self.log("Sending request for list MyList.DcLst.")
        self.send_get('MyList.DcLst')

    def download(self, sharename):
        """convinient function to download a share
        prefix sharename with TTH: to download by tth
        """
        pass

    def on_finished_download(self, e):
        """call back function, when raw data has been received
        @e is DownloadFile object you can use to inquire detail of downloaded file
        """
        pass

    def get_path_for_download(self, share_name):
        if share_name in CCBaseWorker.LIST_NAMES:
            # add the nick :D
            path = os.path.join(self.store_dir, get_filesafe_name(self.remote_nick +'.' + os.path.basename(share_name)))
        else:
            path = os.path.join(self.store_dir, get_filesafe_name(os.path.basename(share_name)))
            # what if file already exists? for now, probably just overwrite it
        return path

    def get_path_for_upload(self, share_name):
        if share_name in CCBaseWorker.LIST_NAMES:
            # list is located on special path
            path = self.list_path
        else:
            # resolve this to real path
            if share_name.startswith('TTH/'):
                # resolve TTH to path, potentially returning None if it's not in self.parent.share_tth_db
                path = self.get_path_from_tth(share_name[4:])
            else:
                # note, share_name path is always separated by '\'. on unix we must convert this to forward slash
                if os.name == 'posix':
                    share_name = share_name.replace('\\', '/')
                path = os.path.join(self.share_dir, share_name)
        return path

    def get_path_from_tth(self, tth):
        return self.parent.share_tth_db.get(tth, None)

    """
    Basic DC $Get protocol handler
    """

    def send_get(self, share_name, start_pos=0, store_dir=None):
        """
        $Get <file>$<offset>|
        # Mode: Downloader

        Possible response:
        - $Error File Not Found
        - $FileLength <filesize>|
        """
        self.send('$Get %s$%d' % (share_name, start_pos+1)) # $Get's start pos start from 1
        if store_dir is not None:
            self.store_dir = store_dir   # by default, download placed at active dir
        size = -1   # size is unknown at the moment, will be known after on the next response
        self.transfer_state = TransferState(share_name, self.get_path_for_download(share_name), size, start_pos)

    def send_getlistlen(self):
        # Warning: this will request length of list in DcLst format, not XML!!
        # Mode: Downloader
        # see handle_listlen
        self.send('$GetListLen')

    def send_send(self):
        # Mode: Uploader
        self.send('$Send')
        self.log("Downloading %d byte file" % self.transfer_state.size)
        self.process_raw_begin()

    def handle_listlen(self, data):
        # Warning: length refer to list in DcLst format, not XML!!
        # Mode: Downloader
        self.log("Handle $ListLen [%s]." % data)

    def handle_filelength(self, data):
        """
        - $FileLength <filesize>|
        # Mode: Downloader
        Send as a response from $Get
        """
        self.log("Handle $FileLength [%s]." % data)
        self.transfer_state.size = int(data)
        self.send_send()    # after this message is send, remote will send the file in rawdata

    def handle_getlistlen(self):
        # Warning: length refer to list in DcLst format, not XML!!
        # Mode: Uploader
        self.log("Handle $GetListLen.")
        # i know i should return DcLst file list size, but i should foolishly just return what list_path size is
        path = self.list_path
        if path is not None and os.path.exists(path) and os.path.isfile(path):
            self.send('$ListLen %d' % os.path.getsize(path))
        else:
            # this is not really compliant to DC standard, but nevertheless, "proper" DC client should never have no file list :D
            self.send('$Error %s' % 'File Not Found')

    def handle_get(self, data):
        """
        $Get <file>$<offset>|
        <file> is the full pathname of the file as announced by the uploader through a $SR or through its FileList.
        <offset> is the starting point of the download (counted from 1, not from 0)
        http://dcpp.net/wiki/index.php/%24Get
        Mode: Downloader
        """
        self.log("Handle $Get [%s]." % data)
        share_name, start_pos = data.rsplit('$',1)
        start_pos = max(0, int(start_pos) - 1)  # since strangely it starts from 1, but in case non-standard client wants to start 0, we let them.
        path = self.get_path_for_upload(share_name)

        if path is not None and os.path.exists(path) and os.path.isfile(path):
            size = os.path.getsize(path)
            self.send('$FileLength %d' % size)
            self.transfer_state = TransferState(share_name, self.get_path_for_upload(share_name), size, start_pos)   # save the state
        else:
            self.send('$Error %s' % 'File Not Found')
            self.transfer_state = None

    def handle_send(self):
        # Mode: Uploader
        self.log("Handle $Send.")
        if self.transfer_state and self.transfer_state.file_path != None:
            self.send_rawsock_from_file()
        else:
            #
            pass

    def handle_maxedout(self):
        # Mode: Downloader
        self.log("Handle $MaxedOut.")
        # what should i do? wait, or just disconnect? let's disconnect
        self.running = False

    """
        XMLBzList support methods

    DCClientClient support for XmlBZList.
    For downloader, this replaces $Get and $Send

    Typical scenario:
    -> $UGetBlock <start> <numbytes> <filename utf8 encoded>|                   ==> numbytes = -1 for all
    <- $Sending <bytes>|<data stream....>    or    <- $Failed <description>|
    """


    def _handle_getblock(self, data, compressed=False):
        start_pos, size, share_name = data.split(' ', 2)
        start_pos = int(start_pos)
        size = int(size)
        path = self.get_path_for_upload(share_name)

        if path is not None and os.path.exists(path) and os.path.isfile(path):
            if size == -1:
                size = os.path.getsize(path)
            self.send('$Sending %d' % size)
            self.transfer_state = TransferState(share_name, path, size, start_pos, compressed=compressed)   # save the state
            self.send_rawsock_from_file()
        else:
            self.send('$Failed %s' % 'File Not Available')
            self.transfer_state = None


    def handle_ugetblock(self, data, compressed=False):
        """
        $UGetBlock <start> <numbytes> <filename utf8 encoded>|
        eg. $UGetBlock 0 -1 files.xml.bz2|
        http://dcpp.net/wiki/index.php/$UGetBlock
        start = [posivite_integer]
        numbytes = ["-1", posivite_integer]
            "-1" = all
        SUPPORT: XmlBZList
        MODE: uploader
        """
        self.log("Handle $UGetBlock: [%s]" % data)
        assert 'XmlBZList' in self.supports, "This handler only enabled if XmlBZList is supported"
        self._handle_getblock(data)

    handle_getblock = handle_ugetblock   # non-unicode version, same with getblock since python handle both transparently in this case

    def handle_sending(self, data):
        """
        handle this if you sent (u)getblock, (u)getzblock
        SUPPORT: XmlBZList
        MODE: downloader
        Syntax:
            $Sending <endByte - startByte>|
        If the requested bytes was -1 then:
            $Sending|
        http://dcpp.net/wiki/index.php/%24Sending
        """
        assert 'XmlBZList' in self.supports, "This handler only enabled if XmlBZList is supported"
        self.log("Handle $Sending [%s]." % data)
        if data != '':
            end_byte, start_byte = data.split(' - ')
            self.transfer_state.start_pos = start_byte
            self.transfer_state.size = end_byte - start_byte
        else:
            # normal all file data from 0
            self.transfer_state.start_pos = 0
        self.process_raw_begin()

    def handle_failed(self, message):
        """
        MODE: uploader
        """
        self.log("Handle $Failed [%s]." % message)
        pass

    def _send_getblock(self, share_name, start_pos=0, size=-1, store_dir=None, compressed=False):
        if store_dir is not None:
            self.store_dir = store_dir   # by default, download placed at active dir
        self.transfer_state = TransferState(share_name, self.get_path_for_download(share_name), size, start_pos, compressed=compressed)

    def send_ugetblock(self, share_name, start_pos=0, size=-1, store_dir=None):
        # mode: downloader
        self.send('$UGetBlock %d %d %s' % (start_pos, size, share_name))
        self._send_getblock(share_name, start_pos, size, store_dir)

    send_getblock = send_ugetblock


    def _xmlbzlist_download_list(self):
        """convinient function to download share list
        # mode: downloader
        """
        # Mode: Downloader
        assert 'XmlBZList' in self.supports, "This handler only enabled if XmlBZList is supported"
        assert self.remote_supports and 'XmlBZList' in self.remote_supports
        self.log("Sending request for list files.xml.bz2.")
        self.send_ugetblock('files.xml.bz2')


    """
    GetZBlock protocol support
    mixin class that gives DCClientClient support for GetZBlock command. Requires XmlBZList support.
    For downloader, this optionally replaces $UGetBlock and $GetBlock

    Typical scenario:
    -> $UGetZBlock <start> <numbytes> <filename utf8 encoded>|                   ==> numbytes = -1 for all, start|numbytes refer to uncompressed data
    <- $Sending <bytes>|<zlib compressed data stream....>    or    <- $Failed <description>|
    """

    def send_ugetzblock(self, share_name, start_pos=0, size=-1, store_dir=None):
        # mode: downloader
        self.send('$UGetZBlock %d %d %s' % (start_pos, size, share_name))

        self._send_getblock(share_name, start_pos, size, store_dir, compressed=True)

    send_getzblock = send_ugetzblock


    def _zblock_download_list(self):
        """convinient function to download share list
        """
        # Mode: Downloader
        assert 'XmlBZList' in self.supports and 'GetZBlock' in self.supports, "This handler only enabled if XmlBZList is supported"
        assert self.remote_supports and 'XmlBZList' in self.remote_supports  and 'GetZBlock' in self.remote_supports
        #~ self.send_ugetzblock('files.xml.bz2')
        self.send_ugetblock('files.xml.bz2')    # coz file list already compressed

    def handle_ugetzblock(self, data):
        """
        similar to $UGetBlock but with compression
        note: all reference to start and numbytes refer to when file is non-compressed
        SUPPORT: GetZBlock + XmlBZList
        MODE: uploader
        """
        assert 'XmlBZList' in self.supports and 'GetZBlock' in self.supports
        self.log("Handle $UGetZBlock: [%s]" % data)
        self._handle_getblock(data, compressed=True)

    handle_getzblock = handle_ugetzblock

    """
    BZList protocol support
    mixin class that gives DCClientClient support for basic MyList filelist but compressed in bz2
    """
    def _bzlist_download_list(self):
        """convinient function to download share list
        """
        # Mode: Downloader
        assert 'BZList' in self.supports, "This handler only enabled if BZList is supported"
        assert self.remote_supports and 'BZList' in self.remote_supports
        self.log("Sending request for MyList.bz2.")
        self.send_get('MyList.bz2')

    """
    MiniSlots protocol support
    mixin class that modified DCClientClient so that small files and file list will use special slot (called "mini-slot") thus does not consume nor need main slot
    """
    # TODO

    """
    ADCGet protocol support with TTHF, ZLIG, and TTHL

    mixin class that add $ADCGet support.
    replaces $Get, $GetBlock, $UGetBlock, $GeZtBlock, $UGetZBlock, $Send, and $Sending.
    TTHL indicates support of TTH leaf, which is the intermediate

    Typical scenario :

    (file list)
    -> $ADCGET file files.xml.bz2 <pos> <size>|                     ==> size = -1, all
    <- $ADCSND file files.xml.bz2 <pos> <size>|<data stream...>

    (normal file w/ TTHF supports)
    -> $ADCGET file TTH/<base32 encoded TTH hash of the file> <pos> <size>|
    <- $ADCSND file TTH/<base32 encoded TTH hash of the file> <pos> <size>|<data stream...>

    (normal file w/ TTHF and ZLIG supports)
    -> $ADCGET file TTH/<base32 encoded TTH hash of the file> <pos> <size> ZL1|
    <- $ADCSND file TTH/<base32 encoded TTH hash of the file> <pos> <size> ZL1|<data stream...>

    References:
    - http://dcpp.net/wiki/index.php/%24ADCGET
    - http://dcplusplus.sourceforge.net/ADC.html
    """

    def send_adcget(self, share_name, pos=0, size=-1, tth_leaf=False, compressed=False):
        # ADCGET does not have to store last requested name/pos/size/etc because the reply (ADCSND) has all of the data
        assert 'ADCGet' in self.supports, "This handler only enabled if ADCGet is supported"
        if tth_leaf:
            assert 'TTHL' in self.supports and 'TTHL' in self.remote_supports, "TTH leaf only enabled if both parties support TTHL"
        if share_name.startswith('TTH/'):
            assert 'TTHF' in self.supports, "TTH only enabled if TTHF is supported"
        if compressed:
            assert 'ZLIG' in self.supports and 'ZLIG' in self.remote_supports
            self.send('$ADCGET %s %s %d %d ZL1' % (tth_leaf and 'tthl' or 'file', share_name, pos, size))
        else:
            self.send('$ADCGET %s %s %d %d' % (tth_leaf and 'tthl' or 'file', share_name, pos, size))

    def send_adcsnd(self, share_name, pos=0, size=-1, tth_leaf=False, compressed=False):
        assert 'ADCGet' in self.supports, "This handler only enabled if ADCGet is supported"
        if tth_leaf:
            assert 'TTHL' in self.supports and 'TTHL' in self.remote_supports, "TTH leaf only enabled if both parties support TTHL"
        if share_name.startswith('TTH/'):
            assert 'TTHF' in self.supports and 'TTHL' in self.remote_supports, "Request using TTH only enabled if both parties support TTHF"
        if compressed:
            assert 'ZLIG' in self.supports and 'ZLIG' in self.remote_supports
            self.send('$ADCSND %s %s %d %d ZL1' % (tth_leaf and 'tthl' or 'file', share_name, pos, size))
        else:
            self.send('$ADCSND %s %s %d %d' % (tth_leaf and 'tthl' or 'file', share_name, pos, size))


    def handle_adcget(self, data):
        # mode: Uploader
        assert 'ADCGet' in self.supports, "This handler only enabled if ADCGet is supported"
        #~ self.log("Handle $ADCGET: [%s]" % data)
        if data.endswith('ZL1'):
            compressed = True
            data_type, share_name, start_pos, size, dummy = data.split(' ') # data_type=["file"|"tthl"]
        else:
            compressed = False
            data_type, share_name, start_pos, size = data.split(' ') # data_type=["file"|"tthl"]
        start_pos, size = int(start_pos), int(size)

        path = self.get_path_for_upload(share_name)
        if path is not None and os.path.exists(path) and os.path.isfile(path):
            if size == -1:
                size = os.path.getsize(path)
            # optionally we can force compressed state to False if we so desire, but at the moment, we just follow what the request ask for
            self.transfer_state = TransferState(share_name, path, size, start_pos, compressed=compressed)   # save the state
            self.send_adcsnd(share_name, start_pos, size, compressed=compressed)
            self.send_rawsock_from_file()
        else:
            self.send('$Error %s' % 'File Not Available')
            self.transfer_state = None

    def handle_adcsnd(self, data):
        # mode: Downloader
        assert 'ADCGet' in self.supports, "This handler only enabled if ADCGet is supported"
        #~ self.log("Handle $ADCSND: [%s]" % data)
        if data.endswith('ZL1'):
            compressed = True
            data_type, share_name, start_pos, size, dummy = data.split(' ') # data_type=["file"|"tthl"]
        else:
            compressed = False
            # data => [file files.xml.bz2 0 776072]
            data_type, share_name, start_pos, size = data.split(' ') # data_type=["file"|"tthl"]
        start_pos, size = int(start_pos), int(size)
        if data_type == 'tthl':
            assert 'TTHL' in self.supports and 'TTHL' in self.remote_supports
            # TODO: handle the TTH leafs database, and use it to verify download
        path = self.get_path_for_download(share_name)
        self.transfer_state = TransferState(share_name, path, size, start_pos, compressed=compressed)
        self.process_raw_begin()

    def _adcget_download_list(self):
        self.send_adcget('files.xml.bz2')

    def _adcget_zlig_download_list(self):
        # since bz2 file already compressed, lets not ask compressed version anymore
        self.send_adcget('files.xml.bz2', compressed=False)

    def download_tthl(self, share_name):
        self.log('Getting TTH leaf of %s' % share_name)
        self.send_adcget(share_name, tth_leaf=True)

DCClientClient = CCBaseWorker
