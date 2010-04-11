#!/usr/bin/python -O
"""
pysda - Starnet Data Accounting client
Copyright 2005, Dody Suria Wijaya <dodysw@gmail.com>

converted to python from Net::Starnet::DataAccounting Perl Module by Iain Truskett <spoon@cpan.org> http://eh.org/~koschei/
"""
import sys, socket, time, encodings.utf_8
__version__ = '1.0.0'
SDA_HOST = '150.203.115.14';
SDA_PORT = 8000;
SDA_LOGIN, SDA_LOGOUT, SDA_UPDATE = 1,2,3
SDA_LOGIN_NO, SDA_LOGIN_YES  = 0, 1
SDA_LOGIN_INCORRECT_USERPASS, SDA_LOGIN_NO_QUOTA, SDA_LOGIN_ALREADY_CONNECTED = 1, 3, 4
SDA_UPDATE_NO, SDA_UPDATE_YES  = 0, 1
SDA_UPDATE_TIME = 6

class SdaClient:
    client = "pySdaClient %s" % __version__
    sda_host = SDA_HOST
    sda_port = SDA_PORT
    def __init__(self, username, password, client_host=None, sda_host=None, sda_port=None):
        assert(password != '')
        assert(username!= '')
        self.username = username
        self.password = password
        self.client_host = None
        self.stat_quota = self.stat_used = 0
        self.state_logged_in = False
        if client_host:
            self.client_host = client_host
        if sda_host:
            self.sda_host = sda_host
        if sda_port:
            self.sda_port = sda_port

    def login(self):
        if __debug__: print 'Logging in'
        response = self.sda_send(SDA_LOGIN)
        type, success, code, msg = response.split(' ',3)
        if int(success) == SDA_LOGIN_YES or int(code) == SDA_LOGIN_ALREADY_CONNECTED:
            if int(success) == SDA_LOGIN_YES:
                self.stat_quota = float(msg.split(' ')[2])
            self.state_logged_in = True
            return True
        return False

    def logout(self):
        if __debug__: print 'Logging out'
        response = self.sda_send(SDA_LOGOUT)
        type, success, quota, msg = response.split(' ',3)
        if int(success) == SDA_LOGIN_YES:
            self.stat_quota = float(quota)
            self.state_logged_in = False
            return True

    def update(self):
        if __debug__: print 'Updating'
        response = self.sda_send(SDA_UPDATE)
        type, success, msg = response.split(' ',2)
        if int(success) == SDA_UPDATE_YES:
            # msg like: 0 Quota 24.423Mb; Used 4.523Mb
            msgs = msg.split(' ',5)
            self.stat_quota = float(msgs[2][0:-3])
            self.stat_used = float(msgs[4][0:-3])
            return True
        return False

    def sda_connect(self):
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        try:
            s.connect((self.sda_host, self.sda_port))
            if not self.client_host:    # determine our ip
                self.client_host = s.getsockname()[0]
        except socket.error:
            if __debug__: print 'Cannot connect to %s:%s' % (self.sda_host, self.sda_port)
            return False
        self.socket = s
        return True

    def sda_send(self, number):
        if not self.sda_connect():
            return False
        request = '%s %s %s %s 0 %s \n' % (number, self.username, self.password, self.client_host, self.client)
        if __debug__: print 'Request:', repr(request)
        request = encode(request)
        if __debug__: print 'Request(encoded):',repr(request)
        self.socket.send(request)
        response = self.socket.recv(10000)
        if __debug__: print 'Response:',repr(response)
        response = decode(response)
        if __debug__: print 'Response(decoded):',repr(response)
        self.socket.close()
        return response

def encode(the_string):
    return ''.join([chr(ord(c) + (i % 7 )) for i,c in enumerate(the_string)])

def decode(the_string):
    return ''.join([chr(ord(c) - (i % 7 )) for i,c in enumerate(the_string)])

if __name__ == '__main__':
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option('-u', "--user", dest="username", help="Your SDA username")
    parser.add_option('-p', "--pass", dest="password", help="Your SDA password")
    parser.add_option('-H', '--server_host', dest="sda_host", help="Your SDA server (default:%s)" % SDA_HOST, default=SDA_HOST)
    parser.add_option('-P', "--server_port", type="int", dest="sda_port", help="Your SDA port (default:%s)" % SDA_PORT, default=SDA_PORT)
    parser.add_option('-i', '--client_host', dest="client_host", help="The client IP Address to link to this account (default: your own IP)", default=None)
    options, args = parser.parse_args()
    if not options.username or not options.password:
        sys.exit('You must enter your username/password')

    sda = SdaClient(username=options.username, password=options.password, client_host=options.client_host, sda_host=options.sda_host, sda_port=options.sda_port)
    if sda.login():
        if __debug__: print 'Im logged in'
        try:
            while 1:
                if __debug__: print 'Sleeping'
                time.sleep(SDA_UPDATE_TIME)
                sda.update()
        finally:
            if __debug__: print 'Ouch, must exit'
            sda.logout()
            sys.exit()
    else:
        sys.exit('Unable to login')