import cPickle as pickle
import sys

try:
    import psyco
    psyco.profile()
except ImportError:
    pass

def slow(the_diff):
    print "With diff=%s" % the_diff
    for uid1 in db:
        for uid2 in db:
            n1, n2 = int(uid1[:6]), int(uid2[:6])
            diff = abs(n1-n2)
            if diff == the_diff:
                biggernum = max(uid1, uid2)
                smallernum = min(uid1, uid2)
                lastdigit_diff = int(biggernum[6]) - int(smallernum[6])
                if lastdigit_diff < 0:
                    lastdigit_diff = 10+lastdigit_diff
                print "%s == %s (%s)" % (uid1, uid2, lastdigit_diff)

def fast(the_diff):
    print "Fast diff=%s" % the_diff
    last_lastdigit_diff = None
    for uid1 in db:
        n1 = int(uid1[:6])
        n2, n3 = n1 + the_diff, n1 - the_diff
        #~ print n1, n2, n3
        for n in [n2, n3]:
            if n in db6:
                biggernum = max(uid1, db6[n])
                smallernum = min(uid1, db6[n])
                lastdigit_diff = int(biggernum[6]) - int(smallernum[6])
                if lastdigit_diff < 0:
                    lastdigit_diff = 10+lastdigit_diff
                if lastdigit_diff != last_lastdigit_diff:
                    print "%s == %s (%s)" % (uid1, db6[n], lastdigit_diff)
                    last_lastdigit_diff = lastdigit_diff

def fast2(digitpos):
    print "Fast diff @ digit position=%s" % digitpos
    last_lastdigit_diff = None
    for uid1 in db:
        digit = int(uid1[digitpos-1])
        n2 = list(uid1[:6])
        n3 = list(uid1[:6])
        n2[digitpos-1] = str((digit + 1) % 10)
        n3[digitpos-1] = str((digit - 1) % 10)
        n2 = ''.join(n2)
        n3 = ''.join(n3)
        #~ print uid1[:6], n2, n3
        for n in [n2, n3]:
            if n in db6:
                biggernum = str(max(int(uid1), int(db6[n])))
                smallernum = str(min(int(uid1), int(db6[n])))
                lastdigit_diff = int(biggernum[6]) - int(smallernum[6])

                if lastdigit_diff < 0:
                    lastdigit_diff = 10+lastdigit_diff
                lastdigit_diff_firstdigit = str(lastdigit_diff)[0]
                if lastdigit_diff != last_lastdigit_diff:
                    print "%s == %s (%s/%s)" % (uid1, db6[n], lastdigit_diff, lastdigit_diff_firstdigit)
                    last_lastdigit_diff = lastdigit_diff


def fast3(digitpos):
    print "Fast diff @ digit position=%s" % digitpos
    last_lastdigit_diff = None
    for uid1 in db:
        digit = int(uid1[digitpos-1])
        n2 = list(uid1[:6])
        n3 = list(uid1[:6])
        n4 = list(uid1[:6])
        n5 = list(uid1[:6])
        #~ n6 = list(uid1[:6])
        #~ n7 = list(uid1[:6])
        n2[digitpos-1] = str((digit + 1) % 10)
        n3[digitpos-1] = str((digit + 2) % 10)
        n4[digitpos-1] = str((digit + 3) % 10)
        n5[digitpos-1] = str((digit + 4) % 10)
        #~ n6[digitpos-1] = str((digit + 5) % 10)
        #~ n7[digitpos-1] = str((digit + 6) % 10)
        n2 = ''.join(n2)
        n3 = ''.join(n3)
        n4 = ''.join(n4)
        n5 = ''.join(n5)
        #~ n6 = ''.join(n6)
        #~ n7 = ''.join(n7)
        if n2 in db6 \
            and n3 in db6 \
            and n4 in db6 \
            and n5 in db6 \
            :
            print "----"
            for n in [n2,
                n3,
                n4,
                n5,
                #~ n6,
                #~ n7
                ]:
                #~ if n in db6:
                biggernum = str(max(int(uid1), int(db6[n])))
                smallernum = str(min(int(uid1), int(db6[n])))
                lastdigit_diff = int(db6[n][6]) - int(uid1[6])

                if lastdigit_diff < 0:
                    lastdigit_diff = 10+lastdigit_diff
                lastdigit_diff_firstdigit = str(lastdigit_diff)[0]
                if 1 or lastdigit_diff != last_lastdigit_diff:
                    print "%s == %s (%s/%s)" % (uid1, db6[n], lastdigit_diff, lastdigit_diff_firstdigit)
                    last_lastdigit_diff = lastdigit_diff

def build_6digit():
    print "Building 6 digit...",
    global db6
    db6 = {}
    for uid in db:
        db6[uid[:6]] = uid
    print "done (%s)" % len(db6)

if __name__ == "__main__":

    db = pickle.load(file('uni.dat','rb'))
    build_6digit()
    #~ slow(int(sys.argv[1]))
    #~ fast2(int(sys.argv[1]))
    fast3(int(sys.argv[1]))
