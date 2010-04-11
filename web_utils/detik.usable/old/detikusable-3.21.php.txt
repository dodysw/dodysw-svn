<?
$app['name'] = "detik.usable";
$app['version'] = "3.21";
$app['version_description'] = <<<__END__
- new: du screen shot on pda
- fix memory hog problem on browse cache at huge cache (ie: > 16megs)
- remove ads on gmail mode (position bug on firefox)
__END__;
/*
detik.usable: a fast-download detik.com
Author: dody suria wijaya <dodysw@gmail.com>
License: THIS IS A PUBLIC DOMAIN CODE (you may even change the author)
Term of Usage: BY USING THIS SCRIPT, YOU TAKE FULL RESPONSIBILITY OF ANY LEGAL ACTION THAT MAY BE TAKEN.

===Search Bookmarks===<start>
(news list regex)
  $regex_prevnews_all
  $regex_headline_all
  $regex_topic_detail
(news detail regex)
  regex_detail_normal
  regex_detail_detikhot
  regex_detail_beritafoto
(news parser)
  function get_news_list(
  function parse_news_list(
  function get_news_detail(
  function ads_parse(
(news renderer)
  function news_list_view(
  function news_list_view_gm(
  function news_list_view_rss(
  function news_detail_view(
===Search Bookmarks===<end>
*/

// MODIFIABLE CONFIGURATION
$app['proxy_mode'] = False;        // TRUE to get data from other detik.usable, FALSE to get it directly from detikcom
$app['proxy_url'] = '';         // Hostname/IP Address of other detik.usable node. Ie: http://myhostname.com/detik.php
$app['ads'] = TRUE;    // TRUE to display advertisement (please be fair to detikcom, they need it)
$app['cache'] = TRUE;   //TRUE to cache retrieved news detail content to filesystem
$app['http_proxy']['enable'] = FALSE; // TRUE to enable using http proxy to connect to detikcom website
$app['http_proxy']['hostname'] = 'proxy.myoffice.com';    // Hostname/IP address of http proxy (if you must use one)
$app['http_proxy']['port'] = '8080';    // port number of above http proxy hostname
$app['http_proxy']['user'] = 'myproxyusername';    // username for http proxy authentication, keep this empty if no authentication is needed
$app['http_proxy']['pass'] = 'myproxypassword';    // password for http proxy authentication. you can put this in config.inc.php (see below)
$app['timediff'] = '+0'; // time difference between detikcom's timezone (+7) and server timezone.  Not with UTC/GMT.
assert_options(ASSERT_CALLBACK,assert_callback);

// RARELY MODIFIED CONFIGURATION
$app['update_url'] = 'http://miaw.tcom.ou.edu/~dody/du/detikusable-latest.php.txt';
$hostname = 'jkt1.detik.com';   # note: if you got "access forbidden" error and your hosting server is located outside indonesia, use "jkt2.detik.com" instead
#~ $hostname = 'localhost';
$author_email = 'dodysw@gmail.com';
$author_name = 'Dody Suria Wijaya';
$author_website = 'http://miaw.tcom.ou.edu/~dody/';
$app['hosted_by'] = get_current_user();//date ("F d Y H:i:s", getlastmod());
$app['http_user_agent'] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1) ';
$app['newslist_cache_revalidate'] = 60*3; // time in seconds in which news list become invalidated, and automatically reload fresh data from original source
$contributors = array(
    array('Mico Wendy','mico@konsep.net','Bug fix: php'),
    array('rudych@gmail.com','rudych@gmail.com','Bug fix: rss'),
    array('Ronny Haryanto','ronny@haryan.to','Bug fix: rss'),
    array('Reno S. Anwari','sireno@gmail.com','Timezone'),
    );

// SHOULD BE UNMODIFIABLE CONFIGURATION
$app['zlib_support'] = extension_loaded('zlib');
$hari = array('Minggu','Senin','Selasa','Rabu','Kamis','Jum\'at','Sabtu');
$bulan = array('','Januari','Februari','Maret','April','Mei','Juni','July','Agustus','September','Oktober','November','Desember');

/* New: create a file called config.inc.php with your own configuration that overrides all above parameters. for instance:
    <?
        $app['ads'] = FALSE;    //set this to TRUE to display advertisement
        $app['http_proxy']['enable'] = TRUE; // TRUE to enable using http proxy to connect to detikcom website
        $app['http_proxy']['hostname'] = 'proxy.myoffice.com';    // Hostname/IP address of http proxy (if you must use one)
        $app['http_proxy']['port'] = '8080';    // port number of above http proxy hostname
        $app['http_proxy']['user'] = 'myproxyusername';    // username for http proxy authentication, keep this empty if no authentication is needed
        $app['http_proxy']['pass'] = 'myproxypassword';    // password for http proxy authentication. you can put this in config.inc.php (see below)
    ?>
*/
if (file_exists('config.inc.php')) include 'config.inc.php';

//    VARIABLE DEFINITIONS
$ctime	= strtotime($app['timediff']." hours");
$timezone_sign = ((7+$app['timediff']) >= 0)? '+':'-';
$tgl_lengkap = $hari[date('w',$ctime)].',&nbsp;'.date('j',$ctime).'&nbsp;'.$bulan[date('n',$ctime)].date(' Y',$ctime).'&mdash;'.date('H:i',$ctime).' GMT'.$timezone_sign.($app['timediff']+7);
$x = $_REQUEST['x'];
$url = $_REQUEST['url'];
$as_node = $_REQUEST['as_node'];    //client request 1 to ask for serialized news array (default: compressed)
$uncompressed = $_REQUEST['uc'];    //client set this to 1 when requesting uncompressed stream
$query_string = $_SERVER['QUERY_STRING'];
$no = $_REQUEST['no'];

$doctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
$header_ouput = "$doctype<html><head><title>detik.usable ($tgl_lengkap)</title><style>body{font-family:verdana;}.o{font-size:11pt;}.p{font-size:13pt;}h1{font-family:georgia;font-size:18pt;align:center;margin-bottom:0;}.s{color:#991111;font-weight:bold;}</style></head><body bgcolor=#ffffff>";
$list_header_output = "$doctype<html><head><title>detik.usable ($tgl_lengkap)</title><style>body{font-family:verdana,arial;}.i{font-size:10pt;font-weight:bold;font-family:verdana;}.j{font-family:arial;font-size:12pt;font-weight:bold;}.u{font-size:10pt;}.s{color:#991111;font-weight:bold;}a{text-decoration:none;}a:hover{text-decoration:underline;} .button {font-size:10pt;background:#D6E7EF;border-bottom:1px solid #104A7B;border-right:1px solid #104A7B;border-left: 1px solid #AFC4D5;border-top:1px solid #AFC4D5;color:#000066;margin:2;}.d{font-size:smaller;color:#555}#footer {font-size:smaller;clear:both;border:none;background:#e3ebe2;margin-top:20px;padding-left:30px;padding-top:10px;padding-bottom: 10px;}#footer a:link{color:#666666;} #footer a:active,.footer a:hover{color:#006486;}#footer a:visited{color:#949494;}h3{margin-top:0;}</style></head><body bgcolor=#ffffff>";
$list_top_output2 = "<p style='margin-bottom:0;'><b><a href='$author_website' title=Home target=_top><span style=color:#991111;>detik</span>.<span style=color:#119911;>usable</span></a></b> // ";
$list_top_output = "<p align=center style='margin-bottom:0;'><b><a href='$author_website' title=Home target=_top><span style=color:#991111;>detik</span>.<span style=color:#119911;>usable</span></a><br><small>$tgl_lengkap</small></b> <a href=".$_SERVER['PHP_SELF']."?".$query_string."&cache_reload=1 class=button>Reload</a>";
$temp_pm = 'DU Proxy: '.($app['proxy_mode']? 'Yeah': 'Nope');
$temp_htp = 'HTTP Proxy: '.($app['http_proxy']['enable']? 'Yeah': 'Nope');
$temp_zlib = "Compression Support: "; if ($app['zlib_support']) $temp_zlib .= 'Yeah'; else $temp_zlib .= 'Nope';
$list_footer = "<div id=footer><a href=mailto:$author_email target=_top>author</a> | <a href=\$temp_orig target=_top>Original page</a> | Generated by <a href='$author_website' target=_top>{$app['name']} v{$app['version']}</a><BR><small>Host: {$app['hosted_by']} | $temp_pm | $temp_htp | \$temp_stream</small></div></body></html>";
$error_cant_open = "<p>Unable to connect to Detikcom's server. This can be caused by this problems: <ul> <li>This webserver's IP has been blocked by Detikcom <li>Your webserver is behind firewall <li>Your PHP's setting has disabled socket connection-related functions <li>Detikcom is being swarmed by huge requests and really really busy <li>Detikcom's URL/port has been changed </ul> <p>What ever is the caused, I may not able to help you with this. Thank you. <p><a href=http://www.detik.com>Visit the original detik.com</a> $errstr ($errno)<br>";
$frameset_output = "$doctype<html><head><title>detik.usable ($tgl_lengkap)</title></head> <frameset cols=\"50%,*\"> <frame name=c target=m src=\"{$_SERVER['PHP_SELF']}?x=i\"> <frame name=m target=_top src=\"{$_SERVER['PHP_SELF']}?x=w\"> <noframes> <body>Looks like u need the <a href=\"{$_SERVER['PHP_SELF']}?no=frame\">non-frame version</a>.</body> </noframes> </frameset></html>";

