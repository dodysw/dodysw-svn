import sys
from django.shortcuts import render_to_response
from django.http import HttpResponse, HttpResponseRedirect
from django.views.decorators.cache import cache_page
import models, bforms
from data_to_import import *
from util import blank2None
from django.contrib.auth.decorators import login_required

@login_required
def home(request):
    print >>sys.stderr, "HASIL:", request.POST.get("save","")
    if request.method == 'GET':
        respform = bforms.RespForm()
    if request.method == 'POST' and request.POST.get("save","") == "":        
        respform = bforms.RespForm(request.POST, error_class=bforms.HideErrorList)
        respform.fields['balai'].queryset = models.Balai.objects.filter(wilayah=respform.data['wilayah'])
        respform.fields['propinsi'].queryset = models.Propinsi.objects.filter(balai=respform.data['balai'])
        respform.fields['proyek'].queryset = models.Proyek.objects.filter(propinsi=respform.data['propinsi'])
        respform.fields['ppk'].queryset = models.Ppk.objects.filter(tahun=respform.data['tahun'], snvt=respform.data['snvt'], propinsi=respform.data['propinsi'], proyek=blank2None(respform.data['proyek']))
        #import pdb; pdb.set_trace()
    if request.method == 'POST' and request.POST.get("save","") != "":
        # todo, validate data here first!
        respform = bforms.RespForm(request.POST)
        if respform.is_valid():
            q = models.Responden.objects.filter(
                tahun=respform.data['tahun'],
                wilayah=blank2None(respform.data['wilayah']),
                balai=blank2None(respform.data['balai']),
                propinsi=blank2None(respform.data['propinsi']),
                snvt=blank2None(respform.data['snvt']),
                proyek=blank2None(respform.data['proyek']),
                ppk=blank2None(respform.data['ppk']),
                posisi=respform.data['posisi'],
                name=respform.data['name'],)
            print >>sys.stderr, q
            if q.count() == 0:
                resp = respform.save()
                # if no jawab exist, then recreate jawaban template
                if models.Jawaban.objects.filter(responden=resp).count() == 0:
                    for judul in models.Judul.objects.all():
                        models.Jawaban(responden=resp, judul=judul).save()
            else:
                resp = q[0]
            return HttpResponseRedirect(resp.get_absolute_url())
        else:
            pass
    payload = dict(respform = respform)
    return render_to_response('home.html', payload)

def resp_detail(request, resp_key):
    return HttpResponseRedirect("/resp/%s/%s/" % (resp_key, models.Divisi.objects.all()[0].id))

def str2int(buff):
    if buff == '':
        return 0
    else:
        return int(buff)


def resp_detail_div(request, resp_key, div_key):
    if request.method == 'GET':
        resp = models.Responden.objects.get(id=resp_key)
        divisi = models.Divisi.objects.get(id=div_key)
        payload = dict(
            resp = resp,
            jawabans = models.Jawaban.objects.filter(responden = resp).filter(judul__seksi__divisi =divisi).order_by('judul'),
            divisis = models.Divisi.objects.all(),
            div_key = div_key,
            tahu_tidaktahu = models.TAHU_TIDAKTAHU,
            sudah_belum = models.SUDAH_BELUM,
            sudah_sedang_belum = models.SUDAH_SEDANG_BELUM,
            mudah_sedang_sulit = models.MUDAH_SEDANG_SULIT,
            paham_cukup_kurang = models.PAHAM_CUKUP_KURANG,
            )
        return render_to_response('detail.html', payload)
    if request.method == 'POST':
        #save first
        for jawab in request.POST.getlist('jawabkeys'):
            obj = models.Jawaban.objects.get(id=jawab)
            obj.jawab1 = str2int(request.POST.get("row_%s_1" % jawab, 0))
            obj.jawab2 = str2int(request.POST.get("row_%s_2" % jawab, 0))
            obj.jawab3 = str2int(request.POST.get("row_%s_3" % jawab, 0))
            obj.jawab4 = str2int(request.POST.get("row_%s_4" % jawab, 0))
            obj.jawab5 = str2int(request.POST.get("row_%s_5" % jawab, 0))
            obj.jawab6 = str2int(request.POST.get("row_%s_6" % jawab, 0))
            obj.jawab7 = str2int(request.POST.get("row_%s_7" % jawab, 0))
            obj.save()

        #then load the new div key
        return HttpResponseRedirect("/resp/%s/%s/" % (resp_key, request.POST['divisi']))



def resetdata(request):
    models.Wilayah.objects.all().delete()
    for x in wilayah.split('\n'):
        x = x.strip()
        if x == "":
            continue
        x = x.split('\t')
        models.Wilayah(id=x[0], name=x[1]).save()

    models.Divisi.objects.all().delete()
    for x in divisi.split('\n'):
        x = x.strip()
        if x == "":
            continue
        x = x.split('\t')
        models.Divisi(id=x[0], name=x[1]).save()

    models.Seksi.objects.all().delete()
    for x in seksi.split('\n'):
        x = x.strip()
        if x == "":
            continue
        x = x.split('\t')
        models.Seksi(id=x[0], divisi=models.Divisi.objects.get(id=x[1]), no=int(x[2]), name=x[3]).save()

    models.Balai.objects.all().delete()
    for x in balai.split('\n'):
        x = x.strip()
        if x == "":
            continue
        x = x.split('\t')
        models.Balai(id=x[0], wilayah=models.Wilayah.objects.get(id=x[1]), name=x[2]).save()

    models.Propinsi.objects.all().delete()
    for x in propinsi.split('\n'):
        x = x.strip()
        if x == "":
            continue
        x = x.split('\t')
        models.Propinsi(id=x[0], balai=models.Balai.objects.get(id=x[1]), name=x[2]).save()

    models.Snvt.objects.all().delete()
    for x in snvt.split('\n'):
        x = x.strip()
        if x == "":
            continue
        x = x.split('\t')
        models.Snvt(id=x[0], name=x[1]).save()

    models.Proyek.objects.all().delete()
    for x in proyek.split('\n'):
        x = x.strip()
        if x == "":
            continue
        x = x.split('\t')
        models.Proyek(id=x[0], propinsi=models.Propinsi.objects.get(id=x[1]), name=x[2]).save()

    models.Ppk.objects.all().delete()
    for x in ppk.split('\n'):
        x = x.strip()
        if x == "":
            continue
        x = x.split('\t')

        ppkrow = models.Ppk(id=x[0],
            tahun=int(x[1]),
            snvt=models.Snvt.objects.get(id=x[2]),
            propinsi=models.Propinsi.objects.get(id=x[3]),
            name=x[5],
            )

        try: 
            ppkrow.proyek = models.Proyek.objects.get(id=x[4])
        except models.Proyek.DoesNotExist:
            pass

        ppkrow.save()

    models.Judul.objects.all().delete()
    for x in judul.split('\n'):
        x = x.strip()
        if x == "":
            continue
        x = x.split('\t')
        models.Judul(id=x[0], seksi=models.Seksi.objects.get(id=x[1]), no=int(x[2]), code=x[3], name=x[4]).save()

    return HttpResponseRedirect('/')