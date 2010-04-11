import sys

class PDU:
	
	def __init__(self):
		

	# function to convert semioctets to a string
	def semioctet_to_string(self,inp):
	    out = ""
	    for i in range(0,len(inp),2):
	        out += inp[i+1] + inp[i]
	    return out
	
	# function to convert hex string to integer
	def hex_to_num(self,hexstr):
	    return int(hexstr,16)
	
	# function to convert hex pairs string, to 7bit representation
	def convert8bit_to_7bit(self, str, length ):
	    """Convert compressed 7bit-encoded string to 8bit characters.
	    len is the number of EXPANDED characters to expect.
	    Returns string."""
	
	    packed = []
	    for i in range(0,len(str),2):
	        packed = packed + [eval( '0x' + str[i:i+2] )]
	
	    dp = ''
	    j = 0
	    while length > 0:
	        i = 0
	        bb = 0
	        while i < 7 and length > 0:
	            bb = bb | packed[i+j] << i
	            dp = dp + chr( bb & 0x7f )
	            bb = bb >> 7
	
	            i = i + 1
	            length = length - 1
	        length = length - 1
	        if length > 0:
	            dp = dp + chr(bb)
	        j = j + 7
	
	    return dp
	
	def convert7bit_to_8bit(self, str, length ):
	    """Convert compressed 7bit-encoded string to 8bit characters.
	    len is the number of EXPANDED characters to expect.
	    Returns string."""
	
	    packed = []
	    for i in range(0,len(str),2):
	        packed = packed + [eval( '0x' + str[i:i+2] )]
	
	    dp = ''
	    j = 0
	    while length > 0:
	        i = 0
	        bb = 0
	        while i < 7 and length > 0:
	            bb = bb | packed[i+j] << i
	            dp = dp + chr( bb & 0x7f )
	            bb = bb >> 7
	
	            i = i + 1
	            length = length - 1
	        length = length - 1
	        if length > 0:
	            dp = dp + chr(bb)
	        j = j + 7
	
	    return dp
	    
	# function to decode timestamp
	def decode_timestamp(self,ts):
        year = ts[0:2]
        month = ts[2:4]
        day = ts[4:6]
        hours = ts[6:8]
        minutes = ts[8:10]
        seconds = ts[10:12]
        gmt = int(ts[12:14])
        #decode GMT, where gmt value is in 15 minutes division
        if gmt >= 128:
            #negative GMT
            gmt = '-' + str((gmt - 128) * 15 / 60)
        else:
            gmt = '+' + str(gmt * 15 / 60)
        return "%s-%s-%s %s:%s:%s GMT%s" % (year, month, day, hours, minutes, seconds, gmt)
	
	# function to translate tp_dcs octet meaning
	def tp_dcs_translate(self,tp_dcs):
	    tp_dcs_desc = tp_dcs
	    pom_dcs = hex_to_num(tp_dcs) 
	    
	    #check coding group at bit 7&6
	    if (pom_dcs & 192) == 0:	#General Data Coding indication
	        self.text_is_compressed = (pom_dcs & 32)
	        self.has_message_class_meaning = (pom_dcs & 16)
	        #alphabet, 0 = default, 4 = 8bit, 8 = 16bit/unicode, 12 = reserved
	        self.alphabet = (pom_dcs & 12)
	        if self.has_message_class_meaning:
	        	# 0 = class 0 (immediate display/alert) set to this value, for flash sms
	        	# 1 = class 1 (me specific)
	        	# 2 = class 2 (sim specific)
	        	# 3 = class 3 (te specific)
	        	self.message_class = (pom_dcs & 3)
	    elif (pom_dcs & 192) in (1,2):
	    	#reserved coding groups
	    	pass
	    elif (pom_dcs & 192) == 3:
	    	#message waiting indication group, test bit 5&4
	    	if (pom_dcs & 48) in (0,1,2):
	    			    		
	    return
	
	
	# Function to get SMSmeta info information from PDU String
	def parse(self,PDUString):
	    start = 0
	    #length of smsc_typeofaddress + smsc_number
	    smsc_length = hex_to_num(PDUString[0:2]) * 2
	    if smsc_length > 0:
	        self.smsc_typeofaddress = PDUString[2:4]
	        self.smsc_number = semioctet_to_string(PDUString[4:4+smsc_length-2]) # (-2) because length include smsc_typeofaddress
	        # if the length is odd remove the trailing  F
	        if self.smsc_number[-1] in ('F','f'):
	            self.smsc_number = self.smsc_number[:-1]
	    
	    #move start point to next unit (sms type)
	    start = smsc_length+2   #(+2) is for smsc_length itself
	    
	    #04 = SMS-DELIVER
	    self.sms_type = hex_to_num(PDUString[start:start+2])
	    
	    if (self.sms_type & 3) == 0: # bit 1 & 0 is 0
	        #sms type SMS-DELIVER
	        print "sms_type: SMS-DELIVER"
	        
	        #Reply path. Parameter indicating that reply path exists.
	        self.tp_rp = self.sms_type & 128	
	        #User data header indicator. This bit is set to 1 if the User Data field starts with a header
	        self.tp_udhi = self.sms_type & 64
	        #Status report indication. This bit is set to 1 if a status report is going to be returned to the SME
	        self.tp_sri = self.sms_type & 32
	        #More messages to send. This bit is set to 0 if there are more messages to send
	        self.tp_mms = self.sms_type & 4
	    
	        #move start point to next unit (sender number length)
	        start += 2
	        sender_length = hex_to_num(PDUString[start:start+2])
	        #must kelipatan 2 (round up)    
	        if sender_length % 2 != 0:
	            sender_length +=1
	        
	        #move start point to next unit (sender_typeofaddress)    
	        start += 2
	        self.sender_typeofaddress = PDUString[start:start+2]
	        
	        #move start point to next unit (sendernumber)        
	        start += 2
	        self.sender_number = semioctet_to_string(PDUString[start:start+sender_length])
	        
	        if self.sender_number[-1] in ('F','f'):
	            self.sender_number = self.sender_number[0:-1]
	            
	        #move start point to next unit (protocol identifier) 
	        start += sender_length
	        self.tp_pid = PDUString[start:start+2]
	        
	        #move start point to next unit (data coding scheme) 
	        start += 2
	        self.tp_dcs = PDUString[start:start+2]
	        tp_dcs_translate(self.tp_dcs)
	        
	        #move start point to next unit (sent time stamp) 
	        start += 2
	        self.sent_timestamp = semioctet_to_string(PDUString[start:start+14])	    
	        self.timestamp_decoded = decode_timestamp(self.sent_timestamp)
	        
	        #move start point to next unit (user data length) 
	        start += 14
	        self.tp_udl = hex_to_num(PDUString[start:start+2])
	        
	        #move start point to next unit (user data)     
	        start += 2        
	        self.userdata = convert8bit_to_7bit(PDUString[start:],self.tp_udl)    #user data is last, so just retrieve remaining data
	        out =  "SMSC # : "+self.smsc_number
	        out += "\nSender : "+self.sender_number
	        out += "\nTimeStamp : "+self.timestamp_decoded
	        out += "\nTP_PID:"+self.tp_pid
	        out += "\nTP_DCS:"+self.tp_dcs
	        out += "\nTP_DCS-popis:"
	        out += "\nMessage length : "+ str(self.tp_udl)
	        out += "\n\n"+self.userdata
	        
	    elif (self.sms_type & 3) == 1:
	        #SMS-SUBMIT
	        
	        #Reply path. Parameter indicating that reply path exists.
	        self.tp_rp = sms_type & 128	
	        
	        #User data header indicator. This bit is set to 1 if the User Data field starts with a header
	        self.tp_udhi = sms_type & 64
	        
	        #Status report request. This bit is set to 1 if a status report is requested
	        self.tp_srr = sms_type & 32
	        
	        #Validity Period Format
				#0: TP-VP field not present
				#	sms never expire
				#1: TP-VP field present. Enhanced format (7 octets)
				#2: TP-VP field present. Relative format (one octet)
				#	0 to 143 =  5 minutes intervals (0 - 12 hours)
				#	144 to 167 = 30 minutes intervals (12 - 24 hours)
				#	168 to 196 = 1 day intervals (2 - 30 days)
				#	197 to 255 = 1 week intervals (5 - 63 weeks)
				#3: TP-VP field present. Absolute format (7 octets)
				#	TP-VP field is 7 octets long, containing TP-SCTS formatted time when SM expires
	        self.tp_vpf = sms_type & 24
	
	        #Reject duplicates. Parameter indicating whether or not the SC shall accept an SMS-SUBMIT for an SM still held in the SC which has the same TP-MR and the same TP-DA as a previously submitted SM from the same OA.
	        self.tp_rd = sms_type & 4
	        
	        #move start point to next unit (tp message reference)
	        start += 2
	        self.tp_mr = hex_to_num(PDUString[start:start+2])   
	        
	        #move start point to next unit (address length)
	        start += 2
	        address_length = hex_to_num(PDUString[start:start+2])                             
	        #must kelipatan 2 (round up)    
	        if address_length % 2 != 0:
	            address_length +=1
	        
	        #move start point to next unit (type of address)
	        start += 2
	        self.typeofaddress = hex_to_num(PDUString[start:start+2])
	        
	        #move start point to next unit (phone number)
	        start += 2
	        self.phone_number = hex_to_num(PDUString[start:start+address_length])
	        
	        # if the length is odd remove the trailing  F
	        if self.phone_number[-1] in ('F','f'):
	            self.phone_number = self.phone_number[:-1]
	            
	        #move start point to next unit (protocol identifier) 
	        start += address_length
	        self.tp_pid = PDUString[start:start+2]
	        
	        #move start point to next unit (data coding scheme) 
	        start += 2
	        self.tp_dcs = PDUString[start:start+2]
	        tp_dcs_translate(self.tp_dcs)
	        
	        #move start point to next unit (can be tp_vp (validity period) or tp_user_data, depends on validity format) 
	        start += 2
	        if self.tp_vpf == 0:	#tp_vp not exist, so just skip it 
	        	pass
	        elif self.tp_vpf == 1:	#tp_vp enhanced format (reserved), just get it (7 octets/14bytes) although not understand the meaning
	        	self.tp_vp = PDUString[start:start+14]
		        #move start point to next unit (user data length) 
		        start += 14	        	
	        elif self.tp_vpf == 2:	#relative format (most of the time), get 1 octet (2 bytes)
	        	self.tp_vp = PDUString[start:start+2]
	        	if self.tp_vp <= 143:
	        		self.tp_vp_decoded = "%.2f %s" % ((self.tp_vp + 1) * 5 / 60.0, "minutes")
	        	elif self.tp_vp <= 167:
	        		self.tp_vp_decoded = "%.2f %s" % (12 + (self.tp_vp - 143) / 2.0, "hours")
	        	elif self.tp_vp <= 196:	
	        		self.tp_vp_decoded = "%d %s" % ((self.tp_vp - 166), "days")
	        	else:
	        		self.tp_vp_decoded = "%d %s" % ((self.tp_vp - 192), "weeks")
		        #move start point to next unit (user data length) 
		        start += 2
	        elif self.tp_vpf == 1:	#tp_vp absolute format, get 7 octet, timestamp format
	        	self.tp_vp = PDUString[start:start+14]
	        	self.tp_vp_decoded = decode_timestamp(self.tp_vp)
		        #move start point to next unit (user data length) 
		        start += 14

	        self.tp_udl = hex_to_num(PDUString[start:start+2])
	        
	        #move start point to next unit (user data)     
	        start += 2        
	        self.userdata = convert8bit_to_7bit(PDUString[start:],self.tp_udl)    #user data is last, so just retrieve remaining data	        
	        
	    return out

	def create(self):
		#create pdu based on object's property

if __name__ == '__main__':
    #pdustr = '07911326040000F0040B911346610089F60000208062917314080CC8F71D14969741F977FD07'
    pdustr = '059126181642040C912618958694810000309001618554828345A3D10582C540D322940A92C1603390B02865069755D0B2482D3AA9D5A013240C4AABA02A75090A0E87D26A90F9921689C526949A7C3A4147A3F1E52C5E41C5610BCAAC4E41CB22D50562169DC765100A425685A0A3D1081A169D5469F1350C3299A061D149951641301CEC16A3C96EB8990C'
    pdustr = '0021000B982618187124F0000030E437390F9AD7E5E930E89E5687F36117481D5E87E5F43028ED26BFDDE5793A0C9287F36110FC3DA783DE'
    Print getPDUMetaInfo(pdustr)
    