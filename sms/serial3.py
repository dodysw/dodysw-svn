import sys,serial,re,csv,cStringIO,smsdody

line_number = 0
debug = 1

class Nokia:
    
    def __init__(self,port):
        self.openserial(port)
        print "Connected to ", port
        self.write("ATE")   #echo off
        self.recv()
        self.write("AT+CMGF=0") #set to pdu mode
        self.recv()
        
    def quit(self):
        self.closeserial()
    
    def openserial(self,port):
        try:
            self.tty = serial.Serial(4,38400,timeout=5)
        except Exception, msg:
            print "Problem: %s" % msg
            sys.exit()
            
    def closeserial(self):
        self.tty.close()
        

    def write(self, text):
        text += "\r\n"
        self.tty.write(text)
        if debug: print "Send>", text[:-2]
        
    def recv(self):
        """ return array of line (not including OK) """
        global line_number
        lines = []
        while 1:
            line = ""
            line = self.tty.readline()
            line_number += 1
            if debug: print line_number, line[:-2]                  
            if line.find("OK")==0:
                return lines
            elif line.find("ERROR") == 0:
                return 0
            lines.append(line)

# --- Main program
if __name__ == '__main__':
    hp = Nokia("COM5")
    hp.write("AT+CMGL")
    sms_lists = hp.recv()
    #processing sms list
    i = 0
    print "List:",sms_lists
    while i < len(sms_lists):        
        line = sms_lists[i]
        print "Line:",line
        header = re.search("^\+CMGL:\s+(.*)",line)
        if header:            
            header2 = header.group(1)
            csvfile = cStringIO.StringIO(header2)
            header_csv = csv.reader(csvfile)
            headers = header_csv.next() #array of elemens in header
            if debug: print "Head:", headers            
            if len(headers) == 5:   #sms with body                
                i += 1
                body = sms_lists[i]
                if debug: print "Body:", body
                sms_info = {'read':headers[1], 'from':headers[2], 'from_date':headers[4], 'body':body}
            elif len(headers) == 9: #sms without body (report)                
                pass
            else:
                raise "Don't understand sms"
        else:
            if debug: print "Other:", line
        i += 1
    hp.quit()