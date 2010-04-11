class People:
    pass

class Pert:
    """
    [perts before] ==> this ==> [perts after]
    """
    slack = early_start = early_finish = late_start = late_finish = None
    def __init__(self, parent, name, duration_effort, people=1, effectivity=1, valid_people=None):
        self.name = name
        self.duration_effort = duration_effort
        self.people = people
        self.effectivity = effectivity
        self.parent = parent
        self._before = []
        self._done_before = []
        self._done_before_meta = []
        self._done_after = []
        self._done_after_meta = []
        self._after = []

    def after(self, el):
        """this task starts after el done"""
        paired_obj = self.parent.pert[el]
        self._before.append(paired_obj)
        paired_obj._after.append(self)

    def before(self, el):
        """this pert is before el, thus el is perts after"""
        paired_obj = self.parent.pert[el]
        self._after.append(paired_obj)
        paired_obj._before.append(self)

    def done_before(self, el, plus=0):
        """this task must be done before el task done"""
        self._done_before.append(self.parent.pert[el])
        self._done_before_meta.append({
            'plus': plus,
            })

    def done_after(self, el, plus=0):
        """this task can be done after el task done"""
        self._done_after.append(self.parent.pert[el])
        self._done_after_meta.append({
            'plus': plus,
            })

    def starts_after(self, el, plus=0):
        """this task starts @plus unit after el"""

    def starts_before(self, el, plus=0):
        """this task starts @plus unit after el"""

    def get_early_start(self):
        if self.parent._dbg_calc:
            print "[%s] calc-ing early start" % self.name

        if self.early_start is None:
            if not self._before:
                self.early_start = 0
            else:
                if self.parent._dbg_calc:
                    print "[%s] max of early finish before me: %s" % (self.name, ','.join([str(pert.get_early_finish()) + '(%s)' % pert.name for pert in self._before]))
                self.early_start = max([pert.get_early_finish() for pert in self._before])
        if self.parent._dbg_calc:
            print "[%s] early start = %s" % (self.name, self.early_start)
        return self.early_start

    def get_early_finish(self):
        if self.parent._dbg_calc:
            print "[%s] calc-ing early finish" % self.name
        if self.early_finish is None:
            possible_timing = []
            possible_timing.append(self.get_early_start() + self.duration_effort/(self.people * self.effectivity))
            for i, pert in enumerate(self._done_after):
                possible_timing.append(pert.get_early_finish() + self._done_after_meta[i]['plus'])
            if self.parent._dbg_calc:
                print "[%s] max of early finish: %s" % (self.name, ','.join([str(x) for x in possible_timing]))
            self.early_finish = max(possible_timing)
        if self.parent._dbg_calc:
            print "[%s] early finish = %s" % (self.name, self.early_finish)
        return self.early_finish

    def get_late_start(self):
        if self.parent._dbg_calc:
            print "[%s] calc-ing late start" % self.name
        if self.late_start is None:
            possible_timing = []
            possible_timing.append(self.get_late_finish() - self.duration_effort/(self.people * self.effectivity))
            #~ for i, pert in enumerate(self._done_after):
                #~ possible_timing.append(pert.get_early_finish() + self._done_after_meta[i]['plus'])
            if self.parent._dbg_calc:
                print "[%s] min of late start: %s" % (self.name, ','.join([str(x) for x in possible_timing]))
            self.late_start = min(possible_timing)
        if self.parent._dbg_calc:
            print "[%s] late start = %s" % (self.name, self.late_start)
        return self.late_start

    def get_late_finish(self):
        """
        for the final task, late finish = early finish
        final task = task that has no after tasks. if >1 final task, error.
        """
        if self.late_finish is None:
            if not self._after and not self._done_before:
                self.late_finish = self.get_early_finish()
            elif self._done_before:
                self.late_finish = min([pert.get_late_finish() for pert in self._done_before])
            else:
                self.late_finish = min([pert.get_late_start() for pert in self._after])
        return self.late_finish

    def get_slack(self):
        return self.get_late_start() - self.get_early_start()

    def get_duration(self):
        return self.get_early_finish() - self.get_early_start()

