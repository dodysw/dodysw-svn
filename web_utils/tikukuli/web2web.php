<?
/*
web2web.php = proxy download
author: Dody Suria Wijaya - dodysw@gmail.com
version: 0.1
date: 25/12/2005 22:02:25
*/

function mystripslashes($val) {
    return get_magic_quotes_gpc()? stripslashes($val) : $val;
}

class DuSock {
	var $user_agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1) ';
	function DuSock ($host='',$port=80) {
		$this->host = $host;
		$this->port = $port == ''? 80: $port;
		$this->timeout = 30;
		$this->error_cant_open = '';
		$this->http_proxy_enable = '';
		$this->http_proxy_host = '';
		$this->http_proxy_port = '';
		$this->http_proxy_user = '';
		$this->http_proxy_pass = '';
		$this->iter_302 = array();
		$this->max_iter_302 = 10;
		$this->referer = '';
		$this->cookie = '';
		$this->location = '/';
		$this->header = array();
	}
	function set_url($url) {
        $u = parse_url($url);
        $this->host = $u['host'];
        $this->port = $u['port'] != ''? $u['port']: $this->port;
        $this->location = $u['path'].'?'.$u['query'];
    }
	function open () {
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
	function send_request ($location='') {
		if ($location != '') $this->location = $location;
		$http_reqs = array();
		if ($this->http_proxy_enable) {
			$header_auth = '';
			if ($this->http_proxy_user != '') {
				$header_auth = 'Proxy-Authorization: Basic '.base64_encode($this->http_proxy_user.':'.$this->http_proxy_pass);
			}
			$http_reqs[] = 'GET http://'.$this->host.':'.$this->port.$this->location.' HTTP/1.0';
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
			$http_reqs[] = 'GET '.$this->location.' HTTP/1.0';
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
	function recv_header () {
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
		$this->header = $this->_parse_header($buffers);
// 		return $buffers;
	}
	function _parse_header($str) {
        preg_match_all("|^(\S+):\s*(.*)$|im", $str, $out, PREG_SET_ORDER);
        $head = array();
        for ($i=1; $i < count($out); $i++) {
            $head[strtolower($out[$i][1])] = $out[$i][2];
        }
        return $head;
    }
	function stream_all () {
		do {	// recv all response body
		   $data = fread($this->fp, 8192);
		   echo $data;
		} while(strlen($data) != 0);
		$this->close();
    }
	function recv_all () { //receive the rest of the data, then close
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


if ($_REQUEST['url'] == '') {
    echo '<html><head><title>Download to Your Browser</title></head><body>';
    echo '<h3>PHP-Proxy Web Interface</h3>';
    echo '<p>Warning! do not let non-authorized user to access this page.';
    echo '<form method="POST">';
    echo '<p>URL: <input type=text name="url" value="'.$_GET['url'].'" size="100">';
    echo '<p><input type=submit>';
    echo '</form>';

    echo '&copy; 2005 - Dody Suria Wijaya';

    echo '</body></html>';
    exit();
}

$url = $_REQUEST['url'];

$s = new DuSock();
$s->set_url($url);
$s->open();
$s->send_request();
$s->recv_header();

header('Content-Type: '.$s->header['content-type']);
header('Content-Length: '.$s->header['content-length']);
$s->stream_all();
?>
