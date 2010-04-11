# File splitter 
# GNU v2 Public License copyright Dody Suria Wijaya 2007
# dodysw@gmail.com

import sys, os, math

READ_BLOCK = 1024*1024  # 1 MB
SPLIT_SIZE, SPLIT_NUM = 1,2

def write_block(fhSrc, fhDest, pos, size):
    size_left = size
    fhSrc.seek(pos)
    while size_left > 0:
        data = fhSrc.read(min(READ_BLOCK, size))
        if data == '': # file size has been troughly read...
            break
        size_left -= len(data)
        fhDest.write(data)
    return size - size_left
    

def split_file(source, target_dir, splitsize=0, splitnum=0):
    """memory efficient buffered read @source_path file, and buffered write socket
    Mode: Uploader
    """
    assert splitsize^splitnum   # only one may be defined
    assert os.path.exists(source) and os.path.isfile(source)
    file_size = os.path.getsize(source)
    fh_read = file(source ,'rb')    
    if splitsize != 0:
        mode = SPLIT_SIZE
    else:
        mode = SPLIT_NUM
        
    try:
        if mode == SPLIT_SIZE:
            # split file by certain size            
            commit_split_size = splitsize
            commit_split_num = int(math.ceil(file_size/float(splitsize)))
        elif mode == SPLIT_NUM:
            commit_split_size = int(math.ceil(file_size/float(splitnum)))
            commit_split_num = splitnum
        print "Splitting %s (%d) to %d files each %d" % (source, file_size, commit_split_num, commit_split_size)
        pos = 0
        zeroPrefix = ".%%0%dd" % math.ceil(math.log10(splitnum))           # trying to create .0001 or .1 dpeending on the number of splits
        splittedFileIndex = 1
        while pos < file_size:
            target_path = os.path.join(target_dir, os.path.basename(source) + zeroPrefix % splittedFileIndex)
            fh_write = file(target_path, 'wb')
            actuallyRead = write_block(fh_read, fh_write, pos, commit_split_size)
            fh_write.close()
            print "wrote", target_path
            pos += actuallyRead
            splittedFileIndex += 1

    finally:
        fh_read.close()
        

if __name__ == "__main__":
    if len(sys.argv) != 3+1:
        print "Parameter: <path to source file> <target dir> <num of split>"
        sys.exit()
    source_path, target_dir, splitnum = sys.argv[1:]
    split_file(source_path, target_dir, 0, int(splitnum))