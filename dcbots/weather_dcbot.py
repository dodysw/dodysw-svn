#!/usr/bin/python
"""
Generic DC Bot
Copyright 2006, Dody Suria Wijaya <dodysw@gmail.com>
"""
import re, time, random, threading, dclib, urllib2
__version__ = '1.0.0'
__description__ = ''

HUBS_ADDRESS = [
    ('thegameplace.ath.cx', 411),
    #~ ('gen2box.ath.cx', 411),
    ('darkmatrix.ath.cx', 411),
]

HUBS_MINIMUM_SHARE = (
    0,
    250*1024*1024,
    0
)

last_sr_nick = None

class Subject:
    _observers = {}
    def attach(self, observer):
        self._observers[observer] = 1
    def detach(self, observer):
        if observer in self._observers:
            del self._observers[observer]
    def notify(self, modifier=None):
        for observer in self._observers:
            if modifier != observer:
                observer.update(self)

class TheWeatherGuy(Subject):
    running = True

    def get_canberra_temp2(self):
        re_parser = re.compile('<tbody>\n<tr>\n\s*?<td>.*?</td>\n\s*?<td>\s*([^\<]+)</td>',re.S)
        url = 'http://www.bom.gov.au/products/IDN65092/IDN65092.94926.shtml'
        try:
            m = re_parser.search(urllib2.urlopen(url).read())
        except urllib2.URLError:
            return None

        if m:
            temp = m.group(1)
            temp = float(temp)
            if temp > -100 and temp < 100:
                return temp, 'n/a'
        return None

    def get_canberra_temp(self):
        re_parser = re.compile('<!-- DATA, (\d\d\d\d)(\d\d)(\d\d):(\d\d)(\d\d)(\d\d), Canberra Airport, ([^,]+),[^,]+,[^,]+,[^,]+,([^,]+),([^,]+)',re.S)
        url = 'http://www.bom.gov.au/products/IDN65066.shtml'
        try:
            m = re_parser.search(urllib2.urlopen(url).read())
        except urllib2.URLError:
            return None
        if m:
            year, month, day, hour, minute, second, temp, wind, wind_gust = m.groups()
            temp, wind, wind_gust = float(temp), int(wind), int(wind_gust)
            if temp > -100 and temp < 100:
                return temp, wind, wind_gust, '%s:%s' % (hour, minute)
        return None

    def check_weather(self):
        self.wind = self.wind_gust = None
        res = self.get_canberra_temp()
        if not res is None:
            self.temp, self.wind, self.wind_gust, self.update_time = res
        else:
            res = self.get_canberra_temp2()
            if not res is None:
                self.temp, self.update_time = res
            else:
                print "Unable to retrieve weather data."
                return
        self.notify()

    def run(self):
        time.sleep(3)
        while self.running:
            self.check_weather()
            time.sleep(60)

class WeatherBot(dclib.DCBot):
    last_temp = minimum_share = None
    _mult = 1024*1024   # we set this to at least 1 MB in order to be able to display decimal value

    def init(self):
        #create background thread to update temp every 10 minutes
        self.email = 'pisangrebus@gmail.com'

    def set_minimum_share(self, n):
        self.share_size = self.minimum_share = n
        self._mult = max(1024*1024, int(1024 ** math.ceil(math.log(self.minimum_share, 1024)) ))# we set this to at least 1 KB in order to be able to display decimal value

    def update(self, weather_guy):
        #~ self.log("I'm updated!")
        if self.is_loggedin:
            # we want temperature in KB so can be displayed with decimal.
            self.last_temp = weather_guy.temp
            if self.minimum_share is not None and (weather_guy.temp * self._mult) < self.minimum_share:
                ss = int(99 * 1024 * self._mult)
            else:
                ss = int(weather_guy.temp * self._mult)

            assert ss >= self.minimum_share

            update_myinfo = False

            if self.share_size != ss:
                # update share size in DC
                self.share_size = ss
                update_myinfo = True

            if not weather_guy.wind is None and not weather_guy.wind_gust is None:
                description = "Canberra temp: %s C wind: %s-%s kph (updated %s)" % (weather_guy.temp, weather_guy.wind, weather_guy.wind_gust, weather_guy.update_time)
            else:
                description = "Canberra temp: %s C (updated %s)" % (weather_guy.temp, weather_guy.update_time)

            if self.description != description:
                self.description = description
                update_myinfo = True

            if update_myinfo:
                self.send(self.get_myinfo())
                #~ self.send(self.get_myinfo())    # send twice for naughty hub

    def handle_search(self, data):
        self.log("Handle Search: [%s]" % data)
        if self.last_temp != None:
            try:
                s = self.parse_search(data)
            except dclib.ParseException:
                self.log("Warning: invalid search query:[%s]. Ignoring..." % data)
                return

            # don't sent if last query to avoid duplicate SR
            global last_sr_nick
            if last_sr_nick:
                last_nick, age = last_sr_nick[0], time.time() - last_sr_nick[1]
                if last_nick == s.nick and age < 10:
                    return

            path = 'result\\%s\\%s.txt' % (s.search_pattern, self.description)
            user_is_passive = (s.nick != '' and s.address == '')
            if user_is_passive:
                self.send_search_response(path, to_nick=s.nick)
            else:
                self.send_search_response(path, to_address=s.address)

            # save last query to avoid duplicate SR
            last_sr_nick = s.nick, time.time()

if __name__ == '__main__':
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("--nick", dest="nick", help="DC++ nick name (def:%s)" % 'WeatherBot', default='_weather_bot_')
    options, args = parser.parse_args()

    w = TheWeatherGuy()
    for i, addr in enumerate(HUBS_ADDRESS):
        hub = WeatherBot(address=addr, nick=options.nick, description='DC++')
        hub.minimum_share = HUBS_MINIMUM_SHARE[i]
        w.attach(hub)
        threading.Thread(target=hub.run, name="Hub @ %s:%s" % (addr[0], addr[1])).start()

    threading.Thread(target=w.run, name="Weather Guy").start()

    # Python will wait until all threads complete.