#~ import psyco
#~ psyco.profile()

def lock2key(lock):
    "Generates response to $Lock challenge from Direct Connect Servers -- by Benjamin Bruheim"
    lock = [ord(c) for c in lock]
    key = [0]
    for n in range(1,len(lock)):
        key.append(lock[n]^lock[n-1])
    key[0] = lock[0] ^ lock[-1] ^ lock[-2] ^ 5
    for n in range(len(lock)):
        key[n] = ((key[n] << 4) | (key[n] >> 4)) & 255
    result = ""
    for c in key:
        if c in [0, 5, 36, 96, 124, 126]:
            result += "/%%DCN%.3i%%/" % c
        else:
            result += chr(c)
    return result

import array
def lock2key2(lock):
    "Generates response to $Lock challenge from Direct Connect Servers -- by Benjamin Bruheim, optimized by Dody Suria Wijaya"
    lock = array.array('B', lock)
    ll = len(lock)
    key = list('0'*ll)
    for n in xrange(1,ll):
        key[n] = lock[n]^lock[n-1]
    key[0] = lock[0] ^ lock[-1] ^ lock[-2] ^ 5
    for n in xrange(ll):
        key[n] = ((key[n] << 4) | (key[n] >> 4)) & 255
    result = ""
    for c in key:
        if c in (0, 5, 36, 96, 124, 126):
            result += "/%%DCN%.3i%%/" % c
        else:
            result += chr(c)
    return result


if __name__=='__main__':
    # sanity check
    key = lock2key("T&AUreb/M_2Wtp_lZU)EA_yU_)2[2/_4u:,`L`3\\m:+ctsnyw9@")
    key2 = lock2key2("T&AUreb/M_2Wtp_lZU)EA_yU_)2[2/_4u:,`L`3\\m:+ctsnyw9@")
    assert key=="\x82'vArqp\xd4&!\xd6V2@\xf23c\xf0\xc7\xc6@\xe1b\xc2\xa0g" \
           + "\xb1\x96\x96\xd1\x07\xb6\x14\xf4a\xc4\xc2\xc25\xf6\x13u\x11" \
           + "\x84qp\xd1q\xe0\xe4\x97"
    assert key2=="\x82'vArqp\xd4&!\xd6V2@\xf23c\xf0\xc7\xc6@\xe1b\xc2\xa0g" \
           + "\xb1\x96\x96\xd1\x07\xb6\x14\xf4a\xc4\xc2\xc25\xf6\x13u\x11" \
           + "\x84qp\xd1q\xe0\xe4\x97"



    from timeit import Timer
    t = Timer("lock2key('T&AUreb/M_2Wtp_lZU)EA_yU_)2[2/_4u:,`L`3\\m:+ctsnyw9@')", "from __main__ import lock2key")
    t2 = Timer("lock2key2('T&AUreb/M_2Wtp_lZU)EA_yU_)2[2/_4u:,`L`3\\m:+ctsnyw9@')", "from __main__ import lock2key2")
    print t.timeit(10000)
    print t2.timeit(10000)
