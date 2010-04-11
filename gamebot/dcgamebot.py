#!/usr/bin/python
"""
DC Game Bot
Copyright 2005, Mark Mckay <etheral.point@gmail.com>
to use this software, you must install Python 2.3.x or above
to compile into independent .exe, you must install py2exe
visit:
    - http://www.python.org
    - http://starship.python.net/crew/theller/py2exe/

modified by Mark Mckay Etheral.point@gmail.com
based on Dody Suria Wijaya's <dodysw@gmail.com> cs bot

17oct05 dodysw: added cs:source and dod support
20/4/06 Mark: seperated cs stuff out into seperate library and made 1.6 compatible again
23/4/06 Mark: minor changes, added in admin controls.
*/6/2006 Mark: major changes to structure for better error detection, easier modification, etc

"""

import socket, time, sys, threading, struct, random, re, cPickle
import war3lib, cslib, dclib
__version__ = '1.4.0'
__description__ = 'DC game Bot'
__author__ = 'Mark Mckay <etheral.point@gmail.com>'
__email__ = 'etheral.point@gmail.com'

# -- pybot config
nick = 'game_bot_test'
description = 'Game tracking bot'
connection_type = 'LAN(T3)'
email = ''
sharesize = 0
admin = 'SUZ' # -- admins nick
contactAdmin = 0 # -- does the bot contact admin on player commands?
games = {}
players = {}
#~ server_list = [('thegameplace.ath.cx',411,'game_pass_bot'),('darkmatrix.ath.cx',411,''),('gen2box.ath.cx',411,"")]#(ip,port,pass)
server_list = [('thegameplace.ath.cx',411,'game_pass_bot')]#(ip,port,pass)
threads=[]
showme, reg, dereg = gen_triggers =['showmegames','regme','deregme']
show_triggers = war3lib.show_triggers + cslib.show_triggers + gen_triggers
triggers = war3lib.triggers + cslib.triggers + ['game']


A_dump, A_reload, A_DIE, A_DO = admin_word = ['adminDump','adminReload','adminDie','adminDo']

regip='[12]?[0-9]?[0-9](\.[12]?[0-9]?[0-9]){3}'


## will this even work?? also not complete but anyway
## looks like it doesn't work. (only starts one connection) not sure what to do now.
## attempting putting in thread
#def start():
#    count=1
#    for server in server_list:
#        ip,port,pas=server
#        servers.append(dclib.dc(main,ip,port,pas,sharesize,count))
#        count+=1


## checks if an ip is valid
def valid_ip(ip):
    if ip == '0.0.0.0': return False
    if ip == '127.0.0.1': return False
    if ip == '255.255.255.255': return False
    if ip == '1.1.1.1': return False
    nums=ip.split('.')
    if int(nums[0]) >255: return False
    if int(nums[1]) >255: return False
    if int(nums[2]) >255: return False
    if int(nums[3]) >255: return False
    if int(nums[1]) == 255 and int(nums[2]) == 255 and int(nums[3]) == 255: return False

    return True

## messages players
def message_players(obj):
    count = 0
    if __debug__: print 'messaging players ',len(players) ,time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
    msg = '\n new %(type)s server :: %(date)s \n %(line)s' % obj
    # PM listed players when a server is added
    # count is there to prevent the bot being kicked.
    # check if the player is online (saves messages).
    #check if they are interested in that type of game.
    for player in players:
        if player.connected:
            if obj.type in player.type or player.type == 'all':
                data = '$To: %s From: %s $<%s> %s' % (player.dc_nick, nick, nick, msg)
                if count >= 5:
                    time.sleep(.01)
                    if __debug__: print 'Sleeping', count, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
                    count = 0
                if __debug__: print 'messaging', count , data, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
                servers[player.ID].send(data)
                count += 1

