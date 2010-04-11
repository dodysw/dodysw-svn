
if __name__ == '__main__':
    import sys
    if sys.platform.startswith('win'):
        DEF_SOURCEDIRS = 'C:/My Documents/Attachments'
        DEF_TARGET = 'G:/Dupes'
    else:
        DEF_SOURCEDIRS = '/media/BlackRitmo/Program Files/OTH2/Attachments/'
        DEF_TARGET = '/media/BlackRitmo/Dupes'

    import pydedupe
    class dummy:
        pass
    options = dummy()
    options.minfilesize = 0
    options.move = True
    sourcedirs = []
    sourcedirs.append(raw_input("Dir to dedupe [%s]:" % DEF_SOURCEDIRS) or DEF_SOURCEDIRS)
    options.target_dir = raw_input("Move dupes to [%s]:" % DEF_TARGET) or DEF_TARGET
    pydedupe.main(sourcedirs, options)
