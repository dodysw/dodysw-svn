#!/usr/bin/env python2
import gtk
import gobject
import sys
from timedate import *
import cPickle as pickle

# CONFIGURATION
week_start_monday = True

#######################################################
##
#######################################################
TYPE_REGULAR = 0
TYPE_WEEK = 1
TYPE_DAY = 2

class ClassConcreteEvent:
      def __init__(self,event,start,duration,text,type):
            self._e = event
            self._s = start
            self._d = duration
            self._text = text
            self._type= type
            return

      def get_event(self):
            return self._e

      def get_type(self):
            return self._type

      def get_start(self):
            return self._s

      def contains_date(self,date):
            return date_range_contains(self._s,self._d,date)

      def view_month(self):
          if self._type == TYPE_REGULAR:
              return [ date_to_string_weekday_day(  self._s ), self._text]
          elif self._type == TYPE_WEEK:
              return [ "", self._text]
          else:
              assert(None)
              return

      def view_day(self):
            return [ date_to_string_time(  self._s ), date_to_string_time(  date_add(self._s, self._d) ),self._text]

      def view_week(self):
          if self._type == TYPE_REGULAR:
              return [ date_to_string_time(  self._s), self._text]
          elif self._type == TYPE_DAY:
              return [ "", self._text]
          else:
              assert(None)
              return

      def __cmp__(a,b):
            return  date_compare(a._s,b._s)

#######################################################
##
#######################################################

class ClassAbstractEvent:
    def __init__(self):
        return

    def init_from_db(self,id, record):
        #~ print record
        self._id = id
        self._start = date_from_string(record['start'])
        field = record['duration']
        if field != "":
                self._duration = date_from_string_duration( field )
        else:
                self._duration = DurationZero
        field = record['length']
        if field != "":
                self._length = date_from_string_duration( field )
        else:
                self._length = DurationZero
        self._action = record['action']
        self._repeat = record['repeat']
        self._category = record['category']
        self._text = record['text']
        self._resp = record['resp']
        self._actv = record['actv']
        self._sensi  = record['sensi']
        self._loc  = record['loc']
        self._predict = record['predict']
        self._done = record['done']
        return

    def init_default(self):
        self._id = None
        self._start = date_now()
        self._duration = DurationZero
        self._length = DurationZero

        self._action = ""
        self._repeat = ""
        self._category = ""
        self._text  = ""

        # below is custom fields
        self._resp = ""
        self._actv = ""
        self._sensi  = ""
        self._loc  = ""
        self._predict = 0
        self._done = 0
        return

    def init_record(self,record):
        return

    def generate_record(self):
        record = dict(start='', duration=date_to_string_duration(DurationZero), repeat=self._repeat, length=date_to_string_duration(DurationZero),
            action=self._action,
            category=self._category,
            sensi=self._sensi,
            loc=self._loc,
            actv=self._actv,
            predict=self._predict,
            done=self._done,
            resp=self._resp,
            text=self._text)
        if self._length != DurationZero:
            record['length'] = date_to_string_duration(self._length)
        if self._duration != DurationZero:
            record['duration'] = date_to_string_duration(self._duration)
        record['start'] = date_to_string_full( self._start)
        return record

      #
    def find_first_time_after(self, date_after):
        date =  self._start
        if self._length != DurationZero and date_add(self._start, self._length) < date_after:
            return None
        #~ print 'repeat is', self._repeat

        if self._repeat.lower() == "yearly":
            x = self._start
            if date_after > x:             # just a speed optimization
                delta = date_sub(date_after,x)
                x = date_inc_year(x, (delta[0] / 365)-1 )
            while 1:
                if self._length != DurationZero and date_add(self._start, self._length) < x : return None
                if x >= date_after: return x
                x = date_inc_year(x,1)

        elif self._repeat.lower() == "weekly":
            x = self._start
            if date_after > x:             # just a speed optimization
                delta = date_sub(date_after,x)
                x = date_inc_week(x, (delta[0] / 7)-1 )
            while 1:
                if self._length != DurationZero and date_add(self._start, self._length) < x : return None
                if x >= date_after: return x
                x = date_inc_week(x,1)

        elif self._repeat.lower() == "daily":
            #~ print 'repeat daily!', self._start, self.length
            x = self._start
            if date_after >  x:           # just a speed optimization
                delta = date_sub(date_after,x)
                x = date_inc_day(x, delta[0]-1 )
            #~ print 'x is', x
            while 1:
                if self._length != DurationZero and date_add(self._start, self._length) < x : return None
                #~ print 'do not pass 1st try'
                if x >= date_after: return x
                x = date_inc_day(x,1)
        else:
            Warning("unsupported repeat pattern: " + self._repeat)
            return None

    def translate_to_display_events(self,begin,end,category):
        if category != "all" and category != self._category: return []
        if self._start >= end: return []

        if self._repeat == "":
            if date_add(self._start,self._duration) < begin: return []
            return [ ClassConcreteEvent(self,self._start,self._duration,self._text,TYPE_REGULAR) ]

        l = []
        while 1:
            #~ print self._start, 'find first after', begin
            begin = self.find_first_time_after( begin )
            #~ print self._start, 'got first after', begin
            if begin == None: break
            if begin >= end: break
            l.append( ClassConcreteEvent(self, begin, self._duration, self._text, TYPE_REGULAR) )
            begin = date_add(begin,DurationMinute) # guarantee progress

        return l
