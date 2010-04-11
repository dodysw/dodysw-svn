#!/usr/bin/python -O
"""
wxSda - Starnet Data Accounting GUI client
Copyright 2005, Dody Suria Wijaya <dodysw@gmail.com>
"""
__version__ = '1.1.0'
__description__ = 'wxSda client'
__author__ = 'Dody Suria Wijaya'

import sys, threading, time, ConfigParser, os

# default ini file content if not found
default_ini = """
[account]
username=
password=
sda_ip=150.203.115.14
sda_port=8000
sda_name=Bruce Hall

[sda_servers]
Bruce Hall=150.203.115.14:8000
Fenner Hall=150.203.110.1:8000

[auto_logout]
by_used=1
used_value=10
by_duration=1
duration=60
"""

try: import pysda
except ImportError:
    sys.exit("This software requires pysda.py module. Please put that file in the same folder or in Python path")

try:
    import wx
    import wx.lib.newevent
except ImportError:
    sys.exit("""
This software requires wxPython 2.5.x or above.
Please visit http://www.wxpython.org/download and install the appropriate version for Windows, MacOS X, Redhat, Fedora, and Mandrake distribution.
Debian distrib, get the instruction on http://www.bitpim.org/developer.html
Ubuntu users, there's an instruction at http://wiki.wxpython.org/index.cgi/wxPython_20with_20Ubuntu

If you are unable to provide wxPython, try executing pysda.py for console/text based SDA.
""")

global sda, session

(UpdateTaskBarIcon, EVT_UPDATE_TASKBAR) = wx.lib.newevent.NewEvent()
(AlarmTrigger, EVT_ALARM_TRIGGER) = wx.lib.newevent.NewEvent()

class EmptyClass:
    pass

class DemoTaskBarIcon(wx.TaskBarIcon):
    TBMENU_CLOSE   = wx.NewId()
    def __init__(self):
        wx.TaskBarIcon.__init__(self)
        self.SetIcon(self.CreateIcon('SDA'), __description__)
        self.Bind(EVT_UPDATE_TASKBAR, self.OnUpdate)
        self.Bind(EVT_ALARM_TRIGGER, self.OnAlarmTrigger)

    def OnAlarmTrigger(self, evt):
        dlg = wx.MessageDialog(None, evt.msg, 'Note', style=wx.OK | wx.ICON_INFORMATION)
        dlg.ShowModal()
        dlg.Destroy()
        StartLoginLoop()    # goback to login loop
        StartUpdateThread()

    def OnUpdate(self, evt):
        self.SetIcon(tbi.CreateIcon(evt.icon_msg), evt.icon_pop_msg)

    def CreateIcon(self, txt, colour=None):
        icon_w = 40
        bmp = wx.EmptyBitmap(icon_w,20,24)
        dc = wx.MemoryDC()
        dc.SelectObject(bmp) # write number on it
        if colour != None:
            dc.SetBackground(wx.Brush(colour, wx.SOLID))
        else:
            dc.SetBackground(wx.Brush(wx.SystemSettings.GetColour(wx.SYS_COLOUR_3DFACE ), wx.SOLID))
        dc.Clear()
        dc.SetFont(wx.Font(8, wx.ROMAN, wx.NORMAL, wx.NORMAL))
        txt_w,txt_h = dc.GetTextExtent(str(txt))
        dc.DrawText(str(txt), int((icon_w-txt_w)/2), 0)
        dc.SelectObject(wx.NullBitmap)
        ic = wx.EmptyIcon()
        ic.CopyFromBitmap(bmp)
        return ic

    def CreatePopupMenu(self):
        menu = wx.Menu()
        global sda
        if globals().has_key('sda') and sda.state_logged_in:
            item = menu.Append(-1, "Log out")
            self.Bind(wx.EVT_MENU, self.OnTaskBarLogOut, item)
            menu.AppendSeparator()
        item = menu.Append(-1, "Close me")
        self.Bind(wx.EVT_MENU, self.OnTaskBarClose, item)
        return menu

    def OnTaskBarLogOut(self, evt):
        global done, sda
        self.SetIcon(self.CreateIcon('SDA'), __description__)
        if sda.state_logged_in: sda.logout()    # this provide instant logout, without having to wait thread to shutdown
        StopUpdateThread()  # stop update thread
        StartLoginLoop()    # goback to login loop
        StartUpdateThread()

    def OnTaskBarClose(self, evt):
        global done
        self.RemoveIcon()
        done = True
        sys.exit()

