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
"""
import socket, time, sys, threading, struct, random, re, cPickle
import dotalib
__version__ = '1.1.0'
__description__ = 'DC game Bot'
__author__ = 'Mark Mckay <etheral.point@gmail.com>'
__email__ = 'etheral.point@gmail.com'

# -- pydcbot config
nick = 'game_bot'
password = 'dc_game_bot'
description = 'Game tracking bot'
tag = '<DCGameBot V:%s,M:A,H:1/0/0,S:1>' % __version__
connection_type = 'LAN(T3)'
email = ''
sharesize = 1
state = 0
T_SHOWMECS, T_DOTASERVER, T_DOTAPLAYER, T_HELP, T_SHOWMEDOTA, T_PUBLICDOTA= trigger_word = ['showmecs', 'dotaserver', 'dotaplayer', 'help', 'showmedota', 'dota']
dota_proper = {}
dota_players = {}
cs_players = {}
regip='[12]?[0-9]?[0-9](\.[12]?[0-9]?[0-9]){3}'

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
    while 1:
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        try:
            s.connect((dc_server_ip, dc_server_port))
        except socket.error:
            #if __debug__: print 'Unable to connect...retrying in 60secs'
            time.sleep(60)   # reconnect if broken in 60 secs
            continue
        while 1:
            data = s.recv(1024)
            #if __debug__: print 'Received', data
            if not data: break
            process_data(s, data)
        s.close()
        #if __debug__: print 'Connection broken...retrying in 10secs'
        time.sleep(10)   # reconnect if broken in 60 secs

def message_players(sock, obj):
    # PM listed players when a server is added
    for player in dota_players:
        msg = 'We have a new DOTA server online at %(ip)s -- %(dc_nick)s' % obj
        data = '$To: %s From: %s $<%s> %s' % (player, nick, nick, msg)
        sock.send(data + '|')

def process_data(sock, line):
    """state machine:
    0: tcp handshake, after receiving Lock challange, before sending Key
    1: after sending Key response, before ValidateNick
    2: after sending ValidateNick, before sending Version/GetNickList/MyInfo
    3: after sending Version/GetNickList/MyInfo, before receiving Hello
    4: after receiving Hello, login complete
    """
    global state, simple_msg, dlg_view_msg
    for comm_group in line.split('|'):
        if comm_group == '':
            continue
        lines = comm_group.split(' ')
        cmd = lines[0]
        #print 'Command is', cmd
        if cmd == '$Lock':
            lock = lines[1]
            key = lock2key(lock)
            #~ data = '$Supports UserCommand NoGetINFO NoHello UserIP2 TTHSearch |$Key %s|$Validate %s|' % (key,nick)
            data = '$Key %s|' % key
            if __debug__: print 'Send', data
            sock.send(data)
            state = 1
            if __debug__: print 'state', state
        elif state == 1:
            data = '$ValidateNick %s|' % nick
            if __debug__: print 'Send', data
            sock.send(data)
            state = 3
            if __debug__: print 'state', state
        elif state == 3:
            if cmd == '$GetPass': # this user must be validated
                data = '$MyPass %s|' % password
                state = 3   # keep state
                if __debug__: print 'Send', data
                sock.send(data)
            elif cmd == '$Hello':
                data = '$Version 1,0091|$GetNickList|$MyINFO $ALL %s %s %s$ $%s%s$%s$%s$|' % (nick, description, tag, connection_type, chr(1), email, sharesize)
                if __debug__: print 'Send', data
                sock.send(data)
                state = 4
                if __debug__: print 'state', state
            else:
                # dont change state
                pass
        else:
            trigger(cmd,comm_group,sock)

def trigger(cmd,comm,sock):
    global cs_players
    if cmd == '$To:':
        m = re.search('<([^>]*)>',comm)
        if not m: return
        from_nick = m.group(1)
        if T_SHOWMECS in comm:
            ret = check(options.cs_server_ip, options.cs_server_port)
            if ret == False:
                simple_msg = 'Unable to connect to %s:%s' % (options.cs_server_ip, options.cs_server_port)
                data = '$To: %s From: %s $<%s> %s - %s' % (from_nick, nick, nick, simple_msg, random.random())
            else:
                t1,t2,mapname = ret
                dlg_view_msg = '%s of %s people. Map: %s. Server: %s (%s:%s) - %s\n%s' % (t1,t2,mapname, server_name, server_addr, server_port, time.strftime('%H:%M:%S'), misc)
                data = '$To: %s From: %s $<%s> %s' % (from_nick, nick, nick, dlg_view_msg)
                data = data.replace('|', '&#124;')
            if __debug__: print 'Send', data
            sock.send(data + '|')
            # new: add
            cs_players.setdefault(from_nick,0)
            cs_players[from_nick] += 1
        elif T_DOTASERVER in comm:
            mip = re.search(regip,comm)
            if not mip:
                msg = 'Please provide the server ip.'
                return
            ip_addr = mip.group()

            if ip_addr in dota_proper:
                del dota_proper[ip_addr]
                cPickle.dump(dota_proper, file("dota_proper.dat","w"))
                msg = 'server removed from Dota server tracker'
            else:
                dota_proper[ip_addr] = dict(dc_nick=from_nick, ip=ip_addr, date=time.strftime('%H:%M:%S on %D/%M',time.localtime()))
                cPickle.dump(dota_proper, file("dota_proper.dat","w"))
                msg = 'Server added'
                message_players(sock, dota_proper[ip_addr])
            data = '$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg)
            sock.send(data + '|')
        elif T_DOTAPLAYER in comm:
            if from_nick in dota_players:
                del dota_players[from_nick]
                cPickle.dump(dota_players, file("dota_players.dat","w"))
                msg = 'You have been removed from player list.'
                sock.send('$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg))
            else:
                dota_players[from_nick] = 1
                cPickle.dump(dota_players, file("dota_players.dat","w"))
                msg = 'Added to player list. You will be PM-ed if there is a new DOTA server.'
                sock.send('$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg))
        elif T_SHOWMEDOTA in comm:
            if not dota_proper:
                msg = "No DOTA server at the moment. Try again next time :D"
                data = '$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg)
                sock.send(data)
                return
            lines = []
            delete_list = []
            for ip in dota_proper:
                rp = dotalib.DotaLanSession(ip)
                if not rp.UpdateServerInfo():
                    output = "DOTA Server at %s -- Not responding, or already started. Will be removed from list." % ip
                    delete_list.append(ip)
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
%s

""" % (ip, rp.name, rp.player_count, rp.max_player)
                lines.append(output)
            msg = ('='*20 + "\n").join(lines)
            data = '$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg)
            sock.send(data + '|')

            for ip in delete_list:
                del dota_proper[ip]
            cPickle.dump(dota_proper, file("dota_proper.dat","w"))

        #~ elif T_HELP in comm:
        else:
            hlp = """

Available commands (without quotes):
    "showmecs" = view counterstrike players and scores.
    "dotaserver 123.123.123.123" = publish/unpublish dota server at given IP address.
    "dotaplayer" = toggle notification to you when dota game is published.
    "showmedota" = view available dota servers.

pm SUZ with complaints and suggestions.

"""
            data = '$To: %s From: %s $<%s> %s' % (from_nick, nick, nick, hlp)
            sock.send(data + '|')

    elif T_PUBLICDOTA in comm.lower():
        mip = re.search(regip,comm)
        if not mip: return
        m = re.search('<([^>]*)>',comm)
        if not m: return
        from_nick = m.group(1)
        ip_addr = mip.group()

        if ip_addr in dota_proper:
            msg = 'Server added'
        else:
            msg = 'Server re-added'
        sock.send('$To: %s From: %s $<%s> %s|' % (from_nick, nick, nick, msg))
        dota_proper[ip_addr] = dict(dc_nick=from_nick, ip=ip_addr, date=time.strftime('%H:%M:%S on %D/%M',time.localtime()))
        cPickle.dump(dota_proper, file("dota_proper.dat","w"))
        message_players(sock, dota_proper[ip_addr])

