"""
Wrcraft 3 Library class
Copyright 2005, Dody Suria Wijaya <dodysw@gmail.com>

# This program is Free Software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.

"""

__version__ = '1.1.0'
__description__ = 'Warcraft 3 Library class'
__author__ = 'Mark Mckay <etheral.point@gmail.com>'
__email__ = 'etheral.point@gmail.com'

import socket, struct, string

timeout_sec = 3
show_triggers = ['showmewar3','showmedota']
triggers = ['dota','war3','bships']


def HexerView(data):
    
    table = string.maketrans(struct.pack('32B',*range(32)),'.'*32)
    chunk = 16
    i = 0
    lines = []
    while 1:
        subdata = data[i*chunk:(i+1)*chunk]
        if subdata == '': break
        subdata2 = struct.unpack('%dB' % len(subdata),subdata)
        s1 = []
        for j in range(16):
            if j+1 > len(subdata2):
                #pad with empties first
                s1 += ['  ']*(16-j)
                break
            s1.append('%02x' % subdata2[j])
        lines.append('%s - %s ; %s %s' % (' '.join(s1[0:8]),' '.join(s1[8:16]), subdata[0:8].translate(table),subdata[8:16].translate(table)))
        i += 1
    return '\n'.join(lines)

class War3LanSession:
    print 'War3 lib loaded'
    port = 6112
    def __init__(self, addr):
        self.server_addr = (addr,self.port)
        self.name = self.session_id = ''
        self.player_count = self.max_player = self.join_tag = -1
        self.nick = "dota_bot"
        self.player_list = []
        self.map_name = 'unknown'
        self.human_player_name = {}

    def UpdateServerInfo(self):
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        sock.settimeout(timeout_sec)
        packet = '\xF7\x2F\x10\x00\x50\x58\x33\x57\x14\x00\x00\x00\x00\x00\x00\x00'
        #if __debug__: print 'error point' , packet, self.server_addr, self
        sock.sendto(packet,self.server_addr)   # get server info
        try:
            buffer = sock.recv(50000)
        except socket.timeout:
            if __debug__: print "Timeout"
            return False
        except socket.error:
            if __debug__: print "Error"
            return False
        self.HandleBuffer(buffer)
        return True

    def Join(self, nick = None):
        if nick:
            self.nick = nick
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(timeout_sec)
        while 1:
            try:
                sock.connect(self.server_addr)
                break
            except socket.error:
                if __debug__: print 'Unable to connect to %s...retrying in 60secs' % (self.server_addr,)
                return False
        source_ip = sock.getsockname()[0]
        assert(self.session_id != '')
        assert(self.nick != '')
        mystery = chr(self.join_tag)    # ???
        packet = '\x00%s\x00\x00\x00%s\x00\xe0\x17\x01\x00\x00\x00%s\x00\x01\x00\x02\x00%s%s\x00\x00\x00\x00\x00\x00\x00\x00' % (
            mystery,
            self.session_id,
            self.nick,
            struct.pack('!H',self.port),     # port
            socket.inet_aton(source_ip) # my ip address
            )
        packet = '\xf7\x1e%s%s' % (chr(len(packet)+3), packet)
        sock.send(packet)   # get server info
        while 1:
            try: buffer = sock.recv(1024)
            except socket.timeout: break
            if not buffer: break
            if not self.HandleBuffer(buffer):
                break
        return True

    def HandleBuffer(self, buffer):
        # it is possibile that each packet contains multiple command
        pos = 0
        while pos < len(buffer):
            command_id = ord(buffer[pos+1])
            command_len = struct.unpack("H",buffer[pos+2:pos+4])[0]
            #if __debug__: print 'HexView:','\n',HexerView(buffer[pos:pos+command_len])
            func_name = 'Handle_%x' % command_id
            if hasattr(self,func_name):
                self._break_connection = False
                getattr(self, func_name)(buffer[pos:pos+command_len])
                if self._break_connection:
                    if __debug__: print 'Disconnect requested by', func_name
                    return False
            else:
                if __debug__: print 'No handler found for proto-id hex %x' % command_id
                if __debug__: print 'HexView:','\n',HexerView(buffer[pos:pos+command_len])
            pos += command_len
        return True

    def Handle_30(self, buffer):
        self.name = buffer[20:buffer.find('\x00\x00',20)]
        self.max_player = ord(buffer[-22])
        self.player_count = self.max_player - ord(buffer[-10])+1
        self.session_id = buffer[16:20]
        self.join_tag = ord(buffer[12:13])

    def Handle_3d(self, buffer):
        i = 8
        pos = buffer.find('\x00',i)
        self.map_name = buffer[i:pos]
        self.map_name = self.map_name[self.map_name.rindex("\\")+1:self.map_name.rindex(".")]
        #~ print self.map_name

    def Handle_4(self, buffer):
        self.player_count=0
        player_num = ord(buffer[6])
        i =  8
        all_players = []
        human_player_name_index = 0
        for j in range(player_num):
            pos = i+j*9
            line = buffer[pos:pos+9]
            #~ print HexerView(line)
            if line[0] == '\xff':
                if line[1] == '\x00':
                    all_players.append('Open')
                    self.player_count+=1
                elif line[1] == '\x01':
                    all_players.append('Closed')
                elif line[1] == '\x02':
                    if line[2] == '\x00':
                        all_players.append('Open')
                        self.player_count+=1
                    elif line[2] == '\x01':
                        level = ['Easy','Normal','Insane']
                        all_players.append('Computer (%s)' % (level[ord(line[-3])]))
                else:
                    all_players.append(HexerView(line))
            elif line[0] == '\x64':
                all_players.append(self.human_player_name.get(human_player_name_index, 'Unknown'))
                human_player_name_index += 1
            else:
                all_players.append(line)
        self.player_list = all_players

    def Handle_6(self, buffer):
        # parse buffer
        i = 8   # start at this pos
        player_no = ord(buffer[i]) - 1
        pos = buffer.find('\x00',i+1)
        player_name = buffer[i+1:pos]
        self.human_player_name[player_no] = player_name
        #~ print self.human_player_name

    def Handle_9(self, buffer):
        self.Handle_4(buffer)
        self._break_connection = True   # quick get out from joining in
