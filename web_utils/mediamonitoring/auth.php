<?
/* check user login authorization
 copyright 2004 - dody suria wijaya, dsw software house - contact: dodysw@gmail.com */
    session_start();
    if ($_SESSION['mmclient_login_ok'] != 1) {
        #~ session_destroy();
        #~ $go = urlencode($GLOBALS['full_self_url']);
        #~ echo 'asdasd';
        header('Location: login.php');
        exit;
    }
?>