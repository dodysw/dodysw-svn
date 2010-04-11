from django.shortcuts import render_to_response
from django.http import HttpResponseRedirect
import util
import active_directory as ad
from django.db import backend
from django.contrib.auth.decorators import login_required

@login_required
def meal_request(request, empid):
    cur = util.getCursorCRS()

    sql = """
    select RequestNo, RequestDate, RequesterBadgeNo, RequestType, RequestStatus
    from MealRequest a
    where a.RequesterBadgeNo = '%s'
    order by RequestDate desc
    """ % empid.upper()

    cur.execute(sql)
    rows = cur.fetchall()

    return render_to_response('ad/meal_request_list.html', {'rows': rows})

@login_required
def expense_claim(request, empid):
    cur = util.getCursorOnlineCA()

    sql = """
    select ExpenseClaimNo, ExpenseClaimDate, RequesterBN, RequestForBN, Status
    from ExpenseClaimHDR a
    where a.RequestForBN = '%s'
    order by ExpenseClaimDate desc
    """ % empid.upper()

    cur.execute(sql)
    rows = cur.fetchall()

    return render_to_response('ad/expense_claim_list.html', {'rows': rows})

@login_required
def cash_advance(request, empid):
    cur = util.getCursorOnlineCA()

    sql = """
    select AdvanceNo, AdvanceDate, RequesterBN, RequestForBN, Status
    from CashAdvance a
    where a.RequestForBN = '%s'
    order by AdvanceDate desc
    """ % empid.upper()

    cur.execute(sql)
    prows = cur.fetchall()

    sql = """
    select AdvanceNo, AdvanceDate, RequesterBN, RequestForBN, Status
    from BusinessAdvance a
    where a.RequestForBN = '%s'
    order by AdvanceDate desc
    """ % empid.upper()

    cur.execute(sql)
    brows = cur.fetchall()

    return render_to_response('ad/cash_advance_list.html', {'prows': prows, 'brows': brows})

@login_required
def flight_request(request, empid):
    t = {}
    cur = util.getCursorFlight()

    #get bn's userid first
    sqlora = """
    select trim(entity)
    from msv020
    where employee_id = '%s'
    """ % empid
    curell = util.getCursor()
    curell.execute(util.sqlToOpenQuery(sqlora))
    emp = curell.fetchone()

    if emp:
        sql = """
        select a.ta_no, a.name, a.employer, a.approved, b.flightdate, b.flightfrom, b.flightto, b.flightstatus, a.user_session, a.reasondescription
        from flight_ta a
        left join flight_transaction b
            on a.ta_no = b.ta_no
        where a.createdby = '%s'
        order by b.flightdate desc
        """ % emp[0]
        cur.execute(sql)
        t['flight_created'] = cur.fetchall()

    sql = """
    select a.ta_no, a.name, a.employer, a.approved, b.flightdate, b.flightfrom, b.flightto, b.flightstatus, a.user_session, a.reasondescription
    from flight_ta a
    left join flight_transaction b
        on a.ta_no = b.ta_no
    where a.badge_no = '%s'
    order by b.flightdate desc
    """ % util.bnLong2Short(empid.upper())

    cur.execute(sql)
    t['myflight'] = cur.fetchall()


    sql = """
    select a.ta_no, a.name, a.employer, a.approved, b.flightdate, b.flightfrom, b.flightto, b.flightstatus, a.user_session, a.reasondescription
    from flight_ta a
    left join flight_transaction b
        on a.ta_no = b.ta_no
    where a.Approver_1_BadgeNo = '%s' or a.Approver_2_BadgeNo = '%s'
    order by b.flightdate desc
    """ % (empid.upper(), empid.upper())

    cur.execute(sql)
    t['flight_approved'] = cur.fetchall()

    return render_to_response('ad/flight_list.html', t)

@login_required
def ta_page(request, tano):
    t = {}
    rows = []
    tastatus = {}
    cur = util.getCursorFlight()

    sql = """
    select a.ta_no, approver_1, approver_2, approver_1_badgeno, approver_2_badgeno, a.approved, approver_1_name, approver_2_name, a.user_session, a.remarks
    from flight_ta a
    where a.ta_no like '%%%s%%'
    """ % tano
    cur.execute(sql)
    ro_tas = cur.fetchall()

    for row in ro_tas:
        ta = list(row)

        if ta[5] in ('2','3'):
            status = "%s (%s)" % (ta[9], ta[5])
        elif (ta[5] is None or ta[5] != '0') and not ta[1] and not ta[2]:
            status = 'Waiting for approval 1 (%s)' % ta[5]
        elif (ta[5] is None or ta[5] != '0') and ta[1] and not ta[2]:
            status = 'Waiting for approval 2 (%s)' % ta[5]
        elif ta[5] == '0':
            status = 'Approved'
        else:
            status = 'Unknown (%s)' % ta[5]
        ta[5] = status

        rows.append(ta)

    t['ta'] = rows
    t['tastatus'] = tastatus
    t['query'] = tano

    return render_to_response('ad/ta_page.html', t)

