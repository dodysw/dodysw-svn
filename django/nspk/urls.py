from django.conf.urls.defaults import *

from django.contrib import admin
admin.autodiscover()

urlpatterns = patterns('',
    # Example:
    # (r'^{{ project_name }}/', include('{{ project_name }}.foo.urls')),
    (r'^$', 'main.views.home'),
    (r'^resetdata/$', 'main.views.resetdata'),
    (r'^resp/(?P<resp_key>[^\.^/]+)/$', 'main.views.resp_detail'),
    (r'^resp/(?P<resp_key>[^\.^/]+)/(?P<div_key>[^\.^/]+)/$', 'main.views.resp_detail_div'),
    (r'^analisis/$', 'main.reports.main'),
    
    (r'^accounts/login/$', 'django.contrib.auth.views.login'),
    (r'^accounts/logout/$', 'django.contrib.auth.views.logout_then_login'),

    
    # Uncomment the admin/doc line below and add 'django.contrib.admindocs'
    # to INSTALLED_APPS to enable admin documentation:
    (r'^admin/doc/', include('django.contrib.admindocs.urls')),

    (r'^admin/(.*)', admin.site.root),
)
