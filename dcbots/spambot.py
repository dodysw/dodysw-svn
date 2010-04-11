#!/usr/bin/python
"""
Generic DC Bot
Copyright 2005, Dody Suria Wijaya <dodysw@gmail.com>
"""
import md5, time, dclib
__version__ = '0.674'
__description__ = '++'

class SpamBot(dclib.DCBot):

    def handle_public_chat(self, nick, data):
        if 'showmeweather' in data:
            #~ self.send_public_chat('Current temperature in Canberra = %s degree Celcius' % self.__get_temperature())
            self.send_pm(nick, 'Current temperature in Canberra = %s degree Celcius' % self.__get_temperature())

    def handle_to(self, nick, data):
        #~ if 'weather' in data:
        self.send_pm(nick, 'Current temperature in Canberra = %s degree Celcius' % self.__get_temperature())


if __name__ == '__main__':
    import optparse, sys
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("--dc_ip", dest="dc_server_ip", help="DC++ Server address (def:gen2box.ath.cx)", default='gen2box.ath.cx')
    parser.add_option("--dc_port", type="int", dest="dc_server_port", help="DC Server port (def:411)", default=411)
    options, args = parser.parse_args()

    #~ nick = md5.new(str(time.time())).hexdigest()
    nick = 'meongnakal'
    bot = SpamBot(address=(options.dc_server_ip, options.dc_server_port), nick=nick)
    bot.share_size = 500*1024*102
    bot.version = '0.687'
    bot.run()