#######################################################
## read database
#######################################################

def read_database(database):
    l = []
    #~ print database
    for id in range(len(database)):
        record = database[id]
        event = ClassAbstractEvent()
        event.init_from_db(id,record)
        l.append( event )
    return l

#######################################################
##
#######################################################

class ClassGlobal:
      def __init__(self):
            self._current_view = None
            return

Global = ClassGlobal()


#######################################################
##
#######################################################

def SwitchMainWindow(mode, data=None):
      global Global

      header = Global._header
      mview = Global._view_month.get_widget()
      wview = Global._view_week.get_widget()
      dview = Global._view_day.get_widget()
      edit = Global._edit.get_widget()

      #print "switching to ", mode
      header.hide()
      edit.hide()
      wview.hide()
      mview.hide()
      dview.hide()

#      gui.last_mode = gui.view_mode.get_text()
#      if gui.last_mode == mode:
#            return

#      print "switching to ", mode
#      gui.view_mode.set_text(mode)

      def switch_to( x, string ):
            header.show()
            x.get_widget().show()
            Global._current_view = x
            return

      if mode == "Month" :
#            print data, date_to_string_full(data)
            Global._view_month.update_time( data, 0)
            switch_to(  Global._view_month,"Month")
      elif mode == "Week" :
            Global._view_week.update_time( data, 0)
            switch_to(  Global._view_week, "Week")
      elif mode == "Day" :
            Global._view_day.update_time( data, 0)
            switch_to(  Global._view_day,"Day")

      elif mode == "Previous":
            Global._raw_events = read_database( Global._database )
            #~ print 'Currentview is', Global._current_view
            Global._current_view.update_body()
            switch_to(  Global._current_view, None )


      elif mode == "EditNew":
            Global._current_time = Global._current_view.get_interval_begin()
            event = ClassAbstractEvent()
            event.init_default()
            Global._edit.set_record(event,1)
            edit.show()

      elif mode == "EditUpdate":
            Global._current_time = Global._current_view.get_interval_begin()
            Global._edit.set_record(data,0)
            edit.show()

      return

#######################################################
##
#######################################################

