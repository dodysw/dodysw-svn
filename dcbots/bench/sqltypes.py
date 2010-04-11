import time
import MySQLdb as dbm

def measure(f):
    start = time.clock()
    f()
    print time.clock()-start


def init():
    global tth_rows, cur
    MYSQL_CONNECT_PARAM = dict(host='localhost', user='whatsnew_dcbot', passwd='', db='whatsnew_dcbot')
    conn = dbm.connect(**MYSQL_CONNECT_PARAM)
    cur = conn.cursor()
    sql = "select tth from shares limit 20000"
    cur.execute(sql)
    tth_rows = cur.fetchall()

def normal():
    for (tth,) in tth_rows:
        sql = "insert into shares (tth, share_path, filename, extension, size, last_nick, first_found_time, last_found_time, dc_file_type) values (%s,'kucing', 'kucing', 'kcg', 0, 'kucing', 1, 1, 10)"
        try:
            cur.execute(sql, tth)
        except dbm.IntegrityError:
            pass

def fast1():
    sql = "insert ignore into shares (tth, share_path, filename, extension, size, last_nick, first_found_time, last_found_time, dc_file_type) values (%s,'kucing', 'kucing', 'kcg', 0, 'kucing', 1, 1, 10)"
    cur.executemany(sql, [r[0] for r in tth_rows])

def try1():
    for (tth,) in tth_rows:
        if cur.execute("select 1 from shares where tth=%s", tth) == 0:
            sql = "insert into shares (tth, share_path, filename, extension, size, last_nick, first_found_time, last_found_time, dc_file_type) values (%s,'kucing', 'kucing', 'kcg', 0, 'kucing', 1, 1, 10)"
            cur.execute(sql, tth)

def try2():
    # create heap table
    cur.execute("create temporary table tth_list (tth char(39)) engine=MEMORY")

    # populate the heap
    cur.executemany("insert into tth_list (tth) values (%s)", [r[0] for r in tth_rows])

    # join with shares, and get list of new tth
    if cur.execute("select tth_list.tth from tth_list left join shares on tth_list.tth = shares.tth where shares.tth is null"):
        print 'at least 1 is new'
        sql = "insert into shares (tth, share_path, filename, extension, size, last_nick, first_found_time, last_found_time, dc_file_type) values (%s,'kucing', 'kucing', 'kcg', 0, 'kucing', 1, 1, 10)"
        cur.executemany(sql, [r[0] for r in cur])
    else:
        print 'all tth are not new'

    #~ cur.execute("drop table tth_new")


if __name__ == '__main__':
    init()
    measure(normal)
    measure(fast1)
    measure(try1)
    measure(try2)