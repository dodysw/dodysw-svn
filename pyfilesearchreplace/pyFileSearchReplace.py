import glob,fileinput,sys,os

def filesearchreplace(search,changeto,path,commit=False,recursive=False,regex=False):
    # get list of files to search
    if recursive:
        filenames = []
        just_path,pattern = os.path.split(path)
        import fnmatch
        for root, dirs, files in os.walk(just_path):
            filenames += [os.path.join(root,n) for n in files if fnmatch.fnmatch(n, pattern)]
    else:
        # if pattern is empty, (c:/tmp/media/) path is directory
        just_path,pattern = os.path.split(path)
        if pattern == '': pattern = '*'                 # assume all files in directory
        path = os.path.join(just_path,pattern)
        filenames = glob.glob(path)                    # glob is non recursive
        filenames = filter(os.path.isfile, filenames)  # glob takes also directory

    stat_lines, stat_files = 0,0

    if regex:
        import re
        re_obj = re.compile(search)
        def match(line,search):
            return bool(re_obj.search(line))
        def replace(line,search,changeto):
            return re_obj.sub(changeto,line)
    else:
        def match(line,search):
            return bool(search in line)
        def replace(line,search,changeto):
            return line.replace(search,changeto)

    if not commit:
        print
        print 'I will simulate replace\n"%s"\nwith:\n"%s"\nin: %s' % (search,changeto,path)
        print 'If you add -y, these files will be replaced at given lines:'
        print
        for filename in filenames:
            output = []
            for i,line in enumerate(file(filename)):
                #~ if search in line:
                if match(line,search):
                    stat_lines += 1
                    #~ newline = line.replace(search,changeto)
                    newline = replace(line,search,changeto)
                    output.append('%s: %s -> %s' % (i+1,line.strip(),newline.strip()))
            if output:
                stat_files += 1
                print '=== %s - %s line(s) ===' % (filename, len(output))
                print '\n'.join(output)
    else:
        for filename in filenames:
            matched = False
            # problem: fileinput write to file whether there is match or not
            #~ for line in fileinput.input(filename,inplace=1):
                #~ if search in line:
                    #~ matched = True
                    #~ stat_lines += 1
                    #~ line = line.replace(search,changeto)
                #~ print >> sys.stdout, line,
            buffer = []
            for line in file(filename):
                #~ if search in line:
                if match(line,search):
                    matched = True
                    stat_lines += 1
                    #~ line = line.replace(search,changeto)
                    line = replace(line,search,changeto)
                buffer.append(line)
            if matched:
                file(filename,'w').writelines(buffer)
                stat_files += 1

    print '_'*40
    print 'Total %s lines in %s files. scanned %s files.' % (stat_lines, stat_files, len(filenames))
    return stat_lines, stat_files

if __name__ == '__main__':
    import optparse
    class OptionParser (optparse.OptionParser): # subclass to provide requirement checking
        def check_required (self, opt):
            option = self.get_option(opt)
            # Assumes the option's 'default' is set to None!
            if getattr(self.values, option.dest) is None:
                self.error("%s option not supplied" % option)
    op = OptionParser()
    op.add_option('-s', '--search', type='string', dest='search', help='string to match for')
    op.add_option('-e', '--replace', type='string', dest='changeto', help='replace matched string with this. if not given, replace with empty string.', default='')
    op.add_option('-p', '--path', type='string', dest='path', help='path(s) and file pattern to match (support * and ?)',default=os.getcwd())
    op.add_option('-y', action='store_true', dest='commit', help='really replace matched files',default=False)
    op.add_option('-r', action='store_true', dest='recursive', help='match directory recursively',default=False)
    op.add_option('-x', action='store_true', dest='regex', help='match using regular expression',default=False)
    options, args = op.parse_args()
    op.check_required("-s")
    #~ op.check_required("-e")


    #~ search = '        row1,row2,rowversion = Update(sid,rowid, row0, row1, row2, rowversion)'
    #~ changeto = '        row1,row2,rowversion = Update(sid,rowid, row0, row1, row2)'
    filesearchreplace(options.search,options.changeto,options.path,commit=options.commit,recursive=options.recursive,regex=options.regex)
    #~ if raw_input('Press Y to continue replacing: ').upper() != 'Y':
        #~ sys.exit()
    #~ filesearchreplace(search,changeto,path,commit=True)