class ClassEditCalendarRecord:

      def __init__(self, tooltips):
            global Global
            self._new = -1
            self._widget = None
            self._dirty = -1
            vbox = gtk.VBox()

            def ActionChanged(widget):
                  self.set_dirty(1,self._new)
                  return

            hbox = gtk.HBox()
            hbox.show()
            vbox.pack_start(hbox,0,0)

            label = gtk.Label("")
            label.set_markup("<b>Actv</b>")
            hbox.pack_start(label,0)
            label.show()

            combo = gtk.Combo()
            combo.entry.set_width_chars(6)
            combo.set_use_arrows_always(1)
            combo.disable_activate()
            combo.set_popdown_strings(['Meeting','Play','Work'])
            combo.show()
            combo.entry.connect("changed",ActionChanged)

            hbox.pack_start(combo)

            self._actv = combo.entry

            label = gtk.Label("")
            label.set_markup("<b>Loc</b>")
            hbox.pack_start(label,0)
            label.show()

            combo = gtk.Combo()
            combo.entry.set_width_chars(6)

            combo.set_use_arrows_always(1)
            combo.disable_activate()
            combo.set_popdown_strings(['*','Rm113','Rm213','Rm333','Rm320','RmMeeting'])
            combo.show()
            combo.entry.connect("changed",ActionChanged)

            hbox.pack_start(combo)
            tooltips.set_tip( combo, "Pick location");
            self._loc = combo.entry

            #######################################################

            hbox = gtk.HBox()
            hbox.show()
            vbox.pack_start(hbox,0,0)

            label = gtk.Label("")
            label.set_markup("<b>Resp</b>")
            hbox.pack_start(label,0)
            label.show()

            combo = gtk.Combo()
            combo.entry.set_width_chars(4)
            #~ combo.set_use_arrows_always(1)
            combo.disable_activate()
            combo.set_popdown_strings(['OPEN','CLOSE','TURN_ON','TURN_OFF','BEEP','CROAK','ALARM'])
            combo.show()
            combo.entry.connect("changed",ActionChanged)

            hbox.pack_start(combo)
            tooltips.set_tip( combo, "Pick location");
            self._resp = combo.entry

            label = gtk.Label("")
            label.set_markup("<b>Sens</b>")
            hbox.pack_start(label,0)
            label.show()

            combo = gtk.Combo()
            combo.entry.set_width_chars(2)

            combo.set_use_arrows_always(1)
            combo.disable_activate()
            combo.set_popdown_strings([str(x + 1) for x in range(10)])
            combo.show()
            combo.entry.connect("changed",ActionChanged)

            hbox.pack_start(combo)
            tooltips.set_tip( combo, "Set record sensitivity");
            self._sensi = combo.entry

            label = gtk.Label("")
            label.set_markup("<b>Note</b>")
            hbox.pack_start(label,0)
            label.show()

            entry = gtk.Entry()
            entry.set_width_chars(4)
            entry.show()
            entry.connect("changed",ActionChanged)
            hbox.pack_start(entry)
            self._text = entry

            #######################################################

            options = 0;
            options = options |  gtk.CALENDAR_SHOW_DAY_NAMES
            options = options |  gtk.CALENDAR_SHOW_HEADING
            options = options |  gtk.CALENDAR_WEEK_START_MONDAY

            calendar = gtk.Calendar()
            calendar.display_options(options)
            calendar.show()
            calendar.connect("month-changed",ActionChanged)
            calendar.connect("day-selected",ActionChanged)
            self._calendar = calendar
            vbox.pack_start(calendar,0,0,0)

            tooltips.set_tip( calendar, "select start day");


            #######################################################
            # calendar uses up a lot of space, so we have a hide button
            #######################################################

            def ActionToggleCalendar(widget):
                if widget.get_active():
                    calendar.show()
                else:
                    calendar.hide()
                return

            button = gtk.ToggleButton(" Cal ")
            button.set_active(1)
            hbox.pack_start(button,0,0)
            button.show()
            button.connect("toggled", ActionToggleCalendar)

            #######################################################

            hbox = gtk.HBox()
            hbox.show()
            vbox.pack_start(hbox,0,0)


            frame = gtk.Frame(" Start Time  h:m")
            frame.show()
            hbox.pack_start(frame,0,0)

            hbox2 = gtk.HBox()
            hbox2.show()
            frame.add(hbox2)

            adj = gtk.Adjustment(0, 0, 23, 1,1,1)
            spin = gtk.SpinButton(adj,1,0)
            spin.show()
            spin.connect("changed",ActionChanged)
            hbox2.pack_start(spin)
            tooltips.set_tip( spin, "select hour");
            self._start_h = spin


            adj = gtk.Adjustment(0, 0, 59, 1,15,1)
            spin = gtk.SpinButton(adj,1,0)
            spin.show()
            spin.connect("changed",ActionChanged)
            hbox2.pack_start(spin)
            tooltips.set_tip( spin, "select minute");
            self._start_m = spin

            #######################################################

            frame = gtk.Frame(" Duration  d:h:m")
            frame.show()
            hbox.pack_start(frame,0,0)

            hbox2 = gtk.HBox()
            hbox2.show()
            frame.add(hbox2)

            adj = gtk.Adjustment(0, 0, 10000, 1,1,1)
            spin = gtk.SpinButton(adj,1,0)
            spin.show()
            spin.connect("changed",ActionChanged)
            hbox2.pack_start(spin)
            tooltips.set_tip( spin, "select days");
            self._duration_d = spin

            adj = gtk.Adjustment(0, 0, 23, 1,1,1)
            spin = gtk.SpinButton(adj,1,0)
            spin.show()
            spin.connect("changed",ActionChanged)
            hbox2.pack_start(spin)
            tooltips.set_tip( spin, "select hours");
            self._duration_h = spin

            adj = gtk.Adjustment(0, 0, 59, 1,15,1)
            spin = gtk.SpinButton(adj,1,0)
            spin.show()
            spin.connect("changed",ActionChanged)
            hbox2.pack_start(spin)
            tooltips.set_tip( spin, "select minutes");
            self._duration_m = spin

            #######################################################

            hbox = gtk.HBox()
            vbox.pack_start(hbox,0,0)
            hbox.show()

            label = gtk.Label("")
            label.set_markup("<b>Repeat</b>")
            hbox.pack_start(label)
            label.show()

            list = ['Yearly','Weekly','Daily']
            combo = gtk.Combo()
            combo.entry.set_width_chars(8)

            combo.set_use_arrows_always(1)
            combo.disable_activate()
            combo.set_popdown_strings(list)
            combo.show()
            combo.entry.connect("changed",ActionChanged)

            hbox.pack_start(combo)
            self._repeat = combo.entry

            label = gtk.Label("")
            label.set_markup("<b>For </b>")
            hbox.pack_start(label)
            label.show()

            adj = gtk.Adjustment(0, 0, 10000, 1,1,1)
            spin = gtk.SpinButton(adj,1,0)
            spin.show()
            spin.connect("changed",ActionChanged)
            hbox.pack_start(spin)
            tooltips.set_tip( spin, "select days");
            self._length_d = spin


            label = gtk.Label("")
            label.set_markup("<b>Days</b>")
            hbox.pack_start(label)
            label.show()

            #######################################################
            ## FIXME: not yet implemented
            #######################################################

