#!/usr/bin/python
"""
tikikuli.py - download given url(s) then send them via email as file attachments

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
"""

__version__ = '1.4.2'
max_try = 3
from_addr = 'dodysw@gmail.com'
author = 'Dody Suria Wijaya <dodysw@gmail.com>'


import urllib2, sys, tempfile, time, os, base64

"""globals"""
job_id = str(int(time.time()))
path_dir = ''


"""string definitions"""

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

try:
    import web2email_config as cfg
    smtp_server = cfg.smtp_server
except ImportError:
    smtp_server = 'localhost'

class MaxTryExceeded(Exception):
    pass

def getfile(*params, **kargs):
    """urllib.urlopen with configurable retry"""
    for i in range(0,max_try):
        try:
            req = urllib2.Request(*params)
            resp = urllib2.urlopen(req)
            return resp
        except urllib2.URLError, e:
            last_error = str(e)
            continue
    raise MaxTryExceeded, last_error

import smtplib, mimetypes
from email.MIMEText import MIMEText

def send_simple_email(to, from_addr='ananymouse', subject='', body='', smtp_server=smtp_server):
    """send email without attachments
    """
    msg = MIMEText(body)
    del body    # save RAM
    msg['Subject'], msg['From'], msg['To'] = subject, from_addr, to
    s = smtplib.SMTP(smtp_server)
    s.sendmail(from_addr, [to], msg.as_string())
    s.close()

def send_email(to, from_addr='ananymouse', subject='', body='', att=None, att_compressed=False, fake=False, smtp_server=smtp_server):
    if att == None:
        return send_simple_email(to, from_addr=from_addr, subject=subject, body=body, smtp_server=smtp_server)

    fake_extension = '.jpg'

    temp_email_file = os.path.join(path_dir,'email.b64')
    fhmail = file(temp_email_file,'w+')
    fhmail.write(header % dict(subject=subject, mail_from=from_addr, mail_to=to, body=body))

    try:
        import tarfile
    except ImportError:
        att_compressed = False

    if att_compressed:  # compress all files into one tar bzip
        import tempfile
        os_handle, tar_filename = tempfile.mkstemp('.tar.bz2')
        fh = tarfile.open(tar_filename,'w:bz2')
        for row in att:
            if not row['is_success']: continue
            fh.add(row['temp_file_path'], os.path.basename(row['real_url']))
        fh.close()

        email_filename = os.path.basename(tar_filename)
        if fake:
            maintype, encoding = mimetypes.guess_type(tar_filename + fake_extension)
            email_filename += fake_extension
        else:
            maintype, encoding = mimetypes.guess_type(tar_filename)
            if not maintype:
                maintype = 'application/octet-stream'
        fhmail.write(sub_header % dict(maintype=maintype, filename=email_filename))
        base64.encode(file(tar_filename,'rb'),fhmail)
        fhmail.write(sub_footer)
        try: os.unlink(tar_filename) # delete temporary tar file
        except OSError: pass        # sometimes it's failed. dunno why.

    else:   #put each file on a new message payload
        for row in att:
            if not row['is_success']: continue
            email_filename = os.path.basename(row['real_url'])
            if fake:
                maintype, encoding = mimetypes.guess_type(row['temp_file_path'] + fake_extension)
                email_filename += fake_extension
            else:
                maintype, encoding = mimetypes.guess_type(row['temp_file_path'])
                if not maintype:
                    maintype = 'application/octet-stream'
            fhmail.write(sub_header % dict(maintype=maintype, filename=email_filename))
            base64.encode(file(row['temp_file_path'],'rb'),fhmail)
        fhmail.write(sub_footer)

    fhmail.flush()
    fhmail.seek(0)
    msg_as_string = fhmail.read()
    try: os.unlink(temp_email_file)
    except OSError: pass

    s = smtplib.SMTP(smtp_server)
    s.sendmail(from_addr, [to], msg_as_string)
    s.close()

