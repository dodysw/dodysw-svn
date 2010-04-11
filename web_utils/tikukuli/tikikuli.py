    #!/usr/bin/python
"""
tikikuli.py - download given url(s) then send them via email as file attachments

(download) => temporary download file => (tar bzip2 file) => encode to base64 => temporary email file => send SMTP data => delete temporaries
(download) => temporary download file =>
..

sample:

send php.gif to dodysw@gmail.com
    python tikikuli.py "dodysw@gmail.com" "http://au.php.net/images/php.gif"

send mynewsong.zip to your email and faking the file attachment as if a jpg file (needed since gmail at this time blocks email with compressed file attachment)
    python tikikuli.py -k "youremail@gmail.com" "http://some.mirror.com/mp3/mynewsong.zip"

send a huge text file to your email as compressed tar.bz2 to reduce email size
    python tikikuli.py -c "youremail@gmail.com" "http://some.mirror.com/txt/huge.txt"

send multiple files
    python tikikuli.py "youremail@gmail.com" "http://some.mirror.com/file1.txt" "http://some.mirror.com/file2.txt" "http://some.mirror.com/file3.txt"

send multiple files as compressed attachments to reduce email size
    python tikikuli.py -c "youremail@gmail.com" "http://some.mirror.com/file1.txt" "http://some.mirror.com/file2.txt" "http://some.mirror.com/file3.txt"

send multiple files as compressed attachments to reduce email size + faking it to circumvent compressed file blocking mail servers
    python tikikuli.py -k -c "youremail@gmail.com" "http://some.mirror.com/file1.txt" "http://some.mirror.com/file2.txt" "http://some.mirror.com/file3.txt"


note:
- smtp server other than localhost: create a file called web2email_config.py, insert this line:
    smtp_server = "my.smtp.server.com"
  and put the file in the same directory as this file.
- Web access: make sure you put web2email.php in the same directory as this file. You must have php installed.

todo
- add split email for large file

version update
1.4.8
- verbose mode
- configurable smtp server via parameter
1.4.6
- stream email file to smtp, to conserve ram
1.4.5
- user agent using IE like string
- customisable referer
1.4.4
- add local file max size limitation feature
- non exist local file now dont trigger exception, and just change ok to false
1.4.3
- change temporary download file to using tempfile.mkstemp()
"""

__version__ = '1.4.9'
MAX_TRY = 3
import random
FROM_ADDR = 'dodysw+tikikuli%s@gmail.com' % random.randint(100000,999999)
AUTHOR = 'Dody Suria Wijaya <dodysw@gmail.com>'
MAX_LOCAL_FILE = 70 # maximum local file to send, 0 for no limitation, in MB.
#Note SMTP server usually has maximum message size around 100 million byte, or about 72 MB of attachment(s) (thats before base64 encoding, evelopes, and headers)

#SPLIT_FILE_LARGER_THAN = 50
USER_AGENT = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)'
READ_BUFFER = 1024*16
FAKE_EXTENSION = '.jpg'
CRLF='\r\n'
BUFFER_LINES = 500 # send this lines at a time through SMTP server's DATA

import urllib2, sys, tempfile, time, os, base64, urlparse, smtplib, mimetypes, re

try:
    import web2email_config as cfg
    SMTP_SERVER = cfg.smtp_server
except ImportError:
    SMTP_SERVER = 'localhost'

# globals
job_id = str(int(time.time()))
temp_dir = ''

class EOF(Exception): pass
class MaxTryExceeded(Exception): pass
class MaxLocalFileExceeded(Exception): pass
class LocalFileNotExists(Exception): pass

#string definitions

notify_body = \
"""
I have received a request to download:

%(urls)s

This job id is: %(job_id)s
I will send you back the file(s) in a moment. Thank you.
--
    tikikuli by %(author)s
"""

finish_download_body = \
"""
I have finished downloading job id %(job_id)s.
Here are the download logs:

%(urls)s

Enjoy!
--
    tikikuli by %(author)s"""

header = \
"""Content-Type: multipart/mixed; boundary="===============1755112047=="
MIME-Version: 1.0
Subject: %(subject)s
From: %(mail_from)s
To: %(mail_to)s

--===============1755112047==
Content-Type: text/plain; charset="us-ascii"
MIME-Version: 1.0
Content-Transfer-Encoding: 7bit

%(body)s
"""

