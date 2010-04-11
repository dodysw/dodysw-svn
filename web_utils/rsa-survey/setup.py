myscript = "sche.py"


from distutils.core import setup
import py2exe,sys

# If run without args, build executables, in quiet mode.
if len(sys.argv) == 1:
    sys.argv.append("py2exe")
    sys.argv.append("-q")

prog1 = dict(
    script = myscript,
)

setup(
    #~ options = dict(py2exe=dict(compressed=1,optimize=2,packages='encodings')),
    options = dict(py2exe=dict(compressed=1,optimize=2)),
    zipfile = "lib/pylib.dat",
    console = [prog1],
    )
