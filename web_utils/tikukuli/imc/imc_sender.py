import smtplib

KEY_SUBJECT = '/./././,/,/,'
EMAIL_FROM = 'dodysw@gmail.com'
EMAIL_TO = 'dody@miaw.tcom.ou.edu'
SMTP_SERVER = 'smtphost'

def main():
    urls = []
    while 1:
        url = raw_input('url:')
        if url == '.':
            headers = '\r\n'.join(['From: %s' % EMAIL_FROM, 'To: %s' % EMAIL_TO, 'Subject: %s' % KEY_SUBJECT])
            body = '\r\n'.join(urls)
            s = smtplib.SMTP(SMTP_SERVER)
            s.sendmail(EMAIL_FROM, EMAIL_TO, '%s\r\n\r\n%s' % (headers, body))
            s.close()
            urls = []
        elif url == 'q':
            import sys
            sys.exit()
        else:
            urls.append(url)

if __name__ == '__main__':
    print '"." to send urls, "q" to quit'
    main()
