import urllib2, winsound, time

url = 'http://192.168.1.2/';
print 'WWW Ping: ping web server with sound feedback - Dody Suria Wijaya <dodysw@gmail.com>'
print 'pinging ', url,
while 1:
    try:
        urllib2.urlopen(url)
        winsound.Beep(800,50)
        print ',',
        time.sleep(3)
    except urllib2.URLError:
        winsound.Beep(1000,1000)
