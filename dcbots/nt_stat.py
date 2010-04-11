
import cPickle, socket, dclib
import MySQLdb as dbm

from trackerfunc import *

MYSQL_CONNECT_PARAM = dict(host='localhost', user='whatsnew_dcbot', passwd='', db='whatsnew_dcbot')
#~ DB_FILE = 'nh.dat'
DB_FILE = 'nicktracker_hist.dat'

cached_hostnames = {}
def gethostname(ip):
    global cached_hostnames
    try:
        return cached_hostnames[ip]
    except KeyError:
        try:
            cached_hostnames[ip] = socket.gethostbyaddr(ip)[0]
        except socket.herror:
            cached_hostnames[ip] ='unknown hostname'
        return cached_hostnames[ip]

def toptth(num=20):
    print "Top %d tth search" % num
    alltth = {}
    for ip in db:
        for nick in db[ip]:
            tthsearches = db[ip][nick].get('tthsearch', [])
            for tth in tthsearches:
                alltth.setdefault(tth, 0)
                alltth[tth] += 1
    alltth = zip(alltth.values(), alltth.keys())
    alltth.sort(reverse=True)
    dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
    cur = dbcon.cursor()
    for i, (count, tth) in enumerate(alltth[:num]):
        if cur.execute('select share_path, size from shares where tth=%s', (tth,)):
            share_path,size = cur.fetchone()
            print "%d. %s (%0.2f MB) = %d downloads" % (i+1, share_path, size/(1024.0*1024.0), count)
        else:
            print "%d. %s = %d downloads" % (i+1, tth, count)

def topsearch(num=20):
    print "Top %d search phrase" % num
    alltth = {}
    for ip in db:
        for nick in db[ip]:
            bysamenick = {}
            searches = db[ip][nick].get('search', [])
            for tth in searches:
                alltth.setdefault(tth, 0)
                if tth in bysamenick:
                    continue
                alltth[tth] += 1
                bysamenick[tth] = 1
    alltth = zip(alltth.values(), alltth.keys())
    alltth.sort(reverse=True)
    for i, (count, tth) in enumerate(alltth[:num]):
        print "%d. %s = %d searches" % (i+1, tth, count)

def topsearchword(num=20):
    print "Top %d search word" % num
    alltth = {}
    for ip in db:
        for nick in db[ip]:
            bysamenick = {}
            searches = db[ip][nick].get('search', [])
            for tth in searches:
                for word in tth.split():
                    alltth.setdefault(word, 0)
                    if word in bysamenick:
                        continue
                    alltth[word] += 1
                    #~ bysamenick[word] = 1
    alltth = zip(alltth.values(), alltth.keys())
    alltth.sort(reverse=True)
    for i, (count, tth) in enumerate(alltth[:num]):
        print "%d. %s = %d searches" % (i+1, tth, count)

def topsharesize(num=20):
    print "Top %d nick by share size" % num
    alltth = {}
    for ip in db:
        for nick in db[ip]:
            sharesize = db[ip][nick].get('sharesize', 0)
            alltth.setdefault(nick,0)
            alltth[nick] = max(sharesize, alltth.get(nick,0))
    alltth = zip(alltth.values(), alltth.keys())
    alltth.sort(reverse=True)
    for i, (size, nick) in enumerate(alltth[:num]):
        print "%d. %s = %0.2f GB" % (i+1, nick, size/(1024.0*1024.0*1024.0))

def user_info(user_nick):
    dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
    cur = dbcon.cursor()
    for ip in db:
        for nick in db[ip]:
            if nick == user_nick:
                print '      %s (%s):' % (ip, gethostname(ip))
                ts = db[ip][nick]['timestamp']
                ss = db[ip][nick]['sharesize']
                searches = ''
                if db[ip][nick].get('search', False):
                    searches = ' searches:%s' % (','.join(db[ip][nick]['search']))
                print '            %s (%s/%s)%s' % (nick, ts.strftime('%H:%M %d%b%y'), ss,searches)
                if db[ip][nick].get('tthsearch', []):
                    print '              tth searches:'
                    for tth in db[ip][nick]['tthsearch']:
                        # resolve tth to file
                        if cur.execute('select share_path, size from shares where tth=%s', (tth,)):
                            share_path,size = cur.fetchone()
                            print '              - %s (%0.2f MB)' % (share_path, size/(1024.0*1024.0))
                        else:
                            print '              - %s' % tth

def sameip_analyst():
    for ip in db:
        last_share = 0
        if len(db[ip]) > 1:
            print '%s (%s):' % (ip, gethostname(ip))
            avg = sum([db[ip][nick]['sharesize'] for nick in db[ip]])/len(db[ip])
            for nick in db[ip]:
                ts = db[ip][nick]['timestamp']
                ss = db[ip][nick]['sharesize']
                if ss != 0:
                    pctg = 100*abs(ss-avg)/ss
                else:
                    pctg = -1
                print '  - %s (ss: %d, diff: %d/%0.2f%%)' % (nick, ss, abs(ss-avg), pctg)

                last_share = ss

def user_requesting_tth(requested_tth):
    print 'These nicks requested TTH %s:' % requested_tth
    for ip in db:
        for nick in db[ip]:
            for tth in db[ip][nick].get('tthsearch', []):
                if tth == requested_tth:
                    print '+ %s (%s):' % (ip, gethostname(ip))
                    ts = db[ip][nick]['timestamp']
                    ss = db[ip][nick]['sharesize']
                    searches = ''
                    #~ if db[ip][nick].get('search', False):
                        #~ searches = ' searches:%s' % (','.join(db[ip][nick]['search']))
                    print '            %s (%s/%s)%s' % (nick, ts.strftime('%H:%M %d%b%y'), ss,searches)

def ip_with_uid():
    dbipuni = cPickle.load(file('dbip_uniid.dat','rb'))
    nf = NameFinder()
    print 'IP and nick with uID:'

    subnets = {}
    for ip in db:
        if ip not in dbipuni:
            continue
        subnet_name = getsubnetfromip(ip)
        subnets.setdefault(subnet_name, [])
        subnets[subnet_name].append(ip)

    subnet_names = subnets.keys()
    subnet_names.sort()

    for subnet_name in subnet_names:
        ips = subnets[subnet_name]
        ips.sort(ip_sorter)

        print '+ %s (%s ip):' % (subnet_name, len(ips))

        for ip in ips:
            print '      %s (%s):' % (ip, gethostname(ip))
            print '        * Uni ID:%s' % ','.join( ["%s(%s)"% (uid, nf(uid)) for uid in dbipuni[ip].keys()] )
            for nick in db[ip]:
                ts = db[ip][nick]['timestamp']
                ss = db[ip][nick].get('sharesize',-1)
                searches = ''
                #~ if db[ip][nick].get('search', False):
                    #~ searches = ' searches:%s' % (','.join(db[ip][nick]['search']))
                print '            %s (%s/%s)%s' % (nick, ts.strftime('%H:%M %d%b%y'), ss,searches)

    nf.savestate()



if __name__ == '__main__':
    db = cPickle.load(file(DB_FILE,'rb'))
    topsharesize()
    topsearchword()
    topsearch()
    toptth(50)
    user_info('becks')
    #~ sameip_analyst()
    #~ user_requesting_tth('DXJF6QOGNVUAUWOSLYE2QAOAMV7XH7AXGFU5SUY')
    ip_with_uid()