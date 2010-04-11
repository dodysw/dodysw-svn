#!/usr/bin/python
__version__ = '1.1.0'
freeze_path = '~/Desktop/cx_Freeze-3.0.1/FreezePython'
seed_file = 'wxsda.py'
app_name = 'wxsda'


import os, re, sys

if '~' in freeze_path:
    freeze_path = os.path.expanduser(freeze_path)

# make sure required files exist
if not os.path.exists(freeze_path): sys.exit('FreezePython not found on path [%s]' % freeze_path)
if not os.path.exists(seed_file): sys.exit('Seedfile not found on path [%s]' % seed_file)

# check file to get version
m = re.search(r"^__version__\s*=\s*'([^']*)'", file(seed_file).read(), re.M)
if not m:
    version = raw_input('Version (ie: 1.1.0):')
else:
    version = m.group(1)
    print 'Will compile version %s' % version
    if raw_input('Continue? (Y/n)').lower() == 'n':
        sys.exit()
print 'Enter your password if asked'
os.system("sudo %s -OO --install-dir releases/%s-%s %s" % (freeze_path,app_name,version,seed_file))
os.chdir('releases/')
c = "tar -cjf %s-%s.tar.bz2 %s-%s/" % (app_name,version,app_name,version)
#~ print c
os.system(c)
os.chdir('../')
os.system("rm -rf releases/%s-%s" % (app_name,version))