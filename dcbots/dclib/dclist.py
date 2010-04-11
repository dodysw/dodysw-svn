"""
DC file list reader and writer module
Copyright 2006 Dody Suria Wijaya <dodysw@gmail.com>
Supports:
- HE3 compressed file list (MyList.DcLst) => only reading
- XML Bzipped file list
"""

import he3
import random, md5, os

random.seed()

import datetime, threading

class Share:
    """Represent a single share object
    """
    def __init__(self, name, size, tth):
        self.name = name
        self.size = size
        self.tth = tth


def log(data):
    if __debug__:
        print '%s [%s] %s' % (datetime.datetime.now().ctime(), threading.currentThread().getName(), data)

def parse_dclst(path='MyList.DcLst'):
    """
    Parse decompressed DcLst file list format:

    dummyshare
        dummy.dat|262144000
    FreeBooks
        Mac
            PowerPC Programming Manual.pdf|10928806

    [one_path_dir]["\r\n"]
    ["\t"* depends on the level]([one_path_dir]["\r\n"]])*)
    ["\t"* depends on the level][filename_with_extension]["|"][file_size_bytes]["\r\n"]
    ..
    ..
    @return = list of tuple[path_to_file,filename,file_size_int]

    """
    decoded_path = md5.new(str(random.randint(100000,999999))).hexdigest()+'.tmp'
    he3.he3_decoder(path, decoded_path)

    all_files = []
    last_dir = []
    last_tab_count = 0
    try:
        fh = file(decoded_path)
        for line in fh:
            if '|' in line:
                filename, size = line.strip().split('|')
                size = int(size)
                all_files.append((os.path.join(*last_dir), filename, size))
            else:
                #a dir
                c = line.count('\t')
                diff_depth = c - last_tab_count
                if diff_depth > 0:
                    assert(diff_depth == 1)
                elif diff_depth < 0:
                    # change last c+1 dir
                    last_dir = last_dir[:c]
                last_dir.append(line.strip())
                last_tab_count = c
    finally:
        fh.close()
        os.unlink(decoded_path)
    return all_files

def write_xml_list(obj):
    """
    e.g.
    <FileListing Version="1" Generator="dc client name and version">
        <Directory Name="xxx">
            <Directory Name="yyy">
                <File Name="zzz" Size="1"/>
            </Directory>
        </Directory>
    </FileList>
    """
    pass


import bz2
BZ_READ_BUFFER = 131072
def uncompress_bz2(path):
    """
    read a bz2 file, then write into a file without .bz2, and return the filename
    """
    assert path.endswith('.bz2'), "This function assumes that the path ends with .bz2"
    target_path = path[:-4]
    log("converting [%s] to [%s]" % (path, target_path))
    fh = bz2.BZ2File(path)
    fhw = file(target_path,'wb')
    try:
        while 1:
            buffer = fh.read(BZ_READ_BUFFER)
            if not buffer:
                break
            fhw.write(buffer)
    finally:
        fh.close()
        fhw.close()
    return target_path

import xml.sax
class XmlListHandler(xml.sax.handler.ContentHandler):

    def __init__(self):
        self._dir = []
        self._all = []

    def startElement(self, name, attrs):
        if name == 'File':
            #Note: Name value is unicode, so don't just print to console
            try:
                self._all.append(('/'.join(self._dir + [attrs['Name']]), int(attrs['Size']), attrs['TTH']))
            except KeyError, e:
                #~ print "XML Parsing ERROR! on name=%r, attr=%s, error:%s" % (name, attrs.getNames(), e)
                pass

        elif name == 'Directory':
            self._dir.append(attrs['Name'])

    def endElement(self, name):
        if name == 'Directory':
            del self._dir[-1]

    def getData(self):
        return self._all

class XmlTTFDictHandler(xml.sax.handler.ContentHandler):

    def __init__(self):
        self._dir = []
        self._all = {}

    def startElement(self, name, attrs):
        if name == 'File':
            #Note: Name value is unicode, so don't just print to console
            try:
                self._all[attrs['TTH']] = ('/'.join(self._dir + [attrs['Name']]), int(attrs['Size']))
            except KeyError, e:
                pass

        elif name == 'Directory':
            self._dir.append(attrs['Name'])

    def endElement(self, name):
        if name == 'Directory':
            del self._dir[-1]

    def getData(self):
        return self._all

def read_xml_list(fh, xml_handler=XmlListHandler):
    """
    turn uncompressed xmllist into list of files, with: fullpath+name, size, and TTH.
    Orig:
    <?xml version="1.0" encoding="utf-8" standalone="yes"?>
    <FileListing Version="1" CID="HBLJX76PGD5RG" Base="/" Generator="DC++ 0.674">
        <Directory Name="dummyshare">
            <File Name="dummy.dat" Size="262144000" TTH="SOG5PMSPWIVM43OQLSJH75NL5KOMMXPMNNVPSLA"/>
        </Directory>
        <Directory Name="Linux">
            <File Name="Using Linux As A Router.pdf" Size="30965" TTH="273XEJPMMVZ6YFDQBLCDK5TIBD37AT5WFQ7UEMQ"/>
            <File Name="The Linux Cookbook - Tips and Techniques for Everyday Use.pdf" Size="7494369" TTH="PVOPV3AW2YZLGRKRCDFYEHIU5GQZTC4IYO3P4EI"/>
        </Directory>
    </FileListing>

    Target:
    [
        ["/dummyshare/dummy.dat", 262144000, "SOG5PMSPWIVM43OQLSJH75NL5KOMMXPMNNVPSLA"],
        ["/Linux/Using Linux As A Router.pdf", 30965, "273XEJPMMVZ6YFDQBLCDK5TIBD37AT5WFQ7UEMQ"],
        ["/Linux/The Linux Cookbook - Tips and Techniques for Everyday Use.pdf", 7494369, "PVOPV3AW2YZLGRKRCDFYEHIU5GQZTC4IYO3P4EI"],
    ]
    """
    parser = xml.sax.make_parser()
    handler = xml_handler()
    parser.setContentHandler(handler)
    #~ log("Parsing xml file list at [%s]" % path)
    try:
        parser.parse(fh)
    except xml.sax.SAXParseException, e:
        print "XML Parsing Error: %s" % e

    return handler.getData()


