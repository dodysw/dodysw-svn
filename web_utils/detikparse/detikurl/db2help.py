import os,datetime,re,MySQLdb
db = MySQLdb.connect('localhost','aspuser','user123','dody')
dbc = db.cursor()

datefrom = 2003,1,1
dateto = 2003,12,31
today = datetime.date(*datefrom)
dayuntil = datetime.date(*dateto)
putfolder = os.getcwd()

while today <= dayuntil:
    sql = "select * from news_tab where tanggal > 