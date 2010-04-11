"""
Calculate TTH hash of a file
Copyright 2006 Dody Suria Wijaya <dodysw@gmail.com>
"""

import sys, struct, array, os, math, copy, base64
import tiger

TTH_BLOCK_SIZE = 1024
LEAF_HASH_MARK = '\x00'
INTERNAL_HASH_MARK = '\x01'
def internal_hash(leaf1, leaf2):
    """calculate hash of two combined hashes
    """
    return tiger.Tiger().update(INTERNAL_HASH_MARK + leaf1 + leaf2).digest()

def leaf_hash(data):
    """calculate the leaf's hash. In this case, using Tiger hash
    """
    return tiger.Tiger().update(LEAF_HASH_MARK + data).digest()

def read_all(fh, size):
    # read until getting size
    buffer = []
    while size:
        data = fh.read(size)
        if data == '':
            break
        buffer.append(data)
        size -= len(data)
    return ''.join(buffer)

def get_tth(fh, file_size=None, show_speed=False, save_tthl=None):
    # return TTH of given file

    tthl = []

    if show_speed:
        import time
        start_time = time.clock()

    if file_size is None:
        fh.seek(0, 2)
        file_size = fh.tell()
        fh.seek(0)
    leaf_count = int(math.ceil(float(file_size)/TTH_BLOCK_SIZE))

    print 'File size is', file_size, 'Leaf count', leaf_count

    #populate list of internal leaves hashes
    leaves = []
    for i in xrange(leaf_count/2):
        block_a = read_all(fh, TTH_BLOCK_SIZE)
        #~ print 'data_A', repr(block_a), 'len', len(block_a)
        block_b = read_all(fh, TTH_BLOCK_SIZE)
        #~ print 'data_B', repr(block_b), 'len', len(block_b)
        #~ print 'HASHING LEAF'
        hash_a = leaf_hash(block_a)
        #~ print 'hash_A', repr(hash_a)
        hash_b = leaf_hash(block_b)
        #~ print 'hash_B', repr(hash_b)
        if save_tthl:
            tthl.append(hash_a)
            tthl.append(hash_b)
        #add combined leaf hash
        leaves.append(internal_hash(hash_a, hash_b))
    #~ print 'LEAF WITHOUT PAIR'

    #leaf without a pair
    if leaf_count % 2 != 0:
        block = read_all(fh, TTH_BLOCK_SIZE)
        hash = leaf_hash(block)
        if save_tthl:
            tthl.append(hash)
        leaves.append(hash)

    fh.close()

    # calculate root hash "recursively" until 1 final hash is found
    while 1:
        internal_collection = copy.copy(leaves)
        leaves = []

        while len(internal_collection) > 1:
            #load next two leafs.
            hash_a = internal_collection[0]
            hash_b = internal_collection[1]

            #add their combined hash.
            leaves.append(internal_hash(hash_a, hash_b))

            #remove the used leafs.
            del internal_collection[0]
            del internal_collection[0]

        #if this leaf can't combine add him at the end.
        if len(internal_collection) > 0:
            leaves.append(internal_collection[0])

        if len(leaves) <= 1:
            break

    tth_string = leaves[0]  # leaves[0] now contains TTH root
    if show_speed:
        end_time = time.clock() - start_time
    print 'Speed: %0.2f MB in %0.2f sec (%0.2f MB/sec)' % (file_size/(1024*1024.0), end_time, (file_size/(1024*1024.0))/end_time)

    if save_tthl:
        for hash in tthl:
            save_tthl.write(hash)
        tthl.append(hash)
    save_tthl.close()

    print 'Without base32:', tth_string
    return base64.b32encode(tth_string)[:-1] # encode in base32

#=============== TEST =====================

def test_tth():
    #~ tth = get_tth(file(r'g:\master\pagedefrag.zip','rb'))
    tth = get_tth(file(r'g:\sharedmp3\GuidedImagery.mp3','rb'), show_speed=True, save_tthl=file('tthl.dat','wb'))
    #~ print 'TTH is', tth
    assert tth == 'J2HRBJD4UDRJNSNRUXQIS4567BLX6OVREX7IMYI', 'Got [%s] instead' % repr(tth)

    import StringIO
    data = ''
    assert get_tth(StringIO.StringIO(data), len(data)) == 'LWPNACQDBZRYXW3VHJVCJ64QBZNGHOHHHZWCLNQ'
    data = '\x00'
    assert get_tth(StringIO.StringIO(data), len(data)) == 'VK54ZIEEVTWNAUI5D5RDFIL37LX2IQNSTAXFKSA'
    data = 'A'*1024
    assert get_tth(StringIO.StringIO(data), len(data)) == 'L66Q4YVNAFWVS23X2HJIRA5ZJ7WXR3F26RSASFA'
    data = 'A'*1025
    assert get_tth(StringIO.StringIO(data), len(data)) == 'PZMRYHGY6LTBEH63ZWAHDORHSYTLO4LEFUIKHWY'


if __name__ == '__main__':
    import psyco
    psyco.profile()
    test_tth()