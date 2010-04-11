#-----------------------------------------------------------------------------
# Name:        popok
# Purpose:     webmail to pop3 gateway
#
# Author:      dody suria wijaya
#
# Copyright:   (c) 2004
# Licence:     GNU
#-----------------------------------------------------------------------------

__version__ = "0.10"

import smtp,pop,web,SocketServer,socket,time
import sys, string, threading, email

import win32serviceutil
import win32service
import win32event
import win32evtlogutil

class dummy: pass
options = dummy()
options.address = ''
options.pop3port = 110
options.smtpport = 25
options.smtpdisable = False
options.verbose = False
threads = []

class popoksvc(win32serviceutil.ServiceFramework):
    _svc_name_ = "popoksvc"
    _svc_display_name_ = "Popok"
    _svc_deps_ = ["EventLog"]
    def __init__(self, args):
        win32serviceutil.ServiceFramework.__init__(self, args)
        self.hWaitStop = win32event.CreateEvent(None, 0, 0, None)

    def SvcStop(self):
        self.ReportServiceStatus(win32service.SERVICE_STOP_PENDING)
        stop()
        win32event.SetEvent(self.hWaitStop)

    def SvcDoRun(self):
        import servicemanager
        # Write a 'started' event to the event log...
        win32evtlogutil.ReportEvent(self._svc_name_,
                                    servicemanager.PYS_SERVICE_STARTED,
                                    0, # category
                                    servicemanager.EVENTLOG_INFORMATION_TYPE,
                                    (self._svc_name_, ''))
        start()

        # wait for beeing stopped...
        win32event.WaitForSingleObject(self.hWaitStop, win32event.INFINITE)

        # and write a 'stopped' event to the event log.
        win32evtlogutil.ReportEvent(self._svc_name_,
                                    servicemanager.PYS_SERVICE_STOPPED,
                                    0, # category
                                    servicemanager.EVENTLOG_INFORMATION_TYPE,
                                    (self._svc_name_, ''))

class MyThreadingSocketTCPServer(SocketServer.ThreadingTCPServer):
    """overrides to enable stopping
    """
    def server_activate(self):
        self.still_serving = True
        SocketServer.ThreadingTCPServer.server_activate(self)
    def stop(self):
        self.still_serving = False
    def serve_forever_until_stopped(self):
        """Handle one request at a time until stopped."""
        while self.still_serving:
            self.handle_request()

class pop_listener(threading.Thread):
    def run(self):
        self.listener = MyThreadingSocketTCPServer((options.address,options.pop3port),pop.POPRequestHandler)
        self.listener.serve_forever_until_stopped()

class smtp_listener(threading.Thread):
    def run(self):
        self.listener = MyThreadingSocketTCPServer((options.address,options.smtpport),smtp.SMTPRequestHandler)
        self.listener.serve_forever_until_stopped()

def start():
    debug = web.debug = pop.debug = smtp.debug = options.verbose
    listener = pop_listener()
    listener.setName('poplistener')
    listener.start()
    threads.append(listener)
    if not options.smtpdisable:
        listener = smtp_listener()
        listener.setName('smtplistener')
        listener.start()
        threads.append(listener)

def stop():
    for t in threads:
        t.listener.stop()
    #send fake connection to force 'continue' on serve_forever_until_stopped loop
    for port in (options.pop3port,options.smtpport):
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.connect(('localhost', port))
        s.send('quit\r\n')
        s.recv(1024)
        s.close()
        s = None
    for t in threads:
        t.join()

if __name__ == '__main__':
    print "popok v%s Copyright 2003,2004 dsw s/h\r\ndsw.gesit.com|Price:Rp 5000/user|Contact: dswsh@plasa.com" % __version__
    if len(sys.argv) > 1 and sys.argv[1] == 'console_start':
        start()
    else:
        win32serviceutil.HandleCommandLine(popoksvc)