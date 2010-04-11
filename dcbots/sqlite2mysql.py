
import sys

try:
    import sqlite3 as sqlite
except ImportError:
    try:
        from pysqlite2 import dbapi2 as sqlite
    except ImportError:
        print "This software requires pySqlite. Download at http://www.pysqlite.org."
        sys.exit()

SQLITE_DB_PATH = 'whatsnew_dcbot.db'
MAX_PACKET = 900*1024

import MySQLdb

def main():
    sq_conn = sqlite.connect(SQLITE_DB_PATH)
    sq_cur = sq_conn.cursor()

    print 'delete from shares;'

    sq_cur.execute("select tth, share_path, filename, extension, size, last_nick, first_found_time, last_found_time from shares")
    prefixsql = "insert into shares (tth, share_path, filename, extension, size, last_nick, first_found_time, last_found_time) values"
    i = datalen = 0
    print prefixsql
    for tth, share_path, filename, extension, size, last_nick, first_found_time, last_found_time in sq_cur:
        if datalen > MAX_PACKET:
            sys.stdout.write(';\n')
            datalen = 0
            print prefixsql
            datalen += len(prefixsql)
        elif i != 0:
            sys.stdout.write(',')
            if i % 100 == 0:
                sys.stdout.write('\n')
        try:
            line = "('%s','%s','%s','%s',%s,'%s',%s,%s)" % (MySQLdb.escape_string(tth.encode('UTF-8')), MySQLdb.escape_string(share_path.encode('UTF-8')), MySQLdb.escape_string(filename.encode('UTF-8')), MySQLdb.escape_string(extension.encode('UTF-8')), size, MySQLdb.escape_string(last_nick.encode('UTF-8')), first_found_time, last_found_time)
            datalen += len(line)
            sys.stdout.write(line)
        except UnicodeEncodeError, e:
            print >> sys.stderr, 'Unicode error', e
            print >> sys.stderr, 'Line:', tth, repr(share_path), repr(filename), extension, size, last_nick, first_found_time, last_found_time
            raise
        i += 1
    sys.stdout.write(';')
    print "# Wrote %s insert" % i

if __name__ == '__main__':
    main()