#            hbox = gtk.HBox()
#            vbox.pack_start(hbox,0,0)
#            hbox.show()

#            label = gtk.Label(" Action " )
#            hbox.pack_start(label)
#            label.show()

            entry = gtk.Entry()
#            hbox.pack_start(entry)
            entry.show()
#            entry.connect("changed",ActionChanged)

            entry.set_width_chars(8)

            self._action = entry
            #######################################################

            hbox = gtk.HBox()
            vbox.pack_start(hbox,0,0)
            hbox.show()

            def ActionClickedCancel(widget):
                  SwitchMainWindow("Previous")
                  return

            button = gtk.Button(" Cancel ")
            hbox.pack_start(button,0,0)
            button.show()
            button.connect("clicked", ActionClickedCancel)
            self._cancel_button = button

            def ActionClickedCreate(widget):
                global Global
                self.get_widgets_contents( self._event)
                record = self._event.generate_record()
                Global._database.append(record)
                pickle.dump(Global._database, file('sched.db','w'))
                SwitchMainWindow("Previous")
                return

            button = gtk.Button(" Create ")
            hbox.pack_start(button,0,0)
            button.show()
            button.connect("clicked", ActionClickedCreate)
            self._create_button = button

            def ActionClickedUpdate(widget):
                global Global
                self.get_widgets_contents( self._event)
                record = self._event.generate_record()
                existing_record = filter(lambda x: x['start'] == record['start'], Global._database)
                existing_record[0].update(record)   # just update that record
                pickle.dump(Global._database, file('sched.db','w'))
                SwitchMainWindow("Previous")
                return

            button = gtk.Button(" Update ")
            hbox.pack_start(button,0,0)
            button.show()
            button.connect("clicked", ActionClickedUpdate)
            self._update_button = button

            def ActionClickedDelete(widget):
                  global Global
                  del Global._database[self._event._id]
                  pickle.dump(Global._database, file('sched.db','w'))
                  SwitchMainWindow("Previous")
                  return

            button = gtk.Button(" Delete ")
            hbox.pack_end(button,0,0)
            button.show()
            button.connect("clicked", ActionClickedDelete)
            self._delete_button = button


            def ActionReset(widget):
                  self.set_widgets_contents(self._event)
                  self.set_dirty(0,self._new)

                  return

            button = gtk.Button(" Reset ")
            hbox.pack_start(button,0,0)
            button.show()
            button.connect("clicked", ActionReset)
            self._reset_button = button

            vbox.show()
            self._widget = vbox
            return

      def get_widget(self):
            return self._widget

      def get_widgets_contents(self,event):
            x = self._calendar.get_date()

            # custom fields
            event._actv = self._actv.get_text()
            event._loc = self._loc.get_text()
            event._sensi = self._sensi.get_text()
            event._resp = self._resp.get_text()
            event._text = self._text.get_text()
            event._repeat = self._repeat.get_text()
            event._action = self._action.get_text()
            event._start = date_from_y_m_d_H_M(x[0],x[1],x[2]-1,int(self._start_h.get_value()),int(self._start_m.get_value()))

            event._duration =  date_from_d_H_M(int(self._duration_d.get_value()),
                                               int(self._duration_h.get_value()),
                                               int(self._duration_m.get_value()))
            event._length =  date_from_d_H_M(int(self._length_d.get_value()),0,0)

            return

      def set_dirty(self, dirty, new):
        if self._dirty == dirty and self._new == new: return
        self._dirty = dirty
        self._new = new
        if dirty:

            self._reset_button.show()
            self._delete_button.hide()
            self._cancel_button.show()
            if new:
                  self._create_button.show()
                  self._update_button.hide()
            else:
                  self._create_button.hide()
                  self._update_button.show()
        else:
            self._cancel_button.show()
            self._reset_button.hide()
            if new:
                  self._delete_button.hide()
            else:
                  self._delete_button.show()

            self._create_button.hide()
            self._update_button.hide()

            return


      def set_widgets_contents(self,event):
            y,m,d,H,M = date_to_y_m_d_H_M( event._start )
            self._calendar.select_month( m,y)
            self._calendar.select_day( d+1)

            self._start_h.set_value( H )
            self._start_m.set_value( M )

            d,H,M = date_to_d_H_M(event._duration)
            self._duration_d.set_value(d)
            self._duration_h.set_value(H)
            self._duration_m.set_value(H)

            self._repeat.set_text( event._repeat )
            d,H,M = date_to_d_H_M(event._length)
            self._length_d.set_value(d)

            self._loc.set_text( event._loc)
            self._actv.set_text( event._actv)
            self._resp.set_text( event._resp)
            self._sensi.set_text( event._sensi)
            self._text.set_text( event._text)
            return

      def set_record_reset(self):
            assert( self._dirty )
            self.set_widgets_contents(self._event)
            self.set_dirty(0)
            return

      def set_record(self, event,new):
            self.set_widgets_contents(event)
            self._event = event
            self._new = new
            self.set_dirty(0,new)
            return
            pass


      def get_record(self):
            pass

      def save_record(self):
            pass

