from django.shortcuts import render_to_response
from django.http import HttpResponse, HttpResponseRedirect
import active_directory as ad
import util
from django.views.decorators.cache import cache_page
from django.contrib.auth.decorators import login_required

CACHE_DURATION = 60*15

@cache_page(CACHE_DURATION)
def home(request):
    return render_to_response('home.html', {})

def test123(request):
    if not request.POST.get('action',None):
        return render_to_response('updateCC.html', {})

    import shutil
    shutil.copy("\\\\ppp-intranet\\CostCenterSummary\\SummaryCostCenter2007_Prod.xls", "\\\\ppp-utilities\\temp$\\")
    con,cur = util.getCursor(for_update=True)

    try:
        sql = """
CREATE TABLE [dbo].[CCSupvtemp] (
	[CostCenter] [varchar] (5) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Description] [nvarchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[CC# Supervisor Name] [nvarchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[BadgeNo] [varchar] (10) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[Department] [nvarchar] (255) COLLATE SQL_Latin1_General_CP1_CI_AS NULL ,
	[DP] [int] NULL
) ON [PRIMARY]
"""
        cur.execute(sql)
    except:
        pass

    sql = "delete from CCSupvtemp"
    cur.execute(sql)

    sql = "insert into CCSupvtemp select * FROM OPENROWSET('Microsoft.Jet.OLEDB.4.0', 'Excel 8.0;Database=f:\\dody\\temp\\SummaryCostCenter2007_Prod.xls', 'SELECT [Cost Center] as CostCenter, Description, [CC# Supervisor Name], [Badge No#] as BadgeNo, Department, DP FROM [CC-Summary$]')"
    cur.execute(sql)

    #connect to onlineca

    cur2 = util.getCursorOnlineCA()
    #~ sql = "delete from CCSupv"
    #~ cur2.execute(sql)
    sql = "insert into CCSupv22 select * FROM OPENROWSET('MSDASQL', 'DRIVER={SQL Server};SERVER=ppp-UTILITIES\\TESTING;UID=sa;PWD=xxxxx', tempdb.dbo.ccsupvtemp)"
    cur2.execute(sql)

    #connect to globalservice
    #~ cur3 = util.getCursorGlobalService()
    #~ sql = "delete from CCSupv"
    #~ sql = "insert into MsCCSupervisor select * FROM OPENROWSET('MSDASQL', 'DRIVER={SQL Server};SERVER=ppp-UTILITIES\\TESTING;UID=sa;PWD=xxxxx', tempdb.dbo.ccsupvtemp)"

    return render_to_response('updateCCFinished.html', {})


@login_required
#~ @cache_page(CACHE_DURATION)
def user_detail(request, userid):
    users = [u for u in ad.AD_object("LDAP://jakarta2/dc=premier-global,dc=local").search(objectCategory='Person', objectClass='User', sAMAccountName=userid)]
    u = None
    if users:
        u = users[0]
    return render_to_response('ad/user_detail.html', {'user': u, 'vale_email': util.getValeEmail(u)})


@login_required
@cache_page(CACHE_DURATION)
def group_detail(request, groupid):
    group = ad.AD_object("LDAP://jakarta2/dc=premier-global,dc=local").find_group(groupid)
    return render_to_response('ad/group_detail.html', {'group': group})


@login_required
@cache_page(CACHE_DURATION)
def search_name(request, name = None):
    if not name:
        if request.method == "POST" and request.POST.get("name") != "":
            return HttpResponseRedirect("/ad/search/name/%s" % request.POST.get("name"))
        else:
            return render_to_response('ad/user_list.html', {})
    else:
        users = [u for u in ad.AD_object("LDAP://dc=premier-global,dc=local").search(objectCategory='Person', objectClass='User', displayName="*%s*" % name)]
        if not users:
            return render_to_response('ad/user_detail.html', {'user': users})
        elif len(users) == 1:
            return HttpResponseRedirect("/ad/user/%s" % users[0].sAMAccountName)
        else:
            return render_to_response('ad/user_list.html', {'users': users})


@login_required
@cache_page(CACHE_DURATION)
def search_name_ell(request, name = None):
    if not name:
        if request.method == "POST" and request.POST.get("name") != "":
            return HttpResponseRedirect("/ell/search/name/%s" % request.POST.get("name"))
        else:
            return render_to_response('ad/ell_user_list.html', {})
    else:
        sqlora = """
        select a.formatted_name, a.employee_id
        from msv810 a
        left join msv020 b
            on a.employee_id = b.employee_id
        where a.formatted_name like '%%%s%%'
        """ % name.upper()
        cur = util.getCursor()
        cur.execute(util.sqlToOpenQuery(sqlora))
        users = cur.fetchall()

        if not users:
            return render_to_response('ad/ell_emp_detail.html', {'emp': users})
        elif len(users) == 1:
            return HttpResponseRedirect("/ell/emp/%s/" % users[0][1])
        else:
            return render_to_response('ad/ell_user_list.html', {'users': users})

