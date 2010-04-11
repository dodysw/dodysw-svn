<?
$app['name'] = "berita.usable";
$app['version'] = "2.6";

//  berita.usable: a fast-download generic news sites
//    Author: dody suria wijaya - dswsh@plasa.com
//    License: THIS IS A PUBLIC DOMAIN CODE (you may even change the author -- see "Configuration").
//    Term of Usage: BY USING THIS SCRIPT, YOU TAKE FULL RESPONSIBILITY OF ANY LEGAL ACTION THAT MAY BE TAKEN.

// CONFIGURATION
$wp_author = "<a href=mailto:dswsh@plasa.com>dody suria wijaya</a>";
$app['proxy_mode'] = FALSE;        //set this to TRUE to get data from other berita.usable nodes
$app['proxy_url'] = "";         //set this to other berita.usable node
$app['hosted_by'] = get_current_user();//date ("F d Y H:i:s", getlastmod());
$app['zlib_support'] = extension_loaded('zlib');
$app['update_url'] = "http://miaw.tcom.ou.edu/~dody/beritausable-latest.php.txt";
$app['cache'] = TRUE;

//    VARIABLE DEFINITIONS

$hari = array('Minggu','Senin','Selasa','Rabu','Kamis',"Jum'at","Sabtu");
$bulan = array('','Januari','Februari','Maret','April','Mei','Juni','July','Agustus','September','Oktober','November','Desember');
$tgl_lengkap = $hari[date("w")].",&nbsp;".date("j")."&nbsp;".$bulan[date("n")].date(" Y")."&#151;".date("H:i")." WIB";

$self = $_SERVER['PHP_SELF'];
$x = $_REQUEST['x'];
$url = $_REQUEST['url'];
$as_node = $_REQUEST['as_node'];    //client request 1 to ask for serialized news array (default: compressed)
$uncompressed = $_REQUEST['uc'];    //client set this to 1 when requesting uncompressed stream
$query_string = $_SERVER['QUERY_STRING'];
#~ $hostname = "www.kompas.co.id";
$no = $_REQUEST['no'];

// define news module
$newsmodule = array();
$newsmodule[] = array('name'=>'Detikcom','url'=>'http://jkt1.detik.com/index.php'); //detik
$newsmodule[] = array('name'=>'Detikcom (Server US)','url'=>'http://jkt2.detik.com/index.php'); //detik
$newsmodule[] = array('name'=>'Kompas','url'=>'http://www.kompas.co.id/index.htm'); //kompas
$newsmodule[] = array('name'=>'Kompas (Server Luar)','url'=>'http://www.kompas.com/index.htm1'); //kompas
$newsmodule[] = array('name'=>'Media Indonesia','url'=>'http://www.mediaindo.co.id/main.asp');
$newsmodule[] = array('name'=>'Jakarta Post','url'=>'http://www.thejakartapost.com/headlines.asp'); //jakartapost
$newsmodule[] = array('name'=>'Antara','url'=>'http://www.antara.co.id/indonesia.asp'); //antara
$newsmodule[] = array('name'=>'Republika','url'=>'http://www.republika.co.id/ASP/default.asp');
$newsmodule[] = array('name'=>'Koran Tempo','url'=>'http://www.korantempo.com/');
$newsmodule[] = array('name'=>'Suara Pembaharuan','url'=>'http://www.suarapembaruan.com/index.htm');

// default is detikcom
$default_urlbase = $newsmodule[0]['url'];

// contruct news module link at top header
$html_newsmodule = " | <small><b><i>Pick News Channel, or try any site:</i></b> <input type=text name=xu value='http://www.'><input type=hidden name=x value=i><input type=submit value='Go'><br>";
foreach ($newsmodule as $module) {
    $html_newsmodule .= "<a href='$self?x=i&xu={$module['url']}' target=c  class=button>{$module['name']}</a> ";
}
$html_newsmodule .= "</form>";