#######################################################
##
#######################################################

class ClassViewerBase:
    def __init__(self,titles,viewer):


        self._widget = None
        self._events = []
        self._interval_begin = None
        self._interval_end = None

        self._viewer =  viewer
        self._color_normal = None
        self._color_active = None
        self._color_now = None

        window = gtk.ScrolledWindow()
        window.set_policy(gtk.POLICY_AUTOMATIC,gtk.POLICY_AUTOMATIC)
        window.show()

        def ActionEditItem(arg1,row,col):
            """When an item is clicked, we either edit the item or in case of a special item change the view"""
            display_event =  self._events[row[0]]
            if display_event.get_type() == TYPE_REGULAR:
                SwitchMainWindow("EditUpdate",display_event.get_event())
            elif display_event.get_type() == TYPE_DAY:
                SwitchMainWindow("Day",display_event.get_start())
            elif display_event.get_type() == TYPE_WEEK:
                SwitchMainWindow("Week",display_event.get_start())
            return

        # gross hack, could not find a better way to invoke gtk.ListStore
        # first column cotains color

        args = []
        args += [gtk.gdk.Color]
        args += [int]
        args += ([gobject.TYPE_STRING] * len(titles))
        model = apply( gtk.ListStore, args)

        self._model = model

        view = gtk.TreeView(model)

        for index in range(len(titles)):

            renderer = gtk.CellRendererText()
            column = gtk.TreeViewColumn(titles[index], renderer, text=index+2)
            column.set_resizable(1)

            column.add_attribute(renderer,"background-gdk",0)
#            column.add_attribute(renderer,"style",1)
            column.add_attribute(renderer,"weight",1)
            view.append_column(column)

        view.connect("row_activated",ActionEditItem)
        #view.set_search_column(1);
        view.show()

        self._view_widget = view

#        window.add_with_viewport(view)
        window.add(view)
        self._widget = window

        style = view.get_style()
        self._color_normal = style.white
        self._color_active = style.bg[gtk.STATE_ACTIVE]
        self._color_now = style.bg[gtk.STATE_SELECTED]
        return

    def get_widget(self):
        return self._widget

    def get_interval_begin(self):
        return self._interval_begin

    def get_interval_end(self):
        return self._interval_end

    def set_interval(self,start, length):
        #~ print 'setting interval to', start, 'until', length
        self._interval_begin = start
        self._interval_end = date_add(start,length)
        return

    def clear_events(self):
        self._events = []
        return

    def sort_events(self):
        self._events.sort()
        return

    def add_events(self,events):
        self._events.extend(events)
        return

    def add_raw_filtered_events(self):
        #~ print 'add_raw_filtered_events!', Global._raw_events
        global Global
        l = []
        for e in Global._raw_events:
            #~ print 'adding raw events', self._interval_begin,self._interval_end,Global._category
            self.add_events( e.translate_to_display_events(self._interval_begin,self._interval_end,Global._category) )
        return

    def find_index_of_earliest_event_after(self,date):
        count = 0
        for event in self._events:
            if event.get_start() < date:
                  count += 1
            else:
                  return count
        return -1

    def scroll_to_index(self,index):
#        print "scroll to ",index
        #self._view_widget.scroll_to_cell((index,), None,1,0,0)
        self._view_widget.scroll_to_point(100,100)
        return

    def redraw_events(self):
        now = date_now()
        now_active = 0
        self._model.clear()
        #~ print 'now',now,'e', self._events
        for event in self._events:
            if event.get_type() == TYPE_REGULAR:
                #~ print 'regular. date:', event._s, 'to', event._d
                if now_active:
                      fields = [self._color_now, 400]
                else:
                      fields = [self._color_normal, 400]
            else:
                #~ print 'e not regular. date:', event._s, 'to', event._d
                if event.contains_date(now):
                    #~ print 'it should be active'
                    now_active = 1
                    fields = [self._color_now, 700]
                else:
                    #~ print 'not active'
                    now_active = 0
                    fields = [self._color_active, 700]
            fields +=  self._viewer(event)
            iter = self._model.append()
            for i in range(len(fields)):
                self._model.set_value(iter, i, fields[i])
        return


