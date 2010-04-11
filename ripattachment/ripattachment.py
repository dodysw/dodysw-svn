import glob, os.path
import email, email.Iterators, email.Errors
path_source = 'test/'
path_source = raw_input('Path source:')
path_result = 'result/'
try: os.mkdir(path_result)
except: pass

for filename in glob.glob(path_source+'*.msg') + glob.glob(path_source+'*.eml'):
    print 'Parsing',filename
    buffer = file(filename).read()
    buffer += '\r\n'    # make sure end with emptyline (email parser tripped at this)
    try:
        msg = email.message_from_string(buffer)
    except email.Errors.BoundaryError:
        print '--boundary error--'
        continue
    for part in msg.walk():
        #~ if part.get_content_maintype() == 'image':
        #~ if 1:
        if part.get_content_maintype() != 'multipart':
            print '->', part.get_content_type()
            buffer = part.get_payload(decode=True)
            if buffer is None:
                print '--empty buffer--'
                continue
            part_filename = (part.get_filename() or 'none.'+part.get_content_subtype())
            for char in r'?/\:*"<>|':
                part_filename = part_filename.replace(char,'') # remove invalid characters
            if '.' not in part_filename: part_filename += '.'   #make sure . is in filename
            i = 1
            orig_part_filename = part_filename
            while os.path.exists(path_result+part_filename):
                #~ print part_filename, 'already exist',
                l,r = orig_part_filename[0:orig_part_filename.rindex('.')], orig_part_filename[orig_part_filename.rindex('.')+1:]
                part_filename = '%s-%s.%s' % (l, i, r)
                #~ print 'trying',part_filename
                i += 1
            print 'Saving', part_filename
            file(path_result+part_filename,'wb').write(buffer)