def mythread():
    global sda, tbi, thread_continue, session
    thread_continue = True
    if __debug__: print 'Im logged in'
    try:
        try:
            msg = 'Quota: %s' % sda.stat_quota
            evt = UpdateTaskBarIcon(icon_msg = str(0.0), icon_pop_msg = msg)
            wx.PostEvent(tbi, evt)
            while thread_continue:
                sda.update()
                msg = 'Quota: %s Used: %s' % (sda.stat_quota, sda.stat_used)
                evt = UpdateTaskBarIcon(icon_msg = str(sda.stat_used), icon_pop_msg = msg)
                wx.PostEvent(tbi, evt)
                # check used quota
                if session.by_used and sda.stat_used >= session.used_value:
                    sda.logout()
                    # post an event to taskbar icon to show certain dialog
                    evt = AlarmTrigger(type=1, msg='User used quota of %s MB has been reached. SDA has been auto logged out.' % session.used_value)
                    wx.PostEvent(tbi, evt)
                    return
                if session.by_duration and (time.time() - session.start_time) > session.duration*60:
                    sda.logout()
                    evt = AlarmTrigger(type=2, msg='Time limit %s minutes has been reached. SDA has been auto logged out.' % session.duration)
                    wx.PostEvent(tbi, evt)
                    return

                if __debug__: print 'Sleeping'
                time.sleep(pysda.SDA_UPDATE_TIME)
        except Exception, e:
            if __debug__: print 'Exception', str(e)
            raise
    finally:
        if __debug__: print 'Ouch, must exit'
        if sda.state_logged_in:
            sda.logout()
        sys.exit()

