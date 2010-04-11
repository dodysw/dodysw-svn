#!/usr/bin/env python2
#######################################################
#######################################################
## TIME AND DATE FUNCTIONS
#######################################################
#######################################################
#
#  time and date are presented as days,hours, and minutes
#  since 1900/01/01 00:00
#
#   duration are also represented in this format
#
#   for performance reasons, the tuple is not wrapped into a class
#   this is most likely an instance of premature optimizations ;-)
#
#######################################################

import re
import time

#######################################################

DurationZero = (0,0,0)
DurationMinute = (0,0,1)
DurationSixHours = (0,6,0)
DurationDay = (1,0,0)
DurationWeek = (7,0,0)


#######################################################

def is_leap_year(year):
    if year % 4 > 0:
        return 0
    if year % 100 > 0:
        return 1
    if year % 400 > 0:
        return 0
    else:
        return 1

#######################################################
DaysInMonth = (31,-1,31,30,31,30,31,31,30,31,30,31)

def days_in_month(month, year):
    if month != 1:
        return DaysInMonth[month]

    if is_leap_year(year):
        return 29
    else:
        return 28

#######################################################

def days_in_year(year):
    if is_leap_year(year):
        return 366
    else:
        return 365

#######################################################
#
DaysToJanFirst = {1900:0}
DaysToJanFirstHi = 1900
DaysToJanFirstLo = 1900

#######################################################
def days_to_jan_first( year ):
    global DaysToJanFirst, DaysToJanFirstLo, DaysToJanFirstHi
    if year > DaysToJanFirstHi:
        days = DaysToJanFirst[DaysToJanFirstHi]
        while DaysToJanFirstHi < year:
            days += days_in_year(DaysToJanFirstHi)
            DaysToJanFirst[DaysToJanFirstHi+1] = days
            DaysToJanFirstHi += 1
    elif year < DaysToJanFirstLo:
        days = DaysToJanFirst[DaysToJanFirstLo]
        while DaysToJanFirstLo >= year:
            DaysToJanFirstLo -= 1
            days -= days_in_year(DaysToJanFirstLo)
            DaysToJanFirst[DaysToJanFirstLo] = days
    assert DaysToJanFirstLo <= year and year <= DaysToJanFirstHi
    return DaysToJanFirst[year]

#######################################################

def date_to_y_m_d_H_M( date ):
#    print date
    d,H,M = date
    # we try to approximate the year
    # it is important to stay above the actual year
    if d >= 0: year = d/365 + 1900
    else: year = d/366 + 1900

#    print "start year:", year
    while 1:
        x = days_to_jan_first( year )
        if x <= d:
            d -= x
            assert 0 <= d and d <= 365
            break
        year -= 1

#    print "day left in year :", d
    for month in range(12):
        x = days_in_month(month,year)
#        print x
        if x <= d: d -= x
        else: return (year,month,d,H,M)

    assert None and "ranged over year"

#######################################################
def date_to_d_H_M( date ):
    return date
#######################################################

def date_normalize( d,H,M ):

    q,r = divmod(M,60)

    M = r
    H += q

    q,r = divmod(H,24)
    H = r
    d += q

    return (d,H,M)

#######################################################

def date_from_y_m_d_H_M( y, m, d, H, M):
    """jan = 0, first day = 0"""
    days = d + days_to_jan_first(y)
    for month in range(m):
        days += days_in_month(month,y)
    return date_normalize(days,H,M)
#######################################################

def date_from_d_H_M( d, H, M):
    return date_normalize(d,H,M)

#######################################################

def date_to_day_of_week( date ):
    # 1900/01/01 was a Monday
    weekday = date[0] % 7
    if weekday <  0: weekday += 7
    return weekday


#######################################################
def date_now():
    t = time.localtime( time.time() )
    return date_from_y_m_d_H_M(t[0],t[1]-1,t[2]-1,t[3],t[4])

#######################################################
def date_add(date,dur):
    return date_normalize(  date[0] + dur[0], date[1] + dur[1], date[2] + dur[2] )

#######################################################
def date_sub(date,dur):
    return date_normalize(  date[0] - dur[0], date[1] - dur[1], date[2] - dur[2] )

#######################################################

def date_compare(d1,d2):
    if d1 < d2: return -1
    elif d2 < d1: return +1
    else: return 0

#######################################################
def date_range_contains(date,duration,the_date):
    if the_date < date: return 0
    if the_date >= date_add(date,duration): return 0
    return 1

#######################################################
def date_inc_hour(date, offset):
    return date_add(date, (0,offset,0))

#######################################################
def date_inc_day(date, offset):
    return date_add(date, (offset,0,0))

#######################################################
def date_inc_week(date, offset):
    return date_add(date, (7*offset,0,0))

#######################################################
def date_inc_month(date, offset):
    return date_add(date, (31*offset,0,0))