$header_ouput = "<html><head><title>{$app['name']}: berita cepat ($tgl_lengkap)</title><style>body{font-family:verdana;}.o{font-size:11pt;}.p{font-size:13pt;}h1{font-family:georgia;font-size:18pt;align:center;}.s{color:#991111;font-weight:bold;}</style></head><body bgcolor=#ffffff>";
$list_header_output = "<html><head><title>{$app['name']}: berita cepat ($tgl_lengkap)</title> <style>body{font-family:verdana,arial;margin: 0cm 0cm 0cm 0cm}.i{font-size:10pt;font-weight:bold;font-family:verdana;}.j{font-family:arial;font-size:12pt;font-weight:bold;}.u{font-size:10pt;}.s{color:#991111;font-weight:bold;}a{text-decoration:none;}a:hover{text-decoration:underline;} .button {font-size:10pt;background:#D6E7EF;border-bottom:1px solid #104A7B;border-right:1px solid #104A7B;border-left: 1px solid #AFC4D5;border-top:1px solid #AFC4D5;color:#000066;margin:2;}.d{font-size:smaller;color:#555}#footer {font-size:smaller;clear:both;border:none;background:#e3ebe2;margin-top:20px;padding-left:30px;padding-top:10px;padding-bottom: 10px;}#footer a:link{color:#666666;} #footer a:active,.footer a:hover{color:#006486;}#footer a:visited{color:#949494;}</style></head><body bgcolor=#ffffff>";
$top_output = "<form method=get action='$self?' target=c><b><a href=http://dsw.gesit.com/?id=du title=Home target=_top><span style=color:#991111;>berita</span>.<span style=color:#119911;>usable</span></a>$html_newsmodule";
$list_top_output = "<a href=".$self."?".$query_string." class=button>REFRESH</a> $tgl_lengkap</b>";
$temp_pm = "Using Proxy: "; if ($app['proxy_mode']) $temp_pm .= 'Yeah'; else $temp_pm .= 'Nope';
$temp_zlib = "Compression Support: "; if ($app['zlib_support']) $temp_zlib .= 'Yeah'; else $temp_zlib .= 'Nope';
$list_footer = "<div id=footer><a href=mailto:dswsh@plasa.com target=_top>author</a> | <a href=\$temp_orig target=_top>Original page</a> | Generated by <a href=http://dsw.gesit.com/?id=du target=_top>{$app['name']} v{$app['version']}</a><BR><small>Host: {$app['hosted_by']} | $temp_pm | $temp_zlib | \$temp_stream</div></body></html>";
$error_cant_open = "<p>Unable to connect to news server. This can be caused by this problems: <ul> <li>This webserver's IP has been blocked by News Serber<li>Your webserver is behind firewall <li>Your PHP's setting has disabled socket connection-related functions <li>Detikcom is being swarmed by huge requests and really really busy <li>Detikcom's URL/port has been changed </ul> <p>What ever is the caused, I may not able to help you with this. Thank you. <p><a href=http://www.detik.com>Visit the original detik.com</a> $errstr ($errno)<br>";
$frameset_output = "<html><head><title>{$app['name']}: berita cepat ($tgl_lengkap)</title></head> <frameset rows=\"45,*\"> <frame name=t scrolling=no target=c src=\"$self?x=t\"> <frameset cols=\"50%,*\"> <frame name=c target=m src=\"$self?x=i\"> <frame name=m target=_top src=\"$self?x=w\"> </frameset><noframes> <body>Looks like u need the <a href=$self?no=frame>non-frame version</a>.</body> </noframes> </frameset></html>";
$welcomepage_output = "<center><h1><a href=http://dsw.gesit.com title=\"{$app['name']} home\" target=_top style=text-decoration:none;> <span style=color:#991111;>berita</span>.<span style=color:#119911;>usable</span></a>: berita <i>cepat</i></h1> <p>Version {$app['version']}<p>dipersembahkan oleh $wp_author</p><p>Produk dari <a href=mailto:dswsh@plasa.com>dsw s/h</a></p></center> <hr><p>Situs ini ditujukan untuk mendemonstrasikan 'look and feel' dari potensi sebuah situs berita yg usable: cepat, bersih, dan mudah digunakan.<p>Bagi yang ingin nge-{$app['name']}, bisa copy n paste <a href=\"$self?x=s\" target=_top>source code situs ini</a> (public domain dan cuman 1 file) dan pasang di hosting apapun yg mendukung php, <b>untuk keperluan anda sendiri</b><ul><li><a href=$self?no=frame target=_top>Non-framed version</a> untuk pembenci frame<li><a href=$self?au=1>Check update</a> versi terbaru<li><a href=$self?cm=1>Pengaturan Cache</a></ul> $new_features</body></html>";

global $fp,$log,$news;

