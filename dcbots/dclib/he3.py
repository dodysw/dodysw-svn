"""
HE3 compression decoder
Converted from C Language by Dody Suria Wijaya
This is the original copyright:
===============================
DCTC - a Direct Connect text clone for Linux
Copyright (C) 2001 Eric Prevoteau
he3.c: Copyright (C) Eric Prevoteau <www@a2pb.gotdns.org>
===============================
"""

import struct, array, os

# psyco really increase the speed up to 10x
try:
    import psyco
    psyco.profile()
except ImportError:
    pass

def he3_decoder(path_from, path_to):

    data = array.array('B')
    data.fromfile(file(path_from,'rb'), os.path.getsize(path_from))

    if not data[0:4].tostring().startswith('HE3\r'):
        raise Exception, 'Invalid HE3 header format. If this a HE3 file?'

    # compute the number of bytes to produce
    nb_output = struct.unpack('<L',data[5:9].tostring())[0]

    # compute the number of couples
    nb_couple = struct.unpack('<H',data[9:11].tostring())[0]

    max_len = 0 #max size of encoded pattern
    ttl_len = 0 #total size of all encoded patterns
    for pos in xrange(nb_couple):
        v = data[12 + pos*2]
        if v > max_len:
            max_len = v
        ttl_len += v

    # clear the decode array
    decode_array = array.array('B', chr(0) * (1 << (max_len+1)))

    # the decode array is technically a binary tree
    # if the depth of the tree is big (let's say more than 10),
    # storing the binary tree inside an array becomes very memory consumming
    # but I am too lazy to program all binary tree creation/addition/navigation/destruction functions :)

    offset_pattern = 8 * (11+nb_couple*2) #position of the pattern block, it is just after the list of couples
    offset_encoded = offset_pattern + ((ttl_len+7) & ~7) #the encoded data are just after the pattern block (rounded to upper full byte)

    # decode_array is a binary tree. byte 0 is the level 0 of the tree. byte 2-3, the level 1, byte 4-7, the level 2,
    # in decode array, a N bit length pattern having the value K is its data at the position:
    # 2^N + (K&((2^N)-1))
    # due to the fact K has always N bit length, the formula can be simplified into:
    # 2^N + K
    for pos in xrange(nb_couple):
        v_len = data[12 + pos*2] #the number of bit required
        res = 0
        for i in xrange(v_len):
            res = (res << 1) | ((data[offset_pattern / 8] >> (offset_pattern & 7)) & 1)
            offset_pattern += 1
        decode_array[(1 << v_len) + res] = data[11 + pos*2] # the character

    # now, its time to decode
    output = array.array('B', chr(0)*nb_output)
    for i in xrange(nb_output):
        cur_val = (data[offset_encoded/8] >> (offset_encoded & 7)) & 1 # get one bit
        offset_encoded += 1
        nb_bit_val = 1
        while decode_array[(1 << nb_bit_val) + cur_val] == 0:
            cur_val = (cur_val << 1) | ((data[offset_encoded/8] >> (offset_encoded & 7)) & 1)
            offset_encoded += 1
            nb_bit_val += 1
        output[i] = decode_array[(1 << nb_bit_val) + cur_val]

    output.tofile(file(path_to,'wb'))

def test():
    import sys, time
    if sys.platform == "win32":
        # On Windows, the best timer is time.clock()
        default_timer = time.clock
    else:
        # On most other platforms the best timer is time.time()
        default_timer = time.time

    start = default_timer()
    he3_decoder(sys.argv[1], 'output.txt')
    print 'Time:', default_timer()-start
    if file('output.txt','rb').read() != file('output_base.txt','rb').read():
        print 'Fail output'
    else:
        print 'Success'

if __name__ == '__main__':
    test()