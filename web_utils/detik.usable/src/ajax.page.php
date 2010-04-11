<?
$du = new DetikUsable();
$du->SetModeList();
if ($_REQUEST['cache_reload'])
    $du->SetSourceOrig();
$du->GetBuffer();
if ($du->last_error == ERROR_SOCKET) {
    echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="60; URL='.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">');
    echo '<h3>Whoops</h3>';
    echo '<p>We are sorry, for some reason we were not able to contact this address: <a href="'.$du->url.'"><b>'.$du->url.'</b> </a>.<p>You have a few options:<ul><li>Switch to another available servers:<ul><li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('dudul'=>0)).'">jkt.detik.com</a><li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('dudul'=>1)).'">jkt1.detik.com</a><li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('dudul'=>2)).'">jkt2.detik.com</a><li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('dudul'=>3)).'">jkt3.detik.com</a></ul><li>Do nothing for 60 seconds and I will retry it again.<li><a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">Retry immediately</a><li><a href="'.$du->url.'">Open the original page</a> to see if it\'s up<li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
    echo '<p>Thank you.';
    show_footer();
    die();
}
$du->Parse();
$rss_url = $_SERVER['PHP_SELF'].'?no=rss2';
ShowHeader('<link rel="alternate" title="Detik.Usable RSS" href="'.$rss_url.'" type="application/rss+xml">');

echo <<<__E__
<script>
function getit(url) {
    //document.getElementById("secondary").style.display = "none";
    obj = document.getElementById("secondary");
    request_obj = new XMLHttpRequest();
    request_obj.open('GET',url,false);
    request_obj.send(null);
    res = request_obj.responseText;
    obj.innerHTML = res;
}
</script>
__E__;


#~ if ($du->news['cache']) echo '<div id="cache_set">';
$du->Render();
#~ flush();
#~ if ($du->news['cache']) echo '</div>';
#~ if ($du->news['cache'] and (time() - $du->news['cache'])>$du->newslist_cache_revalidate and !$_REQUEST['pda']) {
    #~ $du->SetSourceOrig(); $du->GetBuffer(); $du->Parse();  # force reload from original
    #~ echo '<div id="latest_set" style="display:none;">'; $du->Render(); echo '</div>';
    #~ echo '<script>document.getElementById("latest_set").style.display = "";document.getElementById("cache_set").style.display = "none";</script>';
#~ }

$footsy = array(
    '<a href="'.$rss_url.'">rss</a>'
    ,'<a href="'.$rss_url.'&complete=1">rss lengkap</a>'
    ,'<a href="'.$_SERVER['PHP_SELF'].'?no=wap">versi wap</a>'
    ,'<a href="'.$_SERVER['PHP_SELF'].'?pda=1">versi layar kecil</a>'
    );
show_footer($du->url,$footsy);
?>