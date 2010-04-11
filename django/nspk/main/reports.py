import sys
from django.shortcuts import render_to_response
from django.http import HttpResponse, HttpResponseRedirect
from django.views.decorators.cache import cache_page
import models, bforms
from util import blank2None

def apply_filter(request, sql):
    param = []
    replace_string = {"filter": ""}
    filter_by = request.POST.get('filter', None)
    if filter_by == "wilayah":
        replace_string = {"filter": "AND k.wilayah_id = %s"}
        param.append(request.POST['wilayah'])
    elif filter_by == "balai":
        replace_string = {"filter": "AND k.balai_id = %s"}
        param.append(request.POST['balai'])
    elif filter_by == "propinsi":
        replace_string = {"filter": "AND k.propinsi_id = %s"}
        param.append(request.POST['propinsi'])

    return sql % replace_string, param

def main(request):
    payload = dict(
        wilayahs = models.Wilayah.objects.all(),
        wilayah_key = request.POST.get('wilayah',''),
        balais = models.Balai.objects.all(),
        balai_key = request.POST.get('balai',''),
        propinsis = models.Propinsi.objects.all(),
        propinsi_key = request.POST.get('propinsi',''),
        filter = request.POST.get('filter',''),
        )

    if request.method == 'POST':
        payload['report_type'] = request.POST['report_type']
        payload['group'] = request.POST['group']

        from django.db import connection
        cursor = connection.cursor()

        if request.POST['report_type'] == 'detail':
            if request.POST['group'] == 'judul':
                sql = """
                SELECT
                    a.id,
                    a.code,
                    a.name,
                    100*sum(case jawab1 when 1 then 1 else 0 end)/count(jawab1) as jawab1_1,
                    100*sum(case jawab1 when 2 then 1 else 0 end)/count(jawab1) as jawab1_2,
                    100*sum(case jawab1 when 0 then 1 else 0 end)/count(jawab1) as jawab1_0,
                    100*sum(case jawab2 when 1 then 1 else 0 end)/count(jawab1) as jawab2_1,
                    100*sum(case jawab2 when 2 then 1 else 0 end)/count(jawab1) as jawab2_2,
                    100*sum(case jawab2 when 0 then 1 else 0 end)/count(jawab1) as jawab2_0,
                    100*sum(case jawab3 when 1 then 1 else 0 end)/count(jawab1) as jawab3_1,
                    100*sum(case jawab3 when 2 then 1 else 0 end)/count(jawab1) as jawab3_2,
                    100*sum(case jawab3 when 3 then 1 else 0 end)/count(jawab1) as jawab3_3,
                    100*sum(case jawab3 when 0 then 1 else 0 end)/count(jawab1) as jawab3_0,
                    100*sum(case jawab4 when 1 then 1 else 0 end)/count(jawab1) as jawab4_1,
                    100*sum(case jawab4 when 2 then 1 else 0 end)/count(jawab1) as jawab4_2,
                    100*sum(case jawab4 when 3 then 1 else 0 end)/count(jawab1) as jawab4_3,
                    100*sum(case jawab4 when 0 then 1 else 0 end)/count(jawab1) as jawab4_0,
                    100*sum(case jawab5 when 1 then 1 else 0 end)/count(jawab1) as jawab5_1,
                    100*sum(case jawab5 when 2 then 1 else 0 end)/count(jawab1) as jawab5_2,
                    100*sum(case jawab5 when 3 then 1 else 0 end)/count(jawab1) as jawab5_3,
                    100*sum(case jawab5 when 0 then 1 else 0 end)/count(jawab1) as jawab5_0,
                    100*sum(case jawab6 when 1 then 1 else 0 end)/count(jawab1) as jawab6_1,
                    100*sum(case jawab6 when 2 then 1 else 0 end)/count(jawab1) as jawab6_2,
                    100*sum(case jawab6 when 3 then 1 else 0 end)/count(jawab1) as jawab6_3,
                    100*sum(case jawab6 when 0 then 1 else 0 end)/count(jawab1) as jawab6_0,
                    100*sum(case jawab7 when 1 then 1 else 0 end)/count(jawab1) as jawab7_1,
                    100*sum(case jawab7 when 2 then 1 else 0 end)/count(jawab1) as jawab7_2,
                    100*sum(case jawab7 when 0 then 1 else 0 end)/count(jawab1) as jawab7_0
                FROM main_judul a
                -- the data
                LEFT JOIN (
                    SELECT
                        judul_id,
                        b.jawab1,b.jawab2,b.jawab3,b.jawab4,b.jawab5,b.jawab6,b.jawab7
                    FROM main_jawaban b, main_responden k
                    WHERE b.responden_id = k.id
                    %(filter)s
                    ) b
                    ON a.id = b.judul_id
                -- for ordering
                 LEFT JOIN main_seksi c
                    ON a.seksi_id = c.id
                GROUP BY a.id, a.code, a.name
                ORDER BY c.divisi_id, c.no, a.no
                """

            elif request.POST['group'] == 'seksi':
                sql = """
                SELECT
                    c.id,
                    c.name,
                    100*sum(case jawab1 when 1 then 1 else 0 end)/count(jawab1) as jawab1_1,
                    100*sum(case jawab1 when 2 then 1 else 0 end)/count(jawab1) as jawab1_2,
                    100*sum(case jawab1 when 0 then 1 else 0 end)/count(jawab1) as jawab1_0,
                    100*sum(case jawab2 when 1 then 1 else 0 end)/count(jawab1) as jawab2_1,
                    100*sum(case jawab2 when 2 then 1 else 0 end)/count(jawab1) as jawab2_2,
                    100*sum(case jawab2 when 0 then 1 else 0 end)/count(jawab1) as jawab2_0,
                    100*sum(case jawab3 when 1 then 1 else 0 end)/count(jawab1) as jawab3_1,
                    100*sum(case jawab3 when 2 then 1 else 0 end)/count(jawab1) as jawab3_2,
                    100*sum(case jawab3 when 3 then 1 else 0 end)/count(jawab1) as jawab3_3,
                    100*sum(case jawab3 when 0 then 1 else 0 end)/count(jawab1) as jawab3_0,
                    100*sum(case jawab4 when 1 then 1 else 0 end)/count(jawab1) as jawab4_1,
                    100*sum(case jawab4 when 2 then 1 else 0 end)/count(jawab1) as jawab4_2,
                    100*sum(case jawab4 when 3 then 1 else 0 end)/count(jawab1) as jawab4_3,
                    100*sum(case jawab4 when 0 then 1 else 0 end)/count(jawab1) as jawab4_0,
                    100*sum(case jawab5 when 1 then 1 else 0 end)/count(jawab1) as jawab5_1,
                    100*sum(case jawab5 when 2 then 1 else 0 end)/count(jawab1) as jawab5_2,
                    100*sum(case jawab5 when 3 then 1 else 0 end)/count(jawab1) as jawab5_3,
                    100*sum(case jawab5 when 0 then 1 else 0 end)/count(jawab1) as jawab5_0,
                    100*sum(case jawab6 when 1 then 1 else 0 end)/count(jawab1) as jawab6_1,
                    100*sum(case jawab6 when 2 then 1 else 0 end)/count(jawab1) as jawab6_2,
                    100*sum(case jawab6 when 3 then 1 else 0 end)/count(jawab1) as jawab6_3,
                    100*sum(case jawab6 when 0 then 1 else 0 end)/count(jawab1) as jawab6_0,
                    100*sum(case jawab7 when 1 then 1 else 0 end)/count(jawab1) as jawab7_1,
                    100*sum(case jawab7 when 2 then 1 else 0 end)/count(jawab1) as jawab7_2,
                    100*sum(case jawab7 when 0 then 1 else 0 end)/count(jawab1) as jawab7_0
                FROM main_seksi c
                LEFT JOIN main_judul a
                    ON c.id = a.seksi_id
                -- the data
                LEFT JOIN (
                    SELECT
                        judul_id,
                        b.jawab1,b.jawab2,b.jawab3,b.jawab4,b.jawab5,b.jawab6,b.jawab7
                    FROM main_jawaban b, main_responden k
                    WHERE b.responden_id = k.id
                    %(filter)s
                    ) b
                    ON a.id = b.judul_id
                GROUP BY c.id, c.name
                ORDER BY c.divisi_id, c.no
                """


            elif request.POST['group'] == 'divisi':
                sql = """
                SELECT
                    d.id,
                    d.name,
                    100*sum(case jawab1 when 1 then 1 else 0 end)/count(jawab1) as jawab1_1,
                    100*sum(case jawab1 when 2 then 1 else 0 end)/count(jawab1) as jawab1_2,
                    100*sum(case jawab1 when 0 then 1 else 0 end)/count(jawab1) as jawab1_0,
                    100*sum(case jawab2 when 1 then 1 else 0 end)/count(jawab1) as jawab2_1,
                    100*sum(case jawab2 when 2 then 1 else 0 end)/count(jawab1) as jawab2_2,
                    100*sum(case jawab2 when 0 then 1 else 0 end)/count(jawab1) as jawab2_0,
                    100*sum(case jawab3 when 1 then 1 else 0 end)/count(jawab1) as jawab3_1,
                    100*sum(case jawab3 when 2 then 1 else 0 end)/count(jawab1) as jawab3_2,
                    100*sum(case jawab3 when 3 then 1 else 0 end)/count(jawab1) as jawab3_3,
                    100*sum(case jawab3 when 0 then 1 else 0 end)/count(jawab1) as jawab3_0,
                    100*sum(case jawab4 when 1 then 1 else 0 end)/count(jawab1) as jawab4_1,
                    100*sum(case jawab4 when 2 then 1 else 0 end)/count(jawab1) as jawab4_2,
                    100*sum(case jawab4 when 3 then 1 else 0 end)/count(jawab1) as jawab4_3,
                    100*sum(case jawab4 when 0 then 1 else 0 end)/count(jawab1) as jawab4_0,
                    100*sum(case jawab5 when 1 then 1 else 0 end)/count(jawab1) as jawab5_1,
                    100*sum(case jawab5 when 2 then 1 else 0 end)/count(jawab1) as jawab5_2,
                    100*sum(case jawab5 when 3 then 1 else 0 end)/count(jawab1) as jawab5_3,
                    100*sum(case jawab5 when 0 then 1 else 0 end)/count(jawab1) as jawab5_0,
                    100*sum(case jawab6 when 1 then 1 else 0 end)/count(jawab1) as jawab6_1,
                    100*sum(case jawab6 when 2 then 1 else 0 end)/count(jawab1) as jawab6_2,
                    100*sum(case jawab6 when 3 then 1 else 0 end)/count(jawab1) as jawab6_3,
                    100*sum(case jawab6 when 0 then 1 else 0 end)/count(jawab1) as jawab6_0,
                    100*sum(case jawab7 when 1 then 1 else 0 end)/count(jawab1) as jawab7_1,
                    100*sum(case jawab7 when 2 then 1 else 0 end)/count(jawab1) as jawab7_2,
                    100*sum(case jawab7 when 0 then 1 else 0 end)/count(jawab1) as jawab7_0
                FROM main_divisi d
                LEFT JOIN main_seksi c
                    ON d.id = c.divisi_id
                LEFT JOIN main_judul a
                    ON c.id = a.seksi_id
                LEFT JOIN (
                    SELECT
                        judul_id,
                        b.jawab1,b.jawab2,b.jawab3,b.jawab4,b.jawab5,b.jawab6,b.jawab7
                    FROM main_jawaban b, main_responden k
                    WHERE b.responden_id = k.id
                    %(filter)s
                ) b
                    ON a.id = b.judul_id
                GROUP BY d.id, d.name"""

            sql, param = apply_filter(request, sql)
            print >> sys.stderr, sql
            cursor.execute(sql, param)
            jawabs = cursor.fetchall()

        else:
            if request.POST['group'] == 'judul':
                sql = """
                SELECT
                    a.id,
                    a.code,
                    a.name,
                    AVG(b.Skor) Skor,
                    AVG(b.Pctg) Pctg
                FROM main_judul a
                -- the data
                LEFT JOIN (
                    SELECT
                        judul_id,
                        b.jawab1*b.jawab2* b.jawab3* b.jawab4* b.jawab5* b.jawab6* b.jawab7 Skor,
                        100*b.jawab1*b.jawab2* b.jawab3* b.jawab4* b.jawab5* b.jawab6* b.jawab7/648 Pctg
                    FROM main_jawaban b, main_responden k
                    WHERE b.responden_id = k.id
                    %(filter)s
                    ) b
                    ON a.id = b.judul_id
                -- for ordering
                 LEFT JOIN main_seksi c
                    ON a.seksi_id = c.id

                GROUP BY a.id, a.code, a.name
                ORDER BY c.divisi_id, c.no, a.no
                """

            elif request.POST['group'] == 'seksi':
                sql = """
                SELECT
                    c.id,
                    c.name,
                    AVG(b.Skor) Skor,
                    AVG(b.Pctg) Pctg
                FROM main_seksi c
                LEFT JOIN main_judul a
                    ON c.id = a.seksi_id
                -- the data
                LEFT JOIN (
                    SELECT
                        judul_id,
                        b.jawab1*b.jawab2* b.jawab3* b.jawab4* b.jawab5* b.jawab6* b.jawab7 Skor,
                        100*b.jawab1*b.jawab2* b.jawab3* b.jawab4* b.jawab5* b.jawab6* b.jawab7/648 Pctg
                    FROM main_jawaban b, main_responden k
                    WHERE b.responden_id = k.id
                    %(filter)s
                    ) b
                    ON a.id = b.judul_id
                GROUP BY c.id, c.name
                ORDER BY c.divisi_id, c.no
                """

            elif request.POST['group'] == 'divisi':
                sql = """
                SELECT
                    d.id,
                    d.name,
                    AVG(b.Skor) Skor,
                    AVG(b.Pctg) Pctg
                FROM main_divisi d
                LEFT JOIN main_seksi c
                    ON d.id = c.divisi_id
                LEFT JOIN main_judul a
                    ON c.id = a.seksi_id
                -- the data
                LEFT JOIN (
                    SELECT
                        judul_id,
                        b.jawab1*b.jawab2* b.jawab3* b.jawab4* b.jawab5* b.jawab6* b.jawab7 Skor,
                        100*b.jawab1*b.jawab2* b.jawab3* b.jawab4* b.jawab5* b.jawab6* b.jawab7/648 Pctg
                    FROM main_jawaban b, main_responden k
                    WHERE b.responden_id = k.id
                    %(filter)s
                    ) b
                    ON a.id = b.judul_id
                GROUP BY d.id, d.name"""

            sql, param = apply_filter(request, sql)

            print >> sys.stderr, sql
            cursor.execute(sql, param)

            jawabs = []
            for row in cursor.fetchall():
                if row[-1] is None:
                    pctg = 0
                else:
                    pctg = float(row[-1])
                mutu = ""
                # import pdb; pdb.set_trace();
                if pctg > 75:
                    mutu = "A"
                elif 50 < pctg <= 75:
                    mutu = "B"
                elif 25 < pctg <= 50:
                    mutu = "C"
                else:
                    mutu = "D"

                jawabs.append(dict(row=row, mutu=mutu))

        payload['jawabs'] = jawabs

    return render_to_response('report_main.html', payload)

def global_report(request):
    payload = dict()
    return render_to_response('report_global.html', payload)