#!/usr/bin/python

"""
Po2Transtool.py = .po file and Transtool cooperation

This script can do two thing:
1. parse a .po file, then output a version which can be easily feed to Transtool
2. parse the output file from Transtool, then comparing the original .po, output the merged result .po

Note: Transtool is popular utility that can translate between Bahasa Indonesia to English, and back
"""
__version__ = '0.1'
author = 'Dody Suria Wijaya <dodysw@gmail.com>'

import sys

def parse(filename):
    data = []
    id,value,refs,comment,comment0,fuzzy = '','','','','',''
    for line in file(filename):
        if line.startswith('#:'):
            refs += line[3:]
        elif line.startswith('#.'):
            comment += line[3:]
        elif line.startswith('#,'):
            fuzzy = line[3:]
        elif line.startswith('# '):
            comment0 += line[2:]
        elif line.startswith('msgid'):
            id = line[7:-2]
        elif line.startswith('msgstr'):
            value = line[8:-2]
        elif line == "\n":
            data.append(dict(id=id,value=value,comment=comment,comment0=comment0,refs=refs,fuzzy=fuzzy))
            id,value,refs,comment,comment0,fuzzy = '','','','','',''
    return data

import re
rdef = re.compile(r'Default: "([^\"]*)"')
def ToTranstool(data):
    for i,row in enumerate(data):
        m = rdef.search(row['comment'])
        if m:
            print "%d. %s" % (i,m.group(1))
        else:
            print "%d. %s" % (i,row['id'])

def parse_transtool(filename):
    """
0. ${itemCount} materi telah dihapus.

1. ${monthname} ${tahun}
    """
    data = {}
    id,value,comment,refs = '','','',''
    for line in file(filename):
        if line == "\n" or '. ' not in line:
            continue
        i,value = line.split('. ',1)
        data[int(i)] = value
    return data

def ToPo(data,datatrans):
    for i,row in enumerate(data):
        if i in datatrans:
            row['valuetrans'] = datatrans[i]
        else:
            row['valuetrans'] = ''

    for i,row in enumerate(data):

        if row['comment0'].strip() != '':
            for comment in row['comment0'].strip().split('\n'):
                print '# %s' % comment.strip()


        if row['comment'].strip() != '':
            for comment in row['comment'].strip().split('\n'):
                print '#. %s' % comment.strip()

        if row['refs'].strip() != '':
            for ref in row['refs'].strip().split('\n'):
                print '#: %s' % ref.strip()

        if row['fuzzy']:
            print '#, %s' % row['fuzzy'].strip()
        print 'msgid "%s"' % row['id'].strip()

        if row['value'].strip() != '':
            print 'msgstr "%s"' % row['value'].strip()
        else:
            print 'msgstr "%s"' % row['valuetrans'].strip()
        print

if __name__ == '__main__':
    import optparse
    usage = '\nfrom po to transtool:\n  %s -t <po file>\n' % sys.argv[0]
    usage += 'from transtool to po:\n  %s -p <orig po file> <transtoolfile_result>' % sys.argv[0]
    parser = optparse.OptionParser(usage)
    parser.add_option("-t", action="store_true", dest="toTranstool", help="convert from .po to Transtool", default=False)
    parser.add_option("-p", action="store_true", dest="toPo", help="convert from Transtool back to .po", default=False)
    parser.add_option("-v", "--version", action="store_true", dest="version", help="display version and return", default=False)
    options, args = parser.parse_args()

    if options.version:
        print __version__
        sys.exit()
    elif not options.toTranstool and not options.toPo:
        parser.error("please provide the options")
    elif (options.toTranstool and len(args) < 1) or (options.toPo and len(args) < 2):
        parser.error("incorrect number of arguments")
        sys.exit()

    if options.toTranstool:
        data = parse(args[0])
        ToTranstool(data)
    elif options.toPo:
        data = parse(args[0])
        datatrans = parse_transtool(args[1])
        ToPo(data,datatrans)
