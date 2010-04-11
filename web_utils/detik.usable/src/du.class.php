<?

require_once('du_sock.class.php');

function str_time_delta ($date_c,$compressed=FALSE,$localtz=FALSE) {
    /* show relative duration of time.
    @localtz = TRUE to assume that date_c is date from news source (usually +7), so this will show duration in localtime (see app['timediff'])
    */
    global $ctime, $app, $tz_diff;
    if ($localtz)
        $delta = $ctime - $date_c + 3600*$tz_diff;
    else
        $delta = $ctime - $date_c;
    # if hour:minute is exactly 0:00, then show like 'hari ini', kemarin, 2 hari lalu. since it could means that time info is not available.
    if (date('Hi',$date_c) == '0000') {
        if ($delta < 86400)
            return ($compressed? 'hr':'hari').' ini';
        elseif ($delta < 2*86400)
            return ($compressed? 'kmrn':'kemarin');
        else
            return floor($delta/86400).($compressed? 'hr':' hari').' lalu';
    }
    if ($delta < 60) {  # dibawah 1 menit
        $satuan = $compressed? 'dtk':' detik';
        $tgl = $delta;
    }
    elseif ($delta < 3600) {    # dibawah 1 jam
        $satuan = $compressed? 'mnt':' menit';
        $tgl = floor($delta/60);
    }
    elseif ($delta < 86400) {   # dibawah 1 hari
        $satuan = $compressed? 'j':' jam';
        $tgl = sprintf('%01.1f',$delta/3600);
    }
    else {
        $hari = floor($delta/86400);
        $jam = floor(($delta - $hari*86400) /3600);
        if ($jam == 0) return $hari.($compressed? 'hr':' hari');
        else return $hari.($compressed? 'hr':' hari').' '.$jam.($compressed? 'j':' jam');
    }
    return $tgl.$satuan.' lalu';
}

class DetikUsable {
    var $nodeserver_url = '';
    var $source = '';
    var $already_parsed = False;
    var $proxy_url = '';
    var $newslist_cache_revalidate = 300; // time in seconds in which news list become invalidated, and automatically reload fresh data from original source
    var $regex_list_prevnews = "|=.nmkanal(.*?)indeks berita(.*)|is";
    #~ var $regex_list_prevnews_all = '|(\d+/\d+/\d+.*?)<.*?<a href="([^"]+url=[^"]+)" class=[^>]*>(.*?<span class="nonhlJudul">.*?)</A>|is';
    var $regex_list_prevnews_all = '|<span class="tglnonhl">.*?(\d+/\d+/\d+.*?)<.*?<a href="([^"]+)" class="nonhl"[^>]*>(.*?<span class="nonhlJudul">.*?)</A>|is';
    var $regex_list_prevnews_subtitle = '|><span class=.nonhlSubJudul.>(.+?)</span>|';
    var $regex_list_prevnews_title = '|nonhlJudul.>(.*)|';
    var $regex_list_headline = '|(<span class="tanggal">.*?)<!-- End of Center(.*)|is';
    #~ var $regex_list_headline_all = '|tanggal.>[^,]*,(.*?) WIB<.*?<A href="([^"]+url=[^"]+)" class=[^>]*>(.*?<span class="summary">.*?</span>)|is';
    var $regex_list_headline_all = '|tanggal.>[^,]*,(.*?) WIB<.*?<A href="([^"]+)" class="hl"[^>]*>(.*?<span class="summary">.*?</span>)|is';
    var $regex_list_headline_subtitle = '|subjudul.>(.*?)</span|is';
    var $regex_list_headline_title = '|strJudul.>(.+?)</span|is';
    var $regex_list_headline_summary = '|summary.>(.*?)</span|s';
    var $regex_list_topic_all = '|(<h\d>[^<]*?)</h\d>\s*<ul>(.*?)</ul>|si';
    var $regex_list_topic_detail = '|<a href="([^"]+tahun/[^"]+)"[^>]*>.*?"judulhlbawah">(.+?)</span>|is';
    var $regex_list_topic_detail_basic = '|<a href="([^"]+)"[^>]*>.*?"judulhlbawah">(.+?)</span>|is';   # in case url format is unknown
    var $regex_list_headline_date = '|/tahun/(\d*)/bulan/(\d*)/tgl/(\d*)/|i';   # skip the time. too unpredictable.
    var $regex_detail = "|<blockquote>(.*?)<!-- FORM|is";

    var $last_error = 0;
    var $last_parse_ok = False;