#######################################################
##
#######################################################


class ClassViewMonth(ClassViewerBase):

      def __init__(self, tooltips):
            ClassViewerBase.__init__(self,["  Date  "," Description "], ClassConcreteEvent.view_month)
            vbox = gtk.VBox()

            #######################################################

            options = 0;
            options = options | gtk.CALENDAR_SHOW_DAY_NAMES
            options = options | gtk.CALENDAR_SHOW_HEADING
            if week_start_monday:
                  options = options | gtk.CALENDAR_WEEK_START_MONDAY
            options = options | gtk.CALENDAR_SHOW_WEEK_NUMBERS

            calendar = gtk.Calendar()
            calendar.display_options(options)
            calendar.show()

            vbox.pack_start(calendar,0,0,0)

            def ActionSwitchToDayView(dummy):
                  y,m,d = calendar.get_date()

                  SwitchMainWindow("Day", date_from_y_m_d_H_M(y,m,d-1,0,0))
                  return

            calendar.connect("day_selected_double_click",ActionSwitchToDayView)

            def ActionScrollToDay(dummy):
                  y,m,d = calendar.get_date()
                  date = date_from_y_m_d_H_M(y,m,d-1,0,0)
                  index = self.find_index_of_earliest_event_after( date )
                  if index >= 0:
                        self.scroll_to_index(index)
                  return

            calendar.connect("day_selected",ActionScrollToDay)

            def ActionMonthChanged(dummy):
                  y,m,d = calendar.get_date()
                  self.update_time( date_from_y_m_d_H_M(y,m,d-1,0,0), 0)
                  return

            calendar.connect("month_changed",ActionMonthChanged)
            self._calendar = calendar

            #######################################################


            vbox.pack_start(ClassViewerBase.get_widget(self))

#            hbox = gtk.HBox()
#            vbox.pack_start(hbox,0,0)
#            hbox.show()

            self._widget = vbox
            return


      def update_head(self):
            """update the title, which does not really exist for the month view"""
            new = date_to_y_m_d_H_M(self.get_interval_begin())   ##################### fixme
            old = self._calendar.get_date() # uses gtk format (y,m,d+1)

            # without the following test we might get infinite loop
            # triggering "month_changed" events over and over again

            if new[0] != old[0] or new[1] != old[1]:
                  self._calendar.select_month( new[1],new[0])
            #~ self._calendar.select_day( new[2]+1) # use gtk format for day
            # if month/year is current, try to use current date if possible, if not the largest of that month
            Now = time.localtime()
            if Now[0] == new[0] and Now[1] == (new[1]+1):   # note, this program month, starts at 0
                #~ print 'now is', Now, 'new is', new
                self._calendar.mark_day(Now[2])
            else:
                self._calendar.unmark_day(Now[2])
            #~ sel_day = min(days_in_month(new[1], new[0]), time.localtime()[2])
            #~ self._calendar.select_day( sel_day) # use gtk format for day
            return


      def add_week_markers(self):
           begin = self.get_interval_begin()
           end  = self.get_interval_end()

           begin = date_to_beginning_of_week(begin, week_start_monday)

           l = []
           while begin < end:
               l.append(  ClassConcreteEvent(self, begin, DurationWeek, date_to_string_week(begin,week_start_monday), TYPE_WEEK)  )
               begin = date_add(begin,DurationWeek)
           self.add_events( l )
           return

      def update_body(self):
            #~ print 'updabte body-nya MONTH'
            self.clear_events()
            self.add_raw_filtered_events()
            self.add_week_markers()
            self.sort_events()
            self.redraw_events()
            return

      def update_time(self, start, offset):
          """set a new month to be displayed -- offset allows to easily inc/dec a month"""
          #assert offset == 0
          y,m,d,H,M = date_to_y_m_d_H_M( start )
          start = date_from_y_m_d_H_M(y,m+offset,0,0,0) ##### fixme
          length = date_from_d_H_M( days_in_month(m,y), 0 ,0 )
          self.set_interval(start, length)
          self.update_head()
          self.update_body()
          return

#######################################################
##
#######################################################

