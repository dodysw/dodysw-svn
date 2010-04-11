##########################################################################
# USPP Library (Universal Serial Port Python Library)
#
# Copyright (C) 2001 Isaac Barona <ibarona@tid.es>
# 
# This library is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; version 2 dated
# June, 1991.
# 
# This library is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# Library General Public License for more details.
# 
# You should have received a copy of the GNU General Public
# License along with this library; if not, write to the
# Free Software Foundation, Inc., 59 Temple Place - Suite 330,
# Boston, MA 02111-1307, USA.
##########################################################################

#-------------------------------------------------------------------------
# Project:   USPP Library (Universal Serial Port Python Library)
# Name:      SerialPort_linux.py
# Purpose:   Handle low level access to serial port in linux.
#
# Author:    Isaac Barona Martínez <ibarona@tid.es>
# Copyright: (c) 2001 by Isaac Barona Martínez
# Licence:   GPL
#
# Created:   26 June 2001
# History:
#
#-------------------------------------------------------------------------

"""
SerialPort_linux.py - Handle low level access to serial port in linux.

See also uspp module docstring.


"""

import os
from termios import *
import TERMIOS
import fcntl
import exceptions

class SerialPortException(exceptions.Exception):
    """Exception raise in the SerialPort methods"""
    def __init__(self, args=None):
        self.args=args