$welcomepage_output = '<center><h1><a href="'.$author_website.'" title="detik.usable, version '.$app['version'].' ('.$app['version_description'].')" target="_top" style="text-decoration:none;"><span style= "color:#991111;">detik</span>.<span style= "color:#119911;">usable</span></a></h1><br><small>v'.$app['version'].'</small></center><br><br> <p align="center"><b>Lihat:</b><br><a href="'.$_SERVER['PHP_SELF'].'?x=i" target="c" title="Refresh">Normal</a> | <a href="'.$_SERVER['PHP_SELF'].'?no=frame" target="_top" title="Untuk pembenci frame">Tanpa frame</a><br><a href="'.$_SERVER['PHP_SELF'].'?no=gm" target="_top" title="Preloaded news using iframe">Ala Gmail (beta)</a> | <a href="'.$_SERVER['PHP_SELF'].'?no=rss2" target="_top" title="Use any rss reader!">RSS2 feed</a><br><a href="'.$_SERVER['PHP_SELF'].'?no=rss2&complete=1" target="_top" title="Note: could be much slower">RSS2 feed w/ body</a> | <a href="'.$_SERVER['PHP_SELF'].'?no=bcache" target="c">Browse cache</a> | <a href="'.$_SERVER['PHP_SELF'].'?x=s" target="_top">Source code</a> <br><p align="center"><b>Atur:</b><br> <a href="'.$_SERVER['PHP_SELF'].'?au=1">Check update</a> | <a href="'.$_SERVER['PHP_SELF'].'?cm=1">Reset Cache</a> | <a href="'.$app['update_url'].'" target="_top" title="Get latest detik.usable  direct from repository">Download</a></p> <br><br><br><p align="center"><!--info--><br><br><br><a href="'.$_SERVER['PHP_SELF'].'?x=w&page=2">Next &gt;</a><br><hr width="200"><center><small><a href="mailto:'.$author_email.'">'.$author_name.'</a>, dari <a href="mailto:dodysw@gmail.com">dsw s/h</a></small></center>
</body>
</html>';

$con = '<ul>';
foreach ($contributors as $c) {
    $con .= '<li><a href="mailto:'.$c[1].'">'.$c[0].'</a>, '.$c[2].'</li>';
}
$con .= '</ul>';

$welcomepage_output_2 = '<center><h1><a href="'.$author_website.'" title="detik.usable, version '.$app['version'].' ('.$app['version_description'].')" target="_top" style="text-decoration:none;"><span style= "color:#991111;">detik</span>.<span style= "color:#119911;">usable</span></a></h1></center><h3>Purpose</h3><p>Situs ini ditujukan untuk mendemonstrasikan <i>look and feel</i> dari potensi sebuah situs berita yg usable: cepat, bersih, dan mudah digunakan.<p>Bagi yang ingin nge-detik.usable, bisa copy and paste <a href="'.$_SERVER['PHP_SELF'].'?x=s" target="_top">source code situs ini</a> (public domain dan hanya satu file), pasang di hosting yg mendukung php, <b>untuk keperluan anda sendiri</b>.
<h3>Resources</h3>
<p><a href="http://groups-beta.google.com/group/detikusable/subscribe"> Daftar milis detik.usable</a> (announcement release baru)
<p>View <a href="http://miaw.tcom.ou.edu/~dody/du/images/DetikScreen1.png">screenshot in PDA</a> (<a href="mailto:pursena@advokasi.com">Bagus Pursena</a>). Seems like old Windows Pocket PC + IE.
<h3>Kontributor</h3>Sorry kalau ada yang kelewat :)'.$con.'<p align="center"><br><br><a href="'.$_SERVER['PHP_SELF'].'?x=w&page=1">&lt; Back</a><br><hr width="200"><center><small><a href="mailto:'.$author_email.'">'.$author_name.'</a>, dari <a href="mailto:dodysw@gmail.com">dsw s/h</a></small></center>
</body>
</html>';

@set_time_limit(60*5);

global $fp,$log,$news;

//    FUNCTIONS

function add_log ($line_number,$string) {
    global $log;
    $log[] = '<b>############ '.$line_number.':</b> '.htmlspecialchars($string);
}

function dump_log () {
    global $log,$news;
    echo '<pre>'.implode("\r\n\r\n",$log).'</pre>';
    exit;
}

function assert_callback( $script, $line, $message ) {
    global $log,$news,$author_email;
    echo '<div style="border:thin solid #ffaaaa;background-color:#ffcccc;margin:10;text-align:center;">';
    echo '<h3>Yikes at Line# <b>'.$line.'</b></h3>';
    echo '<p>Send this whole page to <a href="mailto:'.$author_email.'">author</a> to help improve the next version.</p>';
    echo '</div>';
    #~ echo '<h4>Traceback</h4>';
    #~ echo '<pre>'; print_r(debug_backtrace()); echo '</pre>';
    echo '<h4>Trace dump</h4>';
    echo '<pre>'.implode("\r\n",$log).'</pre>';
    echo '<pre>'; print_r($news); echo '</pre>';
    exit;
 }


function newsdetail_fetch ($pattern_start,$pattern_end) {
    global $fp;
    while (!feof ($fp)) { //skip non-content to make regmatching later much faster
        $buffer = fgets($fp, 65536);
        if (preg_match($pattern_start,$buffer)) break;
    }

    while (!feof ($fp)) { //start collecting data until designated sign found
        $buffer = fgets($fp, 65536);
        if (preg_match($pattern_end,$buffer)) break;
        $buffers .= $buffer;
    }

    return $buffers;
}

class MySocket {

    function MySocket($host,$port) {
        global $app;
        $this->host = $host;
        $this->port = $port;
        $this->timeout = 30; //seconds
        $this->error_cant_open = '';
        $this->user_agent = $app['http_user_agent'];
        $this->http_proxy_enable = $app['http_proxy']['enable'];
        $this->http_proxy_host = $app['http_proxy']['hostname'];
        $this->http_proxy_port = $app['http_proxy']['port'];
        $this->http_proxy_user = $app['http_proxy']['user'];
        $this->http_proxy_pass = $app['http_proxy']['pass'];
    }

    function socket_open () {
        if ($this->http_proxy_enable) {
            add_log(__LINE__, "sock_open, {$this->http_proxy_host}/{$this->http_proxy_port}, {$this->timeout} sec timeout");
            $this->fp = @fsockopen ($this->http_proxy_host,$this->http_proxy_port,$errno,$errstr,$this->timeout);
        }
        else {
            add_log(__LINE__, "sock_open, {$this->host}/{$this->port}, {$this->timeout} sec timeout");
            $this->fp = @fsockopen ($this->host,$this->port,$errno,$errstr,$this->timeout);
        }
        if (!$this->fp) {
            add_log(__LINE__, 'sock_open: can\'t connect');
            assert($errno != 0);
            if ($errno == 0) {
                add_log(__LINE__, 'sock_open: problem before connect (dns/socket)');
                dump_log();
            }
            else {
                add_log(__LINE__, 'sock_open: problem trying to connect (hostname notfound, blocked, downed, busy, or timeout)');
                write_error("$errno \"$errstr\".");
                dump_log();
            }
            return FALSE;
        }
        else {
            add_log(__LINE__, 'sock_open: connected');
            return TRUE;
        }
    }

    function sock_send_request ($location) {
        if ($this->http_proxy_enable) {
            $header_auth = '';
            if ($this->http_proxy_user != '') {
                $header_auth = 'Proxy-Authorization: Basic '.base64_encode($this->http_proxy_user.':'.$this->http_proxy_pass)."\r\n";
            }
            $http_req = "GET http://{$this->host}:{$this->port}{$location} HTTP/1.0\r\nHost: {$this->host}:{$this->port}\r\nReferer: http://{$this->host}/\r\nUser-Agent: {$this->user_agent}\r\n{$header_auth}Connection:close\r\n\r\n";
        }
        else {
            $http_req = "GET $location HTTP/1.0\r\nHost: {$this->host}:{$this->port}\r\nReferer: http://{$this->host}/\r\nUser-Agent: {$this->user_agent}\r\nConnection:close\r\n\r\n";
        }
        add_log(__LINE__, 'http_req: '.$http_req);
        $return = fputs ($this->fp, $http_req);
        if ($return == -1) {
            add_log(__LINE__, 'http_req: can\'t send');
            return FALSE;
        }
        else {
            add_log(__LINE__, 'http_req: sent');
            return TRUE;
        }
    }

    function sock_recv_header () {  //return HTTP response header
        $buffers = '';
        add_log(__LINE__, 'http_resp_header: receiving...');
        while (!feof ($this->fp)) {
            $buffer = fgets($this->fp, 65536 );
            if ($buffer == "\r\n") break;
            $buffers .= $buffer;
        }
        add_log(__LINE__, 'http_resp_header: '.$buffers);
        if (!preg_match('/200 OK/',$buffers)) {  //validate buffer
            write_error('Invalid HTTP Response');
            dump_log();
            return false;
        }
        else {
            add_log(__LINE__, 'http_resp_header: 200 OK');
        }
        return $buffers;
    }

    function sock_recv_all () { //receive the rest of the data, then close
        $buffers = '';
        do {    // recv all response body
           $data = fread($this->fp, 8192); #use fread as body may be binary (fget is not binary safe)
           if (strlen($data) == 0) break;
           $buffers .= $data;
        } while(true);

        $this->close();
        return $buffers;
    }

