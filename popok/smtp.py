import threading,SocketServer,sys,time,web,socket,string,re

debug = False

class SMTPRequestHandler(SocketServer.StreamRequestHandler):
    sys_version = "Python/" + sys.version.split()[0]
    server_version = "popokSMTP/"
    requestline = ''

    #~ def __init__(self):
        #~ # init variables
        #~ self.rcptto_emails = []
        #~ self.mailfrom_email = ''

    def parse_request(self):
        """Parse a request (internal).
        The request should be stored in self.raw_requestline; the results
        are in self.command, and self.params
        Return True for success, False for failure; on failure, an
        error is sent back.
        """
        self.command = None  # set in case of error on the first line
        requestline = self.raw_requestline
        if debug:
            print 'Request:',requestline.__repr__()
        if requestline[-2:] == '\r\n':
            requestline = requestline[:-2]
        elif requestline[-1:] == '\n':
            requestline = requestline[:-1]
        self.requestline = requestline
        if self.requestline == '':
            self.send_error(500, "Command unrecognized: Empty")
            return False
        words = map(string.upper,requestline.split())
        command = words[0]  # we would only interested the first word (esp. RPCT TO, MAIL FROM)
        params = []
        if len(words)>1:
            params = words[1:]
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
            self.send_error(500, "Command unrecognized: %s" % `self.command`)
            return
        method = getattr(self, mname)
        method()

    def handle(self):
        """Handle multiple requests if necessary.
        started the first time
        """
        # init variables
        self.close_connection = False
        self.rcptto_emails = []
        self.mailfrom_email = ''
        self.send_response(220, "popok, Assalamualaikum")
        while not self.close_connection:
            self.handle_one_request()

    def do_HELO(self):
        self.send_response(250,"Welcome %s" % self.address_string())
        #~ self.send_response(250,'-localhost')
        #~ self.send_response(250,'AUTH PLAIN')

    def do_MAIL(self):
        #ignored, parse from mail header later
        m = re.search("<([^>]*)",self.requestline)
        if not m:
            self.send_error(500,'From email address not found')
            return
        self.mailfrom_email = m.group(1)
        self.send_response(250)

    def do_RCPT(self):
        #ignored, parse from mail header later
        #now not ignored, since Bcc: emails insert email here, not at letter header
        m = re.search("<([^>]*)",self.requestline)
        if not m:
            self.send_error(500,'To email address not found')
            return
        self.rcptto_emails.append(m.group(1))
        self.send_response(250)

    def do_NOOP(self):
        self.send_response(250)

    def do_RSET(self):
        """reset rcpt to list
        """
        #ignored, parse from mail header later
        self.rcptto_emails = []
        self.send_response(250)

    def do_DATA(self):
        self.send_response(354)
        buffer = ""
        while 1:
            line = self.rfile.readline()
            buffer = buffer + line
            if line == '.\r\n':
                break
        buffer = buffer[:-3]
        #start posting to HTTP
        webclient = web.HTTPMaildrop(self.mailfrom_email)
        webclient.rcptto_emails = self.rcptto_emails  #notify webclient instance, so they can get an idea which emails is the Bcc
        ret = webclient.compose(buffer)
        if ret is not None:
            self.send_response(*ret)
        else:
            self.send_response(250)

    def do_QUIT(self):
        self.send_response(221)
        self.close_connection = True

    def send_error(self, code, message=None):
        """Send and log an error reply.
        Arguments are the error code, and a detailed message.
        The detailed message defaults to the short entry matching the
        response code.
        This sends an error response (so it must be called before any
        output has been generated), logs the error, and finally sends
        a piece of HTML explaining the error to the user.
        """

        try:
            short = self.responses[code]
        except KeyError:
            short = '???'
        if message is None:
            message = short

        self.log_error("code %d, message %s", code, message)
        self.send_response(code, message)

    def send_response(self, code, message=None):
        """Send the response header and log the response code.
        """
        self.log_request(code)
        if message is None:
            if code in self.responses:
                message = self.responses[code]
            else:
                message = ''
        line = "%d %s\r\n" % (code, message)
        if debug: print line
        self.wfile.write(line)

    def log_request(self, code='-', size='-'):
        self.log_message('"%s" %s %s', self.requestline, str(code), str(size))

    def log_error(self, *args):
        self.log_message(*args)

    def log_message(self, format, *args):
        #sys.stderr.write("%s - - [%s] %s\n" % (self.address_string(), self.log_date_time_string(), format%args))
        pass

    def version_string(self):
        """Return the server software version string."""
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

    responses = {
        220: ('Popok, Assalamualaikum'),
        221: ('Wassalamualaikum'),
        250: ('OK'),
        354: ('Start mail input; end with <CRLF>.<CRLF>'),
        500: ('Command unrecognized'),
        }