def iter_xml_bz2_list(path):
    for share in read_xml_list(uncompress_bz2(path)):
        yield Share(share[0], share[1], share[2])

def get_xml_list(path):
    fh = file(path, 'rb')
    try:
        return read_xml_list(fh)
    finally:
        fh.close()

def get_xml_dict(path):
    fh = file(path, 'rb')
    try:
        return read_xml_list(fh, xml_handler=XmlTTFDictHandler)
    finally:
        fh.close()

def parse_write_xml_list(fh, level):
    pass


# ----------------from Python Cookbook
import os, itertools
def all_equal(elements):
    ''' return True if all the elements are equal, otherwise False. '''
    first_element = elements[0]
    for other_element in elements[1:]:
        if other_element != first_element: return False
    return True

def common_prefix(*sequences):
    ''' return a list of common elements at the start of all sequences,
        then a list of lists that are the unique tails of each sequence. '''
    # if there are no sequences at all, we're done
    if not sequences: return [  ], [  ]
    # loop in parallel on the sequences
    common = [  ]
    for elements in itertools.izip(*sequences):
        # unless all elements are equal, bail out of the loop
        if not all_equal(elements): break
        # got one more common element, append it and keep looping
        common.append(elements[0])
    # return the common prefix and unique tails
    return common, [ sequence[len(common):] for sequence in sequences ]

def relpath(p1, p2, sep=os.path.sep, pardir=os.path.pardir):
    ''' return a relative path from p1 equivalent to path p2.
        In particular: the empty string, if p1 == p2;
                       p2, if p1 and p2 have no common prefix.
    '''
    common, (u1, u2) = common_prefix(p1.split(sep), p2.split(sep))
    if not common:
        return p2      # leave path absolute if nothing at all in common
    return sep.join( [pardir]*len(u1) + u2 )
# ----------------


def write_xml_list(shares, fh, sorted=True):
    """given a list of shares, write a xml list file
    @shares is a list of [name_with_path, size, tth] whereas name_with_path is separated with '/'
    """
    # first, sort the shares based on the name_with_path
    if not sorted:
        shares.sort()   #simply, since the sort key is the first value of list
    fh.write('<?xml version="1.0" encoding="utf-8" standalone="yes"?>\n')
    fh.write('<FileListing Version="1" CID="HBLJX76PGD5RG" Base="/" Generator="Whatsnew DCBot">\n')
    prev_dir = []
    level = 0
    for share in shares:    # assume that shares is sorted by path
        share_path, size, tth = share
        share_dir = os.path.dirname(share_path).split('/')
        filename = os.path.basename(share_path)

        # first remove common dir prefix of previous and current dir from current dir
        # if prevdir = /a/b/c and sharedir= /a/b/x/y/z, common = ['a','b'], u1 = ['c'], u2 = ['x','y','z']
        common, (u1, u2) = common_prefix(prev_dir, share_dir)
        prev_dir = share_dir

        # go back if needed
        for i in xrange(len(u1)):
            level -= 1
            #~ print level
            fh.write('%s</Directory>\n' % ('\t'*level))

        # then step through the remaining dirs, outputing Directory leaf on each step. Or do nothing if no remaining left.
        for dir in u2:
            fh.write('%s<Directory Name="%s">\n' % ('\t'*level, dir.replace('&', '&amp;')))
            level += 1

        # output the file name
        #~ fh.write('%s<File Name="%s" Size="%d" TTH="%s"/>\n' % ('\t'*level, filename.encode('UTF-8'), size, tth.encode('UTF-8')))
        fh.write('%s<File Name="%s" Size="%d" TTH="%s"/>\n' % ('\t'*level, filename.replace('&', '&amp;'), size, tth))

    # close remaining tags
    for i in xrange(level):
        level -= 1
        fh.write('%s</Directory>\n' % ('\t'*level))

    fh.write('</FileListing>\n')


def test():
    print parse_dclst(sys.argv[1])

def test2():
    print read_xml_bz2_list(sys.argv[1])

def test3():
    tth = get_tth(r"g:\master\pagedefrag.zip")
    assert tth == "J2HRBJD4UDRJNSNRUXQIS4567BLX6OVREX7IMYI", "Instead got: %s" % tth

def test4():
    write_xml_list(read_xml_list(uncompress_bz2(sys.argv[1])), 'written.xml')

if __name__ == '__main__':
    import sys
    test4()