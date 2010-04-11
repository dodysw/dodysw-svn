#-----------------------------------------------------------------------------
# Name:        popok
# Purpose:     webmail to pop3 gateway
#
# Author:      dody suria wijaya
#
# Copyright:   (c) 2004
# Licence:     GNU
#-----------------------------------------------------------------------------

__version__ = "0.12"

__whatsnew__ = """
Version 0.12:
* Tested email client:
- The Bat 2.11 (note: increase "Server Timeout" to 300 sec)
- Mozilla Thunderbird 0.7
- Microsoft Outlook 2002 (note: increase "Server Timeout" to "Long")
- Microsoft Outlook Express 6.0 (note: increase "Server Timeout" to "Long")
* Known bugs:
- plasa/telkom quota exceeded: popok will not be able to confirm (by pressing the button).
  you must login, and do it manually.
"""

__usage__ = """
* Detikcom Module:
- pop3 server: localhost (110)
- pop3 user: detikcom
- pop3 pass: detikcom
- set/tick "leave mail on server" = Yes -> this will avoid duplicate news
* Communigate Pro Webmail (webmail.plasa.com/mail.telkom.net):
- pop3 server: localhost (110)
- pop3 user: useremail@plasa.com or useremail@telkom.net
- pop3 pass: <your email password>
- smtp server: localhost (25) (you must login to pop3 first, before smtp-ing)
* Note: On all email client, set "Server Timeout" configuration to at least 5 minutes!
"""

import smtp,pop,web,SocketServer
import sys, string, threading, email

debug = False

class pop_listener(threading.Thread):
    def run(self):
        popd = SocketServer.ThreadingTCPServer((options.address,options.pop3port),pop.POPRequestHandler)
        sa = popd.socket.getsockname()
        print "Serving POP on", sa[0], "port", sa[1], "..."
        popd.serve_forever()

class smtp_listener(threading.Thread):
    def run(self):
        smtpd = SocketServer.ThreadingTCPServer((options.address,options.smtpport),smtp.SMTPRequestHandler)
        sa = smtpd.socket.getsockname()
        print "Serving SMTP on", sa[0], "port", sa[1], "..."
        smtpd.serve_forever()

if __name__ == '__main__':

    print "popok v%s Copyright 2003,2004 dsw s/h\r\ndsw.gesit.com|Price:Rp 5000/user|Contact: dodysw@gmail.com" % __version__
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    #~ parser.add_option(help=optparse.SUPPRESS_HELP)
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
    debug = web.debug = pop.debug = smtp.debug = options.verbose


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