sub_header = \
"""
--===============1755112047==
Content-Type: %(maintype)s
MIME-Version: 1.0
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="%(filename)s"

"""

sub_footer = \
"""
--===============1755112047==--
"""



def getfile(url, referer=None, *params, **kwargs):
    """urllib.urlopen with configurable retry"""
    for i in range(0,MAX_TRY):
        try:
            req = urllib2.Request(url, *params, **kwargs)
            req.add_header('User-Agent', USER_AGENT)
            if referer:
                req.add_header('Referer', referer)
            resp = urllib2.urlopen(req)
            return resp
        except urllib2.URLError, e:
            last_error = str(e)
            continue
    raise MaxTryExceeded, last_error

def send_simple_email(to, from_addr='anonymous', subject='', body='', smtp_server=SMTP_SERVER):
    # send email without attachments
    from email.MIMEText import MIMEText
    msg = MIMEText(body)
    del body    # save RAM
    msg['Subject'], msg['From'], msg['To'] = subject, from_addr, to
    s = smtplib.SMTP(smtp_server)
    s.sendmail(from_addr, [to], msg.as_string())
    s.close()

class FileWrapper:
    def __init__(self, fh):
        self.fh = fh
    def __len__(self):
        return int(os.path.getsize(self.fh.name))

class StreamingSMTP(smtplib.SMTP):
    leading_dot = re.compile(r'(?m)^\.')
    proper_eol = re.compile(r'(?:\r\n|\n|\r(?!\n))')
    verbose = False

    def data(self, fhwrapper):
        if self.verbose: print 'Sending DATA...'
        self.putcmd("data")
        (code,repl)=self.getreply()
        if self.debuglevel >0 : print>>stderr, "data:", (code,repl)
        if code != 354:
            raise SMTPDataError(code,repl)

        finish = False
        while not finish:
            buffer = []
            try:
                for i in xrange(BUFFER_LINES):
                    line = fhwrapper.fh.readline()
                    if not line: raise EOF
                    buffer.append(line)
            except EOF:
                finish = True
            if buffer:
                q = self.leading_dot.sub('..', self.proper_eol.sub(CRLF, ''.join(buffer)))
                self.send(q)

        # final bit of data termination
        r = ''
        if q[-2:] != CRLF:
            r += CRLF
        r += "." + CRLF
        self.send(r)
        (code,msg)=self.getreply()
        if self.debuglevel >0 : print>>stderr, "data:", (code,msg)
        return (code,msg)

def send_email(to, from_addr='anonymous', subject='', body='', atch=None, atch_compressed=False, fake=False, smtp_server=SMTP_SERVER, verbose=False):
    # send email with attachment

    if atch == None:
        if verbose: print 'No attachment, sending simple email'
        return send_simple_email(to, from_addr=from_addr, subject=subject, body=body, smtp_server=smtp_server)

    if atch_compressed:
        try:
            import tarfile
        except ImportError:
            atch_compressed = False

    #======
    # output email to file to conserve ram
    temp_email_file = os.path.join(temp_dir,'email.b64')
    fhmail = file(temp_email_file,'w+')
    fhmail.write(header % dict(subject=subject, mail_from=from_addr, mail_to=to, body=body))

    def encode_file(email_filename, file_path):
        if fake:
            email_filename += FAKE_EXTENSION
        maintype, encoding = mimetypes.guess_type(email_filename)
        if not maintype:
            maintype = 'application/octet-stream'
        fhmail.write(sub_header % {'maintype':maintype, 'filename':email_filename})
        if verbose: print 'Converting+writing base64 to "%s" ...' % temp_email_file
        base64.encode(file(file_path,'rb'),fhmail)

    if atch_compressed:  # compress all files into one tar bzip
        fd, tar_filename = tempfile.mkstemp('.tar.bz2')
        os.close(fd)
        fh = tarfile.open(tar_filename,'w:bz2')
        for u in atch:
            if u.is_success:
                fh.add(u.temp_file_path, u.filename)
        fh.close()

        encode_file(os.path.basename(tar_filename), tar_filename)
        os.unlink(tar_filename) # delete temporary tar file

    else:   #put each file on a new message payload
        for u in atch:
            if u.is_success:
                encode_file(u.filename, u.temp_file_path)

    fhmail.write(sub_footer)
    fhmail.flush()

    try_count = 1
    while try_count <= MAX_TRY:
        fhmail.seek(0)
        if verbose: print 'Contacting SMTP server at...', smtp_server
        s = StreamingSMTP(smtp_server)
        s.verbose = verbose
        try:
            if verbose: print 'Sending envelope... (from:%s, to:%s)' % (from_addr, to)
            s.sendmail(from_addr, [to], FileWrapper(fhmail))
            s.close()
            break
        except smtplib.SMTPResponseException, e:
            if verbose: print 'SMTP Error: "%s". Retrying...' % str(e)
            if verbose: print 'Disconnecting SMTP server'
            s.close()
            time.sleep(1)
        try_count += 1

    fhmail.close()
    try:
        if verbose: print 'Deleting base64 file at "%s"' % temp_email_file
        os.unlink(temp_email_file)
    except OSError: pass

    if try_count > MAX_TRY:
        raise MaxTryExceeded, "Max try exceed when sending through SMTP"

