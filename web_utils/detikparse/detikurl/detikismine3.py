"""
detikismine v.1.0
grab index, parse for url to news, gra
"""
print
print "detikismine v1.1"
print "copyright by dsw s/h <dswsh@plasa.com>"
print
print "do you agree take full responsibility of any consequential damage risen by the use of this software? (type 'yes' to agree)"
#if raw_input().lower() != 'yes': sys.exit()

import sys

v = sys.version_info
if int('%s%s'%(v[0],v[1])) < 23:
    print "I kinda need python v2.3 or newer, sorry."
    sys.exit()

import htmllib,formatter,urllib,os,datetime,re,MySQLdb
from _parser import *

datefrom = 2001,3,1
dateto = 2003,12,31

today = datetime.date(*datefrom)
dayuntil = datetime.date(*dateto)
putfolder = os.getcwd()
channels = ['detikNews','detikFinance','detikFoto','detikFood','detikHealth','detikHot','detiki-Net','detikSport']
url_index = "http://www1.detik.com/indeksberita/index.php?fuseaction=index.Berita&chan=%s&tgl=%s&bln=%s&thn=%s"
all_urls = []
putlistfile = 'newslist.txt'
logfile = 'log.txt'
maxtry = 5
verbose = True

def print2(line):
    global verbose
    file(logfile,'a').write(str(line)+ "\n")        
    if verbose:
        print line

def trygeturl(url,filename):
    trycount = 0
    while trycount <= maxtry:
        try:
            urllib.urlretrieve(url,filename)
            break
        except:                
            trycount += 1            
            print2("--retry %s/%s" % (trycount,maxtry))

if __name__ == '__main__':
    db = MySQLdb.connect('localhost','aspuser','user123','dody')
    dbc = db.cursor()
    sql = "TRUNCATE TABLE news_tab"
    dbc.execute(sql)

    while today <= dayuntil:
        print2("On %s" %today.strftime("%A, %d %b %Y"))
        thn,bln,tgl = today.year, today.month, today.day
        for channel in channels:
            #print2("-channel %s" % channel)
            url = url_index % (channel,tgl,bln,thn)
            filename = "./index/index-%s-%s-%s-%s.html" % (thn,bln,tgl,channel)
            #print2("--downloading index to %s" % filename)
            if os.access(filename,os.F_OK):
                #print2("--already downloaded. using it instead.")
                pass
            else:            
                trygeturl(url,filename)
            parser = htmllib.HTMLParser(formatter.NullFormatter())
            parser.feed(file(filename).read())
            atags = [atag for atag in parser.anchorlist if "%s/%02d/%02d" % (thn,bln,tgl) in atag]
            parser.close()
            if len(atags)>0:
                for i,url in enumerate(atags):
                    filename = "./news-%s-%s/news-%s-%s-%s-%s-%s.html" % (thn,bln,thn,bln,tgl,channel,i)
                    #parse url to get hour+minute+second
                    m = re.search('\-(\d\d)(\d\d)(\d\d)\.shtml',url)
                    if m:
                        jam,menit,detik = m.groups()
                    else:
                        print 'unable to parse time from url:',
                        sys.exit()
                    #print2("--downloading news (%s/%s) to %s" % (i+1,len(atags),filename))
                    if os.access(filename,os.F_OK):
                        pass
                        #print2("--already downloaded. skipping..")
                    else:
                        trygeturl(url,filename)
                    parsenews(filename,dbc,(jam,menit,detik),url)
                file(putlistfile,'a').write("\n".join(atags) + "\n")        
                all_urls.extend(atags)
            #print2("--got %s of %s urls" % (len(atags),len(all_urls)))
        today = today + datetime.timedelta(days=1)