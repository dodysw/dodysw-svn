"""
    changeindent.py: change space indentation of python file
    copyright september, 2004. dody suria wijaya
"""

import sys, re, glob

def reindent(filename,current_indent,target_indent):
    reg = re.compile(r'^([\ \t]+)')
    lines = file(filename,'r').readlines()
    for i,line in enumerate(lines):
        res = re.match(reg,line)
        if res:
            if res.group(1)[0] == '\t':
                new_indent = len(res.group(1)) * target_indent
            else:
                new_indent = (len(res.group(1)) / current_indent) * target_indent
            lines[i] = ' ' * new_indent + line.lstrip()
    file(filename,'w').writelines(lines)

def analyzeindent(filename):
    # analyze type ('tab','space'), and if space, the number of space
    space_stat = {}
    indent_type = ''
    indent_stat = {'tab':0, 'space':0}
    reg = re.compile(r'^([\ \t]+)')
    lines = file(filename,'r').readlines()
    for i,line in enumerate(lines):
        res = re.match(reg,line)
        if res:
            indent = res.group(1)
            if indent[0] == ' ':
                indent_stat['space'] += 1
                space_stat.setdefault(len(indent),0)
                space_stat[len(indent)] += 1
            elif indent[0] == '\t':
                indent_stat['tab'] += 1
    predict_spacenum = 0
    if space_stat:
        predict_spacenum = min(space_stat.keys())
    indent_type = 'tab'
    if indent_stat['space'] > indent_stat['tab']:
        indent_type = 'space'
    return indent_type, predict_spacenum

def main():
    filename = sys.argv[1]
    target_indent = 4
    try: target_indent = int(sys.argv[2])
    except: pass
    for f in glob.glob(filename):
        print f,
        indent_type, current_indent = analyzeindent(f)
        print 'currently:%s - type:%s' % (current_indent,indent_type),
        if current_indent and current_indent != target_indent:
            reindent(f,current_indent, target_indent)
            print '[fix to %s]' % target_indent
        else:
            print '[skipped]'

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print 'Usage: ' + sys.argv[0] + ' <filename(s)> [target_indentation]'
        sys.exit()
    main()