    # -------- <REGEX> --------
    function DetikUsable ($url='') {
        global $app;
        $this->SetSourceFast();
        $this->SetUrl($url);
        $this->proxy_url = $app['proxy_url'];
        $this->zlib_support = extension_loaded('zlib');
        $this->enable_cache = $app['cache'];
        $this->unxdt = $_REQUEST['unxdt'];
        $this->url_list = $app['url_list'];
        $this->cache_prefix = '';   # prefix appended to cache file. needed for anynews detikcom.
        $this->cookie_code = '';
        $this->footer_info = '';
        $this->url_base = '';       # <base> HTML tag defining proper absolute base URL for relative anchor links
    }
    function GetSourceMode () {
        return $this->source;
    }
    function SetSourceNode ($url='') {
        if ($url != '') $this->url = $url;
        $this->source = 'node';
    }
    function SetSourceCache ($url='') {
        if ($url != '') $this->url = $url;
        $this->source = 'cache';
    }
    function SetSourceOrig ($url='') {
        if ($url != '') $this->url = $url;
        $this->source = 'orig';
    }
    function SetSourceFast ($url='') {
        if ($url != '') $this->url = $url;
        $this->source = 'fast';
    }
    function SetUrl ($url) {
        $this->url = $url;
    }
    function SetNodeServerUrl ($url) {
        $this->nodeserver_url = $url;
    }
    function GetDataMode () {
        return $this->data_mode;
    }
    function SetModeDetail () {
        $this->data_mode = 'detail';
    }
    function SetModeList () {
        $this->data_mode = 'list';
        if ($this->url == '') $this->url = $this->url_list;
    }
    function SetDetailDate ($dt) {
        $this->unxdt = $dt;
    }
    function GetBufferNode ($url) {
    /* return buffer taken from other du node
    @url: the original detikcom url
    @mode: 'list'=take list, 'detail'=take detail
    */
        if ($this->data_mode == 'list') $this->proxy_url .= "?url=$url&amp;as_node=1";
        elseif ($this->data_mode == 'detail') $this->proxy_url .= "?x=i&amp;as_node=1";
        else die('Invalid mode');
        if (!$this->zlib_support) $this->proxy_url .= '&amp;uc=1';    //ask uncompressed stream if i don't support zlib library
        $url_parsed = parse_url($this->proxy_url);
        $sock = new DuSock($url_parsed['host'],$url_parsed['port']!=''? $url_parsed['port'] : 80);
        if (!$sock->socket_open()) die('Cannot contact repository at '.$this->proxy_url);
        $sock->sock_send_request($url_parsed['path'].'?'.$url_parsed['query']);
        $sock->sock_recv_header();
        $buffer = $sock->sock_recv_all();
        if ($buffer=='') { die('getter_node: unable to download node data');}
        $this->buffer = &$buffer;
    }
    function GetBufferCache($force_reload=False,$although_expired=False) {
    /*
    @force_reload:
        False = take from cache if not expired (if any, and app configured to cache)
        True = always take from original
    @although_expired: False=invalidate cache if expired, True=use cache although expired
    */
        if (!$this->enable_cache) return;
        if ($this->GetDataMode() == 'list' and $this->url == '') $this->url = $this->url_list;
        elseif ($this->GetDataMode() == 'detail' or $this->GetDataMode() == 'an_detail') $although_expired = True; # detail is considered stable
        $this->from_cache = False;
        #~ $filename = 'cache/'.md5($this->url);
        $filename = 'cache/'.$this->cache_prefix.md5($this->url);   # need to add more signature, since AnyNews detikcom conflict with DU
        if (!file_exists($filename) or $force_reload) {         # check if already in cache
            $this->SetGetBufferOK(False);
            return;
        }
        $buffer = '';
        $fp = fopen($filename,'r');
        while(!feof($fp)) $buffer .= fread($fp,1024);
        fclose($fp);
        $this->news = unserialize($buffer);
        if (!$although_expired and (time() - $this->news['cache']) > $this->newslist_cache_revalidate ) {$this->SetGetBufferOK(False);return;} # check whether cache is expired
        $this->already_parsed = True;   # cache buffer already contain parsed news
        $this->last_parse_ok = True;
        $this->SetGetBufferOK(True);
        $this->from_cache = True;
    }
    function SaveCache () {
    /* save serialized array to file
    */
        if (!$this->enable_cache or !$this->news) return;
        if (!file_exists('cache')) mkdir('cache',0755);
        #~ $filename = 'cache/'.md5($this->url);
        $filename = 'cache/'.$this->cache_prefix.md5($this->url);   # need to add more signature, since AnyNews detikcom conflict with DU
        $this->news['cache'] = time();    # record the time of cache last updated. Note: it's on localtime.
        $buffer = serialize($this->news);
        unset($this->news['cache']);      # remove back our modification
        $fp = fopen($filename,'w');
        fwrite($fp,$buffer);
        fclose($fp);
    }
    function GetBufferOrig () {
        if ($this->GetDataMode() == 'list' and $this->url == '') $this->url = &$this->url_list;
        elseif ($this->GetDataMode() == 'detail')
            assert($this->url != '');
        #~ echo '<br/>GettingBufferOrig'.$this->url;
        $url_parsed = parse_url($this->url);
        $sock = new DuSock($url_parsed['host'],$url_parsed['port']);
        if (!$sock->socket_open()) {
            $this->last_error = ERROR_SOCKET;
            return False;
        }

        $sock->cookie = $this->cookie_code;
        $sock->sock_send_request($url_parsed['path'].(($url_parsed['query'] == '')?'':'?'.$url_parsed['query']));
        $header_buffer = $sock->sock_recv_header();
        $this->buffer = $sock->sock_recv_all();
        #~ echo $this->buffer;
        if (preg_match('|Content-Encoding:\s*gzip|i',$header_buffer)) { // if Content-Encoding: gzip, then body is gzipped. Unzipped first.
            $this->buffer = gzinflate(substr($this->buffer, 10,-4));  //skip the first 10 characters,as they are GZIP header, and php's gzinflate only need the data
        }
        $this->ParseAnchor();
        $this->last_parse_ok = $this->Parse();
        $this->SaveCache();
    }
    function IsGetBufferOK () {
        return $this->getbuffer_ok;
    }
    function SetGetBufferOK ($ok=True) {
        assert(is_bool($ok));
        $this->getbuffer_ok = $ok;
    }

    function PrepareGetBuffer() {
        $url = $this->url;
        if ($this->GetDataMode() == 'detail') {
            if (strpos($url, 'detikhot.com') !== FALSE and strpos($url, 'idnews') == FALSE) {    # link to detikhot from frontpage.
                # expect param1, the title of the article
                # visit detikhot and get its content (cache it)
                $du2 = new AnyNews($url);
                $du2->SetModeList();
                $du2->cache_prefix = 'an_';   # parse using new parser to get title->url link
                $du2->GetBuffer();
                # find link with same title
                $success = False;
                foreach ($du2->news['links'] as $link) {
                    if ($link['cleantext'] == '') continue; # optimization
                    if ($_REQUEST['param1'] != '' and strpos($link['cleantext'], $_REQUEST['param1']) !== FALSE) {
                        $this->url = $link['href'];
                        $success = True;
                        break;
                    }
                }
                if (!$success) {    # try to find the nearest URL
                    $rank = array();
                    foreach ($du2->news['links'] as $link) {
                        if ($link['cleantext'] == '') continue; # optimization
                        $score = levenshtein($link['cleantext'], $_REQUEST['param1']);
                        $rank[$score] = $link['href'];
                    }
                    ksort($rank);
                    reset($rank);
                    list($key, $val) = each($rank);
                    $this->url = $val;
                    $success = True;
                }
                if (!$success) {print_r($du2->news['links']); die('Cant find url for title "'.$_REQUEST['param1'].'"');}
                unset($du2);
                # continue as usual...
            }
            #~ elseif (strpos($url, 'detik.read') !== FALSE) {
                #~ $this->cookie_code = $this->GetPageForCookieCode('/indexfr.php?url='.$this->url);
            #~ }
            elseif (strpos($url, 'detikfinance.com') !== FALSE) {
                $this->cookie_code = $this->GetPageForCookieCode('/indexfr.php?url='.$this->url);
            }
            elseif ((strpos($url, 'detikinet.com') !== FALSE or strpos($url, 'detiksport.com') !== FALSE or strpos($url, 'detikpublishing.com') !== FALSE) and strpos($url, 'idnews') === FALSE ) {    # link to detikhot from frontpage.
                # expect param1, the title of the article
                # visit detikhot and get its content (cache it)
                # retrieve cookie first
                $du2 = new AnyNews($url);
                #~ $du2->cookie_code = $du2->GetPageForCookieCode('/indexfr.php?url='.$du2->url);
                #~ $this->cookie_code = $du2->cookie_code;

                $du2->SetModeList();
                $du2->cache_prefix = 'an_';   # parse using new parser to get title->url link
                $du2->GetBuffer();
                $success = False;
                foreach ($du2->news['links'] as $link) {
                    if ($link['cleantext'] == '') continue; # optimization
                    if ($_REQUEST['param1'] != '' and strpos($link['cleantext'], $_REQUEST['param1']) !== FALSE) {
                        $this->url = $link['href'];
                        $success = True;
                        break;
                    }
                }
                if (!$success) {    # try to find the nearest URL
                    $rank = array();
                    foreach ($du2->news['links'] as $link) {
                        if ($link['cleantext'] == '') continue; # optimization
                        $score = levenshtein($link['cleantext'], $_REQUEST['param1']);
                        $rank[$score] = $link['href'];
                    }
                    ksort($rank);
                    reset($rank);
                    list($key, $val) = each($rank);
                    $this->url = $val;
                    $success = True;
                }
                if (!$success) {print_r($du2->news['links']); die('Cant find url for title "'.$_REQUEST['param1'].'"');}
                unset($du2);
                # continue as usual...
            }
        }
    }

