"""
pyfetchsquirrel
trigger fetch function under squirrelmail, just by supplying user and password
"""
visit_inbox = True  # True, to visit inbox to trigger message filtering
url1 = 'https://sqmail.anu.edu.au/squirrelmail/src/redirect.php'
url2 = 'http://sqmail.anu.edu.au/squirrelmail/plugins/mail_fetch/fetch.php'
url3 = 'http://sqmail.anu.edu.au/squirrelmail/src/right_main.php?mailbox=INBOX'

import urllib, urllib2, cookielib, sys

def trigger_fetch(username, password):
    cj = cookielib.CookieJar()
    opener = urllib2.build_opener(urllib2.HTTPCookieProcessor(cj))
    data = urllib.urlencode(dict(login_username=username,secretkey=password,js_autodetect_results=0,just_logged_in=1))
    r = opener.open(url1, data)
    if 'password incorrect' in r.read():
        print 'Sorry. User or password is incorrect.'
        return False

    if visit_inbox:
        print 'Visiting inbox...'
        r = opener.open(url3)
        r.read()

    print 'Please wait, fetching....'
    data = urllib.urlencode(dict(server_to_fetch='all'))
    r = opener.open(url2, data)
    print r.read()
    #~ while 1:
        #~ buff = resp.read(5)
        #~ if buff == '': break
        #~ print buff
    return True

if __name__ == '__main__':
    import getpass
    if len(sys.argv) < 1:
        print 'format:\n%s username password' % sys.argv[0]
        sys.exit()
    if len(sys.argv) == 1:
        username = ''
        while username == '':
            username = raw_input('Username:')
        password = getpass.getpass()
    elif len(sys.argv) == 2:
        username = sys.argv[1]
        password = getpass.getpass()
    else:
        username, password = sys.argv[1:3]
    if trigger_fetch(username, password):
        print 'Sucess'
    else:
        print 'Fail'