class UrlGetData:
    delete_tempfile = True
    def __init__(self, url, real_url=None, filename=None, is_success=True, error_msg='OK', handle=None, content_length=None, content_type=None, prefix_filename=None):
        self.url = url

        #if HTTP redirected, handle.url could be != url
        self.real_url = real_url is None \
                and (handle is not None and handle.url or url) \
                or real_url

        # http://sub.domain.com/ => (wihtout path) will cause empty string filename ''. We assume its name is something else.
        self.filename = filename is None \
                and (os.path.basename(urlparse.urlparse(self.real_url)[2]) or 'index.html') \
                or filename

        if prefix_filename:
            self.filename = prefix_filename + self.filename

        # for non-local file, prepare temporary file (and close file descriptor since we wont be needing it)
        # for optimization, local file's temporary file is its "url" path. can directly use the url
        if self.is_local():
            tfp = urlparse.urlparse(self.real_url)[2]
            self.delete_tempfile = False
        else:
            fd, tfp = tempfile.mkstemp()
            os.close(fd)
        self.temp_file_path = tfp

        self.content_length = int(content_length is None \
                and (handle is not None and handle.headers.get('content-length',-1) or -1) \
                or content_length)
        self.content_type = content_type is None \
                and (handle is not None and handle.headers.get('content-type','Unknown/Type') or 'Unknown/Type') \
                or content_type

        self.is_success = is_success
        self.error_msg = error_msg
        self.handle = handle        # file-like handler

    def is_local(self):
        return self.url.startswith('file:')

