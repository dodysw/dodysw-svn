<?
$app['name'] = "detik.usable";
$app['version'] = "6.0";
$app['version_description'] = <<<__END__
- rss link in meta tag, and every footer of du and anynews footer
- anynews rss title now use its url or its name
- reduce welcome page
- rss complete for anynews
- nicer error page
- lots of bug fixes on date/timezone
- cache browsing daily sorted on time
- ability to switch using jkt/jkt1/jkt2/jkt3 + save the state via cookie
- du/anynews class refactoring
__END__;
/**
	detik.usable: a fast-download detik.com
	Author: dody suria wijaya <dodysw@gmail.com>
	License: THIS IS A PUBLIC DOMAIN CODE (you may even change the author)
	Term of Usage: BY USING THIS SCRIPT, YOU TAKE FULL RESPONSIBILITY OF ANY LEGAL ACTION THAT MAY BE TAKEN.
	Note: Mail me with your personal info (bio) to get non-text-compressed version of the source code
*/
$app['proxy_mode'] = False;
$app['proxy_url'] = '';
$app['ads'] = TRUE;
$app['cache'] = TRUE;
$app['http_proxy']['enable'] = FALSE;
$app['http_proxy']['hostname'] = 'proxy.myoffice.com';
$app['http_proxy']['port'] = '8080';
$app['http_proxy']['user'] = 'myproxyusername';
$app['http_proxy']['pass'] = 'myproxypassword';
$app['url_list'] = 'http://jkt3.detik.com/index.php';
/**
note: if you got "access forbidden" error, or connection fail, and your hosting server located
outside Indonesia, use one of this line instead:
$app['url_list'] = 'http://jkt.detik.com/index.php';
$app['url_list'] = 'http://jkt1.detik.com/index.php';
$app['url_list'] = 'http://jkt2.detik.com/index.php';
**/
#~ $app['url_list'] = 'http://localhost/detik-index.html';
$app['update_url'] = array();
$app['update_url'][] = 'http://popok.sourceforge.net/du/detikusable-latest.php.txt';
$app['update_url'][] = 'http://du.port5.com/detikusable-latest.php.txt';
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
	array('name'=>'SWA','url'=>'http://www.swa.co.id/'),
	array('name'=>'Gatra','url'=>'http://www.gatra.com/'),
	array('name'=>'Infokomputer','url'=>'http://www.infokomputer.com/'),
	array('name'=>'Pos&nbsp;Kota','url'=>'http://www.poskota.co.id/poskota/index.asp'),
	array('name'=>'Indonesian&nbsp;Business','url'=>'http://articles.ibonweb.com/default.asp'),
	array('name'=>'Berita&nbsp;Iptek','url'=>'http://www.beritaiptek.com/')
);
$hari = array('Minggu','Senin','Selasa','Rabu','Kamis','Jum\'at','Sabtu');
$bulan = array('','Januari','Februari','Maret','April','Mei','Juni','July','Agustus','September','Oktober','November','Desember');
$develmode = 0;
$list_footer = '';
$app['last-modified'] = filemtime($_SERVER['SCRIPT_FILENAME']);
if (file_exists('config.inc.php')) include 'config.inc.php';
function merge_query($newquery) {
	foreach (array_merge($_GET,$newquery) as $k=>$v) $temparr[] = urlencode($k).'='.urlencode($v);
	return implode('&amp;',$temparr);
}
function show_footer($temp_orig=False, $fields=array()) {
	global $author_email, $author_website, $app, $list_footer;
	$e = array();
	if ($temp_orig)
		$e[] = '<a href="'.$temp_orig.'" target="_top">sumber berita</a>';
	$e[] = '<a href="'.$author_website.'" target="_top">dibangkitkan '.$app['name'].' v'.$app['version'].'</a>';
	$e[] = 'pemiliknya '.$app['hosted_by'];
	echo $list_footer;
	echo '<div id="footer"><p>'.join(' | ',array_merge($e,$fields)).'</p></div></body></html>';
}
function DieError($msg) {
	show_footer();
}
function mystripslashes($val) {
	return get_magic_quotes_gpc()? stripslashes($val) : $val;
}
function httpcache_by_lastupdate($modif_time = -1) {
	if ($modif_time == -1) {
		global $app;
		$modif_time = $app['last-modified'];
	}
	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) and gmdate('D, d M Y H:i:s', $modif_time).' GMT' == trim($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
		header('HTTP/1.0 304 Not Modified');
		header('Content-Length: 0');
		exit();
	}
	header('Last-Modified: '.gmdate('D, d M Y H:i:s',$modif_time).' GMT');
}
function ShowHeader($meta='') {
	global $tgl_lengkap;
	echo HtmlHeader(2,$meta);
	echo '<div id="header">';
	echo '<p class="date">'.$tgl_lengkap.'</p>';
	echo '<h1><a href="'.$_SERVER['PHP_SELF'].'"><span class="detik">detik.</span><span class="usable">usable</span></a></h1>';
	echo '<div id="nav">';
	echo '<ul>';
	if ($_REQUEST['x']=='x')
		echo '<li><a href="'.$_SERVER['PHP_SELF'].'?cache_reload=1">Reload</a> </li>';
	else
		echo '<li><a href="'.$_SERVER['PHP_SELF'].'">Awal</a> </li>';
	echo '<li><a href="'.$_SERVER['PHP_SELF'].'?x=s">Kode sumber</a> </li>';
	echo '<li><a href="'.$_SERVER['PHP_SELF'].'?au=1">Ada versi baru?</a> </li>';
	echo '<li><a href="'.$_SERVER['PHP_SELF'].'?no=bcache">Lihat</a> / <a href="'.$_SERVER['PHP_SELF'].'?cm=1">Hapus cache</a> </li>';
	echo '<li><a href="'.$app['update_url'].'?x=y">AnyNews</a> </li>';
	echo '<li><a href="'.$_SERVER['PHP_SELF'].'?x=w">Tentang detik.usable</a> </li>';
	echo '</ul>';
	echo '</div>';
	echo '</div>';
}
function ShowCacheBrowseWarning() {
	ShowHeader();
	echo '<div id="info">';
	echo '<h1>Cache belum ada isi</h1>';
	echo '<p>Sepertinya belum ada berita yang disimpan dalam cache. Bila detik.usable diatur untuk boleh menyimpan berita, lihat lagi di sini setelah membuka beberapa berita.</p>';
	echo '</div>';
	show_footer();
	die();
}
define('ERROR_SOCKET',1);
define('ERROR_LOOPINGREDIR',2);
$tz = intval(substr(date('O'),0,3));
$tz_diff = $tz - 7;
$ctime = time();
$timezone_sign = ($tz >= 0)? '+':'';
$tgl_lengkap = $hari[date('w',$ctime)].',&nbsp;'.date('j',$ctime).'&nbsp;'.$bulan[date('n',$ctime)].date(' Y',$ctime).'&mdash;'.date('H:i',$ctime).' GMT'.$timezone_sign.($tz);
function HtmlHeader($css_id=2, $meta='', $title='detik.usable') {
	global $tgl_lengkap;
	return '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><title>'.$title.' ('.$tgl_lengkap.')</title><link rel="stylesheet" href="'.$_SERVER['PHP_SELF'].'?x=css&x2='.$css_id.'" type="text/css" />'.$meta.'</head><body>';
}
@set_time_limit(60*5);
ob_end_flush();
$current_time = date('His', time()-(3600*$tz_diff));
function smaller_than_curr_time($var) {
	global $current_time;
	return ($var <= $current_time);
}
if ($_GET['dudul'] != '') {
	switch ($_GET['dudul']) {
		case 0: $app['url_list'] = 'http://jkt.detik.com/index.php'; break;
		case 1: $app['url_list'] = 'http://jkt1.detik.com/index.php'; break;
		case 2: $app['url_list'] = 'http://jkt2.detik.com/index.php'; break;
		case 3: $app['url_list'] = 'http://jkt3.detik.com/index.php'; break;
	}
}
if ($_REQUEST['dudul'] != '')   # both GET and COOKIE
	setcookie('dudul',$_REQUEST['dudul'], time()+31000000);