## cs/source/hl checker
def DOCS(ID,from_nick):
    tharbegames=0
    for game in games[:]:
        if game.type=='cs' or game.type=='dod' or game.type=='source':
            ret = cslib.check(game.ip,27015)
            if ret == False:
                simple_msg = 'Unable to connect to server at %s:%s' % (game.ip, 27015)
                data = '$To: %s From: %s $<%s> %s - %s' % (from_nick, nick, nick, simple_msg, random.random())
                tharbegames=1
                del games[game.ip]
            else:
                t1,t2,mapname,server_name,server_addr,server_port,misc = ret
                dlg_view_msg = '\n %s server \n%s of %s people. Map: %s. Server: %s (%s:%s)\n%s' % (game.type.upper(),t1,t2,mapname, server_name, server_addr, server_port, misc)
                data = '$To: %s From: %s $<%s> %s' % (from_nick, nick, nick, dlg_view_msg)
                data = data.replace('|', '&#124;')
                tharbegames=1
            threads[ID].send(data + '|')
    if not tharbegames:
        msg = ':( sorry no games'
        data = '$To: %s From: %s $<%s> %s' % (from_nick, nick, nick, msg)
        threads[ID].send(data + '|')


## war3 check and stuff
def DODOTA(cmd,comm,ID,from_nick):
    tharbegames=0
    for game in games[:]:
        if game.type=='dota' or game.type=='war3' or game.type=='bships':
            rp = dotalib.DotaLanSession(game)
            if not rp.UpdateServerInfo():
                output = "DOTA Server at %s -- Not responding, or already started. Will be removed from list." % ip
                delete_list.append(game)
                tharbegames = 1
            else:
                if rp.Join("dota_bot"):
                    player_str = '\n'.join([ "  %s. %s" % (i+1,name) for i,name in enumerate(rp.player_list)])
                    output = """

DOTA Server at %s -- Game Name: %s
Current Capacity: %s of %s Map: [%s]
Players:
%s

""" % (ip, rp.name, rp.player_count, rp.max_player, rp.map_name, player_str)
                else:
                    output = """

DOTA Server at %s -- Game Name: %s
Current Capacity: %s of %s (FULL PACKED)
Players: -- sorry can't get the list since it's already full

""" % (ip, rp.name, rp.player_count, rp.max_player)

            lines.append(output)
        msg = ('='*20 + "\n").join(lines)
        data = '$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg)
        threads[ID].send(data + '|')

        for game in delete_list:
            del games[game]
        cPickle.dump(games, file("games","w"))


    if not tharbegames:
        msg = ':( sorry no games'
        data = '$To: %s From: %s $<%s> %s' % (from_nick, nick, nick, msg)
        threads[ID].send(data + '|')


