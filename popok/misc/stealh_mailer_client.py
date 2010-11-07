import urllib

def stealh_mail(from='',to='',subject='',replyto='',cc='',body=''):
    SILUMAN_PASSWORD = 'XXXXXXXXXXXX'
    SILUMAN_URL = http://sipeha.com.11.hostcorporate.com/sms/stealthmailer.php
    data = urllib.urlencode(
        {
        'p':siluman_pass,
        'from':self.emailaddress,
        'body':body,
        'header':header,
        'usemyheader':'1',
        'to':to,
        'subject':subject,
        'replyto':replyto,
        'cc':cc,
        #~ 'bcc':msg.get('Bcc','') # this would never happen, since Bcc field only append at RCPT TO
        }
        )
    if __debug__: print "Get %s Data %s" % (siluman_url.__repr__(),data.__repr__())
    fh = urllib.urlopen(siluman_url,data)
    ret = fh.read()
    if ret == '1':
        return True
    else:
        return False