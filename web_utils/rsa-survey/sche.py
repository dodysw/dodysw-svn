import urllib,time
url = 'http://sipeha.com.11.hostcorporate.com/survey/survey_schedule.php'
timer = 60
print 'Remote routing php caller -- dody suria wijaya <dswsh@plasa.com>'
print 'Calling: %s\nEvery %s secs'% (url,timer)
while 1:
    urllib.urlopen(url)
    print '.',
    time.sleep(timer)
