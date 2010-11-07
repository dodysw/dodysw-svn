<?

function import_module($module) {
    include_once(APP_MODULE_ROOT.'/'.$module.'.mod.php');
}

function instantiate_module($module) {
    import_module($module);
    $obj = new $module();
    return $obj;
}

function myaddslashes($val) {
    return get_magic_quotes_gpc()? $val : addslashes($val);
}

function mystripslashes($val) {
    return get_magic_quotes_gpc()? stripslashes($val) : $val;
}

function supermailer ($h) {
    /*
    simple mail function to send text email
    */
    $mail_addheaders = array();
    $mail_addheaders[] = 'From: '.$h['from'];
    $h['replyto'] == ''? $h['from']:$h['replyto'];  # avoid anti-spam
    $mail_addheaders[] = 'Reply-To: '.$h['replyto'];
    if ($h['bcc'])
        $mail_addheaders[] = 'Bcc: '.$h['bcc'];
    $mail_addheaders[] = 'X-Mailer: '.(($GLOBALS['mail_xmailer'] == '')? 'supermailer/dsw/sh/2004': $GLOBALS['mail_xmailer']);
    $mail_addheaders_str = join("\r\n",$mail_addheaders);
    #~ echo $mail_addheaders_str;exit;

    $mail_body = $h['body'];
    $mail_to = $h['to'];
    $mail_subject = $h['subject'];

    # Some MTA confuse \r\n and translate it to \n\n, causing double lines. This will makes them happy:
    $mail_addheaders_str = str_replace("\r\n", "\n", $mail_addheaders_str);
    $mail_body = str_replace("\r\n", "\n", $mail_body);

    return mail($mail_to, $mail_subject, $mail_body, $mail_addheaders_str);

}

function debug($mix='') {
    global $prog;
    $title = is_string($mix)? $mix: '';
    $obj = is_object($mix)? $mix: False;
    echo '<pre style="background-color:eee">';
    if ($title != '')
        echo '<center><b>'.$title.'</b></center>'."\r\n";
    if ($obj)
        echo '"'.get_class($obj).'" DATASOURCE: '; print_r($obj->ds); echo "\r\n";
    echo 'PROG DATASOURCE: '; print_r($prog->ds); echo "\r\n";
    print_r($obj); echo "\r\n";
    echo 'POST: '; print_r($_POST); echo "\r\n";
    echo 'GET: '; print_r($_GET); echo "\r\n";
    #~ echo 'COOKIES: '; print_r($_COOKIE); echo CRLF;
    #~ echo 'PROG: '; print_r($prog); echo CRLF;
    #~ echo 'SESSION: '; print_r($_SESSION); echo CRLF;
    echo '</pre>';
}

function get_fullpath() {
    /* return like http://localhost/path1/path2/ */
    $path = dirname($_SERVER['PHP_SELF']);
    $path = str_replace( "\\", "/", $path); # special for windows
    # make sure to end with /
    if (substr($path,-1) != "/")
        $path .= '/';
    return $GLOBALS['server_protocol'].'://'.$_SERVER['HTTP_HOST'].$path;
}

function dbdatetime2unix ($tanggal = '') {  //format 2004-12-31 23:59:59 into unixtime as return by time()
    list($year,$month,$day,$hour,$minute,$second) = sscanf($tanggal,'%4d-%2d-%2d %2d:%2d:%2d');
    return mktime($hour,$minute,$second,$month,$day,$year);
}

function unix2dbdatetime ($tanggal = '') {  //reverse of dbdatetime2unix
    return date('Y-m-d H:i:s',$tanggal);
}

function unix2dbdate ($tanggal = '') {  //reverse of dbdatetime2unix
    return date('Y-m-d',$tanggal);
}

# --- USER ACCESS AND NAVIGATION FUNCTIONS ---

function check_module_access($m) {
    /* see navdata.inc.php for complete information
    */
    if ($m == '') return True;
    global $navdata; # set at include/navdata.inc.php, then index.php
    foreach ($navdata as $k=>$v) {
        foreach ($v as $row) {
            if ($row[0] != $m) continue;
            $level = $row[2];
            $group = $row[3];
            assert($level >= -2);
            if ($level == -1 and $_SESSION['login_ok'] != 1) return False;
            elseif ($level >= 0 and $_SESSION['login_level'] > $level) return False;
            # next check: group id
            if ($group == '') return True;
            $groups = explode('^',$group);
            if (in_array($_SESSION['login_group'],$groups)) return True;
            return False;
        }
    }
    die('Module '.$m.' permission has not been defined at navdata');
}

function check_no_double_login() {
    /*
    */
}

function lang($str) {
    global $language_array;
    if (array_key_exists($str,$language_array)) {
        return $language_array[$str];
    }
    return $str;
}


function merge_query($newquery) {
    /* merge new key/val array with query string to a new array copy and return the copy in an url ready format
    */
    foreach (array_merge($_GET,$newquery) as $k=>$v) $temparr[] = urlencode($k).'='.urlencode($v);
    return implode('&amp;',$temparr);
}


?>