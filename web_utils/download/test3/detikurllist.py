"""
detikcom url news parser
this software gather all urls pointing to news in detikcom database
"""
print
print "comdetik url news parser v1.0 - copyright dswj@plasa.com"
print "note: by using this software, you take full responsibility of any consequential damage."
print
import sys
v = sys.version_info
if int('%s%s'%(v[0],v[1])) < 23:
    print "I kinda need python v2.3 or newer, sorry."
    sys.exit()
import htmllib,formatter,urllib,os,datetime
today = datetime.date.today()
putfolder = os.getcwd()
channels = ['detikNews','detikFinance','detikFoto','detikFood','detikHealth','detikHot','detiki-Net','detikNews','detikSport']
url_index = "http://www1.detik.com/indeksberita/index.php?fuseaction=index.Berita&chan=%s&tgl=%s&bln=%s&thn=%s"
all_urls = []
putlistfile = 'newslist.txt'
maxtry = 5

def trygeturl(url,filename):
    trycount = 0
    while trycount <= maxtry:
        try:
            urllib.urlretrieve(url,filename)
            break
        except:                
            trycount += 1            
            print "--retry %s/%s" % (trycount,maxtry)

while 1:
    print "On",today.strftime("%A, %d %b %Y")
    tgl = today.day
    bln = today.month
    thn = today.year
    for channel in channels:
        print "-channel",channel
        url = url_index % (channel,tgl,bln,thn)
        filename = "index-%s-%s-%s-%s.html" % (thn,bln,tgl,channel)
        print "--downloading index to %s" % filename
        if os.access(filename,os.F_OK):
            print "--already downloaded. using it instead."
        else:            
            trygeturl(url,filename)
        parser = htmllib.HTMLParser(formatter.NullFormatter())
        parser.feed(file(filename).read())
        atags = [atag for atag in parser.anchorlist if "%s/%02d/%02d" % (thn,bln,tgl) in atag]
        parser.close()
        if len(atags)>0:
            #download all news
            for i,url in enumerate(atags):
                filename = "news-%s-%s-%s-%s-%s.html" % (thn,bln,tgl,channel,i)
                print "--downloading news to %s" % filename
                if os.access(filename,os.F_OK):
                    print "--already downloaded. skipping.."
                    continue                
                trygeturl(url,filename)
            file(putlistfile,'a').write("\n".join(atags) + "\n")        
            all_urls.extend(atags)
        print "--got %s of %s urls" % (len(atags),len(all_urls))    
    today = today - datetime.timedelta(days=1)