class ClassViewWeek(ClassViewerBase):

    def __init__(self, tooltips):
        ClassViewerBase.__init__(self,[" Time "," Description "], ClassConcreteEvent.view_week)

        vbox = gtk.VBox()

        ############################################################################################

        hbox = gtk.HBox()
        vbox.pack_start(hbox,0,0)
        hbox.show()

        def ActionMoveWeek(widget,offset):
            #print "move", self.get_start_time(), offset
            self.update_time( self.get_interval_begin(), offset)
            return

        button = gtk.Button(" << ")
        hbox.pack_start(button,0,0)
        button.show()
        button.connect("clicked", ActionMoveWeek,-1)
        tooltips.set_tip( button, "previous week");

        button = gtk.Button(" >> ")
        hbox.pack_start(button,0,0)
        button.show()
        button.connect("clicked", ActionMoveWeek,+1)
        tooltips.set_tip( button, "next week");

        label = gtk.Label("Week")
        hbox.pack_start(label)
        label.show()
        self._week_label = label

        def ActionGoMonth(widget):
            SwitchMainWindow( "Month",self.get_interval_begin())
            return

        button = gtk.Button("Month")
        hbox.pack_start(button,0,0)
        button.show()
        button.connect("clicked", ActionGoMonth)
        tooltips.set_tip( button, "go to month");
        self._month_button = button

        ############################################################################################

        vbox.pack_start(ClassViewerBase.get_widget(self))
        self._widget = vbox
        return

    def update_head(self):
        date = self.get_interval_begin()
        self._week_label.set_markup( '<b>' + date_to_string_week(date,week_start_monday) + '</b>' )
        self._month_button.set_label( date_to_string_month_year(date) )
        return


    def add_day_markers(self):
        begin = self.get_interval_begin()
        end = self.get_interval_end()

#        begin = date_to_beginning_of_week(begin)

        l = []
        while begin < end:
            l.append(  ClassConcreteEvent(self,begin, DurationDay, date_to_string_weekday_day(begin), TYPE_DAY)  )
            begin = date_add(begin,DurationDay)

        self.add_events( l )
        return

    def update_body(self):
        self.clear_events()
        self.add_raw_filtered_events()
        self.add_day_markers()
        self.sort_events()
        self.redraw_events()
        return

    def update_time(self, start, offset):
        start = date_inc_week(start,  offset )
        start = date_to_beginning_of_week( start,  week_start_monday )

        self.set_interval(start, DurationWeek)
        self.update_head()
        self.update_body()
        return

#######################################################
##
#######################################################

class ClassViewDay(ClassViewerBase):
    """This class view daily schedule
    """
    def __init__(self, tooltips):
        ClassViewerBase.__init__(self,[" Beg "," End "," Description "], ClassConcreteEvent.view_day)

        vbox = gtk.VBox()

        #######################################################

        hbox = gtk.HBox()
        vbox.pack_start(hbox,0,0)
        hbox.show()


        def ActionMoveDay(widget,dir):
            self.update_time( self.get_interval_begin(),dir)
            return

        button = gtk.Button(" << ")
        hbox.pack_start(button,0,0)
        button.show()
        button.connect("clicked", ActionMoveDay, -1)
        tooltips.set_tip( button, "prev day");

        button = gtk.Button(" >> ")
        hbox.pack_start(button,0,0)
        button.show()
        button.connect("clicked", ActionMoveDay, +1)
        tooltips.set_tip( button, "next day");


        label = gtk.Label("Day")
        hbox.pack_start(label)
        label.show()
        self._day_label = label

        def ActionGoMonth(widget):
            SwitchMainWindow( "Month",self.get_interval_begin())
            return

        button = gtk.Button("Month")
        hbox.pack_start(button,0,0)
        button.show()
        button.connect("clicked", ActionGoMonth)
        tooltips.set_tip( button, "go to month");

        self._month_button = button

        def ActionGoWeek(widget):
            SwitchMainWindow( "Week",self.get_interval_begin())
            return

        button = gtk.Button("Week")
        hbox.pack_start(button,0,0)
        button.show()
        button.connect("clicked", ActionGoWeek)
        tooltips.set_tip( button, "go to week");

        self._week_button = button


        ############################################################################################

        #~ vbox.pack_start(ClassViewerBase.get_widget(self))
        vbox.pack_start(self._widget)   # equal to

        self._widget = vbox
        return

    def update_head(self):
        date = self.get_interval_begin()
        self._day_label.set_markup( '<b>' + date_to_string_weekday_day(date)  + '</b>')
        self._week_button.set_label(  date_to_string_week(date,week_start_monday) )
        self._month_button.set_label( date_to_string_month_year(date) )
        return

    def add_hour_markers(self):
        begin = self.get_interval_begin()
        end = self.get_interval_end()

        begin = date_to_beginning_of_day(begin)

        l = []
        while begin < end:
            l.append(  ClassConcreteEvent(self,begin, DurationSixHours, "", TYPE_DAY)  )
            begin = date_add(begin,DurationSixHours)

        self.add_events( l )
        return

    def update_body(self):
        self.clear_events()                 # self._events = []
        self.add_raw_filtered_events()
        self.add_hour_markers()
        self.sort_events()
        self.redraw_events()
        return

    def update_time(self,start, offset):
        delta = (offset,0,0)
        start = date_add(start,delta)
        start = date_to_beginning_of_day(start)

        self.set_interval(start,(1,0,0))
        self.update_head()  # update the head part with current date
        self.update_body()
        return

