import urllib
folder = 'c:/_py/download/'
folder = '~/'
urls = "http://imgsrc.hubblesite.org/hu/db/%s/%s/images/a/formats/full_jpg.jpg"
for year in range(1990,2003):
    for i in range(1,45):
        if i < 10: i = '0'+str(i)
        url = urls % (year,i)        
        filename = "%shubble-%s-%s.jpg" % (folder,year,i)        
        print "- Downloading %s to %s" % (url, filename)
        urllib.urlretrieve(url,filename)