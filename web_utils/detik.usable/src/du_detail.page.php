<?
    $_REQUEST['param1'] = trim(mystripslashes($_REQUEST['param1']));
    $du = new DetikUsable($_REQUEST['url']);
    $du->SetModeDetail();
    if ($_REQUEST['cache_reload']) $du->SetSourceOrig();
    if ($app['proxy_mode']) { $du->SetModeNode(); $du->SetNodeServerUrl($app['proxy_url']);}
    $du->GetBuffer();
    if ($du->last_error == ERROR_SOCKET) {
        echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="60; URL='.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">');
        echo '<h3>Whoops</h3>';
        echo '<p>We are sorry, for some reason we were not able to contact this address: <a href="'.$du->url.'"><b>'.$du->url.'</b> </a>.<p>You have a few options:<ul><li>Do nothing for 60 seconds and I will retry it again.<li><a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">Retry immediately</a><li><a href="'.$du->url.'">Open the original page</a> to see if it\'s up<li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
        echo '<p>Thank you.';
        show_footer();
        die();
    }
    $ret = $du->Parse();
    if (!$ret) {
        //show something if news is unparseable
        echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="10; URL='.$du->url.'">');
        echo '<h3>Whoops</h3>';
        echo '<p>We are sorry, we could not parse the page detail properly. You have a few options:<ul><li>Do nothing for 10 second and I will redirect to original page.<li><a href="'.$du->url.'">Open the original page immediately</a><li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
        echo '<p>Thank you.';
        show_footer();
        die();
    }
    else {
        if ($du->from_cache) {
            //detail taken from cache file is considered static as of the cache last update
            httpcache_by_lastupdate($du->news['cache']);
        }
        if ($_REQUEST['as_node']) $du->RenderNode($_REQUEST['cu']); //client set cu to 1 when requesting uncompressed stream
        elseif ($_REQUEST['wap']) $du->RenderDetailWap();
        else {
            ShowHeader();
            $du->Render();
            show_footer($du->url);
        }
    }
?>