    function GetBuffer () {
        $this->PrepareGetBuffer();
        if ($this->GetSourceMode() == 'node')
            return $this->GetBufferNode();
        elseif ($this->GetSourceMode() == 'orig')
            return $this->GetBufferOrig();
        elseif ($this->GetSourceMode() == 'cache')
            return $this->GetBufferCache();
        elseif ($this->GetSourceMode() == 'fast') {
            # try fastest source first
            $this->GetBufferCache();
            if ($this->IsGetBufferOK()) return;
            if ($this->nodeserver_url != '') {
                $this->GetBufferNode();
                if ($this->IsGetBufferOK()) return;
            }
            return $this->GetBufferOrig();
        }
        else {
            die('Unknown source mode:'.$this->GetSourceMode());
        }
    }
    function ParseNode () {
    /* return $news array from node buffer. support both list and detail.
    @buffer: reference to buffer taken from other node to be parsed
    */
        if ($this->zlib_support) {
            $buffer2 = @gzuncompress($buffer);   // try to uncompress
            if (!$buffer2 or is_bool($buffer2)) {    //it's probably not gzcompressed
            }
            else {
                $buffer = &$buffer2;  // change to new buffer
                $stream_compress = TRUE;
            }
        }
        $news = unserialize($buffer);
        if (!$news) {
            die('newslist: from node: Unable to unserialize data');
        }
        if (!is_array($news)) {
            die("newslist: from node: Data was not formatted correctly");
        }
        # todo: write news to cache
        $this->news = &$news;
        $this->already_parsed = True;
    }

    function ParseList () {
        global $tz_diff;
        $this->ParseAds();
        # narrowing-in to "prevnews" content
        if (preg_match($this->regex_list_prevnews,$this->buffer,$result)) {
            $narrow_buffer = $result[1];
            $remaining_buffer = $result[2];
        }
        else {
            $narrow_buffer = $this->buffer;   # continue anyway
        }



        if (preg_match_all($this->regex_list_prevnews_all,$narrow_buffer,$result)) {
            unset($narrow_buffer);
            $total_prev_news = count($result[2]);
            for ($i = 0; $i < $total_prev_news; $i++) {
                $url = $result[2][$i];
                $date = $result[1][$i];
                $title_temp = $result[3][$i];   # contain title and possibily subtitle
                $date = preg_replace('/([0-9]*)\/([0-9]*)\//','\\2/\\1/', $date);
                $this->news['prevnews'][$i]['date'] = strtotime($date);
                if (!preg_match('/http:\/\//',$url)) {   //  makeit absolute url
                    $url = 'http://www.detik.com'.$url;
                }
                if (preg_match('/\?url=(.*)/',$url,$url_res)) {  // if link formatted like ...?url=http://.... retrieve the param value instead
                    $url = $url_res[1];
                }
                $this->news['prevnews'][$i]['url'] = $url;
                $this->news['prevnews'][$i]['subtitle'] = '';
                if (preg_match($this->regex_list_prevnews_subtitle,$title_temp,$subtitle_res)) {
                    $this->news['prevnews'][$i]['subtitle'] = $subtitle_res[1];
                }
                if (preg_match($this->regex_list_prevnews_title,$title_temp,$title_res)) {
                    $this->news['prevnews'][$i]['title'] = $title_res[1];
                }
                if (trim($this->news['prevnews'][$i]['title']) == '') $this->news['prevnews'][$i]['title'] = $this->GetAnchorText($this->news['prevnews'][$i]['url']);
            }
        }

        //    narrowing-in to headline news content
        if (preg_match($this->regex_list_headline,$remaining_buffer,$result)) {
            $narrow_buffer = $result[1];
            $remaining_buffer = $result[2];
            assert(preg_match_all($this->regex_list_headline_all,$narrow_buffer,$result));
            unset($narrow_buffer);
            $total_news = count($result[2]);
            for ($i = 0; $i < $total_news; $i++) {
                $date = $result[1][$i];
                $url = $result[2][$i];
                $title = $result[3][$i];
                if (!preg_match('/http:\/\//',$url)) { #makeit absolute url
                    $url = 'http://www.detik.com'.$url;
                }
                if (preg_match('/\?url=(.*)/',$url,$url_res)) { // if link formatted like ...?url=http://.... retrieve the param value instead
                    $url = $url_res[1];
                }
                $this->news['headline'][$i]['url'] = $url;
                $this->news['headline'][$i]['subtitle'] = '';
                if (preg_match($this->regex_list_headline_subtitle,$title,$subtitle_res)) {
                    $this->news['headline'][$i]['subtitle'] = $subtitle_res[1];
                }
                if (preg_match($this->regex_list_headline_title,$title,$title_res)) {
                    $this->news['headline'][$i]['title'] = $title_res[1];
                }
                if (trim($this->news['headline'][$i]['title']) == '') $this->news['headline'][$i]['title'] = $this->GetAnchorText($this->news['headline'][$i]['url']);
                if (preg_match($this->regex_list_headline_summary,$title,$summary_res)) {
                    $this->news['headline'][$i]['summary'] = strip_tags($summary_res[1]);
                }
                $date = preg_replace('|([0-9]*)/([0-9]*)/|','\\2/\\1/', $date);
                $this->news['headline'][$i]['date'] = strtotime($date)+$tz_diff*3600;   # fix date to local timezone
            }
        }

        //    narrowing-in to topic news content
        if (preg_match_all($this->regex_list_topic_all,$remaining_buffer,$result)) {
            $tp_buff = $result;
            $count_topic = count($tp_buff[1]);  # daftar topik
            for ($i = 0; $i < $count_topic; $i++) {
                $title = trim(strip_tags($tp_buff[1][$i]));
                if ($title == '') continue; // 9nov04, skip if topic has no title
                $this->news['topic'][$i]['title'] = $title;  // topic->title
                if (!preg_match_all($this->regex_list_topic_detail,$tp_buff[2][$i],$tpdetail_buff)) {
                    if (!preg_match_all($this->regex_list_topic_detail_basic,$tp_buff[2][$i],$tpdetail_buff))
                        continue;   # skip if can't parse it
                }
                $titles = $tpdetail_buff[2];
                $urls = $tpdetail_buff[1];
                $dates = $urls; //date will be parsed from url
                $count_news = count($tpdetail_buff[1]);
                for ($j = 0; $j < $count_news; $j++) {
                    $this->news['topic'][$i]['news'][$j]['title'] = $titles[$j];  //    topic->title->title
                    $regex_topic_url = '|\?url=(.*)|';
                    if (!preg_match($regex_topic_url,$urls[$j],$urls_res)) {
                        //try apakah ini http biasa
                        $regex_topic_url = '|^http://|';
                        if (preg_match($regex_topic_url,$urls[$j],$urls_res)) $this->news['topic'][$i]['news'][$j]['url'] = $urls[$j];
                    }
                    else {
                        $this->news['topic'][$i]['news'][$j]['url'] = $urls_res[1];
                    }
                    if (preg_match($this->regex_list_headline_date,$dates[$j],$tgl)) {
                        $this->news['topic'][$i]['news'][$j]['date'] = mktime(0,0,0,$tgl[2],$tgl[3],$tgl[1]);
                    }
                    if (trim($this->news['topic'][$i]['news'][$j]['title']) == '') $this->news['topic'][$i]['news'][$j]['title'] = $this->GetAnchorText($this->news['topic'][$i]['news'][$j]['title']);
                }
            }
        }
    }

