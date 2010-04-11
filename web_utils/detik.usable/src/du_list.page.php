<?
$du = new DetikUsable();
if ($app['proxy_mode']) {
    //retrieve serialized+processed html containing ready-to-view array from other detik.usable node.
    $du->SetModeNode();
    $du->SetNodeServerUrl($app['proxy_url']);
    $du->SetModeList();
    $du->GetBuffer();
    $du->Parse();
}
else {
    //retrieve raw html from detik, parse, and output as new detik.usable-style design
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
}

//@as_node = 1 to ask for serialized news array (default: compressed)
if ($_REQUEST['as_node'])
    $du->RenderNode($_REQUEST['cu']); //client set cu to 1 when requesting uncompressed stream
elseif ($_REQUEST['no'] == 'rss2')
    $du->RenderListRss($_REQUEST['complete']);
elseif ($_REQUEST['no'] == 'wap')
    $du->RenderListWap();
else {
    $rss_url = $_SERVER['PHP_SELF'].'?no=rss2';
    echo HtmlHeader(2,'<link rel="alternate" title="Detik.Usable RSS" href="'.$rss_url.'" type="application/rss+xml">');
    echo '<div>';
    echo '<h1><a href="'.$_SERVER['PHP_SELF'].'?x=w" title="Home" target="_top">detik.usable</a></h1>';
    echo '<p>'.$tgl_lengkap.'</p>';
    echo '<p><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('cache_reload'=>1)).'">Reload</a>';
    echo '</div>';
    if ($du->news['cache']) echo '<div id="cache_set">';
    $du->Render();
    $du->RenderAds();
    flush();
    if ($du->news['cache']) echo '</div>';
    if ($du->news['cache'] and (time() - $du->news['cache'])>$du->newslist_cache_revalidate and !$_REQUEST['pda']) {
        $du->SetSourceOrig(); $du->GetBuffer(); $du->Parse();  # force reload from original
        echo '<div id="latest_set" style="display:none;">'; $du->Render(); echo '</div>';
        echo '<script>document.getElementById("latest_set").style.display = "";document.getElementById("cache_set").style.display = "none";</script>';
    }
    $footsy = array(
        '<a href="'.$rss_url.'">rss</a>'
        ,'<a href="'.$rss_url.'&complete=1" title="Note: could be much slower">rss complete</a>'
        ,'<a href="'.$_SERVER['PHP_SELF'].'?no=wap" title="WAP mode buat handphone jadul">wap version</a>'
        ,'<a href="'.$_SERVER['PHP_SELF'].'?pda=1" title="PDA/Handphone yang tidak mendukung javascript">small screen version</a>'
        );
    show_footer($du->url,$footsy);
}
?>