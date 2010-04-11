import pymssql
DEBUG = False

def sqlToOpenQuery(sql):
    return "select * from openquery(ellca, '%s')" % sql.replace("'","''")

def bnLong2Short(bn):
    short_bn = bn
    try: short_bn = int(bn)
    except ValueError: pass
    return short_bn

def bnShort2Long(bn):
    long_bn = bn.replace("#","")
    if long_bn.isdigit():
        long_bn = "%0.10d" % int(long_bn)
    return long_bn

def getCursor(for_update = False):
    con = pymssql.connect(host='ppp-utilities\\testing', user='sa', password='xxxxx', database='tempdb')
    if for_update:
        return con, con.cursor()
    else:
        return con.cursor()

def getCursorOnlineCA(for_update = False):
    con = pymssql.connect(host='ppp-sql03', user='causer', password='xxxxx', database='OnlineCA')
    if for_update:
        return con, con.cursor()
    else:
        return con.cursor()

def getCursorGlobalService(for_update = False):
    con = pymssql.connect(host='ppp-sql03', user='globserv_acc', password='xxx', database='ppp_GLOBAL_SERVICES')
    if for_update:
        return con, con.cursor()
    else:
        return con.cursor()

def getCursorCRS():
    con = pymssql.connect(host='ppp-sql01', user='sa', password='xxx', database='CRS')
    return con.cursor()

def getCursorFlight(for_update = False):
    if DEBUG:
        con = pymssql.connect(host='ppp-utilities\\testing', user='sa', password='xxx', database='Flight')
    else:
        con = pymssql.connect(host='ppp-sql01', user='sa', password='xxx', database='Flight')
    if for_update:
        return con, con.cursor()
    else:
        return con.cursor()

def getCursorAccbooking():
    con = pymssql.connect(host='ppp-sql01', user='sa', password='xxx', database='portal')
    return con.cursor()

def quote_name(name):
    if name is None:
        return ""
    return name.replace("'", "''")

def clearDjangoCache():
    from django.db import connection, transaction
    from django.core.cache import cache
    try:
        cache._cache.clear()	# in-memory caching
    except AttributeError:
        old = cache._cull_frequency
        cache._cull_frequency = 0
        cache._cull(connection.cursor(), None)
        cache._cull_frequency = old
        transaction.commit_unless_managed()

def getValeEmail(ad_obj):
    try:
        for row in ad_obj['proxyAddresses']:
            if row.startswith('smtp:') and 'valeppp' in row:
                return row[5:]
        return "n/a"
    except:
        return "n/a"