class DuSock {
	var $user_agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1) ';
	function DuSock ($host,$port=80) {
		global $app;
		$this->host = $host;
		$this->port = $port == ''? 80: $port;
		$this->timeout = 30;
		$this->error_cant_open = '';
		$this->http_proxy_enable = $app['http_proxy']['enable'];
		$this->http_proxy_host = $app['http_proxy']['hostname'];
		$this->http_proxy_port = $app['http_proxy']['port'];
		$this->http_proxy_user = $app['http_proxy']['user'];
		$this->http_proxy_pass = $app['http_proxy']['pass'];
		assert($this->host != '');
		$this->iter_302 = array();
		$this->max_iter_302 = 10;
		$this->referer = '';
		$this->cookie = '';
	}
	function socket_open () {
		if ($this->http_proxy_enable) {
			$this->fp = @fsockopen ($this->http_proxy_host,$this->http_proxy_port,$errno,$errstr,$this->timeout);
		}
		else {
			$this->fp = @fsockopen ($this->host,$this->port,$errno,$errstr,$this->timeout);
		}
		if (!$this->fp) {
			if ($errno == 0) {
			}
			else {
			}
			return FALSE;
		}
		else {
			return TRUE;
		}
	}
	function sock_send_request ($location) {
		if ($location == '') $location = '/';
		$http_reqs = array();
		if ($this->http_proxy_enable) {
			$header_auth = '';
			if ($this->http_proxy_user != '') {
				$header_auth = 'Proxy-Authorization: Basic '.base64_encode($this->http_proxy_user.':'.$this->http_proxy_pass);
			}
			$http_reqs[] = 'GET http://'.$this->host.':'.$this->port.$location.' HTTP/1.0';
			$http_reqs[] = 'Host: '.$this->host.':'.$this->port;
			if ($this->referer != '')
				$http_reqs[] = 'Referer: '.$this->referer;
			else
				$http_reqs[] = 'Referer: http://'.$this->host.'/';
			$http_reqs[] = 'User-Agent: '.$this->user_agent;
			if ($header_auth)
				$http_reqs[] = $header_auth;
			$http_reqs[] = 'Connection: close';
			if ($this->cookie != '')
				$http_reqs[] = 'Cookie: '.$this->cookie;
		}
		else {
			$http_reqs[] = 'GET '.$location.' HTTP/1.0';
			$http_reqs[] = 'Host: '.$this->host.':'.$this->port;
			if ($this->referer != '')
				$http_reqs[] = 'Referer: '.$this->referer;
			else
				$http_reqs[] = 'Referer: http://'.$this->host.'/';
			$http_reqs[] = 'User-Agent: '.$this->user_agent;
			$http_reqs[] = 'Connection: close';
			if ($this->cookie != '')
				$http_reqs[] = 'Cookie: '.$this->cookie;
		}
		$http_req = implode("\r\n",$http_reqs)."\r\n\r\n";
		$return = fputs ($this->fp, $http_req);
		if ($return == -1) {
			return FALSE;
		}
		else {
			return TRUE;
		}
	}
	function sock_recv_header () {
		$buffers = '';
		while (!feof ($this->fp)) {
			$buffer = fgets($this->fp, 65536 );
			if ($buffer == "\r\n") break;
			$buffers .= $buffer;
		}
		$temp = explode('\n',$buffers,2);
		list($http, $respcode, $respdesc) = explode(' ',$temp[0]);
		if ($respcode == '302') {  //redirecting
			if (count($this->iter_302) >= $this->max_iter_302)
				die('<p>Max redirection iteration exceeded. Locations:<br>'.implode('<br>',$this->iter_302));
			if (!preg_match('|^Location:\s*(.+)$|im',$buffers,$group))
				assert('Response code 302 but can\'t find location header');
			$new_url = trim($group[1]);
			$this->iter_302[] = $new_url;
			$p = parse_url($new_url);
			$this->host = ($p['host'] == '')? $this->host: $p['host'];
			$this->port = ($p['port'] == '')? (($p['host'] == '')? $this->port: 80): $p['port'];
			if (!$this->socket_open()) die('Cannot contact repository at '.$this->proxy_url);
			$this->sock_send_request($p['path'].(($p['query'] == '')? '': '?'.$p['query']));
			return $this->sock_recv_header();
		}
		elseif ($respcode == '200') {  //redirecting
			$this->iter_302 = array();
		}
		else {
		}
		return $buffers;
	}
	function sock_recv_all () { //receive the rest of the data, then close
		$buffers = '';
		do {	// recv all response body
		   $data = fread($this->fp, 8192);
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
function str_time_delta ($date_c,$compressed=FALSE,$localtz=FALSE) {
	global $ctime, $app, $tz_diff;
	if ($localtz)
		$delta = $ctime - $date_c + 3600*$tz_diff;
	else
		$delta = $ctime - $date_c;
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
	elseif ($delta < 3600) {	# dibawah 1 jam
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
	var $newslist_cache_revalidate = 300;
	var $regex_list_prevnews = "|=.nmkanal(.*?)indeks berita(.*)|is";
	var $regex_list_prevnews_all = '|<span class="tglnonhl">.*?(\d+/\d+/\d+.*?)<.*?<a href="([^"]+)" class="nonhl"[^>]*>(.*?<span class="nonhlJudul">.*?)</A>|is';
	var $regex_list_prevnews_subtitle = '|><span class=.nonhlSubJudul.>(.+?)</span>|';
	var $regex_list_prevnews_title = '|nonhlJudul.>(.*)|';
	var $regex_list_headline = '|(<span class="tanggal">.*?)<!-- End of Center(.*)|is';
	var $regex_list_headline_all = '|tanggal.>[^,]*,(.*?) WIB<.*?<A href="([^"]+)" class="hl"[^>]*>(.*?<span class="summary">.*?</span>)|is';
	var $regex_list_headline_subtitle = '|subjudul.>(.*?)</span|is';
	var $regex_list_headline_title = '|strJudul.>(.+?)</span|is';
	var $regex_list_headline_summary = '|summary.>(.*?)</span|s';
	var $regex_list_topic_all = '|(<h\d>[^<]*?)</h\d>\s*<ul>(.*?)</ul>|si';
	var $regex_list_topic_detail = '|<a href="([^"]+tahun/[^"]+)"[^>]*>.*?"judulhlbawah">(.+?)</span>|is';
	var $regex_list_topic_detail_basic = '|<a href="([^"]+)"[^>]*>.*?"judulhlbawah">(.+?)</span>|is';
	var $regex_list_headline_date = '|/tahun/(\d*)/bulan/(\d*)/tgl/(\d*)/|i';
	var $regex_detail = "|<blockquote>(.*?)<!-- FORM|is";
	var $last_error = 0;
	var $last_parse_ok = False;
	function DetikUsable ($url='') {
		global $app;
		$this->SetSourceFast();
		$this->SetUrl($url);
		$this->proxy_url = $app['proxy_url'];
		$this->zlib_support = extension_loaded('zlib');
		$this->enable_cache = $app['cache'];
		$this->unxdt = $_REQUEST['unxdt'];
		$this->url_list = $app['url_list'];
		$this->cache_prefix = '';
		$this->cookie_code = '';
		$this->footer_info = '';
		$this->url_base = '';
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
		if ($this->data_mode == 'list') $this->proxy_url .= "?url=$url&amp;as_node=1";
		elseif ($this->data_mode == 'detail') $this->proxy_url .= "?x=i&amp;as_node=1";
		else die('Invalid mode');
		if (!$this->zlib_support) $this->proxy_url .= '&amp;uc=1';
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
		if (!$this->enable_cache) return;
		if ($this->GetDataMode() == 'list' and $this->url == '') $this->url = $this->url_list;
		elseif ($this->GetDataMode() == 'detail' or $this->GetDataMode() == 'an_detail') $although_expired = True;
		$this->from_cache = False;
		$filename = 'cache/'.$this->cache_prefix.md5($this->url);
		if (!file_exists($filename) or $force_reload) {		 # check if already in cache
			$this->SetGetBufferOK(False);
			return;
		}
		$buffer = '';
		$fp = fopen($filename,'r');
		while(!feof($fp)) $buffer .= fread($fp,1024);
		fclose($fp);
		$this->news = unserialize($buffer);
		if (!$although_expired and (time() - $this->news['cache']) > $this->newslist_cache_revalidate ) {$this->SetGetBufferOK(False);return;} # check whether cache is expired
		$this->already_parsed = True;
		$this->last_parse_ok = True;
		$this->SetGetBufferOK(True);
		$this->from_cache = True;
	}
	function SaveCache () {
		if (!$this->enable_cache or !$this->news) return;
		if (!file_exists('cache')) mkdir('cache',0755);
		$filename = 'cache/'.$this->cache_prefix.md5($this->url);
		$this->news['cache'] = time();
		$buffer = serialize($this->news);
		unset($this->news['cache']);
		$fp = fopen($filename,'w');
		fwrite($fp,$buffer);
		fclose($fp);
	}
	function GetBufferOrig () {
		if ($this->GetDataMode() == 'list' and $this->url == '') $this->url = &$this->url_list;
		elseif ($this->GetDataMode() == 'detail')
			assert($this->url != '');
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
		if (preg_match('|Content-Encoding:\s*gzip|i',$header_buffer)) { // if Content-Encoding: gzip, then body is gzipped. Unzipped first.
			$this->buffer = gzinflate(substr($this->buffer, 10,-4));
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
			if (strpos($url, 'detikhot.com') !== FALSE and strpos($url, 'idnews') == FALSE) {	# link to detikhot from frontpage.
				$du2 = new AnyNews($url);
				$du2->SetModeList();
				$du2->cache_prefix = 'an_';
				$du2->GetBuffer();
				$success = False;
				foreach ($du2->news['links'] as $link) {
					if ($link['cleantext'] == '') continue;
					if ($_REQUEST['param1'] != '' and strpos($link['cleantext'], $_REQUEST['param1']) !== FALSE) {
						$this->url = $link['href'];
						$success = True;
						break;
					}
				}
				if (!$success) {	# try to find the nearest URL
					$rank = array();
					foreach ($du2->news['links'] as $link) {
						if ($link['cleantext'] == '') continue;
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
			}
			elseif (strpos($url, 'detikfinance.com') !== FALSE) {
				$this->cookie_code = $this->GetPageForCookieCode('/indexfr.php?url='.$this->url);
			}
			elseif ((strpos($url, 'detikinet.com') !== FALSE or strpos($url, 'detiksport.com') !== FALSE or strpos($url, 'detikpublishing.com') !== FALSE) and strpos($url, 'idnews') === FALSE ) {	# link to detikhot from frontpage.
				$du2 = new AnyNews($url);
				$du2->SetModeList();
				$du2->cache_prefix = 'an_';
				$du2->GetBuffer();
				$success = False;
				foreach ($du2->news['links'] as $link) {
					if ($link['cleantext'] == '') continue;
					if ($_REQUEST['param1'] != '' and strpos($link['cleantext'], $_REQUEST['param1']) !== FALSE) {
						$this->url = $link['href'];
						$success = True;
						break;
					}
				}
				if (!$success) {	# try to find the nearest URL
					$rank = array();
					foreach ($du2->news['links'] as $link) {
						if ($link['cleantext'] == '') continue;
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
		if ($this->zlib_support) {
			$buffer2 = @gzuncompress($buffer);
			if (!$buffer2 or is_bool($buffer2)) {	//it's probably not gzcompressed
			}
			else {
				$buffer = &$buffer2;
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
		$this->news = &$news;
		$this->already_parsed = True;
	}
	function ParseList () {
		global $tz_diff;
		$this->ParseAds();
		if (preg_match($this->regex_list_prevnews,$this->buffer,$result)) {
			$narrow_buffer = $result[1];
			$remaining_buffer = $result[2];
		}
		else {
			$narrow_buffer = $this->buffer;
		}
		if (preg_match_all($this->regex_list_prevnews_all,$narrow_buffer,$result)) {
			unset($narrow_buffer);
			$total_prev_news = count($result[2]);
			for ($i = 0; $i < $total_prev_news; $i++) {
				$url = $result[2][$i];
				$date = $result[1][$i];
				$title_temp = $result[3][$i];
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
				$this->news['headline'][$i]['date'] = strtotime($date)+$tz_diff*3600;
			}
		}
		if (preg_match_all($this->regex_list_topic_all,$remaining_buffer,$result)) {
			$tp_buff = $result;
			$count_topic = count($tp_buff[1]);
			for ($i = 0; $i < $count_topic; $i++) {
				$title = trim(strip_tags($tp_buff[1][$i]));
				if ($title == '') continue;
				$this->news['topic'][$i]['title'] = $title;
				if (!preg_match_all($this->regex_list_topic_detail,$tp_buff[2][$i],$tpdetail_buff)) {
					if (!preg_match_all($this->regex_list_topic_detail_basic,$tp_buff[2][$i],$tpdetail_buff))
						continue;
				}
				$titles = $tpdetail_buff[2];
				$urls = $tpdetail_buff[1];
				$dates = $urls;
				$count_news = count($tpdetail_buff[1]);
				for ($j = 0; $j < $count_news; $j++) {
					$this->news['topic'][$i]['news'][$j]['title'] = $titles[$j];
					$regex_topic_url = '|\?url=(.*)|';
					if (!preg_match($regex_topic_url,$urls[$j],$urls_res)) {
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
		$content_level_2 = '';
		$narrow_buffer = str_replace('<font color="#F8EEBE">SMSiklan</font>', '', $narrow_buffer);
		$narrow_buffer = preg_replace('#<font class="judultop5">.*?</font>#is', '', $narrow_buffer);
		if (strpos($this->url,'berita-foto') !== False) { // this channel is different enough, that need specific pregmathicng
			$regex = "|<FONT size=5>(.*?)</font>|is";
			if (preg_match($regex,$narrow_buffer,$res)) $this->news['title'] = $res[1];
			$regex = "|<br/><FONT color=#ff0000 size=2>(.*?)</font>|is";
			if (preg_match($regex,$narrow_buffer,$res)) $this->news['reporter'] = $res[1];
			$regex = '|<P align="Justify">(.*)|is';
			if (preg_match($regex,$narrow_buffer,$res)) $this->news['content'] = $res[1];
			$this->news['content'] = preg_replace('|<a href=(.?)http://www.detik.com/|',"<a href=\\1{$_SERVER['PHP_SELF']}?url=http://www.detik.com/",$this->news['content']);
		}
		elseif (strpos($this->url,'detikhot') !== False) { // this channel is different enough, that need specific pregmathicng
			$regex = "|<font class=.?subjudulberita.?>(.*?)</font>|is";
			if (preg_match($regex,$narrow_buffer,$res)) $this->news['subtitle'] = $res[1];
			$regex = '|<span class="judul">(.*?)</span>.*$|is';
			$this->news['title'] = $_REQUEST['param1'];
			$regex = '|<span class="reporter">(.*?)</span>(.*)<!-- content //-->|is';
			if (preg_match($regex,$narrow_buffer,$res)) $this->news['reporter'] = $res[1];
			$this->news['content'] = $res[2];
			$this->news['reporter'] = strip_tags($this->news['reporter'],'<b><i>');
			$this->news['content'] = strip_tags($this->news['content'],'<b><i><a><p><br><li><ul>');
		}
		else {
			while (1) {
				$regex = '|<title>(.*?)-\s*(.{6,}?)</title>(.*)$|is';
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
				$this->news['title'] = '';
				break;
			}
			while (1) {
				$regex = '|<font class=.?subjudulberita.?>(.{10,}?)</font>(.*)|is';
				if (preg_match($regex,$narrow_buffer,$res)) {$this->news['reporter'] = $res[1]; break; }
				if ($this->news['title']) {
					$regex = '|'.preg_quote($this->news['title'],'|').'.*?<font.*?>(.{10,}?)</font>(.*)|is';
					if (preg_match($regex,$narrow_buffer,$res)) {$this->news['reporter'] = $res[1]; $narrow_buffer = $res[2]; break; }
				}
				$this->news['reporter'] = '';
				break;
			}
			$minchar = 1000;
			$rt = -1;
			while (1) {
				if (strpos($this->url,'detikinet') !== False or strpos($this->url,'detiksport') !== False) {
					$regex = $this->news['title']."(.{".$minchar.",}?)</blockquote>";
					if (preg_match('#'.$regex.'#is', $narrow_buffer, $group)) { $this->news['content'] = $group[1]; $rt=15; break; }
				}
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
				$regex = "var\s+(\S+)='(.*?)';";
				if (preg_match('#'.$regex.'#is', $narrow_buffer, $group) and strlen($group[2]) > $minchar ) { $this->news['content'] = str_replace("\\'", "'",$group[2]); $rt=12; break; }
				$narrow_buffer = preg_replace('|<script[^>]*?>document.write\(\'(.*?)\'\)</script>|is','\1',$narrow_buffer);
				$narrow_buffer = preg_replace('|<script.*?>.*?</script>|is','',$narrow_buffer);
				$regex = '#relion\.swf.*?>(.{'.$minchar.',}?)<div id="smsblok">#is';
				if (preg_match($regex,$narrow_buffer,$res)) { $this->news['content'] = $res[1]; $rt=1.1; break; }
				$regex = '#relion\.swf.*?>(.{'.$minchar.',}?)</blockquote>#is';
				if (preg_match($regex,$narrow_buffer,$res)) { $this->news['content'] = $res[1]; $rt=1; break; }
				$regex = '/<font class=.?textberita.?>(.{'.$minchar.',}?)(?:<\/font>|$)/is';
				if (preg_match($regex,$narrow_buffer,$res) and strpos($res[1], '</font>') === FALSE) { $this->news['content'] = $res[1]; $rt=2; break; }
				$regex = '/(.{'.$minchar.',}?)(?:SMS Iklan|$)/is';
				if (preg_match($regex,$narrow_buffer,$res)) { $this->news['content'] = $res[1]; $rt=20; break; }
				$this->news['content'] = nl2br(trim(strip_tags($narrow_buffer)));
				$rt=0;
				$success = False;
				break;
			}
			$this->news['reporter'] = strip_tags($this->news['reporter'],'<b><i>');
			$this->news['content'] = strip_tags($this->news['content'],'<b><i><a><p><li><ul><br>');
			$this->news['content'] = str_replace('\<br \/>','<br/>',$this->news['content']);
			$this->news['rgxid'] = $rt;
		}
		$url_parsed = parse_url($this->url);
		$prefix_url = $_SERVER['PHP_SELF'].'?url=http://'.$url_parsed['host'].':'.$url_parsed['port'].'/';
		$this->news['content'] = preg_replace('#<a href=("|\')(index\.php[^\1]*?)\1(.*?)>#is', '<a href=\1'.$prefix_url.'\2\1\3>', $this->news['content']);
		$this->news['url'] = $this->url;
		$this->news['date'] = $this->unxdt;
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
		global $app;
		if (!$app['ads']) return;
		$regex_ads = '|<a([^>]*)>(.*?)</a>|is';
		if (!preg_match_all($regex_ads,$this->buffer,$ads_res,PREG_SET_ORDER)) {   // get all ad links
			return;
		}
		$this->news['ads'] = array();
		for ($i = 0; $i < count($ads_res); $i++) {
			preg_match('|href="([^"]*)"|is',$ads_res[$i][1],$url_res);
			if (!preg_match('|http://ad\.detik\.com/link|is',$url_res[1])) continue;
			unset($temp);
			$temp['url'] = $url_res[1];
			$name = trim(strip_tags($ads_res[$i][2]));
			if ($name == '') {
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
		global $url,$app;
		$content = preg_replace('|<B>(.*?)<P>|is','<span style=font-size:larger><B>\\1</span><P>',$this->news['content']);
		$content = preg_replace('|<a href=("?)http://www.detik.com|is',"<a href=\\1{$_SERVER['PHP_SELF']}?url=http://www.detik.com",$content);
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
	}
	function RenderNode ($uncompressed=0) {
		set_magic_quotes_runtime(0);
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
		$content = preg_replace('|<B>(.*?)<P>|is','<span style=font-size:larger><B>\\1</span><P>',$this->news['content']);
		$content = preg_replace('|<a href=("?)http://www.detik.com|is',"<a href=\\1{$_SERVER['PHP_SELF']}?url=http://www.detik.com",$content);
		echo '<?xml version="1.0"?><!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml"><wml><head></head><card id="main" title="'.htmlentities(strip_tags($this->news['subtitle'].' '.$this->news['title'])).'">';
		echo '<p>'.htmlentities(strip_tags($this->news['content'])).'</p>';
		echo '</card></wml>';
	}
	function RenderListRssComplete () {
		return $this->RenderListRss(True);
	}
	function RenderListRss ($with_content=False) {
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
		foreach ($this->news['topic'] as $topic) {	//  view topic news
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
				echo '<div class="block">';
				echo '<h3><a href="'.$headline['url'].'">'.strip_tags($headline['subtitle'].$headline['title']).'</a> </h3>';
				echo '<p>'.$headline['summary'].'</p>';
echo '<p class="date">'.$date_delta.' - '.$date.'</p>';
				echo '</div>';
			}
		}
		else {
			echo '<p class="error">Sorry, top headline news are not available. If this persists, <a href="mailto:'.$author_email.'">contact author</a>.</p>';
		}
		echo '</div>';
		echo '<div id="content-oldernews">';
		if ($this->news['prevnews']) {
			foreach ($this->news['prevnews'] as $headline) {  //  view prevnews
				$headline['url_orig'] = $headline['url'];
				$headline['url'] = $_SERVER['PHP_SELF'].'?unxdt='.urlencode($headline['date']).'&url='.urlencode($headline['url']);
				if ($headline['subtitle'] != '') $headline['subtitle'] .= ' - ';
				$date = date('H:i',$headline['date']);
				$title_string = strip_tags($headline['subtitle'].$headline['title']);
				echo '<div class="block">';
				echo '<h3><a href="#" onClick="getit(\''.$headline['url'].'\');return false;">'.$title_string.'</a></h3>';
				echo '<p class="date">'.$date.'</p>';
				echo '</div>';
			}
		}
		else {
			echo '<p class="error">Sorry, previous headline news are not available. If this persists, <a href="mailto:'.$author_email.'">contact author</a>.</p>';
		}
		echo '</div>';
		echo '</div>';
		echo '<div id="secondary">';
		echo '<div id="content-channels">';
		if ($this->news['topic']) {
			foreach ($this->news['topic'] as $topic) {	//  view topic news
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
				echo '</div>';
			}
		}
		else {
			echo '<p class="error">Sorry, topical news are not available. If this persists, <a href="mailto:'.$author_email.'">contact author</a>.</p>';
		}
		echo '</div>';
		$this->RenderAds();
		echo '</div>';
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
		if ($this->GetSourceMode() != 'orig') return False;
		$this->anchors = array();
		$source_url = parse_url($this->url);
		$regex = "/<a\s+href=[\"']?([^\" >]*).*?>(.*?)<\/a>(.*?)(?=<a|$)/is";
		if (preg_match_all($regex,$this->buffer,$groups,PREG_SET_ORDER)) {
			foreach ($groups as $group) {
				$href = $group[1];
				if (!stristr($href,'http://')) {
					if (substr($href,0,1) != '/') $href = $fixpathnonslash.$href;
					else $href = 'http://'.$source_url['host'].':'.$source_url['port'].$href;
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
		return '&lt;unknown title&gt;';
	}
	function GetPageForCookieCode ($url) {
		$url_parsed = parse_url($this->url);
		$sock = new DuSock($url_parsed['host'],$url_parsed['port']);
		if (!$sock->socket_open()) {
			die('Unable to create connection to ['.$sock->host.']:['.$sock->port.']');
		}
		$sock->sock_send_request($url);
		$header_buffer = $sock->sock_recv_header();
		if (!preg_match_all('|^set-cookie:\s*(.+?);|im',$header_buffer,$result)) {
			$this->footer_info = 'ncn';
			return '';
		}
		return implode(';',$result[1]);
	}
}
function nopadding_time_parser ($time) {
	if (strlen($time) == 6) {   # complete. always correct.
		return sscanf($time,"%02d%02d%02d");
	}
	elseif (strlen($time) == 5) {
		$correct = array();
		list($hour,$minute,$second) = sscanf($time,"%02d%02d%01d");
		if ($hour < 24 and $minute < 60 and $second < 60) $correct[] = sprintf('%02d%02d%02d',$hour,$minute,$second);
		list($hour,$minute,$second) = sscanf($time,"%02d%01d%02d");
		if ($hour < 24 and $minute < 60 and $second < 60) $correct[] = sprintf('%02d%02d%02d',$hour,$minute,$second);
		list($hour,$minute,$second) = sscanf($time,"%01d%02d%02d");
		if ($hour < 24 and $minute < 60 and $second < 60) $correct[] = sprintf('%02d%02d%02d',$hour,$minute,$second);
		if (count($correct) == 0) return array(0,0,0);
		if (count($correct) > 1) {
			sort($correct,SORT_STRING);
			$test1 = array_filter($correct, 'smaller_than_curr_time');
			if (count($test1)) $correct = $test1;
		}
		return sscanf(array_pop($correct),"%02d%02d%02d");
	}
	elseif (strlen($time) == 4) {
		$correct = array();
		list($hour,$minute,$second) = sscanf($time,"%01d%01d%02d");
		if ($hour < 24 and $minute < 60 and $second < 60) $correct[] = sprintf('%02d%02d%02d',$hour,$minute,$second);
		list($hour,$minute,$second) = sscanf($time,"%01d%02d%01d");
		if ($hour < 24 and $minute < 60 and $second < 60) $correct[] = sprintf('%02d%02d%02d',$hour,$minute,$second);
		list($hour,$minute,$second) = sscanf($time,"%02d%01d%01d");
		if ($hour < 24 and $minute < 60 and $second < 60) $correct[] = sprintf('%02d%02d%02d',$hour,$minute,$second);
		if (count($correct) == 0) return array(0,0,0);
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
	function AnyNews ($url='') { parent::DetikUsable($url); }
	function GetTitleName() { return $this->title_name; }
	function SetTitleName($t) { $this->title_name = $t; }
	function ParseList() {
		$logic_url = ($this->url_real != '')?$this->url_real: $this->url;
		$up = parse_url($logic_url);
		$hostname = $up['host'];
		if ($up['port'] != '') $hostname .= ':'.$up['port'];
		$scheme = $up['scheme'];
		$fixpathnonslash = $scheme.'://'.$hostname.substr($up['path'],0,strrpos($up['path'],'/')).'/';
		$regex = '<base[^>]*?href=(?:"|\')([^\1]*?)\1[^>]*?>';
		if (preg_match('#'.$regex.'#is', $this->buffer, $group)) {
			$this->url_base = $group[1];
			if (substr($this->url_base,-1,1) != '/') $this->url_base .= '/';
		}
		$links = array();
		$temp_deduper = array();
		$regex = "<a.*?href=[\"']?([^\" >]*).*?>(.*?)</a>";
		if (preg_match_all('|'.$regex.'|is',$this->buffer,$groups,PREG_SET_ORDER)) {
			foreach ($groups as $group) {
				$href = $group[1];
				if (substr($href,0,7) != 'http://') {
					if (substr($href,0,2) == './') $href = substr($href,2);
					if (substr($href,0,2) == '//')  # see slashdot.org. // => currentscheme://
						$href = $scheme.':'.$href;
					elseif (substr($href,0,1) != '/') {
						if ($this->url_base != '')
							$href = $this->url_base.$href;
						else
							$href = $fixpathnonslash.$href;
					}
					else	//href begin with '/', absolutize with hostname
						$href = $scheme.'://'.$hostname.$href;
				}
				$href = str_replace('&amp;','&',$href);
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
		$regex = "<frame .*?src=[\"']?([^\" >]*).*?>";
		if (preg_match_all('|'.$regex.'|is',$this->buffer,$groups,PREG_SET_ORDER)) {
			foreach ($groups as $group) {
				$href = $group[1];
				if (!stristr($href,'http://')) {
					if (substr($href,0,1) != '/')
						$href = $fixpathnonslash.$href;
					else	//href begin with '/', absolutize with hostname
						$href = 'http://'.$hostname.$href;
				}
				$links[] = array('href'=> $href, 'text' => 'frameset', 'cleantext' => "<strong>Frame [{$group[1]}]</strong>" );
			}
		}
		$channels = array();
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
		$regex = 'www.kompas.co[^\/]+/ver1/([^/]*)/news/(\d\d)(\d\d)/(.*?)/(\d\d)(\d\d)(\d\d)';
		for ($i = 0; $i < count($links) ; $i++) {
			$link = $links[$i];
			if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
				list($year,$month,$day,$hour,$minute,$second) = array(0,0,0,0,0,0);
				list($dummy,$channel,$year,$month,$day,$hour,$minute,$second) = $group;
				$link['channel'] = $channel;
				$link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
				$channels[$channel] += 1;
				$link['href'] = str_replace('_.htm', '.htm', $link['href']);
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
				$link['href'] = str_replace('_.htm', '.htm', $link['href']);
			}
			$links[$i] = $link;
		}
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
		$regex = '//[^\.]+\.(.*?)\.com/.*?tahun/(.+?)/.*?/(.+?)/.*?/(.+?)/.*?/(.+?)/';
		for ($i = 0; $i < count($links) ; $i++) {
			$link = $links[$i];
			if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
				list($dummy,$channel,$year,$month,$day,$time) = $group;
				list($hour,$minute,$second) = nopadding_time_parser($time);
				$link['channel'] = $channel;
				$link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
				$regex_summary = $link['text'].'(.*?)<span class="summary">(.*?)</span>';
				if (preg_match("|$regex_summary|is",$this->buffer,$minigroup)) {
					if (strlen($minigroup[1]) < 300) {	# summary should be "close" to link
						$link['summary'] = $minigroup[2];
						$channel = 'Headline';
						$link['channel'] = $channel;
					}
				}
				if (preg_match('|url=(.*)|',$link['href'],$group)) $link['href'] = $group[1];
				$channels[$channel] += 1;
			}
			$links[$i] = $link;
		}
		$regex = 'www.detik.com/berita-foto/.*?/(\d\d\d\d)(\d\d)(\d\d)-(\d\d)(\d\d)(\d\d).shtml';
		$channel = 'Berita Foto';
		for ($i = 0; $i < count($links) ; $i++) {
			$link = $links[$i];
			if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
				list($dummy,$year,$month,$day,$hour,$minute,$second) = $group;
				$link['channel'] = $channel;
				$link['unixtime'] = mktime($hour,$minute,$second,$month,$day,$year);
				$regex_summary = $link['text'].'.*?<span class="summary">(.*?)</span>';
				if (preg_match("|$regex_summary|is",$this->buffer,$minigroup)) {
					$link['summary'] = $minigroup[1];
				}
				$channels[$channel] += 1;
			}
			$links[$i] = $link;
		}
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
		$regex = 'www.tempo[^.]+.com/hg/(.*?)/(\d\d\d\d)/(\d*)/(\d*)';
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
		$regex = 'www.jawapos.com/index.php\?act=([^\&]+)\&';
		for ($i = 0; $i < count($links) ; $i++) {
			$link = $links[$i];
			if (preg_match('|'.$regex.'|is',$link['href'],$group)) {
				list($dummy,$channel) = $group;
				$link['channel'] = $channel;
				$channels[$channel] += 1;
				if (stristr($link['href'],'detail') === FALSE) $link['list'] = 1;
			}
			$links[$i] = $link;
		}
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
			$regex = '<span class="sectionHL">(.*?)<.*?<p><b>(.*?)<.*?<!---Start--->(.*?)<!--';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$reporter,$body) = $group; break;
			}
			$regex = 'size="4">(.*?)</font.*?<font color="#000000" face="Arial" size="2">(.*?)</font.*?<!---Start--->(.*?)&nbsp;&nbsp;&nbsp;&nbsp;';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$reporter,$body) = $group; break;
			}
			$regex = '<span class="txttagline"><br>(.*?)</span>.*?<b></b><br><P>(.*?)</p><p>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy, $title,$body) = $group; break;
			}
			$regex = '<!-- Put the news record in here -->(.*?)</font>.*?<font face="Arial, Helvetica" size="2">(.*?)<hr noshade';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<p><font face="Helvetica, Arial" size=3>(.*?)</font>.*?<font face="Arial, Helvetica" size="2">(.*?)</font>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<span class="clsfont3">(.*?)</span>.*?<span class="clsfont1">(.*?)<p align=right>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<BR><FONT size=5>(.*?)</font>.*?<FONT color=#ff0000 size=2>(.*?)</font>.*?P align="Justify">(.*?)<!-- FORM BERITA ';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$reporter,$body) = $group; break;
			}
			$regex = '<font class="subjudulberita">(.*?)</font>.*?<font class="judulberita">(.*?)</font>.*?<font class="textreporter">(.*?)</font>.*?<font class="textberita">(.*?)</blockquote>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$subtitle,$title,$reporter,$body) = $group; break;
			}
			$regex = '<font class="judulberita">(.*?)</font>.*?<font class="textreporter">(.*?)</font>.*?<font class="textberita">(.*?)\n</font>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$reporter,$body) = $group; break;
			}
			$regex ='<font class="headline">(.*?)</font>.*?<font class="copy">(.*?)</font>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<font class="judul">(.*?)</font>.*?<font class="navigasi">(.*?)</font>.*?<font class="deskripsi">(.*?)</font>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$reporter,$body) = $group; break;
			}
			$regex = '<font class="judul">(.*?)</font>.*?<font class="deskripsi">(.*?)</font>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<meta name="title" content="([^"]*?)".*?<font color=#666666>(.*?)</font></p> ';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<H1>(.*?)</H1>.*?<P>(.*)<HR>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<div class=JudulBerita>(.*?)</div>.*?<p class=BeritaBaca>(.*?)<div>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<span class=JudulRubrik>(.*?)</span>.*?<span class=JudulBerita>(.*?)</span>.*<tr><td>\s+<p>(.*?)<div class=PrintMail>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$subtitle,$title,$body) = $group; break;
			}
			$regex = '<!-- st_title -->(.*?)<!-- end_title -->.*?<!-- st_story -->(.*?)<div class="six">';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<font size="5">(.*?)</font>.*?</font><p>(.*?)</font></td>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<div id="AktualJudul">(.*?)</div>.*?<div id="AktualIsi">(.*?)</div>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<div class="pad20">.*?<br><b>(.*?)</b>.*?<br><br>(.*?)<br><br>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<font size="4" face="Times New Roman, Times, serif">(.*?)</font>.*?<font face="Arial" size="2">(.*?)<br></font><br>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<font face="Arial" size="3"><b>(.*?)</b>.*?<div align= "justify">(.*?)<td width="100%" align=center>&nbsp;<p>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<span class=judul>(.*?)</span>.*?<br><br>(.*?)<br><br>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<span class="texttitle03">(.*?)</span>.*?<span class="copy03">(.*?)</span>(.*?)</DIV><BR><BR>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$reporter,$body) = $group; break;
			}
			$regex = '<font size="5">(.*?)</font>.*?<font size="2" color="Blue">(.*?)</font>.*?</font><br><br>(.*?)</font><br><br>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$reporter,$body) = $group; break;
			}
			$regex = '<td class="title1">(.*?)</td>.*?<td class="content_biru">(.*?)</td>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
			$regex = '<p class="title">(.*?)</p>.*?<p class="body">(.*?)<p>&nbsp;</p>';
			if (preg_match('|'.$regex.'|is',$this->buffer,$group)) {
				list($dummy,$title,$body) = $group; break;
			}
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
		$this->news['url'] = $this->url;
		return True;
	}
	function QualifyLinks() {
		$links = &$this->news['links'];
		if ($this->news['channels']) {
			for ($i=0; $i < count($links) ; $i++) {
				$link = &$links[$i];
				if ($link['group'] or $link['channel'] == '') continue;
				$link['group'] = 1;
			}
		}
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
			foreach ($this->news['channels'] as $channel=>$kount) {	//  view topic news
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
					if ($link['list']) {
						echo '<li><a href="'.$_SERVER['PHP_SELF'].'?x=i&amp;anurl='.urlencode($link['href']).'">'.$link_text.'</a>'.$link['summary'].'</li>';
					}
					elseif ($link['unixtime'])  { # if this link has known date in it
						$date = date('H:i',$link['unixtime']);
						$date_delta = str_time_delta($link['unixtime']);
						if ($date == '00:00')
							$date_exp = $date_delta;
						else
							$date_exp = $date.', '.$date_delta;
						echo '<li><a href="'.$_SERVER['PHP_SELF'].'?unxdt='.urlencode($link['unixtime']).'&anurl='.htmlentities(urlencode($link['href'])).'" target="'.$frame_target.'">'.$link_text.'</a>'.$link_summary.' -- '.$date_exp.'</li>';
					}
					else
						echo '<li><a href="'.$_SERVER['PHP_SELF'].'?anurl='.urlencode($link['href']).'" target="'.$frame_target.'">'.$link_text.'</a>'.$link_summary.'</li>';
					$link['printed'] = 1;
				}
				echo '</ul>';
			}
		}
		echo '<hr>';
		echo '<ul>';
		for ($i = 0; $i < count($links) ; $i++) {
			$link = &$links[$i];
			if ($link['group'] != 2 or $link['printed'] or $link['score'] < $stat_avg or $link['score2'] < $stat_avg2) continue;
			echo '<li><a href="'.$_SERVER['PHP_SELF'].'?x=i&anurl='.urlencode($link['href']).'">'.$link['cleantext'].'</a></span> - <a href="'.$_SERVER['PHP_SELF'].'?anurl='.urlencode($link['href']).'" target="'.$frame_target.'">&gt;&gt;</a></li>';
			$link['printed'] = 1;
		}
		echo '</ul>';
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
		echo '</div>';
		if ($with_header) {
			$footsy = array();
			$footsy[] = 'rgxid '.$this->news['rgxid'];
			echo show_footer($this->news['url'],$footsy);
		}
	}
	function RenderListRss ($with_content=False, $link_to_orig=True) {
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
#~ httpcache_by_lastupdate();
function ShowAboutPage($page) {
	global $contributors,$app;
	if ($page == '1') {
		foreach ($contributors as $c)
			$con .= '<li><a href="mailto:'.$c[1].'">'.$c[0].'</a>, '.$c[2].'</li>';
		$temp = array();
		foreach (preg_split('/^-\s*/m',$app['version_description']) as $row)
			$temp[] = '<li>'.$row.'</li>';
		array_shift($temp);
		$last_changes = join('',$temp);
		echo <<< __E__
<div id="main">
<h2>Tentang Detik.Usable</h2>
<p>Awalnya...."dipicu oleh salah satu disain <a href="http://www.detik.com">situs berita populer di Indonesia</a> yang menurut opini kami menyengsarakan
pengunjung yang datang dengan niat untuk membaca berita, script ini ingin menunjukkan potensi sebuah situs berita yg <em>usable</em>: cepat, bersih,
dan mudah digunakan".</p>
<p>Teknis cara kerjanya adalah dengan pencocokan <em>regular expression</em> terhadap pola kode HTML di semua halaman sumber untuk mendapatkan judul,
deskripsi, tanggal, link, dan isi berita, lalu dikeluarkan dalam format HTML baru. Aspek yang inheren dalam metode ini adalah tingginya maintenance terhadap sedikit perubahan pada susunan HTML di situs sumber,
sehingga kegagalan parsing akan kadang-kadang anda temui selama menggunakan script ini. Pada situasi demikian, anda dengan mudah dapat membuka
halaman orisinilnya sebagai ganti.</p>
<h2>Pasang Sendiri</h2>
<p>Bila Anda ingin memasang detik.usable untuk sendiri, anda dapat menyalin <a href="{$_SERVER['PHP_SELF']}?x=s" target="_top">source code dari situs ini</a>
(hanya satu file .php), atau mengambil <a href="{$app['update_url']}">kode sumber terbaru dari pusat</a>, lalu upload ke web hosting yg mendukung <a href="http://php.net">php</a>, dan selesai!
</p>
<p>Mengingat script ini berpotensi melakukan penerbitan ulang materi berhak cipta, maka kami menghimbau anda menggunakan materinya <em>hanya untuk keperluan yang wajar, atau pribadi</em>,
-- sebagai mana sebuah web browser -- dan tidak mempublikasikannya kepada umum tanpa seijin pemegang hak.</p>
<p>Kami tidak bertanggung jawab atas segala efek dan akibat dari penggunaan script ini.</p>
</div>
<div id="secondary">
<h2>Perubahan Terakhir</h4>
<ul>{$last_changes}</ul>
<h2>Materi Terkait</h2>
<ul>
<li><a href="http://groups-beta.google.com/group/detikusable/subscribe">Milis detik.usable</a> (pengumuman rilis baru)</li>
<li>Tampilan <a href="http://miaw.tcom.ou.edu/~dody/du/images/DetikScreen1.png">screenshot di PDA</a> (<a href="mailto:pursena@advokasi.com">Bagus Pursena</a>)</li>
</ul>
<h2>Kontributor</h2>
<p>Mohon maaf bila ada yang terlewat:</p>
<ul>{$con}</ul>
<h2>Lisensi</h2>
<p>Lisensi script ini adalah milik umum (<em>public domain</em>) dan Anda dipersilahkan memodifikasi dan mempergunakannya tanpa batas.</p>
</div>
__E__;
	}
}
function ShowAnyNewsPage($page) {
	global $an_m;
	$an_welcome_list = array();
	foreach ($an_m as $m)
		$an_welcome_list[] = '<li><a href="'.$_SERVER['PHP_SELF'].'?x=i&anurl='.$m['url'].'">'.$m['name'].'</a> </li>';
	$an_welcome_list = join('',$an_welcome_list);
	echo <<< __E__
<div id="main">
<div id="content-headlines">
<h2>AnyNews<sup>Beta</sup></h2>
<div id="nav">
<ul>
{$an_welcome_list}
</ul>
</div>
<form action="{$_SERVER['PHP_SELF']}">
<input type="hidden" name="x" value="i">
<p>Situs lain: <input type="text" name="anurl" value="http://" size="30"><input type="submit" value="Buka"></p>
</div>
</div>
<div id="secondary"><div id="content-channels">
<h2>Tentang AnyNews</h2>
<p>AnyNews adalah pendekatan pengenalan elemen daftar berita, dengan cara pencocokan <em>regular expression</em> di dalam komponen URI (hyperlink) sebuah situs, pengelompokan kanal berita, dan analisa statistik berdasarkan panjang karakter URI (makin panjang, makin cenderung merujuk ke berita). Hal ini kontras dengan Detik.Usable yang hanya melakukan pecocokan <em>regular expression</em> terhadap pola kode sumber HTML.</p>
<p>Karena hanya pola URI yang diperlukan, AnyNews
1) lebih tahan terhadap perubahan layout/pola HTML,
2) lebih sederhana <em>regular expression</em>-nya,
3) namun seperti Detik.Usable, elemen non-link seperti isi/detail berita tetap harus dikenali dengan teknik <em>regular expression</em> ke layout/kode sumber HTML.
<p>Oleh sebabnya lebih cocok untuk keperluan murni tampilan daftar/list judul berita, misal untuk RSS.</p>
</form>
</div></div>
__E__;
}
# test to make sure cache is possible
# 1. check if ./cache folder exist and writeable
# 2. if not, check if ./ folder is writeable
if ($app['cache']) {
	if (file_exists('./cache')) {
		if  (!is_writeable('./cache')) {
			$app['cache'] = FALSE;
			$list_footer = '<p class="tips"><strong>Optimization tips:</strong> You would get better performance (via caching) by making sure this script can write in current folder to put the cache files. To do this, ssh/ftp to current folder, and do a <strong>chmod 777 ./cache</strong></p>'.$list_footer;
		}
	}
	else {
		if  (!is_writeable('.')) {
			$app['cache'] = FALSE;
			$list_footer = '<p class="tips"><strong>Optimization tips:</strong> You would get better performance (via caching) by making sure this script can write in current folder to put the cache files. To do this, ssh/ftp to current folder, and either do a <strong>chmod 777 .</strong> OR <strong>mkdir cache; chmod 777 ./cache</strong></p>'.$list_footer;
		}
	}
}
if ($_REQUEST['pda']) {
	$_REQUEST['x'] = 'i';
	$_REQUEST['no'] = 'frame';
}
if (isset($_REQUEST['url'])) {
	$_REQUEST['param1'] = trim(mystripslashes($_REQUEST['param1']));
	$du = new DetikUsable($_REQUEST['url']);
	$du->SetModeDetail();
	if ($_REQUEST['cache_reload']) $du->SetSourceOrig();
	if ($app['proxy_mode']) { $du->SetModeNode(); $du->SetNodeServerUrl($app['proxy_url']);}
	$du->GetBuffer();
	if ($du->last_error == ERROR_SOCKET) {
		echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="60; URL='.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">');
		echo '<h3>Whoops</h3>';
		echo '<p>We are sorry, for some reason we were not able to contact this address: <a href="'.$du->url.'"><b>'.$du->url.'</b> </a>.<p>You have a few options:<ul><li>Do nothing for 60 seconds and I will retry it again.<li><a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">Retry immediately</a><li><a href="'.$du->url.'">Open the original page</a> to see if it\'s up<li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
		echo '<p>Thank you.';
		show_footer();
		die();
	}
	$ret = $du->Parse();
	if (!$ret) {
		echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="10; URL='.$du->url.'">');
		echo '<h3>Whoops</h3>';
		echo '<p>We are sorry, we could not parse the page detail properly. You have a few options:<ul><li>Do nothing for 10 second and I will redirect to original page.<li><a href="'.$du->url.'">Open the original page immediately</a><li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
		echo '<p>Thank you.';
		show_footer();
		die();
	}
	else {
		if ($du->from_cache) {
			httpcache_by_lastupdate($du->news['cache']);
		}
		if ($_REQUEST['as_node']) $du->RenderNode($_REQUEST['cu']);
		elseif ($_REQUEST['wap']) $du->RenderDetailWap();
		else {
			ShowHeader();
			$du->Render();
			show_footer($du->url);
		}
	}
}
elseif ($_REQUEST['x']=='y') {
	$page = $_REQUEST['page'] == ''? '1': $_REQUEST['page'];
	ShowHeader();
	ShowAnyNewsPage($page);
	show_footer();
}
elseif ($_REQUEST['x'] == '' and $_REQUEST['anurl'] != '') {
	$du = new AnyNews($_REQUEST['anurl']);
	if ($_REQUEST['cache_reload']) $du->SetSourceOrig();
	$du->SetModeDetail();
	$du->GetBuffer();
	if ($du->last_error == ERROR_SOCKET) {
		echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="60; URL='.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">');
		echo '<h3>Whoops</h3>';
		echo '<p>We are sorry, for some reason we were not able to contact this address: <a href="'.$du->url.'"><strong>'.$du->url.'</strong> </a>.<p>You have a few options:<ul><li>Do nothing for 60 seconds and I will retry it again.<li><a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">Retry immediately</a><li><a href="'.$du->url.'">Open the original page</a> to see if it\'s up<li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
		echo '<p>Thank you.';
		show_footer();
		die();
	}
	if (!$du->Parse()) {
		echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="10; URL='.$du->url.'">');
		echo '<p>We are sorry, we could not parse the page detail properly. You have a few options:<ul><li>Do nothing for 10 second and I will redirect to original page.<li><a href="'.$du->url.'">Open the original page immediately</a><li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
		echo '<p>Thank you.';
		show_footer();
	}
	else {
		$du->Render();
	}
}
elseif ($_REQUEST['x']=='i' and $_REQUEST['anurl'] != '') {
	$du = new AnyNews($_REQUEST['anurl']);
	$du->SetModeList();
	$du->cache_prefix = 'an_';
	if ($_REQUEST['cache_reload'])
		$du->SetSourceOrig();
	$du->GetBuffer();
	if ($du->last_error == ERROR_SOCKET) {
		echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="60; URL='.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">');
		echo '<h3>Whoops</h3>';
		echo '<p>We are sorry, for some reason we were not able to contact this address: <a href="'.$du->url.'"><strong>'.$du->url.'</strong> </a>.<p>You have a few options:<ul><li>Do nothing for 60 seconds and I will retry it again.<li><a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">Retry immediately</a><li><a href="'.$du->url.'">Open the original page</a> to see if it\'s up<li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
		echo '<p>Thank you.';
		show_footer();
		die();
	}
	$title_name = $du->url;
	$title_usable = '';
	foreach ($an_m as $am) {
		if ($am['url'] == $title_name) {
			$title_name = $am['name'];
			$title_usable = '.usable';
		}
	}
	$du->SetTitleName($title_name);
	if ($_REQUEST['no'] == 'rss2')
		$du->RenderListRss($_REQUEST['complete']);
	else {
		$rss_url = $_SERVER['PHP_SELF'].'?x=i&anurl='.urlencode($du->url).'&no=rss2';
		ShowHeader('<link rel="alternate" title="'.$title_name.' RSS" href="'.$rss_url.'" type="application/rss+xml">');
		$du->Render();
		flush();
		$footsy = array(
			'<a href="'.$rss_url.'">rss</a>'
			,'<a href="'.$rss_url.'&complete=1">rss complete</a>'
			);
		show_footer($du->url,$footsy);
	}
}
elseif ($_REQUEST['x']=='i' or $_REQUEST['no']=='frame' or $_REQUEST['no']=='gm' or $_REQUEST['no']=='rss2' or $_REQUEST['no']=='wap') {
$du = new DetikUsable();
if ($app['proxy_mode']) {
	$du->SetModeNode();
	$du->SetNodeServerUrl($app['proxy_url']);
	$du->SetModeList();
	$du->GetBuffer();
	$du->Parse();
}
else {
	$du->SetModeList();
	if ($_REQUEST['cache_reload'])
		$du->SetSourceOrig();
	$du->GetBuffer();
	if ($du->last_error == ERROR_SOCKET) {
		echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="60; URL='.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">');
		echo '<h3>Whoops</h3>';
		echo '<p>We are sorry, for some reason we were not able to contact this address: <a href="'.$du->url.'"><b>'.$du->url.'</b> </a>.<p>You have a few options:<ul><li>Switch to another available servers:<ul><li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('dudul'=>0)).'">jkt.detik.com</a><li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('dudul'=>1)).'">jkt1.detik.com</a><li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('dudul'=>2)).'">jkt2.detik.com</a><li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('dudul'=>3)).'">jkt3.detik.com</a></ul><li>Do nothing for 60 seconds and I will retry it again.<li><a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">Retry immediately</a><li><a href="'.$du->url.'">Open the original page</a> to see if it\'s up<li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
		echo '<p>Thank you.';
		show_footer();
		die();
	}
	$du->Parse();
}
if ($_REQUEST['as_node'])
	$du->RenderNode($_REQUEST['cu']);
elseif ($_REQUEST['no'] == 'rss2')
	$du->RenderListRss($_REQUEST['complete']);
elseif ($_REQUEST['no'] == 'wap')
	$du->RenderListWap();
else {
	$rss_url = $_SERVER['PHP_SELF'].'?no=rss2';
	echo HtmlHeader(2,'<link rel="alternate" title="Detik.Usable RSS" href="'.$rss_url.'" type="application/rss+xml">');
	echo '<div>';
	echo '<h1><a href="'.$_SERVER['PHP_SELF'].'?x=w" title="Home" target="_top">detik.usable</a></h1>';
	echo '<p>'.$tgl_lengkap.'</p>';
	echo '<p><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('cache_reload'=>1)).'">Reload</a>';
	echo '</div>';
	if ($du->news['cache']) echo '<div id="cache_set">';
	$du->Render();
	$du->RenderAds();
	flush();
	if ($du->news['cache']) echo '</div>';
	if ($du->news['cache'] and (time() - $du->news['cache'])>$du->newslist_cache_revalidate and !$_REQUEST['pda']) {
		$du->SetSourceOrig(); $du->GetBuffer(); $du->Parse();
		echo '<div id="latest_set" style="display:none;">'; $du->Render(); echo '</div>';
		echo '<script>document.getElementById("latest_set").style.display = "";document.getElementById("cache_set").style.display = "none";</script>';
	}
	$footsy = array(
		'<a href="'.$rss_url.'">rss</a>'
		,'<a href="'.$rss_url.'&complete=1" title="Note: could be much slower">rss complete</a>'
		,'<a href="'.$_SERVER['PHP_SELF'].'?no=wap" title="WAP mode buat handphone jadul">wap version</a>'
		,'<a href="'.$_SERVER['PHP_SELF'].'?pda=1" title="PDA/Handphone yang tidak mendukung javascript">small screen version</a>'
		);
	show_footer($du->url,$footsy);
}
}
elseif ($_REQUEST['x']=='w') {
	$page = $_REQUEST['page'] == ''? '1': $_REQUEST['page'];
	ShowHeader();
	ShowAboutPage($page);
	show_footer();
}
elseif ($_REQUEST['x']=='css') {
echo <<<__E__
body{margin:0;padding:0;font-family:"Bitstream Vera Sans",Verdana,Arial,sans-serif;color:#333;}
a:hover{background:#cec;}
#header {background:#ded;margin:0;padding:0;}
#header h1 {margin:0;padding:0;}
#header p {margin:0;padding:0;}
#header .date {color:#666;font-size:90%;float:right;font-weight:bold;margin-right:0.5em;}
#header h1 a {text-decoration:none;}
#header .detik {color:#911;}
#header .usable {color:#191;}
#header .usable:after { content: " {$app['version']}"; font-weight:bold;color:#aaa;}
#nav {}
#nav ul {padding: 0;margin: 0 0 0 1em;}
#nav li {display: inline;list-style: none;}
#nav li:first-child:before { content: ""; }
#nav li:before { content: "| "; }
#main{float:left;width:50%;border-right:solid 1px #aaa;}
#content-headlines {margin:0.5em;}
#content-headlines .block {padding-bottom:0.2em;}
#content-headlines h3 {margin:0;padding:0;font-size:110%;display:inline;}
#content-headlines h3 a {text-decoration:none;}
#content-headlines h3:after { content: " = ";}
#content-headlines p {margin:0;padding:0;display:inline;}
#content-headlines .date {margin-left:0.5em;color:#777;font-size:80%;}
#content-oldernews {margin:0 0.4em;float:left;}
#content-oldernews h3 {margin:0;padding:0;font-size:95%;display:inline;}
#content-oldernews h3 a {text-decoration:none;}
#content-oldernews p {margin:0;padding:0;display:inline;}
#content-oldernews .date {margin-left:0.5em;color:#777;font-size:80%;}
#secondary {width:48%; float:right;margin-right:0.5em;}
#content-channels {}
#content-channels h2 {margin:0;padding:0;font-size:95%;color:#999;}
#content-channels ul {padding: 0;margin: 0 0 0 0em;}
#content-channels li {list-style: none;}
#content-channels .block {margin-top:0.5em;}
#content-channels li:after { content: ".."; font-weight:bold;}
#content-channels a {text-decoration:none;}
#ads {margin-top:1em; border:solid 1px #aab;background:#eef;padding:0.5em;}
#ads a {text-decoration:none;}
#ads h2 {margin:0 0 0.2em 0;padding:0;font-size:95%;color:#999;text-align:center;float:right;}
#ads ul {padding: 0;margin: 0 0 0 0em;}
#ads li {display: inline;list-style: none;}
#ads li:first-child:before { content: ""; }
#content-detail {margin:1em;}
#content-detail h2 {margin:0;padding:0;font-size:120%;}
#content-detail .date {margin:0;color:#777;font-size:80%;}
#content-detail .body {}
#content-detail ul {padding: 0;margin: 0 0 0 0em;}
#content-detail li {display: inline;list-style: none;}
#content-detail li:first-child:before { content: ""; }
#content-detail li:before { content: "| "; }
#footer {clear:both;}
#footer p {padding:0.5em;margin-top:2em;padding:0;background:#ded;}
#info {border:solid 1px #aca;background:#efe;padding:0.5em;width:50%;margin:1em;}
#info h1 {margin:0 0 0.2em 0;padding:0;font-size:110%;color:#363;text-align:center;}
__E__;
}
elseif ($_REQUEST['x']=='s') {
	show_source(__FILE__);
}
elseif ($_REQUEST['au']) {
	$target_filename = basename(__FILE__);
	ShowHeader();
	echo '<div id="info">';
	echo '<h1>Update Versi Baru</h1>';
	echo '<ul>';
	echo '<li>Memeriksa izin tulis...';
	if (!is_writable(__FILE__)) {
		echo 'Gagal</li>';
		echo '<li>Uji merubah izin tulis...';
		if (@!chmod(__FILE__,0777)) {	//test ubah permission
			echo 'Gagal</li>';
			echo '<li>Uji menyimpan ke nama file lain di direktori yang sama...';
			if (!is_writable(dirname(__FILE__))) {  // coba simpan ke file yg berbeda di folder yg sama
				echo 'Gagal</li>';
				echo '</ul><p>Menyerah</p>';
				echo '<p>Maaf, program ini tidak memiliki izin tulis ke "'.__FILE__.'". Coba rubah file permission-nysa: <strong>chmod 777 '.__FILE__.'</strong>.</p>';
				echo '</div>';
				show_footer();
				die();
			}
			else {
				$target_filename = 'index2.php';
				echo 'OK. Update akan ditulis ke file '.dirname(__FILE__).'/'.$target_filename.'</li>';
			}
		}
		else {
			echo 'OK</li>';
		}
	}
	else {
		echo 'OK</li>';
	}
	flush();
	$url_parsed = parse_url($app['update_url']);
	$port = $url_parsed['port']!=''? $url_parsed['port'] : 80;
	$sock = new DuSock($url_parsed['host'],$port);
	$addr = $url_parsed['scheme'].'://'.$url_parsed['host'].':'.$port;
	echo '<li>Menghubungi repositori di <a href="'.$addr.'">'.$addr.'</a> ...';
	flush();
	if (!$sock->socket_open()) {
		echo 'Gagal</li>';
		echo '</ul><p>Menyerah</p>';
		echo '</div>';
		show_footer();
		die();
	}
	echo 'OK</li>';
	$addr_wp = $addr.$url_parsed['path'].($url_parsed['query']==''?'':'?'.$url_parsed['query']);
	echo '<li>Mengambil versi terakhir di <a href="'.$addr_wp.'">'.$addr_wp.'</a> ...';
	$sock->sock_send_request($url_parsed['path'].'?'.$url_parsed['query']);
	$sock->sock_recv_header();
	$buffers = $sock->sock_recv_all();
	if ($buffers == '') {
		echo 'Gagal</li>';
		echo '</ul><p>Menyerah</p>';
		echo '</div>';
		show_footer();
		die();
	}
	echo 'OK</li>';
	if (!$_REQUEST['commit']) {
		echo '<li>Memeriksa versi ...';
		if (preg_match('/\$app\[\'version\'\]\s*=\s*"([^"]*)"/i',$buffers,$remote_res))
			$remote_version = $remote_res[1];
		else
			$remote_version = '0.0';
		list($remote_major, $remote_minor) = explode('.',$remote_version,2);
		list($local_major, $local_minor) = explode('.',$app['version'],2);
		echo 'detik.usable ini: '.$local_major.'.'.$local_minor.', yang terbaru: '.$remote_major.'.'.$remote_minor.'</li>';
		echo '</ul>';
		echo '<form method="get" action="'.$_SERVER['PHP_SELF'].'"><input type="hidden" name="au" value="1"><input type="hidden" name="commit" value="1">';
		if ($remote_major > $local_major or ($remote_major == $local_major and $remote_minor > $local_minor)) {
			echo '<p>Versi yang lebih baru telah tersedia. <input type="submit" value="Update ke '.$remote_version.'">';
		}
		else {
			echo '<p>detik.usable ini sudah versi terbaru. Namun bila mau, <input type="submit" value="Paksa perbarui lagi"></p>';
		}
		echo '</form>';
		echo '</div>';
		show_footer();
		die();
	}
	else {
		$target = dirname(__FILE__).'/'.$target_filename;
		echo '<li>Menulis ke '.$target.' ...';
		$fp = fopen($target,'w');
		fwrite($fp,$buffers);
		fclose($fp);
		echo 'OK</li>';
		$redirect = dirname($_SERVER['PHP_SELF']).'/'.$target_filename;
		$redirect = str_replace('//','/',$redirect);
		echo '</ul><p>Update selesai. <a href="'.$redirect.'">Buka ulang detik.usable</a> untuk melihatnya.</p>';
		echo '</div>';
		show_footer();
		die();
	}
}
elseif ($_REQUEST['cm']) {
	if (!$_REQUEST['commit'] or ($_REQUEST['confirm_text'] != $_REQUEST['confirm_text2'])) {
		$dirsize = 0;
		$dh = opendir('cache');
		while (false !== ($filename = readdir($dh))) if (($file_name != '.' && $file_name != '..')) $dirsize += filesize('cache/'.$filename);
		$cache_size = round($dirsize/1024,2);
		$crazy_number = rand(1000,9999);
		ShowHeader();
		echo '<div id="info">';
		echo '<h1>Hapus Cache</h1>';
		echo '<form action="'.$_SERVER['PHP_SELF'].'"><input type="hidden" name="cm" value="1"><input type="hidden" name="commit" value="1">';
		echo '<p>Cache saat ini menempati ruang sebesar '.$cache_size.' KB. Ketik angka <strong>'.$crazy_number.'</strong> bila yakin ingin mengosongkan.</p>';
		if ($_REQUEST['confirm_text'] != $_REQUEST['confirm_text2']) {
			echo '<p>Angka belum benar, coba lagi.</p>';
		}
		echo '<input type="text" name="confirm_text"><input type="hidden" name="confirm_text2" value="'.$crazy_number.'"> <input type="submit" value="Kosongkan"></form>';
		echo '</div>';
		show_footer();
	}
	else {
		$dh = opendir('cache');
		while (false !== ($filename = readdir($dh))) if (($file_name != '.' && $file_name != '..')) @unlink('cache/'.$filename);
		ShowHeader();
		echo '<h1>Cache telah dikosongkan</h1>';
		echo "<p><a href={$_SERVER['PHP_SELF']}?x=w>Kembali ke awal</a></p>";
	}
}
elseif ($_REQUEST['no'] == 'bcache') {
	$display = '';
	if (($dh = @opendir('cache')) === FALSE) {
		ShowCacheBrowseWarning();
	}
	$cached_news = array();
	while (false !== ($filename = readdir($dh))) {
		if ($filename == '.' or $filename == '..') continue;
		ob_start();
		readfile('cache/'.$filename);
		$buffer = ob_get_contents();
		ob_end_clean();
		$news = unserialize($buffer);
		unset($news['content']);
		if ($news['date'] == '')
			$str_date = 'unknown';
		else {
			$tgl = getdate($news['date']);
			$str_date = mktime(0,0,0,$tgl['mon'],$tgl['mday'],$tgl['year']);
		}
		$cached_news[$str_date][] = $news;
	}
	if (!$cached_news) {
		ShowCacheBrowseWarning();
	}
	ShowHeader();
	krsort($cached_news,SORT_NUMERIC);
	function cmp_by_date($a,$b) {
		if ($a['date'] == $b['date']) {
			return 0;
		}
		return ($a['date'] < $b['date']) ? 1 : -1;
	}
	echo '<div id="content-headlines">';
	foreach ($cached_news as $str_date=>$news_list) {
		if ($str_date == 'unknown') continue;
		$unx_date_group = $str_date;
		$str_date = $hari[date('w',$unx_date_group)].',&nbsp;'.date('j',$unx_date_group).'&nbsp;'.$bulan[date('n',$unx_date_group)].date(' Y',$unx_date_group);
		echo '<p class="date">'.$str_date.'</p>';
		usort($news_list,'cmp_by_date');
		echo '<ul>';
		foreach ($news_list as $news) {
			$dateme = date('H:i',$news['date']);
			if ($dateme == '00:00') $dateme = '';
			else $dateme .= ' - ';
			$title = trim(strip_tags($news['title']));
			if ($title == '') $title = 'Tanpa Judul';
			echo '<li>'.$dateme.'<a href="'.$_SERVER['PHP_SELF'].'?url='.urlencode($news['url']).'" target="m">'.$title.'</a></li>';
		}
		echo '</ul>';
	}
	if ($cached_news['unknown']) {
		echo '<p class="date">Tanpa Tanggal</p>';
		echo '<ul>';
		foreach ($cached_news['unknown'] as $news) {
			$title = trim(strip_tags($news['title']));
			if ($title == '') $title = 'Tanpa Judul';
			echo '<li><a href="'.$_SERVER['PHP_SELF'].'?url='.urlencode($news['url']).'" target="m">'.$title.'</a></li>';
		}
		echo '</ul>';
	}
	echo '</div>';
	show_footer();
}
#~ elseif ($_REQUEST['x']=='x') {
else {
	$_REQUEST['x']='x';
$du = new DetikUsable();
$du->SetModeList();
if ($_REQUEST['cache_reload'])
	$du->SetSourceOrig();
$du->GetBuffer();
if ($du->last_error == ERROR_SOCKET) {
	echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="60; URL='.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">');
	echo '<h3>Whoops</h3>';
	echo '<p>We are sorry, for some reason we were not able to contact this address: <a href="'.$du->url.'"><b>'.$du->url.'</b> </a>.<p>You have a few options:<ul><li>Switch to another available servers:<ul><li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('dudul'=>0)).'">jkt.detik.com</a><li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('dudul'=>1)).'">jkt1.detik.com</a><li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('dudul'=>2)).'">jkt2.detik.com</a><li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('dudul'=>3)).'">jkt3.detik.com</a></ul><li>Do nothing for 60 seconds and I will retry it again.<li><a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">Retry immediately</a><li><a href="'.$du->url.'">Open the original page</a> to see if it\'s up<li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
	echo '<p>Thank you.';
	show_footer();
	die();
}
$du->Parse();
$rss_url = $_SERVER['PHP_SELF'].'?no=rss2';
ShowHeader('<link rel="alternate" title="Detik.Usable RSS" href="'.$rss_url.'" type="application/rss+xml">');
echo <<<__E__
<script>
function getit(url) {
	obj = document.getElementById("secondary");
	request_obj = new XMLHttpRequest();
	request_obj.open('GET',url,false);
	request_obj.send(null);
	res = request_obj.responseText;
	obj.innerHTML = res;
}
</script>
__E__;
$du->Render();
$footsy = array(
	'<a href="'.$rss_url.'">rss</a>'
	,'<a href="'.$rss_url.'&complete=1">rss lengkap</a>'
	,'<a href="'.$_SERVER['PHP_SELF'].'?no=wap">versi wap</a>'
	,'<a href="'.$_SERVER['PHP_SELF'].'?pda=1">versi layar kecil</a>'
	);
show_footer($du->url,$footsy);
}
?>
