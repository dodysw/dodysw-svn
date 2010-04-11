"""
CS Library class
Copyright 2006, Mark Mckay <u4221317@anu.edu.au>

# This program is Free Software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.

"""

__version__ = '1.1.0'
__description__ = 'CS (1.6 and source) Library class'
__author__ = 'Mark Mckay <u4221317@anu.edu.au>'
__email__ = 'u4221317@anu.edu.au'

import socket, struct, string, time

show_triggers = ['showmecs','showmedod','showmesource']
triggers = ['cs','dod','source']

def check(version, addr, port):
    global misc, server_addr, server_port, mapname, server_name, current_player_count
    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    sock.settimeout(3)
    server_addr, server_port = addr, port
    try:
        # get challenge with version check
        if version == source: sock.sendto('\xff\xff\xff\xffW',(addr, port))
        else: sock.sendto('\xff\xff\xff\xffinfo\x00',(addr, port))
        buffer = sock.recv(50000)
        if version == source:
            # find \x00 which delimit the name/value
            challenge_string = buffer[5:].split(chr(0))[0]
            sock.sendto('\xff\xff\xff\xff\x54Source Engine Query',(addr, port))   # get server info
            buffer = sock.recv(50000)
            ar = buffer[5:].split(chr(0))
        else: ar = bffer.split('\x00')


        if ar[5] == '':
            t1 = t2 = 0
        else:
            if ar[5][0] != 0: t1 = ord(ar[5][0])
            if ar[5][1] != 0: t2 = ord(ar[5][1])
        server_name, mapname = ar[1], ar[2]
        
        if version == source: sock.sendto('\xff\xff\xff\xff\x55%s' % challenge_string,(addr, port))    # get player
        else: sock.sendto('\xff\xff\xff\xffplayers\x00',(addr, port))    # get player
        
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
            #print 'Here!', names
            names.sort()
            ff = []
            for i in range(1,len(names)+1):
                ff.append('%s -> %s (%s)' % (names[-i][0], names[-i][1], names[-i][2]))
            misc = '\n'.join(ff)
        else:
            misc = 'no one is playing...'
        current_player_count = t1
        return t1,t2,mapname,server_name,server_addr,server_port,misc
    except socket.error, e: # usually because LAN is not available
        return False
    except Exception, e:
        # anyother error, log to file, then continue
        file('error.log','a').write('%s: %s\n' % (time.strftime('%Y-%m-%d %H:%M:%S'), str(e)))
        return False


"""
this stuff has been combined into the check module should make things smallerish and possibly faster but anyway.

def check_cs(addr, port):
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
            #print 'Here!', names
            names.sort()
            ff = []
            for i in range(1,len(names)+1):
                ff.append('%s -> %s (%s)' % (names[-i][0], names[-i][1], names[-i][2]))
            misc = '\n'.join(ff)
        else:
            misc = 'no one is playing...'
        current_player_count = t1
        return t1,t2,mapname,server_name,server_addr,server_port,misc
    except socket.error, e: # usually because LAN is not available
        return False
    except Exception, e:
        # anyother error, log to file, then continue
        file('error.log','a').write('%s: %s\n' % (time.strftime('%Y-%m-%d %H:%M:%S'), str(e)))
        return False

def check_source(addr, port):
    global misc, server_addr, server_port, mapname, server_name, current_player_count
    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    sock.settimeout(3)
    server_addr, server_port = addr, port
    try:
        # get challenge
        sock.sendto('\xff\xff\xff\xffW',(addr, port))
        buffer = sock.recv(50000)
        # find \x00 which delimit the name/value
        challenge_string = buffer[5:].split(chr(0))[0]
        sock.sendto('\xff\xff\xff\xff\x54Source Engine Query',(addr, port))   # get server info
        buffer = sock.recv(50000)
        ar = buffer[5:].split(chr(0))
        if ar[5] == '':
            t1 = t2 = 0
        else:
            if ar[5][0] != 0: t1 = ord(ar[5][0])
            if ar[5][1] != 0: t2 = ord(ar[5][1])
        server_name, mapname = ar[1], ar[2]
        sock.sendto('\xff\xff\xff\xff\x55%s' % challenge_string,(addr, port))    # get player
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
        return t1,t2,mapname,server_name,server_addr,server_port,misc
    except socket.error, e: # usually because LAN is not available
        return False
    except Exception, e:
        # anyother error, log to file, then continue
        file('error.log','a').write('%s: %s\n' % (time.strftime('%Y-%m-%d %H:%M:%S'), str(e)))
        return False
"""
        
