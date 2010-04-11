#~ import psyco
#~ psyco.profile()

import tiger

if __name__=='__main__':
    tiger.test_tiger()
    print 'Base speed: 2.81'
    from timeit import Timer
    #~ t1 = Timer("tiger.Tiger().update('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+-ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+-').hex_digest()", "import tiger_working as tiger")
    t2 = Timer("tiger.Tiger().update('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+-ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+-').hex_digest()", "import tiger")

    #~ print t1.timeit(3000)
    print t2.timeit(3000)
