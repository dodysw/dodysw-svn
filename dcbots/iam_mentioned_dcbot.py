#!/usr/bin/python
"""
Generic DC Bot
Copyright 2006, Dody Suria Wijaya <dodysw@gmail.com>
"""
import re, time, random, threading, dclib, urllib2
__version__ = '1.0.0'
__description__ = ''

DEFAULT_DC_HOST = 'gen2box.ath.cx'
DEFAULT_DC_PORT = 411
DEFAULT_NICK = '_iam_mentioned_bot__'
DEFAULT_NICK_DESCRIPTION = 'This bot PM users when their nick is being mentioned in public chat'

class IamMentionedBot(dclib.DCActionBot):
    """
    A bot which observe public chat for a user's nick being mentioned, then PM the nick of the line.
    """

    HELP_HEADER = """
**********************************
IAM MENTIONED BOT
**********************************
Commands:
"""


    def init(self):
        self.share_size = 250*1024*1024
        self.nicklist = {}
        self.nicklist_optin = dclib.SimplePickledObject('iam_mentioned')

    def handle_public_chat(self, nick, data):
        super(IamMentionedBot, self).handle_public_chat(nick, data)
        for n in self.nicklist_optin.get_data():
            if ')' in n or '(' in n:
                pat = re.escape(n)
            else:
                pat = "\\b%s\\b" % re.escape(n)
            #~ print 'Pattern', pat
            m = re.search(pat, data, re.I)
            if m:
                print "PM to %s this line:%s" % (nick, data)
                self.send_pm(n, "<%s> %s" % (nick, data))
                break

    def handle_nicklist(self, nicks):
        super(IamMentionedBot, self).handle_nicklist(nicks)
        self.nicklist = dict.fromkeys(nicks.split('$$')[:-1])   # [:-1] required as a trailing $$ exist in nicks

    def handle_quit(self, nick):
        super(IamMentionedBot, self).handle_quit(nick)
        try:
            del self.nicklist[nick]
        except KeyError:
            pass

    def handle_hello(self, nick):
        super(IamMentionedBot, self).handle_hello(nick)
        self.nicklist[nick] = None

    def on_loggedin(self):
        self.send_getnicklist()

    def action_optin(self, nick, data=''):
        """get mentioned"""
        self.nicklist_optin.set(nick, None)
        self.send_pm(nick, 'You will be PM-ed when your nick is mentioned.')

    def action_optout(self, nick, data=''):
        """dont mention me"""
        self.nicklist_optin.remove(nick)
        self.send_pm(nick, 'Thanks for using this service. Bye.')

    def action_list(self, nick, data=''):
        """list current opted-in users"""
        if not self.nicklist_optin.get_data():
            self.send_pm(nick, '\nEmpty')
        else:
            self.send_pm(nick, '\n' + '\n'.join([n for n in self.nicklist_optin.get_data()]))

BOT_CLASS = IamMentionedBot

if __name__ == '__main__':
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("--dc_ip", dest="dc_server_ip", help="DC++ Server address (def:%s)" % DEFAULT_DC_HOST, default=DEFAULT_DC_HOST)
    parser.add_option("--dc_port", type="int", dest="dc_server_port", help="CS Server port (def:%s)" % DEFAULT_DC_PORT, default=DEFAULT_DC_PORT)
    parser.add_option("--nick", dest="nick", help="DC++ nick name (def:%s)" % DEFAULT_NICK, default=DEFAULT_NICK)
    options, args = parser.parse_args()

    bot = BOT_CLASS(address=(options.dc_server_ip, options.dc_server_port), nick=options.nick, description=DEFAULT_NICK_DESCRIPTION)
    bot.run()