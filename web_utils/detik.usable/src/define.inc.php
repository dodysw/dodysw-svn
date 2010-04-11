<?
/**
    detik.usable: a fast-download detik.com
    Author: dody suria wijaya <dodysw@gmail.com>
    License: THIS IS A PUBLIC DOMAIN CODE (you may even change the author)
    Term of Usage: BY USING THIS SCRIPT, YOU TAKE FULL RESPONSIBILITY OF ANY LEGAL ACTION THAT MAY BE TAKEN.
    Note: Mail me with your personal info (bio) to get non-text-compressed version of the source code
*/

// MODIFIABLE CONFIGURATION
$app['proxy_mode'] = False;                             // TRUE to get data from other detik.usable, FALSE to get it directly from detikcom
$app['proxy_url'] = '';                                 // Hostname/IP Address of other detik.usable node. Ie: http://myhostname.com/detik.php
$app['ads'] = TRUE;                                     // TRUE to display advertisement (please be fair to detikcom, they need it)
$app['cache'] = TRUE;                                   //TRUE to cache retrieved news detail content to filesystem
$app['http_proxy']['enable'] = FALSE;                   // TRUE to enable using http proxy to connect to detikcom website
$app['http_proxy']['hostname'] = 'proxy.myoffice.com';  // Hostname/IP address of http proxy (if you must use one)
$app['http_proxy']['port'] = '8080';                    // port number of above http proxy hostname
$app['http_proxy']['user'] = 'myproxyusername';         // username for http proxy authentication, keep this empty if no authentication is needed
$app['http_proxy']['pass'] = 'myproxypassword';         // password for http proxy authentication. you can put this in config.inc.php (see below)

// RARELY MODIFIED CONFIGURATION
$app['url_list'] = 'http://jkt3.detik.com/index.php';
/**
note: if you got "access forbidden" error, or connection fail, and your hosting server located
outside Indonesia, use one of this line instead:

$app['url_list'] = 'http://jkt.detik.com/index.php';   #  hosting server
$app['url_list'] = 'http://jkt1.detik.com/index.php';   #  hosting server
$app['url_list'] = 'http://jkt2.detik.com/index.php';   #  hosting server
**/
#~ $app['url_list'] = 'http://localhost/detik-index.html';      # used for testing
$app['update_url'] = array();
$app['update_url'][] = 'http://popok.sourceforge.net/du/detikusable-latest.php.txt';
$app['update_url'][] = 'http://du.port5.com/detikusable-latest.php.txt';
#~ $app['update_url'] = 'http://localhost/detikusable-latest.php.txt';
$author_email = 'dodysw@gmail.com';
$author_name = 'Dody Suria Wijaya';
$author_website = 'http://miaw.tcom.ou.edu/~dody/du/';
$app['hosted_by'] = get_current_user();
$contributors = array(
    array('Mico Wendy','mico@konsep.net','Bug fix: php'),
    array('rudych@gmail.com','rudych@gmail.com','Bug fix: rss'),
    array('Ronny Haryanto','ronny@haryan.to','Bug fix: rss'),
    array('Reno S. Anwari','sireno@gmail.com','Timezone'),
    );

// define any news module
$an_m = array(
    array('name'=>'detikcom(jkt)','url'=>'http://jkt.detik.com/index.php'),
    array('name'=>'detikcom(jkt1)','url'=>'http://jkt1.detik.com/index.php'),
    array('name'=>'detikcom(jkt2)','url'=>'http://jkt2.detik.com/index.php'),
    array('name'=>'detikcom(jkt3)','url'=>'http://jkt3.detik.com/index.php'),
    array('name'=>'Kompas','url'=>'http://www.kompas.co.id/index2.htm'),
    array('name'=>'Kompas(LN)','url'=>'http://www.kompas.com/index2.htm'),
    array('name'=>'Media&nbsp;Indonesia','url'=>'http://www.mediaindo.co.id/main.asp'),
    array('name'=>'Jakarta&nbsp;Post','url'=>'http://www.thejakartapost.com/headlines.asp'),
    array('name'=>'Antara','url'=>'http://www.antara.co.id/'),
    array('name'=>'Republika','url'=>'http://www.republika.co.id/'),
    array('name'=>'Koran&nbsp;Tempo','url'=>'http://www.korantempo.com/'),
    array('name'=>'Tempo&nbsp;Interaktif','url'=>'http://www.tempointeraktif.com/'),
    array('name'=>'Tempo&nbsp;Interactive','url'=>'http://www.tempointeractive.com/'),
    array('name'=>'Suara&nbsp;Pembaruan','url'=>'http://www.suarapembaruan.com/index.htm'),
    array('name'=>'Pikiran&nbsp;Rakyat','url'=>'http://www.pikiran-rakyat.com/cetak/'),
    array('name'=>'Suara&nbsp;Merdeka','url'=>'http://www.suaramerdeka.com/'),
    array('name'=>'Jawa&nbsp;Pos','url'=>'http://www.jawapos.com/'),
    array('name'=>'BBC&nbsp;Indonesia','url'=>'http://www.bbc.co.uk/indonesian/'),
    array('name'=>'ABC&nbsp;Radio&nbsp;Aus-Indo','url'=>'http://www.abc.net.au/ra/indon/'),
    array('name'=>'Warta&nbsp;Ekonomi','url'=>'http://www.wartaekonomi.com/'),
    #~ array('name'=>'Sinar&nbsp;Pagi','url'=>'http://www.sinarpagi.co.id/'),
    array('name'=>'SWA','url'=>'http://www.swa.co.id/'),
    array('name'=>'Gatra','url'=>'http://www.gatra.com/'),
    array('name'=>'Infokomputer','url'=>'http://www.infokomputer.com/'),
    array('name'=>'Pos&nbsp;Kota','url'=>'http://www.poskota.co.id/poskota/index.asp'),
    #~ array('name'=>'Bali&nbsp;Post','url'=>'http://www.balipost.co.id/'),
    array('name'=>'Indonesian&nbsp;Business','url'=>'http://articles.ibonweb.com/default.asp'),
    array('name'=>'Berita&nbsp;Iptek','url'=>'http://www.beritaiptek.com/')
);


// SHOULD BE UNMODIFIABLE CONFIGURATION
$hari = array('Minggu','Senin','Selasa','Rabu','Kamis','Jum\'at','Sabtu');
$bulan = array('','Januari','Februari','Maret','April','Mei','Juni','July','Agustus','September','Oktober','November','Desember');
$develmode = 0;
$list_footer = '';  #additional footer, showed before real footer
$app['last-modified'] = filemtime($_SERVER['SCRIPT_FILENAME']);
?>