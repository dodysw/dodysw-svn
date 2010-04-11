from distutils.core import setup
import py2exe,sys

# If run without args, build executables, in quiet mode.
if len(sys.argv) == 1:
    sys.argv.append("py2exe")
    sys.argv.append("-q")

sys.path += ['../src']

import cs_player as theprog
version = theprog.__version__
description = theprog.__description__

prog1 = dict(
    version = version,
    description = description,
    script = "cs_player.py",
    icon_resources = [(1, "game.ico")],
    )

service1 = dict(
    version = version,
    description = description,
    #~ modules = ["popoksvc"],
    #~ icon_resources = [(1, "../distutils/popok.ico")],
    )



opts = dict(
    py2exe = dict(
        compressed=1,
        optimize=2,
        packages='encodings',
        dist_dir = "win32",
        dll_excludes = ['unicodedata.pyd', '_ssl.pyd'],
        excludes = ['unicodedata', '_ssl'],
        ),
    )

setup(
    options = opts,
    zipfile = "lib/plib.dll",
    windows = [prog1],
    #~ service = [service1],
    )