## the big motha fugger that controls all the commands and shite. realllly need to work in simplifying this. maybe.
## watcha know I did somehow :S. turned it into a class to make linking with the dclib easier.
class main:
    def trigger(self,cmd,comm,ID):
        if cmd == '$Hello':
            from_nick = comm.split()[1]
            if from_nick in players:players[from_nick].connected=1
        if cmd == '$Quit':
            from_nick = comm.split()[1]
            if from_nick in players:players[from_nick].connected=0
        if cmd == '$To:':
            m = re.search('<([^>]*)>',comm)
            if not m: return
            from_nick = m.group(1)
            for trigger in show_triggers:
                if trigger in comm.lower():
                    if __debug__: print 'show trigger', from_nick, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
                    if trigger in cslib.show_triggers:
                        msg = 'checking games please wait.'
                        threads[ID].send('$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg))
                        if games:
                            if __debug__: print 'checking cs', from_nick, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
                            DOCS(ID,from_nick)
                        else:
                            msg = ':( sorry no games are being tracked.'
                            threads[ID].send('$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg))
                        ## do it
                    if trigger in war3lib.show_triggers:
                        msg = 'checking games please wait.'
                        threads[ID].send('$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg))
                        if games:
                            if __debug__: print 'checking DOTA', from_nick, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
                            DODOTA(ID,from_nick)
                        else:
                            msg = ':( sorry no games are being tracked.'
                            threads[ID].send('$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg))
                        ## do it
                    if showme in comm.lower():
                        msg = 'checking games please wait.'
                        threads[ID].send('$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg))
                        if games:
                            showall(ID,from_nick)
                        else:
                            msg = ':( sorry no games are being tracked.'
                            threads[ID].send('$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg))
                        ## do it
                    if reg in comm.lower():
                        reg(comm,ID,from_nick)
                    if dereg in comm.lower():
                        dereg(comm,ID,from_nick)
                elif from_nick == admin:
                    admin(comm,ID)
                else:
                    hlp = """
Available commands (without quotes):
    "showmecs"                             = view info on counter strike games
    "showmedod"                          = view info on day of defeat games.
    "showmesource"                     = view info on source games games.
    the above three basically do the same thing.

    "showmewar3"                         = view available warcraft 3 based games.
    "showmedota"                         = view available dota servers.
    these two are basically the same.

    "regme"                            = turn on notification about games (bot will explain more)
    "deregme"                              = turn of notification (can be selective like reg me)
    "showmegames"                      = Show all known game status'

pm SUZ with complaints and suggestions.
"""
                    data = '$To: %s From: %s $<%s> %s' % (from_nick, nick, nick, hlp)
                    print ID, len(threads)
                    threads[ID].send(data)

        else:
            for trigger in triggers:
                if trigger in comm.lower():reggame(comm,ID)

## register games and things
def reggame(comm,ID):
    mip = re.search(regip,comm)
    if not mip: return
    m = re.search('<([^>]*)>',comm)
    if (not m) or (m.group(1) == '-MOTD-'): return
    if __debug__: print 'adding game ip, public', m.group(1), mip.group(), time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
    from_nick = m.group(1)
    ip_addr = mip.group()

    if not valid_ip(ip_addr):
        return
    elif ip_addr in games:
        msg = 'Server re-added'
    else:
        msg = 'Server added'

    line = comm[comm.find(from_nick)-1:len(comm)]
    type = ''
    for trigger in triggers:
        if trigger in comm.lower():
            type = trigger
            if type == 'game': type = 'unknown game'
            break

    game[ip_addr] = dict(dc_nick=from_nick, ip=ip_addr, type=type, date=time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime()),line=line)
    cPickle.dump(games, file("games.dat","w"))

    data = '$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg)
    sharesize = len(games)
    update = '$MyINFO $ALL %s %s %s$ $%s%s$%s$%s$|' % (nick, description, tag, connection_type, chr(1), email, sharesize)
    threads[ID].send(data + update)

    message_players(games[ip_addr])

## de register players and things
def dereg(comm,ID,from_nick):
    if from_nick in players:
        if triggers in comm.lower():
            if player[from_nick].type =='all':player[from_nick].type = clone(triggers)
            de_types =[]
            for trigger in triggers:
                if trigger in comm.lower(): de_type.append(trigger)
            for de_type in de_types:
                try:player[from_nick].type.remove(de_type)
                except: de_types.remove(de_type)
            msg = 'your registration for '++','.join(de_types)++' games has been removed.\n\nYou are currently registered for' ++','.join(player[from_nick].type)++' games.'
        else:
            msg='you have been removed from the players list'
            del players[from_nick]

    else:
        msg='you are not registered'
    threads[ID].send('$To: %s From: %s $<%s> %s' % (from_nick, nick, nick, msg))

## registration control
def reg(comm,ID,from_nick):
    if triggers in comm.lower():
        type =[]
        for trigger in triggers:
            if trigger in comm.lower():
                type.append(trigger)
        player[from_nick] = dict(dc_nick=from_nick, ID=ID, type=type, connected = 1)
        msg = 'registered for ' ++ ', '.join(type) ++' games'
    elif 'all' in comm.lower():
        type = 'all'
        player[from_nick] = dict(dc_nick=from_nick, ID=ID, type=type, connected = 1)
        msg = 'registered for all games'
    else:
        msg ="""'
please send 'regme' followed by game types or all to register for all games

half life/cs/dod/source game type codes:
""" ++ ', '.join(cslib.triggers) ++"""
warcraft 3 game type codes:
"""++', '.join(war3lib.triggers)++"""
other games will appear as type 'game' until added.
"""
    threads[ID].send('$To: %s From: %s $<%s> %s' % (from_nick, nick, nick, msg))

## admin commands. mostly trivial stuff but anyway.
def admin(comm,ID):
    if __debug__: print 'admin talking' , comm, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
    if A_dump in comm:
        showservers = []
        showplayers = []
        for ip in games:
            serv=games[ip]
            showservers.append('%(ip)s : %(dc_nick)s : %(type)s : %(date)s : %(type)s' % (serv))
        for name in players:
            player=players[name]
            showplayers.append('%(name)s : %(state)s : %(games)s' %(player))
        playerlist = '\n'.join(showplayers)
        serverlist = '\n'.join(showservers)
        msg = "\nPlayers: \n%s \n Servers: \n%s" % (playerlist,serverlist)
        threads[ID].send ('$To: %s From: %s $<%s> %s|' % (admin, nick, nick, msg))
    elif A_reload in comm:
        try:
            reload(cslib)
            reload(dotalib)
            #specialshell.reloadALL #--need to find out about reloading main module
            msg='reload succesful'
        except:
            print 'errors in reload' , sys.exc_info(), time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
            raise
            msg='reload failed'

        threads[ID].send ('$To: %s From: %s $<%s> %s|' % (admin, nick, nick, msg))
    elif A_DO in comm:
        cut=len('$To: %s $From: %s <%s> adminDO' %(nick,admin,admin))
        do = comm[len-1:]
        try:
            eval(do)
            msg = 'command done sucesfully. (I think)'
        except error:
            msg = 'command faild. (I think)\n',sys.exc_info()[0]
        threads[ID].send('$To: %s From: %s $<%s> %s|' % (admin, nick, nick, msg))
    elif A_die in comm:
        exit()
    else:
        hlp = """
Admin commands (without quotes)
    "adminDump"            = terminal screen dump of DOTA player and server list
    "adminReload"           = reload modules (experimental)
    "adminServer" (ip)     = remove server from list
    "adminPlayer" [nick] = remove nick from player list. nick must be inside [] brackets.
    "adminDie"             = kill the bot for some reason.
"""
        data = '$To: %s From: %s $<%s> %s' % (from_nick, nick, nick, hlp)
        threads[ID].send(data + '|')

## starting options. removed most of it since it's no longer really neccesary. given the admin comands and what not.
if __name__ == '__main__':
    #global nick, dc_server_ip, dc_server_port, cs_Version, cs_server_ip, cs_server_port, dod_server_ip, dod_server_port, dlg_view_msg, options
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("--nick", dest="nick", help="DC++ nick name (def:%s)" % nick, default=nick)

    options, args = parser.parse_args()
    nick = options.nick

    try:
        fh = file("players.dat")
        players = cPickle.load(fh)
    except IOError:
        if __debug__: print 'IOError', sys.exc_info(), time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
        pass

    try:
        fh = file("games.dat")
        games = cPickle.load(fh)
    except IOError:
        if __debug__: print 'IOError', sys.exc_info(), time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
        pass

    servers = []
    dcbot = threading.Thread()
    dcbot.start()

    count = 1
    for server in server_list:
        ip,port,pas=server
        servers.append(threading.Thread(target = dclib.dc, args = (dcbot,ip,port,pas,sharesize,count)))
        count = count + 1

    for thread in servers:
        thread.start()
        #thread.main=dcbot



    threads.append(dcbot)
    threads.extend(servers)
    for thread in threads:
        thread.join()