    function close() {
        fclose($this->fp);
    }
}

function write_error ($string) {
    global $error;
    echo '<p><font color=red><b>ERROR:</b><!--begin-->'.$string.'<!--end--></font>';
    $error = TRUE;
    return;
}

function dump_buffer ($buffers,$title = '') {
    global $error;
    if ($error) {
        echo "<p>Core dump $title...</p>\n<pre><!--Start Dump-->\n".$buffers."\n<!--Stop Dump--></pre>\n";
    }
    return;
}

function str_time_delta ($date_c) {
    global $ctime;
    $delta = $ctime - $date_c;
    if ($delta < 60) {
        $satuan = 'detik';
        $tgl = $delta;
    }
    elseif ($delta < 3600) {
        $satuan = 'menit';
        $tgl = floor($delta/60);
    }
    elseif ($delta < 86400) {
        $satuan = 'jam';
        $tgl = sprintf('%01.1f',$delta/3600);
    }
    else {
        $satuan = 'hari';
        #~ $tgl = sprintf('%01.1f',$delta/86400);
        $hari = floor($delta/86400);
        $jam = floor(($delta - $hari*86400) /3600);
        if ($jam == 0)
            return $hari.' hari';
        else
            return $hari.' hari '.$jam.' jam';
    }
    return $tgl.' '.$satuan;
}

function ads_parse(&$buffers_orig) {
    global $app,$news;
    //    parsing advertisements in main page
    if ($app['ads']) {
        // get all ad links
        $regex_ads = '|<a([^>]*)>(.*?)</a>|is';
        add_log(__LINE__, 'parser: ads: regex:"'.$regex_ads.'" to >>'.$buffers_orig.'<<');
        assert(preg_match_all($regex_ads,$buffers_orig,$ads_res,PREG_SET_ORDER));
        add_log(__LINE__, 'parser: ads: 1: success');
        for ($i = 0; $i < count($ads_res); $i++) {
            preg_match('|href="([^"]*)"|is',$ads_res[$i][1],$url_res);   //    get a href url

            //hanya url dengan hostname ad.detik yg diambil
            if (!preg_match('|http://ad\.detik\.com/link|is',$url_res[1])) continue;
            unset($temp);
            $temp['url'] = $url_res[1];
            $name = trim(strip_tags($ads_res[$i][2]));
            if ($name == '') {
                //get name from url
                preg_match('|/[^\-]*-([^/]*)\.ad|i',$ads_res[$i][1],$adsname_res);
                $name = $adsname_res[1];
            }
            $temp['name'] = $name;
            $news['ads'][] = $temp;
        }
    }
}

function ads_view(&$news) {// view ads
    global $app;
    $buffer = '';
    if ($app['ads'] and $news['ads'] != '') {
        $buffer .= '<table align=right bgcolor=#B4D0DC border=0 cellspacing=0 width=100><tr><td><table border=0 cellpadding=3 cellspacing=0 width=100%><tr><td bgcolor=#ECF8FF>';
        $buffer .= '<p class=u><span class=i>Iklan</span>';
        foreach ($news['ads'] as $ads) {
            $url = $ads['url'];
            $desc = $ads['name'];
            if (strlen($desc)>10) $desc = substr($desc,0,10).'&gt;';
            if ($desc == '') $desc = 'Iklan';
            $buffer .= "<br><a href=\"$url\" target=m>$desc</a>";
        }
        $buffer .= '</td></tr></table></td></tr></table>';
    }
    return $buffer;

}

function news_list_view(&$news) {
    global $list_header_output,$list_top_output,$app,$no;
    if ($no == 'frame') $target = '';
    else $target = ' target=m';
    #~ echo $list_header_output;
    #~ echo $list_top_output;
    // start view list
    if ($news['cache']) {
        if ((time() - $news['cache'])>$app['newslist_cache_revalidate'])
            echo '<center><span style="font-size:x-small;font-weight:bold;color:c00;">list dari cache '.str_time_delta($news['cache']).' lalu. Auto-loading berita baru di background.</span></center>';
        else
            echo '<center><span style="font-size:x-small;font-weight:bold;color:c00;">list dari cache '.str_time_delta($news['cache']).' lalu</span></center>';

    }

    foreach ($news['headline'] as $headline) {  // view headlines
        $headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&amp;url='.urlencode($headline['url']);
        if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
        $date = date('H:i',$headline['date']);
        $date_delta = str_time_delta($headline['date']);

        $alt = 3;
        if ($alt == 1) {
            # before du 3.20
            echo '<p><span class="d">('.$date.')</span> <span class="j"><a href="'.$headline['url'].'" '.$target.'>'.strip_tags($headline['subtitle'].$headline['title']).'</a></span> <span class="d">['.$date_delta.' lalu]</span>';
            echo '<br><span class="u">'.$headline['summary'].'</span>';
        }
        elseif ($alt == 2) {
            # center oriented with "pk"
            echo '<p align=center><span class="d">Pk '.$date.', '.$date_delta.' lalu</span>';
            echo '<br><span class="j"><a href="'.$headline['url'].'" '.$target.'>'.strip_tags($headline['subtitle'].$headline['title']).'</a></span>';
            echo '<br><span class="u">'.$headline['summary'].'</span>';
        }
        elseif ($alt == 3) {
            # time below title, slashdot style
            echo '<p>';
            echo '<span class="j"><a href="'.$headline['url'].'" '.$target.'>'.strip_tags($headline['subtitle'].$headline['title']).'</a></span>';
            echo '<br><span class="u">'.$headline['summary'].'</span>';
            echo '<b><small><span class="d">--'.$date.', '.$date_delta.' lalu</span></small></b></p>';
        }

    }

    echo '<p></p>';
    echo '<table border="0" cellspacing="0" cellpadding="0" summary="">';
    foreach ($news['prevnews'] as $headline) {  //  view prevnews
        $headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&amp;url='.urlencode($headline['url']);
        if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
        $date = date('H:i',$headline['date']);
        echo '<tr><td valign="top">&bull;&nbsp;</td><td><span class="i"><a href="'.$headline['url'].'" '.$target.'>'.strip_tags($headline['subtitle'].$headline['title']).'</a></span><small><b><span class="d">--'.$date.'</span></b></small></td></tr>';
    }
    echo '</table>';

    foreach ($news['topic'] as $topic) {    //  view topic news
        echo '<p style="margin-bottom:0;"><span class="i">'.$topic['title'].'</span></p>';
        echo '<table border="0" cellspacing="0" cellpadding="0" summary="">';
        foreach ($topic['news'] as $headline) {
            $headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&amp;url='.urlencode($headline['url']);
            if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
            # skip the time (too unpredictable)
            #~ $date = date('H:i',$headline['date']);
            echo '<tr><td valign="top">&bull;&nbsp;</td><td><span class="i"><a href="'.$headline['url'].'" '.$target.'>'.$headline['subtitle'].$headline['title'].'</a></span></td></tr>';
        }
        echo '</table>';
    }

    echo '<p align="center"><a href="'.$_SERVER['PHP_SELF'].'?x=w" target="m">Home &gt;</a></p>';

}

