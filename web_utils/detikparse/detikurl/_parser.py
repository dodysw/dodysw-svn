
import MySQLdb,re,sys

def parsenews(filename,dbc,timestamp,url):
    jam,menit,detik = timestamp
    state = 'start'
    buffer = file(filename).read()
    if len(buffer)<100:
        print 'file length not make sense',filename
        return        
    regex = 'Kode kesalahannya <strong>404</strong>'
    m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
    if m:
        print '404 document',filename
        return
    while 1:
        regex = '<font class="subjudulberita">(.*?)</font>.*?<font class="judulberita">(.*?)</font><br>.*?<font class="textreporter">(.*?)<br></font>.*?<font class="textberita">.*?(<b>.*?)</font>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            subjudul,judul,reporter,berita = m.groups()
            break
        regex = '<font class="subjudulberita">(.*?)</font>.*?<font class="judulberita">(.*?)</font><br>.*?<font class="textreporter">(.*?)</font>.*?<font class="textberita">.*?(<b>.*?)</font>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            subjudul,judul,reporter,berita = m.groups()
            break
        regex = '<BR><FONT size=5>(.*?)</FONT>.*?<BR><FONT color=#ff0000 size=2>(.*?)</FONT>.*?<P align="Justify">(.*?)</center>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul = ''
            break            
        regex = '<font class=subjudulberita>(.*?)</font>.*?<font class=judulberita>(.*?)</font><br>.*?<font class=textreporter>(.*?)<br></font>.*?<font class=textberita>.*?(<b>.*?)</font>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            subjudul,judul,reporter,berita = m.groups()
            break        
        regex = '<FONT size=5>(.*?)</FONT>.*?<FONT color=#ff0000 size=2>(.*?)</FONT>.*?(<B>.*?)</font>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul=''
            break

        regex = '<font size="5" color="#F00000">(.*?)</font>.*?<font size="2">(.*?)</font>.*?<font color="black">(.*?)</font><p>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul=''
            break

        regex = '<font size="5" color="red">(.*?)</font>.*?<font color="red" size="2">(.*?)</font>.*?<p><font color="black">(.*?)</font></p>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul=''
            break
            
        regex = '<FONT size=5><B>(.*?)</B>.*?(<B>.*?)<!'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,berita = m.groups()
            subjudul=reporter=''
            break

        regex = '<FONT size="4" color="Black">(.*?)</FONT>.*?<FONT size="5" color="Black">(.*?)</FONT>.*?<FONT color="#ff0000" size="2">(.*?)<BR>.*?<FONT color="#000000">.*?(<B>.*?)</b></font>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            subjudul,judul,reporter,berita = m.groups()
            break    

        regex ='<font size="5" color="#F00000">(.*?)</font>.*<font size="2">(.*?)</font>.*?<font color="black">(.*?)</font>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul = ''
            break
        
        regex = '<font size="3" color="#009700">(.*?)</font>.*<font size="5" color="#F00000">(.*?)</font>.*?<font color="black">(.*?)<font color="black">'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul = ''
            break
            
        print "regex failed at ",filename.replace('/','\\')
        return

    if berita.strip() == "":
        print "news zero content",filename.replace('/','\\')
        return
    if judul.strip() == "":
        print "title zero content",filename.replace('/','\\')
        return

        #sys.exit()
    #parse date
    m = re.search('news-(\d+)-(\d+)-(\d+)-(.*)-(\d+)\.html',filename)
    if m:
        date = "%s-%s-%s %s:%s:%s" % (m.group(1),m.group(2),m.group(3),jam,menit,detik)
        channel,idx = m.group(4),m.group(5)
    else:
        print "unable to parse filename",filename
        sys.exit()
    sql = """
    insert into NEWS_TAB 
    (TANGGAL, CHANNEL, IDX, JUDUL, SUBJUDUL, REPORTER,BERITA,FILENAME) values 
    (%s,%s,%s,%s,%s,%s,%s,%s)""" % tuple(map(MySQLdb.string_literal,(date,channel,idx,judul,subjudul,reporter,berita,filename)))
    dbc.execute(sql)