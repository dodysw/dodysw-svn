"""
combine multitple php files include into one
dody suria wijaya <dodysw@gmail.com>
"""

__version__ = '1.1.0'

import sys, os, re, cStringIO

php_start_tag = '<?'
include_start_comment = False
include_end_comment = False
strip_all_comment = True
strip_new_lines = True
strip_unneeded_php_tag = True
replace_4space_with = '\t'  #False, '', 'tab',

def combine(inputfile): # recursive calls
    if not os.path.exists(inputfile):
        print 'File "%s"does not exist' % inputfile
        sys.exit()
    r = re.compile(r"^(\s*)require_once\(('|\")(.*?)\2\);");
    for line in file(inputfile,'r'):
        m = r.search(line)
        if m == None:
            print line,
            continue
        include_file = m.group(3)
        include_file = os.path.join(os.path.dirname(inputfile),include_file)
        if include_start_comment:
            print '/** %s start file %s %s */%s' % ('-'*10,include_file, '-'*10, '?>')
        else:
            print '?>'

        combine(include_file)
        if include_end_comment:
            print '%s\n/** %s end file %s %s */' % (php_start_tag, '-'*10,include_file, '-'*10)
        else:
            print php_start_tag

if __name__ == '__main__':
    import optparse
    parser = optparse.OptionParser(version="%%prog %s" % __version__)
    parser.add_option("-i", "--input", dest="input", help="Seed file input")
    options, args = parser.parse_args()
    if not options.input:
        print 'You must provide the seed file input'
        sys.exit()
    # redirect output
    newio = cStringIO.StringIO()
    sys.stdout = newio
    combine(options.input)
    sys.stdout = sys.__stdout__
    buffer = newio.getvalue()
    newio.close()

    if strip_unneeded_php_tag:
        buffer = re.sub(r'\?>\s*%s' % re.escape(php_start_tag), '',buffer)

    # strip comments
    if strip_all_comment:
        buffer = re.sub(r'(?m)^\s*//.*$','',buffer)
        buffer = re.sub(r'(?m)(?<=;)\s*//.*$','',buffer)
        buffer = re.sub(r'(?m)(?<=;)\s*#.*$','',buffer)
        buffer = re.sub(r'(?s)/\*(?!\*).*?\*/','',buffer)
        buffer = re.sub(r'(?m)^[ \t]+#.*$','',buffer)      # since ^# could be CSS, we only enable this for # which prefixed with whitespace(s)

    # strip new lines
    if strip_new_lines:
        buffer = re.sub(r'(?m)\s*$',r'',buffer)
        buffer = re.sub(r'\n{2,}',r'\n',buffer)

    if replace_4space_with != False:
        buffer = buffer.replace('    ',replace_4space_with)

    print buffer