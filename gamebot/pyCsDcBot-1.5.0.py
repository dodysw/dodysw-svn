#!/usr/bin/python
"""
CS DC Bot
Copyright 2005, Dody Suria Wijaya <dodysw@gmail.com>
to use this software, you must install Python 2.3.x or above
to compile into independent .exe, you must install py2exe
visit:
    - http://www.python.org
    - http://starship.python.net/crew/theller/py2exe/
"""
import socket, time, sys, threading, struct, random, re
#~ import warnings
#~ warnings.filterwarnings('ignore','',FutureWarning)

__version__ = '1.5.0'
__description__ = 'CS DC Bot'
__author__ = 'Dody Suria Wijaya <dodysw@gmail.com>'
__email__ = 'dodysw@gmail.com'

# -- pydcbot config
nick = 'cs_bot'
password = 'cs_bot_cs'
description = 'Counter-strike bot'
tag = '<pyCsDcBot++ V:%s,M:A,H:1/0/0,S:1>' % __version__
connection_type = 'LAN(T3)'
email = 'dodysw@gmail.com'
sharesize = 1
state = 0
trigger_word = 'showmecs'
reply_public_channel = False    # True to reply trigger_word from public channel, in addition to private channel
# --

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
            print 'Here!', names
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
            if __debug__: print 'Unable to connect...retrying in 60secs'
            time.sleep(60)   # reconnect if broken in 60 secs
            continue
        while 1:
            data = s.recv(1024)
            if __debug__: print 'Received', data
            if not data: break
            process_data(s, data)
        s.close()
        if __debug__: print 'Connection broken...retrying in 10secs'
        time.sleep(10)   # reconnect if broken in 60 secs

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
        print 'Command is', cmd
        if cmd == '$Lock':
            lock = lines[1]
            key = lock2key(lock)
            #~ data = '$Supports UserCommand NoGetINFO NoHello UserIP2 TTHSearch |$Key %s|$Validate %s|' % (key,nick)
            data = '$Key %s|' % key
            if __debug__: print 'Send', data
            sock.send(data)
            state = 1
        elif state == 1:
            data = '$ValidateNick %s|' % nick
            if __debug__: print 'Send', data
            sock.send(data)
            state = 3
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
            else:
                # dont change state
                pass
        else:
            if (options.pm_only and cmd == '$To:' and trigger_word in comm_group) or (not options.pm_only and trigger_word in comm_group):
                # findout the source
                m = re.search('<([^>]*)>',comm_group)
                if m:
                    ret = check(options.cs_server_ip, options.cs_server_port)
                    if ret == False:
                        simple_msg = 'Unable to connect to %s:%s' % (options.cs_server_ip, options.cs_server_port)
                        data = '$To: %s From: %s $<%s> %s - %s' % (m.group(1), nick, nick, simple_msg, random.random())
                    else:
                        t1,t2,mapname = ret
                        dlg_view_msg = '%s of %s people. Map: %s. Server: %s (%s:%s) - %s\n%s' % (t1,t2,mapname, server_name, server_addr, server_port, time.strftime('%H:%M:%S'), misc)
                        data = '$To: %s From: %s $<%s> %s' % (m.group(1), nick, nick, dlg_view_msg)
                        data = data.replace('|', '&#124;')
                    if __debug__: print 'Send', data
                    sock.send(data + '|')
            elif options.pm_only and cmd != '$To:' and trigger_word in comm_group:
                # findout the source
                m = re.search('<([^>]*)>',comm_group)
                if reply_public_channel and m:
                    msg = 'pycsdcbot is configured to reply only via personal message. Type this instead: /pm %s %s' % (nick, trigger_word)
                    data = '$To: %s From: %s $<%s> %s' % (m.group(1), nick, nick, msg)
                    if __debug__: print 'Send', data
                    sock.send(data + '|')
            elif comm_group[0] == '<':
                try: start = comm_group.index('>')+2
                except ValueError: start = 0
                try: end = comm_group.index('|')
                except ValueError: end = -1

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
    threads = []
    dcbot = threading.Thread(target = pydcbot)
    dcbot.start()
    threads.append(dcbot)
    for thread in threads:
        thread.join()
