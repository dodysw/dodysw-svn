<?
require_once('version.inc.php');
require_once('define.inc.php');
/* create a file called config.inc.php with your own configuration that overrides all above parameters.
*/
if (file_exists('config.inc.php')) include 'config.inc.php';
require_once('function.inc.php');

//    VARIABLE DEFINITIONS
define('ERROR_SOCKET',1);
define('ERROR_LOOPINGREDIR',2);

$tz = intval(substr(date('O'),0,3));
$tz_diff = $tz - 7;
$ctime = time();
$timezone_sign = ($tz >= 0)? '+':'';
$tgl_lengkap = $hari[date('w',$ctime)].',&nbsp;'.date('j',$ctime).'&nbsp;'.$bulan[date('n',$ctime)].date(' Y',$ctime).'&mdash;'.date('H:i',$ctime).' GMT'.$timezone_sign.($tz);

function HtmlHeader($css_id=2, $meta='', $title='detik.usable') {
    global $tgl_lengkap;
    return '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><head><title>'.$title.' ('.$tgl_lengkap.')</title><link rel="stylesheet" href="'.$_SERVER['PHP_SELF'].'?x=css&x2='.$css_id.'" type="text/css" />'.$meta.'</head><body>';
}

@set_time_limit(60*5);
ob_end_flush();


$current_time = date('His', time()-(3600*$tz_diff));    # current time in detikcom server
function smaller_than_curr_time($var) {
    global $current_time;
    return ($var <= $current_time);
}
if ($_GET['dudul'] != '') {
    switch ($_GET['dudul']) {
        case 0: $app['url_list'] = 'http://jkt.detik.com/index.php'; break;
        case 1: $app['url_list'] = 'http://jkt1.detik.com/index.php'; break;
        case 2: $app['url_list'] = 'http://jkt2.detik.com/index.php'; break;
        case 3: $app['url_list'] = 'http://jkt3.detik.com/index.php'; break;
    }
}
if ($_REQUEST['dudul'] != '')   # both GET and COOKIE
    setcookie('dudul',$_REQUEST['dudul'], time()+31000000);

require_once('du.class.php');
require_once('anynews.class.php');
require_once('welcome.page.php');

# test to make sure cache is possible
# 1. check if ./cache folder exist and writeable
# 2. if not, check if ./ folder is writeable
if ($app['cache']) {
    if (file_exists('./cache')) {
        if  (!is_writeable('./cache')) {
            $app['cache'] = FALSE;
            $list_footer = '<p class="tips"><strong>Optimization tips:</strong> You would get better performance (via caching) by making sure this script can write in current folder to put the cache files. To do this, ssh/ftp to current folder, and do a <strong>chmod 777 ./cache</strong></p>'.$list_footer;
        }
    }
    else {
        if  (!is_writeable('.')) {
            $app['cache'] = FALSE;
            $list_footer = '<p class="tips"><strong>Optimization tips:</strong> You would get better performance (via caching) by making sure this script can write in current folder to put the cache files. To do this, ssh/ftp to current folder, and either do a <strong>chmod 777 .</strong> OR <strong>mkdir cache; chmod 777 ./cache</strong></p>'.$list_footer;
        }
    }
}
if ($_REQUEST['pda']) {
    # PDA MODE EASY
    $_REQUEST['x'] = 'i';
    $_REQUEST['no'] = 'frame';
}
if (isset($_REQUEST['url'])) {
    require_once('du_detail.page.php');
}
elseif ($_REQUEST['x']=='y') {
    $page = $_REQUEST['page'] == ''? '1': $_REQUEST['page'];
    ShowHeader();
    ShowAnyNewsPage($page);
    show_footer();
}
elseif ($_REQUEST['x'] == '' and $_REQUEST['anurl'] != '') {
    require_once('anynews_detail.page.php');
}
elseif ($_REQUEST['x']=='i' and $_REQUEST['anurl'] != '') {
    require_once('anynews_list.page.php');
}
elseif ($_REQUEST['x']=='i' or $_REQUEST['no']=='frame' or $_REQUEST['no']=='gm' or $_REQUEST['no']=='rss2' or $_REQUEST['no']=='wap') {
    require_once('du_list.page.php');
}
elseif ($_REQUEST['x']=='w') {
    $page = $_REQUEST['page'] == ''? '1': $_REQUEST['page'];
    ShowHeader();
    ShowAboutPage($page);
    show_footer();
}
elseif ($_REQUEST['x']=='css') {
    require_once('css.page.php');
}
elseif ($_REQUEST['x']=='s') {
    show_source(__FILE__);
}
elseif ($_REQUEST['au']) {
    require_once('auto_update.page.php');
}
elseif ($_REQUEST['cm']) {
    require_once('cache_clear.page.php');
}
elseif ($_REQUEST['no'] == 'bcache') {
    require_once('cache_browse.page.php');
}
#~ elseif ($_REQUEST['x']=='x') {
else {
    $_REQUEST['x']='x';
    require_once('ajax.page.php');
}

?>