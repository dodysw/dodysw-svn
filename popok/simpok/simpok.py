#-----------------------------------------------------------------------------
# Name:        simpopok
# Purpose:     simple popok: echo pop3/smtp for testing mail sending
#
# Author:      dody suria wijaya
#
# Copyright:   (c) 2004
# Licence:     GNU
#-----------------------------------------------------------------------------

__version__ = '0.16'

__description__ = 'webmail to pop3 gateway'

__whatsnew__ = """

"""
import SocketServer, sys, string, threading, email
import time,string,socket

debug = True
__version__ = '0.13'

import re

inbox = []

class SMTPRequestHandler(SocketServer.StreamRequestHandler):
    sys_version = "Python/" + sys.version.split()[0]
    server_version = "popokSMTP/"
    requestline = ''

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
            print 'Request',requestline
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
        self.send_response(250,'-localhost')
        self.send_response(250,'AUTH PLAIN')

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
        inbox.append(buffer)
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
        self.wfile.write("%d %s\r\n" % (code, message))

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
            print requestline
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

        self.state = self.TRANSACTION
        self.send_ok("Password accepted")

    def do_STAT(self):
        if self.state != self.TRANSACTION:
            self.send_error("Unknown state command")
            return
        msg_count = len(inbox)
        if type(msg_count) == int and msg_count >= 0:
            msg_size = sum(map(len,inbox))
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
            if msgno > len(inbox)-1:
                self.send_error(resp)
                return
        self.send_ok("unique-id listing follows")
        body = []
        for i,email in enumerate(inbox):
            body.append("%d %d" % ((i+1),hash(email)))
        body.append(".")
        self.send_body('\r\n'.join(body))

    def do_DELE(self,msg_no):
        if self.state!=self.TRANSACTION:
            self.send_error("Unknown state command")
            return
        try:
            msg_no = int(msg_no)-1
        except ValueError,e:
            self.send_error("Not a number")
            return
        try:
            del inbox[msg_no]
            self.send_ok("Message deleted")
        except IndexError:
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
        try:
            inbox[msg_no]
            self.send_ok("Top of the message follows")
            body = inbox[msg_no][0:line]
            self.send_body(body)
        except IndexError:
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
        try:
            inbox[msg_no]
            self.send_ok("Sending message")
            body = inbox[msg_no]
            body = body.replace("\r\n.\r\n","\r\n..\r\n").replace("\r\n.\r\n","\r\n..\r\n")
            self.send_body(body + '\r\n.')
        except IndexError:
            self.send_error('Error in RETR')

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
            if msg_no > (len(inbox)-1):
                self.send_error(resp)
                return
        self.send_ok("scan listing begins")
        body = []
        for i,email in enumerate(inbox):
            body.append("%d %d" % ((i+1),len(email)))
        body.append(".")
        self.send_body('\r\n'.join(body))

    def do_QUIT(self):
        if self.state==self.TRANSACTION:
            pass
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
        if debug: print msg,
        self.wfile.write(msg)

    def send_body(self, message):
        """Send the response body
        """
        msg = "%s\r\n" % message
        if debug: print msg,
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


class pop_listener(threading.Thread):
    def run(self):
        popd = SocketServer.ThreadingTCPServer((options.address,options.pop3port),POPRequestHandler)
        sa = popd.socket.getsockname()
        print "Serving POP on", sa[0], "port", sa[1], "..."
        popd.serve_forever()

class smtp_listener(threading.Thread):
    def run(self):
        smtpd = SocketServer.ThreadingTCPServer((options.address,options.smtpport),SMTPRequestHandler)
        sa = smtpd.socket.getsockname()
        print "Serving SMTP on", sa[0], "port", sa[1], "..."
        smtpd.serve_forever()

if __name__ == '__main__':

    print "simpok v%s Copyright 2004 dsw s/h\r\ndsw.gesit.com|Price:Rp 5000/user|Contact: dodysw@gmail.com" % __version__
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("-g", "--guide", action="store_true", dest="guide", help="show guide to installation", default=False)
    parser.add_option("-v", "--verbose", action="store_true", dest="verbose", help="echo what popok is doing to stdout", default=False)
    parser.add_option("--pop3port", type="int", dest="pop3port", help="bind pop3 gateway to port PORT (default:110)", default=110,metavar="PORT")
    parser.add_option("--smtpport", type="int", dest="smtpport", help="bind smtp gateway to port PORT (default:25)", default=25,metavar="PORT")
    parser.add_option("--smtpdisable", action="store_true",dest="smtpdisable", help="disable smtp gateway", default=False)
    parser.add_option("--ip", dest="address", help="bind pop3/smtp gateway to this ip address (default: 0.0.0.0 -> all ip address of this machine)", default='0.0.0.0')

    options, args = parser.parse_args()
    if options.guide:
        #~ parser.print_help()
        print "==What's New==", __whatsnew__
        print "==Usage Guide==", __usage__
        sys.exit(0)
    debug = options.verbose


    if 'linux' in sys.platform:
        import os
        pid = os.getpid()
        file('/var/run/popokd.pid','w').write(pid)

    threads = []

    listener = pop_listener()
    listener.start()
    threads.append(listener)

    if not options.smtpdisable:
        listener = smtp_listener()
        listener.start()
        threads.append(listener)

    for thread in threads:
        thread.join()
    if debug: print "Main end of program"