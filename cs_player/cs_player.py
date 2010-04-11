"""
CS Player Alarm
Copyright 2005, Dody Suria Wijaya <dodysw@gmail.com>
to use this software, you must install Python 2.3.x or above, and wxPython 2.5.x or above
to compile into independent .exe, you must install py2exe
visit:
    - http://www.python.org
    - http://www.wxpython.org
    - http://starship.python.net/crew/theller/py2exe/
"""
import socket, wx, time, sys, threading
__version__ = '1.5.0'
__description__ = 'CS Player Alarm'
__author__ = 'Dody Suria Wijaya <dodysw@gmail.com>'
def check(addr, port):
    global misc, server_addr, server_port, mapname, server_name
    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    server_addr, server_port = addr, port
    sock.connect((addr, port))
    sock.send('\xff\xff\xff\xffinfo\x00')   # get server info
    buffer = sock.recv(100)
    ar = buffer.split('\x00')
    if ar[5] == '':
        t1 = t2 =0
    else:
        if ar[5][0] != 0: t1 = ord(ar[5][0])
        if ar[5][1] != 0: t2 = ord(ar[5][1])
    server_name, mapname = ar[1], ar[2]
    sock.send('\xff\xff\xff\xffplayers\x00')    # get player
    buffer = sock.recv(1000)
    sock.close()
    num = buffer[5]
    buffer = buffer[7:]
    names = []
    while len(buffer) > 0:
        pos = buffer.find('\x00')
        name = buffer[0:pos]
        name = name.replace('[No C-D]','')  # none uses cheatdeath
        frag = ord(buffer[pos+1]) + (ord(buffer[pos+2]) << 8) + (ord(buffer[pos+3])<<16) + (ord(buffer[pos+4])<<24)
        names.append([frag,name])
        buffer = buffer[pos + 10:];
    if len(names):
        names.sort()
        ff = []
        for i in range(1,len(names)+1):
            ff.append('%s -> %s' % (names[-i][0], names[-i][1]))
        misc = '\n'.join(ff)
    else:
        misc = 'no one is playing...'
    return t1,t2,mapname

class DemoTaskBarIcon(wx.TaskBarIcon):
    TBMENU_CLOSE   = wx.NewId()
    def __init__(self):
        wx.TaskBarIcon.__init__(self)
        self.SetIcon(tbi_icon, __description__)
        self.Bind(wx.EVT_TASKBAR_LEFT_DCLICK, self.OnTaskBarView)

    def CreatePopupMenu(self):
        menu = wx.Menu()
        item = menu.Append(-1, "View score")
        self.Bind(wx.EVT_MENU, self.OnTaskBarView, item)
        menu.AppendSeparator()
        item = menu.Append(-1, "Close me")
        self.Bind(wx.EVT_MENU, self.OnTaskBarClose, item)
        return menu

    def OnTaskBarView(self, evt):
        global misc, dlg_view
        msg = '%s (%s:%s)\nMap: %s\nTime: %s\n\nPlayers Top Scorer:\n%s' % (server_name, server_addr, server_port, mapname, time.strftime('%H:%M:%S'), misc)
        dlg_view = wx.Dialog(None, -1, '%s %s %s' % (__description__, __version__, __author__))
        sizer = wx.BoxSizer(wx.VERTICAL)
        dlg_view.label = wx.StaticText(dlg_view, -1, msg)
        sizer.Add(dlg_view.label, 0, wx.ALIGN_CENTRE|wx.ALL, 5)
        sizer.Add(wx.Button(dlg_view, wx.ID_OK), 0, wx.ALIGN_CENTRE|wx.ALL, 5)
        dlg_view.SetSizer(sizer)
        sizer.Fit(dlg_view)
        dlg_view.ShowModal()
        dlg_view.Destroy()
        dlg_view = None

    def OnTaskBarClose(self, evt):
        global done
        self.RemoveIcon()
        done = True
        sys.exit()

def mythread():
    global reached, done, dlg_view
    st = 0
    while not done:
        t1,t2,mapname = check(options.server_ip, options.server_port)
        ico = tbi_icon
        if t1 >= min_players: ico = tbi_icon2
        tbi.SetIcon(ico, '%s:%s - %s of %s (>%s) %s' % (options.server_ip, options.server_port, t1,t2,min_players,mapname))
        if dlg_view:
            msg = '%s (%s:%s)\nMap: %s\nTime: %s\n\nPlayers Top Scorer:\n%s' % (server_name, server_addr, server_port, mapname, time.strftime('%H:%M:%S'), misc)
            dlg_view.label.SetLabel(msg)
        if t1 >= min_players and not reached:
            dlg = wx.MessageDialog(None, 'CS Server has %s players, FORGET ESSAY, FORGET EXAM, LETS PLAY!!!' % t1,'%s %s %s' % (__description__, __version__, __author__), wx.OK | wx.ICON_INFORMATION)
            dlg.ShowModal()
            dlg.Destroy()
            reached = True
        elif t1 < min_players and reached:
            reached = False
        time.sleep(10)

if __name__ == '__main__':
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("-s", "--server_ip", dest="server_ip", help="CS Server address (def:150.203.239.103)", default='150.203.239.103')
    parser.add_option("-p", "--server_port", type="int", dest="server_port", help="CS Server port (def:27015)", default=27015)
    parser.add_option("-m", "--min_players", type="int", dest="min_players", help="Minimum player(s) to trigger alarm")
    options, args = parser.parse_args()
    app = wx.PySimpleApp()
    reached, done, dlg_view, min_players = False, False, None, options.min_players
    if not min_players:
        dlg = wx.SingleChoiceDialog(None, 'CS Server: %s:%s\nNote: To change, do a cs_server.exe --help to see how\n\nPick the minimum number of players\nI\'ll remind you if it\'s been reached.' % (options.server_ip, options.server_port), '%s %s %s' % (__description__, __version__, __author__), [str(i) for i in range(1,31)],  wx.CHOICEDLG_STYLE )
        if dlg.ShowModal() != wx.ID_OK:
            sys.exit()
        min_players = int(dlg.GetStringSelection())
        dlg.Destroy()
    tbi_icon, tbi_icon2 = wx.Icon("game.ico",wx.BITMAP_TYPE_ICO), wx.Icon("game2.ico",wx.BITMAP_TYPE_ICO)
    tbi = DemoTaskBarIcon()
    t = threading.Thread(target = mythread)
    t.start()
    app.MainLoop()