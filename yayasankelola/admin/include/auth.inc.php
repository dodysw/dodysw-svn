<?
/* check user login authorization
 copyright 2004 - dody suria wijaya, dsw software house - contact: dodysw@gmail.com */

    if (!$_SESSION['login_ok']) {
        session_destroy();
        $go = urlencode($GLOBALS['full_self_url']);
        header($GLOBALS['fullredirect'].'?m=login&go='.$go);
        exit;
    }
?>