import sys,httplib,re


hostname = "www.desktopmoneymachine.com"
conn = httplib.HTTPConnection(hostname)
if len(sys.argv) < 2:
	print "Please write it like this: slurpemail.exe 1 6000\r\nThat will download from desktopmoneymachine from id 1 to 6000\r\nThanks for the book!"
	sys.exit()
else:
	first = int(sys.argv[1])
	last = int(sys.argv[2])
	print "Dody Suria Wijaya's Software House (yahoomessenger: dody)\r\nGetting %d to %d" % (first,last)

for num in range(first,last):
	url = "/index.cfm?id=%d" % num
	print "Downloading http://%s%s" % (hostname,url)
	conn.request("GET", url)
	r1 = conn.getresponse()
	body = r1.read()
	m = re.search("<a href=\"mailto:([^\"]*)\">Contact</a>",body)
	if not m:
	    print "%s: email regex not found!" % num
	else:
		email = m.group(1)
		if email == "":
			print "%s: email not found!" % num
		else:
			print "%s: %s" % (num,email)
			fd = open("emails.log",'a')
			email = email + "\r\n"
			fd.writelines((email))
			fd.close()
print "end of downloading"