#!/usr/bin/python
# pytreesize.py - simply tell the size of directories size recursively
"""
philosphy: to quickly see which directory occupies the largest amount, so
user can do something about it.

linux has du (disk usage) which do quite the same, but 1) windows has no such
utility, 2) ability to show only "interesting" folders by way of minimum
size parameter. this ability does not exist in du.
"""
__version = '0.2'
__author = 'Dody Suria Wijaya <dodysw@gmail.com>'
__name = 'pytreesize'
import sys, os, stat, time

# i'm using callable class (functor?) to practice avoiding cluttering global scope
class Main:
    result = []
    visited_dirs = 0

    def __call__(self, path, options):
        start = time.time()
        self.GetDirSize(path)
        self.result.sort()   #sort by its total
        end = time.time()
        for total,root, level in self.result:
            t = total/1048576.0
            if options.minsize > 0 and t < options.minsize:
                continue
            if options.maxlevel == 0 or level > options.maxlevel:
                continue
            print "%0.2f MB\t%s" % (t, root)
        print '='*40
        print 'Done visiting %s folders in %d seconds' % (self.visited_dirs, int(end-start))

    def GetDirSize(self, path, level=0):
        self.visited_dirs += 1
        total = 0
        for entry in os.listdir(path):
            filepath = os.path.join(path, entry)
            if os.path.isdir(filepath):
                total += self.GetDirSize(filepath, level=level+1)
            elif os.path.isfile(filepath):
                total += os.path.getsize(filepath)
        self.result.append([total, path, level])
        return total

if __name__ == '__main__':
    import optparse
    usage = 'Usage: %s [options] <path>' % sys.argv[0]
    parser = optparse.OptionParser(usage)
    parser.add_option("-s", "--minsize", type="float", dest="minsize", help="minimum directory size in MB to display. Default=1 MB. Give 0 for no minimum.", default=1)
    parser.add_option("-l", "--maxlevel", type="int", dest="maxlevel", help="maximum level of directory to display (calculation will still be done on all level). Default=2 levels. Give 0 for no minimum.", default=2)
    parser.add_option("-v", "--version", action="store_true", dest="version", help="display version and return", default=False)
    options, args = parser.parse_args()
    if options.version:
        print __name, __version
    elif len(args) < 1:
        parser.error("incorrect number of arguments")
    else:
        Main()(args[0], options)