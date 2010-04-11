"""
pydedupe.py: remove duplicate files in folder recursively
copyright 2004, dody suria wijaya's software house < dodysw@gmail.com >

Requires: Python 2.3.x, wxPython 2.5.x.x

Algorithm:
1. collect list of files to check
2. retrieve file size
3. group file by file size
4. compare crc for group member > 1
"""

import sys,os,binascii,shutil,stat,time

__appname__ = 'pyDeduper'
__version__ = '1.3'
__author__  = 'Dody Suria Wijaya <dodysw@gmail.com>'

title = '%s v%s - %s' % (__appname__, __version__, __author__)

def do(paths, minfilesize=0, action_move=False, targetdir="", guiMode=False):
    if not paths:
        paths = ['.']  # scan current folder by default
    else:
        # make sure path is exist
        for path in paths:
            if not os.path.exists(path):
                print "%s does not exist!" % path
                sys.exit()

    if action_move:
        try: os.mkdir(targetdir)
        except OSError: pass

    starttime = time.clock()

    #walk recursive folders
    fs = {}
    if guiMode:
        dlg = wx.ProgressDialog(title + " - Scanning file list...","Scanning file list...",10, None, wx.PD_CAN_ABORT | wx.PD_APP_MODAL | wx.PD_AUTO_HIDE | wx.PD_REMAINING_TIME)
        idx = 0
        cancelled_pressed = False
    else:
        print "Scanning file list..."
    for path in paths:
        print path
        for root, dirs, files in os.walk(unicode(path)):
            for filename in files:
                filename = os.path.join(root,filename)
                filesize = os.path.getsize(filename)
                if filesize < minfilesize:
                    #~ print "Skipping %s (%d < %d)" % (filename, filesize, minfilesize)
                    continue
                fs[filename] = filesize
            if guiMode:
                idx += 1
                if idx == 10:
                    idx = 0
                cancelled_pressed = not dlg.Update(idx,"%s\nFiles: %d" % (root,len(fs)))
                if cancelled_pressed:
                    break
        if guiMode and cancelled_pressed:
            break
    if guiMode:
        dlg.Show(False)
        dlg.Destroy()
    else:
        print "Got %d files" % len(fs)

    #deduping...
    if guiMode:
        max = len(fs)-1
        dlg = wx.ProgressDialog(title+" - deduping...","Deduping...", max, None, wx.PD_CAN_ABORT | wx.PD_APP_MODAL | wx.PD_AUTO_HIDE | wx.PD_REMAINING_TIME)
        idx = 0
        cancelled_pressed = False
    else:
        print "Deduping..."
    sizelist = {}
    size_set = set()
    totaldupefiles = 0
    c = {}
    for filename, size in fs.iteritems():

        if guiMode:
            if idx % 10 == 0:
                cancelled_pressed = not dlg.Update(i,"Files: %s/%s - Dupes: %s"%(i,max,totaldupefiles))
                if cancelled_pressed:
                    break
            wx.Yield()  # update gui

        # if there is no other file with same byte size, consider this as a unique file
        if not sizelist.has_key(size):
            sizelist[size] = [dict(crc_number=None, filename=filename, filedupes=[])]
            sys.stderr.write(".")
            continue

        # there's 1 or more file(s) with the same size.
        # let's compare the crc
        crc_number = binascii.crc32(file(filename,'rb').read())
        isDupe = False
        readCrc = False
        for fobj in sizelist[size]:
            if fobj['crc_number'] == None:
                fobj['crc_number'] = binascii.crc32(file(fobj['filename'],'rb').read())
                readCrc = True
            if fobj['crc_number'] == crc_number:
                isDupe = True
                fobj['filedupes'].append(filename)
                break

        if isDupe:
            readCrc and sys.stderr.write("D") or sys.stderr.write("d")
            totaldupefiles += 1
            if action_move:
                if not guiMode:
                    print "Moving %s to %s..." % (filename, targetdir)
                try:
                    shutil.copy(filename,targetdir)
                    try:
                        os.remove(filename)
                    except OSError:
                        print 'cannot remove', filename
                except IOError:
                    print 'cannot copy', filename
        else:
            readCrc and sys.stderr.write("S") or sys.stderr.write("s")
            sizelist[size].append(dict(crc_number=crc_number,filename=filename,filedupes=[]))

    if guiMode:
        dlg.Show(False)
        dlg.Destroy()
    else:
        # display report
        sf = []
        for i,(size,fobjs) in enumerate(sizelist.items()):
            for fobj in fobjs:
                if not fobj['filedupes']:
                    continue
                fobj['filedupes'].sort()
                sf.append((fobj['filename'],fobj['filedupes']))
        sf.sort()
        for filename,dupes in sf:
            print filename
            for dupe in dupes:
                print " > %s" % dupe
    deltatime = time.clock() - starttime

    #final result window
    if totaldupefiles == 0:
        message = "No duplicate file found. Finished in %0.2f sec" % deltatime
    else:
        message = "Done deduping %s %d files. Finished in %0.2f sec" % (path,totaldupefiles,deltatime)
    if guiMode:
        dlg = wx.MessageDialog(None, message,title, wx.OK | wx.ICON_INFORMATION)
        dlg.ShowModal()
        dlg.Destroy()
    else:
        print message

def main(sourcedirs, options):
    do(sourcedirs, minfilesize=options.minfilesize, action_move=options.move, targetdir=options.target_dir, guiMode=False)

def mainGui(sourcedirs, options):
    class MyApp(wx.App):
        def OnInit(self):
            wx.InitAllImageHandlers()
            if not sourcedirs:
                dlg = wx.DirDialog(None, "Choose a directory to dedupe:",defaultPath = os.getcwd())
                if dlg.ShowModal() == wx.ID_OK:
                    path = dlg.GetPath()
                    dlg.Destroy()
                else:
                    dlg.Destroy()
                    return
                sourcedirs = [path,]
            do(sourcedirs, minfilesize=options.minfilesize, action_move=options.move, targetdir=options.target_dir, guiMode=True)
            return True

    app = MyApp(0)


if __name__ == '__main__':
    import optparse
    usage = 'usage: %s [options] [path1] [path2] [path3] ...' % sys.argv[0]
    parser = optparse.OptionParser(usage)
    parser.add_option("--minfilesize", dest="minfilesize", type="int", help="minimum file size in Bytes to include", default=0)
    parser.add_option("-m", "--move", action="store_true", dest="move", help="move duplicate file(s)", default=False)
    parser.add_option("-t", "--targetdir", dest="target_dir", help="target directory to move duplicate files", default=None)
    parser.add_option("-g", "--gui", action="store_true", dest="gui", help="use GUI", default=False)

    options, args = parser.parse_args()
    sourcedirs = args

    if options.gui:
        import wx
        mainGui(sourcedirs, options)
    else:
        main(sourcedirs, options)
