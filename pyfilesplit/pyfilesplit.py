#!/usr/bin/python
"""
pyfilesplit.py - split a file into x number of files, and join it back
"""

version = '1.1.1'
author = 'Dody Suria Wijaya <dodysw@gmail.com>'

READ_BUFFER = 1024*64

import os, sys

def splitfile_by_num(filename, split_num):
    # split file by split_num files of approximately equal size
    # 11byte @ 3 => 3 + 3 + 5
    if split_num in (0,1):
        raise Exception, "Split number must be > 1"
    fh = file(filename,'rb')
    filesize = os.path.getsize(filename)
    split_size = filesize / split_num


    i = 1
    while filesize > 0:
        split_file_path = '%s.pyfilesplit.%s' % (os.path.basename(filename), i)
        new_fh = file(split_file_path,'wb')
        split_file_len = split_size
        while split_file_len > 0:
            buffer = fh.read(min(READ_BUFFER, split_file_len))
            split_file_len -= len(buffer)
            if buffer == '': #eof
                break
            new_fh.write(buffer)
        # if reach here, new file has reach split size
        new_fh.close()
        filesize -= (split_size - split_file_len)
        i += 1

    fh.close()


    for i in xrange(1,split_num+1):
        split_file_path = '%s.pyfilesplit.%s' % (filename, i)
        new_fh = file(split_file_path,'wb')
        if i == split_num:      # just read whatever left
            buffer = fh.read()
        else:
            buffer = fh.read(split_size)
        new_fh.write(buffer)
        new_fh.close()
    fh.close()

def splitfile_by_size(filename, split_size):
    # split file by split file size limitation
    # 11 byte @ 3kb => 3 + 3 + 3 + 2
    split_size = int(split_size*1024*1024)
    filesize = os.path.getsize(filename)
    if filesize <= split_size:
        raise Exception, "File is already smaller than split file size"
    fh = file(filename,'rb')

    i = 1
    while filesize > 0:
        split_file_path = '%s.pyfilesplit.%s' % (os.path.basename(filename), i)
        new_fh = file(split_file_path,'wb')
        split_file_len = split_size
        while split_file_len > 0:
            buffer = fh.read(min(READ_BUFFER, split_file_len))
            split_file_len -= len(buffer)
            if buffer == '': #eof
                break
            new_fh.write(buffer)
        # if reach here, new file has reach split size
        new_fh.close()
        filesize -= (split_size - split_file_len)
        i += 1

    fh.close()

def joinfile(filename):
    if 'pyfilesplit' not in filename:
        filename += '.pyfilesplit.1'
        if not os.path.exists(filename):
            print 'Can\'t find ' + filename
            sys.exit()
    elif not filename.endswith('pyfilesplit.1'):
        print 'Pick file which end with pyfilesplit.1'
        sys.exit()
    fh_write = file(os.path.basename(filename)[0:-14],'wb')
    i = 1
    while 1:
        try:
            print 'opening', filename[0:-1]+str(i)
            fh_read = file(filename[0:-1]+str(i),'rb')
            while 1:
                buffer = fh_read.read(READ_BUFFER)
                if not buffer: break
                fh_write.write(buffer)
            fh_read.close()
        except IOError:
            break
        i += 1
    fh_write.close()


if __name__ == '__main__':
    if len(sys.argv) < 2:
        print 'usage (split by number): %s -n <filename> <number_of_split>' % sys.argv[0]
        print 'usage (split by max filesize): %s -s <filename> <max_split_filesize_in_MB>' % sys.argv[0]
        print 'usage (join): %s -j <filename>' % sys.argv[0]
        sys.exit()
    if sys.argv[1] == '-j':
        joinfile(sys.argv[2])
    elif sys.argv[1] == '-n':
        filename, split_num = sys.argv[2:4]
        splitfile_by_num(filename, int(split_num))
    elif sys.argv[1] == '-s':
        filename, split_size = sys.argv[2:4]
        splitfile_by_size(filename, float(split_size))
