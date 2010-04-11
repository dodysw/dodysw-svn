import socket, dclib

def ip_sorter(ip1, ip2):
    if type(ip1) in (tuple, list):
        return ip_sorter(ip1[0], ip2[0])
    return cmp(ip2num(ip1), ip2num(ip2))

import struct
def ip2num(ip):
    return struct.unpack("!I",socket.inet_aton(ip))[0]
    # or
    t = ip.split('.')
    t = map(int, t)
    return (t[0] << 24) + (t[1] << 16) + (t[2] << 8) + (t[3])

#parse subnets.txt file
def subnet_parser(filename="subnets.txt"):
    db = []
    for line in file(filename):
        #150.203.54.0/25	Faculties-Science SRES student machines :65080
        p1, p2 = line.split(None,1)

        #expand ip into range
        if '/' in p1:
            ip_start, netmask = p1.split('/')
            netmask = int(netmask)
        else:
            ip_start = p1
            netmask = 24

        if ip_start.count('.') < 3:
            ip_start += '.0'

        ip_start = ip2num(ip_start)
        ip_end = ip_start + 2**(32-netmask) - 1 # ip starts from zero

        if ':' in p2:
            p2 = p2.split(':')[0]
        p2 = p2.strip()

        db.append([ip_start, ip_end, p2])
    print "Parsing subnet file [%s], got %d entries" % (filename, len(db))
    return db

subnet_db = subnet_parser()
def getsubnetfromip(ip):
    ip_num = ip2num(ip)
    for start, end, name in subnet_db:
        if start <= ip_num <= end:
            return name
    return 'Unknown subnet (%s/%s)' % (ip,ip_num)

hostnames_db = dclib.SimplePickledObject('gethostname_cache')
cached_hostnames = hostnames_db.get_data()
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


import ldap
class NameFinder:
    _cache_db = dclib.SimplePickledObject('UniIdNameFinder.dat')
    _cache = _cache_db.get_data()

    def __init__(self, server='ldap2.anu.edu.au', bindname='o=anu.edu.au'):
        self.l = ldap.open(server)
        self.l.simple_bind(bindname)

    def getname(self, uid):
        try:
            return self._cache[uid]
        except KeyError:
            l_resid = self.l.search('o=anu.edu.au', ldap.SCOPE_SUBTREE, 'uid=%s' % uid, None)
            x = self.l.result(l_resid, 0)
            if x == 100:
                ret = 'unknown'
            else:
                try:
                    ret = x[1][0][1]['cn'][0]
                except IndexError:
                    print 'Cant find uid[%s]:%r' % (uid, x)
                    ret = 'unknown'
            self._cache[uid] = ret
            return ret
    __call__ = getname

    def savestate(self):
        self._cache_db.save()