function news_list_view_gm(&$news) {
    /* news list renderer ala google-mail (beta) */
    global $list_header_output,$list_top_output2,$app,$no;

    $iframes = array();
    $iframes_ids = 1;

    echo $list_header_output;

    echo '
<script>
    function Show(id) {
        if (document.getElementById(id).style.display == "") {
            document.getElementById(id).style.display = "none";
            if (document.getElementById(\'summary_\'+id) != null)
                document.getElementById(\'summary_\'+id).style.display = "";
        }
        else {
            HideAll();
            document.getElementById(id).style.display = "";
            if (document.getElementById(\'summary_\'+id) != null)
                document.getElementById(\'summary_\'+id).style.display = "none";
        }
    }

    function ShowWp() {
        HideAll();
        document.getElementById(\'newslist\').style.display = "none";
        document.getElementById(\'wp\').style.display = "";
    }

    function ShowNewsList() {
        HideAll();
        document.getElementById(\'wp\').style.display = "none";
        document.getElementById(\'newslist\').style.display = "";
    }


    function HideAll() {
        total_iframe = 23;
        for (i=1; i <= total_iframe; i++) {
            document.getElementById(\'news_\'+i).style.display = "none";
        }
        document.getElementById(\'wp\').style.display = "none";
    }
</script>';

    echo $list_top_output2;
    #~ echo ads_view($news);    # bug in firefox

    echo '<b><a href="#" onclick="return ShowWp()">Home</a> | <a href="#" onclick="return ShowNewsList()">News list</a> | <a href="'.$_SERVER['PHP_SELF'].'" target="_top">Normal Framed</a></b>';
    #~ echo '<table width="100%" border="0"><tr><td width="50%" valign="top">';
    global $welcomepage_output;

    echo '<div id="newslist" style="display:none;width:100%;">';

    // start view list
    ads_view($news);

    foreach ($news['headline'] as $headline) {  // view headlines
        $headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&amp;url='.urlencode($headline['url']).'&no=gm';
        if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
        $date = date('H:i',$headline['date']);
        $date_delta = str_time_delta(strtotime($date));
        $id = 'news_'.$iframes_ids++;
        $iframes[] = array($id,$headline['url']);
        echo "\r\n".'<p><span class="d">('.$date.')</span> <span class="j"><a href="#" onclick="return Show(\''.$id.'\')">'.$headline['subtitle'].$headline['title'].'</a></span> <span class=d>['.$date_delta.' lalu]</span>';
        echo '<span id="summary_'.$id.'" class="u">'.$headline['summary'].'</span>';
        echo "\r\n".'<div id="'.$id.'" style="display:none;width:100%;height:300px"><hr><iframe src="'.$headline['url'].'" style="width:100%;height:300px" align="center" marginwidth="0" marginheight="0" scrolling="auto"  frameborder="0"></iframe><hr></div>';

    }

    foreach ($news['prevnews'] as $headline) {  //  view prevnews
        $headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&amp;url='.urlencode($headline['url']).'&no=gm';
        if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
        $date = date('H:i',$headline['date']);
        $id = 'news_'.$iframes_ids++;
        $iframes[] = array($id,$headline['url']);
        echo "<span class=d>($date)</span> <span class=i><a href=\"#\" onclick=\"return Show('$id')\">{$headline['subtitle']}{$headline['title']}</a></span>";
        echo "\r\n".'<div id="'.$id.'" style="display:none;width:100%;height:300px"><hr><iframe src="'.$headline['url'].'" style="width:100%;height:300px" align="center" marginwidth="0" marginheight="0" scrolling="auto"  frameborder="0"></iframe><hr></div>';
        echo '<br>';
    }

    foreach ($news['topic'] as $topic) {    //  view topic news
        echo "<br><span class=i>{$topic['title']}</span><br>";
        foreach ($topic['news'] as $headline) {
            $headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&amp;url='.urlencode($headline['url']).'&no=gm';
            if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
            $date = date('H:i',$headline['date']);
            $id = 'news_'.$iframes_ids++;
            $iframes[] = array($id,$headline['url']);
            echo "<span class=d>($date)</span> <span class=i><a href=\"#\" onclick=\"return Show('$id')\">{$headline['subtitle']}{$headline['title']}</a></span>";
            echo "\r\n".'<div id="'.$id.'" style="display:none;width:100%;height:300px"><hr><iframe src="'.$headline['url'].'" style="width:100%;height:300px" align="center" marginwidth="0" marginheight="0" scrolling="auto"  frameborder="0"></iframe><hr></div>';
            echo '<br>';
        }
    }
    echo '</div>';

    echo '<div id="wp" style="width:100%;">';
    echo $welcomepage_output;
    echo '</div>';

    #~ foreach ($iframes as $iframe) {
        #~ list($id, $url) = $iframe;
        #~ echo '<iframe src="'.$url.'" style="display:none;width:100%;height:600px" align="center" marginwidth="0" marginheight="0" scrolling="auto" id="'.$id.'" frameborder="0"></iframe>';
    #~ }
    #~ echo '</td></tr></table>';

    // view footer
    global $list_footer,$stream_compress,$location,$hostname;
    $temp_orig = "http://$hostname/$location";
    $temp_stream = 'Stream: '; if (!$app['proxy_mode']) $temp_stream .= 'N/A'; elseif ($stream_compress) $temp_stream .= 'Compressed'; else $temp_stream .= 'Uncompressed';
    eval("\$list_footer = \"$list_footer\";");
    echo $list_footer;

}

function news_list_view_rss(&$news,$with_content=False) {
    /* news list renderer for RSS
    @with_content  True to include complete news body inside RSS
    */
    global $list_header_output,$list_top_output2,$app,$no,$author_name,$author_email;
    $buffer = '';
    $buffer .= '<?xml version="1.0"?>
<rss version="2.0">
<channel>
<title>Detik.Usable: berita cepat</title>
<link>'.($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'</link>
<description>'.htmlentities($app['version_description']).'</description>
<language>id</language>
<category>news</category>
<managingEditor>'.$author_email.'</managingEditor>
<webMaster>'.$author_email.'</webMaster>
<lastBuildDate>'.date('r').'</lastBuildDate>
<generator>'.$app['name'].' v'.$app['version'].'</generator>
';

    foreach ($news['headline'] as $headline) {  // view headlines
        $url = ($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?url='.urlencode($headline['url']);
        if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
        if ($with_content)
            $description = htmlentities(news_detail_view(get_news_detail($headline['url'], $headline['date']), False, False));
        else
            $description = htmlentities($headline['summary']);
        $buffer .= '
<item>
    <title>'.htmlentities(strip_tags($headline['subtitle'].$headline['title'])).'</title>
    <link>'.$url.'</link>
    <description>'.$description.'</description>
    <guid>'.$headline['url'].'</guid>
    <pubDate>'.date('r',$headline['date']).'</pubDate>
    <category>Headlines</category>
</item>
';
    }

    foreach ($news['prevnews'] as $headline) {  //  view prevnews
        $url = ($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?url='.urlencode($headline['url']);
        if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
        if ($with_content)
            $description = htmlentities(news_detail_view(get_news_detail($headline['url'], $headline['date']), False, False));
        else
            $description = '';
        $buffer .= '
<item>
    <title>'.htmlentities(strip_tags($headline['subtitle'].$headline['title'])).'</title>
    <link>'.$url.'</link>
    <description>'.$description.'</description>
    <guid>'.$headline['url'].'</guid>
    <pubDate>'.date ('r',$headline['date']).'</pubDate>
    <category>Previous News</category>
</item>
';
    }

    foreach ($news['topic'] as $topic) {    //  view topic news
        foreach ($topic['news'] as $headline) {
            $url = ($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?url='.urlencode($headline['url']);
            if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
            if ($with_content)
                $description = htmlentities(news_detail_view(get_news_detail($headline['url'], $headline['date']), False, False));
            else
                $description = '';
            $buffer .= '
<item>
    <title>'.htmlentities(strip_tags($headline['subtitle'].$headline['title'])).'</title>
    <link>'.$url.'</link>
    <description>'.$description.'</description>
    <guid>'.$headline['url'].'</guid>
    <pubDate>'.date('r',$headline['date']).'</pubDate>
    <category>'.$topic['title'].'</category>
</item>
';
        }
    }

    $buffer .= '</channel></rss>';

    return $buffer;
}

function news_detail_view(&$news,$complete=True,$with_header=True) {
    /* renderer for news detail/body */
    global $list_header_output,$url,$app;
    $buffer = '';
    //specialized first paragraph
    $news['content'] = preg_replace('|<B>(.*?)<P>|is','<span style=font-size:larger><B>\\1</span><P>',$news['content']);
    //fix url berita terkait
    $news['content'] = preg_replace('|<a href=("?)http://www.detik.com|is',"<a href=\\1{$_SERVER['PHP_SELF']}?url=http://www.detik.com",$news['content']);
    if ($with_header) $buffer .= $list_header_output;
    if ($_REQUEST['no'] == 'gm') $complete = False;

    if ($complete) {
        if ($news['date'] != '')
            $buffer .= '<small>'.date('Y-m-d H:i:s',$news['date']).'</small>';
        $buffer .= '<h3>'.$news['subtitle'].' '.$news['title'].'</h3>';
        $buffer .= '<p class="u">'.$news['reporter'].'</p>';
        $buffer .= ads_view($news);
        $buffer .= '<span class="u">'.$news['content'].'</span>';
    }
    else {
        $buffer .= '<span class=u>'.$news['content'].'</span>';
    }

    $buffer .= '<p><small><a href="'.$news['url'].'" style="color:#666">'.$news['url'].'</a></small></p>';

    #~ //        view footer
    #~ global $list_footer,$stream_compress;
    #~ $temp_orig = $url;
    #~ $temp_stream = 'Stream: '; if (!$app['proxy_mode']) $temp_stream .= 'N/A'; elseif ($stream_compress) $temp_stream .= 'Compressed'; else $temp_stream .= 'Uncompressed';
    #~ eval("\$list_footer = \"$list_footer\";");
    #~ echo $list_footer;

    return $buffer;

}

function get_news_list() {
    global $app,$hostname;
    $location =  '/index.php';
    $url = $hostname.$location;
    $cache_reload = $_REQUEST['cache_reload'];

    //check if already in cache
    if ($app['cache'] and !$cache_reload) {
        $urls = parse_url($url);
        $filename = 'cache/'.md5($urls['path']);
        if (file_exists($filename)) {
            $buffer = '';
            $fp = fopen($filename,'r');
            while(!feof($fp)) {
               $buffer .= fread($fp,1024);
            }
            fclose($fp);
            $news = unserialize($buffer);
            $from_cache = TRUE;
            #~ $news['content'] .= '<p><small><span style="color:#666">(news from cache)</span> - <a href="'.$_SERVER['PHP_SELF'].'?unxdt='.$_REQUEST['unxdt'].'&amp;url='.$_REQUEST['url'].'&cache_reload=1">Reload from Original</a></small>';

        }
    }

    $sock = new MySocket($hostname,80);
    if ($from_cache or !$sock->socket_open()) {
        //do nothing
    }
    else {
        $sock->sock_send_request($location);
        $header_buffer = $sock->sock_recv_header();
        $buffers = $sock->sock_recv_all();
        // if Content-Encoding: gzip, then body is gzipped. Unzipped first.
        if (preg_match('|Content-Encoding:\s*gzip|i',$header_buffer)) {
            $buffers = gzinflate(substr($buffers, 10,-4));  //skip the first 10 characters,as they are GZIP header, and php's gzinflate only need the data
        }
        $buffers_orig = $buffers;
        $news = parse_news_list($buffers);
    }

    if ($app['cache'] and !$from_cache and $news) {   //save serialized array to file
        if (!file_exists('cache')) mkdir('cache',0755);
        $urls = parse_url($url);
        $filename = 'cache/'.md5($urls['path']);
        $news['cache'] = time();    # record the time of cache last updated
        $buffer = serialize($news);
        unset($news['cache']);  # first time data should not be called "cached at 0 second"
        $fp = fopen($filename,'w');
        fwrite($fp,$buffer);
        fclose($fp);
    }
    return $news;
}

function get_news_detail($url,$unxdt='') {
    /*
    @url = complete absolute url of news detail
    @unxdt = unix time, datetime of news detail (passed from param for RSS, from _REQUEST for others)
    */
    global $app,$news;
    $news = array();
    $unxdt = ($unxdt == '')? $_REQUEST['unxdt']: $unxdt;
    $cache_reload = $_REQUEST['cache_reload'];

    if (preg_match('/http:\/\/([^\/]*)(\/.*)/',$url,$result)) {
        $hostname = $result[1];
        $location = $result[2];
    }
    else {
        $location = '/peristiwa'.$url;
    }

    //check if already in cache
    if ($app['cache'] and !$cache_reload) {
        $urls = parse_url($url);
        $filename = 'cache/'.md5($urls['path']);
        if (file_exists($filename)) {
            $buffer = '';
            $fp = fopen($filename,'r');
            while(!feof($fp)) {
               $buffer .= fread($fp,1024);
            }
            fclose($fp);
            $news = unserialize($buffer);
            $news_from_cache = TRUE;
            $news['content'] .= '<p><small><span style="color:#666">(news from cache)</span> - <a href="'.$_SERVER['PHP_SELF'].'?unxdt='.$_REQUEST['unxdt'].'&amp;url='.$_REQUEST['url'].'&cache_reload=1">Reload from Original</a></small>';
        }
    }
    $sock = new MySocket($hostname,80);
    if ($news_from_cache or !$sock->socket_open())    {
        //do nothing
    }
    else {
        $sock->sock_send_request($location);
        $header_buffer = $sock->sock_recv_header();
        $buffers = $sock->sock_recv_all();

        // Content-Encoding: gzip? then body is gzipped. Unzipped first.
        if (preg_match('|Content-Encoding:\s*gzip|i',$header_buffer)) {
            $buffers = gzinflate(substr($buffers, 10)); //skip the first 10 characters,as they are GZIP header, and php's gzinflate only need the data
        }

        $buffers_orig = $buffers;

        $regex_start = '<blockquote>';
        $regex_end = '<!-- FORM';
        $regex_1 = "|$regex_start(.*?)$regex_end|is";

        if (!preg_match($regex_1,$buffers,$result)) {
            add_log(__LINE__, "parser: newsdetail: 1: fail ($regex_1)");
            add_log(__LINE__, 'parser: '.$buffers);
        }
        else {
            add_log(__LINE__, 'parser: newsdetail: 1: success');
            $buffers = $result[1];
        }

        if (preg_match('/berita-foto/',$url)) { // this channel is different enough, that need specific pregmathicng
            // -- regex_detail_beritafoto
            //        title
            $regex_start = '<FONT size=5>';
            $regex_end = '</font>';
            $regex = "|$regex_start(.*?)$regex_end|is";

            if (!preg_match($regex,$buffers,$res)) {
                add_log(__LINE__, "parser: newsdetail: title: fail ($regex)");
                dump_buffer ($buffers);
            }
            else {
                add_log(__LINE__, 'parser: newsdetail: title: success');
                $news['title'] = $res[1];
            }

            //        reporter
            $regex_start = '<BR><FONT color=#ff0000 size=2>';
            $regex_end = '</font>';
            $regex = "|$regex_start(.*?)$regex_end|is";

            if (!preg_match($regex,$buffers,$res)) {
                add_log(__LINE__, "parser: newsdetail: reporter: fail ($regex)");
                dump_buffer ($buffers);
            }
            else {
                add_log(__LINE__, 'parser: newsdetail: reporter: success');
                $news['reporter'] = $res[1];
            }

            //        content
            $regex_start = '<P align="Justify">';
            $regex = "|$regex_start(.*)|is";

            if (!preg_match($regex,$buffers,$res)) {
                add_log(__LINE__, "parser: newsdetail: content: fail ($regex)");
                dump_buffer ($buffers);
            }
            else {
                add_log(__LINE__, 'parser: newsdetail: content: success');
                $news['content'] = $res[1];
            }

            //        'recondition' urls in content
            $news['content'] = preg_replace('|<a href=(.?)http://www.detik.com/|',"<a href=\\1{$_SERVER['PHP_SELF']}?url=http://www.detik.com/",$news['content']);
        }
        elseif (preg_match('/detikhot/',$url)) { // this channel is different enough, that need specific pregmathicng
            // -- regex_detail_detikhot
            //        sub-title
            $regex_start = '<font class=.?subjudulberita.?>';
            $regex_end = '</font>';
            $regex = "|$regex_start(.*?)$regex_end|is";

            if (!preg_match($regex,$buffers,$res)) {
                add_log(__LINE__, "parser: newsdetail: subtitle: fail ($regex)");
            }
            else {
                add_log(__LINE__, 'parser: newsdetail: subtitle: success');
                $news['subtitle'] = $res[1];
            }

            //        title
            $regex_start = '<font color="#669900" size="4" face="Arial, Helvetica, sans-serif">';
            $regex_end = '</font>';
            $regex = "|$regex_start(.*?)$regex_end.*$|is";

            assert(preg_match($regex,$buffers,$res));
            add_log(__LINE__, 'parser: newsdetail: title: success');
            $news['title'] = $res[1];

            //        reporter
            $regex_start = "<td valign=\"top\"><strong> ";    # yes ada space di suffix-nya
            $regex_end = '</strong>';
            $regex = "|$regex_start(.*?)$regex_end|is";

            #assert(preg_match($regex,$buffers,$res));
            preg_match($regex,$buffers,$res);
            add_log(__LINE__, 'parser: newsdetail: reporter: success');
            $news['reporter'] = $res[1];

            //        content
            $regex_start = '<tr valign="top" width="525">.*?<td valign="top">';
            #$regex_end = '(?:<\/font>|$)';    // </font> or end of string
            $regex_end = '(?:<\/td>|$)';    // </font> or end of string
            $regex = "/$regex_start(.*?)$regex_end/is";

            assert(preg_match($regex,$buffers,$res));
            add_log(__LINE__, 'parser: newsdetail: content: success');
            $news['content'] = $res[1];

            //clean html
            $news['reporter'] = strip_tags($news['reporter'],'<b></b><i></i>');
            $news['content'] = strip_tags($news['content'],'<b></b><i></i><a></a><p></p><br>');


        }
        else {
            // -- regex_detail_normal

            //        sub-title
            $regex_start = '<font class=.?subjudulberita.?>';
            $regex_end = '</font>';
            $regex = "|$regex_start(.*?)$regex_end|is";

            if (!preg_match($regex,$buffers,$res)) {
                add_log(__LINE__, "parser: newsdetail: subtitle: fail ($regex)");
            }
            else {
                add_log(__LINE__, 'parser: newsdetail: subtitle: success');
                $news['subtitle'] = $res[1];
            }

            //        title
            $regex_start = '<font class=.?judulberita.?>';
            $regex_end = '</font>';
            $regex = "|$regex_start(.*?)$regex_end.*$|is";

            assert(preg_match($regex,$buffers,$res));
            add_log(__LINE__, 'parser: newsdetail: title: success');
            $news['title'] = $res[1];

            //        reporter
            $regex_start = '<font class=.?textreporter.?>';
            $regex_end = '</font>';
            $regex = "|$regex_start(.*?)$regex_end|is";

            assert(preg_match($regex,$buffers,$res));
            add_log(__LINE__, 'parser: newsdetail: reporter: success');
            $news['reporter'] = $res[1];

            //        content
            $regex_start = '<font class=.?textberita.?>';
            $regex_end = '(?:<\/font>|$)';    // </font> or end of string

            $regex = "/$regex_start(.{300,}?)$regex_end/is";
            #~ $regex = "/$regex_start(.*?detikcom.*?)$regex_end/is";   # siap siap

            assert(preg_match($regex,$buffers,$res));
            add_log(__LINE__, 'parser: newsdetail: content: success');
            $news['content'] = $res[1];

            //clean html
            $news['reporter'] = strip_tags($news['reporter'],'<b></b><i></i>');
            $news['content'] = strip_tags($news['content'],'<b></b><i></i><a></a><p></p><br>');
        }

        ads_parse($buffers_orig);

    }
    $news['url'] = $url;    # save url, will parse later
    # its hard to really parse datetime from url, but easier done from news list, so if newslist provide one, we'll use it
    $news['date'] = $unxdt;


    if ($app['cache'] and !$news_from_cache and $news) {   //save serialized array to file
        if (!file_exists('cache')) mkdir('cache',0755);
        $urls = parse_url($url);
        $filename = 'cache/'.md5($urls['path']);
        $buffer = serialize($news);
        $fp = fopen($filename,'w');
        fwrite($fp,$buffer);
        fclose($fp);
    }

    return $news;
}


function parse_news_list($buffers) {
    global $news;
    $news = array();
    ads_parse($buffers);
    //    narrowing-in to "prevnews" content
    add_log(__LINE__, 'parser: prevnews: start');
    $regex_prevnews_1 = '=.nmkanal';
    $regex_prevnews_2 = '<IMG';
    $regex_prevnews = "/$regex_prevnews_1(.*?)$regex_prevnews_2(.*)/s";
    unset($result);
    if (!preg_match($regex_prevnews,$buffers,$result)) {
        add_log(__LINE__, "parser: prevnews: fail ($regex_prevnews)");
        add_log(__LINE__, 'parser: '.$buffers);
    }
    else {
        add_log(__LINE__, 'parser: prevnews: success');
        $pn_buf = $result[1];
        $buffers = $result[2];
        //parser utk tanggal+jam, hyperlink, dan judul
        #~ $regex_prevnews_all = "/(\d+\/\d+\/\d+.*?) WIB.*?<A href=\"([^\"]*)\"[^>]*>(.*?)<\/A>/is";
        #~ $regex_prevnews_all = '/(\d+\/\d+\/\d+.*?)<.*?<a href="([^"]+)"[^>]*>(.*?)<\/A>/is';
        $regex_prevnews_all = '/(\d+\/\d+\/\d+.*?)<.*?<a href="([^"]+url=[^"]+)" class=[^>]*>(.*?)<\/A>/is';
        unset($result);
        assert(preg_match_all($regex_prevnews_all,$pn_buf,$result));
        add_log(__LINE__, 'parser: prevnews: all: success');
        for ($i = 0; $i < 6; $i++) {
            $url = $result[2][$i];
            $date = $result[1][$i];
            $title_temp = $result[3][$i];

            #~ echo "<p>Data: $date, URL: $url, Title:$title_temp";

            //    prevnews->date
            $date = preg_replace('/([0-9]*)\/([0-9]*)\//','\\2/\\1/', $date);
            $news['prevnews'][$i]['date'] = strtotime($date);

            //    prevnews->url
            if (!preg_match('/http:\/\//',$url)) {   //  makeit absolute url
                add_log(__LINE__, "parser: prevnews($i): url: add absolute url");
                $url = 'http://www.detik.com'.$url;
            }

            if (preg_match('/\?url=(.*)/',$url,$url_res)) {  // if link formatted like ...?url=http://.... retrieve the param value instead
                add_log(__LINE__, "parser: prevnews($i): url: get from param");
                $url = $url_res[1];
            }
            $news['prevnews'][$i]['url'] = $url;

            //    prevnews->subtitle
            $regex_prevnews_subtitle = '/nonhlsubJudul.>(.*?)<\/span>/';
            if (!preg_match($regex_prevnews_subtitle,$title_temp,$subtitle_res)) {
                add_log(__LINE__, "parser: prevnews($i): no-subtitle");
            }
            else {
                add_log(__LINE__, "parser: prevnews($i): has subtitle");
                $news['prevnews'][$i]['subtitle'] = $subtitle_res[1];
            }

            //    prevnews->title
            $regex_prevnews_title = '/nonhlJudul.>(.*)/';
            if (!preg_match($regex_prevnews_title,$title_temp,$title_res)) {
                add_log(__LINE__, "parser: prevnews($i): no-title ($regex_prevnews_title)");
            }
            else {
                add_log(__LINE__, "parser: prevnews($i): has title");
                $news['prevnews'][$i]['title'] = $title_res[1];
            }
        }
    }

    //    narrowing-in to headline news content
    add_log(__LINE__, 'parser: headline: start');
    $regex_headline_1 = '<span class="tanggal">([^<]*)<';
    $regex_headline_2 = '</td';
    $regex_headline = "/{$regex_headline_1}(.*?){$regex_headline_2}(.*)/is";
    $regex_headline = '|(<span class="tanggal">.*?)</td>\s+<td valign="top"(.*)|is';
    add_log(__LINE__, 'Matching "'.$regex_headline.'" to "'.$buffers.'"');
    assert(preg_match($regex_headline,$buffers,$result));
    add_log(__LINE__, 'parser: headline: success');
    $hl_buf = $result[1];
    $buffers = $result[2];
    #~ $regex_headline_all = '|tanggal.>[^,]*,(.*?) WIB<.*?<A href="([^"]+)".*?parent.>(.*?<span class="summary">.*?</span>)|is';
    $regex_headline_all = '|tanggal.>[^,]*,(.*?) WIB<.*?<A href="([^"]+url=[^"]+)" class=[^>]*>(.*?<span class="summary">.*?</span>)|is';
    assert(preg_match_all($regex_headline_all,$hl_buf,$result));
    add_log(__LINE__, 'parser: headline: all: success');
    for ($i = 0; $i < 5; $i++) {
        $date = $result[1][$i];
        $url = $result[2][$i];
        $title = $result[3][$i];

        //    headline->url
        if (!preg_match('/http:\/\//',$url)) { //        makeit absolute url
            add_log(__LINE__, "parser: headline($i): url: add absolute url");
            $url = 'http://www.detik.com'.$url;
        }

        if (preg_match('/\?url=(.*)/',$url,$url_res)) { // if link formatted like ...?url=http://.... retrieve the param value instead
            add_log(__LINE__, "parser: headline($i): url: get from param");
            $url = $url_res[1];
        }
        $news['headline'][$i]['url'] = $url;

        //    headline->subtitle
        $regex_headline_subtitle = '/subjudul.>(.*?)<\/span/is';
        if (!preg_match($regex_headline_subtitle,$title,$subtitle_res)) {
            add_log(__LINE__, "parser: headline($i): subtitle: fail");
        }
        else {
            add_log(__LINE__, "parser: headline($i): subtitle: success");
            $news['headline'][$i]['subtitle'] = $subtitle_res[1];
        }

        //    headline->title
        $regex_headline_title = '|strJudul.>(.+?)</span|is';
        if (!preg_match($regex_headline_title,$title,$title_res)) {
            add_log(__LINE__, "parser: headline($i): title: fail ($regex_headline_title)");
        }
        else {
            add_log(__LINE__, "parser: headline($i): subtitle: success");
            $news['headline'][$i]['title'] = $title_res[1];
        }

        //    headline->summary
        $regex_headline_summary = '/summary.>(.*?)<\/span/s';
        if (!preg_match($regex_headline_summary,$title,$summary_res)) {
            add_log(__LINE__, "parser: headline($i): summary: fail ($regex_headline_summary)");
        }
        else {
            add_log(__LINE__, "parser: headline($i): summary: success");
            $news['headline'][$i]['summary'] = $summary_res[1];
        }

        //    headline->date
        $date = preg_replace('/([0-9]*)\/([0-9]*)\//','\\2/\\1/', $date);
        $news['headline'][$i]['date'] = strtotime($date);


        #echo "<p>Data: $date, URL: $url, Fulltitle: $title, Title:{$title_res[1]} Subtitle: {$subtitle_res[1]}";
    }

    //    narrowing-in to topic news content
    add_log(__LINE__, 'parser: topic: start');
    $regex_topic_all = '/<td width="100%" align="left" colspan="2">(.*?)<\/tr>(.*?)<table/si';

    if (!preg_match_all($regex_topic_all,$buffers,$result)) {
        add_log(__LINE__, "parser: topic: fail ($regex_topic_all)");
    }
    else {
        add_log(__LINE__, 'parser: topic: success');
        $tp_buff = $result;
        $count_topic = count($tp_buff[1]);  # daftar topik
        for ($i = 0; $i < $count_topic; $i++) {
            $title = trim(strip_tags($tp_buff[1][$i]));
            if ($title == '') continue; // 9nov04, skip if topic has no title
            $news['topic'][$i]['title'] = $title;  // topic->title
            #~ $regex_topic_detail = '|90%">(.*?)<a.*?</a><a href="([^"]+)"[^>]*>.*?"judulhlbawah">(.*?)</font>|is';
            $regex_topic_detail = '|90%">(.*?)<a href="([^"]+tahun/[^"]+)"[^>]*>.*?"judulhlbawah">(.+?)</font>|is';
            assert(preg_match_all($regex_topic_detail,$tp_buff[2][$i],$tpdetail_buff));
            #~ preg_match_all($regex_topic_detail,$tp_buff[2][$i],$tpdetail_buff);
            add_log(__LINE__, "parser: topic($i): detail: success");

            $titles = $tpdetail_buff[3];
            $urls = $tpdetail_buff[2];
            $dates = $urls; //date will be parsed from url

            $count_news = count($tpdetail_buff[1]);
            for ($j = 0; $j < $count_news; $j++) {
                $news['topic'][$i]['news'][$j]['title'] = $titles[$j];  //    topic->title->title

                //    topic->title->url
                $regex_topic_url = '/\?url=(.*)/';
                if (!preg_match($regex_topic_url,$urls[$j],$urls_res))
                {
                    //try apakah ini http biasa
                    $regex_topic_url = '|^http://|';
                    if (!preg_match($regex_topic_url,$urls[$j],$urls_res)) {
                        add_log(__LINE__, "parser: topic($i): detail($j): url: fail");
                    }
                    else {
                        add_log(__LINE__, "parser: topic($i): detail($j): url: success (2nd try)");
                        $news['topic'][$i]['news'][$j]['url'] = $urls[$j];
                    }
                }
                else {
                    add_log(__LINE__, "parser: topic($i): detail($j): url: success");
                    $news['topic'][$i]['news'][$j]['url'] = $urls_res[1];
                }

                //    topic->title->date
                //  http://www.detiknews.com/index.php/detik.read/tahun/2004/bulan/04/tgl/15/time/1298/idnews/127625/idkanal/10
                //http://jkt1.detiksport.com/index.php/detik.read/tahun/2004/bulan/10/tgl/27/time/715/idnews/231219/idkanal/75
                #~ $regex_headline_date = '|/tahun/(\d*)/bulan/(\d*)/tgl/(\d*)/time/(\d\d)(\d\d)|i';
                $regex_headline_date = '|/tahun/(\d*)/bulan/(\d*)/tgl/(\d*)/|i';   # skip the time. too unpredictable.
                if (!preg_match($regex_headline_date,$dates[$j],$tpdetail_res)) {
                    add_log(__LINE__, "parser: topic($i): detail($j): date: fail");
                }
                else {
                    add_log(__LINE__, "parser: topic($i): detail($j): date: success");
                    $tgl = $tpdetail_res;
                    $news['topic'][$i]['news'][$j]['date'] = mktime(0,0,0,$tgl[2],$tgl[3],$tgl[1]);
                }
            }
        }
    }
    return $news;
}

//    START
add_log(__LINE__, "{$app['name']} v{$app['version']} starting up from {$_SERVER['SERVER_ADDR']}/{$_SERVER['SERVER_PORT']}");

ob_end_flush();

if (isset($url)) {
    if ($app['proxy_mode']) {
        $detikusable_mode = 'news_detail_from_node';
    }
    else {
        $detikusable_mode = 'news_detail';
    }
}
elseif ($x=='i' or $no=='frame' or $no=='gm' or $no=='rss2') {
    if ($app['proxy_mode']) {
        $detikusable_mode = 'news_list_from_node';    //retrieve serialized+processed html containing ready-to-view array from other detik.usable node.
    }
    else {
        $detikusable_mode = 'news_list';    //retrieve raw html from detik, parse, and output as new detik.usable-style design
    }
}
elseif ($x=='w') {
    $detikusable_mode = 'welcome_page';
}
elseif ($x=='s') {
    $detikusable_mode = 'source_code';
}
elseif ($_REQUEST['au']) {
    $detikusable_mode = 'auto_update';
}
elseif ($_REQUEST['cm']) {
    $detikusable_mode = 'cache_management';
}
elseif ($no == 'bcache') {
    $detikusable_mode = 'browse_cache';
}
else {
    $detikusable_mode = 'frame_set';
}
add_log(__LINE__, 'mode: '.$detikusable_mode);

#=============== NEWS DETAIL PAGE ===========================
if ($detikusable_mode == 'news_detail') {
    $news = get_news_detail($_REQUEST['url']);

    if ($as_node) {
        set_magic_quotes_runtime(0); //to avoid null char be converted to \0
        $news_serial = serialize($news);
        if (!$app['zlib_support'] or $uncompressed) echo $news_serial;
        else echo gzcompress($news_serial);
    }
    else {
        echo news_detail_view($news);
    }
}

#=============== NEWS LIST PAGE ===========================
if ($detikusable_mode == 'news_list') {
    $news = get_news_list();
    if ($as_node)  {
        set_magic_quotes_runtime(0); //to avoid null char be converted to \0
        $news_serial = serialize($news);
        if (!$app['zlib_support'] or $uncompressed) echo $news_serial;
        else echo gzcompress($news_serial);
    }
    else {
        if ($no == 'gm')
            news_list_view_gm($news);
        elseif ($no == 'rss2')
            echo news_list_view_rss($news,$_REQUEST['complete']);
        else {
            echo $list_header_output;
            echo $list_top_output;
            if ($news['cache']) echo '<div id="cache_set">';
            news_list_view($news);
            flush();
            if ($news['cache']) echo '</div>';
            if ($news['cache'] and (time() - $news['cache'])>$app['newslist_cache_revalidate']) {
                $_REQUEST['cache_reload'] = 1;
                $news = get_news_list();
                echo '<div id="latest_set" style="display:none;">';
                news_list_view($news);
                echo '</div>';
                echo '<script>document.getElementById("latest_set").style.display = ""</script>';
                echo '<script>document.getElementById("cache_set").style.display = "none"</script>';
            }

            // view footer
            global $list_footer,$stream_compress,$location,$hostname;
            $temp_orig = "http://$hostname/$location";
            $temp_stream = 'Stream: '; if (!$app['proxy_mode']) $temp_stream .= 'N/A'; elseif ($stream_compress) $temp_stream .= 'Compressed'; else $temp_stream .= 'Uncompressed';
            eval("\$list_footer = \"$list_footer\";");
            echo $list_footer;

        }
    }
}

#=============== NEWS LIST SUPPLIER NODE PAGE ===========================
if ($detikusable_mode == 'news_list_from_node')
{
    $app['proxy_url'] .= '?x=i&as_node=1';
    if (!$app['zlib_support']) $app['proxy_url'] .= '&uc=1';    //ask uncompressed stream if i don't support zlib library
    $url_parsed = parse_url($app['proxy_url']);
    $sock = new MySocket($url_parsed['host'],$url_parsed['port']!=''? $url_parsed['port'] : 80);
    if (!$sock->socket_open()) die('Cannot contact repository at '.$app['proxy_url']);
    $sock->sock_send_request($url_parsed['path'].'?'.$url_parsed['query']);
    $sock->sock_recv_header();
    $buffer = $sock->sock_recv_all();
    if ($buffer == '') {
        write_error('newslist: from node: Unable to download from node');
    }
    else {
        $buffer_orig = $buffer;
        if ($app['zlib_support']) {
            $buffer = @gzuncompress($buffer);   // try to uncompress
            if (!$buffer or $buffer == '' or is_bool($buffer)) {
                //write_error('newslist: from node: Unable to uncompress data');
                //let's assume it's not gzcompressed
                add_log(__LINE__, 'newslist: from node: unable to uncompress data');
                $buffer = $buffer_orig;  // revert to original
            }
            else {
                $stream_compress = TRUE;
            }
        }
        $buffer = unserialize($buffer);
        if (!buffer) {
            write_error('newslist: from node: Unable to unserialize data');
            exit;
        }

        if (!is_array($buffer)) {
            write_error("newslist: from node: Data is not formatted correctly: X{$buffer}X");
            exit;
        }

        news_list_view($buffer);

    }
}

#=============== NEWS DETAIL SUPPLIER NODE PAGE ===========================
if ($detikusable_mode == 'news_detail_from_node')
{
    $app['proxy_url'] .= "?url=$url&as_node=1";
    if (!$app['zlib_support'])    $app['proxy_url'] .= '&uc=1';    //ask uncompressed stream if i don't support zlib library
    $url_parsed = parse_url($app['proxy_url']);
    $sock = new MySocket($url_parsed['host'],$url_parsed['port']!=''? $url_parsed['port'] : 80);
    if (!$sock->socket_open()) die('Cannot contact repository at '.$app['proxy_url']);
    $sock->sock_send_request($url_parsed['path'].'?'.$url_parsed['query']);
    $sock->sock_recv_header();
    $buffer = $sock->sock_recv_all();

    if ($buffer == '') {
        write_error('newsdetail: from node: Unable to download from node');
    }
    else {
        $buffer_orig = $buffer;
        if ($app['zlib_support']) {
            $buffer = @gzuncompress($buffer);
            if (!$buffer) {
                //write_error('newslist: from node: Unable to uncompress data');
                //let's assume it's not gzcompressed
                add_log(__LINE__, 'newslist: from node: unable to uncompress data');
                $buffer = $buffer_orig;
            }
            else {
                $stream_compress = TRUE;
            }
        }

        $buffer = unserialize($buffer);
        if (!buffer) {
            write_error('newsdetail: from node: Unable to unserialize data');
            exit;
        }

        if (!is_array($buffer)) {
            write_error('newsdetail: from node: Data is not formatted correctly');
            exit;
        }
        echo news_detail_view($buffer);
    }
}

#=============== WELCOME PAGE ===========================
if ($detikusable_mode == 'welcome_page') {
    $page = $_REQUEST['page'] == ''? '1': $_REQUEST['page'];
    echo $header_ouput;
    if ($page == '1')
        echo $welcomepage_output;
    elseif ($page == '2')
        echo $welcomepage_output_2;
}

#=============== SOURCE CODE VIEWER PAGE ===========================
if ($detikusable_mode == 'source_code') {
    show_source(__FILE__);
}

#=============== FRAME SET PAGE ===========================
if ($detikusable_mode == 'frame_set') {
    echo $frameset_output;
}

#=============== AUTO-UPDATE PAGE ===========================
if ($detikusable_mode == 'auto_update') {
    if (!$_REQUEST['commit']) {
        $target_filename = basename(__FILE__);
        echo $list_header_output;
        echo '<h4>Check versi terbaru</h4>';

        //check permission to write
        echo '<br>..checking write permission to this file..';
        if (!is_writable(__FILE__)) {
            echo '<b>Fail</b>';
            echo '<br>..trying to chmod..';
            if (@!chmod(__FILE__,0777)) {    //test ubah permission
                echo '<b>Fail</b>';
                echo '<br>..checking folder permission to insert new file..';
                if (!is_writable(dirname(__FILE__))) {  // coba simpan ke file yg berbeda di folder yg sama
                    echo '<b>Fail</b>';
                    echo '<p>Sorry, akses tulis ke "'.__FILE__.'" tidak ada. Coba ubah permission file tersebut ke 777. Di linux, coba: <br><i>chmod 777 '.__FILE__.'</i>';
                    echo "<p><a href={$_SERVER['PHP_SELF']}?x=w>Back to welcome page</a>";
                    exit;
                }
                else {
                    $target_filename = 'index2.php';
                    echo '<b>Success, will be written to '.dirname(__FILE__).'/'.$target_filename.'</b>';
                }
            }
            else {
                echo '<b>Success</b>';
            }
        }
        else {
            echo '<b>Success</b>';
        }
        flush();

        //compare version
        $url_parsed = parse_url($app['update_url']);
        $sock = new MySocket($url_parsed['host'],$url_parsed['port']!=''? $url_parsed['port'] : 80);
        if (!$sock->socket_open()) die('Cannot contact repository at '.$app['update_url']);
        $sock->sock_send_request($url_parsed['path'].'?'.$url_parsed['query']);
        $sock->sock_recv_header();
        $buffers = $sock->sock_recv_all();
        if ($buffers == '') {
            echo '<h1>update url:'.$app['update_url'].' cannot be contacted!</h1>';
            exit;
        }

        if (preg_match('/\$app\[\'version\'\]\s*=\s*"([^"]*)"/i',$buffers,$remote_res))
            $remote_version = $remote_res[1];
        else
            $remote_version = '0.0';
        list($remote_major, $remote_minor) = explode('.',$remote_version,2);    # split into major and minor version
        list($local_major, $local_minor) = explode('.',$app['version'],2);

        echo '<ul><li>Versi detik.usable ini: <b>'.$local_major.'.'.$local_minor.'</b><li>Versi detik.usable terbaru: <b>'.$remote_major.'.'.$remote_minor.'</b></ul>';
        if ($remote_major > $local_major or ($remote_major == $local_major and $remote_minor > $local_minor))
            echo "<p><form method=get action={$_SERVER['PHP_SELF']}><input type=hidden name=au value=1><input type=hidden name=commit value=1><input type=hidden name=target_filename value='".htmlentities($target_filename)."'><input type=submit value=\"Update ke $remote_version\"></form>";
        else echo "<p>detik.usable ini sudah versi terbaru<p><form method=get action={$_SERVER['PHP_SELF']}><input type=hidden name=au value=1><input type=hidden name=commit value=1><input type=hidden name=target_filename value='".htmlentities($target_filename)."'><input type=submit value=\"Force update again\"></form>";
        echo "<p><a href={$_SERVER['PHP_SELF']}?x=w>Back to welcome page</a>";
    }
    else {
        $url_parsed = parse_url($app['update_url']);
        $sock = new MySocket($url_parsed['host'],$url_parsed['port']!=''? $url_parsed['port'] : 80);
        if (!$sock->socket_open()) die('Cannot contact repository at '.$app['update_url']);
        $sock->sock_send_request($url_parsed['path'].'?'.$url_parsed['query']);
        $sock->sock_recv_header();
        $buffers = $sock->sock_recv_all();
        if ($buffers == '') {
            write_error("auto_update: Unable to get latest version at {$app['update_url']}");
            echo "<p><a href={$_SERVER['PHP_SELF']}?x=w>Back to welcome page</a>";
        }
        else {
            $target = dirname(__FILE__).'/'.$_REQUEST['target_filename'];
            echo '<br>...writing to '.$target;
            $fp = fopen($target,'w');
            fwrite($fp,$buffers);
            fclose($fp);
            echo '<br><b>Success</b>';
            echo $list_header_output;
            echo '<h4>Update Berhasil</h4>';
            $redirect = dirname($_SERVER['PHP_SELF']).'/'.$_REQUEST['target_filename'];
            $redirect = str_replace('//','/',$redirect);
            echo '<p><a href="'.$redirect.'" target="_top">Reload detik.usable</a>';
        }
    }
}

#=============== CACHE MANAGEMENT PAGE ===========================
if ($detikusable_mode == 'cache_management') {
    if (!$_REQUEST['commit'] or ($_REQUEST['confirm_text'] != $_REQUEST['confirm_text2'])) {
        $dirsize = 0;
        $dh = opendir('cache');
        while (false !== ($filename = readdir($dh))) if (($file_name != '.' && $file_name != '..')) $dirsize += filesize('cache/'.$filename);
        $cache_size = round($dirsize/1024,2);
        echo $list_header_output;
        echo '<h4>Pengaturan Cache</h4>';
        echo '<p>Total space yang digunakan cache: '.$cache_size.' KB';
        if ($cache_size) {
            $crazy_number = rand(1000,9999);
            if ($cache_size > 0) echo '<p><form action="'.$_SERVER['PHP_SELF'].'"><input type=hidden name=cm value="1"><input type=hidden name=commit value="1"><p>Untuk menghindari search engine spiderbot ngutak ngatik, tulis angka berikut ini untuk mengosongkan cache: <b>'.$crazy_number.'</b><br>';
            if ($_REQUEST['confirm_text'] != $_REQUEST['confirm_text2']) {
                echo '<span style="color:f00">Try again</span>';
            }
            echo '<input type=text name=confirm_text><input type=hidden name=confirm_text2 value="'.$crazy_number.'"><input type=submit></form>';
        }
        echo "<p><a href={$_SERVER['PHP_SELF']}?x=w>Back to welcome page</a>";
    }
    else {
        $dh = opendir('cache');
        while (false !== ($filename = readdir($dh))) if (($file_name != '.' && $file_name != '..')) @unlink('cache/'.$filename);
        echo $list_header_output;
        echo '<h4>Cache telah dikosongkan</h4>';
        echo "<p><a href={$_SERVER['PHP_SELF']}?x=w>Back to welcome page</a>";
    }
}

#=============== BROWSE CACHE PAGE ===========================
if ($detikusable_mode == 'browse_cache') {
    $display = '';
    # iterate cache file list
    $dh = opendir('cache');
    $cached_news = array();
    while (false !== ($filename = readdir($dh))) {
        if ($filename == '.' or $filename == '..') continue;
        # open and unserialize into var
        ob_start();
        readfile('cache/'.$filename);
        $buffer = ob_get_contents();
        ob_end_clean();
        $news = unserialize($buffer);
        unset($news['content']); # preserve memory
        # get date, title, url and append into list
        #~ $news['title']
        #~ $news['url']
        if ($news['date'] == '')
            $str_date = 'unknown';
        else {
            $tgl = getdate($news['date']);
            $str_date = mktime(0,0,0,$tgl['mon'],$tgl['mday'],$tgl['year']);
        }
        $cached_news[$str_date][] = $news;  # memory hogging for large cache. should be parsed at display iteration.
    }
    if (!$cached_news) {
        $display .= $list_header_output;
        $display .= '<h3>Cache is empty</h3>'.$list_footer;
        echo $display;
        return;
    }


    # sort by date desc, group by same date
    # - sort daily date first
    # new: con
    echo $list_header_output;
    krsort($cached_news,SORT_NUMERIC);
    foreach ($cached_news as $str_date=>$news_list) {
        if ($str_date == 'unknown') continue;
        $unx_date_group = $str_date;
        $str_date = $hari[date('w',$unx_date_group)].',&nbsp;'.date('j',$unx_date_group).'&nbsp;'.$bulan[date('n',$unx_date_group)].date(' Y',$unx_date_group);
        #~ $display .= '<p><b>'.$str_date.'</b>';
        echo '<p><b>'.$str_date.'</b>';
        #~ $display .= '<small>';
        echo '<small>';
        foreach ($news_list as $news) {
            $dateme = date('H:i',$news['date']);
            if ($dateme == '00:00') $dateme = '';
            else $dateme .= ' - ';
            #~ $display .= '<br>'.$dateme.'<a href="'.$_SERVER['PHP_SELF'].'?url='.urlencode($news['url']).'" target="m">'.$news['title'].'</a>';
            echo '<br>'.$dateme.'<a href="'.$_SERVER['PHP_SELF'].'?url='.urlencode($news['url']).'" target="m">'.$news['title'].'</a>';
        }
        #~ $display .= '</small>';
        echo '</small>';
    }
    if ($cached_news['unknown']) {
        #~ $display .= '<p><b>Unknown date</b>';
        echo '<p><b>Unknown date</b>';
        #~ $display .= '<small>';
        echo '<small>';
        foreach ($cached_news['unknown'] as $news) {
            #~ $display .= '<br> - <a href="'.$_SERVER['PHP_SELF'].'?url='.urlencode($news['url']).'" target="m">'.$news['title'].'</a>';
            echo '<br> - <a href="'.$_SERVER['PHP_SELF'].'?url='.urlencode($news['url']).'" target="m">'.$news['title'].'</a>';
        }
        #~ $display .= '</small>';
        echo '</small>';
    }

    #~ $display .= '<p align="center"><a href="'.$_SERVER['PHP_SELF'].'?x=w" target="m">Home &gt;</a></p>';
    echo '<p align="center"><a href="'.$_SERVER['PHP_SELF'].'?x=w" target="m">Home &gt;</a></p>';

    # display
    #~ echo $list_header_output.$display.$list_footer;
    eval("\$list_footer = \"$list_footer\";");
    echo $list_footer;
}