class SerialPort:
    """Encapsulate methods for accesing to a serial port."""

    BaudRatesDic={
        110: TERMIOS.B110,
        300: TERMIOS.B300,
        600: TERMIOS.B600,
        1200: TERMIOS.B1200,
        2400: TERMIOS.B2400,
        4800: TERMIOS.B4800, 
        9600: TERMIOS.B9600,
        19200: TERMIOS.B19200,
        38400: TERMIOS.B38400,
        57600: TERMIOS.B57600,
        115200: TERMIOS.B115200
        }

    def __init__(self, dev, timeout=None, speed=None, mode='232', params=None):
        """Open the serial port named by the string 'dev'

        'dev' can be any of the following strings: '/dev/ttyS0', '/dev/ttyS1',
        ..., '/dev/ttySX' or '/dev/cua0', '/dev/cua1', ..., '/dev/cuaX'.
        
        'timeout' specifies the inter-byte timeout or first byte timeout
        (in miliseconds) for all subsequent reads on SerialPort.
        If we specify None time-outs are not used for reading operations
        (blocking reading).
        If 'timeout' is 0 then reading operations are non-blocking. It
        specifies that the reading operation is to return inmediately
        with the bytes that have already been received, even if
        no bytes have been received.
        
        'speed' is an integer that specifies the input and output baud rate to
        use. Possible values are: 110, 300, 600, 1200, 2400, 4800, 9600,
        19200, 38400, 57600 and 115200.
        If None a default speed of 9600 bps is selected.
        
        'mode' specifies if we are using RS-232 or RS-485. The RS-485 mode
        is half duplex and use the RTS signal to indicate the
        direction of the communication (transmit or recive).
        Default to RS232 mode (at moment, only the RS-232 mode is
        implemented).

        'params' is a list that specifies properties of the serial 
        communication.
        If params=None it uses default values for the number of bits
        per byte (8), the parity (NOPARITY) and the number of stop bits (1)
        else params is the termios package mode array to use for 
        initialization.

        """
        self.__devName, self.__timeout, self.__speed=dev, timeout, speed
        self.__mode=mode
        self.__params=params
        try:
	    self.__handle=os.open(dev, os.O_RDWR)
        except:
            raise SerialPortException('Unable to open port')

        self.__configure()

    def __del__(self):
        """Close the serial port and restore its initial configuration
        
        To close the serial port we have to do explicity: del s
        (where s is an instance of SerialPort)
        """
	
    	tcsetattr(self.__handle, TERMIOS.TCSANOW, self.__oldmode)
	
        try:
            os.close(self.__handle)
        except IOError:
            raise SerialPortException('Unable to close port')


    def __configure(self):
        """Configure the serial port.

        Private method called in the class constructor that configure the 
        serial port with the characteristics given in the constructor.
        """
        if not self.__speed:
            self.__speed=9600
        
        # Save the initial port configuration
        self.__oldmode=tcgetattr(self.__handle)
        if not self.__params:
            # self.__params is a list of attributes of the file descriptor
            # self.__handle as follows:
            # [c_iflag, c_oflag, c_cflag, c_lflag, c_ispeed, c_ospeed, cc]
            # where cc is a list of the tty special characters.
            self.__params=[]
            # c_iflag
            self.__params.append(TERMIOS.IGNPAR)           
            # c_oflag
            self.__params.append(0)                
            # c_cflag
            self.__params.append(TERMIOS.CS8|TERMIOS.CLOCAL|TERMIOS.CREAD) 
            # c_lflag
            self.__params.append(0)                
            # c_ispeed
            self.__params.append(SerialPort.BaudRatesDic[self.__speed]) 
            # c_ospeed
            self.__params.append(SerialPort.BaudRatesDic[self.__speed]) 
            # XXX FIX: Theorically, it should be better to put:
            # cc=[0]*TERMIOS.NCCS 
            # but it doesn't work because NCCS is 19 and self.__oldmode[6]
            # is 32 ¿¿¿¿¿¿¿¿¿¿¿ Any help ??????????????
            cc=[0]*len(self.__oldmode[6])
            if self.__timeout==None:
                # A reading is only complete when VMIN characters have
                # been received (blocking reading)
                cc[TERMIOS.VMIN]=1
                cc[TERMIOS.VTIME]=0
            elif self.__timeout==0:
                # Non-blocking reading. The reading operation returns
                # inmeditately, returning the characters waiting to 
                # be read.
                cc[TERMIOS.VMIN]=0
                cc[TERMIOS.VTIME]=0
            else:
                # Time-out reading. For a reading to be correct
                # a character must be recieved in VTIME*100 seconds.
                cc[TERMIOS.VMIN]=0
                cc[TERMIOS.VTIME]=self.__timeout/100
            self.__params.append(cc)               # c_cc
        
        tcsetattr(self.__handle, TERMIOS.TCSANOW, self.__params)
    

    def fileno(self):
        """Return the file descriptor for opened device.

        This information can be used for example with the 
        select funcion.
        """
        return self.__handle


    def __read1(self):
        """Read 1 byte from the serial port.

        Generate an exception if no byte is read and self.timeout!=0 
        because a timeout has expired.
        """
        byte = os.read(self.__handle, 1)
        if len(byte)==0 and self.__timeout!=0: # Time-out
            raise SerialPortException('Timeout')
        else:
            return byte
            

    def read(self, num=1):
        """Read num bytes from the serial port.

        Uses the private method __read1 to read num bytes. If an exception
        is generated in any of the calls to __read1 the exception is reraised.
        """
        s=''
        for i in range(num):
            s=s+SerialPort.__read1(self)
        
        return s
            
        
    def write(self, s):
        """Write the string s to the serial port"""

        os.write(self.__handle, s)

        
    def inWaiting(self):
        """Returns the number of bytes waiting to be read"""
        # XXX FIX: This method doesn't work for me. I get the 
        # following:
        #    >> tty.inWaiting()
        #    Traceback (most recent call last):
        #      File "<stdin>", line 1, in ?
        #      File "SerialPort_linux.py", line 181, in inWaiting
        #        n=fcntl.ioctl(self.__handle, TERMIOS.TIOCINQ)
        #    IOError: [Errno 14] Bad address
        #
        # Any ideas ?????????????
        n=fcntl.ioctl(self.__handle, TERMIOS.TIOCINQ)
        return n

    def flush(self):
        """Discards all bytes from the output or input buffer"""
        tcflush(self.__handle, TERMIOS.TCIOFLUSH)

    def __rts_on():
        """XXX To be implemented with the RS-485 mode."""
        pass

    def __rts_of():
        """XXX To be implemented with the RS-485 mode."""
        pass

        

