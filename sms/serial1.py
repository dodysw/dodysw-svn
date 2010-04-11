from uspp import *
tty = SerialPort("COM5",1000,9600)
tty.write("AT\r\n")
print tty.read(tty.inWaiting())