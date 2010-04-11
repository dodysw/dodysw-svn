#convert utf-8 file to ansi
# Copyright 2007
# Public domain
#Dody Suria Wijaya <dodysw@gmail.com>
import sys

def convertUTF8toANSI(filepath):
	buff = file(filepath).read()
	if buff[0:3] == '\xef\xbb\xbf':
		file(filepath,'w').write(buff[3:])
		return True
	return False
	
if __name__ == '__main__':
	import glob
	if len(sys.argv) == 1:
		# assume doing it on current folder
		print "Parameter: <path to file(s) to be converted>"
		sys.exit()
	for path in glob.glob(sys.argv[1]):
		convertUTF8toANSI(path)	