#========================FUNCTIONS=========================

    if (phpversion() < '4.3.0' or !function_exists('file_get_contents')) {
        function file_get_contents($path) {
            $output = '';
            $resource = fopen($path,"rb");
            while (1) {
                if (!$resource or feof($resource)) break;
                $output .= fgets ($resource, 1024);
            }
            return $output;
        }
    }

    function nopadding_time_parser ($time) {
        #try it's best to parse time without 0 padding like in detikcom (yikes)
        if (strlen($time) == 6) {   # complete. always correct.
            # hourminutesecond
            return sscanf($time,"%02d%02d%02d");
        }
        elseif (strlen($time) == 5) {
            $correct = array();
            # try 1 char second
            list($hour,$minute,$second) = sscanf($time,"%02d%02d%01d");
            if ($hour < 24 and $minute < 60 and $second < 60) $correct[] = sprintf('%02d%02d%02d',$hour,$minute,$second);
            # try 1 char minute
            list($hour,$minute,$second) = sscanf($time,"%02d%01d%02d");
            if ($hour < 24 and $minute < 60 and $second < 60) $correct[] = sprintf('%02d%02d%02d',$hour,$minute,$second);
            # try 1 char hour
            list($hour,$minute,$second) = sscanf($time,"%01d%02d%02d");
            if ($hour < 24 and $minute < 60 and $second < 60) $correct[] = sprintf('%02d%02d%02d',$hour,$minute,$second);

            if (count($correct) == 0) return array(0,0,0); # fail
            if (count($correct) > 1) sort($correct,SORT_STRING);
            return sscanf(array_pop($correct),"%02d%02d%02d");
        }
        elseif (strlen($time) == 4) {
            $correct = array();
            # try 1 char hour/minute
            list($hour,$minute,$second) = sscanf($time,"%01d%01d%02d");
            if ($hour < 24 and $minute < 60 and $second < 60) $correct[] = sprintf('%02d%02d%02d',$hour,$minute,$second);
            # try 1 char hour/second
            list($hour,$minute,$second) = sscanf($time,"%01d%02d%01d");
            if ($hour < 24 and $minute < 60 and $second < 60) $correct[] = sprintf('%02d%02d%02d',$hour,$minute,$second);
            # try 1 char minute/second
            list($hour,$minute,$second) = sscanf($time,"%02d%01d%01d");
            if ($hour < 24 and $minute < 60 and $second < 60) $correct[] = sprintf('%02d%02d%02d',$hour,$minute,$second);

            if (count($correct) == 0) return array(0,0,0); # fail
            if (count($correct) > 1) sort($correct,SORT_STRING);
            return sscanf(array_pop($correct),"%02d%02d%02d");
        }
        elseif (strlen($time) == 3) { # always correct
            return sscanf($time,"%d%d%d");
        }
        else {
            return array(0,0,0);
        }
    }

    function str_time_delta ($date_c) {
        $delta = time() - $date_c;
        if ($delta < 60) {
            $satuan = "detik";
            $tgl = $delta;
        }
        elseif ($delta < 3600) {
            $satuan = "menit";
            $tgl = round($delta/60);
        }
        elseif ($delta < 86400) {
            $satuan = "jam";
            $tgl = sprintf("%01.1f",$delta/3600);
        }
        else {
            $satuan = "hari";
            $tgl = sprintf("%01.1f",$delta/86400);
        }
        return "$tgl $satuan";
    }

    function your_file_is_mine($filename) {
       $fd = fopen("$filename", "r");
       #~ $content = fread($fd, filesize($filename));
       $content = fread($fd, 99999999);
       fclose($fd);
       return $content;
    }


    //    START
    ob_end_flush();

    if (isset($url)) {
        if ($app['proxy_mode']) {
            $detikusable_mode = 'news_detail_from_node';
        }
        else {
            $detikusable_mode = 'news_detail';
        }
    }
    elseif ($x=="i" or $no=="frame") {
        if ($app['proxy_mode']) {
            $detikusable_mode = 'news_list_from_node';    //retrieve serialized+processed html containing ready-to-view array from other berita.usable node.
        }
        else {
            $detikusable_mode = 'news_list';    //retrieve raw html from detik, parse, and output as new berita.usable-style design
        }
    }
    elseif ($x=="w") {
        $detikusable_mode = 'welcome_page';
    }
    elseif ($x=="t") {
        $detikusable_mode = 'top_frame';
    }
    elseif ($x=="s") {
        $detikusable_mode = 'source_code';
    }
    elseif ($_REQUEST['au']) {
        $detikusable_mode = 'auto_update';
    }
    elseif ($_REQUEST['cm']) {
        $detikusable_mode = 'cache_management';
    }
    else {
        $detikusable_mode = 'frame_set';
    }

