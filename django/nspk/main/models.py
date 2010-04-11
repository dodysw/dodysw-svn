from django.db import models
from django.contrib.auth.models import User

YEARS = (
    ['2008','2008'],
    ['2009','2009'],
    ['2010','2010'],
    ['2011','2011'],
    ['2012','2012'],
    ['2013','2013'],
    ['2014','2014'],
    ['2015','2015'],
    
)

TAHU_TIDAKTAHU = [1,"Tahu"],[2,"Tidak Tahu"]
SUDAH_BELUM = [1,"Sudah"],[2,"Belum"]
SUDAH_SEDANG_BELUM = [1,"Sudah"],[2,"Sedang"],[3,"Belum"],
MUDAH_SEDANG_SULIT = [1,"Mudah"],[2,"Sedang"],[3,"Sulit"],
PAHAM_CUKUP_KURANG = [1,"Paham"],[2,"Cukup"],[3,"Kurang"],

class Wilayah(models.Model):
    id = models.CharField(primary_key=True, max_length=10)
    name = models.CharField(max_length=100)
    def __str__(self):
        return '%s' %self.name

class Balai(models.Model):
    id = models.CharField(primary_key=True, max_length=10)
    wilayah = models.ForeignKey(Wilayah)
    name = models.CharField(max_length=100)
    def __str__(self):
        return '%s' %self.name

class Propinsi(models.Model):
    id = models.CharField(primary_key=True, max_length=10)
    balai = models.ForeignKey(Balai)
    name = models.CharField(max_length=100)
    def __str__(self):
        return '%s' %self.name

class Snvt(models.Model):
    id = models.CharField(primary_key=True, max_length=10)
    name = models.CharField(max_length=100)
    def __str__(self):
        return '%s' %self.name

class Proyek(models.Model):
    id = models.CharField(primary_key=True, max_length=10)
    propinsi = models.ForeignKey(Propinsi)
    name = models.CharField(max_length=100)
    def __str__(self):
        return '%s' %self.name

class Ppk(models.Model):
    id = models.CharField(primary_key=True, max_length=10)
    tahun = models.IntegerField()
    snvt = models.ForeignKey(Snvt)
    propinsi = models.ForeignKey(Propinsi)
    proyek = models.ForeignKey(Proyek, blank=True, null=True)
    name = models.CharField(max_length=100)
    def __str__(self):
        return '%s' %self.name

class Divisi(models.Model):
    id = models.IntegerField(primary_key=True)
    name = models.CharField(max_length=100)
    def __str__(self):
        return '%s' %self.name

class Indikator(models.Model):
    name = models.CharField(max_length=100)
    short_name = models.CharField(max_length=100)

class Seksi(models.Model):
    id = models.CharField(primary_key=True, max_length=10)
    divisi = models.ForeignKey(Divisi)
    no = models.IntegerField()
    name = models.CharField(max_length=200)
    def __str__(self):
        return '%s' %self.name

class Judul(models.Model):
    id = models.CharField(primary_key=True, max_length=10)
    seksi = models.ForeignKey(Seksi)
    no = models.IntegerField()
    code = models.CharField(max_length=100)
    name = models.CharField(max_length=200)
    def __str__(self):
        return '%s' %self.name

class Responden(models.Model):
    tahun = models.CharField(choices=YEARS, max_length=4)
    wilayah = models.ForeignKey(Wilayah)
    balai = models.ForeignKey(Balai)
    propinsi = models.ForeignKey(Propinsi, blank=True, null=True)
    snvt = models.ForeignKey(Snvt, blank=True, null=True)
    proyek = models.ForeignKey(Proyek, blank=True, null=True)
    ppk = models.ForeignKey(Ppk, blank=True, null=True)
    level = models.IntegerField(blank=True, null=True)
    posisi = models.CharField(max_length=100)
    name = models.CharField(max_length=100)
    comment = models.CharField(max_length=100,blank=True, null=True)
    created_date = models.DateTimeField(auto_now_add=True)
    changed_date = models.DateTimeField(auto_now=True)
    owner = models.ForeignKey(User, blank=True, null=True)

    def get_absolute_url(self):
        return '/resp/%s/' % self.id
    def __str__(self):
        return '%s' %self.name

class Jawaban(models.Model):
    responden = models.ForeignKey(Responden)
    judul = models.ForeignKey(Judul)
    jawab1 = models.IntegerField(blank=True, default=0)
    jawab2 = models.IntegerField(blank=True, default=0)
    jawab3 = models.IntegerField(blank=True, default=0)
    jawab4 = models.IntegerField(blank=True, default=0)
    jawab5 = models.IntegerField(blank=True, default=0)
    jawab6 = models.IntegerField(blank=True, default=0)
    jawab7 = models.IntegerField(blank=True, default=0)
    #~ seksi = models.ForeignKey(Seksi)     #helper field, google apps doesn't support join table
    #~ divisi = models.ForeignKey(Divisi)   #helper field, google apps doesn't support join table