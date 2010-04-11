import glob, os.path
import email, email.Iterators, email.Errors
import sys, wx

def process(path_source, path_result):
    try: os.mkdir(path_result)
    except: pass
    print 'DOING!'
    for filename in glob.glob(os.path.join(path_source,'*.msg')) + glob.glob(os.path.join(path_source,'*.eml')):
        print 'Parsing',filename
        buffer = file(filename).read()
        buffer += '\r\n'    # make sure end with emptyline (email parser tripped at this)
        try:
            msg = email.message_from_string(buffer)
        except email.Errors.BoundaryError:
            print '--boundary error--'
            continue
        for part in msg.walk():
            #~ if part.get_content_maintype() == 'image':
            #~ if 1:
            if part.get_content_maintype() != 'multipart':
                print '->', part.get_content_type()
                buffer = part.get_payload(decode=True)
                if buffer is None:
                    print '--empty buffer--'
                    continue
                part_filename = (part.get_filename() or 'none.'+part.get_content_subtype())
                for char in r'?/\:*"<>|':
                    part_filename = part_filename.replace(char,'') # remove invalid characters
                if '.' not in part_filename: part_filename += '.'   #make sure . is in filename
                i = 1
                orig_part_filename = part_filename
                while os.path.exists(os.path.join(path_result,part_filename)):
                    #~ print part_filename, 'already exist',
                    l,r = orig_part_filename[0:orig_part_filename.rindex('.')], orig_part_filename[orig_part_filename.rindex('.')+1:]
                    part_filename = '%s-%s.%s' % (l, i, r)
                    #~ print 'trying',part_filename
                    i += 1
                print 'Saving', part_filename
                file(os.path.join(path_result,part_filename),'wb').write(buffer)


if __name__ == '__main__':
    source_path = target_path = ''
    if len(sys.argv)>1:
        source_path = sys.argv[1]
    if len(sys.argv)>2:
        target_path = sys.argv[2]
    app = wx.PySimpleApp()
    frm = wx.Dialog(None,-1,'WinRipAttachment 2005 - Dody Suria Wijaya',style=wx.DEFAULT_DIALOG_STYLE|wx.RESIZE_BORDER )

    # put widgets
    #~ pnl = wx.Panel(frm)
    pnl = frm
    c_src = wx.TextCtrl(pnl, -1)
    c_src.SetMinSize((200,-1))
    bt_src = wx.Button(pnl, -1, 'Browse')
    c_tgt = wx.TextCtrl(pnl, -1)
    c_tgt.SetMinSize((200,-1))
    bt_tgt = wx.Button(pnl, -1, 'Browse')

    sz = wx.FlexGridSizer(-1, 3, 5, 5)
    sz.AddGrowableCol(1)
    sz.Add(wx.StaticText(pnl, -1, 'Source folder'))
    sz.Add(c_src,1,wx.EXPAND)
    sz.Add(bt_src)
    sz.Add(wx.StaticText(pnl, -1, 'Target folder'))
    sz.Add(c_tgt,1,wx.EXPAND)
    sz.Add(bt_tgt)

    ss = wx.BoxSizer(wx.VERTICAL)
    ss.Add(wx.StaticText(pnl, -1, 'Disassemble multipart mime email files located on folder source, \nand save all of its component to target folder'), 0, wx.ALL, 5)
    ss.Add(wx.StaticLine(pnl, -1), 0, wx.EXPAND | wx.ALL, 5)
    ss.Add(sz,1,wx.EXPAND)
    bt_process = wx.Button(pnl, -1, '&Start Ripping it!')
    ss.Add(bt_process,0,wx.ALIGN_CENTER | wx.ALL, 5)
    frm.SetSizer(ss)
    ss.Fit(frm)

    # put event handler
    def handle_src(event):
        path = c_src.GetValue()
        if path == '':
            path = os.getcwd()
        dlg = wx.DirDialog(None, "Choose source folder containing msg files:",defaultPath = path,style=wx.DD_DEFAULT_STYLE|wx.DD_NEW_DIR_BUTTON)
        if dlg.ShowModal() == wx.ID_OK:
            c_src.SetValue(dlg.GetPath())
        dlg.Destroy()
    def handle_tgt(event):
        path = c_src.GetValue()
        if path == '':
            path = os.getcwd()
        dlg = wx.DirDialog(None, "Choose folder to place results:",defaultPath = path,style=wx.DD_DEFAULT_STYLE|wx.DD_NEW_DIR_BUTTON)
        if dlg.ShowModal() == wx.ID_OK:
            c_tgt.SetValue(dlg.GetPath())
        dlg.Destroy()

    def handle_process(event):
        process(c_src.GetValue(), c_tgt.GetValue())
        dlg = wx.MessageDialog(frm, 'Done', 'Result', style=wx.OK)
        dlg.ShowModal()

    frm.Bind(wx.EVT_BUTTON, handle_src, bt_src)
    frm.Bind(wx.EVT_BUTTON, handle_tgt, bt_tgt)
    frm.Bind(wx.EVT_BUTTON, handle_process, bt_process)
    frm.Bind(wx.EVT_UPDATE_UI, lambda event: event.Enable(c_src.GetValue() != '' and c_tgt.GetValue() != ''), bt_process)

    frm.ShowModal()
    frm.Destroy()
    frm.SetAutoLayout(True)
    app.SetTopWindow(frm)
    app.MainLoop()
