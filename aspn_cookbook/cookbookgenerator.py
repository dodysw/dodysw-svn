#!/usr/bin/python
"""
cookbookgenerator.py

version 1.1, Sep 2004
copyright 2004,2005 dody suria wijaya's software house <dodysw@gmail.com>
Released under a BSD-style license
download aspn cookbook python recipes index, build url list, download recipes, parse recipe, and ready for chm building
Note: all read/write using binary, since html taken from aspn cookbook has varying EOL characters
This program is multiplatform, but the HTML Help Compiler is windows only, so there you go...
"""
import re,sys,urllib2,htmllib,formatter,os,glob,string,os.path,shutil,datetime,imp

# modifyable paramters
html_workshop_path = 'c:\\Program Files\\HTML Help Workshop\\hhc.exe' # path to html workshop, empty this to avoid spawing hhc
hhp_title = 'ASPN Python Cookbook - updated ' + datetime.date.today().strftime('%d %b %Y')  # help file title

# these probably should not be modified frequently
url = 'http://aspn.activestate.com/ASPN/Cookbook/Python?query_start=%d' # url to indices
query_start = 1 # aspn index start
query_increment = 20 # aspn index start
query_limit = 2000 # aspn index end, increase this if you see that aspn cookbook collection has exceeded 900. you can safely put 5000 if you like, but it gets slower since i'd download until index 5000 without getting new articles.

# get absolute dir
def main_is_frozen():
    return (hasattr(sys, "frozen") or # new py2exe
            hasattr(sys, "importers") # old py2exe
            or imp.is_frozen("__main__")) # tools/freeze

def get_main_dir():
    if main_is_frozen():
        return os.path.dirname(sys.executable)
    return os.path.dirname(sys.argv[0])
abs_path = get_main_dir()

print 'Absolute path is', abs_path
raw_input('Note: 1) delete cache/index folder to refresh index. 2) delete cache/page folder to refresh page (needed for comment). Press enter to continue.');

# prepare temporary folders
try: os.removedirs(os.path.join(abs_path,'result'))    # clean result folder
except OSError: pass
try: os.mkdir(os.path.join(abs_path,'cache'))
except OSError: pass
try: os.mkdir(os.path.join(abs_path,'cache','index'))    # contain aspn indices caches
except OSError: pass
try: os.mkdir(os.path.join(abs_path,'cache','page')) # contain aspn page caches
except OSError: pass
try: os.mkdir(os.path.join(abs_path,'result'))
except OSError: pass

# start retrieving indices from aspn, and put them to temporary files, so that it can be continued later
queue_downloads = {}
link_counts = []
while query_start < query_limit:
    if len(link_counts) > 3: # detect the tendency of finish, start at forth page
        if link_counts[-1] != link_counts[0] and link_counts[-1] == link_counts[-2] == link_counts[-3]:
            print 'I think no more page to download'
            break
    print '- grabbing', query_start,' - ', query_start + query_increment,
    try: # to get from cache
        cache_idxfile = os.path.join(abs_path,'cache','index', 'idx%s.html' % query_start)
        buffer = file(cache_idxfile,'rb').read()
    except: # if not available, download index from aspn
        buffer = urllib2.urlopen(url % query_start).read()  #   download index
        file(cache_idxfile,'wb').write(buffer)   # save to cache file

    # parse index file to get urls to pages
    parser = htmllib.HTMLParser(formatter.NullFormatter())
    parser.feed(buffer)
    atag_count = 0
    for atag in parser.anchorlist:  # valid url is like http://aspn.activestate.com/ASPN/Cookbook/Python/Recipe/286224
        if '/ASPN/Cookbook/Python/Recipe/' in atag:
            urld = 'http://aspn.activestate.com' + atag + '\n'
            queue_downloads[urld] = 1   # put the url to download queue list
            atag_count += 1
    print 'got (%s) page' % atag_count
    link_counts.append(atag_count)
    parser.close()
    query_start += query_increment  # next..

# start retrieving urls in queue downloads
for i,urld in enumerate(queue_downloads):
    filename = urld[urld.rfind('/')+1:].strip() + '.html'
    print '- grabbing page (%s / %s)' % (i+1, len(queue_downloads)), filename,
    if os.access(os.path.join(abs_path, 'cache', 'page', filename),os.F_OK): # check file existance
        print 'exist'
        continue
    buffer = urllib2.urlopen(urld).read()   # download cookbook
    print 'ok'
    file(os.path.join(abs_path,'cache','page',filename),'wb').write(buffer)   # save to cache file


