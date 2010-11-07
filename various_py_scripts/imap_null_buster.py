# imap tikikuli monitor
import getpass, imaplib, email, os, base64, time, sys, string

IMAP_SERVER = 'localhost'
IMAP_USERNAME = 'dody.wijaya'
IMAP_PASSWORD = getpass.getpass('Password for %s:' % IMAP_USERNAME)

invalid_chars = r':/\*?"<>|' # win32
table = string.maketrans(invalid_chars, ' '*len(invalid_chars))

def main():
    M = imaplib.IMAP4_SSL(IMAP_SERVER)
    M.login(IMAP_USERNAME, IMAP_PASSWORD)
    M.select()
    typ, imap_emails = M.search(None, 'ALL')
    for num in imap_emails[0].split():
        sys.stdout.write(".")
        typ, data = M.fetch(num, '(RFC822)')        # data could be BIG +60MB
        msg = email.message_from_string(data[0][1]) # msg could be BIG +60MB (StringIO) +60MB internal data = 180MB
        file("output.txt","wb").write(data[0][1])
        break
        if chr(0) in data[0][1]:
            print 'HAS NULL: Msg subject:',msg['subject']
            #~ # delete email
            #~ M.store(num, '+FLAGS', '\\Deleted')
            #~ M.expunge()
    M.close()
    M.logout()

if __name__ == '__main__':
    main()