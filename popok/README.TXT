popok - a webmail to pop3/smtp gateway 
by dsw software house
(http://dsw.gesit.com)


=====================================

--This README refers to popok version 0.9--

What's New?
==========
v0.9
- detikcom news plugins!
   Now you can check the latest Indonesian news from www.detik.com by using your own pop3 email client.
   Instruction:   
    - create a new email account, using username: detikcom, password: <anything>, pop3 server: localhost, port: 110
    - for every email checking, popok will retrieve today's news and simulate them as incoming email.            
    - the news will be parsed, and returned as a normal text, with the 
    original HTML (without the images) attached. this is so that when the 
    regex fail to parse the news, you can still read the news.
    
- HTTP proxy support

    Popok now use http proxy automatically by retrieving current Internet 
    Explorer proxy setting in Windows' registry
    
- Linux support

    Popok has been tested to be quite stable in Linux environment with 1 normal 
    user, and another heavy users fetching megabytes of emails each day from 
    multiple account at the same time, checks every 5 minutes, and send emails 
    too. Note: since Linux usually means your email client is not at the same 
    computer, and many user might uses the machine, you might want to turn off 
    SMTP feature to prevent other user hijack your user/pass cache (see below).

- email size detection

    Before this version, popok fake the email size as 5000 bytes, when email 
    client ask using STAT and LIST comand. This is a performance decision (more 
    accurate method is to download all of the email first). Now popok try to 
    parse the email size from INBOX email list, and give the information back. 
    The information is accurate for <10 Kilobyte email, and rounded to nearest 1 
    Kilobyte for >=10 KB email.

Introduction
============

Popok is simply a software to retrieve and send email through Indonesia's 
webmail, using many POP3/SMTP clients. Popok has been tested to work at these 
sites:

CommuniGate Pro based webmail: 
	- http://webmail.plasa.com (free webmail)
	- http://mail.telkom.net (free webmail)
	
Popok has also been tested to work with this POP3/SMTP clients:
    - The Bat 2.x
    - Microsoft Outlook Express
    - Microsoft Outlook

Download
========

Latest version is available at 
    - http://miaw.compeng.org/~dody/
	- http://dsw.gesit.com/

Installation
============

Extract the compressed file to an empty directory, and execute popok.exe to run 
popok using all default configuration. To change some configuration, do:

    popok.exe --help

Next, configure your POP3 email client: 

- Username: your full email address. ie: dodysw@plasa.com
- Password: your webmail account password
- POP3 server hostname: localhost
- POP3 server port: 110
- SMTP server hostname: localhost
- SMTP server port 25

You should adapt the above parameters to your specific configuration. Use your 
email address as username, ie: popok@plasa.com. This is required as popok needs 
to know which webmail server you're talking about. 

SMTP is optional, that is you can choose to use your ISP's SMTP server. But if 
you do intend to use popok as SMTP server gateway to your webmail send email 
feature, look out for this caveat: 

1. You have to do "POP before SMTP" authentication method. That is, do a quick 
email check, just before sending. The rationale behind this requirement is popok 
needs your normal username/password to send email through web email, but 
standard SMTP protocol do not have authentication method like in pop3. So what 
popok does is it caches user/pass when you do email checking, and uses that to 
send email. 

Note: This open to a possible attack when a single instance of popok is used by 
more than one user: some one else might hijack the authentication cache by 
putting your email address in their "From:" parameter. This is really possible 
since popok detect which valid user/pass cache to use just by checking SMTP from 
envelope.

2. Make sure the From: field on your email equal to your full email address 
provided as POP3's username.  This is the only way popok can understand which 
user/pass cache to pick (notice popok does not ask password for SMTP?)

Parameters
==========
You can provide this parameters to modify popok features:

options:
  --version        show program's version number and exit
  -h, --help       show this help message and exit
  -v, --verbose    echo what popok is doing to stdout
  --pop3port=PORT  bind pop3 gateway to port PORT (default:110)
  --smtpport=PORT  bind smtp gateway to port PORT (default:25)
  --smtpdisable    disable smtp gateway
  --ip=ADDRESS     bind pop3/smtp gateway to this ip address (default:
                   0.0.0.0 -> all ip address of this machine)

License
=======

This software is released as freeware (but may be changed later). You have the 
right to download and use without any limit, and are encouraged to give it to 
your friends.

By using this software, you agree for this term of use:

This software is provided by the copyright holders and contributors "as is" and 
any express or implied warranties, including, but not limited to, the implied 
warranties of merchantability and fitness for a particular purpose are 
disclaimed. in no event shall Dody Suria Wijaya's Software House or contributors 
be liable for any direct, indirect, incidental, special, exemplary, or 
consequential damages (including, but not limited to, procurement of substitute 
goods or services; loss of use, data, or profits; or business interruption) 
however caused and on any theory of liability, whether in contract, strict 
liability, or tort (including negligence or otherwise) arising in any way out of 
the use of this software, even if advised of the possibility of such damage.

Acknowledgements
================

Thanks to these people in which Popok was built upon:

1. Python by peoples at Python Software Foundation
2. py2exe by Thomas Heller, Mark Hammond

Also to these people:

all webmail providers for keeping their service free of charge

--
  dody suria wijaya
  dswsh@plasa.com

-oO end of readme Oo-