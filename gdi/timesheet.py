
import time
def start():
    t1 = time.time()
    raw_input("Press Enter if you finish...")
    d = int(time.time()-t1)
    print "Duration: ",
    if d < 60:
        print d, "s"
    elif d < 3600:
        print d/60, "m", d%60, "s"
    else:
        print d/3600, "h", d%3600/60, "m", d%3600%60, "s"