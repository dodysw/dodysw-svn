import urllib, re, datetime
import htmllib,formatter,StringIO
import gzip
import email,email.MIMEText, email.MIMEMultipart

http_session = {}
debug = False
pop_limit = 1000      #define maximum emails downloaded from webmail

class HTTPMaildrop:
    "A maildrop implemented by fetching from detikcom"

    NOT_AUTHENTICATED = 0
    AUTHENTICATED = 1

    def __init__(self,username):
        self.emails=[]
        self.state = HTTPMaildrop.NOT_AUTHENTICATED
        self.urls = []

    def login(self,username,password):
        self.password = password
        self.state = HTTPMaildrop.AUTHENTICATED
        return 1

    def do_detik_getindex(self):
        #~ channels = ['detikNews','detikFinance','detikFoto','detikFood','detikHealth','detikHot','detiki-Net','detikSport']
        #~ url_index = "http://www1.detik.com/indeksberita/index.php?fuseaction=index.Berita&chan=%s&tgl=%s&bln=%s&thn=%s"
        #~ channels = ['detikNews',]
        #~ all_urls = []
        #~ today = datetime.date.today()
        #~ thn,bln,tgl = today.year,today.month,today.day
        #~ for channel in channels:
            #~ url = url_index % (channel,tgl,bln,thn)
            #~ buffer = urllib.urlopen(url).read()
            #~ parser = htmllib.HTMLParser(formatter.NullFormatter())
            #~ parser.feed(buffer)
            #~ atags = [atag for atag in parser.anchorlist if "%s/%02d/%02d" % (thn,bln,tgl) in atag]
            #~ parser.close()
            #~ all_urls.extend(atags)

        # populate list of url to download
        all_urls = []
        today = datetime.date.today()
        thn,bln,tgl = today.year,today.month,today.day
        url = 'http://jkt1.detik.com/index.php'
        buffer = urllib.urlopen(url).read()
        # de-gzipped buffer incase of mod_gzip
        buffer = degzipped(buffer)
        parser = htmllib.HTMLParser(formatter.NullFormatter())
        parser.feed(buffer)
        #~ atags = [atag for atag in parser.anchorlist if "%s/%02d/%02d" % (thn,bln,tgl) in atag]
        #~ print parser.anchorlist
        atags = []
        for atag in parser.anchorlist:
            # Filter all non-news link
            # News links:
            # - http://www.detiknews.com/index.php/detik.read/tahun/2004/bulan/04/tgl/02/time/91725/idnews/123085/idkanal/10
            # - http://www.detiknews.com/indexfr.php?url=http://www.detiknews.com/index.php/detik.read/tahun/2004/bulan/06/tgl/28/time/16553/idnews/169327/idkanal/10
            # - http://www.detiksport.com/indexfr.php?url=http://www.detiksport.com/index.php/detik.read/tahun/2004/bulan/06/tgl/28/time/12439/idnews/169152/idkanal/73
            # - http://www.detik.com/peristiwa/index.php?url=http://www.detik.com/peristiwa/2004/01/26/20040126-150358.shtml

            if atag[0] == '/': # make sure it's abosulte url
                atag = 'http://www.detik.com'+atag
            if atag.count('http') > 1:  # double http is always news
                atag = atag[atag.rfind('http'):]
                atags.append(atag)
                #~ print atag,'OK!'
            elif "%s/%02d/%02d" % (thn,bln,tgl) in atag:  # date in tag is always news
                atags.append(atag)
                #~ print atag,'OK!'
            #~ elif 'www.detik' in atag:
                #~ print atag,'Naah'

        if debug: print 'Berita:',len(atags)
        parser.close()
        all_urls.extend(atags)
        #dedupe urls
        tempdict = {}
        for url in all_urls: tempdict[url] = 1
        #save url list
        self.urls = tempdict.keys()


    def get_msg_count(self):
        if self.state!=HTTPMaildrop.AUTHENTICATED:
            return 0
        self.do_detik_getindex()
        for i,url in enumerate(self.urls):
            # this will try to construct filename (which like 20040302235959) as UIDL

            #~ m = re.search(".*/(.*?)\.shtml",url)
            #~ if m:
                #~ id = m.group(1).replace('-','')
            #~ else:
                #~ if debug: print "warning, filename not found!"

            #- instead, hash the url as UIDL
            id = hash(url)
            self.emails.append([0,'',id,5000,url])
        return len(self.urls)

    def get_msg_size_total(self):
        return sum([5000 for deleted,email,id,size,url in self.emails if not deleted])

    def delete_msg(self,msg_no):
        try:
            deleted, msg, id, size, url = self.emails[msg_no]
            if deleted:
                return "Message already deleted"
            else:
                self.emails[msg_no] = 1, msg, id, size, url
                return 1
        except IndexError,e:
            return "No such message"

    def delete_msg_commit(self):
        pass

    def msg_exists(self,msg_no):
        try:
            deleted = self.emails[msg_no][0]
            if deleted: return "Message has been deleted"
            else:
                return 1
        except IndexError,e:
            return "No such message"

    def send_msg(self,msg_no,linecount=-1):
        res = self.msg_exists(msg_no)
        if not res: return res
        deleted,msg,id,size,url = self.emails[msg_no]
        if msg == "":
            if debug: print "Getting",url
            msg = urllib.urlopen(url).read()
            msg = degzipped(msg)

            #parse html news
            newsdict = parsenews(msg,url)
            if newsdict['judul'] == '':
                newsdict['judul'] = "FAIL: Unable to parse this news"

            #convert potentially html content string to normal text
            for k,v in newsdict.items():
                newsdict[k] = html2text(str(v))

            #add additional information (url and date)
            m = re.search('(\d\d\d\d)(\d\d)(\d\d)\-(\d\d)(\d\d)(\d\d)\.shtml',url)
            if not m:
                #fake it
                newsdict['date'] = datetime.datetime.now()
            else:
                tahun,bulan,tgl,jam,menit,detik = map(int,m.groups())
                newsdict['date'] = datetime.datetime(tahun,bulan,tgl,jam,menit,detik)
            newsdict['url'] = url
            newsdict['message_id'] = id
            msg = parsenews2email(newsdict,msg).replace("\n","\r\n")
            self.emails[msg_no] = deleted, msg, id, size, url

        body = []
        if linecount == -1:     #this came from RETR
            msg = msg.replace("\r\n.\r\n","\r\n..\r\n").replace("\r\n.\r\n","\r\n..\r\n") #escape "." //BUG HERE!
            body.append(msg)
        else:   #this came from TOP
            linecount = int(linecount)
            msgp = msg.split("\r\n")
            while 1:    #send all header line
                line = msgp.pop(0)
                body.append(line)
                if line == "":
                    break
            count = 0
            while (linecount > count):  #send count number of line
                count += 1
                line = msgp.pop(0)
                if line == ".": #escape "."
                    line = ".."
                body.append(line)
        body.append('.')
        complete_body = '\r\n'.join(body)
        return complete_body

    def send_list(self,msg_no = None):
        body = []
        if msg_no == None:
            mails = filter(lambda msg:msg[0] != 1,self.emails)
        else:
            mails = [self.emails[msg_no]]
        for i,(deleted,msg,id,size,url) in enumerate(mails):
            body.append("%d %d" % ((i+1),size))
        body.append(".")
        return '\r\n'.join(body)

    def send_uidl_list(self,msg_no = None):
        body = []
        if msg_no == None:
            mails = filter(lambda msg:msg[0] != 1,self.emails)
        else:
            mails = [self.emails[msg_no]]
        for i,(deleted,msg,id,size,url) in enumerate(mails):
            body.append("%d %s" % ((i+1),id))
        body.append(".")
        return '\r\n'.join(body)