#######################################################
def date_inc_year(date, offset):
    y,m,d,H,M = date_to_y_m_d_H_M( date )
    return date_from_y_m_d_H_M(y+offset,m,d,H,M)

#######################################################
def date_to_day_of_year( date ):
    y,m,d,H,M = date_to_y_m_d_H_M( date )
    days = d
    for month in range(m):
        days += days_in_month(month,y)
    return days

#######################################################
def date_to_beginning_of_day( date ):
    return (date[0],0,0)

#######################################################
def date_to_beginning_of_week( date, week_starts_monday ):
    weekday = date_to_day_of_week(date)
    if not week_starts_monday:
        weekday = (weekday + 1) % 7

    return (date[0] - weekday, 0, 0)

#######################################################
def date_to_beginning_of_month( date ):
    y,m,d,H,M = date_to_y_m_d_H_M( date )
    return (date[0] - d,0,0)

#######################################################
def date_to_beginning_of_year( date ):
    return (date[0] - date_to_day_of_year(date),0,0)

#######################################################
#
# this was derived by trial and error to resemble the gtk
# calendar widget's week numbering
#
# NB: no effort has been made to get this right when the week starts on Sundays
# instead of Mondays

def date_to_week_no(date, week_starts_monday):
    monday = date_to_beginning_of_week( date, True )
    day_of_year = date_to_day_of_year( monday )

    year,m,d,H,M = date_to_y_m_d_H_M( monday )

    weeks,days = divmod( day_of_year, 7)


    if days > 3:
        weeks = weeks + 1

    if days_in_year(year) - day_of_year < 4:
        weeks = 0

    return weeks + 1


#######################################################
# string and parsing stuff
#######################################################

def date_to_string_full( date ):
    y,m,d,H,M = date_to_y_m_d_H_M( date )
    return "%4d/%02d/%02d %02d:%02d" % (y,m+1,d+1,H,M)

#######################################################
def date_to_string_duration( date ):
    d,H,M = date_to_d_H_M( date )
    return "%d:%02d:%02d" % (d,H,M)

#######################################################
def date_to_string_day( date ):
    y,m,d,H,M = date_to_y_m_d_H_M( date )
    return "%02d" % (d+1)

#######################################################
WeekdayNames = ("Mon","Tue","Wed","Thu","Fri","Sat","Sun")

def date_to_string_weekday(date):
    return WeekdayNames[ date_to_day_of_week(date) ]

#######################################################

def date_to_string_weekday_day(date):
    y,m,d,H,M = date_to_y_m_d_H_M( date )
    return "%s %02d" % (date_to_string_weekday(date),d+1)

#######################################################
MonthNames = ("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec")

def date_to_string_month_year(date):
    y,m,d,H,M = date_to_y_m_d_H_M( date )
    return "%s %04d" %  (MonthNames[m],y)

#######################################################
def date_to_string_week(date, week_starts_monday):
    return "Week %02d" % date_to_week_no(date,week_starts_monday)
#######################################################

def date_to_string_time( date):
      return "%02d:%02d" % (date[1],date[2])

#######################################################
GlobalDateRe = re.compile("^(\d+)/(\d+)/(\d+)\s+(\d+):(\d+)")

def date_from_string(s):
    match = GlobalDateRe.match(s)
    if not match: Error("misformed date format " + s)
    y = int(match.group(1))
    m = int(match.group(2))
    d = int(match.group(3))
    H = int(match.group(4))
    M = int(match.group(5))
    if m < 1 or m > 12: Error("misformed date format: month " + s)
    if d < 1 or d > days_in_month(m-1,y): Error("misformed date format: day " + s)
    if H < 0 or H > 23: Error("misformed date format: hour " + s)
    if M < 0 or M > 59: Error("misformed date format: min " + s)
    return date_from_y_m_d_H_M(y,m-1,d-1,H,M)

#######################################################

GlobalDurationRe = re.compile("^(\d+):(\d+):(\d+)")

def date_from_string_duration(s):
    match = GlobalDurationRe.match(s)
    if not match: Error("misformed duration format " + s)
    d = int(match.group(1))
    H = int(match.group(2))
    M = int(match.group(3))
    if H < 0 or H > 23: Error("misformed duration format: hour " + s)
    if M < 0 or M > 59: Error("misformed duration format: min " + s)
    return (d,H,M)

#######################################################
if __name__ == '__main__':
    print "time date test"
    if len(sys.argv) != 2:
        Error("usage:\n timedate \"year/month/day houres:minutes\"")
    date = date_from_string(sys.argv[1])
    print "raw: ", date
    print "weekday ", date_to_string_weekday( date )
    print "week ", date_to_string_week( date )
    print "full ", date_to_string_full( date )
    sys.exit(-1)
#######################################################





