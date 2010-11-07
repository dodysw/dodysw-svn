import os
fh = file('g:/biggy','w')
buff = '\x00' * 1000000
for i in xrange(1000):
    fh.write(buff)
fh.close