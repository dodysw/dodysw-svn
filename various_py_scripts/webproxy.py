import smtplib
from twisted.web import http
from twisted.web.proxy import Proxy
from twisted.internet import reactor

SMTP_SERVER = 'smtphost.anu.edu.au'

f = http.HTTPFactory()
f.protocol = Proxy
reactor.listenTCP(3128, f)

server = smtplib.SMTP(SMTP_SERVER)
server.sendmail('dodysw@gmail.com', 'dodysw@gmail.com', "Starting wpx")
server.quit()

reactor.run()

server = smtplib.SMTP(SMTP_SERVER)
server.sendmail('dodysw@gmail.com', 'dodysw@gmail.com', "Stopping wpx")
server.quit()
