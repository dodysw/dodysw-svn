
# bind to port 6112 udp

# if got udp msg from 6112, with this tag:
# F7 2F 10 00 50 58 33 57 13 00 00 00 00 00 00 00

"""
reply this:
F7 30
84 00 <= change to packet size
50 58 33 57 13 00 00 00 01 00 00 00
54 51 B2 7D
Local Game (dodygila)
00 00 01 03 49 07 01 01 75 01 91 75 01 5B CB 7D 35 4D CB 61 71 73 5D 45 6F 77 19 6F 6D 6F 61 65 5D 45 2B 6F 75 41 21 41 6D 6D 2B 73 75 61 73 73 21 77 C9 37 2F 31 39 2F 77 33 D1 79 01 69 61 73 75 6B 03 61 01 01 00 0C 00
00 00 01 00 00 00 01 00 00 00
0A 00   <= change to player count + 3
00 00 48 00 00 00 E0 17
"""

import SocketServer, socket, struct, time, string, pprint

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

class DotaLanSession:
    port = 6112
    def __init__(self, addr):
        self.server_addr = (addr,self.port)
        self.name = self.session_id = ''
        self.player_count = self.max_player = self.join_tag = -1
        self.nick = "dota_bot"
        self.player_list = []

    def UpdateServerInfo(self):
        sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        sock.settimeout(1)
        packet = '\xF7\x2F\x10\x00\x50\x58\x33\x57\x13\x00\x00\x00\x00\x00\x00\x00'
        sock.sendto(packet,self.server_addr)   # get server info
        try:
            buffer = sock.recv(50000)
        except socket.timeout:
            return False
        self.HandleBuffer(buffer)
        return True

    def Join(self, nick = None):
        if nick:
            self.nick = nick
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(1)
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
            buffer = sock.recv(1024)
            if not buffer: break
            #~ if __debug__: print 'HexView:','\n',HexerView(buffer)
            self.HandleBuffer(buffer)
            if ord(buffer[1]) == 6: break
        return True

    def HandleBuffer(self, buffer):
        # it is possibile that each packet contains multiple command
        pos = 0
        while pos < len(buffer):
            command_id = ord(buffer[pos+1])
            command_len = struct.unpack("H",buffer[pos+2:pos+3])[0]
            func_name = 'Handle_%x' % command_id
            if hasattr(self,func_name):
                getattr(self, func_name)(buffer)
            else:
                if __debug__: print 'No handler found for proto-id hex %x' % command_id
            pos += command_len

    def Handle_30(self, buffer):
        self.name = buffer[20:buffer.find('\x00\x00',20)]
        self.player_count = 12-ord(buffer[-10])+1
        self.max_player = ord(buffer[-22])
        self.session_id = buffer[16:20]
        self.join_tag = ord(buffer[12:13])

    def Handle_6(self, buffer):
        # parse buffer
        i = 8   # start at this pos
        players = []
        while 1:
            player_no = ord(buffer[i])
            pos = buffer.find('\x00',i+1)
            if pos == -1: break
            player_name = buffer[i+1:pos]
            players.append(player_name)
            if ord(buffer[i+1+len(player_name)+39]) == 1:
                i += 1+len(player_name)+43
                break
            #~ if __debug__: print HexerView(buffer[i:pos+43])
            i = pos + 43
            if i > len(buffer)-1:
                break

        # parse map name
        pos = buffer.find('\x00',i)
        self.map_name = buffer[i:pos]
        i = pos
        i += 19
        player_num = ord(buffer[i])
        # parse player list (+20 after map)
        i += 2
        all_players = []
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
                all_players.append(players[0])
                del players[0]
            else:
                all_players.append(line)
        self.player_list = all_players

class MyRequestHandler(SocketServer.DatagramRequestHandler):
    def handle(self):
        """
        The handle() method can find the request as self.request, the
        client address as self.client_address, and the server (in case it
        needs access to per-server information) as self.server.  Since a
        separate instance is created for each request, the handle() method
        can define arbitrary other instance variariables.
        """
        if ord(self.request[0][0]) == 0xf7:
            #~ self.client_address = ('150.203.223.162',6112)
            rp = LanSession(self.client_address)
            rp.UpdateServerInfo()
            print 'Name:',rp.name, 'Count:',rp.player_count,'/12'
            # try joining
            if __debug__: print "Joining"
            rp.Join("Cendol")

if __name__ == '__main__':
    #~ udpd = SocketServer.ThreadingUDPServer(('0.0.0.0', 6112), MyRequestHandler)
    #~ udpd.serve_forever()


    ip = '150.203.115.116'
    rp = DotaLanSession(ip)
    if not rp.UpdateServerInfo():
        output = "DOTA Server at %s | Not responding, or already started" % ip
    else:
        if rp.Join("dota_bot"):
            player_str = '\n'.join([ "%s. %s" % (i+1,name) for i,name in enumerate(rp.player_list)])
            output = """
DOTA Server at %s | Game Name: %s
Current Capacity: %s of %s
Players:
%s
            """ % (ip, rp.name, rp.player_count, rp.max_player, player_str)
        else:
            output = """
DOTA Server at %s | Game Name: %s
Current Capacity: %s of %s (FULL CAPACITY)
Players: -- sorry can't get the list since it's already full
%s
            """ % (ip, rp.name, rp.player_count, rp.max_player)
    print output