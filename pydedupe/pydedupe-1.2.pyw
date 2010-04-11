"""
pydedupe.py: remove duplicate files in folder recursively
copyright 2004, dody suria wijaya's software house < dodysw@gmail.com >

Requires: Python 2.3, wxPython 2.5.x.x

Algorithm:
1. collect list of files to check
2. retrieve file size
3. group file by file size
4. compare crc for group member > 1
"""

import sys,os,binascii,shutil,stat,time,wx
title = 'deduper v1.2 by dsw s/h'
def do(path):
    if not path:
        dlg = wx.DirDialog(None, "Choose a directory to dedupe:",defaultPath = os.getcwd())
        if dlg.ShowModal() == wx.ID_OK:
            path = dlg.GetPath()
            dlg.Destroy()
        else:
            dlg.Destroy()
            return
    #movedir = path+'/dupes'
    movedir = '/_dupes'
    try:
        os.mkdir(movedir)
    except:
        pass

    starttime = time.clock()

    #walk recursive folder
    fs = []
    totalfiles = idx = 0
    dlg = wx.ProgressDialog(title+" - Scanning file list...","Scanning file list...",10,None,wx.PD_CAN_ABORT | wx.PD_APP_MODAL | wx.PD_AUTO_HIDE | wx.PD_REMAINING_TIME)
    for root, dirs, files in os.walk(path):
        fs.extend([(os.path.join(root,filename),os.path.getsize(os.path.join(root,filename))) for filename in files])
        totalfiles += len(files)
        idx += 1
        cancelled_pressed = not dlg.Update(idx,"Folder: %s - Files: %d" % (root,totalfiles))
        if cancelled_pressed: break
        if idx == 10: idx = 0
    dlg.Show(False)
    dlg.Destroy()

    #deduping...
    max = len(fs)-1
    dlg = wx.ProgressDialog(title+" - deduping...","Deduping...",max,None,wx.PD_CAN_ABORT | wx.PD_APP_MODAL | wx.PD_AUTO_HIDE | wx.PD_REMAINING_TIME)
    crclist = sizelist = {}
    totaldupefiles = 0
    for i,(filename,size) in enumerate(fs):
        ##print filename,
        if i % 10 == 0:
            cancelled_pressed = not dlg.Update(i,"Files: %s/%s - Dupes: %s"%(i,max,totaldupefiles))
            if cancelled_pressed: break
        while 1:
            #CHECK 1 - check size
            if not sizelist.has_key(size):
                sizelist[size] = filename
                #print 'newsize',size,
                break
            ##print 'oldsize',size,
            #CHECK 2 - let's check crc
            # - first, get crc for the first filename of this filesize
            if type(sizelist[size]) == str:
                crc_number = binascii.crc32(file(sizelist[size],'rb').read())
                sizelist[size] = {crc_number:1}
            # - then continue checking current filename
            crc_number = binascii.crc32(file(filename,'rb').read())
            if not sizelist[size].has_key(crc_number):
                sizelist[size][crc_number] = 1
                ##print 'newcrc',
            else:
                ##print 'oldcrc-dupe!',
                shutil.copy(filename,movedir)
                os.remove(filename)
                totaldupefiles += 1
            break
        ##print
    dlg.Show(False)
    dlg.Destroy()

    deltatime = time.clock() - starttime

    #final result window
    if totaldupefiles == 0:
        message = "No duplicate file found (checked in %0.2f seconds)" % deltatime
    else:
        message = "Done deduping %s %d files (checked in %0.2f seconds)" % (path,totaldupefiles,deltatime)
    dlg = wx.MessageDialog(None, message,title, wx.OK | wx.ICON_INFORMATION)
    dlg.ShowModal()
    dlg.Destroy()

if __name__ == '__main__':
    class MyApp(wx.App):
        def OnInit(self):
            wx.InitAllImageHandlers()
            path = None
            if len(sys.argv)>1:
                path = sys.argv[1]
            do(path)
            return True
    app = MyApp(0)
