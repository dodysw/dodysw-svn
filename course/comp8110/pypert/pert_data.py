from pypert import Perts, People

eVacs = Perts()
#~ eVacs.SetEffectivity(0.6)

class Analyst(People):
    pass

class Writer(People):
    pass

class Programmer(People):
    pass

class Tester(People):
    pass

# add people into project
eVacs.AddPeople(2, Analyst)
eVacs.AddPeople(1, Writer)
eVacs.AddPeople(3, Programmer)
eVacs.AddPeople(2, Tester)

# two analyst/designer undertaking analysis and design activities
eVacs.add("SRS-01",1.08, valid_people=[Analyst])
eVacs.add("SRS-02",4.17, valid_people=[Analyst])
eVacs.add("SRS-03",4, valid_people=[Analyst])
eVacs.add("SRS-04",2.33, valid_people=[Analyst])
eVacs.add("SRS-05",2.5, valid_people=[Analyst])
eVacs.add("SRS-06",3.33, valid_people=[Analyst])
eVacs.add("SRS-07",4, valid_people=[Analyst])
eVacs.add("SRS-08",3.16, valid_people=[Analyst])
eVacs.add("SRS-09",1.33, valid_people=[Analyst])
eVacs.add("SRS-10",4.16, valid_people=[Analyst])
eVacs.add("SRS-11",5, valid_people=[Analyst,Writer])     # technical writer
eVacs.add("SRS-12",1.83, valid_people=[Analyst])

# two analyst/designer undertaking analysis and design activities
eVacs.add("SDD-01",3.83, valid_people=[Analyst])
eVacs.add("SDD-02",6, valid_people=[Analyst])
eVacs.add("SDD-03",15, valid_people=[Analyst])
eVacs.add("SDD-04",2.17, valid_people=[Analyst])
eVacs.add("SDD-05",2.17, valid_people=[Analyst])
eVacs.add("SDD-06",2.17, valid_people=[Analyst])
eVacs.add("SDD-07",2.83, valid_people=[Analyst])

# two testers available after SRS complete
eVacs.add("ATP-01",1, valid_people=[Tester])
eVacs.add("ATP-02",1.92, valid_people=[Tester])
eVacs.add("ATP-03",4, valid_people=[Tester])
eVacs.add("ATP-04",4, valid_people=[Tester])
eVacs.add("ATP-05",2, valid_people=[Tester])
eVacs.add("ATP-06",1, valid_people=[Tester])
eVacs.add("ATP-07",3.17, valid_people=[Tester])
eVacs.add("ATP-08",2.17, valid_people=[Tester])

# two testers available after SRS complete
eVacs.add("STP-01",1, valid_people=[Tester])
eVacs.add("STP-02",3.83, valid_people=[Tester])
eVacs.add("STP-03",5, valid_people=[Tester])
eVacs.add("STP-04",2.17, valid_people=[Tester])
eVacs.add("STP-05",4, valid_people=[Tester])
eVacs.add("STP-06",1.58, valid_people=[Tester])

# two testers available after SRS complete
eVacs.add("ITP-01",1, valid_people=[Tester])
eVacs.add("ITP-02",3, valid_people=[Tester])
eVacs.add("ITP-03",5.17, valid_people=[Tester])
eVacs.add("ITP-04",2.17, valid_people=[Tester])
eVacs.add("ITP-05",3, valid_people=[Tester])
eVacs.add("ITP-06",1.58, valid_people=[Tester])

# 3 programmers available during coding and unit testing, one for each product
# with additional 2 analysts (divided into 3 product) totalling 3/3 + 2/3 = 1.67 per product
eVacs.add("CUT-01(EV)",2, valid_people=[Programmer])
eVacs.add("CUT-02(EV)",2, valid_people=[Programmer])
eVacs.add("CUT-03(EV)",36.67, valid_people=[Programmer])
eVacs.add("CUT-04(EV)",7.33, valid_people=[Programmer])
eVacs.add("CUT-05(EV)",4.17, valid_people=[Programmer])
eVacs.add("CUT-06(EV)",5, valid_people=[Programmer])
eVacs.add("CUT-07(EV)",2.17, valid_people=[Programmer])
eVacs.add("CUT-08(EV)",4.17, valid_people=[Programmer])