@login_required
@cache_page(CACHE_DURATION)
def ell_emp_detail_notfound(request, empid):
    cur = util.getCursor()

    sqlora = """
    select a.formatted_name, a.employee_id
    from msv810 a
    left join msv020 b
        on a.employee_id = b.employee_id
    where a.employee_id like '%%%s%%'
    """ % empid.upper()

    cur.execute(util.sqlToOpenQuery(sqlora))
    emps = cur.fetchall()

    if not emps:
        return render_to_response('ad/ell_emp_detail.html', {'emp': emps})
    elif len(emps) == 1:
        # redirect
        return HttpResponseRedirect("/ell/emp/%s" % emps[0][1])
    else:
        # display list of valid badge num (and name) to pick
        return render_to_response('ad/ell_emp_list.html', {'emps': emps})


@login_required
@cache_page(CACHE_DURATION)
def ell_emp_detail(request, empid):
    cur = util.getCursor()

    sqlora = """
    select a.formatted_name, a.employee_id, trim(a.email_address), trim(a.home_phone_no), trim(a.mobile_no), trim(b.entity), c.work_loc, c.expat_ind, c.emp_status
    from msv810 a
    left join msv020 b
        on a.employee_id = b.employee_id
    left join msv760 c
        on a.employee_id = c.employee_id
    where a.employee_id='%s'
    """ % empid

    cur.execute(util.sqlToOpenQuery(sqlora))
    emp = cur.fetchone()

    if not emp:
        return ell_emp_detail_notfound(request, empid)

    #current position
    sqlora = """
    select c.position_id, d.pos_title, c.primary_pos, c.inv_str_date, c.pos_stop_date
    from msv878 c
    left join msv870 d
        on c.position_id = d.position_id
    where c.employee_id='%s'
        and c.inv_str_date <= to_char(sysdate,'YYYYMMDD')
        and (c.pos_stop_date >= to_char(sysdate,'YYYYMMDD') or pos_stop_date = '00000000')
    order by c.primary_pos, c.inv_str_date desc
    """ % empid

    cur.execute(util.sqlToOpenQuery(sqlora))
    currpos = cur.fetchall()

    #pos history
    sqlora = """
    select c.position_id, d.pos_title, c.primary_pos, c.inv_str_date, c.pos_stop_date
    from msv878 c
    left join msv870 d
        on c.position_id = d.position_id
    where c.employee_id='%s'
        and c.inv_str_date <= to_char(sysdate,'YYYYMMDD')
        and c.pos_stop_date < to_char(sysdate,'YYYYMMDD')
        and pos_stop_date != '00000000'
    order by c.inv_str_date desc
    """ % empid

    cur.execute(util.sqlToOpenQuery(sqlora))
    poshist = cur.fetchall()


    #get doa info from sharepoint @ ppp-intranet2
    #bn used there for number bn type is in short mode (9665 vs 0000009665)

    # current/future delegate to
    sql = """
    SELECT
    nvarchar2 AS DELEGATEFROM,
    nvarchar3 AS BN,
    --RIGHT('0000000000' + nvarchar3, 10) AS BN,
    --nvarchar4 AS JOBTITLE,
    nvarchar5 AS DELEGATEDNAME,
    nvarchar6 AS DELEGATETOBN,
    --RIGHT('0000000000' + nvarchar6, 10) AS DELEGATEDBN,
    --ntext2 AS REASON,
    --datetime1 AS DATEREQUEST,
    datetime2 AS DATEEFFECTIVE,
    datetime3 AS DATEEND
    --tp_Created AS DATECREATED,
    --tp_Modified AS DATEMMODIFIED,
    --tp_ListId AS LISTID
    FROM [ppp-SQL01].WSS_pppINTRANETWSS_PROD.dbo.UserData
    WHERE
    tp_ListId = '{24206258-7FCE-4E4C-8B2E-91FC055E2E10}'
    and nvarchar3 like '%%%s%%'
    and datetime3 >= CONVERT(nvarchar(10), GETDATE(), 101)
    order by datetime2 desc
    """ % util.bnLong2Short(empid)

    cur.execute(sql)
    currdelegateto = cur.fetchall()

    # current/future delegating
    sql = """
    SELECT
    nvarchar2 AS DELEGATEFROM,
    nvarchar3 AS BN,
    --RIGHT('0000000000' + nvarchar3, 10) AS BN,
    --nvarchar4 AS JOBTITLE,
    nvarchar5 AS DELEGATEDNAME,
    nvarchar6 AS DELEGATETOBN,
    --RIGHT('0000000000' + nvarchar6, 10) AS DELEGATEDBN,
    --ntext2 AS REASON,
    --datetime1 AS DATEREQUEST,
    datetime2 AS DATEEFFECTIVE,
    datetime3 AS DATEEND
    --tp_Created AS DATECREATED,
    --tp_Modified AS DATEMMODIFIED,
    --tp_ListId AS LISTID
    FROM [ppp-SQL01].WSS_pppINTRANETWSS_PROD.dbo.UserData
    WHERE
    tp_ListId = '{24206258-7FCE-4E4C-8B2E-91FC055E2E10}'
    and nvarchar6 like '%%%s%%'
    and datetime3 >= CONVERT(nvarchar(10), GETDATE(), 101)
    order by datetime2 desc
    """ % util.bnLong2Short(empid)

    cur.execute(sql)
    currdelegating = cur.fetchall()


    # delegation history
    sql = """
    SELECT
    nvarchar3 AS BN,
    nvarchar5 AS DELEGATEDNAME,
    nvarchar6 AS DELEGATETOBN,
    datetime2 AS DATEEFFECTIVE,
    datetime3 AS DATEEND
    FROM [ppp-SQL01].WSS_pppINTRANETWSS_PROD.dbo.UserData
    WHERE
    tp_ListId = '{24206258-7FCE-4E4C-8B2E-91FC055E2E10}'
    and (nvarchar3 like '%%%s%%')
    and datetime3 < CONVERT(nvarchar(10), GETDATE(), 101)
    order by datetime2 desc
    """ % util.bnShort2Long(empid)

    cur.execute(sql)
    doahist = cur.fetchall()

    return render_to_response('ad/ell_emp_detail.html', {
        'emp': emp,
        'currpos': currpos,
        'poshist': poshist,
        'currdelegateto': currdelegateto,
        'currdelegating': currdelegating,
        })


