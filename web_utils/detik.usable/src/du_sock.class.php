<?
class DuSock {
    var $user_agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1) ';
    function DuSock ($host,$port=80) {
        global $app;
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
        $this->iter_302 = array();    # increment for every redirection
        $this->max_iter_302 = 10;   # safeguard, to avoid infinite redirection
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
            #~ echo 'sock_open: can\'t connect';
            if ($errno == 0) {
                #~ echo 'sock_open: problem before connect (dns/socket)';
            }
            else {
                #~ echo 'sock_open: problem trying to connect (hostname notfound, blocked, downed, busy, or timeout)';
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
        #~ echo '<hr><pre>'.htmlentities($http_req).'</pre>';   #DEBUG
        $return = fputs ($this->fp, $http_req);
        if ($return == -1) {
            #~ echo 'http_req: can\'t send'
            return FALSE;
        }
        else {
            return TRUE;
        }
    }
    function sock_recv_header () {
        //return HTTP response header
        $buffers = '';
        while (!feof ($this->fp)) {
            $buffer = fgets($this->fp, 65536 );
            if ($buffer == "\r\n") break;
            $buffers .= $buffer;
        }
        #~ echo '<hr><pre>'.htmlentities($buffers).'</pre>';
        $temp = explode('\n',$buffers,2); # get first line, split into three token
        list($http, $respcode, $respdesc) = explode(' ',$temp[0]);
        if ($respcode == '302') {  //redirecting
            if (count($this->iter_302) >= $this->max_iter_302)
                die('<p>Max redirection iteration exceeded. Locations:<br>'.implode('<br>',$this->iter_302));
            # do something...
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
            $this->iter_302 = array();  # reset iter flag
        }
        else {
            #~ echo 'Invalid HTTP Response';    # wartaekonomi website err, return 500 though looks ok
            #~ return false;
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
?>