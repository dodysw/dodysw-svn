"""
CS Player Alarm
Copyright 2005, Dody Suria Wijaya <dodysw@gmail.com>prev_num
to use this software, you must install Python 2.3.x or above, and wxPython 2.5.x or above
to compile into independent .exe, you must install py2exe
visit:
    - http://www.python.org
    - http://www.wxpython.org
    - http://starship.python.net/crew/theller/py2exe/
"""
import socket, wx, time, sys, threading, struct, random, re
import warnings
warnings.filterwarnings('ignore','',FutureWarning)

__version__ = '2.1.0'
__description__ = 'CS Player Alarm'
__author__ = 'Dody Suria Wijaya <dodysw@gmail.com>'

# -- pydcbot config
nick = 'dodysw_bot'
description = 'Counter-strike bot'
tag = '<pydcbot++ V:0.1.0,M:A,H:1/0/0,S:1>'
connection_type = 'LAN(T3)'
email = 'dodysw@gmail.com'
sharesize = 1000000
state = 0
# --

random_asking = [
    'What are you waiting for?',
    'Join now!',
    'Let\'s go, baby',
    'Let\'s do it!',
    'Go go go',
    'Affirmative, sir',
    'Enemy sighted',
    'Sir, we\'re waiting for you',
    'Follow me!',
    'Let\'s get out of here, it\'s going to blow!'
    ]

def check(addr, port):
    global misc, server_addr, server_port, mapname, server_name, current_player_count
    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    sock.settimeout(3)
    server_addr, server_port = addr, port
    try:
        sock.sendto('\xff\xff\xff\xffinfo\x00',(addr, port))   # get server info
        buffer = sock.recv(50000)
        ar = buffer.split('\x00')
        if ar[5] == '':
            t1 = t2 =0
        else:
            if ar[5][0] != 0: t1 = ord(ar[5][0])
            if ar[5][1] != 0: t2 = ord(ar[5][1])
        server_name, mapname = ar[1], ar[2]
        sock.sendto('\xff\xff\xff\xffplayers\x00',(addr, port))    # get player
        buffer = sock.recv(50000)
        sock.close()
        num = buffer[5]
        buffer = buffer[7:]
        names = []
        while len(buffer) > 0:
            pos = buffer.find('\x00')
            name = buffer[0:pos]
            name = name.replace('[No C-D]','')  # none uses cheatdeath
            frag = ord(buffer[pos+1]) + (ord(buffer[pos+2]) << 8) + (ord(buffer[pos+3])<<16) + (ord(buffer[pos+4])<<24)
            #~ frag = ord(buffer[pos+1]) + pow(ord(buffer[pos+2]),9) + pow(ord(buffer[pos+3]),17) + pow(ord(buffer[pos+4]),25)
            playtime = time.strftime('%M:%S',time.localtime(int(struct.unpack('f',buffer[pos+5:pos+9])[0]) + 82800))
            names.append([frag,name,playtime])
            buffer = buffer[pos + 10:];
        if len(names):
            names.sort()
            ff = []
            for i in range(1,len(names)+1):
                ff.append('%s -> %s (%s)' % (names[-i][0], names[-i][1], names[-i][2]))
            misc = '\n'.join(ff)
        else:
            misc = 'no one is playing...'
        current_player_count = t1
        return t1,t2,mapname
    except socket.error, e: # usually because LAN is not available
        return False
    except Exception, e:
        # anyother error, log to file, then continue
        file('error.log','a').write('%s: %s\n' % (time.strftime('%Y-%m-%d %H:%M:%S'), str(e)))
        return False