@login_required
def ec_page(request, ecno):
    t = {}
    rows = []
    tastatus = {}
    cur = util.getCursorOnlineCA()

    statusdesc = dict(
        D = "D - Draft",
        W1 = 'W1 - Waiting for supervisor approval',
        Z = "Z - Rejected",
        R2 = "R2 - Rejected by Finance Checker",
        P = "P - Paid",
        A3 = "A3 - Approved by finance",
        A2 = "A2 - Approved by finance checker",
        A1 = "A1 - Approved by supervisor",
        C1 = "C1 - Need Revision from supervisor",
        C2 = "C2 - Need Revision from Finance Checker"
        )


    sql = """
    select top 100 a.expenseclaimno, a.status, a.expenseclaimdate, a.requesterbn, a.requestforbn, a.requestforgivenname, a.isidr, a.isusd, a.fincheckerbn, a.fincheckerapprovaldate, a.financeapprovaldate, a.usercreated, a.datecreated
    from expenseclaimhdr a
    where a.expenseclaimno like '%%%s%%'
    order by a.expenseclaimdate
    """ % ecno
    cur.execute(sql)
    ro_rows = cur.fetchall()

    for row in ro_rows:
        ec = list(row)

        sql = """
        select a.supvbn, a.status, a.delegatedbn
        from expenseclaimsupvapp a
        where a.expenseclaimno = '%s'
        """ % ec[0]
        cur.execute(sql)
        rows2 = cur.fetchall()
        ec[2] = rows2
        if ec[1].strip() in statusdesc:
            ec[1] = statusdesc[ec[1].strip()]
        rows.append(ec)

    t['rows'] = rows
    t['query'] = ecno

    return render_to_response('ad/ec_page.html', t)

@login_required
def search_acbooking_by_ta(request, tano):
    cur = util.getCursorAccbooking()
    sql = """select ReservationId, SponsorName, SponsorEmail, VisitorName, SponsorAuth, SponsorApproveDate, TASAuth, TASApproveDate, TANumber
    from Acc_Master a
    where a.TANumber like '%%%s%%'
    """ % tano
    cur.execute(sql)
    t = {}
    t['rows'] = cur.fetchall()
    t['tano'] = tano

    return render_to_response('ad/accbooking_page.html', t)


@login_required
def ta_edit(request, tano):
    t = {}
    rows = []
    tastatus = {}
    cur = util.getCursorFlight()

    sql = """
    select a.ta_no, approver_1, approver_2, approver_1_badgeno, approver_2_badgeno, a.approved,
    approver_1_name, approver_2_name, a.user_session, a.remarks, last_changed_user,
    approver_1_email, approver_2_email
    from flight_ta a
    where a.ta_no like '%%%s%%'
    """ % tano
    cur.execute(sql)
    row = cur.fetchone()

    t['ta'] = row
    t['tastatus'] = tastatus
    t['query'] = tano

    t['submitenabled'] = False

    #~ raise Exception, row

    if row[5] in ('2','3'):
        status = "%s (%s)" % (row[9], row[5])
        t['submitenabled'] = True
    elif (row[5] is None or row[5] != '0') and not row[1] and not row[2]:
        status = 'Waiting for approval 1'
        t['submitenabled'] = True
    elif (row[5] is None or row[5] != '0') and row[1] and not row[2]:
        status = 'Waiting for approval 2'
        t['submitenabled'] = True
    elif row[5] == '0':
        status = 'Approved'
    else:
        status = 'Unknown (%s)' % row[5]


    t['status'] = status

    return render_to_response('ad/ta_edit.html', t)

@login_required
def ta_update(request, tano):
    t = {}
    rows = []
    tastatus = {}
    con, cur = util.getCursorFlight(for_update = True)

    #check status

    sql = """
    select a.ta_no, approver_1, approver_2, approver_1_badgeno, approver_2_badgeno, a.approved,
    approver_1_name, approver_2_name, a.user_session, a.remarks, last_changed_user,
    approver_1_email, approver_2_email
    from flight_ta a
    where a.ta_no like '%%%s%%'
    """ % tano
    cur.execute(sql)
    row = cur.fetchone()

    if row[5] == '0':
        raise Exception, "Status already approved"

    u1displayName = row[6]
    u2displayName = row[7]
    u1employeeId = row[3]
    u2employeeId = row[4]
    u1approver = request.POST['approver_1'] or row[1]
    if u1approver is not None:
        u1approver = str(u1approver)
    u2approver = request.POST['approver_2'] or row[2]
    if u2approver is not None:
        u2approver = str(u2approver)
    u1email = row[11]
    u2email = row[12]

    #resolve name and badgeno
    u1 = u2 = None

    if request.POST['approver_1_badgeno']:
        users = [u for u in ad.AD_object("LDAP://ppp.net").search(objectCategory='Person', objectClass='User', employeeId=request.POST['approver_1_badgeno'])]
        if not users:
            raise Exception, "Badge no %s not found" % request.POST['approver_1_badgeno']
        u1 = users[0]
        u1displayName = u1.displayName
        u1email = u1.mail

    if request.POST['approver_2_badgeno']:
        users = [u for u in ad.AD_object("LDAP://ppp.net").search(objectCategory='Person', objectClass='User', employeeId=request.POST['approver_2_badgeno'])]
        if not users:
            raise Exception, "Badge no %s not found" % request.POST['approver_2_badgeno']
        u2 = users[0]
        u2displayName = u2.displayName
        u2email = u2.mail

    remark = request.POST['remark']

    if request.POST.get('autoupdateremark', None):
        if not request.POST['approver_1']:
            remark = "Waiting Approval From " + u1displayName
        else:
            remark = "Waiting Approval From " + u2displayName


    sql = """
    update flight_ta set
        approver_1=%s, approver_1_name=%s, approver_1_badgeno=%s, approver_1_email=%s, approver_2=%s, approver_2_name=%s, approver_2_badgeno=%s, approver_2_email=%s, remarks=%s, last_changed_user=%s, last_changed_date=getdate()
    where ta_no = %s
    """

    param = [
        u1approver,
        str(u1displayName),
        str(request.POST['approver_1_badgeno']),
        str(u1email),
        u2approver,
        str(u2displayName),
        str(request.POST['approver_2_badgeno']),
        str(u2email),
        str(remark),
        str(request.POST['last_changed_user']),
        str(tano)]

    cur.execute(sql, param )

    con.commit()
    return HttpResponseRedirect("/ta/%s/edit/" % tano)
