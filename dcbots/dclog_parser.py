# dc++ log parser - by bnn :D
# dclog_parser.py "c:\Program Files\DC++\Logs\150*.log" "c:\Program Files\DC++\Logs\*cx.log"

import sys, re, glob

logline = re.compile("\[\d\d\d\d\-\d\d\-\d\d \d\d:\d\d\] (?:<([^>]+)>|\* (\w+)) (.*?)(?=\n\[)", re.M | re.S)
exclude_nicks = ('VerliHub', 'MOTD', 'Ass\xc2\xa0Kicking\xc2\xa0Bouncer')
def main(filenames):

    top_talkative = {}

    for filename in filenames:
        print >> sys.stderr, "Parsing", filename
        buffer = file(filename).read()
        for m in logline.findall(buffer):
            nick = (m[0] or m[1])
            if nick in exclude_nicks or nick.startswith('-'):
                continue
            key = nick.lower()
            top_talkative.setdefault(key,[nick, 0])
            top_talkative[key][1] += len(m[2].strip())

    ls = [(o[1],k) for k,o in top_talkative.items()]
    ls.sort(reverse=True)
    for i, (score, k) in enumerate(ls):
        print "%d.%s(%d) " % (i+1, top_talkative[k][0], score),
if __name__ == "__main__":
    filenames = []
    for path in sys.argv[1:]:
        filenames += glob.glob(path)
    main(filenames)