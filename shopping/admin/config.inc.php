<?
/* global configuration
 copyright 2004 - dody suria wijaya, dsw software house - contact: dodysw@gmail.com */

    /* CONSTANTS */
    define('AADM_VERSION', '1.2.0');

    /* GENERAL */
    $appconf['default_module'] = 'admfp';

    include 'config_db.inc.php';

    /* uploaded (comment me if local)*/

    define('APP_PATH_ROOT',str_replace('\\', '/', dirname(__FILE__)));  # decide automatically, override if not correct
    define('APP_INCLUDE_ROOT',APP_PATH_ROOT.'/include');  # decide automatically, override if not correct
    define('APP_MODULE_ROOT',APP_PATH_ROOT.'/modules');  # decide automatically, override if not correct

    /* FORMATTING */
    $appconf['site_title'] = 'al-admin '.AADM_VERSION;
    $appconf['site_description'] = '';

    $appconf['max_idle_time'] = 0;    # int, (seconds) used by auto_logout and no_double_login. to disable auto_logout, set max_idle_time to 0
    $appconf['no_double_login'] = False; # inside max_idle_time duration, the same user is not allowed to do a login, counted from user's last activity datetime.

    $GLOBALS['dbpre'] = $appconf['dbprefix'];
    $GLOBALS['server_protocol'] = $_SERVER['HTTPS'] == "on"? 'https': 'http';
    $GLOBALS['redirect'] = 'Location: '.$GLOBALS['server_protocol'].'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/';  # http://hostname/path/to/
    $GLOBALS['fullredirect'] = 'Location: '.$GLOBALS['server_protocol'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];  # http://hostname/path/to/file.php
    $GLOBALS['full_self_url'] = $GLOBALS['server_protocol'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

    # header($GLOBALS['redirect'].'file.php?a=1')

    /* MAIL RELATED */
    $GLOBALS['mail_xmailer'] = 'al-admin';
    #$GLOBALS['mail_server'] = 'localhost';  # comment this to use default smtp on hosting account
    $GLOBALS['mail_from'] = 'from@gmail.com';  # the email address used as from: header
    $GLOBALS['mail_replyto'] = 'replyto@gmail.com';  # the email address used as replyto: header, must be entered to avoid detected as spam
    $GLOBALS['mail_to'] = 'dodysw@gmail.com';  # the email address used as from: header

    /* INIT */
    if ($GLOBALS['mail_server'] != '')
        ini_set('SMTP',$GLOBALS['mail_server']);

    #~ define('CRLF',"\r\n"); # conflict with htmlmimemail

    #~ error_reporting(E_ALL);
    $include_dir = 'include/';
    include $include_dir.'conn.inc.php';

    $cfg_language = 'en';
    include('include/'.$cfg_language.'.lang.inc.php');


?>