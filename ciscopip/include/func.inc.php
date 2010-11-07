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
    /*
    @level: int
            -2 available even when not authenticated,
            -1 always available when authenticated,
            1 highest level of permission,
            2+ lower and lower permission
    */
    global $user_access; # setted at useraccess.inc.php, then index.php

    if ($_SESSION['login_ok'] != 1 and $level != -2) return;
    if ($_SESSION['login_ok'] == 1 and $level != -1 and $_SESSION['login_level'] > $level) return;  # level permission, allow if login_level is smaller
    if ($_SESSION['login_level'] != 0 and $_SESSION['login_level'] == $level and $group != '') {   # check group permission if not '' or empty array
        if (is_array($group)) {
            $pass = False;
            foreach ($group as $grp) {
                if ($grp == $_SESSION['login_group']) {
                    $pass = True;
                    break;
                }
            }
            if (!$pass) return;
        }
        elseif ($group != $_SESSION['login_group']) return;
    }

}


function parse_user_access($data) {
    /*
    parse data into list of these: array('m'=>'usrmgr', 'label'=>'User Manager', 'level'=>1, 'groups'=>array('PO','DE'), 'default_access'=>'deny');
    */
    $rows = explode("\n",$data);
    $temp_arr = array();
    foreach ($rows as $row) {
        $row = explode(',',$row);
        $row = array_map('trim',$row); #make sure it's clean and dandy
        list($m,$label,$level,$groups) = $row;
        if ($groups) $groups = explode('^',$groups);
        $temp_arr[] = array('m'=>$m, 'label'=>$label, 'level'=>$level, 'groups'=>$groups);
    }
    return $temp_arr;
}

function check_no_double_login() {
    /*
    */

}

?>
