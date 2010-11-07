<?
/* the mother of all scripts
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include 'config.inc.php';
include_once($include_dir.'func.inc.php');
session_start();
#~ include 'perencanaan/model/entitymanager.inc.php';


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

// check m (module) param
if ($_REQUEST['m'] == '') $_REQUEST['m'] = $appconf['default_module'];  # set default
$_REQUEST['m'] = str_replace(array('.',',','/',':','&'),'',$_REQUEST['m']); # strip all non alphanumeric from m

# check permission
include $include_dir.'useraccess.inc.php';
$user_access = parse_user_access($user_access); # convert text-based to array-based format
check_module_access($_REQUEST['m']);    # make sure user is really allowed to access module

import_module($_REQUEST['m']);

$prog = new $_REQUEST['m']();
if ($prog->must_authenticated) include_once($include_dir.'auth.inc.php');
method_exists($prog,'final_init')? $prog->final_init():'';    # called after initialization, usually to provide post handler

//start output
include $include_dir.'header.inc.php';
echo '<table border="0" width="100%">';
echo '<tr><td>';
include $include_dir.'nav.inc.php';
echo '</td></tr></table>';

echo '<table border="0" width="100%">';
echo '<tr><td width="100">&nbsp;</td><td valign="top">';
include $include_dir.'left.inc.php';
echo "\r\n</td>\r\n<td valign=top>";
$prog->go();
#~ echo "\r\n</td>\r\n<td width=150 valign=top>";
echo "\r\n</td>\r\n<td valign=top>";
include $include_dir.'right.inc.php';
echo "\r\n</td>\r\n</tr>\r\n</table>";
include $include_dir.'footer.inc.php';

?>