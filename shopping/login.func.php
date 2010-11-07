<?
    include 'admin/config.inc.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $password = md5($_POST['password']);
        $sql = "SELECT * FROM {$GLOBALS['dbpre']}membership_tab WHERE `username`='$username' AND `password`='$password'";
        #~ echo $sql;
        $res = mysql_query($sql) or die(mysql_error());
        if (mysql_num_rows($res) == 0) { # login failed
            print "<SCRIPT> alert('Anda salah mengisi Username dan atau Password. Ulangi lagi!'); window.history.go(-1); </SCRIPT>\n";
            #~ print "<SCRIPT>alert('Invalid username or password. '); </SCRIPT>\n";
            exit();
        }
        $row = mysql_fetch_array($res);
        session_start();
        $_SESSION['shop_login_ok'] = 1;
        $_SESSION['shop_login_id'] = $row['rowid'];
        $_SESSION['shop_login_user'] = $username;
        $_SESSION['shop_login_datetime'] = time();
        if ($_REQUEST['go'] != '') {
            header('Location: '.$_REQUEST['go']);
        }
        else
            header('Location: index.php');
        exit();
    }

    header('Location: index.php');
?>