class Perts:
    _dbg_calc = False
    def __init__(self):
        self.pert = {}
        self.effectivity = 1

    def SetEffectivity(self, num):
        self.effectivity = num

    def add(self, *attr, **kwarg):
        pname = attr[0]
        assert pname not in self.pert
        self.pert[pname] = Pert(self, effectivity=self.effectivity, *attr, **kwarg)

    def __call__(self, pname):
        return self.pert[pname]

    def Validate(self):
        # make sure there is only one final task
        final_task = None
        for task in self.pert.values():
            if len(task._after) == 0 and len(task._done_after) == 0:
                assert final_task is None, "%s: Already has final task [%s]" % (task.name, final_task.name)
                final_task = task

    def ShowData(self):
        sortfield = self.pert.keys()
        sortfield.sort()
        for field in sortfield:
            obj = self.pert[field]
            print "%s=\t%s\t%s\t%s\t%s" % (field, obj.get_early_start(), obj.get_early_finish(), obj.get_late_start(), obj.get_late_finish())

    def ShowTimelineEarly(self):
        sortfield = self.pert.values()
        def by_early_start(task1, task2):
            return cmp(task1.get_early_start(), task2.get_early_start())
        sortfield.sort(by_early_start)
        print "Early Timeline:"
        print "Task\tEarly Start\tEarly Finish\tDuration\tDurEff\tEffort\tAvgEffortPerPeriod"
        for obj in sortfield:
            print "%s\t%0.2f\t%0.2f\t%0.2f\t%0.2f\t%0.2f\t%0.2f" % (obj.name, obj.get_early_start(), obj.get_early_finish(), obj.get_duration(), obj.duration_effort, obj.people, obj.duration_effort/obj.get_duration())

    def ShowTimelineLate(self):
        sortfield = self.pert.values()
        def by_late_start(task1, task2):
            return cmp(task1.get_late_start(), task2.get_late_start())
        sortfield.sort(by_late_start)
        print "Late Timeline:"
        for obj in sortfield:
            print "%s=\t%0.2f\t%0.2f (%0.2fd) | %0.2fd" % (obj.name, obj.get_late_start(), obj.get_late_finish(), obj.get_duration(), obj.get_slack())

    def ShowUnlinked(self):
        sortfield = self.pert.keys()
        sortfield.sort()
        print "Unlinked tasks:"
        for field in sortfield:
            obj = self.pert[field]
            if not obj._after and not obj._before and not obj._done_after and not obj._done_before:
                print field

    def ShowCalc(self, state):
        self._dbg_calc = state

    def ShowCriticalPath(self):
        sortfield = self.pert.values()
        def by_early_start(task1, task2):
            return cmp(task1.get_early_start(), task2.get_early_start())
        sortfield.sort(by_early_start)
        print "Critical Path:"
        for obj in sortfield:
            if abs(obj.get_slack()) < 1e-6:
                print "%s=\t%0.2f\t%0.2f (%0.2f days)" % (obj.name, obj.get_early_start(), obj.get_early_finish(), obj.get_duration())

    def AddPeople(self, count, type):
        pass

    def ShowResourceLevel(self):
        sortfield = self.pert.values()
        def by_early_start(task1, task2):
            return cmp(task1.get_early_start(), task2.get_early_start())
        sortfield.sort(by_early_start)
        print "Resource level per period:"
        for period in range(10):
            level = 0
            msg = []
            for obj in sortfield:
                if obj.get_early_start() <= period <= obj.get_early_finish():
                    level += obj.people
                    msg.append("%s=%s" % (obj.name, obj.people))

            print "Day %s:\t%s\t%s" % (period+1, level, ', '.join(msg))