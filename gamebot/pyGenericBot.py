#!/usr/bin/python
"""
Generic DC Bot
Copyright 2005, Dody Suria Wijaya <dodysw@gmail.com>
"""
import dclib
__version__ = '1.0.0'
__description__ = 'Generic DC Bot'

class WeatherBot(dclib.DCBot):

    def handle_public_chat(self, nick, data):
        if 'showmeweather' in data:
            #~ self.send_public_chat('Current temperature in Canberra = %s degree Celcius' % self.__get_temperature())
            self.send_pm(nick, 'Current temperature in Canberra = %s degree Celcius' % self.__get_temperature())

    def handle_to(self, nick, data):
        #~ if 'weather' in data:
        self.send_pm(nick, 'Current temperature in Canberra = %s degree Celcius' % self.__get_temperature())

    def __get_temperature(self):
        import urllib2, re
        buffer = urllib2.urlopen('http://dragonlair.anu.edu.au').read()
        m = re.search('Current Temperature is (\d+) deg',buffer)
        if not m:
            return 'N/A'
        else:
            try:
                self.share_size = int(m.group(1))
                self.send('$MyINFO $ALL %s %s %s$ $%s%s$%s$%s$' % (self.nick, self.description, self.tag, self.connection_type, chr(1), self.email, self.share_size))
            except ValueError:
                pass
            return m.group(1)

if __name__ == '__main__':
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("--dc_ip", dest="dc_server_ip", help="DC++ Server address (def:150.203.121.98)", default='150.203.121.98')
    parser.add_option("--dc_port", type="int", dest="dc_server_port", help="CS Server port (def:411)", default=411)
    parser.add_option("--nick", dest="nick", help="DC++ nick name (def:%s)" % 'g_bot', default='g_bot')
    parser.add_option("--pm_only", action="store_true", dest="pm_only", help="Only reply query via personal message", default=False)
    options, args = parser.parse_args()

    bot = WeatherBot(address=(options.dc_server_ip, options.dc_server_port), nick=options.nick)
    bot.run()