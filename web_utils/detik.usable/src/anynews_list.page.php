<?
    /* experimental mode, taking any website as news list*/
    $du = new AnyNews($_REQUEST['anurl']);
    $du->SetModeList();
    $du->cache_prefix = 'an_';
    if ($_REQUEST['cache_reload'])
        $du->SetSourceOrig();
    $du->GetBuffer();
    if ($du->last_error == ERROR_SOCKET) {
        echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="60; URL='.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">');
        echo '<h3>Whoops</h3>';
        echo '<p>We are sorry, for some reason we were not able to contact this address: <a href="'.$du->url.'"><strong>'.$du->url.'</strong> </a>.<p>You have a few options:<ul><li>Do nothing for 60 seconds and I will retry it again.<li><a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">Retry immediately</a><li><a href="'.$du->url.'">Open the original page</a> to see if it\'s up<li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
        echo '<p>Thank you.';
        show_footer();
        die();
    }
    # resolve the name of this url, or fallback to original url
    $title_name = $du->url;
    $title_usable = '';
    foreach ($an_m as $am) {
        if ($am['url'] == $title_name) {
            $title_name = $am['name'];
            $title_usable = '.usable';
        }
    }
    $du->SetTitleName($title_name);

    # decide whether to output RSS or normal list
    if ($_REQUEST['no'] == 'rss2')
        $du->RenderListRss($_REQUEST['complete']);
    else {
        $rss_url = $_SERVER['PHP_SELF'].'?x=i&anurl='.urlencode($du->url).'&no=rss2';
        ShowHeader('<link rel="alternate" title="'.$title_name.' RSS" href="'.$rss_url.'" type="application/rss+xml">');
        #~ echo '<h1><a href="'.$_SERVER['PHP_SELF'].'?x=w" title="Home" target="_top">'.$title_name.$title_usable.'</a></h1>';
        #~ echo $tgl_lengkap.' <a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('cache_reload'=>1)).'" class="button">Reload</a>';
        #~ if ($du->news['cache']) echo '<div id="cache_set">';
        $du->Render();
        flush();
        #~ if ($du->news['cache']) echo '</div>';
        #~ if ($du->news['cache'] and (time() - $du->news['cache'])>$du->newslist_cache_revalidate and !$_REQUEST['pda']) {
            #~ $du->SetSourceOrig(); $du->GetBuffer(); $du->Parse();  # force reload from original
            #~ echo '<div id="latest_set" style="display:none;">'; $du->Render(); echo '</div>';
            #~ echo '<script>document.getElementById("latest_set").style.display = "";document.getElementById("cache_set").style.display = "none";</script>';
        #~ }
        $footsy = array(
            '<a href="'.$rss_url.'">rss</a>'
            ,'<a href="'.$rss_url.'&complete=1">rss complete</a>'
            );
        show_footer($du->url,$footsy);

    }
?>