def main(email, url_list, compressed=False, notify=False, fake=False):
    global path_dir
    path_dir = tempfile.mkdtemp()   # prepare temporary folder
    result_list = []
    for url in url_list:
        if '://' not in url:    # local file, don't create copy
            if not os.path.exists(url): raise Exception, 'path %s not exists' % url
            result_list.append(dict(
                is_success = True
                ,error_msg = 'OK'
                ,url = url
                ,real_url = url
                ,temp_file_path = url
                ,dont_delete = True
                ,content_length = os.path.getsize(url)
                ,content_type = mimetypes.guess_type(url)[0]
                ))
        else:
            filename = os.path.basename(url)                   # decide filename from url
            temp_file_path = os.path.join(path_dir,filename)
            i = 1
            while os.path.exists(temp_file_path):           # make sure it's not exist
                temp_file_path = os.path.join(path_dir,filename+'.%d'%i)
                i += 1

            try:
                fh = getfile(url)
            except MaxTryExceeded, e:
                result_list.append(dict(
                    is_success = False
                    ,error_msg = str(e)
                    ,url = url
                    ,real_url = url
                    ,temp_file_path = temp_file_path
                    ,dont_delete = True
                    ))
                continue
            result_list.append(dict(
                is_success = True
                ,error_msg = 'OK'
                ,url = url
                ,real_url = fh.url                  # if redirected, fh.url != url
                ,temp_file_path = temp_file_path
                ,dont_delete = False
                ,content_length = fh.headers.get('content-length')
                ,content_type = fh.headers.get('content-type')
                ,
                ))

            if not notify:  # if notification is wished, download later after sending emails
                #download file(s) to temporary files to conserve ram
                fh_temp = file(temp_file_path,'wb')
                while 1:
                    buffer = fh.read(10000)
                    if buffer == '':
                        break
                    fh_temp.write(buffer)
                fh.close()
                fh_temp.close()


    if notify:
        # send preliminiary email with size/filetype info

        url_info = []
        for row in result_list:
            if row['is_success']:
                if row['content_length'] in (None, ''):
                    row['content_length'] = -1
                url_info.append(
                    '- %s\n   Size: %0.2f KB\n   Type: %s%s' % (
                    row['url']
                    ,int(row['content_length'])/1024.0  #in KB
                    ,row['content_type']
                    ,(row['url'] != row['real_url']) and "\n   Redirected: %s" % row['real_url'] or ""))
            else:
                url_info.append('- %s\n   Problem: %s' % (row['url'],row['error_msg']))

        send_email(
            to = email
            ,from_addr = from_addr
            ,subject = '%s - downloading %s url(s)' % (job_id, len(result_list))
            ,body = notify_body % dict(
                urls = '\n'.join(url_info)
                ,author = author
                ,job_id = job_id
                )
            )
        for row in result_list:
            if '://' in row['url'] and row['is_success']:
                try:
                    fh = getfile(row['url'])
                except MaxTryExceeded, e:
                    row['is_success'] = False
                    row['error_msg'] = str(e)
                    continue
                fh_temp = file(row['temp_file_path'],'wb')
                while 1:
                    buffer = fh.read(10000)
                    if buffer == '':
                        break
                    fh_temp.write(buffer)
                fh.close()
                fh_temp.close()

    send_email(
        to = email
        ,from_addr = from_addr
        ,subject = '%s - finish' % job_id
        ,body = finish_download_body % dict(
            urls = '\n\n'.join(
                ['File: %s\nURI: %s\nResult: %s\nNote: %s' % \
                (os.path.basename(row['url'])
                ,row['url']
                ,row['is_success'] and 'Success' or 'Fail'
                ,row['error_msg'])
                for row in result_list])
            ,author = author
            ,job_id = job_id
            )
        ,att = result_list
        ,att_compressed = compressed
        ,fake = fake
        )

    # delete temporary file and folder
    for row in result_list:
        if not row['is_success'] or row['dont_delete']: continue
        os.unlink(row['temp_file_path'])
    try:
        os.rmdir(path_dir)
    except OSError: pass

if __name__ == '__main__':
    import optparse
    usage = 'usage: %s [options] <email> <url1> [url2] [url3] ...' % sys.argv[0]
    parser = optparse.OptionParser(usage)
    parser.add_option("-c", "--compress", action="store_true", dest="compress", help="compress file to tar.bz2", default=False)
    parser.add_option("-n", "--notify", action="store_true", dest="notify", help="notify download intention to target email", default=False)
    parser.add_option("-k", "--fake", action="store_true", dest="fake", help="fake attachment extension to .jpg", default=False)
    parser.add_option("-v", "--version", action="store_true", dest="version", help="display version and return", default=False)
    #~ parser.add_option("-i", "--stdin", action="store_true", dest="stdin", help="get url from stdin", default=False)
    options, args = parser.parse_args()
    if options.version:
        print __version__
        sys.exit()
    elif len(args) <= 1:
        parser.error("incorrect number of arguments")
    email, url = args[0], args[1:]
    main(email, url, compressed=options.compress, notify=options.notify, fake=options.fake)
    raw_input('Paused...')