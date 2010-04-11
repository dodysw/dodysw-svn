"""
ip history db:
==============

1. ips
- id, int auto increment
- ip, varchar(15)


2. nicks
- id, int auto increment
- ip_id, int => ips
- nick, varchar(30)
- last activity date, datetime
- share_size, bigint

3. searches
- nick_id, int => nicks
- search_query varchar(200)

4. tthsearches
- nick_id, int => nicks
- tth, char(39)

create table ips (id int auto_increment primary key, ip varchar(15) not null);
create table nicks (id int auto_increment primary key, ip_id int not null, nick varchar(30) not null, share_size bigint(10), last_activity datetime);
create table searches (nick_id int not null, search_query varchar(200) not null default '');
create table tth_searches (nick_id int not null, tth char(39) not null);
"""
import dclib
import MySQLdb as dbm

MYSQL_CONNECT_PARAM = dict(host='localhost', user='nicktracker', passwd='nicktracker', db='nicktracker')

if __name__ == '__main__':
    ip_history_db = dclib.SimplePickledObject('nicktracker_hist')
    ip_history = ip_history_db.get_data() #get reference to data
    dbcon = dbm.connect(**MYSQL_CONNECT_PARAM) # each thread must use different connection!!
    cur = dbcon.cursor()

    sql = 'delete from ips'
    cur.execute(sql)
    sql = 'delete from nicks'
    cur.execute(sql)
    sql = 'delete from tth_searches'
    cur.execute(sql)
    sql = 'delete from searches'
    cur.execute(sql)

    for ip in ip_history:
        sql = "select id from ips where ip=%s"
        args = ip
        if cur.execute(sql, args):
            ip_id = cur.fetchone()[0]
        else:
            sql = "insert into ips (ip) values (%s)"
            args = ip
            cur.execute(sql, args)
            ip_id = cur.lastrowid

        for nick in ip_history[ip]:
            sql = "select id from nicks where nick=%s"
            args = nick
            last_activity, share_size = ip_history[ip][nick]['timestamp'].strftime('%Y-%m-%d %H:%M:%S'), ip_history[ip][nick].get('sharesize',0)
            if cur.execute(sql, args):
                nick_id = cur.fetchone()[0]
                sql = "update nicks set last_activity=%s, share_size=%s where id=%s"
                args = last_activity, share_size, nick_id
                cur.execute(sql, args)
            else:
                sql = "insert into nicks (ip_id, nick, last_activity, share_size) values (%s, %s, %s, %s)"
                args = ip_id, nick, last_activity, share_size
                print args
                cur.execute(sql, args)
                nick_id = cur.lastrowid

            for tth in ip_history[ip][nick].get('tthsearch', []):
                if len(tth) != 39:
                    continue
                sql = "insert into tth_searches (nick_id, tth) values (%s, %s)"
                args = nick_id, tth
                cur.execute(sql, args)

            for search_query in ip_history[ip][nick].get('search', []):
                sql = "insert into searches (nick_id, search_query) values (%s, %s)"
                args = nick_id, search_query
                cur.execute(sql, args)
