import ClientCookie # require ClientCookie (http://wwwsearch.sourceforge.net/ClientCookie/)
import urllib, urllib2, re, string, email, imp, sys, os.path

#~ ClientCookie.getLogger("ClientCookie").setLevel(ClientCookie.DEBUG)

http_session = {}
debug = False
pop_limit = 150      #define maximum emails downloaded from webmail per try
maxtry = 5
siluman_enabled = False

def main_is_frozen():
    return (hasattr(sys, "frozen") or # new py2exe
            hasattr(sys, "importers") # old py2exe
            or imp.is_frozen("__main__")) # tools/freeze

def get_main_dir():
    if main_is_frozen():
        return os.path.dirname(sys.executable)
    return os.path.dirname(sys.argv[0])

configfile = os.path.join(get_main_dir(),'popok.ini')
try:
    fh = file(configfile)
    import ConfigParser
    scp = ConfigParser.SafeConfigParser()
    scp.readfp(fh)
    p = dict(scp.items('SILUMAN'))
    siluman_url = p['url']
    siluman_pass = p['password']
    siluman_enabled = True
except IOError:
    # python.ini not found in current folder
    pass
except ConfigParser.NoSectionError: # python.ini does not contain [SILUMAN]
    pass

def trygeturlvar(*params, **kargs):
    """urllib.urlopen with configurable retry"""
    if debug: print "trygeturl", params
    for i in range(0,maxtry):
        try:
            #~ print 'connecting to', params
            request = ClientCookie.Request(*params)
            if kargs.get('unverifiable', False): # special case, mail.telkom.net set cookie at redirection to login.plasa.com (RFC 2109 unverifiable transaction). we must do this so that ClientCookie pass those cookies to the new location also
                request.unverifiable = True
                request.origin_req_host = kargs.get('unverifiable')
            response = ClientCookie.urlopen(request)
            #~ print response.info().headers
            return response
            #~ return ClientCookie.urlopen(*params)
            break
        except urllib2.URLError:
            #~ print 'Retrying'
            continue

    raise Exception,"Max try exceeded"

