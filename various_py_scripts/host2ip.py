import sys, socket

if __name__ == '__main__':
    ips = {}
    for line in file(sys.argv[1]):
        website = line.strip()
        print socket.gethostbyname(website)
        ips[socket.gethostbyname(website)] = 1
    for ip in ips.keys():
        print ip