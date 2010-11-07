import sys, socket, threading, time
from urlparse import urlparse

MAX_THREAD = 30

def beep():
    try:
        import winsound
        winsound.Beep(1200,200)
    except ImportError:
        pass

def is_website_up(website):
    try:
        if website.startswith('http://'):
            host, port = urlparse(website)[1], 80
            if ':' in host:
                host, port = host.split(':')
        else:
            host, port = website.split(':')
            port = int(port)
        s = socket.socket(socket.AF_INET,socket.SOCK_STREAM)
        s.settimeout(4)
        s.connect((host,port))
        s.close()
        beep()
        print '[%s] %s...UP' % website
    except socket.error:
        #~ print '%s...DOWN' % website
        pass

if __name__ == '__main__':
    c = 1
    for line in file(sys.argv[1]):
        website = line.strip()
        while threading.activeCount() > MAX_THREAD:
            time.sleep(0.2)
        t = threading.Thread(target=is_website_up, args=(website,), name='T%s' % c)
        c += 1
        t.start()