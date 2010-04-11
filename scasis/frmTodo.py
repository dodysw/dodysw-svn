#!/usr/bin/env python2
import cPickle as pickle
import gtk
import gobject

class classGui:
    def __init__(self):
        self.edit = None
        self.view  = None
        self.model = None
        self.database = None
        self.id = None

def RedrawView(gui):
    gui.model.clear()
    for row in gui.database:
        iter = gui.model.append()
        gui.model.set_value(iter, 0, row['id'])
        gui.model.set_value(iter, 1, row['actv'])
        gui.model.set_value(iter, 2, row['loc'])
        gui.model.set_value(iter, 3 ,row['resp'])

#######################################################
#######################################################

def SwitchToEdit(gui,id):
    if id >= 0:
        record = [x for x in gui.database if x['id'] == id][0]
    else:   # new row, when saving, we must change id to the next bigger value
        record = gui.empty_record

    gui.edit_actv.set_text( record['actv'])
    gui.edit_loc.set_text( record['loc'])
    gui.edit_resp.set_text( record['resp'])

    gui.id = id

    gui.view.hide()
    gui.edit.show()

def SwitchToView(gui):
    RedrawView(gui)
    gui.edit.hide()
    gui.view.show()
    return



#######################################################
##
#######################################################

def CreateWidgetEdit(gui,tooltips):
    """this function creates the edit row panel
    """
    gui.empty_record = dict(id='',loc='',resp='',actv='')

    vbox = gtk.VBox()   # create a vertical box sizer
    vbox.show()         # show it

    hbox = gtk.HBox()   # create a horizontal box sizer
    vbox.pack_start(hbox,0,0)   # append the previous object to vertical sizer
    hbox.show()         # show it

    def ActionBack(args,gui):
        SwitchToView(gui)
        return

    button = gtk.Button(" Cancel ") # create button object
    hbox.pack_start(button,0,0)     # append button to horizontal sizer
    button.connect('clicked', ActionBack,gui)   # bind "clicked" event to ActionBack callback, passing reference to gui to additional parameter
    button.show()       # show button
    tooltips.set_tip( button, "Cancel") # define tooltips for this button

    combo = gtk.Combo()             # create combobox object
    combo.set_use_arrows_always(1)  # guess: show arrow in pulldown button of combo
    combo.disable_activate()        # ???
    combo.set_popdown_strings(['Buy milk','Recharge battery','Mail letters'])
    combo.show()                    # show combo
    hbox.pack_start(combo,0,0)      # add combo to horizontal box sizer
    combo.entry.set_width_chars(10) # set combo texteditor to 10 character
    #~ combo.entry.set_editable(0)     # set combo texteditor to be editable
    tooltips.set_tip( combo.entry, "Select or write activity");  # set tooltips for this combo

    gui.edit_actv = combo.entry # create reference to combobox's texteditor

    combo = gtk.Combo()
    combo.set_use_arrows_always(1)
    combo.disable_activate()
    combo.set_popdown_strings(['Groceries','Post-offices','Rooms'])
    combo.show()
    hbox.pack_start(combo,0,0)
    combo.entry.set_width_chars(7)
    #~ combo.entry.set_editable(0)
    tooltips.set_tip( combo.entry, "Select or write location");

    gui.edit_loc = combo.entry

    gui.combo = gtk.Combo()

    combo = gtk.Combo()
    combo.set_use_arrows_always(1)
    combo.disable_activate()
    combo.set_popdown_strings(['Beep','Croak','Ring'])
    hbox.pack_start(combo,0,0)
    combo.show()
    #~ combo.entry.set_editable(0)
    combo.entry.set_width_chars(5)
    tooltips.set_tip( combo.entry, "Select response");

    gui.edit_resp = combo.entry

    def ActionSaveRecord(args,gui):
        """edit row, then put into database, then dump it to file
        """
        record = dict(id=gui.id, actv=gui.edit_actv.get_text(), loc=gui.edit_loc.get_text(), resp=gui.edit_resp.get_text())

        # find if record given id is in the database
        existing_record = filter(lambda x: x['id'] == record['id'], gui.database)
        if gui.id != -1 and existing_record:
            existing_record[0].update(record)   # just update that record
        elif gui.id == -1:
            # makeup id
            record['id'] = str(max([int(x['id']) for x in gui.database]) + 1)
            gui.database.append(record)
        else:
            gui.database.append(record)

        # dump it to file
        pickle.dump(gui.database, file('todo.db','w'))
        SwitchToView(gui)
        return

    button = gtk.Button(" Save ")
    hbox.pack_end(button,0,0)
    button.connect('clicked', ActionSaveRecord,gui)
    button.show()
    tooltips.set_tip( button, "update/save record");
    return vbox


