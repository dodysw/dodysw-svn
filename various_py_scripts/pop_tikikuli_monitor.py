# imap tikikuli monitor

import getpass, poplib
# M = imaplib.IMAP4_SSL('anumail.anu.edu.au')
M = poplib.POP3_SSL('anumail.anu.edu.au')
username = 'UUUUUUUU'
password = 'PPPPPPPP'
M.user(username)
M.pass_(password)
numMessages = len(M.list()[1])
for i in range(numMessages):
    for j in M.retr(i+1)[1]:
        print j

M.quit()
