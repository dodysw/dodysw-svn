#! /usr/bin/python
import shutil,re
filename = 'detik_usable.php'
buffer = open(filename).read()
m = re.search("\$app\['version'\]\s*=\s*\"([^\"]*)\";", buffer);
guess_ver = None
if m:
    version = guess_ver = m.group(1)
if not guess_ver:
    version = raw_input("Release version (%s):" % guess_ver)
filedest = 'detikusable-%s.php.txt' % version
filedest2 = 'detikusable-latest.php.txt'
print "Copying %s to %s" % (filename,filedest)
shutil.copyfile(filename,filedest)
print "Copying %s to %s" % (filename,filedest2)
shutil.copyfile(filename,filedest2)
