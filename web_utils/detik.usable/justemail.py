#!/usr/bin/python
import ftplib
import os
import sys
import traceback
import urllib

hostname = 'miaw.tcom.ou.edu'
username = 'dody'
password = 'letmein'
notsure = ''
change_lines = ''
while notsure != 'y':
    version = raw_input("Release version (ie 2.45):")
    change_line = raw_input("Enter version changes (end with .):\n")
    while change_line != '.':
        change_lines += change_line + '\n'
        change_line = raw_input()

    print 'Will release version %s with this body:\n%s' % (version, change_lines)
    notsure = raw_input('Continue (y/n) ?').lower().strip()
    if notsure == 'y':
        break

if change_lines.strip() == '':
    change_lines = 'Too lazy to write'


def stealh_mail(vrom='', to='', subject='', replyto='', cc='', body=''):
    SILUMAN_URL = 'http://miaw.tcom.ou.edu/~dody/private/stealthmailer.php'
    SILUMAN_PASSWORD = 'xxx'
    data = urllib.urlencode(
        {
        'p':SILUMAN_PASSWORD,
        'from':vrom,
        'body':body,
        #~ 'header':header,
        #~ 'usemyheader':'1',
        'to':to,
        'subject':subject,
        'replyto':replyto,
        'cc':cc,
        #~ 'bcc':msg.get('Bcc','') # this would never happen, since Bcc field only append at RCPT TO
        }
        )
    if __debug__: print "Get %s Data %s" % (SILUMAN_URL.__repr__(),data.__repr__())
    fh = urllib.urlopen(SILUMAN_URL,data)
    ret = fh.read()
    if ret == '1':
        return True
    else:
        print ret
        return False

if __name__ == '__main__':
    print "Sending notification to mailing list",
    mail_body = """\
Pengumuman,

Detik.Usable versi terakhir barusan diupload ke server.

    Versi: %s
    URL:   http://miaw.tcom.ou.edu/~dody/du/%s
    Perubahan: %s

Update bisa juga dilakukan dengan memilih "Check Update" di Detik.Usable
masing-masing.

Ciaw!
--
  Robot-nya Dody
""" % (version, 'detikusable-%s.php.txt' % version, change_lines)
    stealh_mail(vrom='Robot-nya Dody <dodysw@gmail.com>', to='detikusable@googlegroups.com', subject='%s uploaded' % version, body=mail_body)
    raw_input("Done. Press Enter...")
