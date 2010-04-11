import urllib
import datetime
folder = 'c:/_py/download/'
folder = '/shares/detiks'
urls = "http://www1.detik.com/indeksberita/index.php?fuseaction=index.Berita&chan=detikNews&tgl=%s&bln=%s&thn=%s"
today = datetime.date(2002,7,31)
while 1:
    tgl = today.day
    bln = today.month
    thn = today.year
    url = urls % (tgl,bln,thn)
    filename = "index-%s-%s-%s.html" % (tgl,bln,thn)
    print "- Downloading %s to %s" % (url, filename)
    urllib.urlretrieve(url,filename)
    today = today - datetime.timedelta(days=1)