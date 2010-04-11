import sys,os,binascii,shutil,stat,time,wx
title = 'deduper v1.1 by dsw s/h'

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

    #~ #scan file list
    #~ fs = [path+'/'+f for f in os.listdir(path) if stat.S_ISREG(os.stat(path+'/'+f)[stat.ST_MODE])]
    #~ for f in os.listdir(path):
        #~ s = os.stat(path+'/'+f)
        #~ if stat.S_ISREG(s[stat.ST_MODE]):
            #~ fs.append([path+'/'+f,s[stat.ST_SIZE]])
    #~ dlg.Show(False)
    #~ dlg.Destroy()

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
        cancelled_pressed = not dlg.Update(i,"Files: %s/%s - Dupes: %s"%(i,max,totaldupefiles))
        if cancelled_pressed: break
        #check size
        if not sizelist.has_key(size):
            sizelist[size] = 1
            continue
        #suspected dupe, let's check crc
        crc_number = binascii.crc32(file(filename,'rb').read())
        if crclist.has_key(crc_number):
            shutil.copy(filename,movedir)
            os.remove(filename)
            totaldupefiles += 1
        else:
            crclist[crc_number] = 1
    dlg.Show(False)
    dlg.Destroy()

    #final result window
    if totaldupefiles == 0:
        message = "No duplicate file found"
    else:
        message = "Done deduping %s %d files" % (path,totaldupefiles)
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