def parsenews(buffer, url=None):
    """heavy duty regex HTML detikcom news parses into dictionary
    """
    subjudul = judul = reporter = berita = ''
    reg_mode = 0
    if debug: print "--------BUFFER----\r\n%s\r\n-------END BUFFER-----------\r\n" % buffer
    if len(buffer)<100:
        if debug: print 'news length not make sense', url
        return dict(subjudul=subjudul,judul=judul,reporter=reporter,berita='news length not make sense',reg_mode=reg_mode)
    regex = 'Kode kesalahannya <strong>404</strong>'
    m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
    if m:
        return dict(subjudul=subjudul,judul=judul,reporter=reporter,berita="Kode kesalahan 404",reg_mode=reg_mode)
    while 1:
        reg_mode = 1
        regex = '<font class="subjudulberita">(.*?)</font>.*?<font class="judulberita">(.*?)</font><br>.*?<font class="textreporter">(.*?)<br></font>.*?<font class="textberita">.*?(<b>.*?)</font>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            subjudul,judul,reporter,berita = m.groups()
            break
        reg_mode = 2
        regex = '<font class="subjudulberita">(.*?)</font>.*?<font class="judulberita">(.*?)</font><br>.*?<font class="textreporter">(.*?)</font>.*?<font class="textberita">.*?(<b>.*?)</font>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            subjudul,judul,reporter,berita = m.groups()
            break
        reg_mode = 3
        regex = '<BR><FONT size=5>(.*?)</FONT>.*?<BR><FONT color=#ff0000 size=2>(.*?)</FONT>.*?<P align="Justify">(.*?)</center>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul = ''
            break
        reg_mode = 4
        regex = '<font class=subjudulberita>(.*?)</font>.*?<font class=judulberita>(.*?)</font><br>.*?<font class=textreporter>(.*?)<br></font>.*?<font class=textberita>.*?(<b>.*?)</font>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            subjudul,judul,reporter,berita = m.groups()
            break
        reg_mode = 5
        regex = '<FONT size=5>(.*?)</FONT>.*?<FONT color=#ff0000 size=2>(.*?)</FONT>.*?(<B>.*?)</font>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul=''
            break
        reg_mode = 6
        regex = '<font size="5" color="#F00000">(.*?)</font>.*?<font size="2">(.*?)</font>.*?<font color="black">(.*?)</font><p>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul=''
            break
        reg_mode = 7
        regex = '<font size="5" color="red">(.*?)</font>.*?<font color="red" size="2">(.*?)</font>.*?<p><font color="black">(.*?)</font></p>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul=''
            break
        reg_mode = 8
        regex = '<FONT size=5><B>(.*?)</B>.*?(<B>.*?)<!'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,berita = m.groups()
            subjudul=reporter=''
            break
        reg_mode = 9
        regex = '<FONT size="4" color="Black">(.*?)</FONT>.*?<FONT size="5" color="Black">(.*?)</FONT>.*?<FONT color="#ff0000" size="2">(.*?)<BR>.*?<FONT color="#000000">.*?(<B>.*?)</b></font>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            subjudul,judul,reporter,berita = m.groups()
            break
        reg_mode = 10
        regex ='<font size="5" color="#F00000">(.*?)</font>.*<font size="2">(.*?)</font>.*?<font color="black">(.*?)</font>'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul = ''
            break
        reg_mode = 11
        regex = '<font size="3" color="#009700">(.*?)</font>.*<font size="5" color="#F00000">(.*?)</font>.*?<font color="black">(.*?)<font color="black">'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul = ''
            break
        reg_mode = 12
        regex = '<font class="judulberita">(.*?)<.*?textreporter.>(.*?)<.*?textberita.>(.*?)</fo'
        m = re.search(regex,buffer,re.DOTALL|re.IGNORECASE)
        if m:
            judul,reporter,berita = m.groups()
            subjudul = ''
            break
        reg_mode = 13
        if debug: print "regex failed at ",url
        berita = "\r\n--------BUFFER----\r\n%s\r\n-------END BUFFER-----------\r\n" % buffer
        return dict(subjudul=subjudul,judul=judul,reporter=reporter,berita=berita,reg_mode=reg_mode)

    if berita.strip() == "":
        if debug: print "news zero content",url
        return dict(subjudul=subjudul,judul=judul,reporter=reporter,berita="news zero content",reg_mode=reg_mode)
    if judul.strip() == "":
        if debug: print "title zero content",url
        return dict(subjudul=subjudul,judul=judul,reporter=reporter,berita="title zero content",reg_mode=reg_mode)

    return dict(subjudul=subjudul,judul=judul,reporter=reporter,berita=berita,reg_mode=reg_mode)

