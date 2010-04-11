
import dclib, time, bz2
import MySQLdb as dbm

try:
    import psyco
    psyco.profile()
except ImportError:
    pass


MYSQL_CONNECT_PARAM = dict(host='localhost', user='whatsnew_dcbot', passwd='', db='whatsnew_dcbot')
XML_FILEPATH = 'files.xml.bz2'

def main():

    # query db for files after or equal to yesterday's 00:00 am and before today's 00:00 am
    now_struct = time.localtime()
    today = time.mktime(now_struct[:3] + (0,0,0,0,0,0)) # remove the hour/minute/second, and get unix time
    yesterday = today - 86400

    dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
    cur = dbcon.cursor()
    sql = "select concat(replace(last_nick,'/','<slash>'), '/', share_path), size, tth from shares where last_found_time >= %s and first_found_time < %s order by last_nick, share_path"
    args = yesterday, today
    cur.execute(sql, args)

    fh = bz2.BZ2File(XML_FILEPATH, 'w')
    try:
        dclib.write_xml_list(cur.fetchall(), fh)
    finally:
        fh.close()

if __name__ == '__main__':
    main()
