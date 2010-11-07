"""
this utility move all photos inside given path recursively by to folder with year 2005, 2006, 2007,...
based on photo's exif photo taken date's year, or if not available, by its file modification date
within the same subfolder as that photo relative to given path. ie:

> cd \result
> photogroup.py "c:\My Documents\My Pictures"

where c:\My Documents\My Pictures contains + dated:
c:\My Documents\My Pictures\graduation\me.jpg           exif=1/5/2006
c:\My Documents\My Pictures\graduation\you.jpg          exif=2/5/2006
c:\My Documents\My Pictures\canberra\zoo\friends.jpg    exif=5/5/2003

will create this:
\result\2003\
\result\2003\canberra\
\result\2003\canberra\zoo\
\result\2003\canberra\zoo\friends.jpg
\result\2006\graduation\me.jpg
\result\2006\graduation\you.jpg
"""

import sys, os, EXIF, time

MOVE_CMD = os.name == 'nt' and 'move' or 'mv'
MKDIR_CMD = 'mkdir'

def get_exif(file, debug=0):
    # determine whether it's a JPEG or TIFF
    data=file.read(12)
    if data[0:4] in ['II*\x00', 'MM\x00*']:
        # it's a TIFF file
        file.seek(0)
        endian=file.read(1)
        file.read(1)
        offset=0
    elif data[0:2] == '\xFF\xD8':
        # it's a JPEG file
        # skip JFIF style header(s)
        fake_exif=0
        while data[2] == '\xFF' and data[6:10] in ('JFIF', 'JFXX', 'OLYM'):
            length=ord(data[4])*256+ord(data[5])
            file.read(length-8)
            # fake an EXIF beginning of file
            data='\xFF\x00'+file.read(10)
            fake_exif=1
        if data[2] == '\xFF' and data[6:10] == 'Exif':
            # detected EXIF header
            offset=file.tell()
            endian=file.read(1)
        else:
            # no EXIF information
            return {}
    else:
        # file format not recognized
        return {}

    # deal with the EXIF info we found
    hdr = EXIF.EXIF_header(file, endian, offset, fake_exif, debug)
    ifd_list=hdr.list_IFDs()
    ctr=0
    for i in ifd_list:
        if ctr == 0:
            IFD_name='Image'
            hdr.dump_IFD(i, IFD_name)
        # EXIF IFD
        exif_off=hdr.tags.get(IFD_name+' ExifOffset')
        if exif_off:
            hdr.dump_IFD(exif_off.values[0], 'EXIF')

        ctr+=1
    return hdr.tags

def main(path):
    path = os.path.abspath(path)

    if not os.path.exists(path):
        print "%s does not exist!" % path
        sys.exit()

    ltp = len(path)
    cwd = os.getcwd()
    years = {}
    output = []
    mkdirset = set()
    for root, dirs, files in os.walk(path):
        for filename in files:
            file_path = os.path.join(root, filename)
            try:
                fh = file(file_path, 'rb')
            except IOError:
                continue
            note = ''
            tags = None
            try:
                tags = get_exif(fh, debug=False)
            except ValueError, e:
                #~ note += "# EXIF error: %s" % e
                pass

            y = year_file = str(time.localtime(os.path.getmtime(file_path))[0])
            if tags:
                try:
                    exif_date = str(tags["Image DateTime"])
                except KeyError:
                    exif_date = str(tags["EXIF DateTimeOriginal"])

                y = year_exif = exif_date[0:4]

                if int(year_exif) < 2000:   # digital camera date might not have been set
                    y = year_file
                    #~ note += "# year < 2000"
            #~ else:
                #~ note += "# using file mtime"

            years.setdefault(y, [])
            years[y].append(file_path)

            location = os.path.join(cwd, y, file_path[ltp+1:])
            ploc = os.path.dirname(location)
            if ploc not in mkdirset:
                mkdirset.add(ploc)
                print '%s "%s"' % (MKDIR_CMD, ploc)

            #~ #set modify date = exif date + 1 year
            #~ # - parse exif date string
            #~ if int(y) == 2004:
                #~ print exif_date
                #~ pdate = map(int, (exif_date[0:4], exif_date[5:7], exif_date[8:10], exif_date[11:13],exif_date[14:16],exif_date[17:20], -1, -1, -1))
                #~ pdate[0] += 1
                #~ new_date = time.mktime(pdate)
                #~ os.utime(file_path,(new_date, new_date))

            print '%s "%s" "%s"%s' % (MOVE_CMD, file_path, location, note and " <-- " + note)

if __name__ == '__main__':
    path = sys.argv[1]
    main(path)
