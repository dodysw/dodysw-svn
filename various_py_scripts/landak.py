"""
Landak
a tool to periodically download and archive a changing URL file
by Dody Suria Wijaya <dodysw@gmail.com>
August 2008
"""
import urllib as urllib
import os, datetime, time

class Landak:

    PREVFILE = "prevfile.dat"
    TARGET_PREFIX = "web"
    target_path = ""

    def do(self, url_file, file_suffix = 'result.txt'):

        #download image
        try:
            buffer = urllib.urlopen(url_file).read()
            now = datetime.datetime.now()
        except IOError:
            print "IO Error, Can't get URL ", url_file
            return False
        except:
            print "Other problems, Can't get URL ", url_file
            return False

        #compare with previous file (prevfile.dat)
        try:
            fh = file(self.PREVFILE,"rb")
            buff_prevfile = fh.read()
            # if the same, ignore. if different save at yearmonthdate/hourminutesecond.gif
            if buffer == buff_prevfile:
                print "%s: Same file, ignoring" % url_file
                try:
                    self.target_path = file("%s.url.txt" % self.PREVFILE).read()
                except:
                    pass
                return False
            print "%s: Found new file" % url_file
        except IOError:
            pass

        # save it
        target_filename = "%s-%s" % (now.strftime("%Y-%m-%d %H%M%S"), file_suffix)
        target_folder = now.strftime("%Y-%m-%d")
        try:
            os.makedirs(os.path.join(self.TARGET_PREFIX, target_folder))
        except:
            pass

        target_path = os.path.join(self.TARGET_PREFIX, target_folder, target_filename)
        print "Saving to", target_path
        file(target_path, "wb").write(buffer)
        #save also to prevfile.dat
        file(self.PREVFILE,"wb").write(buffer)
        #save also last file path
        file("%s.url.txt" % self.PREVFILE,"w").write(target_path)

        self.target_path = target_path
        return True

if __name__ == "__main__":
    DOWNLOAD_PERIOD = 60
    import sys
    if len(sys.argv) == 0:
        print "format: %s url [file suffix]"
        sys.exit()
    url_file = sys.argv[1]
    if len(sys.argv) > 2:
        file_suffix = sys.argv[2]

    while 1:
        img = Landak()
        img.do(url_file, file_suffix)
        time.sleep(DOWNLOAD_PERIOD)