#!/usr/bin/env python2
import sys
import re
import gtk

def main():

    geometry_x = -1
    geometry_y = -1
    geometry_w = -1
    geometry_h = -1

    try:
        index = sys.argv.index("-geometry")
        gstring = sys.argv[index+1]
        del sys.argv[index:index+1]
        match = re.compile(r"^(?:(?P<w>[0-9]+)[xX](?P<h>[0-9]+))?" + r"(?:(?P<x>[\+\-][0-9]+)(?P<y>[\+\-][0-9]+))?$").match( gstring )
        if match:

            try:
                geometry_w = int(match.group("w"))
            except:
                geometry_w = -1
            try:
                geometry_h = int(match.group("h"))
            except:
                geometry_h = -1

            try:
                geometry_x = match.group("x")
                if geometry_x[0] == '-':
                    geometry_x = gtk.screen_width() + int(geometry_x)
                else:
                    geometry_x = int(geometry_x)
            except:
                geometry_x = -1

            try:
                geometry_y = match.group("y")
                if geometry_y[0] == '-':
                    geometry_y = gtk.screen_height() + int(geometry_y)
                else:
                    geometry_y = int(geometry_y)
            except:
                geometry_y = -1

    except:  pass

    tooltips = gtk.Tooltips()

    window = gtk.Window()

    def destroy(args):
        window.destroy()
        gtk.main_quit()

    window.connect("destroy", destroy)
    window.set_title('ScaSIS - Location based PIM')

    if geometry_x != -1:
        window.set_uposition( geometry_x, geometry_y)

    if geometry_w != -1:
        window.set_usize( geometry_w, geometry_h)

    box = gtk.VBox()
    window.add(box)
    box.show()

    nb = gtk.Notebook()
    nb.set_tab_pos(gtk.POS_TOP)
    box.pack_start(nb)
    nb.show()


    #~ for (name,component) in [("Todo","frmTodo"),("Schedule","frmSched"),("About","frmAbout")]:
    for (name,component) in [("Schedule","frmSched"),("Todo","frmTodo"),("About","frmAbout")]:
        module = __import__(component)
        widget = module.CreateWidget(tooltips)
        label = gtk.Label(name)
        label.set_padding(2, 2)
        nb.append_page(widget, label)

    window.show()
    tooltips.enable()
    gtk.main()

if __name__ == '__main__':
    main()
