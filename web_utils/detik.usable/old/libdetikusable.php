<?
class Logify {
    var $log = array();
    function add ($line_number,$string) {
        $this->log[] = array($line_number,$string);
    }
    function write_error ($title,$description='<p>This is an expected error. Check your configuration and internet connection</p>') {
        global $error; $error = TRUE;
        echo '<div style="border:thin solid #ffaaaa;background-color:#ffcccc;margin:10;text-align:center;"><h3>'.$title.'</h3>'.$description.'</div>';
        echo '<h4>Log history</h4>';
        echo $this->dump();
        echo '<h4>Debug traceback</h4>';
        $this->print_debug_backtrace();
        return;
    }
    function dump () {
        echo '<pre>';
        foreach ($this->log as $temp_arr) echo '#'.$temp_arr[0].': '.htmlspecialchars($temp_arr[1])."\r\n";
        echo '</pre>';
    }
    function print_debug_backtrace () {
		if (PHP_VERSION >= 4.3) {
			$MAXSTRLEN = 128;
			echo '<pre>';
			$traceArr = debug_backtrace();
			array_shift($traceArr);
			$tabs = sizeof($traceArr)-1;
			foreach ($traceArr as $arr) {
				$args = array();
				for ($i=0; $i < $tabs; $i++) $s .= ' &nbsp; ';
				$tabs -= 1;
				if (isset($arr['class'])) $s .= $arr['class'].'.';
				if (isset($arr['args']))
				 foreach($arr['args'] as $v) {
					if (is_null($v)) $args[] = 'null';
					else if (is_array($v)) $args[] = 'Array['.sizeof($v).']';
					else if (is_object($v)) $args[] = 'Object:'.get_class($v);
					else if (is_bool($v)) $args[] = $v ? 'true' : 'false';
					else {
						$v = (string) @$v;
						$str = htmlspecialchars(substr($v,0,$MAXSTRLEN));
						if (strlen($v) > $MAXSTRLEN) $str .= '...';
						$args[] = $str;
					}
				}
				echo '<b>'.$arr['function']."</b>\t".'('.implode(', ',$args).')';
				echo @sprintf('<font color="#808080" size="-1"> # line %4d, file: <a href="file:/%s">%s</a></font>', $arr['line'],$arr['file'],$arr['file']);
				echo  "\r\n";
			}
			echo  '</pre>';
		}

    }

}
$log = new Logify();

function assert_callback( $script, $line, $message ) {
    global $author_email,$log;
    echo $log->write_error('Assertion error at line# '.$line, '<p>Send this whole page to <a href="mailto:'.$author_email.'">author</a> to help improve the next version.</p>');
    exit;
}
assert_options(ASSERT_CALLBACK,assert_callback);

#~ function write_error ($string) {
    #~ global $error;
    #~ echo '<p><font color=red><b>ERROR:</b><!--begin-->'.$string.'<!--end--></font>';
    #~ echo '<div style="border:thin solid #ffaaaa;background-color:#ffcccc;margin:10;text-align:center;">';
    #~ echo '<h3>'.$string.'</h3>';
    #~ echo '</div>';
    #~ echo '<h4>Trace dump</h4>';
    #~ echo $log->dump();
    #~ $error = TRUE;
    #~ return;
#~ }

function str_time_delta ($date_c,$compressed=FALSE) {
    global $ctime;
    $delta = $ctime - $date_c;
    if ($delta < 60) {
        $satuan = $compressed? 'dtk':' detik';
        $tgl = $delta;
    }
    elseif ($delta < 3600) {
        $satuan = $compressed? 'mnt':' menit';
        $tgl = floor($delta/60);
    }
    elseif ($delta < 86400) {
        $satuan = $compressed? 'j':' jam';
        $tgl = sprintf('%01.1f',$delta/3600);
    }
    else {
        $hari = floor($delta/86400);
        $jam = floor(($delta - $hari*86400) /3600);
        if ($jam == 0)
            return $hari.($compressed? 'hr':' hari');
        else
            return $hari.($compressed? 'hr':' hari').' '.$jam.($compressed? 'j':' jam');
    }
    return $tgl.$satuan;
}


