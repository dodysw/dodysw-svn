
from dclib import *
from dclist import *
from examples import *

__all__ = [
'DCBot','DCActionBot','DCClientGetList'
'SimplePickledObject',
'get_filesafe_name',
'iter_xml_list',
'uncompress_bz2',
'write_xml_list',
]

# ============================== UTILITIES =================================

try:
    import cPickle as pickle
except ImportError:
    import Pickle as pickle

class SimplePickledObject:
    """very simple permanent object saved into pickled file
    """
    _data = {}
    _auto_save = True
    filename = ''

    def __init__(self, name):
        self.filename = '%s.dat' % name
        if os.path.exists(self.filename):
            #~ self.log('Loaded', self.filename)
            self._data = pickle.load(file(self.filename,'rb'))

    def save(self):
        # save to temporary files first, if success, copy to target file, and if success, delete temporary files
        import shutil, os
        tempfilename = self.filename + '.tmp'
        fh = file(tempfilename,"wb")
        pickle.dump(self._data, fh)
        fh.close()
        shutil.copyfile(tempfilename, self.filename)
        os.unlink(tempfilename)


    def set(self, key, value=None):
        self._data[key] = value
        if self._auto_save:
            self.save()

    def get(self, key):
        return self._data[key]

    def remove(self, key):
        del self._data[key]
        if self._auto_save:
            self.save()

    def set_auto_save(self, b):
        self._auto_save = b

    def get_data(self):
        return self._data