class Login(wx.Dialog):
    def __init__(self, parent):
        wx.Dialog.__init__(self, parent, -1, '%s %s %s' % (__description__, __version__, __author__))

        main_sizer = wx.BoxSizer(wx.VERTICAL)

        sizer = wx.FlexGridSizer(-1,2)

        obj = wx.StaticText(self, -1, 'Username:')
        sizer.Add(obj, 0, wx.ALL, 5)
        self.tcUser = obj = wx.TextCtrl(self, -1)
        sizer.Add(obj, 0, wx.ALL, 5)

        obj = wx.StaticText(self, -1, 'Password:')
        sizer.Add(obj, 0, wx.ALL, 5)
        self.tcPass = obj = wx.TextCtrl(self, -1, style=wx.TE_PASSWORD )
        sizer.Add(obj, 0, wx.ALL, 5)

        main_sizer.Add(sizer)

        self.cbSaveUserPass = obj = wx.CheckBox(self, -1, label="Save password")
        main_sizer.Add(obj, flag=wx.LEFT, border=10)

        sb = wx.StaticBox(self, label='Auto logout options')
        sizer = wx.StaticBoxSizer(sb, wx.VERTICAL)

        obj = wx.StaticText(self, -1, 'Tick and specify the type and \nvalue of your session auto-logout')
        sizer.Add(obj)

        rowsizer = wx.BoxSizer(wx.HORIZONTAL)
        self.cbUsed = obj = wx.CheckBox(self, -1)
        rowsizer.Add(obj, 0, wx.ALIGN_CENTRE|wx.ALL, 5)
        obj = wx.StaticText(self, -1, 'Used quota:')
        rowsizer.Add(obj, 0, wx.ALIGN_CENTRE|wx.ALL, 5)
        self.tcUsed = obj = wx.TextCtrl(self, -1, size=(60,-1))
        rowsizer.Add(obj, 0, wx.ALIGN_CENTRE|wx.ALL, 5)
        obj = wx.StaticText(self, -1, 'MB')
        rowsizer.Add(obj, 0, wx.ALIGN_CENTRE|wx.ALL, 5)
        sizer.Add(rowsizer)

        rowsizer = wx.BoxSizer(wx.HORIZONTAL)
        self.cbTime = obj = wx.CheckBox(self, -1)
        rowsizer.Add(obj, 0, wx.ALIGN_CENTRE|wx.ALL, 5)
        obj = wx.StaticText(self, -1, 'Duration:')
        rowsizer.Add(obj, 0, wx.ALIGN_CENTRE|wx.ALL, 5)
        self.tcTime = obj = wx.TextCtrl(self, -1, size=(60,-1))
        rowsizer.Add(obj, 0, wx.ALIGN_CENTRE|wx.ALL, 5)
        obj = wx.StaticText(self, -1, 'Minutes')
        rowsizer.Add(obj, 0, wx.ALIGN_CENTRE|wx.ALL, 5)

        sizer.Add(rowsizer)

        main_sizer.Add(sizer, flag=wx.ALL, border=10)

        sb = wx.StaticBox(self, label='SDA Server')
        sizer = wx.StaticBoxSizer(sb, wx.VERTICAL)

        rowsizer = wx.BoxSizer(wx.HORIZONTAL)
        obj = wx.StaticText(self, -1, 'Hall/College:')
        rowsizer.Add(obj, 0, wx.ALL, 5)
        self.sda_servers = scp.items('sda_servers')
        choices = [server[0] for server in self.sda_servers]
        self.tcSdaPick = obj = wx.ComboBox(self, -1, size=(100,-1), choices=choices)
        rowsizer.Add(obj, 0, wx.ALL, 5)
        sizer.Add(rowsizer)

        rowsizer = wx.BoxSizer(wx.HORIZONTAL)
        obj = wx.StaticText(self, -1, 'IP:Port')
        rowsizer.Add(obj, 0, wx.ALL, 5)
        self.tcSdaHost = obj = wx.TextCtrl(self, -1,size=(150,-1))
        rowsizer.Add(obj, 0, wx.ALL, 5)
        sizer.Add(rowsizer)

        main_sizer.Add(sizer, flag=wx.ALL, border=10)
        sizer = wx.BoxSizer(wx.HORIZONTAL)

        btn_ok = wx.Button(self, wx.ID_OK)
        btn_ok.SetDefault()
        sizer.Add(btn_ok, 0, wx.ALIGN_CENTRE|wx.ALL, 5)
        sizer.Add(wx.Button(self, wx.ID_CANCEL), 0, wx.ALIGN_CENTRE|wx.ALL, 5)

        main_sizer.Add(sizer)

        self.SetSizer(main_sizer)
        main_sizer.Fit(self)

        # set default value from config file
        self.tcUser.SetValue(pysda.decode(scp.get('account','username')))
        self.tcPass.SetValue(pysda.decode(scp.get('account','password')))

        if scp.get('account','username') != '':
            self.cbSaveUserPass.SetValue(True)

        if scp.get('account','sda_ip') != '':
            self.tcSdaHost.SetValue('%s:%s' % (scp.get('account','sda_ip'),scp.get('account','sda_port')))
            self.tcSdaPick.SetValue(scp.get('account','sda_name'))
        self.cbUsed.SetValue(int(scp.get('auto_logout','by_used')))
        self.cbTime.SetValue(int(scp.get('auto_logout','by_duration')))
        self.tcUsed.SetValue(scp.get('auto_logout','used_value'))
        self.tcTime.SetValue(scp.get('auto_logout','duration'))

        if options.username and options.password:
            self.tcUser.SetValue(options.username)
            self.tcPass.SetValue(options.password)

        self.Bind(wx.EVT_BUTTON, self.OnOk, id=wx.ID_OK)
        self.Bind(wx.EVT_UPDATE_UI, lambda evt: evt.Enable(self.cbTime.GetValue()), self.tcTime)
        self.Bind(wx.EVT_UPDATE_UI, lambda evt: evt.Enable(self.cbUsed.GetValue()), self.tcUsed)

        def xyz(evt):
            s = evt.GetString()
            if s == '': return
            self.tcSdaHost.SetValue([server[1] for server in self.sda_servers if server[0] == s][0])
        self.Bind(wx.EVT_TEXT, xyz, self.tcSdaPick) # icant use EVT_COMBOBOX since on GTK, picking the first option does not trigger this event

    def OnOk(self, evt):
        # makeuser user/pass not empty
        if self.tcUser.GetValue() == '' or self.tcPass.GetValue() == '':
            dlg = wx.MessageDialog(self, 'Username or password may not empty', 'Error', style=wx.OK | wx.ICON_ERROR)
            dlg.ShowModal()
            return
        elif self.tcSdaHost.GetValue() == '' or len(self.tcSdaHost.GetValue().split(':')) != 2:
            dlg = wx.MessageDialog(self, 'Invalid SDA server', 'Error', style=wx.OK | wx.ICON_ERROR)
            dlg.ShowModal()
            return

        if self.cbSaveUserPass.GetValue():  # save user/password
            # put it in config
            scp.set('account','username', pysda.encode(self.tcUser.GetValue()))
            scp.set('account','password', pysda.encode(self.tcPass.GetValue()))

        # save auto_logout and sda server setting
        sda_ip, sda_port = self.tcSdaHost.GetValue().split(':')
        scp.set('account','sda_ip', sda_ip)
        scp.set('account','sda_port', sda_port)
        scp.set('account','sda_name', self.tcSdaPick.GetValue())

        scp.set('auto_logout','by_used', str(int(self.cbUsed.GetValue())))
        scp.set('auto_logout','used_value', str(self.tcUsed.GetValue()))
        scp.set('auto_logout','by_duration', str(int(self.cbTime.GetValue())))
        scp.set('auto_logout','duration', str(self.tcTime.GetValue()))

        global session
        session = EmptyClass()
        session.by_used = self.cbUsed.GetValue()
        session.by_duration = self.cbTime.GetValue()
        session.used_value = float(self.tcUsed.GetValue())
        session.duration = float(self.tcTime.GetValue())
        session.start_time = time.time()

        SaveConfigFile()
        evt.Skip()

