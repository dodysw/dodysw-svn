import re,sys,os,MySQLdb,string
newdir = './result'
db = MySQLdb.connect('localhost','aspuser','user123','dody')
dbc = db.cursor()
sql = "TRUNCATE TABLE pycookbook"
dbc.execute(sql)

fs = [f for f in os.listdir('.') if 'html' in f]
regex = '<b>Title:</b>&nbsp;([^<]*).*?<b>Submitter:</b>&nbsp;([^<]*).*?<b>Last Updated:</b>&nbsp;([^<]*).*?<b>Version no:</b>&nbsp;([^<]*).*?<b>Category:</b>[^>]*>([^<]*).*?<p><b>Description:</b></p>.*?<p>(.*?)</p>.*?<p><b>Source:</b>.*?(<pre>.*?</pre>)'
tmpl = file('template.tpl').read()
tmpl_cat = file('templatecat.tpl').read()
tmpl_main = file('templatemain.tpl').read()
tmpl_toc = file('templatetoc.tpl').read()
def todict(**d): return d
try:
    os.mkdir(newdir)
except:
    pass

#parse and populate table
for filename in fs:
    buffer = file(filename).read()
    m = re.search(regex,buffer,re.DOTALL)
    if m:
        title, submitter,lastupdate,version,category,description,source = m.groups()
        submitter = submitter.replace('(','')
        title, submitter,lastupdate,version,category,description,source = map(string.strip,(title, submitter,lastupdate,version,category,description,source))
        if category == '': category = 'Others'
        sql = """INSERT INTO pycookbook
        (title,submitter,lastupdate,version,category,description,source,filename) VALUES
        (%s,%s,%s,%s,%s,%s,%s,%s);""" % tuple(map(MySQLdb.string_literal,[title,submitter,lastupdate,version,category,description,source,filename]))
        dbc.execute(sql)
    else:
        print "parsing fail: %s" % filename
    newfile = tmpl % todict(title=title, submitter=submitter,lastupdate=lastupdate,version=version,category=category,description=description,source=source,filename=filename)
    file("%s/%s"%(newdir,filename),'w').write(newfile)


sql = "select distinct category from pycookbook order by category"
dbc.execute(sql)
cats = dbc.fetchall()
toc = '<UL>\n'
toc += '<LI><OBJECT type="text/sitemap"><param name="Name" value="Main page"><param name="Local" value="main.html"></OBJECT>\n'
for cat, in cats:
    toc += '<LI><OBJECT type="text/sitemap"><param name="Name" value="%s"><param name="Local" value="%s.html"></OBJECT>\n' % (cat,cat)
    sql = "select title,filename,lastupdate from pycookbook where category=%s order by lastupdate desc" % MySQLdb.string_literal(cat)
    dbc.execute(sql)
    rows = dbc.fetchall()
    sources_link = ''.join(["<br><a href='%s'>%s</a> (%s)" % (filename,title,lastupdate) for title,filename,lastupdate in rows])
    toc += '<UL>\n' + ''.join(['<LI><OBJECT type="text/sitemap"><param name="Name" value="%s"><param name="Local" value="%s"></OBJECT>\n' % (title,filename) for title,filename,lastupdate in rows]) + '</UL>\n'
    newfile = tmpl_cat % todict(title=title, category=cat,sources_link=sources_link)
    file("%s/%s.html"%(newdir,cat),'w').write(newfile)
toc += '</UL>\n'
sources_link = ''.join(["<br><a href='%s.html'>%s</a>" % (cat[0],cat[0]) for cat in cats])
newfile = tmpl_main % todict(sources_link=sources_link)
file("%s/main.html"%newdir,'w').write(newfile)
newfile = tmpl_toc % todict(toc=toc)
file("%s/toc.hhc"%newdir,'w').write(newfile)