
from dclib import DCBot, DCClientClient

class DCActionBot(DCBot):
    """Bot with simple action binding on PM
    """

    HELP_HEADER = """
**********************************
GENERIC DC BOT
**********************************
Commands:
"""

    HELP_FOOTER = """**********************************"""

    def handle_to(self, nick, data):
        if data.startswith('+'):
            if ' ' in data:
                pos = data.index(' ')
                cmd = data[1:pos]
                param = data[pos+1:]
            else:
                cmd = data[1:]
                param = ''
            self.log('nick [%s] running action [%s] with data [%s]' % (nick, cmd, param))
            if hasattr(self, 'action_'+cmd):
                getattr(self, 'action_'+cmd)(nick, param)
            else:
                self.action_unknowncommand(nick, param)
        else:
            self.action_unknowncommand(nick,'')

    def action_unknowncommand(self, nick, data=''):
        """when an unknown command is received"""
        self.send_pm(nick, 'to get list of command, type   +help')

    def action_help(self, nick, data=''):
        """shows this help"""
        # dynamically generate help from __doc__
        actions = [attr for attr in dir(self) if attr.startswith('action_')]
        actions.sort()
        helpout = []
        helpout.append(self.HELP_HEADER)
        for action in actions:
            doc = getattr(self, action).__doc__
            if doc:
                helpout.append('+%s = %s' % (action[7:], doc))
            else:
                helpout.append('+%s' % action[7:])
        helpout.append(self.HELP_FOOTER)
        self.send_pm(nick, '\n'.join(helpout))

class DCClientGetList(DCClientClient):
    """Worker DC client to client that downloads file list and exit
    """
    def on_loggedin(self):
        """Called when login successful"""
        if not self.mode_upload:
            self.download_list()
            #~ self.download_tthl("TTH/NHD52JMJGMNISZCSPV3Y52TNRGNTSLFQ4TEHFHI")
    def on_finished_download(self, e):
        # after finishes downloading (happens just after sending $Send message), close us
        self.log("Ending thread as data has been received")
        self.running = False
