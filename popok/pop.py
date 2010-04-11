import threading,SocketServer,sys,time,string,socket
import web

__version__ = '0.9'

debug = False

class POPRequestHandler(SocketServer.StreamRequestHandler):
    sys_version = "Python/" + sys.version.split()[0]
    server_version = "popokSMTP/" + __version__

    AUTHORIZATION_USER=0
    AUTHORIZATION_PASS=1
    TRANSACTION=2

    # Negative numbers count a number of optional parameters
    params_count = {"USER":1,"PASS":1,"CAPA":0,"STAT":0,"QUIT":0,"DELE":1,"NOOP":0,"RETR":1,"TOP":2,"LIST":-1,"UIDL":-1}

    def parse_request(self):
        """Parse a request (internal).
        The request should be stored in self.raw_requestline; the results
        are in self.command, and self.params
        Return True for success, False for failure; on failure, an
        error is sent back.
        """
        self.command = None  # set in case of error on the first line
        requestline = self.raw_requestline.strip()
        if debug:
            print requestline.__repr__()
        self.requestline = requestline
        if self.requestline == '':
            self.send_error('Null command')
            return False
        words = map(string.upper,requestline.split())
        command = words[0]
        params = []
        if len(words)>1:
            params = words[1:]
        try:
            if self.params_count[command] < 0:
                if len(params) > -self.params_count[command]:
                    self.send_error("Invalid number of arguments: %s %s (should be %s)" % (command,params,self.params_count[command]))
                    #~ print 1
                    return False
            elif len(params) != self.params_count[command]:
                self.send_error("Invalid number of arguments: %s %s (should be %s)" % (command,params,self.params_count[command]))
                #~ print 2
                return False
            #~ print 3
        except KeyError:
            self.send_error("Unknown command")
            #~ print 4
            return False

        self.command,self.params = command,params
        return True

    def handle_one_request(self):
        self.raw_requestline = self.rfile.readline()
        if not self.raw_requestline:
            self.close_connection = True
            return
        if not self.parse_request(): # An error code has been sent, just skip to next line
            return
        mname = 'do_' + self.command
        if not hasattr(self, mname):
            self.send_error("Command unrecognized: %s" % `self.command`)
            return
        method = getattr(self, mname)
        method(*self.params)

    def handle(self):
        """Handle multiple requests if necessary."""
        self.close_connection = False
        self.send_ok("popok %s, Assalamualaikum" % __version__)
        self.state = self.AUTHORIZATION_USER
        while not self.close_connection:
            self.handle_one_request()

    def do_USER(self,username):
        if self.state not in (self.AUTHORIZATION_USER,self.AUTHORIZATION_PASS):
            self.send_error("Unknown state command")
            return
        self.username=username
        self.state=self.AUTHORIZATION_PASS
        self.send_ok("User name accepted, password please")

    def do_PASS(self,password):
        if debug: print "PASS"
        if self.state != self.AUTHORIZATION_PASS:
            self.send_error("Unknown state command")
            return
        if self.username == 'DETIKCOM':
            import web_detik as web
        else:
            import web
        self.webclient = web.HTTPMaildrop(self.username)
        resp = self.webclient.login(self.username,password)
        if resp == 1:
            self.state = self.TRANSACTION
            self.send_ok("Password accepted")
        elif resp == -1:
            self.send_error("Invalid user/password")
        else:
            self.send_error(resp)
            self.close_connection = True

    def do_STAT(self):
        if self.state != self.TRANSACTION:
            self.send_error("Unknown state command")
            return
        msg_count = self.webclient.get_msg_count()
        if type(msg_count) == int and msg_count >= 0:
            msg_size = self.webclient.get_msg_size_total()
            self.send_ok("%d %d" % (msg_count,msg_size))
        else:
            self.send_error(msg_count)

    def do_UIDL(self, msgno=None):
        """implementation note:
        UIDL return unique ID copied from each email's "filename" url"""
        if self.state != self.TRANSACTION:
            self.send_error("Unknown state command")
            return
        if msgno != None:
            try:
                msgno = int(msgno)-1
            except ValueError,e:
                self.send_error("Not a number")
                return
            resp = self.webclient.msg_exists(msgno)
            if resp != 1:
                self.send_error(resp)
                return
        self.send_ok("unique-id listing follows")
        body = self.webclient.send_uidl_list(msgno)
        self.send_body(body)

    def do_DELE(self,msg_no):
        if self.state!=self.TRANSACTION:
            self.send_error("Unknown state command")
            return
        try:
            msg_no = int(msg_no)-1
        except ValueError,e:
            self.send_error("Not a number")
            return
        resp = self.webclient.delete_msg(msg_no)
        if resp == 1:
            self.send_ok("Message deleted")
        else:
            self.send_error(resp)

    def do_TOP(self,msg_no,line):
        if self.state != self.TRANSACTION:
            self.send_error("Unknown state command")
            return
        try:
            msg_no = int(msg_no)-1
        except ValueError,e:
            self.send_error("Not a number")
            return

        resp = self.webclient.msg_exists(msg_no)
        if resp == 1:
            self.send_ok("Top of the message follows")
            body = self.webclient.send_msg(msg_no,line)
            self.send_body(body)
        else:
            self.send_error(resp)

    def do_RETR(self,msg_no):
        if self.state != self.TRANSACTION:
            self.send_error("Unknown state command")
            return
        try:
            msg_no = int(msg_no)-1
        except ValueError,e:
            self.send_error("Not a number")
            return
        resp = self.webclient.msg_exists(msg_no)
        if resp == 1:
            self.send_ok("Sending message")
            body = self.webclient.send_msg(msg_no)
            self.send_body(body)
        else:
            self.send_error(resp)

    def do_NOOP(self):
        if self.state != self.TRANSACTION:
            self.send_error("Unknown state command")
            return
        self.send_ok("Still here...")

    def do_LIST(self,msgno=None):
        if self.state != self.TRANSACTION:
            self.send_error("Unknown state command")
            return
        if msgno != None:
            try:
                msgno = int(msgno)-1
            except ValueError:
                self.send_error("Not a number")
                return
            if self.webclient.msg_exists(msgno) != 1:
                self.send_error(resp)
                return
        self.send_ok("scan listing begins")
        body = self.webclient.send_list(msgno)
        self.send_body(body)

    def do_QUIT(self):
        if self.state==self.TRANSACTION:
            self.webclient.delete_msg_commit()
        self.send_ok("Wassalamualaikum")
        self.close_connection = True

    def do_CAPA(self):
        self.send_ok("Capability list follows:")
        caps = ['TOP','UIDL','USER','.']
        self.send_body('\r\n'.join(caps))

    def send_error(self, message=''):
        self.log_error(message)
        self.wfile.write("+ERR %s\r\n" % message)

    def send_ok(self, message=''):
        """Send the response header and log the response code.
        """
        msg = "+OK %s\r\n" % message
        if debug: print msg.__repr__(),
        self.wfile.write(msg)

    def send_body(self, message):
        """Send the response body
        """
        msg = "%s\r\n" % message
        if debug: print msg.__repr__(),
        self.wfile.write(msg)

    def log_request(self, message):
        self.log_message('"%s" %s %s', self.requestline, str(code), str(size))

    def log_error(self, *args):
        self.log_message(*args)

    def log_message(self, format, *args):
        #sys.stderr.write("%s - - [%s] %s\n" % (self.address_string(), self.log_date_time_string(), format%args))
        pass

    def version_string(self):
        """Return the server software version string
        """
        return self.server_version + ' ' + self.sys_version

    def date_time_string(self):
        """Return the current date and time formatted for a message header."""
        now = time.time()
        year, month, day, hh, mm, ss, wd, y, z = time.gmtime(now)
        s = "%s, %02d %3s %4d %02d:%02d:%02d GMT" % (
                self.weekdayname[wd],
                day, self.monthname[month], year,
                hh, mm, ss)
        return s

    def log_date_time_string(self):
        """Return the current time formatted for logging."""
        now = time.time()
        year, month, day, hh, mm, ss, x, y, z = time.localtime(now)
        s = "%02d/%3s/%04d %02d:%02d:%02d" % (
                day, self.monthname[month], year, hh, mm, ss)
        return s

    weekdayname = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
    monthname = [None,'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun','Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

    def address_string(self):
        """Return the client address formatted for logging.
        This version looks up the full hostname using gethostbyaddr(),
        and tries to find a name that contains at least one dot.
        """
        host, port = self.client_address[:2]
        return socket.getfqdn(host)