class HTTPMaildrop:
    "A maildrop implemented by fetching from a webmail"

    NOT_AUTHENTICATED = 0
    AUTHENTICATED = 1

    def __init__(self,username):
        self.emails=[]
        self.cookie = ''
        #~ self.cgate_cookie = '' # new: cookie located at set-cookie http header, plasa.com now require this to be transmitted also
        username = username.lower()
        if '@' not in username:
            self.username = username
            self.userdomain = 'plasa.com'
        else:
            self.username,self.userdomain = username.split('@')

        self.emailaddress = self.username + '@' + self.userdomain
        if not http_session.has_key(self.emailaddress):
            http_session[self.emailaddress] = {}

        if self.userdomain == 'telkom.net':
            self.hostname = 'mail.telkom.net'
            self.login_hostname = 'login.plasa.com'
            self.login_url = '/index_net.php'
        else:   #default to plasa.com
            self.hostname = 'mail1.plasa.com'
            #self.login_hostname = 'login.plasa.com'
            self.login_hostname = 'mail1.plasa.com'
            self.login_url = '/'
        self.password = ''
        self.state = HTTPMaildrop.NOT_AUTHENTICATED
        self.cache_msg_count = -1

    def login(self,username,password):
        self.password = password
        if http_session[self.emailaddress].get('cookie',False):
            if debug: print "Getting cookie from cache"
            self.cookie = http_session[self.emailaddress]['cookie']
            #~ self.cgate_cookie = http_session[self.emailaddress]['cgate_cookie']
            self.state = HTTPMaildrop.AUTHENTICATED
            return 1
        data = urllib.urlencode({'username':self.username,'password':self.password,'login.x':0,'login.y':0})
        unverifiable = (self.userdomain == 'telkom.net' and self.login_hostname == 'login.plasa.com') and 'mail.telkom.net' or False
        fh = trygeturlvar("http://%s%s"%(self.login_hostname,self.login_url),data,unverifiable = unverifiable)
        #~ print 'XXX', fh.info().headers

        # check if account is overquota
        if 'Alerts.wssp' in fh.geturl():
            if debug: print "Account over quota!"
            # plasacom force us to confirm the overquota!
            ## get the session
            m = re.search("Session/([^/]+)",fh.geturl())
            if not m:
                if debug: print "Parser: I can't find session cookie"
                return "strange, i can't find session cookie"
            cookie = m.group(1)
            # get alert time (20040407101325)
            # 		<INPUT type=hidden Name="AlertTime" Value="20040629070506"></TD></TR></TABLE>
            m = re.search("name=\"alerttime\"\s*value=\"(.*?)\"",fh.read(),re.IGNORECASE)
            if not m:
                if debug: print "Unable to parse over quota body for alerttime field value"
                return -1
            alerttime = m.group(1)
            url = "/Session/%s/Alerts.wssp" % cookie
            data = urllib.urlencode({'Update':'Confirm','returnURL':'mailbox.wssp?Mailbox=INBOX&amp;','AlertTime':alerttime})
            if debug: print "Get %s Data %s" % (self.login_hostname+url,data)
            trygeturlvar("http://%s%s"%(self.login_hostname,url),data,unverifiable = unverifiable)
        elif 'Hello.wssp' not in fh.geturl():
            if debug: print "Parser: User/pass FAIL"
            return -1
        m = re.search("Session/([^/]+)",fh.geturl())
        if not m:
            if debug: print "Parser: I can't find session cookie"
            return "strange, i can't find session cookie"
        http_session[self.emailaddress]['cookie'] = self.cookie = m.group(1)
        # retrieve cgate cookie from header
        # Set-Cookie: CGateProWebUser=65pzKmp9BqfWynKBihW1;Version=1;Max-Age=11100;Path=/Session/21427-j2ttFIkvLVAMEBfiAPB6-kmbcuww
        #             ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
        #~ print 'YY', fh.info().getheaders('Set-Cookie')
        #~ m = re.search("CGateProWebUser=([^;]+)",fh.info().getheaders('Set-Cookie')[0])
        #~ if not m:
            #~ if debug: print "Parser: I can't find communigate header session cookie"
            #~ return "strange, i can't find session cookie"
        #~ http_session[self.emailaddress]['cgate_cookie'] = self.cgate_cookie = m.group(1)
        if debug: print "Parser: Cookie is", self.cookie
        #~ if debug: print "Parser: CGate Cookie is", self.cgate_cookie
        self.state = HTTPMaildrop.AUTHENTICATED
        return 1

    def get_msg_count(self):
        if self.state!=HTTPMaildrop.AUTHENTICATED: return 0
        if len(self.emails) > 0: return len(self.emails)
        if self.cache_msg_count > -1: return self.cache_msg_count
        #sort by 4/Received, Order by 1/older first
        try:
            url = "/Session/%s/mailbox.wssp?Mailbox=INBOX&Sort=4&SDir=1&Limit=%d" % (self.cookie,pop_limit)
            fh = trygeturlvar("http://%s%s"%(self.hostname,url))
            htmlbody = fh.read()
        except:
            return "Unable to receive message list index"

        #currently, in webmail.plasa.com, we can detect the maximum e-mail index by checking the first checkbox's value
        m = re.findall("<INPUT type=checkbox name=\"Msg\" value=(\d*)",htmlbody)
        if not m:
            #if debug: print "Parser: Email index not found. Finding out..."
            #if debug: print "-----\r\n%s\r\n------" % htmlbody
            m = re.search("disconnect",htmlbody,re.IGNORECASE)
            if m:
                if debug: print "Parser: Webmail session disconnected. relogging.."
                #session end, login again to refresh session
                http_session[self.emailaddress]['cookie'] = ""
                self.login(self.username,self.password)
                return self.get_msg_count()

            m = re.search("Kegagalan Akses",htmlbody,re.IGNORECASE|re.DOTALL)
            if m:
                if debug: print "Parser: Server logically downed!"
                #the site is down logically, send error message and disconnect
                return "Site is logically downed"

            if debug: print "Parser: No email"
            #then it's probably because there really is no email in inbox
            self.cache_msg_count = 0
            return 0
        else:
            msg_count = len(m)
            if debug: print "Message count:", msg_count
            self.emails=[]
            #get each email's size
            m_size = re.findall("<TD class=texton NOWRAP align=RIGHT>\s*(\S*)\s*</TD>",htmlbody)
            if m_size:
                #convert "K" in email size string into 000
                size = map(lambda s: int(s.replace('K','000')),m_size)
                if len(m) != len(size):
                    #something wrong -- refert to pseudo size
                    if debug: print "UUPS SIZE NOT SAME:",size
                    size = [5000]*len(m)
            else:
                if debug: print "UUPS SIZE NOT FOUND!"
                size = [5000]*len(m)

            #construct array of emails, based on the number of email
            for i,id in enumerate(m):
                self.emails.append([0,'',id,size[i]])
            self.cache_msg_count = len(self.emails)
            return len(self.emails)

    def get_msg_size_total(self):
        return sum([size for deleted,email,id,size in self.emails if not deleted])

    def delete_msg(self,msg_no):
        try:
            deleted,msg,id,size = self.emails[msg_no]
            if deleted: return "Message already deleted"
            self.emails[msg_no][0] = 1
            return 1
        except IndexError,e:
            return "No such message"

    def delete_msg_commit(self):
        #BUG!!!!!!!!!!!!!!, DICT KEYS MUST BE UNIQUE, BUT MSG IS REPEATED FOR EACH MESSAGE
        params = ["Msg=%s" % id for deleted,msg,id,size in self.emails if deleted]
        if len(params) > 0:
            if debug: print "C--P->S: Committing delete..."
            url = "/Session/%s/Mailbox.wssp?Mailbox=INBOX" % self.cookie
            params.append(urllib.urlencode({'SID': self.cookie, 'Delete': ''}))
            try:
                if debug: print "HTTP POST request... (%s)" % url
                trygeturlvar("http://%s%s"%(self.hostname,url),'&'.join(params))
                if debug: print "HTTP POST response..."
            except:
                if debug: print "Problem connecting to server"
                return "Can't connect to " + self.hostname

    def msg_exists(self,msg_no):
        try:
            deleted = self.emails[msg_no][0]
            if deleted: return "Message has been deleted"
            return 1
        except IndexError,e:
            return "No such message"

    def send_msg(self,msg_no,linecount=-1):
        res = self.msg_exists(msg_no)
        if not res: return res
        deleted,msg,id,size = self.emails[msg_no]
        if msg == "":
            #start retrieving message from webmail
            try:
                url = "/Session/%s/MessagePart/INBOX/%s-P.txt" % (self.cookie,id)
                target = "http://%s%s"%(self.hostname,url)
                if debug: print "Getting msg",deleted,msg,id,size,"at",target
                fh = trygeturlvar(target)
                htmlbody = fh.read()
                if debug: print "done"
            except:
                return "Unable to receive message inbox"
            msg = self.emails[msg_no][1] = htmlbody.replace("\n","\r\n")

        body = []
        if linecount == -1:     #this came from "RETR" command
            msg = msg.replace("\r\n.\r\n","\r\n..\r\n").replace("\r\n.\r\n","\r\n..\r\n") #escape "." //BUG HERE!
            body.append(msg)
        else:   #this came from "TOP" command
            linecount = int(linecount)
            msglines = msg.split("\r\n")
            while 1:    #send all header line
                line = msglines.pop(0)
                body.append(line)
                if line == "": break
            count = 0
            while linecount > count:  #send count number of line
                count += 1
                line = msg.pop(0)
                if line == ".": line = ".." #escape "."
                body.append(line)
        body.append('.')
        return '\r\n'.join(body)

    def send_list(self,msg_no=None):
        body = []
        if msg_no == None:
            mails = filter(lambda msg:msg[0] != 1,self.emails)
        else:
            mails = [self.emails[msg_no]]
        for i,(deleted,msg,id,size) in enumerate(mails):
            body.append("%d %d" % ((i+1),size))
        body.append(".")
        return '\r\n'.join(body)

    def send_uidl_list(self,msg_no=None):
        body = []
        if msg_no == None:
            mails = filter(lambda msg:msg[0] != 1,self.emails)
        else:
            mails = [self.emails[msg_no]]
        for i,(deleted,msg,id,size) in enumerate(mails):
            body.append("%d %d" % ((i+1),int(id)))
            #if debug: print list
        body.append(".")
        return '\r\n'.join(body)

    def compose(self,msgbody):
        try_siluman = False
        try:
            cookie = http_session[self.emailaddress]['cookie']  #check whether "pop before smtp" has been done suessfuly by client
        except:
            #~ return 451, 'Must POP before SMTP can be done!'
            # assume siluman mode!
            try_siluman = True
        msg = email.message_from_string(msgbody)

        if debug:
            print 'Message data from email client'
            print msgbody.__repr__()
        header,body = msgbody.split('\r\n\r\n',1)

        # find out which emails is Bcc using rcptto_emails var (Set by smtp instance's do_DATA)
        strtos = msg.get('To','') + ' ' + msg.get('Cc','')
        bcc_emails = [emailaddr for emailaddr in self.rcptto_emails if emailaddr not in strtos]
        bcc_emails = ','.join(bcc_emails)
        header += '\r\nBcc: ' + bcc_emails   #we're doing raw header passing, so let's add bcc here

        if debug: print "Siluman mode? ", siluman_enabled and try_siluman, 'To', self.emailaddress
        if siluman_enabled and try_siluman: # use smtp proxy
            data = urllib.urlencode(
                {
                'p':siluman_pass,
                #~ 'from':self.emailaddress,
                #~ 'froml':msg.get('From',''),
                'body':body,
                'header':header,
                'usemyheader':'1',
                'to':msg.get('To',''),
                'subject':msg.get('Subject',''),
                #~ 'replyto':msg.get('Reply-To',''),
                #~ 'cc':msg.get('Cc',''),
                #~ 'bcc':msg.get('Bcc','') # this would never happen, since Bcc field only append at RCPT TO
                #~ 'bcc':bcc_emails
                }
                )
            if debug: print "Get %s Data %s" % (siluman_url.__repr__(),data.__repr__())
            #~ fh = trygeturlvar(siluman_url,data)
            fh = urllib.urlopen(siluman_url,data)
            ret = fh.read()
            if ret == '1':
                #~ print 'ret is one'
                return None
            else:
                #~ print 'Invalid Stealth sending',ret
                return 500,'Unable to send email through stealth mailer'

        msg = email.message_from_string(msgbody)
        params = {}
        params['To'] = msg.get('To','')
        params['Subject'] = msg.get('Subject','')
        params['Cc'] = msg.get('Cc','')
        #~ params['Bcc'] = msg.get('Bcc','')   # this would never happen, since Bcc field only append at RCPT TO
        params['Bcc'] = bcc_emails
        params['desiredCharset'] = "ISO-8859-1"
        params['Send'] = "Kirim"
        params['filled'] = "1"
        if msg.is_multipart():
            #email has attachments
            for payload in msg.get_payload():
                #assume payload to be normal body, if content type is text/plain
                if payload.get_content_type() == "text/plain":
                    params['Body'] = payload.get_payload()
                else:
                    params['Attachment'] = payload.get_payload(None,True),payload.get_filename(),payload.get_content_type()
        else:
            params['Body'] = msg.get_payload()
        params = urllib.urlencode(params)

        try:
            url = "/Session/%s/Compose.wssp" % cookie
            if debug: print "C--P->S: posting compose... (%s)" % url
            fh = trygeturlvar("http://%s%s"%(self.hostname,url),params)
            # POTENTIAL BUGS: IF THE RESULT OF MESSAGE POST IS 200 BUT ACTUALY NOT OK (HELLO, SERVER IS BUSSY, PLEASE COME AGAIN), I SHOULD CHECK THE MESSAGE BODY!
            if debug: print "C--P<-S: waiting response..."
        except:
            return 500, "Unable to send"