    function ParseDetail () {
        $this->ParseAds();
        if (preg_match($this->regex_detail,$this->buffer,$result)) {
            $narrow_buffer = $result[1];
        }
        else {
            $narrow_buffer = $this->buffer;
        }
        $success = True;
        /*
        $narrow_buffer = preg_replace('|<style .*?>.*?</style>|is','',$narrow_buffer); # remove "extraneous" fake css
        */
        # <script src="http://jkt1.detiknews.com/index.php/detik.x/tahun/2005/bulan/06/tgl/03/time/184727/idnews/374537/idkanal/10"></script>
        # <script src="http://jkt1.detiknews.com/index.php/detik.chicken/tahun/2005/bulan/06/tgl/07/time/13749/idnews/376381/idkanal/10"></script>
        $content_level_2 = '';
        #~ $regex = '<script src=("|\')([^\1]*?detik\.x[^\1]*?)\1';
        # $regex = '<script src="([^"]*?detik\.chicken[^"]*?)"';
        # if (preg_match('#'.$regex.'#is', $this->buffer, $group)) {  # this is the real content
        # -- thx rudych

        /*
        $narrow_buffer = preg_replace('|<script[^>]*?>document.write\(\'(.*?)\'\)</script>|is','\1',$narrow_buffer); # remove "extraneous" fake javascript
        $narrow_buffer = preg_replace('|<script[^>]*?>\s*?function.*?\'(.*?)\'.*?</script>|is','<font class="textberita">\1</font>',$narrow_buffer); # remove "extraneous" fake javascript
        */

        $narrow_buffer = str_replace('<font color="#F8EEBE">SMSiklan</font>', '', $narrow_buffer);
        $narrow_buffer = preg_replace('#<font class="judultop5">.*?</font>#is', '', $narrow_buffer);

        if (strpos($this->url,'berita-foto') !== False) { // this channel is different enough, that need specific pregmathicng
            #title
            $regex = "|<FONT size=5>(.*?)</font>|is";
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['title'] = $res[1];
            #reporter
            $regex = "|<br/><FONT color=#ff0000 size=2>(.*?)</font>|is";
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['reporter'] = $res[1];
            #content
            $regex = '|<P align="Justify">(.*)|is';
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['content'] = $res[1];
            #'recondition' urls in content
            $this->news['content'] = preg_replace('|<a href=(.?)http://www.detik.com/|',"<a href=\\1{$_SERVER['PHP_SELF']}?url=http://www.detik.com/",$this->news['content']);
        }
        elseif (strpos($this->url,'detikhot') !== False) { // this channel is different enough, that need specific pregmathicng
            #sub-title
            $regex = "|<font class=.?subjudulberita.?>(.*?)</font>|is";
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['subtitle'] = $res[1];
            #title
            $regex = '|<span class="judul">(.*?)</span>.*$|is';
            $this->news['title'] = $_REQUEST['param1'];
            #reporter
            $regex = '|<span class="reporter">(.*?)</span>(.*)<!-- content //-->|is';
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['reporter'] = $res[1];
            #content
            $this->news['content'] = $res[2];
            #clean html
            $this->news['reporter'] = strip_tags($this->news['reporter'],'<b><i>');
            $this->news['content'] = strip_tags($this->news['content'],'<b><i><a><p><br><li><ul>');
        }
        else {
            #sub-title
            /*
            $regex = '|<font class=.?subjudulberita.?>(.*?)</font>|is';
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['subtitle'] = $res[1];
            */

            #title
            while (1) {

                $regex = '|<title>(.*?)-\s*(.{6,}?)</title>(.*)$|is'; # get title from <title>
                if (preg_match($regex,$narrow_buffer,$res)) {$this->news['title'] = $res[2]; $narrow_buffer = $res[3]; break; }

                if ($_REQUEST['param1']) {
                    $this->news['title'] = $_REQUEST['param1'];
                    $regex = '|.*?</title>(.*)$|is';
                    if (preg_match($regex,$narrow_buffer,$res)) { $narrow_buffer = $res[1]; break; }
                }

                $regex = '|<font class=.?judulberita.?>(.{5,150}?)</font>.*$|is';
                if (preg_match($regex,$narrow_buffer,$res)) {$this->news['title'] = $res[1]; break; }

                $regex = '|\.(\S+?)\s*\{[^\}]*?bold 22px[^\}]*?\}|is';
                if (preg_match_all($regex,$narrow_buffer,$groups,PREG_SET_ORDER)) {
                    foreach ($groups as $group) {
                        $regex = '#<font class=("|\')?'.preg_quote($group[1],'#').'\1?.*?>(.{5,}?)</font>#is';
                        if (preg_match($regex,$narrow_buffer,$res)) {$this->news['title'] = $res[2]; break 2; }
                    }
                }

                # fail getting title, assume users still remember the title from the list
                $this->news['title'] = '';
                break;
            }


            #reporter
            while (1) {
                /*
                $regex = '|<font class=.?textreporter.?>(.{10,}?)</font>(.*)|is';
                */
                $regex = '|<font class=.?subjudulberita.?>(.{10,}?)</font>(.*)|is';
                if (preg_match($regex,$narrow_buffer,$res)) {$this->news['reporter'] = $res[1]; break; }

                if ($this->news['title']) {
                    $regex = '|'.preg_quote($this->news['title'],'|').'.*?<font.*?>(.{10,}?)</font>(.*)|is';
                    if (preg_match($regex,$narrow_buffer,$res)) {$this->news['reporter'] = $res[1]; $narrow_buffer = $res[2]; break; }
                }

                # fail
                $this->news['reporter'] = '';
                break;
            }

            #content
            $minchar = 1000;
            $rt = -1;
            while (1) {
                #~ /*
                /*
                if (strpos($this->url,'detikinet') !== False or strpos($this->url,'detiksport') !== False) {    # detikinet and detiksport still uses docuwrite method
                    $narrow_buffer = preg_replace('|<script[^>]*?>document.write\(\'(.*?)\'\)</script>|is','\1',$narrow_buffer);
                    $regex = '#what16\(\)\{\s*\}(.{'.$minchar.',}?)</blockquote>#is';
                    if (preg_match($regex, $narrow_buffer, $group)) { $this->news['content'] = str_replace("\\'", "'",$group[1]); $rt=9; break; }
                }
                */
                // detikinet and detiksport still uses docuwrite method
                if (strpos($this->url,'detikinet') !== False or strpos($this->url,'detiksport') !== False) {
                    $regex = $this->news['title']."(.{".$minchar.",}?)</blockquote>";
                    if (preg_match('#'.$regex.'#is', $narrow_buffer, $group)) { $this->news['content'] = $group[1]; $rt=15; break; }
                }

                // detikpublishing, new commercial news channel
                if (strpos($this->url,'detikpublishing') !== False) {
                    $regex = 'class="copy05">(.{'.$minchar.',}?)</td>';
                    if (preg_match('#'.$regex.'#is', $narrow_buffer, $group)) { $this->news['content'] = $group[1]; $rt=15.1; break; }
                }

                $regex = "theWho='(.*?)';return";
                if (preg_match('#'.$regex.'#is', $narrow_buffer, $group) and strlen($group[1]) > $minchar ) { $this->news['content'] = str_replace("\\'", "'",$group[1]); $rt=7; break; }

                $regex = "theWhat='(.*?)';return";
                if (preg_match('#'.$regex.'#is', $narrow_buffer, $group) and strlen($group[1]) > $minchar ) { $this->news['content'] = str_replace("\\'", "'",$group[1]); $rt=8; break; }

                $regex = "(\S+)='(.*?)';return \\1;";
                if (preg_match('#'.$regex.'#is', $narrow_buffer, $group) and strlen($group[2]) > $minchar ) { $this->news['content'] = str_replace("\\'", "'",$group[2]); $rt=11; break; }
                #~ $regex = "hush='(.*?)';return hush;";
                #~ if (preg_match('#'.$regex.'#is', $this->buffer, $group) and strlen($group[1]) > $minchar ) { $this->news['content'] = str_replace("\\'", "'",$group[1]); $rt=11; break; }

                $regex = "var\s+(\S+)='(.*?)';";
                if (preg_match('#'.$regex.'#is', $narrow_buffer, $group) and strlen($group[2]) > $minchar ) { $this->news['content'] = str_replace("\\'", "'",$group[2]); $rt=12; break; }

                $narrow_buffer = preg_replace('|<script[^>]*?>document.write\(\'(.*?)\'\)</script>|is','\1',$narrow_buffer); # remove "extraneous" fake javascript
                /*
                $regex = "<script[^>]*?>document.write\('(.{".$minchar.",}?)'\)</script>";

                if (preg_match('#'.$regex.'#is',$this->buffer,$group)) { $this->news['content'] = $group[1]; $rt=14; break; }
                */

                /*

                $regex = '<script src="([^"]*?detik\.regex[^"]*?)"';
                #~ preg_match_all('#'.$regex.'#is', $this->buffer, $group);
                #~ foreach ($group[1] as $k => $v) if (strpos($v,'php') !== False) $group[1] = $v;
                if (preg_match('#'.$regex.'#is', $narrow_buffer, $group)) {
                    $content_level_2 = implode('', file($group[1]));
                    $content_level_2 = str_replace("\\'", "#quotejelek#",$content_level_2);
                    if (preg_match("#'([^']{500,})'#is", $content_level_2, $group))
                        $content_level_2 = $group[1];
                    $content_level_2 = str_replace("#quotejelek#", "'",$content_level_2);
                    $this->news['content'] = $content_level_2;
                    $rt=13;
                    break;
                }
                */


                # end of erronous javascript
                $narrow_buffer = preg_replace('|<script.*?>.*?</script>|is','',$narrow_buffer); # remove "extraneous" fake javascript

                $regex = '#relion\.swf.*?>(.{'.$minchar.',}?)<div id="smsblok">#is';
                if (preg_match($regex,$narrow_buffer,$res)) { $this->news['content'] = $res[1]; $rt=1.1; break; }

                $regex = '#relion\.swf.*?>(.{'.$minchar.',}?)</blockquote>#is';
                if (preg_match($regex,$narrow_buffer,$res)) { $this->news['content'] = $res[1]; $rt=1; break; }

                $regex = '/<font class=.?textberita.?>(.{'.$minchar.',}?)(?:<\/font>|$)/is';
                if (preg_match($regex,$narrow_buffer,$res) and strpos($res[1], '</font>') === FALSE) { $this->news['content'] = $res[1]; $rt=2; break; }


                #~ $regex = '/(.{'.$minchar.',}?)(?:<\/font>|$)/is';
                #~ if (preg_match($regex,$narrow_buffer,$res) and strpos($res[1], '</font>') === FALSE) { $this->news['content'] = $res[1]; $rt=3; break; }

                #~ $regex = '/(.{'.$minchar.',}?)(?:<div id="smsblok">|$)/is';
                #~ if (preg_match($regex,$narrow_buffer,$res)) { $this->news['content'] = $res[1]; $rt=4; break; }

                #~ $regex = '#(.{'.$minchar.',}?\([a-zA-Z]{3}\))#is';
                #~ if (preg_match($regex,$narrow_buffer,$res)) { $this->news['content'] = $res[1]; $rt=5; break; }

                #~ $regex = '#(.{'.$minchar.',}?<([^>]+)>\([a-zA-Z]{3}\)</\2>)#is';
                #~ if (preg_match($regex,$narrow_buffer,$res)) { $this->news['content'] = $res[1]; $rt=6; break; }

                #~ */

                $regex = '/(.{'.$minchar.',}?)(?:SMS Iklan|$)/is';
                if (preg_match($regex,$narrow_buffer,$res)) { $this->news['content'] = $res[1]; $rt=20; break; }

                # haven't got the proper regex, just dump everything. at least users can read them.
                #~ $this->news['content'] = nl2br($this->my_strip_html($narrow_buffer));
                $this->news['content'] = nl2br(trim(strip_tags($narrow_buffer)));
                $rt=0;
                $success = False;
                break;
            }

            //clean html
            $this->news['reporter'] = strip_tags($this->news['reporter'],'<b><i>');
            $this->news['content'] = strip_tags($this->news['content'],'<b><i><a><p><li><ul><br>');
            $this->news['content'] = str_replace('\<br \/>','<br/>',$this->news['content']);
            $this->news['rgxid'] = $rt;
            #~ $this->news['content'] = '#'.$rt.'# START_CONTENT'.$this->news['content'].'END_CONTENT';
        }

        //fix relative links
        # <a href="index.php/detik.read/tahun/2005/bulan/06/tgl/01/time/17111/idnews/372954/idkanal/10">
        # http://an.punten.com/detik/index.php?unxdt=&url=http%3A%2F%2Fwww.detikhot.com%2F&param1=Ngintip+%22Ungu+Violet%22+Sebelum+Rilis
        # http://jkt1.detik.com/

        $url_parsed = parse_url($this->url);
        $prefix_url = $_SERVER['PHP_SELF'].'?url=http://'.$url_parsed['host'].':'.$url_parsed['port'].'/';
        $this->news['content'] = preg_replace('#<a href=("|\')(index\.php[^\1]*?)\1(.*?)>#is', '<a href=\1'.$prefix_url.'\2\1\3>', $this->news['content']);


        $this->news['url'] = $this->url;    # save url, will parse later
        # its hard to really parse datetime from url, but easier done from news list, so if newslist provide one, we'll use it
        $this->news['date'] = $this->unxdt;      # unix time, datetime of news detail (passed from param for RSS, from _REQUEST for others)
        return $success;
    }

