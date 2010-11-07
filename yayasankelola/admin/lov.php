<?
/* list of values
 copyright 2004,2005 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

## start 1 - the same as index ##
include 'config.inc.php';
include_once($include_dir.'func.inc.php');
ob_start();
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

# check m (module) param
if ($_REQUEST['m'] == '') $_REQUEST['m'] = $appconf['default_module'];  # set default
$_REQUEST['m'] = str_replace(array('.',',','/',':','&'),'',$_REQUEST['m']); # strip all non alphanumeric from m

# load navdata for use at navigator, frontpage, and permission checking
include_once($include_dir.'navdata.inc.php');

# check permission
check_module_access($_REQUEST['m']);    # make sure user is really allowed to access module

$prog = instantiate_module($_REQUEST['m']);
if ($prog->must_authenticated) include_once($include_dir.'auth.inc.php');
## end 1 - the same as index ##

$prog->query_only = True;
$prog->browse_mode_forced = True;
# hide all but enumerated field
foreach ($prog->properties as $k=>$v) {
    if (!in_array($v->colname,$prog->enum_keyval)) $prog->properties[$k]->hidden = True;
    $prog->properties[$k]->hyperlink = 'lov';
}

method_exists($prog,'final_init')? $prog->final_init():'';    # called after initialization, usually to provide post handler


# get program's
echo <<< __END__
<html>
<head>
<title>List of values: {$prog->title}</title>
<link rel="stylesheet" type="text/css" href="clean.css">
<script type="text/javascript">
function set_lov(val) { window.origfield.value = val; window.close(); }
</script>
</head>
<body bgcolor="#F5F5F5">
__END__;
$prog->go();

?>
<p><small><a href="javascript: window.close();">Close window</a></small>
</body>
</html>