def CreateWidgetView(gui,tooltips):

    vbox = gtk.VBox()
    vbox.show()

    hbox = gtk.HBox()
    vbox.pack_start(hbox,0,0)
    hbox.show()

    window = gtk.ScrolledWindow()
    window.set_policy(gtk.POLICY_AUTOMATIC,gtk.POLICY_AUTOMATIC)
    window.set_shadow_type (gtk.SHADOW_ETCHED_IN);
    vbox.pack_start(window)
    window.show()

    model = gtk.ListStore(gobject.TYPE_PYOBJECT, gobject.TYPE_STRING, gobject.TYPE_STRING, gobject.TYPE_STRING) # create listcontrol's storage object, which define 4 column with data type python object, and 3 string
    gui.model = model   # save this to gui, so that future function can manipulate the content of listcontrol
    view = gtk.TreeView(model)              # create a list control, with model defined at ListStore
    renderer = gtk.CellRendererText()       # create object which render column 1 on all rows
    column = gtk.TreeViewColumn("Activity", renderer, text=1)   # create tree column object with label on 1st param, with renderer given on param 2, and id# given on 3rd param (used by add row later to refer this column)
    column.set_sort_column_id(1)# guess: if this column is sorted, which column id# it's really sorted by
    column.set_resizable(1) # 1 to enable this column to be resized
    view.append_column(column)  # append this tree column object to the listcontrol

    renderer = gtk.CellRendererText()       # create object which render column 2  on all rows
    column = gtk.TreeViewColumn("Location", renderer, text=2)# create tree column object with label on 1st param, with renderer given on param 2, and id# given on 3rd param (used by add row later to refer this column)
    column.set_sort_column_id(2)    # guess: if this column is sorted, which column id# it's really sorted by
    column.set_resizable(1) # 1 to enable this column to be resized
    view.append_column(column)  # append this tree column object to the listcontrol

    renderer = gtk.CellRendererText()       # create object which render column 3  on all rows
    column = gtk.TreeViewColumn("Response", renderer, text=3)# create tree column object with label on 1st param, with renderer given on param 2, and id# given on 3rd param (used by add row later to refer this column)
    column.set_sort_column_id(3)# guess: if this column is sorted, which column id# it's really sorted by
    column.set_resizable(1) # 1 to enable this column to be resized
    view.append_column(column)  # append this tree column object to the listcontrol

    def ActionEditItem(arg1,path,col,gui):
        iter = gui.model.get_iter(path)     # gui.model is reference to ListStore storage object. get_iter i guess return reference to currently selected row
        id = gui.model.get_value(iter,0)    # get column 0 of storage object at row given by previous statement
        SwitchToEdit(gui,id)
        return
    view.connect("row_activated",ActionEditItem,gui)        # bind double click (activated) on listcontrol row to "ActionEditItem" function callback, and also pass additional args which is reference to gui
    view.set_search_column(1);

    view.show()
    window.add_with_viewport(view)

    hbox = gtk.HBox()
    vbox.pack_start(hbox,0,0)
    hbox.show()

    label = gtk.Label(" Item ")
    hbox.pack_start(label,0,0)
    label.show()

    def GetCurrentDbId():
        selection = view.get_selection()
        (dummy,iter) =  selection.get_selected()
        if not iter: return -1
        return model.get_value(iter,0)

    def ActionNewItem(args,gui):
        SwitchToEdit(gui,-1)        # -1 is a sign that this is a new row
        return

    button = gtk.Button(" Add ")
    hbox.pack_start(button,0,0)
    button.connect('clicked', ActionNewItem,gui)
    button.show()
    tooltips.set_tip( button,"Add Item");

    def ActionCloseItem(args,gui):
        id = GetCurrentDbId()
        if id < 0: return
        record = gui.database.get_record( id )
        record.update_field("s1","closed")
        SaveDbRecord(gui.database,id,record)
        RedrawView(gui)
        return

    button = gtk.Button(" Close ")
    hbox.pack_start(button,0,0)
    button.connect('clicked', ActionCloseItem,gui)
    button.show()
    tooltips.set_tip( button,"Close Selected Item");

    def ActionDeleteItem(args,gui):
        """this function delete a row from memory database, and dump it
        """
        id = GetCurrentDbId()
        assert( id >= 0 )
        gui.database = filter(lambda x: x['id'] != id, gui.database)
        pickle.dump(gui.database, file('todo.db','wb'))
        RedrawView(gui)
        return

    button = gtk.Button(" Delete ")
    hbox.pack_end(button,0,0)
    button.connect('clicked', ActionDeleteItem,gui)
    button.show()
    tooltips.set_tip( button,"Delete Selected Item Permanently");

    return vbox

def CreateWidget(tooltips):
    gui = classGui()
    try:
        gui.database = pickle.load(file('todo.db','rb'))
    except IOError:
        gui.database = []

    vbox = gtk.VBox()
    gui.edit = CreateWidgetEdit(gui,tooltips)
    gui.view = CreateWidgetView(gui,tooltips)
    vbox.pack_start(gui.edit)
    vbox.pack_start(gui.view)
    SwitchToView(gui)
    vbox.show()
    return vbox

if __name__ == '__main__':
    window = gtk.Window()
    def ActionDestroy(args):
        window.destroy()
        gtk.main_quit()
    window.connect("destroy", ActionDestroy)
    window.set_title('TODO')
    tooltips = gtk.Tooltips()
    widget = CreateWidget(tooltips)
    tooltips.enable()
    window.add(widget)
    window.show()
    gtk.main()