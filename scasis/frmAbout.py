#!/usr/bin/env python2
import gtk
version = "Scasis $Revision: 1.6 $"
copyright = "Copyright (C) 2005\nDody Suria Wijaya\nTeddy Mantoro"
about = """
<b>%(copyright)s</b>

ScaSIS is an implementation of
intelligent environment studies by
Teddy Mantoro &lt;teddy.mantoro@anu.edu.au&gt;
from Computer Science Department
Australian National University

This software is based on "Winzig",
the work of Robert Muth &lt;robert@muth.org&gt;

Please email suggestions to:
&lt;dodysw@gmail.com&gt;
<b>%(version)s</b>
""" % dict(copyright=copyright,version=version)

def CreateWidget(tooltips):
    vbox = gtk.VBox()
    vbox.show()
    label = gtk.Label("")
    label.set_markup(about)
    label.show()
    vbox.pack_start(label)
    return vbox

if __name__ == '__main__':
    window = gtk.Window()
    def destroy(args):
        window.destroy()
        gtk.main_quit()
    window.connect("destroy", destroy)
    window.set_title('ABOUT')
    tooltips = gtk.Tooltips()
    widget = CreateWidget(tooltips)
    window.add(widget)
    window.show()
    gtk.main()