if __name__ == '__main__':
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("--cs_ip", dest="cs_server_ip", help="CS Server address (def:150.203.239.103)", default='150.203.239.103')
    parser.add_option("--cs_port", type="int", dest="cs_server_port", help="CS Server port (def:27015)", default=27015)
    parser.add_option("--dc_ip", dest="dc_server_ip", help="DC++ Server address (def:150.203.121.98)", default='150.203.121.98')
    parser.add_option("--dc_port", type="int", dest="dc_server_port", help="CS Server port (def:411)", default=411)
    parser.add_option("--nick", dest="nick", help="DC++ nick name (def:%s)" % nick, default=nick)
    parser.add_option("--pm_only", action="store_true", dest="pm_only", help="Only reply query via personal message", default=False)

    options, args = parser.parse_args()
    nick, dc_server_ip, dc_server_port, cs_server_ip, cs_server_port, dlg_view_msg = options.nick, options.dc_server_ip, options.dc_server_port, options.cs_server_ip, options.cs_server_port, ''

    try:
        fh = file("dota_players.dat")
        dota_players = cPickle.load(fh)
    except IOError:
        pass

    try:
        fh = file("dota_proper.dat")
        dota_proper = cPickle.load(fh)
    except IOError:
        pass

    threads = []
    dcbot = threading.Thread(target = pydcbot)
    dcbot.start()
    threads.append(dcbot)
    for thread in threads:
        thread.join()