#=========================NEWS DETAIL===========================
if ($detikusable_mode == 'news_detail') {
    echo $list_header_output;
    $urlbase = $_REQUEST['url'];

    if ($app['cache']) { //check if already in cache
        $filename = 'cache/'.md5($urlbase);
        if (file_exists($filename)) {
            $buffer = "";
            $fp = fopen($filename,'rb');
            while(!feof($fp)) {
               $buffer .= fread($fp,1024);
            }
            fclose($fp);
            $news = unserialize($buffer);
            extract($news);
            $news_from_cache = 1;
        }
    }

    if (!$news_from_cache) {
        //download and parse news
        $buffer = file_get_contents($urlbase);
        $turl = parse_url($urlbase);
        $hostname = $turl['host'];
        $location = $turl['path'];
        $fixpathnonslash = dirname($urlbase);

        while (1) {
            // kompas 1
            $regex = '<span class="sectionHL">(.*?)<.*?<p><b>(.*?)<.*?<!---Start--->(.*?)<!--';
            if (preg_match("|$regex|is",$buffer,$group)) {
                list($dummy,$title,$reporter,$body) = $group; break;
            }
            // kompas 2
            $regex = 'size="4">(.*?)</font.*?<font color="#000000" face="Arial" size="2">(.*?)</font.*?<!---Start--->(.*?)&nbsp;&nbsp;&nbsp;&nbsp;';
            if (preg_match("|$regex|is",$buffer,$group)) {
                list($dummy,$title,$reporter,$body) = $group; break;
            }
            // jakartapost 1
            $regex = '<!-- Put the news record in here -->(.*?)</font>.*?<font face="Arial, Helvetica" size="2">(.*?)<hr noshade';
            if (preg_match("|$regex|is",$buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            // jakartapost 2
            $regex = '<p><font face="Helvetica, Arial" size=3>(.*?)</font>.*?<font face="Arial, Helvetica" size="2">(.*?)</font>';
            if (preg_match("|$regex|is",$buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            // antara 1
            $regex = '<span class="clsfont3">(.*?)</span>.*?<span class="clsfont1">(.*?)<p align=right>';
            if (preg_match("|$regex|is",$buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            // detik-foto
            $regex = '<BR><FONT size=5>(.*?)</font>.*?<FONT color=#ff0000 size=2>(.*?)</font>.*?P align="Justify">(.*?)<!-- FORM BERITA ';
            if (preg_match("|$regex|is",$buffer,$group)) {
                list($dummy,$title,$reporter,$body) = $group; break;
            }
            // detik-inet
            $regex = '<font class="subjudulberita">(.*?)</font>.*?<font class="judulberita">(.*?)</font>.*?<font class="textreporter">(.*?)</font>.*?<font class="textberita">(.*?)</blockquote>';
            if (preg_match("|$regex|is",$buffer,$group)) {
                list($dummy,$subtitle,$title,$reporter,$body) = $group; break;
            }
            // detik-news
            $regex = '<font class="judulberita">(.*?)</font>.*?<font class="textreporter">(.*?)</font>.*?<font class="textberita">(.*?)\n</font>';
            if (preg_match("|$regex|is",$buffer,$group)) {
                list($dummy,$title,$reporter,$body) = $group; break;
            }
            // republika
            $regex ='<font class="headline">(.*?)</font>.*?<font class="copy">(.*?)</font>';
            if (preg_match("|$regex|is",$buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            // tempo interaktif
            $regex = '<meta name="title" content="([^"]*?)".*?<font color=#666666>(.*?)</font></p> ';
            if (preg_match("|$regex|is",$buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            // suara pembaruan
            $regex = '<H1>(.*?)</H1>.*?<P>(.*)<HR>';
            if (preg_match("|$regex|is",$buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            // media indoensia
            $regex = '<div class=JudulBerita>(.*?)</div>.*?<p class=BeritaBaca>(.*?)<div>';
            if (preg_match("|$regex|is",$buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            echo "<h1>Sorry, unable to parse news content</h1>";
            echo "<p>Redirecting to original news, or click <a href='$urlbase'>Original News</a>.</p>";
            echo "<script>window.location='$urlbase'</script>";
            $error = 1;
            break;
        }

        //save serialized array to file
        $news = array('title'=>$title,'subtitle'=>$subtitle,'reporter'=>$reporter,'body'=>$body);
        $result = 1;
        if (!file_exists('cache')) {
            $result = @mkdir('cache',0755);
        }
        // sometime, the permission is not given to create folder
        if ($result) {
            $urls = parse_url($url);
            $filename = 'cache/'.md5($urlbase);
            $buffer = serialize($news);
            $fp = fopen($filename,'wb');
            fwrite($fp,$buffer);
            fclose($fp);
            #~ print "<br>SAVE CACHE!";
        }
    }

    if (!$error) {
        echo "<h3>$title</h3>";
        echo "<p class=u>$reporter</p>";
        // absolutized all external url in body
        # img src="/koleksifoto/0404/4042101.jpg"
        # a href=".."
        $body = preg_replace('|img src="/|i',"img src=\"http://$hostname/",$body);
        $body = preg_replace('|img src="([a-gi-z0-9][a-su-z0-9])|i',"img src=\"$fixpathnonslash/\\1",$body);
        $body = preg_replace('|a href="/|i',"a href=\"http://$hostname/",$body);
        $body = preg_replace('|a href="([a-gi-z0-9][a-su-z0-9])|is',"a href=\"$fixpathnonslash/\\1",$body);
        // kompas.co.id
        $body = preg_replace('|a href="(http://www.kompas.co.id)|i',"a href=\"$self?url=\\1",$body);
        $body = preg_replace("|javascript:WindowOpen\('/|i","javascript:WindowOpen('http://$hostname/",$body);
        // detik
        $body = preg_replace('|a href="(http://www.detik)|i',"a href=\"$self?url=\\1",$body);
        echo "<span class=u>$body</span>";
        if ($news_from_cache)
            echo "<p align=right><span style=color:aaaaaa><small><i>[cached page]</i></small></span>";
    }


    $temp_orig = $urlbase;
    $temp_stream = "Stream: "; if (!$app['proxy_mode']) $temp_stream .= "N/A"; elseif ($stream_compress) $temp_stream .= "Compressed"; else $temp_stream .= "Uncompressed";
    eval("\$list_footer = \"$list_footer\";");
    echo $list_footer;
}

#=============== NEWS LIST PAGE ===========================
if ($detikusable_mode == 'news_list')
{
    $urlbase = $_REQUEST['xu'];
    if (!$urlbase)
        $urlbase = $default_urlbase;

    $turl = parse_url($urlbase);
    $hostname = $turl['host'];
    $location = $turl['path'];
    $fixpathnonslash = dirname($urlbase);

    // Download file first
    $buffer = file_get_contents($urlbase);

    // We Got New Intelligent Algorithm to parse New list more reliable!
    // 1. Get dictionary of <a>...</a> links
    $links = array();
    #~ $regex = '<a href="(.*?)".*>(.*?)</a';
    $regex = "<a href=[\"']?([^\" >]*).*?>(.*?)</a>";
    if (preg_match_all("|$regex|is",$buffer,$groups,PREG_SET_ORDER)) {
        foreach ($groups as $group) {
            # we deal only with absolute href
            $href = $group[1];
            if (!stristr($href,'http://')) {
                # if href does not begin with '/', complete with fixpathnonslash
                if (substr($href,0,1) != '/')
                    $href = $fixpathnonslash.'/'.$href;
                else    //href begin with '/', absolutize with hostname
                    $href = 'http://'.$hostname.$href;
            }
            $links[] = array('href'=> $href, 'text' => $group[2], 'cleantext' => strip_tags($group[2]) );
        }
    }

    // 1.b Get Frameset Links (if any)
    $regex = "<frame .*?src=[\"']?([^\" >]*).*?>";
    if (preg_match_all("|$regex|is",$buffer,$groups,PREG_SET_ORDER)) {
        foreach ($groups as $group) {
            # we deal only with absolute href
            $href = $group[1];
            if (!stristr($href,'http://')) {
                # if href does not begin with '/', complete with fixpathnonslash
                if (substr($href,0,1) != '/')
                    $href = $fixpathnonslash.'/'.$href;
                else    //href begin with '/', absolutize with hostname
                    $href = 'http://'.$hostname.$href;
            }
            $links[] = array('href'=> $href, 'text' => 'frameset', 'cleantext' => "<b>Frame [{$group[1]}]</b>" );
        }
    }

    // 2. categorize url based on certain url element
    $channels = array();    #dict of channel (as key)

    // - frameset
    $channel = 'Frameset';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if ($link['text'] == 'frameset') {
            $link['channel'] = $channel;
            $link['list'] = 1;
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }

    // - kompas
    # http://www.kompas.co.id/utama/news/0404/21/174215.htm
    #                         chanel    yearmonth/day/hourminutesecond
    $regex = 'www.kompas.co.id/([^/]*)/news/(\d\d)(\d\d)/(.*?)/(\d\d)(\d\d)(\d\d)';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
            list($dummy,$channel,$year,$month,$day,$hour,$minute,$second) = $group;
            $link['channel'] = $channel;
            $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }
    //- kompas cetak
    # http://www.kompas.co.id/kompas-cetak/0404/19/utama/976212.htm
    $regex = 'www.kompas.co.id/kompas-cetak/(\d\d)(\d\d)/(.*?)/';
    $channel = 'Kompas Cetak';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
            list($dummy,$year,$month,$day) = $group;
            $link['channel'] = $channel;
            $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }

    // - detik news
    # http://www.detiknews.com/index.php/detik.read/tahun/2004/bulan/04/tgl/22/time/15616/idnews/128646/idkanal/10
    # http://www.detikhot.com/index.php/detik.read/tahun/2004/bulan/04/tgl/22/time/13450/idnews/128624/idkanal/118
    #             chanel                                year        month day     can't easily get hour/minute/sec
    $regex = '//www\.(.*?)\.com/.*?tahun/(.+?)/.*?/(.+?)/.*?/(.+?)/.*?/(.+?)/';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            list($dummy,$channel,$year,$month,$day,$time) = $group;
            list($hour,$minute,$second) = nopadding_time_parser($time);
            $link['channel'] = $channel;
            $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
            # tries to find this link's summary
            $regex_summary = $link['text'].'.*?<span class="summary">(.*?)</span>';
            if (preg_match("|$regex_summary|is",$buffer,$minigroup)) {
                #~ print "OK!";
                $link['summary'] = $minigroup[1];
            }
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }


    #~ // - detik ads
    #~ # http://ad.detik.com/link/wp/wp-mobile.ad
    #~ # http://ad.detik.com/link/wp/wp-aceh4.ad
    #~ $regex = '//ad\.detik\.com/';
    #~ $channel = 'detik iklan';
    #~ for ($i = 0; $i < count($links) ; $i++) {
        #~ $link = $links[$i];
        #~ if (preg_match("|$regex|is",$link['href'],$group)) {
            #~ $link['channel'] = $channel;
            #~ $temp = basename($link['href']);
            #~ if (trim($link['cleantext'])=='')
                #~ $link['cleantext'] = $temp;
            $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
            #~ $channels[$channel] += 1;
        #~ }
        #~ $links[$i] = $link;
    #~ }

    // detik foto
    # http://www.detik.com/berita-foto/2004/04/22/20040422-194357.shtml
    $regex = 'www.detik.com/berita-foto/.*?/(\d\d\d\d)(\d\d)(\d\d)-(\d\d)(\d\d)(\d\d).shtml';
    $channel = 'Berita Foto';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            list($dummy,$year,$month,$day,$hour,$minute,$second) = $group;
            $link['channel'] = $channel;
            $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
            # tries to find this link's summary
            $regex_summary = $link['text'].'.*?<span class="summary">(.*?)</span>';
            if (preg_match("|$regex_summary|is",$buffer,$minigroup)) {
                $link['summary'] = $minigroup[1];
            }
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }
    // - jakarta post news
    # http://www.thejakartapost.com/detaillatestnews.asp?fileid=20040422112727&irec=8
    # http://www.thejakartapost.com/detailheadlines.asp?fileid=20040422.A02&irec=5
    $regex = 'www\.thejakartapost\.com/detaillatestnews\.asp\?fileid=(\d\d\d\d)(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)';
    $channel = 'Latest News';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            list($dummy,$year,$month,$day,$hour,$minute,$second) = $group;
            $link['channel'] = $channel;
            $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }

    # http://www.thejakartapost.com/detailheadlines.asp?fileid=20040422.A02&irec=5
    $regex = 'www\.thejakartapost\.com/detailheadlines\.asp\?fileid=(\d\d\d\d)(\d\d)(\d\d)';
    $channel = 'Headlines';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            list($dummy,$year,$month,$day) = $group;
            list($hour,$minute,$second) = array(0,0,0);
            $link['channel'] = $channel;
            $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }

    // antara
    # http://www.antara.co.id/berita.asp?id=149317&th=2004
    $regex = 'www\.antara\.co\.id/berita\.asp';
    $channel = 'Berita';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            #~ list($dummy,$year,$month,$day) = $group;
            #~ list($hour,$minute,$second) = array(0,0,0);
            $link['channel'] = $channel;
            #~ $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
            # text in antara are capitalized, fix em here
            $link['cleantext'] = ucwords(strtolower($link['cleantext']));
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }

    // republika - online
    # http://www.republika.co.id/ASP/online_detail.asp?id=159015&kat_id=23
    $channel = 'Online';
    $regex = 'www.republika.co.id/ASP/online_detail.asp';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            $link['channel'] = $channel;
            #~ $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
            # text in antara are capitalized, fix em here
            #~ $link['cleantext'] = ucwords(strtolower($link['cleantext']));
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }
    // republika - koran cetak
    # http://www.republika.co.id/ASP/koran_detail.asp?id=159022&kat_id=3
    $channel = 'Koran Cetak';
    $regex = 'www.republika.co.id/ASP/koran_detail.asp';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            $link['channel'] = $channel;
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }

    // republika - cetak detail
    # http://www.republika.co.id/cetak_detail.asp?mid=7&id=159046&kat_id=89
    $channel = 'Koran Cetak';
    $regex = 'www.republika.co.id/ASP/cetak_detail.asp';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            $link['channel'] = $channel;
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }

    // republika supplement cetak detail
    # http://www.republika.co.id/suplemen/cetak_detail.asp?mid=2&id=159086&kat_id=330&kat_id1=334
    $channel = 'Supplemen Cetak';
    $regex = 'www.republika.co.id/suplemen/cetak_detail.asp';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            $link['channel'] = $channel;
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }

    // republika - supplemen -> list
    # http://www.republika.co.id/suplemen/indeks_suplemen.asp?mid=3&kat_id=149&kat_id1=249
    $channel = 'Supplemen';
    $regex = 'www.republika.co.id/suplemen/indeks_suplemen.asp';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            $link['channel'] = $channel;
            $channels[$channel] += 1;
            $link['list'] = 1;
        }
        $links[$i] = $link;
    }
    // republika - supplemen2 -> list
    $channel = 'Supplemen';
    $regex = 'www.republika.co.id/indeks_suplemen.asp';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            $link['channel'] = $channel;
            $channels[$channel] += 1;
            $link['list'] = 1;
        }
        $links[$i] = $link;
    }

    // koran tempo
    # http://www.korantempo.com/news/2004/4/23/headline/top_head.html
    $regex = 'www.korantempo.com/news/(\d\d\d\d)/(\d*)/(\d*)';
    $channel = 'Headline';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
            list($dummy,$year,$month,$day) = $group;
            $link['channel'] = $channel;
            $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }

    # http://www.tempointeraktif.com/hg/nasional/2004/04/22/brk,20040422-34,id.html
    $regex = 'www.tempointeraktif.com/hg/(.*?)/(\d\d\d\d)/(\d*)/(\d*)';
    #~ $channel = 'Tempo Interaktif';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
            list($dummy,$channel,$year,$month,$day) = $group;
            $link['channel'] = $channel;
            $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }

    // suara pembaruan
    # http://www.suarapembaruan.com/News/2004/04/22/Utama/ut01.htm
    $regex = 'www.suarapembaruan.com/News/(\d\d\d\d)/(\d*)/(\d*)';
    $channel = 'Utama';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
            list($dummy,$year,$month,$day) = $group;
            $link['channel'] = $channel;
            $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }

    // media indoensia
    # http://www.mediaindo.co.id/beritakhusus.asp?id=2341 & berita biasa
    # http://www.mediaindo.co.id/berita.asp?id=40466
    $regex = 'www.mediaindo.co.id/beritakhusus.asp';
    $channel = 'Berita Khusus';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            $link['channel'] = $channel;
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }
    // media indoensia
    # http://www.mediaindo.co.id/beritakhusus.asp?id=2341 & berita biasa
    # http://www.mediaindo.co.id/berita.asp?id=40466
    $regex = 'www.mediaindo.co.id/berita.asp';
    $channel = 'Berita';
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        if (preg_match("|$regex|is",$link['href'],$group)) {
            $link['channel'] = $channel;
            $channels[$channel] += 1;
        }
        $links[$i] = $link;
    }

    // 3. print url based on sorted channel
    echo $list_header_output;
    echo $list_top_output;

    foreach ($channels as $channel=>$kount) {
        echo "<p><span class=i>".ucwords($channel)."</span>";
        for ($i = 0; $i < count($links) ; $i++) {
        #~ foreach ($links as $link) {
            $link = $links[$i];
            # do not print duplicates
            if ($link['printed']) continue;
            if ($link['channel'] == $channel) {
                $delta = str_time_delta($link['unixtime']);
                $left_date = date("H:i",$link['unixtime']);
                $target = 'target=m';
                if ($no) $target = '';
                if ($link['list'])  // link meant to be a list, not a news detail
                    echo "<br><span class=i><a href='$self?x=i&xu={$link['href']}'>{$link['cleantext']}</a></span>";
                elseif ($link['unixtime'])
                    echo "<br><span class=d>($left_date)</span> <span class=i><a href='$self?url={$link['href']}' $target>{$link['cleantext']}</a></span> <span class=d>[$delta]</span>";
                else
                    echo "<br><span class=i><a href='$self?url={$link['href']}' $target>{$link['cleantext']}</a></span> <span class=d></span>";
                if ($link['summary'])
                    echo "<br><span class=u>{$link['summary']}</span><br>";
                # mark link as havebeen written
                $links[$i]['printed'] = 1;
            }
        }
    }

    # other links which have not been printed
    #~ echo "<p><span class=i>Other Links</span>";
    echo "<hr>";
    for ($i = 0; $i < count($links) ; $i++) {
        $link = $links[$i];
        # do not print printed links
        if ($link['printed']) continue;
        # do not print empty text links
        if (trim($link['cleantext']) == '') continue;
        echo "<span class=i><a href='$self?x=i&xu={$link['href']}'>{$link['cleantext']}</a></span> <small><i><span style=color:aaaaaa>[<a href='{$link['href']}' target=_top>orig</a>] [<a href='$self?url={$link['href']}' target=m>view</a>]</span></i></small><br>";
    }
    $temp_orig = $urlbase;
    $temp_stream = "Stream: "; if (!$app['proxy_mode']) $temp_stream .= "N/A"; elseif ($stream_compress) $temp_stream .= "Compressed"; else $temp_stream .= "Uncompressed";
    eval("\$list_footer = \"$list_footer\";");
    echo $list_footer;
}

