from nspk.main.models import *
from django.contrib import admin

admin.site.register(Wilayah)
admin.site.register(Balai)
admin.site.register(Propinsi)
admin.site.register(Snvt)
admin.site.register(Proyek)
admin.site.register(Ppk)
admin.site.register(Divisi)
admin.site.register(Indikator)
admin.site.register(Seksi)
admin.site.register(Judul)

class RespondenAdmin(admin.ModelAdmin):
    date_hierarchy = 'created_date'

admin.site.register(Responden,RespondenAdmin)