    function Parse () {
        if ($this->already_parsed) return $this->last_parse_ok;
        if ($this->GetSourceMode() == 'node')
            $ret = $this->ParseNode();
        elseif ($this->GetDataMode() == 'list')
            $ret = $this->ParseList();
        elseif ($this->GetDataMode() == 'detail')
            $ret = $this->ParseDetail();
        else
            die('Unknown parsing state');
        $this->already_parsed = True;
        $this->last_parse_ok = $ret;
        return $ret;
    }
    function ParseAds () {
    /*
    decorate @news with key "ads" containing advertisements data
    */
        global $app;
        if (!$app['ads']) return;
        $regex_ads = '|<a([^>]*)>(.*?)</a>|is';
        if (!preg_match_all($regex_ads,$this->buffer,$ads_res,PREG_SET_ORDER)) {   // get all ad links
            return;
        }
        $this->news['ads'] = array();   # reset ads first
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
            $this->news['ads'][] = $temp;
        }
    }
    function Render () {
        if ($this->GetDataMode() == 'detail') {
            return $this->RenderDetailNormal();
        }
        elseif ($this->GetDataMode() == 'list') {
            if ($this->GetSourceMode() == 'node')
                return $this->RenderNode();
            $target = $_REQUEST['no'] == 'frame'? '': 'm';
            return $this->RenderListNormal($target);
        }
    }
    function RenderDetailNormal ($complete=True,$with_header=True,$strip_ads=False) {
        /* renderer for news detail/body */
        global $url,$app;
        $content = preg_replace('|<B>(.*?)<P>|is','<span style=font-size:larger><B>\\1</span><P>',$this->news['content']);    //specialized first paragraph
        $content = preg_replace('|<a href=("?)http://www.detik.com|is',"<a href=\\1{$_SERVER['PHP_SELF']}?url=http://www.detik.com",$content);  //fix url berita terkait
        #~ if ($with_header) echo HtmlHeader();

        if ($complete) {
            echo '<div id="content-detail">';
            if ($this->news['date'] != '')
                echo '<p class="date">'.date('Y-m-d H:i:s',$this->news['date']).'</p>';
            echo '<h2>'.$this->news['subtitle'].' '.$this->news['title'].'</h2>';
            echo '<p class="author">'.$this->news['reporter'].'</p>';
            if (!$strip_ads) $this->RenderAds();
        }
        echo '<div id="body"><p>'.$content.'</p></div>';
        echo '<div id="endnote">';
        echo '<p><a href="'.$this->news['url'].'"">'.$this->news['url'].'</a></p>';
        if ($this->footer_info != '') echo '<p>'.$this->footer_info.'</p>';
        if ($this->from_cache) echo '<ul><li>Berita dari cache '.str_time_delta($this->news['cache']).' lalu </li><li><a href="'.$_SERVER['PHP_SELF'].'?unxdt='.$_REQUEST['unxdt'].'&amp;url='.$this->news['url'].'&cache_reload=1&param1='.urlencode($_REQUEST['param1']).'">Cek Ulang</a> </li></ul>';
        echo '</div>';
        #~ if ($with_header) {
            #~ echo '</div>';  # content-main
            #~ $footsy = array();
            #~ $footsy[] = 'rgxid '.$this->news['rgxid'];      # show which regex parse this detail (for easy fixing)
            #~ echo show_footer($this->news['url'],$footsy);
        #~ }
    }
    function RenderNode ($uncompressed=0) {
    /*
    @uncompressed: set this to 1 if client requested uncompressed stream
    support list and detail
    */
        set_magic_quotes_runtime(0); //to avoid null char be converted to \0
        $news_serial = serialize($this->news);
        if (!$this->zlib_support or $uncompressed) echo $news_serial;
        else echo gzcompress($news_serial);
    }

    function RenderDetailRss () {
        return $this->RenderDetailNormal(True,False,True);
    }
    function CaptureStart () {
        ob_end_clean();
        ob_start();
    }
    function CaptureEnd () {
        $ret = ob_get_contents();
        ob_end_clean();
        return $ret;
    }
    function RenderDetailWap () {
        header('Content-Type: text/vnd.wap.wml');
        $content = preg_replace('|<B>(.*?)<P>|is','<span style=font-size:larger><B>\\1</span><P>',$this->news['content']);    //specialized first paragraph
        $content = preg_replace('|<a href=("?)http://www.detik.com|is',"<a href=\\1{$_SERVER['PHP_SELF']}?url=http://www.detik.com",$content);  //fix url berita terkait
        echo '<?xml version="1.0"?><!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml"><wml><head></head><card id="main" title="'.htmlentities(strip_tags($this->news['subtitle'].' '.$this->news['title'])).'">';
        echo '<p>'.htmlentities(strip_tags($this->news['content'])).'</p>';
        echo '</card></wml>';
    }
    function RenderListRssComplete () {
        return $this->RenderListRss(True);
    }
    function RenderListRss ($with_content=False) {
    /* news list renderer for RSS
    @with_content  True to include complete news body inside RSS
    */
        global $app,$author_name,$author_email;
        header('Content-Type: text/xml');
        echo '<?xml version="1.0"?><rss version="2.0"><channel><title>Detik.Usable: berita cepat</title><link>'.($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'</link><description>'.htmlentities($app['version']).'</description><language>id</language><category>news</category><managingEditor>'.$author_email.'</managingEditor><webMaster>'.$author_email.'</webMaster><lastBuildDate>'.date('r').'</lastBuildDate><generator>'.$app['name'].' v'.$app['version'].'</generator>';
        foreach ($this->news['headline'] as $headline) {  // view headlines
            $url = ($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?url='.urlencode($headline['url']);
            if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
            if ($with_content) {
                $du = new DetikUsable($headline['url']); $du->SetModeDetail(); $du->SetDetailDate($headline['date']); $du->GetBuffer(); $du->Parse(); $du->CaptureStart(); $du->RenderDetailRss();
                $description = htmlentities($du->CaptureEnd());
            }
            else $description = $headline['summary'];
            echo '<item><title>'.htmlentities(strip_tags($headline['subtitle'].$headline['title'])).'</title><link>'.htmlentities($url).'</link><description>'.htmlentities($description).'</description><guid>'.htmlentities($headline['url']).'</guid><pubDate>'.date('r',$headline['date']).'</pubDate><category>Headlines</category></item>';
        }
        foreach ($this->news['prevnews'] as $headline) {  //  view prevnews
            $url = ($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?url='.urlencode($headline['url']);
            if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
            if ($with_content) {
                $du = new DetikUsable($headline['url']); $du->SetModeDetail(); $du->SetDetailDate($headline['date']); $du->GetBuffer(); $du->Parse(); $du->CaptureStart(); $du->RenderDetailRss();
                $description = $du->CaptureEnd();
            }
            else $description = '';
            echo '<item><title>'.htmlentities(strip_tags($headline['subtitle'].$headline['title'])).'</title><link>'.$url.'</link><description>'.htmlentities($description).'</description><guid>'.htmlentities($headline['url']).'</guid><pubDate>'.date ('r',$headline['date']).'</pubDate><category>Previous News</category></item>';
        }
        foreach ($this->news['topic'] as $topic) {    //  view topic news
            foreach ($topic['news'] as $headline) {
                $url = ($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?url='.urlencode($headline['url']);
                if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
                if ($with_content) {
                    $du = new DetikUsable($headline['url']); $du->SetModeDetail(); $du->SetDetailDate($headline['date']); $du->GetBuffer(); $du->Parse(); $du->CaptureStart(); $du->RenderDetailRss();
                    $description = $du->CaptureEnd();
                }
                else $description = '';
                echo '<item><title>'.htmlentities(strip_tags($headline['subtitle'].$headline['title'])).'</title><link>'.htmlentities($url).'</link><description>'.htmlentities($description).'</description><guid>'.htmlentities($headline['url']).'</guid><pubDate>'.date('r',$headline['date']).'</pubDate><category>'.$topic['title'].'</category></item>';
            }
        }
        echo '</channel></rss>';
        return $list;
    }
    function RenderListWap () {
        header('Content-Type: text/vnd.wap.wml');
        echo '<?xml version="1.0"?><wml><head></head><card id="main" title="detik.usable">';
        foreach ($this->news['headline'] as $headline) {  // view headlines
            $url = ($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?wap=1&amp;url='.urlencode($headline['url']);
            if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
            $description = htmlentities($headline['summary']);
            $date_delta = str_time_delta($headline['date'],TRUE);
            echo '<p><anchor>'.$date_delta.': '.htmlentities(strip_tags($headline['subtitle'].$headline['title'])).'<go href="'.$url.'"></go></anchor></p>';
        }
        foreach ($this->news['prevnews'] as $headline) {  //  view prevnews
            $url = ($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?wap=1&amp;url='.urlencode($headline['url']);
            if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
            echo '<p><anchor>'.htmlentities(strip_tags($headline['subtitle'].$headline['title'])).'<go href="'.$url.'"></go></anchor></p>';
        }
        echo '</card></wml>';
    }
    function RenderListNormal ($frame_target='') {
        global $author_email, $tz_diff;
        if (0 and $this->news['cache']) {
            echo '<p>list dari cache '.str_time_delta($this->news['cache'],FALSE,FALSE);
            if ((time() - $this->news['cache'])>$this->newslist_cache_revalidate) {
                if ($_REQUEST['pda']) echo '(press reload button for realtime update)';
                else echo '. Auto-loading berita baru di background.';
            }
            echo '</p>';
        }

        echo '<div id="main">';

        echo '<div id="content-headlines">';
        if ($this->news['headline']) {
            foreach ($this->news['headline'] as $headline) {  // view headlines
                $headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&url='.urlencode($headline['url']);
                if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
                $date = date('H:i',$headline['date']);
                $date_delta = str_time_delta($headline['date']);
                $alt = 3;
                # time below title, slashdot style
                echo '<div class="block">';
#~ echo '<p class="date">'.$date.', '.$date_delta.'</p>';
                echo '<h3><a href="'.$headline['url'].'">'.strip_tags($headline['subtitle'].$headline['title']).'</a> </h3>';
                #~ echo '<h3><a href="'.$headline['url'].'" onClick="getit();return false;">'.strip_tags($headline['subtitle'].$headline['title']).'</a> </h3>';
                #~ echo '<h3><a href="#" onClick="getit(\'http://localhost'.$headline['url'].'\');return false;">'.strip_tags($headline['subtitle'].$headline['title']).'</a> </h3>';

                echo '<p>'.$headline['summary'].'</p>';
echo '<p class="date">'.$date_delta.' - '.$date.'</p>';
                echo '</div>'; # block
            }
        }
        else {
            echo '<p class="error">Sorry, top headline news are not available. If this persists, <a href="mailto:'.$author_email.'">contact author</a>.</p>';
        }
        echo '</div>'; #content-headlines

        echo '<div id="content-oldernews">';
        if ($this->news['prevnews']) {
            foreach ($this->news['prevnews'] as $headline) {  //  view prevnews
                $headline['url_orig'] = $headline['url'];
                $headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&url='.urlencode($headline['url']);
                if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
                $date = date('H:i',$headline['date']);
                $title_string = strip_tags($headline['subtitle'].$headline['title']);
                echo '<div class="block">';
                #~ echo '<h3><a href="'.$headline['url'].'">'.$title_string.'</a></h3>';
                echo '<h3><a href="#" onClick="getit(\''.$headline['url'].'\');return false;">'.$title_string.'</a></h3>';
                echo '<p class="date">'.$date.'</p>';
                echo '</div>'; # block

            }
        }
        else {
            echo '<p class="error">Sorry, previous headline news are not available. If this persists, <a href="mailto:'.$author_email.'">contact author</a>.</p>';
        }
        echo '</div>';  #content-oldernews

        echo '</div>'; #main

        echo '<div id="secondary">';

        echo '<div id="content-channels">';
        if ($this->news['topic']) {
            foreach ($this->news['topic'] as $topic) {    //  view topic news
                echo '<div class="block">';
                echo '<h2>'.$topic['title'].'</h2>';
                if ($topic['news']) {
                    echo '<ul>';
                    foreach ($topic['news'] as $headline) {
                        $headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&url='.urlencode($headline['url']).'&param1='.urlencode(strip_tags($headline['title']));
                        if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
                        echo '<li><a href="'.$headline['url'].'">'.$headline['subtitle'].$headline['title'].'</a></li>';
                    }
                    echo '</ul>';
                }
                else {
                    echo '<p class="error">Sorry, this part of news are not available. If this persists, <a href="mailto:'.$author_email.'">contact author</a>.</p>';
                }
                echo '</div>';  #block
            }
        }
        else {
            echo '<p class="error">Sorry, topical news are not available. If this persists, <a href="mailto:'.$author_email.'">contact author</a>.</p>';
        }
        echo '</div>';  #content-channels

        $this->RenderAds();

        echo '</div>';  #secondary;
    }
    function RenderAds () {
        global $app;
        $maxchar = 40;
        if ($app['ads'] and isset($this->news['ads']) and is_array($this->news['ads']) and count($this->news['ads'])>0 ) {
            echo '<div id="ads">';
            echo '<h2>Iklan</h2>';
            echo '<ul>';
            foreach ($this->news['ads'] as $ads) {
                $url = $ads['url'];
                $desc = $ads['name'];
                if (strlen($desc)>$maxchar) $desc = substr($desc,0,$maxchar);
                if ($desc == '') $desc = 'Iklan';
                echo '<li><a href="'.$url.'">'.$desc.'</a> .. </li>';
            }
            echo '</ul>';
            echo '</div>';
        }
    }
    function ParseAnchor () {
    /* We Got New Intelligent Algorithm to parse New list more reliable!
    get dictionary of <a>...</a> links
    */
        if ($this->GetSourceMode() != 'orig') return False; # we expect buffer to be filled with original HTML version
        $this->anchors = array();   # reset anchors
        $source_url = parse_url($this->url);
        $regex = "/<a\s+href=[\"']?([^\" >]*).*?>(.*?)<\/a>(.*?)(?=<a|$)/is";
        if (preg_match_all($regex,$this->buffer,$groups,PREG_SET_ORDER)) {
            foreach ($groups as $group) {
                # we deal only with absolute href
                $href = $group[1];
                if (!stristr($href,'http://')) {
                    # if href does not begin with '/', complete with fixpathnonslash
                    if (substr($href,0,1) != '/') $href = $fixpathnonslash.$href;
                    else $href = 'http://'.$source_url['host'].':'.$source_url['port'].$href;   //href begin with '/', absolutize with hostname
                }
                $this->anchors[] = array('href'=>$href, 'text'=>$group[2], 'data'=>$group[3]);
            }
        }
        return True;
    }
    function GetAnchorText ($url) {
        if (!$this->anchors) return '&lt;anchor has not been searched&gt;';
        foreach ($this->anchors as $a) {
            if (strstr($a['href'], $url) and trim(strip_tags($a['text'])) != '')  { # find in anchors
                return $a['text'];
            }
        }
        return '&lt;unknown title&gt;';    # give a default not-found value
    }

    function GetPageForCookieCode ($url) {
        $url_parsed = parse_url($this->url);
        $sock = new DuSock($url_parsed['host'],$url_parsed['port']);
        if (!$sock->socket_open()) {
            die('Unable to create connection to ['.$sock->host.']:['.$sock->port.']');
        }
        # first visit the ad decorator site to get the cookie
        $sock->sock_send_request($url);
        $header_buffer = $sock->sock_recv_header();
        #~ echo $header_buffer;
        # parse for cookie header
        if (!preg_match_all('|^set-cookie:\s*(.+?);|im',$header_buffer,$result)) {
            # if no cookie available, then dtkcom has disabled cookie checking
            $this->footer_info = 'ncn'; #no cookie needed - disable this for this url/channel to increase speed
            return '';
        }
        return implode(';',$result[1]);
        #~ $sock->referer = 'http://jkt1.detiknews.com/indexfr.php?url='.$this->url;
    }
}
// end libdetikusable
?>