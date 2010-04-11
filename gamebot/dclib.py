"""
DC++ Library class
Copyright 2006, Mark Mckay <u4221317@anu.edu.au>

# This program is Free Software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.

"""

import socket, time, dcgamebot

__version__ = '1.0.0'
__description__ = 'DC++ Library class'
__author__ = 'Mark Mckay <u4221317@anu.edu.au>'
__email__ = 'u4221317@anu.edu.au'

# -- dc configuration
nick = 'game_bot_test'
password = ''
tag = '<DCGameBot V:%s,M:A,H:1/0/0,S:1>' % __version__
connection_type = 'LAN(T3)'
email = ''
description = 'Game tracking bot'
sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
connected = 0
sharesize = 0
state = 0
ID=0 # position in servers array
main = dcgamebot.main()

def process_data(dc,line):
    """state machine:
    0: tcp handshake, after receiving Lock challange, before sending Key
    1: after sending Key response, before ValidateNick
    2: after sending ValidateNick, before sending Version/GetNickList/MyInfo
    3: after sending Version/GetNickList/MyInfo, before receiving Hello
    4: after receiving Hello, login complete
    """
    global state, simple_msg, dlg_view_msg
    for comm_group in line.split('|'):
        print 'COMM GROUP', repr(comm_group)
        if comm_group == '':
            continue
        lines = comm_group.split(' ')
        cmd = lines[0]
        #print 'Command is', cmd
        if cmd == '$Lock':
            lock = lines[1]
            key = lock2key(lock)
            #~ data = '$Supports UserCommand NoGetINFO NoHello UserIP2 TTHSearch |$Key %s|$Validate %s|' % (key,nick)
            data = '$Key %s' % key
            if __debug__: print 'Send', repr(data), time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
            dc.send(data)
            state = 1
            if __debug__: print 'state', state, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
        elif state == 1:
            data = '$ValidateNick %s' % nick
            if __debug__: print 'Send', data, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
            dc.send(data)
            state = 2
            if __debug__: print 'state', state, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
        elif state == 2:
            if cmd == '$GetPass': # this user must be validated
                data = '$MyPass %s' % password
                state = 2   # keep state
                if __debug__: print 'Send', data, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
                dc.send(data)
            elif cmd == '$Hello':
                data = '$Version 1,0091|$MyINFO $ALL %s %s %s$ $%s%s$%s$%s$' % (nick, description, tag, connection_type, chr(1), email, sharesize)
                if __debug__: print 'Send', data, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
                dc.send(data)
                state = 4
                if __debug__: print 'state', state, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
            else:
                # dont change state
                pass
        else:
            main.trigger(cmd,comm_group,ID)


def lock2key(lock):
    "Generates response to $Lock challenge from Direct Connect Servers"
    print 'LOCK:[%s]' % repr(lock)
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


class dc:
    def __init__(self, caller, ip, port = 411, pas = 'dc++_game_bot_pass', share = 0, pos=0):
        sharesize=share*1024*1024*1024*1024
        main = caller
        ID = pos
        password = pas
        while 1:
            try:
                sock.connect((ip,port))
                connected = 1
            except socket.error:
                if __debug__: print 'server %s \n' % ID, 'Unable to connect...retrying in 60secs', time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
                time.sleep(60)   # reconnect if broken in 60 secs
                connected = 0
                continue
            while connected:
                try:
                    data = sock.recv(1024)
                except socket.error, error:
                    if __debug__: print '/n socket error after connect /n', time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime()), '/n error %s /n' % error
                    connected = 0
                    break
                #except:
                #    if __debug__: print '/n error after connect /n', time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime()), '/n error %s /n' % error
                #    connected = 0
                #    break
                process_data(self,data)
                #if __debug__: print '/n Received /n', data
            sock.close()
            time.sleep(60) # reconnect if broken in 60 secs

    def send(self,data):
        try:
            sock.send(data+'|')
        except socket.error:
            if __debug__: print 'socket error on send', 'errno %s - %S' % error, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
            connected =0
        #except:
        #    if __debug__: print 'unknown error on send', 'errno %s - %S' % error, time.strftime('%H:%M:%S on %d/%m/%Y',time.localtime())
        #    connected = 0

    def update(share):
        sharesize = share
        data = '$MyINFO $ALL %s %s %s$ $%s%s$%s$%s$' % (nick, description, tag, connection_type, chr(1), email, sharesize)
        send(data)


