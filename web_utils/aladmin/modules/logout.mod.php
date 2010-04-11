<?
/* login and logout form and handler
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

class logout {

    function logout() {
        // initialize instance
        global $html_title;
        $this->title = 'Login';
        $html_title = $this->title;

    }
    function final_init() {
        // process logout
        global $appconf;        
        if ($appconf['no_double_login']) { # for no double login
        	# first, empty the activity date
        	$sql = "update {$GLOBALS['dbpre']}user_tab set last_activity='' where username='{$_SESSION['login_user']}'";
        	mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
        }
        #~ session_start();
        $ul = instantiate_module('usrmgr_login');
        $ul->record_log($_SESSION['login_id'], 'out');
        session_destroy();
        header($GLOBALS['fullredirect'].'?m=login');
        exit;

    }
    function post_handler() {
        // called inside main content
    }

    function go() {

    }

}

?>