# initialize variables as preparation for creating html help
regex = '<b>Title:</b>&nbsp;([^<]*).*?<b>Submitter:</b>&nbsp;([^<]*).*?<b>Last Updated:</b>&nbsp;([^<]*).*?<b>Version no:</b>&nbsp;([^<]*).*?<b>Category:</b>[^>]*>([^<]*).*?<p><b>Description:</b></p>.*?<p>(.*?)</p>.*?<p><b>Source:</b>.*?(<pre>.*?</pre>)'
regex_2 = 'Discussion:</b></p>(.*?)</td>.*?<!-- show comment dtml -->(.*?)<!-- end of show comment dtml -->'
tmpl = file(os.path.join(abs_path,'include','template.tpl'),'rb').read()
tmpl_cat = file(os.path.join(abs_path,'include','templatecat.tpl'),'rb').read()
tmpl_main = file(os.path.join(abs_path,'include','templatemain.tpl'),'rb').read()
tmpl_toc = file(os.path.join(abs_path,'include','templatetoc.tpl'),'rb').read()

# iterate saved caches of html page to get:
#    1) distinct categories
#    2) @page title, author, lastupdate, version, category, description, source, discussion, and comments
# then create new pages based on template in template.tpl using those informations
categories = {}
for filename in glob.glob(os.path.join(abs_path,'cache','page','*.html')):
    buffer = file(filename,'rb').read()
    m = re.search(regex,buffer,re.DOTALL)
    if m:
        title, submitter,lastupdate,version,category,description,source = m.groups()
        submitter = submitter.replace('(','')
        title, submitter,lastupdate,version,category,description,source = map(string.strip,(title, submitter,lastupdate,version,category,description,source))
        if category == '':
            category = 'Others'
        categories.setdefault(category,[])
        categories[category].append([title,os.path.basename(filename),lastupdate])
    else:
        print "parsing fail: %s" % filename
    m = re.search(regex_2,buffer,re.DOTALL)
    discussion = comment = '<i>Not available</i>'
    if m:
        discussion, comment = m.groups()
    newfile = tmpl % dict(title=title, submitter=submitter,lastupdate=lastupdate,version=version,category=category,description=description,source=source,filename=os.path.basename(filename),discussion=discussion, comment=comment)
    newfile = newfile.replace('\r\n','\n') #new: makesure file ends with \n, to avoid double spacing
    file(os.path.join(abs_path,'result', os.path.basename(filename)),'wb').write(newfile)  # then create new pages based on template in template.tpl using those informations

# prepare creation of toc (table of content) file, used by html help for left-panel tree navigation
sources_link_str = "<br><a href='%s'>%s</a> (%s)"
toc_str1 = '<LI><OBJECT type="text/sitemap"><param name="Name" value="%s"><param name="Local" value="%s.html"></OBJECT>\n'
toc_str2 = '<LI><OBJECT type="text/sitemap"><param name="Name" value="%s"><param name="Local" value="%s"></OBJECT>\n'
toc = '<UL>\n'
toc += '<LI><OBJECT type="text/sitemap"><param name="Name" value="Main page"><param name="Local" value="main.html"></OBJECT>\n'

# sort the categories
catsort = categories.keys()
catsort.sort()

# iterate categories for
#   1) creating index of cookbook pages per each category
#   2) completing the table of content file
for cat in catsort:
    toc += toc_str1 % (cat,cat) # used in toc
    rows = categories[cat]
    sources_link = ''.join([sources_link_str % (filename,title,lastupdate) for title,filename,lastupdate in rows])  # used in main page
    toc += '<UL>\n' + ''.join([toc_str2 % (title,filename) for title,filename,lastupdate in rows]) + '</UL>\n'  # used in toc
    newfile = tmpl_cat % dict(title=title, category=cat,sources_link=sources_link)    # category index: paste variables into template
    file(os.path.join(abs_path, 'result','%s.html' % cat),'wb').write(newfile)  # category index: commit to file
toc += '</UL>\n'

# then, create the table of content
newfile = tmpl_toc % dict(toc=toc)    # table of content : paste variables into template
file(os.path.join(abs_path, 'result','toc.hhc'),'wb').write(newfile)    # table of content: commit to file

# create the main/default page
sources_link = ''.join(["<br><a href='%s.html'>%s</a>" % (cat,cat) for cat in catsort])
newfile = tmpl_main % dict(sources_link=sources_link) # main page: paste variables into template
file(os.path.join(abs_path,'result','main.html'),'wb').write(newfile)  # main page: commit to file

# create hpp file, based on template.hhp so that i can automate modified-date
buffer = file(os.path.join(abs_path, 'include','template.hhp'),'rb').read()
buffer = buffer % dict(
    title=hhp_title,
    compiled_file = os.path.join(abs_path, 'aspnpython-%s.chm' % datetime.date.today().isoformat())
    )
file(os.path.join(abs_path, 'result','cookbook.hhp'),'wb').write(buffer)    # table of content: commit to file

# copy additional files
shutil.copy(os.path.join(abs_path, 'include','aspn.css'),os.path.join(abs_path, 'result'))

# spawn html workshop, if defined
if 'win32' in sys.platform and html_workshop_path:
    hw_path = '"%s" %sresult\\cookbook.hhp' % (html_workshop_path, abs_path)
    hw_path.replace('/','\\') # fix '/' problem
    os.system(hw_path)
else:
    print 'Files has been prepared in "result" folder. Please doubleclick result/cookbook.hhp file, and compile.'

print 'done.'