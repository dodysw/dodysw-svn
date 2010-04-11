import re,sys,os,string,glob
newdir = './result'

regex = '<b>Title:</b>&nbsp;([^<]*).*?<b>Submitter:</b>&nbsp;([^<]*).*?<b>Last Updated:</b>&nbsp;([^<]*).*?<b>Version no:</b>&nbsp;([^<]*).*?<b>Category:</b>[^>]*>([^<]*).*?<p><b>Description:</b></p>.*?<p>(.*?)</p>.*?<p><b>Source:</b>.*?(<pre>.*?</pre>)'
tmpl = file('template.tpl').read()
tmpl_cat = file('templatecat.tpl').read()
tmpl_main = file('templatemain.tpl').read()
tmpl_toc = file('templatetoc.tpl').read()

try:
    os.mkdir(newdir)
except:
    pass

categories = {}
#parse and populate table
for filename in glob.glob('*.html'):
    buffer = file(filename).read()
    m = re.search(regex,buffer,re.DOTALL)
    if m:
        title, submitter,lastupdate,version,category,description,source = m.groups()
        submitter = submitter.replace('(','')
        title, submitter,lastupdate,version,category,description,source = map(string.strip,(title, submitter,lastupdate,version,category,description,source))
        if category == '':
            category = 'Others'
        categories.setdefault(category,[])
        categories[category].append([title,filename,lastupdate])
    else:
        print "parsing fail: %s" % filename
    newfile = tmpl % dict(title=title, submitter=submitter,lastupdate=lastupdate,version=version,category=category,description=description,source=source,filename=filename)
    file("%s/%s"%(newdir,filename),'w').write(newfile)  # cookie page: commit to file

sources_link_str = "<br><a href='%s'>%s</a> (%s)"
toc_str1 = '<LI><OBJECT type="text/sitemap"><param name="Name" value="%s"><param name="Local" value="%s.html"></OBJECT>\n'
toc_str2 = '<LI><OBJECT type="text/sitemap"><param name="Name" value="%s"><param name="Local" value="%s"></OBJECT>\n'
toc = '<UL>\n'
toc += '<LI><OBJECT type="text/sitemap"><param name="Name" value="Main page"><param name="Local" value="main.html"></OBJECT>\n'

catsort = categories.keys() #sort categories
catsort.sort()

for cat in catsort:
    toc += toc_str1 % (cat,cat)
    rows = categories[cat]
    sources_link = ''.join([sources_link_str % (filename,title,lastupdate) for title,filename,lastupdate in rows])
    toc += '<UL>\n' + ''.join([toc_str2 % (title,filename) for title,filename,lastupdate in rows]) + '</UL>\n'
    newfile = tmpl_cat % todict(title=title, category=cat,sources_link=sources_link)    # category index: paste variables into template
    file('%s/%s.html' % (newdir,cat),'w').write(newfile)  # category index: commit to file

toc += '</UL>\n'
sources_link = ''.join(["<br><a href='%s.html'>%s</a>" % (cat,cat) for cat in catsort])
newfile = tmpl_main % todict(sources_link=sources_link) # main page: paste variables into template
file("%s/main.html"%newdir,'w').write(newfile)  # main page: commit to file
newfile = tmpl_toc % todict(toc=toc)    # table of content : paste variables into template
file("%s/toc.hhc"%newdir,'w').write(newfile)    # table of content: commit to file