class MySocket {
    var $user_agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1) ';
    function MySocket($host,$port=80) {
        global $app,$log;
        $this->host = $host;
        $this->port = $port == ''? 80: $port;
        $this->timeout = 30; //seconds
        $this->error_cant_open = '';
        $this->http_proxy_enable = $app['http_proxy']['enable'];
        $this->http_proxy_host = $app['http_proxy']['hostname'];
        $this->http_proxy_port = $app['http_proxy']['port'];
        $this->http_proxy_user = $app['http_proxy']['user'];
        $this->http_proxy_pass = $app['http_proxy']['pass'];
        assert($this->host != '');
        $this->log = &$log;
    }
    function socket_open () {
        if ($this->http_proxy_enable) {
            $this->log->add(__LINE__, "sock_open, {$this->http_proxy_host}/{$this->http_proxy_port}, {$this->timeout} sec timeout");
            $this->fp = @fsockopen ($this->http_proxy_host,$this->http_proxy_port,$errno,$errstr,$this->timeout);
        }
        else {
            $this->log->add(__LINE__, "sock_open, [{$this->host}]/[{$this->port}], {$this->timeout} sec timeout");
            $this->fp = @fsockopen ($this->host,$this->port,$errno,$errstr,$this->timeout);
        }
        if (!$this->fp) {
            $this->log->add(__LINE__, 'sock_open: can\'t connect');
            #~ assert($errno != 0);
            if ($errno == 0) {
                $this->log->add(__LINE__, 'sock_open: problem before connect (dns/socket)');
                #~ $this->log->dump();
            }
            else {
                $this->log->add(__LINE__, 'sock_open: problem trying to connect (hostname notfound, blocked, downed, busy, or timeout)');
                $this->log->write_error("$errno \"$errstr\".");
            }
            return FALSE;
        }
        else {
            $this->log->add(__LINE__, 'sock_open: connected');
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
        $this->log->add(__LINE__, 'http_req: '.$http_req);
        $return = fputs ($this->fp, $http_req);
        if ($return == -1) {
            $this->log->add(__LINE__, 'http_req: can\'t send');
            return FALSE;
        }
        else {
            $this->log->add(__LINE__, 'http_req: sent');
            return TRUE;
        }
    }
    function sock_recv_header () {  //return HTTP response header
        $buffers = '';
        $this->log->add(__LINE__, 'http_resp_header: receiving...');
        while (!feof ($this->fp)) {
            $buffer = fgets($this->fp, 65536 );
            if ($buffer == "\r\n") break;
            $buffers .= $buffer;
        }
        $this->log->add(__LINE__, 'http_resp_header: '.$buffers);
        if (!preg_match('/200 OK/',$buffers)) {  //validate buffer
            $this->log->write_error('Invalid HTTP Response');
            return false;
        }
        else {
            $this->log->add(__LINE__, 'http_resp_header: 200 OK');
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

class DetikUsable {
    var $nodeserver_url = '';
    var $source = '';
    var $already_parsed = False;
    var $proxy_url = '';
    var $url_list = 'http://jkt1.detik.com/index.php';   # note: if you got "access forbidden" error and your hosting server is located outside indonesia, use "jkt2.detik.com" instead
    #~ var $url_list = 'http://localhost/index.php';
    var $newslist_cache_revalidate = 300; // time in seconds in which news list become invalidated, and automatically reload fresh data from original source
    # -------- <REGEX> --------
    var $regex_list_prevnews = "|=.nmkanal(.*?)<IMG(.*)|s";
    //parser utk tanggal+jam, hyperlink, dan judul
    #~ $regex_list_prevnews_all = "/(\d+\/\d+\/\d+.*?) WIB.*?<A href=\"([^\"]*)\"[^>]*>(.*?)<\/A>/is";
    #~ $regex_list_prevnews_all = '/(\d+\/\d+\/\d+.*?)<.*?<a href="([^"]+)"[^>]*>(.*?)<\/A>/is';
    #~ $regex_list_prevnews_all = '/(\d+\/\d+\/\d+.*?)<.*?<a href="([^"]+url=[^"]+)" class=[^>]*>(.*?)<\/A>/is';
    var $regex_list_prevnews_all = '|(\d+/\d+/\d+.*?)<.*?<a href="([^"]+url=[^"]+)" class=[^>]*>(.*?<span class="nonhlJudul">.*?)</A>|is';
    #~ var $regex_list_prevnews_subtitle = '/nonhlsubJudul.>(.{5,}?)<\/span>/';
    var $regex_list_prevnews_subtitle = '|><span class=.nonhlSubJudul.>(.+?)</span>|';
    var $regex_list_prevnews_title = '|nonhlJudul.>(.*)|';
    var $regex_list_headline = '|(<span class="tanggal">.*?)</td>\s+<td valign="top"(.*)|is';
    #~ $regex_list_headline_all = '|tanggal.>[^,]*,(.*?) WIB<.*?<A href="([^"]+)".*?parent.>(.*?<span class="summary">.*?</span>)|is';
    var $regex_list_headline_all = '|tanggal.>[^,]*,(.*?) WIB<.*?<A href="([^"]+url=[^"]+)" class=[^>]*>(.*?<span class="summary">.*?</span>)|is';
    var $regex_list_headline_subtitle = '|subjudul.>(.*?)</span|is';
    var $regex_list_headline_title = '|strJudul.>(.+?)</span|is';
    var $regex_list_headline_summary = '|summary.>(.*?)</span|s';
    var $regex_list_topic_all = '|<td width="100%" align="left" colspan="2">(.*?)</tr>(.*?)<table|si';
    #~ $regex_list_topic_detail = '|90%">(.*?)<a.*?</a><a href="([^"]+)"[^>]*>.*?"judulhlbawah">(.*?)</font>|is';
    var $regex_list_topic_detail = '|90%">(.*?)<a href="([^"]+tahun/[^"]+)"[^>]*>.*?"judulhlbawah">(.+?)</font>|is';
    // http://www.detiknews.com/index.php/detik.read/tahun/2004/bulan/04/tgl/15/time/1298/idnews/127625/idkanal/10
    // http://jkt1.detiksport.com/index.php/detik.read/tahun/2004/bulan/10/tgl/27/time/715/idnews/231219/idkanal/75
    #~ $regex_list_headline_date = '|/tahun/(\d*)/bulan/(\d*)/tgl/(\d*)/time/(\d\d)(\d\d)|i';
    var $regex_list_headline_date = '|/tahun/(\d*)/bulan/(\d*)/tgl/(\d*)/|i';   # skip the time. too unpredictable.
    var $regex_detail = "|<blockquote>(.*?)<!-- FORM|is";
    # -------- <REGEX> --------

    function DetikUsable ($url='') {
        global $app,$log;
        $this->log = &$log;
        $this->SetSourceFast();
        $this->SetUrl($url);
        $this->proxy_url = $app['proxy_url'];
        $this->zlib_support = extension_loaded('zlib');
        $this->enable_cache = $app['cache'];
        $this->unxdt = $_REQUEST['unxdt'];
    }

    function GetSourceMode () {
        return $this->source;
    }
    function SetSourceNode ($url='') {
        $this->log->add(__LINE__, 'set source node');
        if ($url != '') $this->url = $url;
        $this->source = 'node';
        return $this;
    }
    function SetSourceCache ($url='') {
        $this->log->add(__LINE__, 'set source cache');
        if ($url != '') $this->url = $url;
        $this->source = 'cache';
        return $this;
    }

    function SetSourceOrig ($url='') {
        $this->log->add(__LINE__, 'set source original');
        if ($url != '') $this->url = $url;
        $this->source = 'orig';
        return $this;
    }

    function SetSourceFast ($url='') {
        $this->log->add(__LINE__, 'set source fast');
        if ($url != '') $this->url = $url;
        $this->source = 'fast';
        return $this;
    }

    function SetUrl ($url) {
        $this->url = $url;
        return $this;
    }
    function SetNodeServerUrl ($url) {
        $this->nodeserver_url = $url;
        return $this;
    }

    function GetDataMode () {
        return $this->data_mode;
    }
    function SetModeDetail () {
        $this->data_mode = 'detail';
        return $this;
    }

    function SetModeList () {
        $this->data_mode = 'list';
        $this->url = $this->url_list;
        return $this;
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
        $this->log->add(__LINE__, 'getting from node...');
        $sock = new MySocket($url_parsed['host'],$url_parsed['port']!=''? $url_parsed['port'] : 80);
        if (!$sock->socket_open()) die('Cannot contact repository at '.$this->proxy_url);
        $sock->sock_send_request($url_parsed['path'].'?'.$url_parsed['query']);
        $sock->sock_recv_header();
        $buffer = $sock->sock_recv_all();
        if ($buffer=='') { $this->log->write_error('getter_node: unable to download node data'); exit; }
        $this->buffer = &$buffer;
        return $this;
    }

    function GetBufferCache($force_reload=False,$although_expired=False) {
    /*
    @force_reload:
        False = take from cache if not expired (if any, and app configured to cache)
        True = always take from original
    @although_expired: False=invalidate cache if expired, True=use cache although expired
    */
        if (!$this->enable_cache) return $this;
        if ($this->GetDataMode() == 'list') $this->url = $this->url_list;
        elseif ($this->GetDataMode() == 'detail') $although_expired = True; # detail is considered stable
        $this->log->add(__LINE__, 'getting from cache...');
        $urls = parse_url($this->url);
        $filename = 'cache/'.md5($urls['path']);
        if (!file_exists($filename) or $force_reload) {         # check if already in cache
            $this->log->add(__LINE__, 'cache not exist or forced to reload');
            $this->SetGetBufferOK(False);
            return $this;
        }
        $buffer = '';
        $fp = fopen($filename,'r');
        while(!feof($fp)) $buffer .= fread($fp,1024);
        fclose($fp);
        $this->news = unserialize($buffer);
        if (!$although_expired and (time() - $this->news['cache']) > $this->newslist_cache_revalidate ) {$this->log->add(__LINE__, 'cache expired');$this->SetGetBufferOK(False);return;} # check whether cache is expired
        $this->already_parsed = True;   # cache buffer already contain parsed news
        $this->SetGetBufferOK(True);
        $this->log->add(__LINE__, 'got from cache...');
        return $this;
    }

    function SaveCache () {
    /* save serialized array to file
    */
        if (!$this->enable_cache or !$this->news) return $this;
        if (!file_exists('cache')) mkdir('cache',0755);
        $urls = parse_url($this->url);
        $filename = 'cache/'.md5($urls['path']);
        $this->news['cache'] = time();    # record the time of cache last updated
        $buffer = serialize($this->news);
        unset($this->news['cache']);      # remove back our modification
        $fp = fopen($filename,'w');
        fwrite($fp,$buffer);
        fclose($fp);
        return $this;
    }

    function GetBufferOrig () {
        if ($this->GetDataMode() == 'list') $this->url = &$this->url_list;
        elseif ($this->GetDataMode() == 'detail') {
            assert($this->url != '');
        }
        $url_parsed = parse_url($this->url);
        $this->log->add(__LINE__, 'getting from original');
        $sock = new MySocket($url_parsed['host'],$url_parsed['port']);
        if (!$sock->socket_open()) {
            $this->log->write_error('Unable to create connection to ['.$sock->host.']:['.$sock->port.']');
            exit;
        }
        $sock->sock_send_request($url_parsed['path'].$url_parsed['query']);
        $header_buffer = $sock->sock_recv_header();
        $this->buffer = $sock->sock_recv_all();
        if (preg_match('|Content-Encoding:\s*gzip|i',$header_buffer)) { // if Content-Encoding: gzip, then body is gzipped. Unzipped first.
            $this->buffer = gzinflate(substr($this->buffer, 10,-4));  //skip the first 10 characters,as they are GZIP header, and php's gzinflate only need the data
        }
        $this->log->add(__LINE__, 'got from original');
        $this->ParseAnchor();
        $this->Parse(); $this->SaveCache();
        return $this;
    }

    function IsGetBufferOK () {
        return $this->getbuffer_ok;
    }

    function SetGetBufferOK ($ok=True) {
        assert(is_bool($ok));
        $this->getbuffer_ok = $ok;
        return $this;
    }

    function GetBuffer () {
        if ($this->GetSourceMode() == 'node')
            return $this->GetBufferNode();
        elseif ($this->GetSourceMode() == 'orig')
            return $this->GetBufferOrig();
        elseif ($this->GetSourceMode() == 'cache')
            return $this->GetBufferCache();
        elseif ($this->GetSourceMode() == 'fast') {
            # try fastest source first
            $this->GetBufferCache();
            if ($this->IsGetBufferOK()) return $this;
            if ($this->nodeserver_url != '') {
                $this->GetBufferNode();
                if ($this->IsGetBufferOK()) return $this;
            }
            return $this->GetBufferOrig();
        }
        return $this;
    }

    function ParseNode () {
    /* return $news array from node buffer. support both list and detail.
    @buffer: reference to buffer taken from other node to be parsed
    */
        if ($this->zlib_support) {
            $buffer2 = @gzuncompress($buffer);   // try to uncompress
            if (!$buffer2 or is_bool($buffer2)) {    //it's probably not gzcompressed
                $this->log->add(__LINE__, 'newslist: from node: cant uncompress data');
            }
            else {
                $buffer = &$buffer2;  // change to new buffer
                $stream_compress = TRUE;
            }
        }
        $news = unserialize($buffer);
        if (!$news) {
            $this->log->write_error('newslist: from node: Unable to unserialize data'); exit;
        }
        if (!is_array($news)) {
            $this->log->write_error("newslist: from node: Data was not formatted correctly"); exit;
        }
        # todo: write news to cache
        $this->news = &$news;
        $this->already_parsed = True;
        return $this;
    }

    function ParseList () {
        $this->ParseAds();
        # narrowing-in to "prevnews" content
        if (preg_match($this->regex_list_prevnews,$this->buffer,$result)) {
            $narrow_buffer = $result[1];
            $remaining_buffer = $result[2];
            $this->log->add(__LINE__, "ParseList: prevnews1: ok");
        }
        else {
            $this->log->add(__LINE__, "ParseList: prevnews1: fail");
            $narrow_buffer = $this->buffer;   # continue anyway
        }
        if (preg_match_all($this->regex_list_prevnews_all,$narrow_buffer,$result)) {
            unset($narrow_buffer);
            $this->log->add(__LINE__, "ParseList: prevnews2: ok");
            $total_prev_news = count($result[2]);
            for ($i = 0; $i < $total_prev_news; $i++) {
                $url = $result[2][$i];
                $date = $result[1][$i];
                $title_temp = $result[3][$i];   # contain title and possibily subtitle
                //    prevnews->date
                $date = preg_replace('/([0-9]*)\/([0-9]*)\//','\\2/\\1/', $date);
                $this->news['prevnews'][$i]['date'] = strtotime($date);
                //    prevnews->url
                if (!preg_match('/http:\/\//',$url)) {   //  makeit absolute url
                    $this->log->add(__LINE__, "ParseList: prevnews: add absolute url");
                    $url = 'http://www.detik.com'.$url;
                }
                if (preg_match('/\?url=(.*)/',$url,$url_res)) {  // if link formatted like ...?url=http://.... retrieve the param value instead
                    $this->log->add(__LINE__, "ParseList: prevnews: url from param");
                    $url = $url_res[1];
                }
                $this->news['prevnews'][$i]['url'] = $url;
                //    prevnews->subtitle
                $this->log->add(__LINE__, "parser: prevnews($i): tt".$title_temp);
                if (preg_match($this->regex_list_prevnews_subtitle,$title_temp,$subtitle_res)) {
                    $this->log->add(__LINE__, "ParseList: prevnews#$i: subtitled");
                    $this->news['prevnews'][$i]['subtitle'] = $subtitle_res[1];
                }
                //    prevnews->title
                if (preg_match($this->regex_list_prevnews_title,$title_temp,$title_res)) {
                    $this->log->add(__LINE__, "ParseList: prevnews#$i: titled");
                    $this->news['prevnews'][$i]['title'] = $title_res[1];
                }
                if (trim($this->news['prevnews'][$i]['title']) == '') $this->news['prevnews'][$i]['title'] = $this->GetAnchorText($this->news['prevnews'][$i]['url']);
            }
        }

        //    narrowing-in to headline news content
        $this->log->add(__LINE__, "ParseList: headline");
        if (preg_match($this->regex_list_headline,$remaining_buffer,$result)) {
            $this->log->add(__LINE__, 'parser: headline: success');
            $narrow_buffer = $result[1];
            $remaining_buffer = $result[2];
            assert(preg_match_all($this->regex_list_headline_all,$narrow_buffer,$result));
            unset($narrow_buffer);
            $this->log->add(__LINE__, 'parser: headline: all: success');
            $total_news = count($result[2]);
            for ($i = 0; $i < $total_news; $i++) {
                $date = $result[1][$i];
                $url = $result[2][$i];
                $title = $result[3][$i];
                //    headline->url
                if (!preg_match('/http:\/\//',$url)) { //        makeit absolute url
                    $url = 'http://www.detik.com'.$url;
                }
                if (preg_match('/\?url=(.*)/',$url,$url_res)) { // if link formatted like ...?url=http://.... retrieve the param value instead
                    $url = $url_res[1];
                }
                $this->news['headline'][$i]['url'] = $url;
                //    headline->subtitle
                if (preg_match($this->regex_list_headline_subtitle,$title,$subtitle_res)) {
                    $this->news['headline'][$i]['subtitle'] = $subtitle_res[1];
                }
                //    headline->title
                if (preg_match($this->regex_list_headline_title,$title,$title_res)) {
                    $this->news['headline'][$i]['title'] = $title_res[1];
                }
                if (trim($this->news['headline'][$i]['title']) == '') $this->news['headline'][$i]['title'] = $this->GetAnchorText($this->news['headline'][$i]['url']);
                //    headline->summary
                if (preg_match($this->regex_list_headline_summary,$title,$summary_res)) {
                    $this->news['headline'][$i]['summary'] = $summary_res[1];
                }
                //    headline->date
                $date = preg_replace('|([0-9]*)/([0-9]*)/|','\\2/\\1/', $date);
                $this->news['headline'][$i]['date'] = strtotime($date);
            }
        }

        //    narrowing-in to topic news content
        $this->log->add(__LINE__, "ParseList: topic");
        if (preg_match_all($this->regex_list_topic_all,$remaining_buffer,$result)) {
            $tp_buff = $result;
            $count_topic = count($tp_buff[1]);  # daftar topik
            for ($i = 0; $i < $count_topic; $i++) {
                $title = trim(strip_tags($tp_buff[1][$i]));
                if ($title == '') continue; // 9nov04, skip if topic has no title
                $this->news['topic'][$i]['title'] = $title;  // topic->title
                if (!preg_match_all($this->regex_list_topic_detail,$tp_buff[2][$i],$tpdetail_buff)) continue;   # skip if can't parse it
                $titles = $tpdetail_buff[3];
                $urls = $tpdetail_buff[2];
                $dates = $urls; //date will be parsed from url
                $count_news = count($tpdetail_buff[1]);
                for ($j = 0; $j < $count_news; $j++) {
                    $this->news['topic'][$i]['news'][$j]['title'] = $titles[$j];  //    topic->title->title
                    //    topic->title->url
                    $regex_topic_url = '|\?url=(.*)|';
                    if (!preg_match($regex_topic_url,$urls[$j],$urls_res)) {
                        //try apakah ini http biasa
                        $regex_topic_url = '|^http://|';
                        if (preg_match($regex_topic_url,$urls[$j],$urls_res)) $this->news['topic'][$i]['news'][$j]['url'] = $urls[$j];
                    }
                    else {
                        $this->news['topic'][$i]['news'][$j]['url'] = $urls_res[1];
                    }
                    //    topic->title->date
                    if (preg_match($this->regex_list_headline_date,$dates[$j],$tgl)) {
                        $this->news['topic'][$i]['news'][$j]['date'] = mktime(0,0,0,$tgl[2],$tgl[3],$tgl[1]);
                    }
                    if (trim($this->news['topic'][$i]['news'][$j]['title']) == '') $this->news['topic'][$i]['news'][$j]['title'] = $this->GetAnchorText($this->news['topic'][$i]['news'][$j]['title']);
                }
            }
        }

        return $this;
    }

    function ParseDetail () {
        $this->ParseAds();
        if (preg_match($this->regex_detail,$this->buffer,$result)) {
            $this->log->add(__LINE__, 'parser: newsdetail: 1: success');
            $narrow_buffer = $result[1];
        }
        else {
            $this->log->add(__LINE__, "parser: newsdetail: 1: fail ($regex_1)");
            $narrow_buffer = $this->buffer;
        }
        if (strpos($url,'berita-foto') !== False) { // this channel is different enough, that need specific pregmathicng
            //        title
            $regex = "|<FONT size=5>(.*?)</font>|is";
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['title'] = $res[1];
            //        reporter
            $regex = "|<BR><FONT color=#ff0000 size=2>(.*?)</font>|is";
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['reporter'] = $res[1];
            //        content
            $regex = '|<P align="Justify">(.*)|is';
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['content'] = $res[1];
            //        'recondition' urls in content
            $this->news['content'] = preg_replace('|<a href=(.?)http://www.detik.com/|',"<a href=\\1{$_SERVER['PHP_SELF']}?url=http://www.detik.com/",$this->news['content']);
        }
        elseif (strpos($url,'detikhot') !== False) { // this channel is different enough, that need specific pregmathicng
            //        sub-title
            $regex = "|<font class=.?subjudulberita.?>(.*?)</font>|is";
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['subtitle'] = $res[1];
            //        title
            $regex = '|<font color="#669900" size="4" face="Arial, Helvetica, sans-serif">(.*?)</font>.*$|is';
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['title'] = $res[1];
            //        reporter
            $regex = "|<td valign=\"top\"><strong> (.*?)</strong>|is";
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['reporter'] = $res[1];
            //        content
            $regex = '|<tr valign="top" width="525">.*?<td valign="top">(.*?)(?:<\/td>|$)|is';
            if (preg_match($regex,$narrow_buffer,$res)) $this->news['content'] = $res[1];
            //        clean html
            $this->news['reporter'] = strip_tags($this->news['reporter'],'<b></b><i></i>');
            $this->news['content'] = strip_tags($this->news['content'],'<b></b><i></i><a></a><p></p><br>');
        }
        else {
            //        sub-title
            $regex = "|<font class=.?subjudulberita.?>(.*?)</font>|is";
            if (preg_match($regex,$buffers,$res)) $this->news['subtitle'] = $res[1];
            //        title
            $regex = "|<font class=.?judulberita.?>(.*?)</font>.*$|is";
            if (preg_match($regex,$buffers,$res)) $this->news['title'] = $res[1];
            //        reporter
            $regex = "|<font class=.?textreporter.?>(.*?)</font>|is";
            if (preg_match($regex,$buffers,$res)) $this->news['reporter'] = $res[1];
            //        content
            $regex = "|<font class=.?textberita.?>(.{300,}?)(?:<\/font>|$)|is";
            #~ $regex = "/$regex_start(.*?detikcom.*?)$regex_end/is";   # siap siap
            if (preg_match($regex,$buffers,$res)) $this->news['content'] = $res[1];
            //clean html
            $this->news['reporter'] = strip_tags($this->news['reporter'],'<b></b><i></i>');
            $this->news['content'] = strip_tags($this->news['content'],'<b></b><i></i><a></a><p></p><br>');
        }
        $this->news['url'] = $this->url;    # save url, will parse later
        # its hard to really parse datetime from url, but easier done from news list, so if newslist provide one, we'll use it
        $this->news['date'] = $this->unxdt;      # unix time, datetime of news detail (passed from param for RSS, from _REQUEST for others)
        return $this;
    }

    function Parse () {
        if ($this->already_parsed) return $this;
        if ($this->GetSourceMode() == 'node')
            return $this->ParseNode();
        elseif ($this->GetDataMode() == 'list')
            return $this->ParseList();
        elseif ($this->GetDataMode() == 'detail')
            return $this->ParseDetail();
        return $this;
    }

    function ParseAds () {
    /*
    decorate @news with key "ads" containing advertisements data
    */
        global $app;
        if (!$app['ads']) return $this;
        $regex_ads = '|<a([^>]*)>(.*?)</a>|is';
        $this->log->add(__LINE__, 'parser: ads: regex:"'.$regex_ads.'"');
        if (!preg_match_all($regex_ads,$this->buffer,$ads_res,PREG_SET_ORDER)) {   // get all ad links
            return $this;
        }
        $this->log->add(__LINE__, 'parser: ads: 1: success');
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
        return $this;
    }

    function Render () {

        if ($this->GetDataMode() == 'detail') {
            return $this->RenderDetailNormal();
        }
        elseif ($this->GetDataMode() == 'list') {
            if ($this->GetSourceMode() == 'node')
                return $this->RenderNode();
            return $this->RenderListNormal();
        }
        return $this;
    }

    function RenderDetailNormal ($complete=True,$with_header=True) {
        /* renderer for news detail/body */
        global $list_header_output,$url,$app;
        $content = preg_replace('|<B>(.*?)<P>|is','<span style=font-size:larger><B>\\1</span><P>',$this->news['content']);    //specialized first paragraph
        $content = preg_replace('|<a href=("?)http://www.detik.com|is',"<a href=\\1{$_SERVER['PHP_SELF']}?url=http://www.detik.com",$content);  //fix url berita terkait
        if ($with_header) echo $list_header_output;

        if ($complete) {
            if ($this->news['date'] != '')
                echo '<small>'.date('Y-m-d H:i:s',$this->news['date']).'</small>';
            echo '<h3>'.$this->news['subtitle'].' '.$this->news['title'].'</h3>';
            echo '<p class="u">'.$this->news['reporter'].'</p>';
            $this->RenderAds();
            echo '<span class="u">'.$content.'</span>';
        }
        else {
            echo '<span class=u>'.$content.'</span>';
        }
        echo '<p><small><a href="'.$this->news['url'].'" style="color:#666">'.$this->news['url'].'</a></small></p>';
        return $this;
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
        return $this;
    }

    function RenderDetailGmail () {
        return $this->RenderDetailNormal(False,False);
    }

    function RenderDetailRss () {
        return $this->RenderDetailNormal(True,False);
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

    function RenderListRss ($with_content = False) {
    /* news list renderer for RSS
    @with_content  True to include complete news body inside RSS
    */
        $this->log->add(__LINE__, 'using news list view rss');
        global $list_header_output,$list_top_output2,$app,$no,$author_name,$author_email;
        echo '<?xml version="1.0"?><rss version="2.0"><channel><title>Detik.Usable: berita cepat</title><link>'.($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'</link><description>'.htmlentities($app['version_description']).'</description><language>id</language><category>news</category><managingEditor>'.$author_email.'</managingEditor><webMaster>'.$author_email.'</webMaster><lastBuildDate>'.date('r').'</lastBuildDate><generator>'.$app['name'].' v'.$app['version'].'</generator>';
        $this->log->add(__LINE__, 'dumping rss for headline');
        foreach ($this->news['headline'] as $headline) {  // view headlines
            $url = ($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?url='.urlencode($headline['url']);
            if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
            if ($with_content) {
                $du = new DetikUsable($headline['url']); $du->SetModeDetail(); $du->SetDetailDate($headline['date']); $du->GetBuffer(); $du->Parse(); $du->CaptureStart(); $du->RenderDetailRss();
                $description = htmlentities($du->CaptureEnd());
            }
            else
                $description = htmlentities($headline['summary']);
            echo '<item><title>'.htmlentities(strip_tags($headline['subtitle'].$headline['title'])).'</title><link>'.$url.'</link><description>'.$description.'</description><guid>'.$headline['url'].'</guid><pubDate>'.date('r',$headline['date']).'</pubDate><category>Headlines</category></item>';
        }
        exit();
        $this->log->add(__LINE__, 'dumping rss for previous news');
        foreach ($this->news['prevnews'] as $headline) {  //  view prevnews
            $url = ($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?url='.urlencode($headline['url']);
            if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
            if ($with_content) {
                $du = new DetikUsable($headline['url']); $du->SetModeDetail(); $du->SetDetailDate($headline['date']); $du->GetBuffer(); $du->Parse(); $du->CaptureStart(); $du->RenderDetailRss();
                $description = htmlentities($du->CaptureEnd());
            }
            else
                $description = '';
            echo '<item><title>'.htmlentities(strip_tags($headline['subtitle'].$headline['title'])).'</title><link>'.$url.'</link><description>'.$description.'</description><guid>'.$headline['url'].'</guid><pubDate>'.date ('r',$headline['date']).'</pubDate><category>Previous News</category></item>';
        }
        $this->log->add(__LINE__, 'dumping rss for topic news');
        foreach ($this->news['topic'] as $topic) {    //  view topic news
            foreach ($topic['news'] as $headline) {
                $url = ($_SERVER['HTTPS'] == "on"? 'https': 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?url='.urlencode($headline['url']);
                if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
                if ($with_content) {
                    $du = new DetikUsable($headline['url']); $du->SetModeDetail(); $du->SetDetailDate($headline['date']); $du->GetBuffer(); $du->Parse(); $du->CaptureStart(); $du->RenderDetailRss();
                    $description = htmlentities($du->CaptureEnd());
                }
                else
                    $description = '';
                echo '<item><title>'.htmlentities(strip_tags($headline['subtitle'].$headline['title'])).'</title><link>'.$url.'</link><description>'.$description.'</description><guid>'.$headline['url'].'</guid><pubDate>'.date('r',$headline['date']).'</pubDate><category>'.$topic['title'].'</category></item>';
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
        return $this;
    }

    function RenderListNormal ($frame_target='m') {
        if ($this->news['cache']) {
            echo '<center><span style="font-size:x-small;font-weight:bold;color:c00;">list dari cache '.str_time_delta($this->news['cache']);
            if ((time() - $this->news['cache'])>$this->newslist_cache_revalidate) {
                if ($_REQUEST['pda']) echo ' lalu (press reload button for realtime update)</span></center>';
                else echo ' lalu. Auto-loading berita baru di background.</span></center>';
            }
            else echo ' lalu</span></center>';
        }
        #~ $this->log->write_error('none');
        foreach ($this->news['headline'] as $headline) {  // view headlines
            $headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&amp;url='.urlencode($headline['url']);
            if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
            $date = date('H:i',$headline['date']);
            $date_delta = str_time_delta($headline['date']);
            $alt = 3;
            # time below title, slashdot style
            echo '<p><span class="j"><a href="'.$headline['url'].'" target="'.$frame_target.'">'.strip_tags($headline['subtitle'].$headline['title']).'</a></span>';
            echo '<br><span class="u">'.$headline['summary'].'</span>';
            echo '<b><small><span class="d">--'.$date.', '.$date_delta.' lalu</span></small></b></p>';
        }
        echo '<p></p><table border="0" cellspacing="0" cellpadding="0" summary="">';
        foreach ($this->news['prevnews'] as $headline) {  //  view prevnews
            $headline['url_orig'] = $headline['url'];
            $headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&amp;url='.urlencode($headline['url']);
            if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
            $date = date('H:i',$headline['date']);
            $title_string = strip_tags($headline['subtitle'].$headline['title']);
            echo '<tr><td valign="top">&bull;&nbsp;</td><td><span class="i"><a href="'.$headline['url'].'" target="'.$frame_target.'">'.$title_string.'</a></span><small><b><span class="d">--'.$date.'</span></b></small></td></tr>';
        }
        echo '</table>';

        foreach ($this->news['topic'] as $topic) {    //  view topic news
            echo '<p style="margin-bottom:0;"><span class="i">'.$topic['title'].'</span></p>';
            echo '<table border="0" cellspacing="0" cellpadding="0" summary="">';
            foreach ($topic['news'] as $headline) {
                $headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&amp;url='.urlencode($headline['url']);
                if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
                # skip the time (too unpredictable)
                #~ $date = date('H:i',$headline['date']);
                echo '<tr><td valign="top">&bull;&nbsp;</td><td><span class="i"><a href="'.$headline['url'].'" target="'.$frame_target.'">'.$headline['subtitle'].$headline['title'].'</a></span></td></tr>';
            }
            echo '</table>';
        }
        echo '<p align="center"><a href="'.$_SERVER['PHP_SELF'].'?x=w" target="m">Home &gt;</a></p>';
        return $this;
    }

    function RenderAds () {
        global $app;
        if ($app['ads'] and $this->news['ads'] != '') {
            echo '<table align=right bgcolor=#B4D0DC border=0 cellspacing=0 width=100><tr><td><table border=0 cellpadding=3 cellspacing=0 width=100%><tr><td bgcolor=#ECF8FF>';
            echo '<p class=u><span class=i>Iklan</span>';
            foreach ($this->news['ads'] as $ads) {
                $url = $ads['url'];
                $desc = $ads['name'];
                if (strlen($desc)>10) $desc = substr($desc,0,10).'&gt;';
                if ($desc == '') $desc = 'Iklan';
                echo "<br><a href=\"$url\" target=m>$desc</a>";
            }
            echo '</td></tr></table></td></tr></table>';
        }
        return $this;
    }

    function ParseAnchor () {
    /* We Got New Intelligent Algorithm to parse New list more reliable!
    get dictionary of <a>...</a> links
    */
        if ($this->GetSourceMode() != 'orig') return False; # we expect buffer to be filled with original HTML version
        $this->anchors = array();   # reset anchors
        $source_url = parse_url($this->url);
        $regex = "|<a href=[\"']?([^\" >]*).*?>(.*?)</a>|is";
        if (preg_match_all($regex,$this->buffer,$groups,PREG_SET_ORDER)) {
            foreach ($groups as $group) {
                # we deal only with absolute href
                $href = $group[1];
                if (!stristr($href,'http://')) {
                    # if href does not begin with '/', complete with fixpathnonslash
                    if (substr($href,0,1) != '/') $href = $fixpathnonslash.'/'.$href;
                    else $href = 'http://'.$source_url['host'].':'.$source_url['port'].$href;   //href begin with '/', absolutize with hostname
                }
                #~ $this->anchors[md5($href)] = array('href'=>$href, 'text'=>$group[2], 'cleantext'=>strip_tags($group[2]) );
                #~ $this->anchors[md5($href)] = array('href'=>$href, 'text'=>$group[2]);
                $this->anchors[] = array('href'=>$href, 'text'=>$group[2]);
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

}
$log->add(__LINE__, "{$app['name']} v{$app['version']} starting up from {$_SERVER['SERVER_ADDR']}/{$_SERVER['SERVER_PORT']}");
?>