class DemoTaskBarIcon(wx.TaskBarIcon):
    TBMENU_CLOSE   = wx.NewId()
    def __init__(self):
        wx.TaskBarIcon.__init__(self)
        self.SetIcon(CreateIcon('CS'), __description__)
        self.Bind(wx.EVT_TASKBAR_LEFT_DCLICK, self.OnTaskBarView)

    def CreatePopupMenu(self):
        menu = wx.Menu()
        item = menu.Append(-1, "View score")
        self.Bind(wx.EVT_MENU, self.OnTaskBarView, item)

        global enable_tts
        if enable_tts:
            item = menu.AppendCheckItem(-1, "Toggle voice")
            item.Check(enable_tts)
            self.Bind(wx.EVT_MENU, self.OnTaskBarToggleVoice, item)

        menu.AppendSeparator()
        item = menu.Append(-1, "Close me")
        self.Bind(wx.EVT_MENU, self.OnTaskBarClose, item)
        return menu

    def OnTaskBarToggleVoice(self, evt):
        global enable_tts
        enable_tts ^= 1

    def OnTaskBarView(self, evt):
        global misc, dlg_view, dlg_view_msg, current_player_count

        #~ msg_tts = "Counter strike server has %s, I repeat, %s players. Forget about assignment. Forget about essays. Forget about examination. Let's just play!!!!" % (current_player_count, current_player_count)
        #~ tts.Speak(msg_tts, pyTTS.tts_async)

        #~ print dlg_view
        if dlg_view:
            dlg_view.SetFocus()
            return
        dlg_view = wx.Dialog(None, -1, '%s %s %s' % (__description__, __version__, __author__))
        sizer = wx.BoxSizer(wx.VERTICAL)
        dlg_view.label = wx.StaticText(dlg_view, -1, dlg_view_msg)
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

def CreateIcon(txt,colour=None):
    bmp = wx.EmptyBitmap(16,16,24)
    dc = wx.MemoryDC()
    dc.SelectObject(bmp) # write number on it
    if colour != None:
        dc.SetBackground(wx.Brush(colour, wx.SOLID))
    else:
        dc.SetBackground(wx.Brush(wx.SystemSettings.GetColour(wx.SYS_COLOUR_3DFACE ), wx.SOLID))
    dc.Clear()
    dc.SetFont(wx.Font(10, wx.ROMAN, wx.NORMAL, wx.BOLD))
    #~ if colour != None:
        #~ dc.SetTextForeground(colour)
    txt_w,txt_h = dc.GetTextExtent(str(txt))
    dc.DrawText(str(txt), int((16-txt_w)/2), 0)
    dc.SelectObject(wx.NullBitmap)
    ic = wx.EmptyIcon()
    ic.CopyFromBitmap(bmp)
    return ic

def lock2key(lock):
    "Generates response to $Lock challenge from Direct Connect Servers"
    lock = [ord(c) for c in lock]
    key = [0]
    for n in range(1,len(lock)):
        key.append(lock[n]^lock[n-1])
    key[0] = lock[0] ^ lock[-1] ^ lock[-2] ^ 5
    for n in range(len(lock)):
        key[n] = ((key[n] << 4) | (key[n] >> 4)) & 255
    result = ""
    for c in key:
        if c in [0, 5, 36, 96, 124, 126]:
            result += "/%%DCN%.3i%%/" % c
        else:
            result += chr(c)
    return result

def pydcbot():
    HOST = 'fennetic.anu.edu.au'
    #~ HOST = 'localhost'
    PORT = 411
    s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    s.connect((HOST, PORT))
    while 1:
        data = s.recv(1024)
        print 'Received', data
        if not data: break
        process_data(s, data)
    s.close()

def process_data(sock, line):
    """state machine:
    0: tcp handshake, after receiving Lock challange, before sending Key
    1: after sending Key response, before ValidateNick
    2: after sending ValidateNick, before sending Version/GetNickList/MyInfo
    3: after sending Version/GetNickList/MyInfo, before receiving Hello
    4: after receiving Hello, login complete
    """
    global state, simple_msg, dlg_view_msg
    lines = line.split(' ')
    cmd = lines[0]
    if cmd == '$Lock':
        lock = lines[1]
        key = lock2key(lock)
        #~ data = '$Supports UserCommand NoGetINFO NoHello UserIP2 TTHSearch |$Key %s|$Validate %s|' % (key,nick)
        data = '$Key %s|' % key
        print 'Send', data
        sock.send(data)
        state = 1
    elif state == 1:
        data = '$ValidateNick %s|' % nick
        print 'Send', data
        sock.send(data)
        state = 2
    elif state == 2:
        data = '$Version 1,0091|$GetNickList|$MyINFO $ALL %s %s %s$ $%s%s$%s$%s$|' % (nick, description, tag, connection_type, chr(1), email, sharesize)
        print 'Send', data
        sock.send(data)
        state = 3
    elif state == 3:
        if cmd == '$Hello':
            state = 4
    else:
        if 'showmecs' in line:
            #~ data = '<%s> %s - %s|' % (nick, simple_msg, random.random())
            # findout the source
            m = re.search('<([^>]*)>',line)
            if m:
                data = '$To: %s From: %s $<%s> %s - %s \n%s|' % (m.group(1), nick, nick, simple_msg, random.random(), dlg_view_msg)
                print 'Send', data
                sock.send(data)
        elif line[0] == '<':
            msg_tts = line[line.index('>')+2:-1]
            # say it
            tts.Speak(msg_tts, pyTTS.tts_async)



