"""
DOTA Library class
Copyright 2005, Dody Suria Wijaya <dodysw@gmail.com>

# This program is Free Software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.

"""

__version__ = '1.0.0'
__description__ = 'DOTA Library class'

import socket, struct, string

timeout_sec = 3

def HexerView(data):
    """
    dump nice Hex formated texts from binary data
    """
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

class DotaLanSession:
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
            if __debug__: print 'HexView:','\n',HexerView(buffer[pos:pos+command_len])
            func_name = 'Handle_%x' % command_id
            if hasattr(self,func_name):
                self._break_connection = False
                getattr(self, func_name)(buffer[pos:pos+command_len])
                if self._break_connection:
                    if __debug__: print 'Disconnect requested by', func_name
                    return False
            else:
                if __debug__: print 'No handler found for proto-id hex %x' % command_id
            pos += command_len
        return True

    def Handle_30(self, buffer):
        """
        Response to basic game information request
f7 30 84 00 50 58 33 57 - 13 00 00 00 01 00 00 00 ; ˜0ä.PX3W ........
c8 12 09 44 4c 6f 63 61 - 6c 20 47 61 6d 65 20 28 ; +..DLoca l Game (
42 61 6e 61 6e 61 29 00 - 00 01 03 49 07 01 01 75 ; Banana). ...I...u
01 91 75 01 5b cb 7d 35 - 4d cb 61 71 73 5d 45 6f ; .æu.[-}5 M-aqs]Eo
77 19 6f 6d 6f 61 65 5d - 45 2b 6f 75 41 21 41 6d ; w.omoae] E+ouA!Am
6d 2b 73 75 61 73 73 21 - 77 c9 37 2f 31 39 2f 77 ; m+suass! w+7/19/w
33 51 79 01 43 61 6f 61 - 6f 03 61 01 01 00 0c 00 ; 3Qy.Caoa o.a.....
00 00 01 00 00 00 01 00 - 00 00 09 00 00 00 8e 0b ; ........ ......Ä.
00 00 e0 17             -                         ; ..a.

full game:
f7 30 8b 00 50 58 33 57 - 13 00 00 00 01 00 00 00 ; ˜0ï.PX3W ........
fc d1 b8 02 4c 6f 63 61 - 6c 20 47 61 6d 65 20 28 ; n-+.Loca l Game (
53 63 61 72 66 69 65 29 - 00 00 01 03 49 07 01 01 ; Scarfie) ....I...
75 01 e1 75 01 d1 f9 87 - 6f 4d 8b 61 71 73 5d 47 ; u.ßu.-·ç oMïaqs]G
73 6f 85 7b 65 6f 55 69 - 73 6f a5 6f 65 5d 45 6f ; soà{eoUi soÑoe]Eo
75 41 a5 21 41 6d 6d 73 - 75 61 85 73 73 21 77 37 ; uAÑ!Amms uaàss!w7
2f 31 9b 39 2f 77 33 79 - 01 53 67 63 61 73 67 69 ; /1¢9/w3y .Sgcasgi
65 01 01 01 00 0c 00 00 - 00 01 00 00 00 0a 00 00 ; e....... ........
00 0a 00 00 00 63 00 00 - 00 e0 17                ; .....c.. .a.

        """
        self.name = buffer[20:buffer.find('\x00\x00',20)]
        self.player_count = 12-ord(buffer[-10])+1
        self.max_player = ord(buffer[-22])
        self.session_id = buffer[16:20]
        self.join_tag = ord(buffer[12:13])

    def Handle_3d(self, buffer):
        """
Map information
f7 3d 3a 00 01 00 00 00 - 4d 61 70 73 5c 44 6f 77 ; ˜=:..... Maps\Dow
6e 6c 6f 61 64 5c 44 6f - 74 41 20 41 6c 6c 73 74 ; nload\Do tA Allst
61 72 73 20 76 36 2e 31 - 38 2e 77 33 78 00 89 90 ; ars v6.1 8.w3x.ëÉ
1c 00 df fd 94 b4 5a cb - 7c 34                   ; ..¯²ö¦Z- |4
        """
        i = 8
        pos = buffer.find('\x00',i)
        self.map_name = buffer[i:pos]
        self.map_name = self.map_name[self.map_name.rindex("\\")+1:self.map_name.rindex(".")]
        #~ print self.map_name

    def Handle_4(self, buffer):
        """
        List of all players currently in game
f7 04 8a 00 73 00 0c 00 - ff 02 01 00 00 04 01 64 ; ˜.è.s...  ......d
01 64 02 00 00 01 04 01 - 64 00 ff 00 00 00 02 04 ; .d...... d. .....
01 64 00 ff 00 00 00 03 - 04 01 64 00 ff 00 00 00 ; .d. .... ..d. ...
04 04 01 64 00 ff 00 00 - 00 05 04 01 64 00 ff 02 ; ...d. .. ....d. .
01 01 06 08 01 64 00 ff - 00 00 01 07 08 01 64 00 ; .....d.  ......d.
ff 00 00 01 08 08 01 64 - 00 ff 00 00 01 09 08 01 ;  ......d . ......
64 00 ff 00 00 01 0a 08 - 01 64 00 ff 02 01 01 0b ; d. ..... .d. ....
08 01 64 1c 23 32 22 03 - 0c 02 02 00 0a ba 96 cb ; ..d.#2". .....¦û-
73 73 00 00 00 00 00 00 - 00 00                   ; ss...... ..
        Almost the same as handle 9
        """
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
                elif line[1] == '\x01':
                    all_players.append('Closed')
                elif line[1] == '\x02':
                    if line[2] == '\x00':
                        all_players.append('Open')
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
        """
        List of human players name
f7 06 32 00 01 00 00 00 - 01 42 61 6e 61 6e 61 00 ; ˜.2..... .Banana.
01 00 00 00 00 00 00 00 - 00 00 00 00 00 00 00 00 ; ........ ........
00 00 00 00 00 00 00 00 - 00 00 00 00 00 00 00 00 ; ........ ........
00 00                   -                         ; ..
        """
        # parse buffer
        i = 8   # start at this pos
        player_no = ord(buffer[i]) - 1
        pos = buffer.find('\x00',i+1)
        player_name = buffer[i+1:pos]
        self.human_player_name[player_no] = player_name
        #~ print self.human_player_name

    def Handle_9(self, buffer):
        """
        List of player data
f7 09 79 00 73 00 0c 00 - ff 02 01 00 00 04 01 64 ; ˜.y.s...  ......d
01 64 02 00 00 01 04 01 - 64 02 ff 02 00 00 02 04 ; .d...... d. .....
01 64 00 ff 00 00 00 03 - 04 01 64 00 ff 00 00 00 ; .d. .... ..d. ...
04 04 01 64 00 ff 00 00 - 00 05 04 01 64 00 ff 02 ; ...d. .. ....d. .
01 01 06 08 01 64 00 ff - 00 00 01 07 08 01 64 00 ; .....d.  ......d.
ff 00 00 01 08 08 01 64 - 00 ff 00 00 01 09 08 01 ;  ......d . ......
64 00 ff 00 00 01 0a 08 - 01 64 00 ff 02 01 01 0b ; d. ..... .d. ....
08 01 64 4a 6d 09 22 03 - 0c                      ; ..dJm.". .

- from hex #3:
- 2B => total packet length
- 2B => subpacket length
- 2B => number of players
- 9B => player list info
    - 1B => 64 = human, ff = else
    ...
- repeat until 9B * number of players
- 6d 09 22 03 0c => ??
        """
        self.Handle_4(buffer)
        self._break_connection = True   # quick get out from joining in