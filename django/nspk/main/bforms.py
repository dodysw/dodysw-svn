import models
from django import forms

class RespForm(forms.ModelForm):
    class Meta:
        model = models.Responden
        exclude = ['created_date', 'changed_date','owner' ,'comment', 'level']
        
class RespForm0(forms.Form):
    tahun = forms.ChoiceField(choices=models.YEARS)
    wilayah = forms.ModelChoiceField(models.Wilayah.objects.all())
    balai = forms.ModelChoiceField(models.Balai.objects.all())

# class FilterForm(forms.Form):
    # byAll = forms.
    # byWilayah
    # wilayah = forms.ModelChoiceField(models.Wilayah.objects.all())
    # byBalai
    # balai = forms.ModelChoiceField(models.Balai.objects.all())
    # byPropinsi
    # propinsi = forms.ModelChoiceField(models.Propinsi.objects.all())

    
#class to hide error
from django.forms.util import ErrorList
class HideErrorList(ErrorList):
    def __unicode__(self):
        return u''
        
        