#######################################################
##
#######################################################


def MyOptionMenu(list,func):
      view = gtk.OptionMenu()
      menu = gtk.Menu()
      for l in list:
            if l:
                  item=gtk.MenuItem(l)
                  item.connect("activate", func, l)
            else:
                  item=gtk.SeparatorMenuItem()
            item.show()
            menu.add(item)

      view.set_menu( menu )
      return view

#######################################################
##
#######################################################

def CreateHeaderWidget(tooltips):
    global Global

    hbox = gtk.HBox()
    hbox.show()
    ############################################################################################

    def ActionSwitchToEditor( dummy):
          SwitchMainWindow("EditNew")
          return

    button = gtk.ToggleButton(" New ")
    button.connect('clicked', ActionSwitchToEditor)
    hbox.pack_start(button,0,0)
    button.show()
    tooltips.set_tip( button,"New Event");

    ############################################################################################

    ############################################################################################

#    def ActionSwitchCategory( x, y ):
#          global Global
#          Global._category = y
#          SwitchMainWindow("Previous")
#          return

    list = ["all"]
    Global._category = list[0]

#    view = MyOptionMenu(list,ActionSwitchCategory)
#    view.show()
#    hbox.pack_start(view,0,0)
#    tooltips.set_tip( view, "Choose Category");

    ############################################################################################

    label = gtk.Label("Time")
    label.show()
    hbox.pack_start(label,1,1)
    tooltips.set_tip( label, "Current Time");

    def ActionGoTo(widget,mode):
        SwitchMainWindow( mode, date_now())
        return

    button = gtk.ToggleButton("Week")
    hbox.pack_end(button,0,0)
    button.show()
    tooltips.set_tip( button,"go to week");
    button.connect("clicked", ActionGoTo, "Week")

    week_button = button

    button = gtk.ToggleButton("Month")
    hbox.pack_end(button,0,0)
    button.show()
    tooltips.set_tip( button,"go to month");
    button.connect("clicked", ActionGoTo,"Month")

    month_button = button

    button = gtk.ToggleButton("Day")
    hbox.pack_end(button,0,0)
    button.show()
    tooltips.set_tip( button,"go to day");
    button.connect("clicked", ActionGoTo,"Day")

    day_button = button



    def TimerInterrupt():
        #print "timer\n"
        now = date_now()
        label.set_markup( "<b>" + date_to_string_time(now) + "</b>" )
        day_button.set_label(  date_to_string_weekday_day(now) )
        month_button.set_label(  date_to_string_month_year(now) )
        week_button.set_label( date_to_string_week(now,week_start_monday) )
        gtk.timeout_add(30000,TimerInterrupt)
        return

    TimerInterrupt()

    # the plan is to use the timer only when the widget is mapped
    #def ActionMapping(args,str):
    #    print "mapping",str
    #    return


    #label.connect('map', ActionMapping,"map")
    #label.connect('unmap', ActionMapping,"unmap")
    #label.connect('show', ActionMapping,"show")
    #label.connect('hide', ActionMapping,"hide")

    return hbox

#######################################################
##
#######################################################

def CreateWidget(tooltips,filename=None):
    try:
        Global._database = pickle.load(file('sched.db','rb'))
    except IOError:
        Global._database = []

    Global._raw_events = read_database( Global._database )

    vbox = gtk.VBox()

    Global._view_month = ClassViewMonth(tooltips)
    vbox.pack_start(Global._view_month.get_widget())

    Global._view_week = ClassViewWeek(tooltips)
    vbox.pack_start(Global._view_week.get_widget())

    Global._view_day = ClassViewDay(tooltips)
    vbox.pack_start(Global._view_day.get_widget())


    Global._edit = ClassEditCalendarRecord(tooltips)
    vbox.pack_start( Global._edit.get_widget())

    Global._header = CreateHeaderWidget(tooltips)
    vbox.pack_start(Global._header,0,0)

    SwitchMainWindow("Month",date_now())

    vbox.show()

    return vbox

#######################################################
##
#######################################################

def Main():
    window = gtk.Window()
    def ActionDestroy(args,window):
        window.destroy()
        gtk.main_quit()
    window.connect("destroy", ActionDestroy, window)
    window.set_title('Calendar')
    window.set_border_width(0)
    tooltips = gtk.Tooltips()
    if len(sys.argv) > 1:
        widget = CreateWidget(tooltips,sys.argv[1])
    else:
        widget = CreateWidget(tooltips)
    window.add(widget)
    window.show()
    tooltips.enable()
    gtk.main()

#######################################################
##
#######################################################

if __name__ == '__main__':
    Main()
