<?
/* check user login authorization
 copyright 2004 - dody suria wijaya, dsw software house - contact: dodysw@gmail.com */
    session_start();

    if ($must_logged_in == 1 and $_SESSION['shop_login_ok'] != 1) {
        #~ session_destroy();
        $go = urlencode($GLOBALS['full_self_url']);
        header('Location: login.php?go='.$go);
        exit;
    }
?>