import sys, zipfile, os

def prunedumpgfxzip(filename, to):
    fh_source = zipfile.ZipFile(filename, 'r')
    target_path = os.path.join(to, os.path.basename(filename))
    if os.path.exists(target_path):
        sys.exit("File [%s] already exists" % target_path)
    fh_target = zipfile.ZipFile(target_path, 'w', zipfile.ZIP_DEFLATED)
    for entry in fh_source.namelist():
        if '-c' in entry.lower() or '_c' in entry.lower():
            fh_target.writestr(entry, "")
        else:
            fh_target.writestr(entry, fh_source.read(entry))

    fh_target.close()
    fh_source.close()


if __name__ == '__main__':
    if len(sys.argv) > 2:
        prunedumpgfxzip(sys.argv[1], sys.argv[2])
    else:
        print "need 2 parameters"

# open file