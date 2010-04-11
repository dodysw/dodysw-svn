import os, EXIF

def do(path):
    for root, dirs, files in os.walk(path):
        for filename in files:
            if not '.jpg' in filename.lower():
                continue
            try:
                e = EXIF.process_file(open(os.path.join(root, filename),"rb"))
                img_number = str(e['MakerNote ImageNumber'])
                real_img_number = 10000*(int(img_number[0])-1) + int(img_number[3:])
                print img_number, "\t", e['Image DateTime'], '\t', os.path.join(root, filename), '\t', real_img_number, '\t', e['Image Model']
            except:
                pass
                #~ print "n/a", "\t", os.path.join(root, filename)
do("I:/")
do("c:/My Pictures")
do("c:/My Pictures (Pre Inco)")
do("c:/My Pictures Personal")