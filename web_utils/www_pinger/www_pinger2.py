import sys, socket, winsound, time

PORT = 80
TIMEOUT = 10.0
BEEP_ERR_LEN = 3000 # miliseconds
PING_PERIOD = 90    # second
url = '192.168.1.2';    # alternative: 202.169.33.202 (www.biz.net.id)

if __name__ == '__main__':
    if len(sys.argv) > 1:
        url = sys.argv[1]
    print 'WWW Ping 2: ping web server with sound feedback\nAuthor: Dody Suria Wijaya <dodysw@gmail.com>'
    print 'pinging ', url, PORT
    while 1:
        try:
            s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            s.settimeout(TIMEOUT)
            s.connect((url, PORT)) # try to connect
            s.send('GET / HTTP/1.0\r\n\r\n')
            s.recv(1)
            s.close()
            winsound.Beep(800,50)
            print ',',
            time.sleep(PING_PERIOD)
        except socket.timeout:
            print 'TO',
            winsound.Beep(1000,BEEP_ERR_LEN)
            time.sleep(3)
        except socket.error:
            print 'ERR',
