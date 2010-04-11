import sys,os,binascii,shutil,stat,time
path = '.'
if len(sys.argv)>1:
    path = sys.argv[1]
movedir = path+'/dupes'
#get a list of regular files
fs = [path+'/'+f for f in os.listdir(unicode(path)) if stat.S_ISREG(os.stat(path+'/'+f)[stat.ST_MODE])]
crclist = {}
totaldupefiles = 0
try:
    os.mkdir(movedir)
except:
    pass
starttime = time.clock()
for filename in fs:
    crc_number = binascii.crc32(file(filename,'rb').read())
    if crclist.has_key(crc_number):
        crclist[crc_number][1] += 1
        #print "%s/%s #%d"%(os.path.basename(crclist[crc_number][0]),os.path.basename(filename),crclist[crc_number][1])
        shutil.copy(filename,movedir)
        os.remove(filename)
        totaldupefiles += 1
    else:
        crclist[crc_number] = [filename,1]
deltatime = time.clock() - starttime
print "== Done deduping %s %d files in %f secs ==" % (path,totaldupefiles,deltatime)
print "== Duplicate files has been moved to %s" % movedir