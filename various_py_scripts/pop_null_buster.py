# POP tikikuli monitor
import getpass, sys, string, poplib

POP_SERVER = 'localhost'
POP_USERNAME = 'dody.wijaya'
try:
    import pop_null_config
    POP_PASSWORD = pop_null_config.data
except ImportError:
    POP_PASSWORD = getpass.getpass('Password for %s:' % POP_USERNAME)

def main():
    delcount = 0
    M = poplib.POP3(POP_SERVER)
    M.user(POP_USERNAME)
    M.pass_(POP_PASSWORD)
    numMessages = len(M.list()[1])
    #~ print "mailbox has", numMessages, "emails"
    for i in range(numMessages):
        has_null = False
        for line_num, line in enumerate(M.retr(i+1)[1]):
            if chr(0) in line:
                #~ print 'email #%s @ line#%s has null:%s' % (i+1, line_num, line)
                # delete email
                M.dele(i+1)
                delcount += 1
                has_null = True
                break
        #~ sys.stdout.write(has_null and "D" or ".")
    M.quit()
    #~ if delcount:
        #~ print "deleted %s emails" % delcount

if __name__ == '__main__':
    main()