"""
pyfetchsquirrel
trigger fetch function under squirrelmail, just by supplying user and password
"""
visit_inbox = True  # True, to visit inbox to trigger message filtering
url1 = 'https://sqmail.anu.edu.au/squirrelmail/src/redirect.php'
url2 = 'http://sqmail.anu.edu.au/squirrelmail/plugins/mail_fetch/fetch.php'
url3 = 'http://sqmail.anu.edu.au/squirrelmail/src/right_main.php?mailbox=INBOX'

import ClientCookie as cc
import urllib, sys

def trigger_fetch(username, password):
    data = urllib.urlencode(dict(login_username=username,secretkey=password,js_autodetect_results=0,just_logged_in=1))
    req = cc.Request(url1, data)
    resp = cc.urlopen(req)
    if 'password incorrect' in resp.read():
        print 'Sorry. User or password is incorrect.'
        return False

    if visit_inbox:
        print 'Visiting inbox...'
        req = cc.Request(url3)
        resp = cc.urlopen(req)
        resp.read()

    print 'Please wait, fetching....'
    data = urllib.urlencode(dict(server_to_fetch='all'))
    req = cc.Request(url2, data)
    resp = cc.urlopen(req)
    print resp.read()
    #~ while 1:
        #~ buff = resp.read(5)
        #~ if buff == '': break
        #~ print buff
    return True

if __name__ == '__main__':
    if len(sys.argv) < 3:
        print 'format:\n%s username password' % sys.argv[0]
        sys.exit()
    username, password = sys.argv[1:3]
    if trigger_fetch(username, password):
        print 'Sucess'
    else:
        print 'Fail'