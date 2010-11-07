# imap tikikuli monitor
import getpass
import imaplib, email, os, base64, time
M = imaplib.IMAP4_SSL('anumail.anu.edu.au')
username = 'dodysw'
password = getpass.getpass()
M.login(username,password)
while 1:
    M.select()
    print "Checking..."
    typ, data = M.search(None, '(FROM "dodysw@gmail.com" SUBJECT "- finish")')
    for num in data[0].split():
        typ, data = M.fetch(num, '(RFC822)')
        # parse to get
        msg = email.message_from_string(data[0][1])
        del data
        if msg.is_multipart():
            for payload in msg.get_payload():
                if payload.get_content_type() == 'text/plain':
                    continue
                #~ print payload.get_content_type()
                filename = payload.get_filename()
                if filename:
                    if filename[-4:].lower() == '.jpg':
                        filename = filename[:-4]
                    print filename
                    # makesure not to overwrite file
                    i = 1
                    test_filename = filename
                    while os.path.exists(test_filename):
                        test_filename = '%s.%d' % (test_filename,i)
                        i += 1
                    filename = test_filename
                    # save to file
                    file('result/'+filename, 'wb').write(base64.b64decode(payload.get_payload()))
                    # delete email
                    M.store(num, '+FLAGS', '\\Deleted')
                    M.expunge()
        del msg
    time.sleep(10)
M.close()
M.logout()