def mythread():
    global reached, done, dlg_view, dlg_view_msg, prev_num, tts, enable_tts, random_asking, simple_msg
    st = 0
    while not done:
        ret = check(options.server_ip, options.server_port)
        if ret == False:
            dlg_view_msg = 'Unable to connect to %s:%s' % (options.server_ip, options.server_port)
            tbi.SetIcon(CreateIcon('X'), dlg_view_msg)
            if dlg_view:
                dlg_view.label.SetLabel(dlg_view_msg)
                dlg_view.Fit()
            time.sleep(10)
            continue

        t1,t2,mapname = ret


        if t1 >= min_players:
            if  t1 != prev_num:
                prev_num = t1
                ico = CreateIcon(t1,wx.RED)
                if enable_tts:
                    msg_tts = "%s players. %s" % (t1, random.choice(random_asking))
                    tts.Speak(msg_tts, pyTTS.tts_async)

            else:
                #~ ico = CreateIcon(t1,wx.Colour(200,80,80))
                ico = CreateIcon(t1,wx.WHITE)
        else:
            ico = CreateIcon(t1)
        simple_msg = '%s:%s - %s of %s (>%s) %s' % (options.server_ip, options.server_port, t1,t2,min_players,mapname)
        tbi.SetIcon(ico, simple_msg)

        dlg_view_msg = '%s (%s:%s)\nMap: %s\nTime: %s\n\n%s' % (server_name, server_addr, server_port, mapname, time.strftime('%H:%M:%S'), misc)
        try:
            if dlg_view:
                dlg_view.label.SetLabel(dlg_view_msg)
                dlg_view.Fit()
        except AttributeError:
            pass
        if t1 >= min_players and not reached:
            if enable_tts:
                msg_tts = "Counter strike server has %s, I repeat, %s players" % (t1, t1)
                tts.Speak(msg_tts, pyTTS.tts_async)
            dlg = wx.MessageDialog(None, 'CS Server has %s players, FORGET ESSAY, FORGET EXAM, LETS PLAY!!!' % t1,'%s %s %s' % (__description__, __version__, __author__), wx.OK | wx.ICON_INFORMATION)
            dlg.ShowModal()
            dlg.Destroy()
            reached = True
        elif t1 < min_players and reached:
            reached = False
        time.sleep(10)

if __name__ == '__main__':
    enable_tts = False
    try:
        import pyTTS
        enable_tts = True
    except ImportError:
        pass
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("-s", "--server_ip", dest="server_ip", help="CS Server address (def:150.203.239.103)", default='150.203.239.103')
    parser.add_option("-p", "--server_port", type="int", dest="server_port", help="CS Server port (def:27015)", default=27015)
    parser.add_option("-m", "--min_players", type="int", dest="min_players", help="Minimum player(s) to trigger alarm")
    if enable_tts:
        parser.add_option("-t", "--no_tts", action="store_false", dest="no_tts", help="Disable cool TTS/computer generated voice", default=True)
        enable_tts = options.no_tts

    options, args = parser.parse_args()
    app = wx.PySimpleApp()
    reached, done, dlg_view, min_players, prev_num, dlg_view_msg  = False, False, None, options.min_players, 0, ''

    if enable_tts:
        tts = pyTTS.Create()
        tts.Speak('Welcome to Counter Striker Player Alarm. Dedicated to Australian National University Canberra.', pyTTS.tts_async)

    if not min_players:
        dlg = wx.SingleChoiceDialog(None, 'CS Server: %s:%s\nNote: To change, do a cs_server.exe --help to see how\n\nPick the minimum number of players\nI\'ll remind you if it\'s been reached.' % (options.server_ip, options.server_port), '%s %s %s' % (__description__, __version__, __author__), [str(i) for i in range(1,31)],  wx.CHOICEDLG_STYLE )
        if dlg.ShowModal() != wx.ID_OK:
            sys.exit()
        min_players = int(dlg.GetStringSelection())
        dlg.Destroy()
    tbi = DemoTaskBarIcon()
    t = threading.Thread(target = mythread)
    t.start()
    #~ t2 = threading.Thread(target = pydcbot)
    #~ t2.start()

    app.MainLoop()