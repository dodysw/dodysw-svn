import sys

uids = [
4000218,
4000220,
4000713,
4000838,
4019067,
4019586,
4010613,
4260319,
4260321,
4267771,
4184800,
4002061,
4005587,
4016313,


3963446,
9105370,

9010392,
9610395,
9710397,
9810399,
9910391,
422141
]

def checkcb(uid):
    uid = str(uid)
    a,b,c,d,e,f = 1,3,1,2,1,2
    res_cb = int(uid[0]) * a + int(uid[1]) * b + int(uid[2]) * c +int(uid[3]) * d +int(uid[4])* e + int(uid[5]) * f
    print "%s|%s|%s|%s|%s|%s and %s = %s" % (a,b,c,d,e,f, uid, res_cb)

def checkcb2(uid):
    uid = str(uid)
    COEF = 1,2,1,2,1,2
    res_cb = 0
    formula = ''
    for i in range(6):
        x = x0 = int(uid[i]) * COEF[i]
        if x > 19:
            x = (x % 10) + 2
        elif x > 9:
            x = (x % 10) + 1
        res_cb += x
        formula += '%s(%s) + ' % (x,x0)
    correct = ''
    if uid[6] == str(res_cb)[-1]:
        correct = 'OK'
    print "%s = %s %s -> %s" % (uid, res_cb, correct, formula)

if __name__ == '__main__':
    #~ uid = sys.argv[1]
    try:
        import psyco
        psyco.profile()
    except ImportError:
        pass

    for uid in uids:
        checkcb2(uid)