@login_required
@cache_page(CACHE_DURATION)
def ell_pos_detail(request, posid):
    cur = util.getCursor()

    # position
    sqlora = """
    select d.position_id, d.pos_title, d.occup_status, d.posn_status, d.pos_stat_date, z.table_desc
    from msv870 d
    left join msv010 z
        on d.posn_status = z.table_code
        and z.table_type = 'PST'
    where d.position_id ='%s'
    """ % posid

    cur.execute(util.sqlToOpenQuery(sqlora))
    pos = list(cur.fetchone())

    occdesc = dict(O='OCCUPIED', D='DELETED', V='VACANT')
    pos[2] = occdesc[pos[2]]


    #current position
    sqlora = """
    select c.employee_id, g.formatted_name, c.primary_pos, c.inv_str_date, c.pos_stop_date
    from msv878 c
    left join msv810 g
        on c.employee_id = g.employee_id
    where c.position_id = '%s'
        and c.inv_str_date <= to_char(sysdate,'YYYYMMDD')
        and (c.pos_stop_date >= to_char(sysdate,'YYYYMMDD') or pos_stop_date = '00000000')
    order by c.primary_pos, c.inv_str_date desc
    """ % posid

    cur.execute(util.sqlToOpenQuery(sqlora))
    currpos = cur.fetchall()


    #pos history
    sqlora = """
    select c.employee_id, g.formatted_name, c.primary_pos, c.inv_str_date, c.pos_stop_date
    from msv878 c
    left join msv810 g
        on c.employee_id = g.employee_id
    where c.position_id ='%s'
        and c.inv_str_date <= to_char(sysdate,'YYYYMMDD')
        and c.pos_stop_date < to_char(sysdate,'YYYYMMDD')
        and pos_stop_date != '00000000'
    order by c.inv_str_date desc
    """ % posid

    cur.execute(util.sqlToOpenQuery(sqlora))
    poshist = cur.fetchall()

    #pos structure
    sqlora = """
    select level, a.position_id, d.pos_title, a.actual_encumbs
    from msv875 a
    left join msv870 d
        on a.position_id = d.position_id
    where a.hier_version = '004'
    connect by prior a.superior_id = a.position_id
    start with a.position_id = '%s'
    """ % posid

    cur.execute(util.sqlToOpenQuery(sqlora))
    posstruct = cur.fetchall()

    #pos subordinates
    sqlora = """
    select a.position_id, d.pos_title, a.actual_encumbs
    from msv875 a
    left join msv870 d
        on a.position_id = d.position_id
    where a.hier_version = '004'
        and a.superior_id = '%s'
    """ % posid

    cur.execute(util.sqlToOpenQuery(sqlora))
    possubordinate = cur.fetchall()

    return render_to_response('ad/ell_pos_detail.html', {
        'pos': pos,
        'currpos': currpos,
        'poshist': poshist,
        'posstruct': posstruct,
        'possubordinate': possubordinate,
            })

@login_required
def clear_cache(request):
    util.clearDjangoCache()
    return render_to_response('cache_cleared.html')

@cache_page(CACHE_DURATION)
def email_to_vale(request, emailaddr):
    users = [u for u in ad.AD_object("LDAP://jakarta2/dc=premier-global,dc=local").search(objectCategory='Person', objectClass='User', mail=emailaddr)]
    if users:
        return HttpResponse(util.getValeEmail(users[0]))

