import sys,re
if __name__ == '__main__':
    emails = {}
    reobj = re.compile("[a-z0-9\.]+@yahoogroups\.com") # check something@yahoogroups.com        
    for line in open(sys.argv[1]): 
        for m in reobj.finditer(line):
            emails[m.group(0).strip()] = 1
            # print m.group(0)
    open("hasil.txt","w").writelines(emails.keys())