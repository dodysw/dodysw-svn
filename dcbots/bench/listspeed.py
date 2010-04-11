import string

class Share: pass

def test():
    "Stupid test function"
    L = []
    for i in range(500):
        L.append([1,2,3,4,5])

def test1():
    "Stupid test function"
    L = []
    for i in range(500):
        L.append((1,2,3,4,5))

def test2():
    "Stupid test function"
    L = set()
    for i in range(500):
        L.add((1,2,3,4,5))

def test3():
    "Stupid test function"
    L = {}
    for i in range(500):
        L[i] = (1,2,3,4,5)

def test4():
    "Stupid test function"
    L = {}.fromkeys(xrange(500),(1,2,3,4,5))


#~ def test2():
    #~ "Stupid test function"
    #~ L = "Stupid test functionStupid test function"
    #~ lower = string.lower
    #~ for i in range(100):
        #~ lower(L)

#~ def test3():
    #~ "Stupid test function"
    #~ L = "Stupid test functionStupid test function"
    #~ for i in range(100):
        #~ string.lower(L)

#~ def test4():
    #~ "Stupid test function"
    #~ for i in range(100):
        #~ string.lower("Stupid test functionStupid test function")

if __name__=='__main__':
    from timeit import Timer
    #~ t = Timer("test()", "from __main__ import test")
    #~ print t.timeit(10000)

    t = Timer("test1()", "from __main__ import test1")
    print t.timeit(10000)


    t = Timer("test2()", "from __main__ import test2")
    print t.timeit(10000)

    t = Timer("test3()", "from __main__ import test3")
    print t.timeit(10000)

    t = Timer("test4()", "from __main__ import test4")
    print t.timeit(10000)
