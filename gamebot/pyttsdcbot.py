#!/usr/bin/python
"""
TTS Bot
Copyright 2005, Dody Suria Wijaya <dodysw@gmail.com>
to use this software, you must install Python 2.3.x or above, pyTTS 3.0 or above for windows, or festival for linux
visit:
    - http://www.python.org
"""
import socket, time, sys, threading, struct, random, re, os

__version__ = '1.1.2'
__description__ = 'TTS Bot'
__author__ = 'Dody Suria Wijaya <dodysw@gmail.com>'
__email__ = 'dodysw@gmail.com'

# -- pydcbot config
nick = 'tts_bot'
password = ''
description = 'Text2speech bot'
tag = '<pyttsdcbot++ V:%s,M:A,H:1/0/0,S:1>' % __version__
connection_type = 'LAN(T3)'
email = 'dodysw@gmail.com'
sharesize = 1
state = 0
# --

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
            #~ if __debug__: print 'Going to receive data'
            data = s.recv(1024)
            if __debug__: print 'Received', data
            if not data: break
            process_data(s, data)
            #~ if __debug__: print 'Done processing data'
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
    lines = line.split(' ')
    cmd = lines[0]
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
        if '$GetPass' in line: # this user must be validated
            data = '$MyPass %s|' % password
            state = 3   # keep state
            if __debug__: print 'Send', data
            sock.send(data)
        elif '$Hello' in line:
            data = '$Version 1,0091|$GetNickList|$MyINFO $ALL %s %s %s$ $%s%s$%s$%s$|' % (nick, description, tag, connection_type, chr(1), email, sharesize)
            if __debug__: print 'Send', data
            sock.send(data)
            state = 4
        else:
            # dont change state
            pass
    else:
        if line[0] == '<':
            try: start = line.index('>')+2
            except ValueError: start = 0
            if line[1:line.index('>')] != 'MOTD': # dont speak message of the day
                try: end = line.index('|')
                except ValueError: end = -1
                msg_tts = line[start:end]
                # say it
                if enable_tts:
                    if msg_tts[0:9] == 'ttsspell:':
                        tts_command = msg_tts[9:]
                        if '=' in tts_command:
                            a,b = tts_command.split('=',1)
                            tts_p.AddMisspelled(a,b)
                            tts_p.Save('myTTS.dict')
                    elif msg_tts[0:8] == 'ttspron:':
                        tts_command = msg_tts[8:]
                        if '=' in tts_command:
                            a,b = tts_command.split('=',1)
                            tts_p.AddPhonetic(a,b)
                            tts_p.Save('myTTS.dict')
                    else:
                        Speak(tts_p.Correct(msg_tts))

def Speak(word):
    if sys.platform == 'win32':
        global tts
        if __debug__: 'Saying:', word
        tts.Speak(word, pyTTS.tts_async)
    elif 'linux' in sys.platform:
        global fst_fh
        word = word.replace('"','\\"')
        word = word.replace('&#124;','|')
        if __debug__: 'Saying:', word
        fst_fh.write('(SayText "%s")' % word)
        fst_fh.flush()

if __name__ == '__main__':
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("--cs_ip", dest="cs_server_ip", help="CS Server address (def:150.203.239.103)", default='150.203.239.103')
    parser.add_option("--cs_port", type="int", dest="cs_server_port", help="CS Server port (def:27015)", default=27015)
    parser.add_option("--dc_ip", dest="dc_server_ip", help="DC++ Server address (def:150.203.121.98)", default='150.203.121.98')
    parser.add_option("--dc_port", type="int", dest="dc_server_port", help="CS Server port (def:411)", default=411)
    parser.add_option("--nick", dest="nick", help="DC++ nick name (def:cs_bot)", default=nick)

    options, args = parser.parse_args()
    nick, dc_server_ip, dc_server_port, cs_server_ip, cs_server_port, dlg_view_msg = options.nick, options.dc_server_ip, options.dc_server_port, options.cs_server_ip, options.cs_server_port, ''

    enable_tts = False
    fst_fh = None
    try:
        if sys.platform == 'win32':
            import pyTTS
            tts = pyTTS.Create()
            ideal_voice = 'ATT-DT-14-Crystal16'
            list_of_voice = tts.GetVoiceNames()
            if ideal_voice in list_of_voice:
                tts.SetVoiceByName(ideal_voice)
                tts.SetOutputFormat(16,16,1)    # 16khz voice
            tts_p = pyTTS.Pronounce()
            if os.path.exists('myTTS.dict'):
                tts_p.Open('myTTS.dict')
            tts.Speak('', pyTTS.tts_async)  # seems to be needed
            enable_tts = True
        elif 'linux' in sys.platform:
            # check existance of festival first. how?
            fst_fh = os.popen('festival --pipe','w')
            enable_tts = True
    except ImportError:
        pass

    if enable_tts:
        Speak('Starting Python TTS')
    pydcbot()
    #~ threads = []
    #~ dcbot = threading.Thread(target = pydcbot)
    #~ dcbot.start()
    #~ threads.append(dcbot)
    #~ for thread in threads:
        #~ thread.join()
