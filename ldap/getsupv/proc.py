supv = {}

def getdepth(key, level=0, show=False):
    if key in supv:
        if level+1 >= 4 and supv[key][0] not in ('A','B','C','D'):
            raise Exception, "%s - %s - %s" % (level+1, key, supv[key])
        if key == supv[key]:
            raise Exeption, "same key/val"
        if show:
            print "   ", level+1, supv[key]
        level = getdepth(supv[key], level+1, show)
    return level

for line in file('data.txt'):
    line = line.split()
    supv[line[3]] = line[1]
    if line[2] == line[4]:
        raise Exception, "supervisor employee id same"

for key in supv:
    #if getdepth(key) > 7:
    print key, getdepth(key,0)
    