"""
cookbookgrabber.py - download aspn cookbook python recipes index, and build url list, ready to download
"""
import sys,urllib2,htmllib,formatter,os
url = 'http://aspn.activestate.com/ASPN/Cookbook/Python?query_start=%d'
query_start = 1
query_increment = 20
query_limit = 900
queue_downloads = {}
try:
    os.mkdir('temp')
except:
    pass
while query_start < query_limit:
    print '> grabbing', query_start,' - ', query_start + query_increment,
    u = url % query_start
    cache_idxfile = './temp/idx%s.html' % query_start
    try:
        buffer = file(cache_idxfile).read() # get from cache
    except:
        buffer = urllib2.urlopen(u).read()  #   download instead
        file(cache_idxfile,'w').write(buffer)   # cache file
    parser = htmllib.HTMLParser(formatter.NullFormatter())
    parser.feed(buffer)
    atags = []
    for atag in parser.anchorlist:  # valid url like http://aspn.activestate.com/ASPN/Cookbook/Python/Recipe/286224
        if '/ASPN/Cookbook/Python/Recipe/' in atag:
            urld = 'http://aspn.activestate.com' + atag + '\n'
            atags.append(urld)
            queue_downloads[urld] = 1
    print '(%s) tags' % len(atags)
    file('urls.txt','a').writelines(atags)
    parser.close()
    query_start += query_increment

for i,urld in enumerate(queue_downloads):
    filename = urld[urld.rfind('/')+1:].strip() + '.html'
    print '> grabbing (%s / %s)' % (i, len(queue_downloads)), filename,
    if os.access(filename,os.F_OK): # check file existance
        print 'exist'
        continue
    buffer = urllib2.urlopen(urld).read()
    print 'ok'
    file(filename,'w').write(buffer)