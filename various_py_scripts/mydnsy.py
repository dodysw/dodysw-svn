import smtplib
from twisted.internet import reactor
from twisted.names import dns, server, client

cl = []
cl.append(client.createResolver())

f = server.DNSServerFactory(client=cl, verbose=2)
#~ ltcp = reactor.listenTCP(0, f, interface="192.168.0.1")
ltcp = reactor.listenTCP(53, f)

f2 = dns.DNSDatagramProtocol(f)
port = ltcp.getHost().port
print "Port:", port
#~ ludp = reactor.listenUDP(port, f2, interface="192.168.0.1")
ludp = reactor.listenUDP(port, f2)

reactor.run()