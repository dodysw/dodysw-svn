from distutils.core import setup
import py2exe,sys

# If run without args, build executables, in quiet mode.
if len(sys.argv) == 1:
    sys.argv.append("py2exe")
    sys.argv.append("-q")

prog1 = dict(
    version = "0.10.0",
    description = "Popok - webmail to smtp/pop3 gateway",
    script = "popok.py",
    #~ icon_resources = [(1, "popok.ico")],
    icon_resources = [(1, "test.ico")],
    dist_dir = 'popok-0.10.0'
)

service1 = dict(
    version = "0.10.0",
    description = "Popok - webmail to smtp/pop3 gateway",
    modules = ["popoksvc"],
    icon_resources = [(1, "popok.ico")],
    )


setup(
    options = dict(py2exe=dict(compressed=1,optimize=2,packages='encodings')),
    #options = dict(py2exe=dict(optimize=2)),
    zipfile = "lib/pylib.dat",
    console = [prog1],
    service = [service1],
    )