#=============== NEWS LIST SUPPLIER NODE PAGE ===========================
if ($detikusable_mode == 'news_list_from_node')
{
    $app['proxy_url'] .= "?x=i&as_node=1";
    if (!$app['zlib_support'])    $app['proxy_url'] .= "&uc=1";    //ask uncompressed stream if i don't support zlib library
    $fp = fopen($app['proxy_url'],'r');
    $buffer = fread($fp,100000);
    fclose($fp);
    if ($buffer == "")
    {
        write_error('newslist: from node: Unable to download from node');
    }
    else
    {
        $buffer_orig = $buffer;
        if ($app['zlib_support'])
        {
            $buffer = @gzuncompress($buffer);
            if (!buffer)
            {
                //write_error('newslist: from node: Unable to uncompress data');
                //let's assume it's not gzcompressed

                $buffer = $bufer_orig;
            }
            else
            {
                $stream_compress = TRUE;
            }
        }

        $buffer = unserialize($buffer);
        if (!buffer)
        {
            write_error('newslist: from node: Unable to unserialize data');
            exit;
        }

        if (!is_array($buffer))
        {
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
    if (!$app['zlib_support'])    $app['proxy_url'] .= "&uc=1";    //ask uncompressed stream if i don't support zlib library
    $fp = fopen($app['proxy_url'],'r');
    $buffer = fread($fp,100000);
    fclose($fp);
    if ($buffer == "")
    {
        write_error('newsdetail: from node: Unable to download from node');
    }
    else
    {
        $buffer_orig = $buffer;

        if ($app['zlib_support'])
        {
            $buffer = @gzuncompress($buffer);
            if (!buffer)
            {
                //write_error('newslist: from node: Unable to uncompress data');
                //let's assume it's not gzcompressed

                $buffer = $bufer_orig;
            }
            else
            {
                $stream_compress = TRUE;
            }
        }

        $buffer = unserialize($buffer);
        if (!buffer)
        {
            write_error('newsdetail: from node: Unable to unserialize data');
            exit;
        }

        if (!is_array($buffer))
        {
            write_error('newsdetail: from node: Data is not formatted correctly');
            exit;
        }
        news_detail_view($buffer);
    }
}

#=============== WELCOME PAGE ===========================
if ($detikusable_mode == 'welcome_page')
{
    echo $header_ouput;
    echo $welcomepage_output;
}

#=============== SOURCE CODE VIEWER PAGE ===========================
if ($detikusable_mode == 'source_code')
{
    $loc = $_SERVER['DOCUMENT_ROOT']."/".basename($self);
    show_source($loc);
}

#=============== TOP FRAME ===========================
if ($detikusable_mode == 'top_frame')
{
    echo $list_header_output;
    echo $top_output;
}

#=============== FRAME SET PAGE ===========================
if ($detikusable_mode == 'frame_set')
{
    echo $frameset_output;
}

#=============== AUTO-UPDATE PAGE ===========================
if ($detikusable_mode == 'auto_update')
{
    if (!$_REQUEST['commit'])
    {
        //compare version
        $fp = fopen($app['update_url'],'r');
        $buffers = '';

        while ($buffer = fgets($fp,1000000)) {
            $buffers .= $buffer;
        }
        if ($buffers == '') {
            echo "<h1>update url:".$app['update_url']." was not found!</h1>";
            exit;
        }
        fclose($fp);

        if (preg_match('/\$app\[\'version\'\]\s*=\s*"([^"]*)"/i',$buffers,$remote_res)) {
            $remote_version = $remote_res[1];
        }
        else {
            $remote_version = "0";
        }

        echo $list_header_output;
        echo "<h4>Check newest version</h4>";
        echo "<ul><li>This {$app['name']} version: <b>{$app['version']}</b><li>Newest version: <b>$remote_version</b></ul>";
        if ($remote_version > $app['version']) echo "<p><form method=get action=$self><input type=hidden name=au value=1><input type=hidden name=commit value=1><input type=submit value=\"Update to $remote_version\"></form>";
        else echo "<p>No newer version available<p><form method=get action=$self><input type=hidden name=au value=1><input type=hidden name=commit value=1><input type=submit value=\"Force update again\"></form>";
        echo "<p><a href=$self?x=w>Back to welcome page</a>";
    }
    else {
        unset($buffer);
        $fp = fopen($app['update_url'],'r');
        $buffers = '';
        while($buffer = fread($fp,1024))
        {
            $buffers .= $buffer;
        }
        fclose($fp);

        if ($buffers == "") {
            write_error("auto_update: Unable to get latest version at {$app['update_url']}");
            echo "<p><a href=$self?x=w>Back to welcome page</a>";
        }
        else {
            $target = $_SERVER['SCRIPT_FILENAME'];
            $fp = fopen($target,'w');
            fwrite($fp,$buffers);
            fclose($fp);
            echo $list_header_output;
            echo "<h4>Update Berhasil</h4>";
            echo "<p><a href=$self target=_top>Reload {$app['name']}</a>";
        }
    }
}

#=============== CACHE MANAGEMENT PAGE ===========================
if ($detikusable_mode == 'cache_management')
{
    if (!$_REQUEST['commit']) {
        $dirsize = 0;
        $dh = opendir('cache');
        while ($filename = readdir($dh)) if (($file_name != "." && $file_name != "..")) $dirsize += filesize('cache/'.$filename);
        $cache_size = round($dirsize/1024,2);
        echo $list_header_output;
        echo "<h4>Cache Management</h4>";
        echo "Total space used by cache: ".$cache_size." KB";
        if ($cache_size > 0) echo "<p><form method=get action=$self><input type=hidden name=cm value=1><input type=hidden name=commit value=1><input type=submit value=\"Empty Cache\"></form>";
        echo "<p><a href=$self?x=w>Back to welcome page</a>";
    }
    else {
        $dh = opendir('cache');
        while ($filename = readdir($dh)) if (($file_name != "." && $file_name != "..")) @unlink('cache/'.$filename);
        echo $list_header_output;
        echo "<h4>Cache emptied</h4>";
        echo "<p><a href=$self?x=w>Back to welcome page</a>";

    }
}