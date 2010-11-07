
import sys
sz = 250*1024*1024

if __name__ == '__main__':
    print 'creating %s MB of %s' % (sz/(1024*1024), sys.argv[1])
    fh = file(sys.argv[1],'wb')
    buff = chr(0)
    line = buff*1024
    while sz > 0:
        fh.write(line)
        sz -= len(line)
    fh.close()