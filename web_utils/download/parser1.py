"""
this software gather all urls pointing to news in detikcom database
"""
import htmllib,formatter,urllib,os,datetime
#today = datetime.date(2002,7,31)
today = datetime.date.today()
putfolder = os.getcwd()
channels = ['detikNews','detikFinance','detikFoto','detikFood','detikHealth','detikHot','detiki-Net','detikNews','detikSport']
url_index = "http://www1.detik.com/indeksberita/index.php?fuseaction=index.Berita&chan=%s&tgl=%s&bln=%s&thn=%s"
all_urls = []
putlistfile = 'newslist.txt'
while 1:
    print "On ".today
    tgl = today.day
    bln = today.month
    thn = today.year
    for channel in channels:
        print " - channel ",channel
        url = urls % (channels,tgl,bln,thn)
        filename = "index-%s-%s-%s-%s.html" % (thn,bln,tgl,channel)
        print " - - downloading index to %s" % filename
        urllib.urlretrieve(url,filename)
        parser = htmllib.HTMLParser(formatter.NullFormatter())
        parser.feed(file(filename).read())
        atags = [atag for atag in parser.anchorlist if "%s/%02d/%02d" % (thn,bln,tgl) in atag]
        parser.close()
        file(putlistfile,'a').write("\n".join(atags) + "\n")        
        all_urls.extend(atags)
        print "- - got %s of %s urls" % (len(atags),len(all_urls))
    today = today - datetime.timedelta(days=1)
"""
Maaf, Indeks
http://www.detik.com/peristiwa/2004/01/08/20040108-080037.shtml
http://www.detikhealth.com/konsultasi/obstreti/2004/01/08/20040108-092743.shtml
http://www.detikhot.com/musik/2004/01/08/20040108-075712.shtml
http://www.detiksport.com/sepakbola/spanyol/2004/01/08/20040108-061411.shtml
"""