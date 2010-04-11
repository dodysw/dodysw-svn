import sys

uids = [
#~ 4000218,
#~ 4000220,
#~ 4000713,
#~ 4000838,
#~ 4019067,
#~ 4019586,
#~ 4010613,
#~ 4260319,

#~ 4002061,
#~ 4005587,
#~ 4016313,


#~ 3963446,

9105370,
]

def checkcb():
    #~ res_cb = int(uid[0]) * 2**6 + int(uid[1]) * 2**5 + int(uid[2]) * 2**4 +int(uid[3]) * 2**3 +int(uid[4])* 2**2 + int(uid[5]) *2
    a = b = c = d = e = 0
    f = 2
    for a in range(10):
        for b in range(10):
            for c in range(10):
                for d in range(10):
                    for e in range(10):
                        #~ for f in range(10):
                        passed = True
                        for uid in uids:
                            uid = str(uid)
                            res_cb = int(uid[0]) * a + int(uid[1]) * b + int(uid[2]) * c +int(uid[3]) * d +int(uid[4])* e + int(uid[5]) *f
                            res_cb = str(res_cb)
                            good_cb = uid[6]
                            if res_cb[-1:] != good_cb:
                                passed = False
                                break
                        if passed:
                            print "%s|%s|%s|%s|%s|%s matches all uuid" % (a,b,c,d,e,f)

if __name__ == '__main__':
    try:
        import psyco
        psyco.profile()
    except ImportError:
        pass

    checkcb()