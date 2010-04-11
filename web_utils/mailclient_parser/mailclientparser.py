import sys,os
if len(sys.argv) <= 1:
    print 'usage:',os.path.basename(sys.argv[0]),' mailboxfile'
    sys.exit()
mailclients = {}
mailcount = 0
mailcount2 = 0
ii = 0
for line in file(sys.argv[1],'rb'):
    ii += 1
    #~ if line[0:10] == 'User-Agent':
    if line[0:5].lower() == 'from ':
        mailcount += 1
    if line[0:10].lower() == 'user-agent' or line[0:8].lower() == 'x-mailer':
        mailcount2 += 1
        try:
            temp = line.split(':',2)[1].strip()
        except IndexError,e:
            print >> sys.stderr, 'Index Error:',line
            sys.exit()
        mailclients.setdefault(temp,0)
        mailclients[temp] += 1
print >> sys.stderr, "Total mail:", mailcount
print >> sys.stderr, "Total w/ user agent:", mailcount2
print >> sys.stderr, "Total lines:", ii, line
for line in mailclients:
    l1 = line
    l2 = str(mailclients[line])
    if ',' in l1:
        l1 = '"'+l1+'"'
    if ',' in l2:
        l2 = '"'+l2+'"'
    print '%s,%s' % (l1,l2)