eVacs.add("CUT-01(CS)",2, valid_people=[Programmer])
eVacs.add("CUT-02(CS)",2, valid_people=[Programmer])
eVacs.add("CUT-03(CS)",36.67, valid_people=[Programmer])
eVacs.add("CUT-04(CS)",7.33, valid_people=[Programmer])
eVacs.add("CUT-05(CS)",4.17, valid_people=[Programmer])
eVacs.add("CUT-06(CS)",5, valid_people=[Programmer])
eVacs.add("CUT-07(CS)",2.17, valid_people=[Programmer])
eVacs.add("CUT-08(CS)",4.17, valid_people=[Programmer])

eVacs.add("CUT-01(DS)",2, valid_people=[Programmer])
eVacs.add("CUT-02(DS)",2, valid_people=[Programmer])
eVacs.add("CUT-03(DS)",30.83, valid_people=[Programmer])
eVacs.add("CUT-04(DS)",7.33, valid_people=[Programmer])
eVacs.add("CUT-05(DS)",4.17, valid_people=[Programmer])
eVacs.add("CUT-06(DS)",5, valid_people=[Programmer])
eVacs.add("CUT-07(DS)",2.17, valid_people=[Programmer])
eVacs.add("CUT-08(DS)",4.17, valid_people=[Programmer])

# two testers divided into 3 products
eVacs.add("AT-01(EV)",1, valid_people=[Tester])
eVacs.add("AT-02(EV)",3, valid_people=[Tester])
eVacs.add("AT-03(EV)",3, valid_people=[Tester])
eVacs.add("AT-04(EV)",3.17, valid_people=[Tester])
eVacs.add("AT-05(EV)",4.83, valid_people=[Tester])

eVacs.add("AT-01(CS)",1, valid_people=[Tester])
eVacs.add("AT-02(CS)",3, valid_people=[Tester])
eVacs.add("AT-03(CS)",3, valid_people=[Tester])
eVacs.add("AT-04(CS)",3.17, valid_people=[Tester])
eVacs.add("AT-05(CS)",4.83, valid_people=[Tester])

eVacs.add("AT-01(DS)",1, valid_people=[Tester])
eVacs.add("AT-02(DS)",3, valid_people=[Tester])
eVacs.add("AT-03(DS)",3, valid_people=[Tester])
eVacs.add("AT-04(DS)",3.17, valid_people=[Tester])
eVacs.add("AT-05(DS)",4.83, valid_people=[Tester])


eVacs("SRS-02").after("SRS-01")
eVacs("SRS-04").after("SRS-01")
eVacs("SRS-05").after("SRS-02")
eVacs("SRS-03").done_before("SRS-04")
eVacs("SRS-04").done_after("SRS-02")
eVacs("SRS-04").done_after("SRS-05")
eVacs("SRS-06").after("SRS-05")
eVacs("SRS-07").after("SRS-06")
eVacs("SRS-08").done_after("SRS-07")
eVacs("SRS-08").done_before("SRS-07", plus=2)
eVacs("SRS-09").after("SRS-07")
eVacs("SRS-10").after("SRS-09")
eVacs("SRS-11").done_after("SRS-10")
eVacs("SRS-12").after("SRS-11")

eVacs("ATP-01").after("SRS-12")
eVacs("ATP-02").after("SRS-12")
eVacs("ATP-03").after("ATP-02")
eVacs("ATP-04").after("ATP-02")
eVacs("ATP-05").after("ATP-04")
eVacs("ATP-06").after("ATP-05")
eVacs("ATP-07").after("ATP-05")
eVacs("ATP-08").after("ATP-07")
eVacs("ATP-08").after("ATP-06") # Draft should be presented before delivering final doc

eVacs("SDD-01").after("SRS-05")
eVacs("SDD-01").done_after("SRS-10")
eVacs("SDD-02").after("SDD-01")
eVacs("SDD-03").after("SDD-01")
eVacs("SDD-04").after("SDD-01")
eVacs("SDD-05").after("SDD-04")
eVacs("SDD-06").after("SDD-05")
eVacs("SDD-07").after("SDD-06")

eVacs("STP-01").after("SDD-02")
eVacs("STP-02").after("STP-01")
eVacs("STP-02").after("SDD-05")
eVacs("STP-03").after("STP-02")
eVacs("STP-04").after("STP-02")
eVacs("STP-05").after("STP-04")
eVacs("STP-06").after("STP-05")