def StartUpdateThread():
    # start update background thread
    update_thread = threading.Thread(target = mythread)
    update_thread.setDaemon(True)   # this to avoid "Python process closing" waiting for thread to close beforehand
    update_thread.start()

def StopUpdateThread():
    global thread_continue
    thread_continue = False

def StartLoginLoop():
    global sda, options
    dlg_login = None
    logged_in = False
    while not logged_in: # ask user question loop
        if not dlg_login:
            dlg_login = Login(None)
        if dlg_login.ShowModal() != wx.ID_OK:
            dlg_login.Destroy()
            sys.exit()
        options.username, options.password = dlg_login.tcUser.GetValue(), dlg_login.tcPass.GetValue()

        if options.username or options.password:
            # todo: use session var
            sda = pysda.SdaClient(username=options.username, password=options.password, client_host=options.client_host, sda_host=options.sda_host, sda_port=options.sda_port)
            if sda.login():
                logged_in = True
                dlg_login.Destroy()
            else:
                dlg = wx.MessageDialog(dlg_login, 'Invalid login. Please try again', 'Login fail', style=wx.OK | wx.ICON_ERROR)
                dlg.ShowModal()
                dlg.Destroy()

def SaveConfigFile():
    global scp, scp_config_path
    scp.write(file(scp_config_path,'w'))

def PrepareConfigFile():
    global scp, scp_config_path
    # prepare the configuration file
    # find out the best location to store config file
    if sys.platform == 'win32':
        scp_config_path = os.getenv('HOMEPATH','.')
    else:
        scp_config_path = os.getenv('HOME','.')
    scp_config_path = os.path.join(scp_config_path,'pysda.ini')
    if __debug__: print 'Preparing config file at', scp_config_path
    if not os.path.exists(scp_config_path): # create it if not exists
        file(scp_config_path,'w').write(default_ini)
    scp = ConfigParser.SafeConfigParser()
    scp.readfp(file(scp_config_path))
    # make sure all sections are available
    sections = ['account', 'sda_servers', 'auto_logout']
    for section in sections:
        if not scp.has_section(section):
            scp.add_section(section)

if __name__ == '__main__':
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option('-u', "--user", dest="username", help="Your SDA username")
    parser.add_option('-p', "--pass", dest="password", help="Your SDA password")
    parser.add_option('-H', '--server_host', dest="sda_host", help="Your SDA server (default:%s)" % pysda.SDA_HOST, default=pysda.SDA_HOST)
    parser.add_option('-P', "--server_port", type="int", dest="sda_port", help="Your SDA port (default:%s)" % pysda.SDA_PORT, default=pysda.SDA_PORT)
    parser.add_option('-i', '--client_host', dest="client_host", help="The client IP Address to link to this account (default: your own IP)", default=None)
    options, args = parser.parse_args()

    PrepareConfigFile()

    app = wx.PySimpleApp()
    tbi = DemoTaskBarIcon()

    # if user/pass is presented via parameter, then try to login
    logged_in = False
    if options.username and options.password:
        if __debug__: print 'Validating user/pass from option (%s/%s)' % (options.username, options.password)
        sda = pysda.SdaClient(username=options.username, password=options.password, client_host=options.client_host, sda_host=options.sda_host, sda_port=options.sda_port)
        if sda.login():
            logged_in = True    # pass, go directly to update thread loop and mainloop
    if not logged_in:
        StartLoginLoop()    # start login loop
    StartUpdateThread()
    app.MainLoop()


"""
Fenner Hall = 150.203.110.1:8000
Bruce Hall  = 150.203.115.14:8000
Toad Hall   =
John XXIII College = doesnt use SDA
B&G Hall =
Ursula Hall =
Burgmann College =
Graduate House =
"""