from django.conf.urls.defaults import *

# Uncomment the next two lines to enable the admin:
from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',

    (r'^$', 'lengkuas.aduser.views.home'),
    (r'^test123/', 'lengkuas.aduser.views.test123'),
    (r'^accounts/login/$', 'django.contrib.auth.views.login'),
    (r'^accounts/logout/$', 'django.contrib.auth.views.logout_then_login'),

    (r'^ad/user/(?P<userid>.+)/$', 'lengkuas.aduser.views.user_detail'),
    (r'^ad/group/(?P<groupid>.+)/$', 'lengkuas.aduser.views.group_detail'),
    (r'^ad/search/name/$', 'lengkuas.aduser.views.search_name'),
    (r'^ad/search/name/(?P<name>.+)/$', 'lengkuas.aduser.views.search_name'),
    (r'^vale/email/(?P<emailaddr>.+)/$', 'lengkuas.aduser.views.email_to_vale'),

    (r'^ell/search/name/$', 'lengkuas.aduser.views.search_name_ell'),
    (r'^ell/search/name/(?P<name>.+)/$', 'lengkuas.aduser.views.search_name_ell'),

    (r'^ell/emp/(?P<empid>.+)/$', 'lengkuas.aduser.views.ell_emp_detail'),
    (r'^ell/pos/(?P<posid>.+)/$', 'lengkuas.aduser.views.ell_pos_detail'),

    (r'^clear_cache/$', 'lengkuas.aduser.views.clear_cache'),


    (r'^request/(?P<empid>.+)/ec/$', 'lengkuas.aduser.requests.expense_claim'),
    (r'^request/(?P<empid>.+)/ca/$', 'lengkuas.aduser.requests.cash_advance'),
    (r'^request/(?P<empid>.+)/crs/$', 'lengkuas.aduser.requests.meal_request'),
    (r'^request/(?P<empid>.+)/flight/$', 'lengkuas.aduser.requests.flight_request'),
    #~ (r'^request/(?P<empid>.+)/accbooking/$', 'lengkuas.aduser.requests.accbooking_request'),


    (r'^ta/(?P<tano>.+)/edit/$', 'lengkuas.aduser.requests.ta_edit'),
    (r'^ta/(?P<tano>.+)/update/$', 'lengkuas.aduser.requests.ta_update'),
    (r'^ta/(?P<tano>.+)/$', 'lengkuas.aduser.requests.ta_page'),
    (r'^ec/(?P<ecno>.+)/$', 'lengkuas.aduser.requests.ec_page'),
    (r'^ab/by/ta/(?P<tano>.+)/$', 'lengkuas.aduser.requests.search_acbooking_by_ta'),

    # Uncomment the admin/doc line below and add 'django.contrib.admindocs'
    # to INSTALLED_APPS to enable admin documentation:
    (r'^admin/doc/', include('django.contrib.admindocs.urls')),

    # Uncomment the next line to enable the admin:
    (r'^admin/(.*)', admin.site.root),

    (r'^static/(?P<path>.*)$', 'django.views.static.serve', {'document_root': '/hr/lengkuas/static'}),

)