eVacs("ITP-01").after("SDD-02")
# when do ITP-01 must finish? I assume ITP-02 is done after ITP-01
eVacs("ITP-02").after("ITP-01")

eVacs("ITP-02").after("SDD-04")
eVacs("ITP-03").after("ITP-02")
eVacs("ITP-04").after("ITP-02")
eVacs("ITP-05").after("ITP-02")
eVacs("ITP-06").after("ITP-05")

eVacs("CUT-02(CS)").after("CUT-01(CS)")
eVacs("CUT-02(CS)").after("SDD-05")
eVacs("CUT-03(CS)").after("CUT-01(CS)")
eVacs("CUT-03(CS)").after("SDD-05")
eVacs("CUT-04(CS)").after("CUT-01(CS)")
eVacs("CUT-04(CS)").after("SDD-05")
eVacs("CUT-04(CS)").after("ITP-06")
eVacs("CUT-05(CS)").after("CUT-04(CS)")
eVacs("CUT-05(CS)").after("STP-06")
eVacs("CUT-06(CS)").after("CUT-05(CS)")
eVacs("CUT-07(CS)").after("CUT-06(CS)")
eVacs("CUT-08(CS)").after("CUT-07(CS)")

eVacs("CUT-02(EV)").after("CUT-01(EV)")
eVacs("CUT-02(EV)").after("SDD-05")
eVacs("CUT-03(EV)").after("CUT-01(EV)")
eVacs("CUT-03(EV)").after("SDD-05")
eVacs("CUT-04(EV)").after("CUT-01(EV)")
eVacs("CUT-04(EV)").after("SDD-05")
eVacs("CUT-04(EV)").after("ITP-06")
eVacs("CUT-04(EV)").done_after("CUT-03(EV)")
eVacs("CUT-03(EV)").done_after("CUT-02(EV)")

eVacs("CUT-05(EV)").after("CUT-04(EV)")
eVacs("CUT-05(EV)").after("STP-06")
eVacs("CUT-06(EV)").after("CUT-05(EV)")
eVacs("CUT-07(EV)").after("CUT-06(EV)")
eVacs("CUT-08(EV)").after("CUT-07(EV)")

eVacs("CUT-02(DS)").after("CUT-01(DS)")
eVacs("CUT-02(DS)").after("SDD-05")
eVacs("CUT-03(DS)").after("CUT-01(DS)")
eVacs("CUT-03(DS)").after("SDD-05")
eVacs("CUT-04(DS)").after("CUT-01(DS)")
eVacs("CUT-04(DS)").after("SDD-05")
eVacs("CUT-04(DS)").after("ITP-06")
eVacs("CUT-05(DS)").after("CUT-04(DS)")
eVacs("CUT-05(DS)").after("STP-06")
eVacs("CUT-06(DS)").after("CUT-05(DS)")
eVacs("CUT-07(DS)").after("CUT-06(DS)")
eVacs("CUT-08(DS)").after("CUT-07(DS)")

eVacs("AT-01(EV)").after("CUT-08(EV)")
eVacs("AT-01(EV)").after("ATP-08")
eVacs("AT-02(EV)").after("AT-01(EV)")
eVacs("AT-03(EV)").after("AT-02(EV)")
eVacs("AT-04(EV)").after("AT-03(EV)")
eVacs("AT-05(EV)").after("AT-04(EV)")

eVacs("AT-01(CS)").after("CUT-08(CS)")
eVacs("AT-01(CS)").after("ATP-08")
eVacs("AT-02(CS)").after("AT-01(CS)")
eVacs("AT-03(CS)").after("AT-02(CS)")
eVacs("AT-04(CS)").after("AT-03(CS)")
eVacs("AT-05(CS)").after("AT-04(CS)")

eVacs("AT-01(DS)").after("CUT-08(DS)")
eVacs("AT-01(DS)").after("ATP-08")
eVacs("AT-02(DS)").after("AT-01(DS)")
eVacs("AT-03(DS)").after("AT-02(DS)")
eVacs("AT-04(DS)").after("AT-03(DS)")
eVacs("AT-05(DS)").after("AT-04(DS)")