def main(email, url_list, compressed=False, notify=False, fake=False, verbose=False, smtp_server=SMTP_SERVER):
    global temp_dir
    temp_dir = tempfile.mkdtemp()   # prepare temporary folder

    #=========
    # first of all, build list of UrlGetData instance, which quickly indicate the availability
    # of the intended resource and its related metadata in optimized way.
    #=========
    result_list = []
    referer = prefix_filename = None
    for url in url_list:
        if url.startswith('r='):    # referer!
            referer = url[2:]
        elif url.startswith('p='):  # prefix filename!
            prefix_filename = url[2:]
        else:
            try:
                if '://' not in url:        # assume local file
                    url = 'file:' + url
                if url.startswith('file:'):
                    if not os.path.exists(url[5:]):
                        raise LocalFileNotExists
                    # check it's size first
                    file_size = os.path.getsize(url[5:]) / (1024.0 * 1024.0)
                    if file_size > MAX_LOCAL_FILE:
                        raise ExceedMaxLocalFile

                if verbose: print 'Contacting "%s"' % url
                fh = getfile(url, referer=referer)
                o = UrlGetData(url, is_success=True, handle=fh, prefix_filename=prefix_filename)
            except MaxTryExceeded, e:
                o = UrlGetData(url, is_success=False, error_msg=str(e), prefix_filename=prefix_filename)
            except MaxLocalFileExceeded:
                error_msg = "File size %0.2f MB is larger than maximum local file" % file_size
                o = UrlGetData(url, is_success=False, error_msg=error_msg, prefix_filename=prefix_filename)
            except LocalFileNotExists:
                if verbose: print 'File "%s" does not exist' % url[5:]
                error_msg = "Local file does not exists"
                o = UrlGetData(url, is_success=False, error_msg=error_msg, prefix_filename=prefix_filename)

            result_list.append(o)

    #=========
    # send an email (if asked) indicating the intention (and likely early result) of operation
    # this provides a quick feedback of successful request from user (although not always sucessful result)
    #=========
    if notify:
        url_info = []
        # build list of rough metadata string per URL
        for u in result_list:
            if u.is_success:
                s = '- %s\n   Size: %0.2f KB\n   Type: %s\n   URL: %s%s' % (
                    u.filename, u.content_length/1024.0  #in KB
                    ,u.content_type, u.url, (u.url != u.real_url) and "\n   Redirected: %s" % u.real_url or '')
            else:
                s = '- %s\n   Problem: %s' % (u.url, u.error_msg)
            url_info.append(s)

        send_email(to=email, from_addr=FROM_ADDR, subject='%s - getting %s url(s)' % (job_id, len(result_list))
            ,body=notify_body % {'urls': '\n'.join(url_info), 'author': AUTHOR, 'job_id': job_id}, smtp_server=smtp_server)

    # dowload url (if needed) to temporary file
    for u in result_list:
        if not u.is_local() and u.is_success:
            fh_temp = file(u.temp_file_path,'wb')
            while 1:
                buffer = u.handle.read(READ_BUFFER)
                if not buffer: break
                fh_temp.write(buffer)
            u.handle.close()
            fh_temp.close()

    body_url = '\n\n'.join(
                ['File: %s\nURI: %s\n%sResult: %s\nNote: %s' % (\
                u.filename
                ,u.url
                ,(u.url != u.real_url) and "   Redirected: %s\n" % u.real_url or ""
                ,u.is_success and 'Success' or 'Fail'
                ,u.error_msg)
                for u in result_list])
    send_email(to=email, from_addr=FROM_ADDR, subject='%s - finish' % job_id
        ,body=finish_download_body % {'urls':body_url, 'author':AUTHOR, 'job_id':job_id}
        ,atch=result_list, atch_compressed=compressed, fake=fake, verbose=verbose, smtp_server=smtp_server)

    # delete temporary file and folder
    for u in result_list:
        if u.is_success and not u.is_local() and u.delete_tempfile:
            if verbose: print 'Deleting temporary download file at "%s"' % u.temp_file_path
            os.unlink(u.temp_file_path)
    try:
        if verbose: print 'Removing temporary directory at "%s"' % temp_dir
        os.rmdir(temp_dir)
    except OSError: pass

if __name__ == '__main__':
    import optparse
    usage = 'usage: %s [options] <email> <url1> [url2] [url3] ...' % sys.argv[0]
    parser = optparse.OptionParser(usage)
    parser.add_option("-c", "--compress", action="store_true", dest="compress", help="compress file to tar.bz2", default=False)
    parser.add_option("-n", "--notify", action="store_true", dest="notify", help="notify download intention to target email", default=False)
    parser.add_option("-k", "--fake", action="store_true", dest="fake", help="fake attachment extension to .jpg", default=False)
    parser.add_option("--version", action="store_true", dest="version", help="display version and return", default=False)
    parser.add_option("-v", action="store_true", dest="verbose", help="verbose mode", default=False)
    parser.add_option( "--smtp-addr", dest="smtp_addr", help="Change default smtp server to send to", default=SMTP_SERVER)
    #~ parser.add_option("-i", "--stdin", action="store_true", dest="stdin", help="get url from stdin", default=False)
    options, args = parser.parse_args()
    if options.version:
        print __version__
        sys.exit()
    elif len(args) <= 1:
        parser.error("incorrect number of arguments")
    email, url = args[0], args[1:]
    main(email, url, compressed=options.compress, notify=options.notify, fake=options.fake, verbose=options.verbose, smtp_server=options.smtp_addr)
