<?
/* the mother of all scripts
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include 'config.inc.php';
include_once($include_dir.'func.inc.php');
define('AADM_ON_BACKEND',1);    # constant to let modules/framework know whether they're being accessed through backend (1) or frontend (other value)
ob_start();
session_start();

if ($_SESSION['login_ok'] == 1 and !$_SESSION['login_super']) {
    # check (optional) auto_logout
    if ($appconf['max_idle_time'] > 0) {
        $delta = ($_SESSION['login_datetime'] + $appconf['max_idle_time'] - time());
        if ($delta < 0) { # session expired
            session_destroy();
            $go = urlencode($GLOBALS['full_self_url']);
            header($GLOBALS['fullredirect'].'?m=login&expired=1&go='.$go);
        }
    }

    # (optionally) record user's last activity datetime, used for no double login
    if ($appconf['no_double_login']) {
        $sql = "update {$GLOBALS['dbpre']}user_tab set last_activity=Now() where username='{$_SESSION['login_user']}'";
        mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
    }

}

# check m (module) param
if ($_REQUEST['m'] == '') $_REQUEST['m'] = $appconf['default_module'];  # set default
$_REQUEST['m'] = str_replace(array('.',',','/',':','&'),'',$_REQUEST['m']); # strip all non alphanumeric from m

# load navdata for use at navigator, frontpage, and permission checking
include_once($include_dir.'navdata.inc.php');

# check permission
if (!check_module_access($_REQUEST['m'])) {    # make sure user is really allowed to access module
    $go = urlencode($GLOBALS['full_self_url']);
    header($GLOBALS['fullredirect'].'?m=login&go='.$go);
    #~ die('You are not allowed to access module '.$_REQUEST['m']);
    exit();
}

$prog = instantiate_module($_REQUEST['m']);
if ($prog->must_authenticated) include_once($include_dir.'auth.inc.php');
method_exists($prog,'final_init')? $prog->final_init():'';    # called after initialization, usually to provide post handler

//start output
if (!$prog->custom_header)
    include $include_dir.'header.inc.php';

echo '<table width="100%" summary="left/middle/right body">';
if ($prog->navbar)
    echo '<tr><td valign="top" width="150" nowrap>';
else
    echo '<tr><td valign="top" nowrap>';
include $include_dir.'left.inc.php';
echo "\r\n</td>\r\n<td valign=top>";
if ($prog->navbar) {
    echo <<<__END__
<table cellpadding="0" cellspacing="0" width="100%">
<tr>
    <td valign="bottom" width="1">
    <table cellpadding="0" cellspacing="0">
        <tr>
            <td width="12"></td><td>
            </td><td>&nbsp;</td><td>
            <table cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td align="left" rowspan="2" nowrap="" title="" class="ttwm"><b>{$prog->title}</b></td><td><img src="images/tbw2.gif" alt="" border="0" height="7" width="7" align="top"></td>
                </tr>
                <tr>
                    <td valign="top" class="ttcdwm">&nbsp;</td>
                </tr>
            </table>
            </td>
        </tr>
    </table>
    </td>
    <td width="1">
__END__;
    if (!$prog->browse_mode_forced and $prog->action == 'browse') {
        $browse_mode = $_SESSION['module_browse_mode'][$prog->module] == ''? $prog->browse_mode: $_SESSION['module_browse_mode'][$prog->module];
        $mode = ($browse_mode == 'table')? array('form','Single'): array('table','Multiple');
        echo '&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?m='.$prog->module.'&set_browse_mode='.$mode[0].'">'.lang($mode[1]).'</a>';
    }
    echo '</td><td nowrap align="right">';
    if ($prog->allow_query and $prog->action == 'browse' and !$prog->logical_parent) {
        $prog->showquery();
    }
    echo '</td></tr></table>';
    echo '<table cellpadding="0" cellspacing="0"><tr><td class="mwrap">';
}
else {  # by default, assume center content when not displaying navbar
    echo '<table align="center" cellpadding="0" cellspacing="0"><tr><td class="mwrap">';
}

$prog->go();
echo '</td></tr></table>';
echo "\r\n</td>\r\n<td valign=top>";
include $include_dir.'right.inc.php';
echo "\r\n</td>\r\n</tr>\r\n</table>";
include $include_dir.'footer.inc.php';

?>