def parsenews2email(newsdict,orig_msg):
    """parse dictionary from parsenews into mime-compliant email
    """
    body = """%(subjudul)s %(judul)s

%(reporter)s
%(date)s

%(berita)s

Original Url: %(url)s
---------------------------------------------------
popok w/ detikcom module => http://dsw.gesit.com
regex mode: %(reg_mode)s
""" % newsdict
    mailmsg = email.MIMEMultipart.MIMEMultipart()
    mailmsg.add_header('From',newsdict['reporter'])
    mailmsg.add_header('To','Popok User <do@not.reply>')
    mailmsg.add_header('Date',newsdict['date'].strftime("%a, %d %b %Y %H:%M:%S +0700"))
    mailmsg.add_header('Subject',newsdict['subjudul']+' '+newsdict['judul'])
    mailmsg.add_header('X-Mailer','popok - webmail to pop3/smtp gateway - dodysw@gmail.com')
    mailmsg.add_header('Message-Id','<%s@detikcom.popok>'%newsdict['message_id'])

    mailmsg.epilogue = ''   # Guarantees the message ends in a newline

    msg_text = email.MIMEText.MIMEText(body)
    msg_html = email.MIMEText.MIMEText(orig_msg,'html')
    mailmsg.attach(msg_text)
    mailmsg.attach(msg_html)

    return mailmsg.as_string()

def html2text(msg):
    fh = StringIO.StringIO()
    mywriter = formatter.DumbWriter(fh)
    myformatter = formatter.AbstractFormatter(mywriter)
    parser = htmllib.HTMLParser(myformatter)
    parser.feed(msg)
    atags = ["[%d] %s" % (i+1,url) for i,url in enumerate(parser.anchorlist)]
    parser.close()
    fh.seek(0)
    buffer = fh.read()
    if len(atags) > 0:
        buffer = buffer + '\n' + '\n'.join(atags)
    return buffer

def degzipped(buffer):
    """check if body in gzip (by way of mod_gzip)
    """
    fh = gzip.GzipFile(fileobj = StringIO.StringIO(buffer))
    try:
        buffer = fh.read()
    except:
        pass
    return buffer