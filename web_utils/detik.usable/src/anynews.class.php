<?

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
        if (count($correct) > 1) {
            sort($correct,SORT_STRING);
            $test1 = array_filter($correct, 'smaller_than_curr_time');
            if (count($test1)) $correct = $test1;
        }
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
        if (count($correct) > 1) {
            sort($correct,SORT_STRING);
            $test1 = array_filter($correct, 'smaller_than_curr_time');
            if (count($test1)) $correct = $test1;
        }
        return sscanf(array_pop($correct),"%02d%02d%02d");
    }
    elseif (strlen($time) == 3) { # always correct
        return sscanf($time,"%d%d%d");
    }
    else {
        return array(0,0,0);
    }
}

class AnyNews extends DetikUsable {
    var $title_name = '';
    var $links_qualified = False;

    # must call base class
    function AnyNews ($url='') { parent::DetikUsable($url); }

    function GetTitleName() { return $this->title_name; }
    function SetTitleName($t) { $this->title_name = $t; }

    function ParseList() {
        /* We Got New Intelligent Algorithm to parse New list more reliable!
        */
        $logic_url = ($this->url_real != '')?$this->url_real: $this->url;
        $up = parse_url($logic_url);
        $hostname = $up['host'];
        if ($up['port'] != '') $hostname .= ':'.$up['port'];
        $scheme = $up['scheme'];
        $fixpathnonslash = $scheme.'://'.$hostname.substr($up['path'],0,strrpos($up['path'],'/')).'/';
        # find base if any
        $regex = '<base[^>]*?href=(?:"|\')([^\1]*?)\1[^>]*?>';
        if (preg_match('#'.$regex.'#is', $this->buffer, $group)) {
            $this->url_base = $group[1];
            if (substr($this->url_base,-1,1) != '/') $this->url_base .= '/';
        }

        // 1. Get dictionary of <a>...</a> links
        $links = array();
        $temp_deduper = array();    # to dedupe
        #~ $regex = '<a href="(.*?)".*>(.*?)</a';
        $regex = "<a.*?href=[\"']?([^\" >]*).*?>(.*?)</a>";
        if (preg_match_all('|'.$regex.'|is',$this->buffer,$groups,PREG_SET_ORDER)) {
            foreach ($groups as $group) {
                # we deal only with absolute href
                $href = $group[1];
                if (substr($href,0,7) != 'http://') {
                    # if href does not begin with '/', complete with fixpathnonslash
                    if (substr($href,0,2) == './') $href = substr($href,2); # ./blabla -> blabla
                    if (substr($href,0,2) == '//')  # see slashdot.org. // => currentscheme://
                        $href = $scheme.':'.$href;
                    elseif (substr($href,0,1) != '/') {
                        if ($this->url_base != '')
                            $href = $this->url_base.$href;
                        else
                            $href = $fixpathnonslash.$href;
                    }
                    else    //href begin with '/', absolutize with hostname
                        $href = $scheme.'://'.$hostname.$href;
                }
                $href = str_replace('&amp;','&',$href); # &amp; is consider & by browser. let's do the same.
                if ($temp_deduper[$href] != '') { //remove duplicate links
                    if (strlen($links[$temp_deduper[$href]]['text']) < strlen($group[2]))
                        $links[$temp_deduper[$href]]['text'] = $group[2];
                    if (strlen($links[$temp_deduper[$href]]['cleantext']) < strlen(trim(strip_tags($group[2]))))
                        $links[$temp_deduper[$href]]['cleantext'] = trim(strip_tags($group[2]));
                }
                else {
                    $links[] = array('href'=> $href, 'text' => $group[2], 'cleantext' => trim(strip_tags($group[2])) );
                    $temp_deduper[$href] = count($links)-1;
                }
            }
        }
        unset($temp_deduper);

        // 1.b Get Frameset Links (if any)
        $regex = "<frame .*?src=[\"']?([^\" >]*).*?>";
        if (preg_match_all('|'.$regex.'|is',$this->buffer,$groups,PREG_SET_ORDER)) {
            foreach ($groups as $group) {
                # we deal only with absolute href
                $href = $group[1];
                if (!stristr($href,'http://')) {
                    # if href does not begin with '/', complete with fixpathnonslash
                    if (substr($href,0,1) != '/')
                        $href = $fixpathnonslash.$href;
                    else    //href begin with '/', absolutize with hostname
                        $href = 'http://'.$hostname.$href;
                }
                $links[] = array('href'=> $href, 'text' => 'frameset', 'cleantext' => "<strong>Frame [{$group[1]}]</strong>" );
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

        // HUMAN_LIST_PARSER

        // - kompas
        # http://www.kompas.co.id/utama/news/0404/21/174215.htm
        #                         chanel    yearmonth/day/hourminutesecond
        $regex = 'www.kompas.co[^\/]+/ver1/([^/]*)/news/(\d\d)(\d\d)/(.*?)/(\d\d)(\d\d)(\d\d)';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
                list($dummy,$channel,$year,$month,$day,$hour,$minute,$second) = $group;
                $link['channel'] = $channel;
                $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
                $channels[$channel] += 1;
                $link['href'] = str_replace('_.htm', '.htm', $link['href']); # in kompas _.htm open frameset instead
            }
            $links[$i] = $link;
        }

        $regex = 'www.kompas.co[^\/]+/([^/]*)/news/(\d\d)(\d\d)/(.*?)/(\d\d)(\d\d)(\d\d)';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
                list($dummy,$channel,$year,$month,$day,$hour,$minute,$second) = $group;
                $link['channel'] = $channel;
                $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
                $channels[$channel] += 1;
                $link['href'] = str_replace('_.htm', '.htm', $link['href']); # in kompas _.htm open frameset instead
            }
            $links[$i] = $link;
        }


        //- kompas cetak
        # http://www.kompas.co.id/kompas-cetak/0404/19/utama/976212.htm
        $regex = 'www.kompas.co[^\/]+/kompas-cetak/(\d\d)(\d\d)/(.*?)/';
        $channel = 'Kompas Cetak';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
                list($dummy,$year,$month,$day) = $group;
                $link['channel'] = $channel;
                $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        // - detik news
        # http://jkt1.detiknews.com/indexfr.php?url=http://jkt1.detiknews.com/index.php/detik.read/tahun/2005/bulan/04/tgl/25/time/9758/idnews/348085/idkanal/10
        #             chanel                                year        month day     can't easily get hour/minute/sec
        #~ $regex = '//www\.(.*?)\.com/.*?tahun/(.+?)/.*?/(.+?)/.*?/(.+?)/.*?/(.+?)/';
        $regex = '//[^\.]+\.(.*?)\.com/.*?tahun/(.+?)/.*?/(.+?)/.*?/(.+?)/.*?/(.+?)/';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($dummy,$channel,$year,$month,$day,$time) = $group;
                list($hour,$minute,$second) = nopadding_time_parser($time);
                $link['channel'] = $channel;
                $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
                # tries to find this link's summary
                $regex_summary = $link['text'].'(.*?)<span class="summary">(.*?)</span>';
                if (preg_match("|$regex_summary|is",$this->buffer,$minigroup)) {
                    if (strlen($minigroup[1]) < 300) {    # summary should be "close" to link
                        $link['summary'] = $minigroup[2];
                        $channel = 'Headline';
                        $link['channel'] = $channel;
                    }
                }
                # fix href, to include only after url=
                if (preg_match('|url=(.*)|',$link['href'],$group)) $link['href'] = $group[1];
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        // detik foto
        # http://www.detik.com/berita-foto/2004/04/22/20040422-194357.shtml
        $regex = 'www.detik.com/berita-foto/.*?/(\d\d\d\d)(\d\d)(\d\d)-(\d\d)(\d\d)(\d\d).shtml';
        $channel = 'Berita Foto';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($dummy,$year,$month,$day,$hour,$minute,$second) = $group;
                $link['channel'] = $channel;
                $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
                # tries to find this link's summary
                $regex_summary = $link['text'].'.*?<span class="summary">(.*?)</span>';
                if (preg_match("|$regex_summary|is",$this->buffer,$minigroup)) {
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
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
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
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
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
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                $link['channel'] = $channel;
                $link['cleantext'] = ucwords(strtolower($link['cleantext']));
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        // republika - online
        # http://www.republika.co.id/online_detail.asp?id=195393&kat_id=23
        $channel = 'Online';
        $regex = 'online_detail.asp';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }
        // republika - koran cetak
        # http://www.republika.co.id/koran_detail.asp?id=195505&kat_id=3
        $channel = 'Koran Cetak';
        $regex = 'koran_detail.asp';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        // republika - cetak detail
        # http://www.republika.co.id/cetak_detail.asp?mid=7&id=159046&kat_id=89
        $channel = 'Koran Cetak';
        $regex = 'www.republika.co.id/cetak_detail.asp';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
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
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
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
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
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
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
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
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
                list($dummy,$year,$month,$day) = $group;
                $link['channel'] = $channel;
                $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        # http://www.tempointeraktif.com/hg/nasional/2004/04/22/brk,20040422-34,id.html
        # http://www.tempointeractive.com/hg/nasional/2005/04/21/brk,20050421-02,uk.html
        $regex = 'www.tempo[^.]+.com/hg/(.*?)/(\d\d\d\d)/(\d*)/(\d*)';
        #~ $channel = 'Tempo Interaktif';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
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
        #http://www.suarapembaruan.com/News/2005/04/19/Editor/edit02.htm
        $regex = 'www.suarapembaruan.com/News/(\d\d\d\d)/(\d*)/(\d*)/([^/]*)/';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
                list($dummy,$year,$month,$day,$channel) = $group;
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
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
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
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        //pikiran rakyat
        # http://www.pikiran-rakyat.com/cetak/2005/0405/26/0103.htm
        $regex = 'www.pikiran-rakyat.com/([^/]+)/(\d+)/(\d\d)\d\d/(\d\d)/(\d\d)(.)';
        $cl = array('01'=>'Berita Utama', '02'=>'Tajuk Rencana','03'=>'Bandung Raya', '04'=>'Jawa Barat', '05'=>'Dalam Negri', '06'=>'Ekonomi', '07'=>'Olah Raga', '08'=>'Artikel', '09'=>'Ada Siapa', '10'=>'Surat Pembaca');
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
                list($dummy,$dummy, $year,$month,$day,$cid,$mark) = $group;
                $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
                $channel = $cl[$cid];
                if ($channel == '') $channel = 'Lain-lain';
                $link['channel'] = $channel;
                if ($mark == '.') $link['list'] = 1;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        // bbc indonesia
        #http://www.bbc.co.uk/indonesian/news/story/2005/04/050425_japantraincrash.shtml
        $regex = 'www.bbc.co.uk/indonesian/([^/]+)/story/(\d+)/(\d\d)/\d\d\d\d(\d\d)';
        $channel = 'Berita Dunia';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
                list($dummy,$channel,$year,$month,$day) = $group;
                $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        //suara merdeka
        # http://www.suaramerdeka.com/harian/0504/26/nas03.htm
        $regex = 'www.suaramerdeka.com/.*?/(\d\d)(\d\d)/(\d\d)/';
        $channel = 'Nasional';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
                list($dummy,$year,$month,$day) = $group;
                $link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        //jawa pos
        # http://www.jawapos.com/index.php?act=detail&id=4866
        $regex = 'www.jawapos.com/index.php\?act=([^\&]+)\&';
        #~ http://www.jawapos.com/index.php?act=khusus&id=76
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            #~ echo '<br>'.$link['href'];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($dummy,$channel) = $group;
                $link['channel'] = $channel;
                $channels[$channel] += 1;
                if (stristr($link['href'],'detail') === FALSE) $link['list'] = 1;
            }
            $links[$i] = $link;
        }

        // ABC radio australia - indonesia
        # http://www.abc.net.au/ra/indon/news/stories/s1353282.htm
        $regex = 'www.abc.net.au/ra/indon/news/stories/';
        $channel = 'Berita Terbaru';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        //warta ekonomi
        # http://www.wartaekonomi.com/detail.asp?aid=4623&cid=2
        $regex = 'www.wartaekonomi.com/detail.asp\?aid';
        $channel = 'Berita';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        //gatra
        # http://www.gatra.com/artikel.php?id=83790
        $regex = 'www.gatra.com/artikel.php';
        $channel = 'Artikel';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        //gatra-rubrik
        #~ http://www.gatra.com/rubrik.php?id=17
        $regex = 'www.gatra.com/rubrik.php';
        $channel = 'Rubrik';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                $link['channel'] = $channel;
                $link['list'] = 1;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        //swa
        # http://www.swa.co.id/primer/manajemen/strategi/details.php?cid=1&id=2486
        $regex = 'www.swa.co.id/.*?/([^/]+)/.*?/details.php';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($dummy,$channel) = $group;
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        //infokomputer
        # http://www.infokomputer.com/aktual/aktual.php?id=3966
        $regex = 'www.infokomputer.com/aktual/aktual.php';
        $channel = 'Aktual';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        //poskota
        # http://www.poskota.co.id/poskota/headline_contents.asp?id=5196&file=index
        # http://www.poskota.co.id/poskota/truestory_contents.asp?id=735&page=&file=index
        $regex = 'www.poskota.co.id/poskota/.*?.asp\?id=';
        $channel = 'Headlines';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        //ibonweb
        # http://articles.ibonweb.com/webarticle.asp?num=1515
        $regex = 'articles.ibonweb.com/webarticle.asp';
        $channel = 'Articles';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        //berita iptek
        # http://www.beritaiptek.com/messages/artikel/933032005em.shtml
        # http://www.beritaiptek.com/messages/iptekindonesia/874082005em.shtml
        $regex = 'www.beritaiptek.com/messages/([^/]+)/';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = $links[$i];
            if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
                list($dummy,$channel) = $group;
                $link['channel'] = $channel;
                $channels[$channel] += 1;
            }
            $links[$i] = $link;
        }

        $this->news['channels'] = $channels;
        $this->news['links'] = $links;
    }

    function ParseDetail() {
        while (1) {
            // HUMAN_DETAIL_PARSER
            // kompas 1
            $regex = '<span class="sectionHL">(.*?)<.*?<p><b>(.*?)<.*?<!---Start--->(.*?)<!--';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$reporter,$body) = $group; break;
            }
            // kompas 2
            $regex = 'size="4">(.*?)</font.*?<font color="#000000" face="Arial" size="2">(.*?)</font.*?<!---Start--->(.*?)&nbsp;&nbsp;&nbsp;&nbsp;';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$reporter,$body) = $group; break;
            }
            //kompas july2006
            $regex = '<span class="txttagline"><br>(.*?)</span>.*?<b></b><br><P>(.*?)</p><p>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy, $title,$body) = $group; break;
            }

            // jakartapost 1
            $regex = '<!-- Put the news record in here -->(.*?)</font>.*?<font face="Arial, Helvetica" size="2">(.*?)<hr noshade';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            // jakartapost 2
            $regex = '<p><font face="Helvetica, Arial" size=3>(.*?)</font>.*?<font face="Arial, Helvetica" size="2">(.*?)</font>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            // antara 1
            $regex = '<span class="clsfont3">(.*?)</span>.*?<span class="clsfont1">(.*?)<p align=right>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            // detik-foto
            $regex = '<BR><FONT size=5>(.*?)</font>.*?<FONT color=#ff0000 size=2>(.*?)</font>.*?P align="Justify">(.*?)<!-- FORM BERITA ';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$reporter,$body) = $group; break;
            }
            // detik-inet
            $regex = '<font class="subjudulberita">(.*?)</font>.*?<font class="judulberita">(.*?)</font>.*?<font class="textreporter">(.*?)</font>.*?<font class="textberita">(.*?)</blockquote>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$subtitle,$title,$reporter,$body) = $group; break;
            }
            // detik-news
            $regex = '<font class="judulberita">(.*?)</font>.*?<font class="textreporter">(.*?)</font>.*?<font class="textberita">(.*?)\n</font>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$reporter,$body) = $group; break;
            }
            // republika (old)
            $regex ='<font class="headline">(.*?)</font>.*?<font class="copy">(.*?)</font>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }

            // republika (new)
            $regex = '<font class="judul">(.*?)</font>.*?<font class="navigasi">(.*?)</font>.*?<font class="deskripsi">(.*?)</font>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$reporter,$body) = $group; break;
            }

            // republika (new-tanpa reporter)
            $regex = '<font class="judul">(.*?)</font>.*?<font class="deskripsi">(.*?)</font>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }

            // tempo interaktif
            $regex = '<meta name="title" content="([^"]*?)".*?<font color=#666666>(.*?)</font></p> ';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            // suara pembaruan
            $regex = '<H1>(.*?)</H1>.*?<P>(.*)<HR>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            // media indonesia
            $regex = '<div class=JudulBerita>(.*?)</div>.*?<p class=BeritaBaca>(.*?)<div>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }
            // media indonesia 2
            $regex = '<span class=JudulRubrik>(.*?)</span>.*?<span class=JudulBerita>(.*?)</span>.*<tr><td>\s+<p>(.*?)<div class=PrintMail>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$subtitle,$title,$body) = $group; break;
            }
            // bbc indonesia
            $regex = '<!-- st_title -->(.*?)<!-- end_title -->.*?<!-- st_story -->(.*?)<div class="six">';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }

            // pikiran rakyat
            $regex = '<font size="5">(.*?)</font>.*?</font><p>(.*?)</font></td>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }

            // suara merdeka
            $regex = '<div id="AktualJudul">(.*?)</div>.*?<div id="AktualIsi">(.*?)</div>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }

            // abc radio
            $regex = '<div class="pad20">.*?<br><b>(.*?)</b>.*?<br><br>(.*?)<br><br>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }

            //jawapos
            $regex = '<font size="4" face="Times New Roman, Times, serif">(.*?)</font>.*?<font face="Arial" size="2">(.*?)<br></font><br>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }

            //warta ekonomi
            $regex = '<font face="Arial" size="3"><b>(.*?)</b>.*?<div align= "justify">(.*?)<td width="100%" align=center>&nbsp;<p>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }

            #~ //sinar pagi
            #~ $regex = '<p ALIGN="justify" style="margin-top: 0; margin-bottom: 0"></p>(.*?)</p>.*?<p ALIGN="justify" style="margin-top: 0; margin-bottom: 0">&nbsp;</p>(.*?)</font></td>';
            #~ if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                #~ list($dummy,$title,$body) = $group; break;
            #~ }

            // gatra
            $regex = '<span class=judul>(.*?)</span>.*?<br><br>(.*?)<br><br>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }

            // swa
            $regex = '<span class="texttitle03">(.*?)</span>.*?<span class="copy03">(.*?)</span>(.*?)</DIV><BR><BR>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$reporter,$body) = $group; break;
            }

            // infokomputer
            $regex = '<font size="5">(.*?)</font>.*?<font size="2" color="Blue">(.*?)</font>.*?</font><br><br>(.*?)</font><br><br>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$reporter,$body) = $group; break;
            }

            //poskota
            $regex = '<td class="title1">(.*?)</td>.*?<td class="content_biru">(.*?)</td>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }

            //ibonweb
            $regex = '<p class="title">(.*?)</p>.*?<p class="body">(.*?)<p>&nbsp;</p>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }

            //berita iptek
            $regex = '<!--top: 95-->(.*?)<font size=2>.*?<font size=2>(.*?)</td></tr>';
            if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
                list($dummy,$title,$body) = $group; break;
            }

            return False;
        }
        $this->news['subtitle'] = $subtitle;
        $this->news['title'] = $title;
        $this->news['reporter'] = $reporter;
        $this->news['content'] = $body;
        $this->news['date'] = $_REQUEST['unxdt'];
        $this->news['url'] = $this->url;    # save url, will parse later
        #~ print_r($this->news);
        return True;
    }

    function QualifyLinks() {
        /*
        divide links into three groups:
        1) links which match by specific regex for this news -- can be detected by having 'channel' property
        2) links which url's length is on the upper half of median
        3) the rest of the links
        */

        $links = &$this->news['links'];

        // (1)
        if ($this->news['channels']) {
            for ($i=0; $i < count($links) ; $i++) {
                $link = &$links[$i];
                if ($link['group'] or $link['channel'] == '') continue;
                $link['group'] = 1;
            }
        }

        // (2)
        # - create statistics on string length, get the median, show the upper median as \n-seperated link, the rest | seperated link
        $stat_count = 0; $stat_val = 0;
        $stat_count2 = 0; $stat_val2 = 0;
        for ($i=0; $i < count($links) ; $i++) {
            $link = &$links[$i];
            if ($link['group']) continue;
            $len = strlen($link['cleantext']);
            if ($len == 0) continue;
            $link['score'] = $len;
            $link['score2'] = strlen($link['href']);
            $stat_val += $link['score'];
            $stat_val2 += $link['score2'];
            $stat_count++;
            $stat_count2++;
        }
        if ($stat_count) {
            $stat_avg = $stat_val/$stat_count;
            $stat_avg2 = $stat_val2/$stat_count2;
            for ($i = 0; $i < count($links) ; $i++) {
                $link = &$links[$i];
                if ($link['group']) continue;
                if ($link['score'] < $stat_avg or $link['score2'] < $stat_avg2) continue;
                $link['group'] = 2;
            }
        }

        // (3)
        //the rest of the links
        for ($i=0; $i < count($links) ; $i++) {
            if ($links[$i]['group']) continue;
            $links[$i]['group'] = 3;
        }

        $this->links_qualified = True;
    }

    function RenderListNormal($frame_target='m') {
        global $author_email;

        if (!$this->links_qualified)
            $this->QualifyLinks();

        $links = &$this->news['links'];

        if ($this->news['cache']) {
            echo '<p>list dari cache '.str_time_delta($this->news['cache'],FALSE,FALSE);
            if ((time() - $this->news['cache'])>$this->newslist_cache_revalidate) {
                if ($_REQUEST['pda']) echo ' (press reload button for realtime update)';
                else echo '. Auto-loading berita baru di background.';
            }
            echo '</p>';
        }

        if ($this->news['channels']) {
            foreach ($this->news['channels'] as $channel=>$kount) {    //  view topic news
                echo '<h2>'.ucwords($channel).'</h2>';
                echo '<ul>';
                for ($i = 0; $i < count($links) ; $i++) {
                    $link = &$links[$i];
                    if ($link['group'] != 1 or $link['printed'] or $link['channel'] != $channel) continue;
                    $news_url = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($link['unixtime']).'&amp;url='.urlencode($link['href']);
                    if ($link['cleantext'] == '') { # make sure hyperlink is clickable
                        $link_text = '...'.substr($link['href'],-30);
                    }
                    else
                        $link_text = $link['cleantext'];
                    if ($link['summary'])
                        $link_summary = '<p>'.$link['summary'].'</p>';
                    else
                        $link_summary = '';
                    // sign that this link is meant to be open as list, not as news detail
                    if ($link['list']) {
                        echo '<li><a href="'.$_SERVER['PHP_SELF'].'?x=i&amp;anurl='.urlencode($link['href']).'">'.$link_text.'</a>'.$link['summary'].'</li>';
                    }
                    elseif ($link['unixtime'])  { # if this link has known date in it
                        $date = date('H:i',$link['unixtime']);
                        $date_delta = str_time_delta($link['unixtime']);
                        // time information may not available
                        if ($date == '00:00')
                            $date_exp = $date_delta;
                        else
                            $date_exp = $date.', '.$date_delta;
                        echo '<li><a href="'.$_SERVER['PHP_SELF'].'?unxdt='.urlencode($link['unixtime']).'&anurl='.htmlentities(urlencode($link['href'])).'" target="'.$frame_target.'">'.$link_text.'</a>'.$link_summary.' -- '.$date_exp.'</li>';
                    }
                    else
                        echo '<li><a href="'.$_SERVER['PHP_SELF'].'?anurl='.urlencode($link['href']).'" target="'.$frame_target.'">'.$link_text.'</a>'.$link_summary.'</li>';

                    $link['printed'] = 1;  # mark link as havebeen written to speed up searching
                }
                echo '</ul>';
            }
        }

        # other links which have not been printed
        echo '<hr>';
        echo '<ul>';
        for ($i = 0; $i < count($links) ; $i++) {
            $link = &$links[$i];
            if ($link['group'] != 2 or $link['printed'] or $link['score'] < $stat_avg or $link['score2'] < $stat_avg2) continue;
            echo '<li><a href="'.$_SERVER['PHP_SELF'].'?x=i&anurl='.urlencode($link['href']).'">'.$link['cleantext'].'</a></span> - <a href="'.$_SERVER['PHP_SELF'].'?anurl='.urlencode($link['href']).'" target="'.$frame_target.'">&gt;&gt;</a></li>';
            $link['printed'] = 1;  # mark link as have been written to speed up searching
        }
        echo '</ul>';


        //the rest of the links
        echo '<hr>';

        $e = array();
        for ($i = 0; $i < count($links) ; $i++) {
            $link = &$links[$i];
            if ($link['group'] != 3 or $link['printed'] or trim($link['cleantext']) == '') continue;
            $news_url = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($link['unixtime']).'&amp;url='.urlencode($link['href']);
            if ($link['cleantext'] == '') { # make sure hyperlink is clickable
                $link_text = '...'.substr($link['href'],-30);
            }
            else
                $link_text = $link['cleantext'];

            if ($link['unixtime'])  { # if this link has known date in it
                $date = date('H:i',$link['unixtime']);
                $date_delta = str_time_delta($link['unixtime']);
                // time information may not available
                if ($date == '00:00')
                    $date_exp = $date_delta;
                else
                    $date_exp = $date.', '.$date_delta;
                $e[] = '<a href="'.$_SERVER['PHP_SELF'].'?unxdt='.urlencode($link['unixtime']).'&amp;anurl='.urlencode($link['href']).'" target="'.$frame_target.'">'.$link_text.'</a></span>';
            }
            else
                $e[] = '<a href="'.$_SERVER['PHP_SELF'].'?x=i&anurl='.urlencode($link['href']).'">'.$link_text.'</a></span>';
        }
        echo join(' | ',$e);
    }

    function RenderDetailNormal ($complete=True,$with_header=True,$strip_ads=False) {
        /* renderer for news detail/body */
        global $url, $app;
        $content = $this->news['content'];
        if ($with_header) echo HtmlHeader();

        echo '<div id="content-main">';
        if ($complete) {
            if ($this->news['date'] != '')
                echo '<p class="date">'.date('Y-m-d H:i:s',$this->news['date']).'</p>';
            echo '<h1>'.$this->news['subtitle'].' '.$this->news['title'].'</h1>';
            echo '<div id="author"><p>'.$this->news['reporter'].'</p></div>';
        }
        echo '<div id="body"><p>'.$content.'</p></div>';
        echo '<div id="endnote">';
        if ($this->from_cache) echo '<p>news from cache | <a href="'.$_SERVER['PHP_SELF'].'?unxdt='.$_REQUEST['unxdt'].'&amp;anurl='.$this->news['url'].'&cache_reload=1">Reload from Original</a></p>';
        echo '<p><a href="'.$this->news['url'].'">'.$this->news['url'].'</a></p>';
        if ($this->footer_info != '')
            echo '<p>'.$this->footer_info.'</p>';
        echo '</div>';  #end note
        if ($with_header) {
            $footsy = array();
            $footsy[] = 'rgxid '.$this->news['rgxid'];      # show which regex parse this detail (for easy fixing)
            echo show_footer($this->news['url'],$footsy);
        }
    }

    function RenderListRss ($with_content=False, $link_to_orig=True) {
    /* news list renderer for RSS
    @with_content  True to include complete news body inside RSS
    @$link_to_orig  True so set RSS link to original news source, instead of AnyNews's detail page
    */
        global $app,$author_name,$author_email;

        if (!$this->links_qualified)
            $this->QualifyLinks();

        header('Content-Type: text/xml');
        echo '<?xml version="1.0"?><rss version="2.0"><channel><title>'.$this->GetTitleName().'</title><link>'.($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'</link><description>'.htmlentities($app['version']).'</description><language>id</language><category>news</category><managingEditor>'.$author_email.'</managingEditor><webMaster>'.$author_email.'</webMaster><lastBuildDate>'.date('r').'</lastBuildDate><generator>'.$app['name'].' v'.$app['version'].'</generator>';
        $nl = "\r\n";
        foreach ($this->news['links'] as $link) {
            if ($link['group'] == 3) continue;
            $url_from_anynews = ($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?unxdt='.urlencode($link['unixtime']).'&anurl='.urlencode($link['href']);
            if ($link_to_orig)
                $url = $link['href'];
            else
                $url = $url_from_anynews;

            if ($link['cleantext'] == '') { # make sure hyperlink is clickable
                $link_text = '...'.substr($link['href'],-30);
            }
            else
                $link_text = $link['cleantext'];
            if ($with_content) {
                $du = new AnyNews($_REQUEST['anurl']); $du->SetModeDetail(); $du->SetDetailDate($link['unixtime']); $du->GetBuffer();
                if (!$du->Parse()) {
                    //news detail was unparseable. what should we do?
                    $description = $link['summary'].$nl.'<p>We are sorry, we could not parse the complete news. See detail in <a href="'.$url_from_anynews.'">anynews</a> or <a href="'.$link['href'].'">original site</a>';
                }
                else {
                    $du->CaptureStart(); $du->RenderDetailRss();
                    $description = $du->CaptureEnd();
                }
            }
            else
                $description = $link['summary'].$nl.'<p>See detail in <a href="'.$url_from_anynews.'">anynews</a> or <a href="'.$link['href'].'">original site</a>';
            echo '<item>'.$nl.'  <title>'.htmlentities(strip_tags($link_text)).'</title>'.$nl.'  <link>'.htmlentities($url).'</link>'.$nl.'  <description>'.htmlentities($description).'</description>'.$nl.'  <guid>'.htmlentities($link['href']).'</guid>'.$nl.'  <pubDate>'.date('r',$link['unixtime']).'</pubDate>'.$nl.'  <category>Headlines</category>'.$nl.'</item>'.$nl;
        }
        echo '</channel></rss>';
        return $list;
    }
}
?>