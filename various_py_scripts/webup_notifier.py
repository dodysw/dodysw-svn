import urllib2 as u
import email, time, smtplib

SMTP_SERVER = 'smtphost.anu.edu.au'
WEBSITE = 'http://www.google.com'
EMAIL_FROM = 'dodysw@gmail.com'
EMAIL_TO = 'dodysw@gmail.com'
SUBJECT = '%s is now up!' % WEBSITE

def beep():
    try:
        import winsound
        winsound.Beep(1200,200)
    except ImportError:
        pass

def sendmail():
    # send mail
    server = smtplib.SMTP(SMTP_SERVER)
    server.sendmail(EMAIL_FROM, EMAIL_TO, "From:%s\r\nSubject:%s\r\nTo:%s\r\n\r\n" % (EMAIL_FROM, SUBJECT, EMAIL_TO))
    server.quit()

if __name__ == '__main__':
    while 1:
        try:
            print 'Checking %s...' % WEBSITE,
            u.urlopen(WEBSITE)
            sendmail()
            beep()
            print 'on!'
        except u.URLError:
            print 'out'
            pass
        time.sleep(60)