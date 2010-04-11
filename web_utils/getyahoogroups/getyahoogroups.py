import re, os, sys, os.path



def main(path):
    reprog = re.compile('([a-zA-Z0-9\.\-]*@yahoogroups\.com)')
    filter1 = re.compile('\.m\d+@')
    filter1b = re.compile('\.w\d+@')
    filter2 = re.compile('^\-')
    filter3 = re.compile('^confirm\-')
    test1 = re.compile('^3d')
    for root, dirs, files in os.walk(path):
        results = {}
        for filename in files:
            results_per_file = {}
            if '.tbb' not in filename.lower(): continue
            filepath = os.path.join(root,filename)
            print 'Parsing', filepath,' (%s MB) ...' % (float(os.path.getsize(os.path.join(root,filename)))/float(1024*1024)),
            #~ buffer = file(filepath,'rb').read() # load file into memory
            for line in file(filepath,'rb'):
                m = reprog.findall(line)
                if not m: continue
                filtered = []
                for i,e in enumerate(m):
                    if filter1.search(e) or filter1b.search(e) or filter2.search(e) or filter3.search(e): continue
                    e = e.lower()
                    e = e.replace('-subscribe@','@')
                    e = e.replace('-unsubscribe@','@')
                    e = e.replace('-owner@','@')
                    e = e.replace('-digest@','@')
                    e = e.replace('-nomail@','@')
                    if test1.search(e): e = e + ' -> ' + line
                    filtered.append(e)
                results_per_file.update(dict.fromkeys(filtered))
            #~ if __debug__: print >> sys.stderr, filepath,': got ', len(resd), 'total', len(results)
            results.update(results_per_file)
            print ': got ', len(results_per_file), 'total', len(results)

        for email in results:
            print email

if __name__ == '__main__':
    path = '.'
    if len(sys.argv) > 1:
        path = sys.argv[1]

    main(path)
