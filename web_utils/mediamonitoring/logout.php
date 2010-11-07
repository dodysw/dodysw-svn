<?
    session_start();
    $_SESSION['mmclient_login_ok'] = 0;
    unset($_SESSION['mmclient_login_id']);
    unset($_SESSION['mmclient_login_user']);
    unset($_SESSION['mmclient_login_datetime']);
    if ($_REQUEST['go'] != '') {
        header('Location: '.$_REQUEST['go']);
    }
    else
        header('./');
    exit();


?>