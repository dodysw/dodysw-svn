<?
    /* experimental mode, taking any website as news detail*/
    $du = new AnyNews($_REQUEST['anurl']);
    if ($_REQUEST['cache_reload']) $du->SetSourceOrig();
    $du->SetModeDetail();
    $du->GetBuffer();
    if ($du->last_error == ERROR_SOCKET) {
        echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="60; URL='.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">');
        echo '<h3>Whoops</h3>';
        echo '<p>We are sorry, for some reason we were not able to contact this address: <a href="'.$du->url.'"><strong>'.$du->url.'</strong> </a>.<p>You have a few options:<ul><li>Do nothing for 60 seconds and I will retry it again.<li><a href="'.$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'">Retry immediately</a><li><a href="'.$du->url.'">Open the original page</a> to see if it\'s up<li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
        echo '<p>Thank you.';
        show_footer();
        die();
    }
    if (!$du->Parse()) {
        //show something if news is unparseable
        echo HtmlHeader(2,'<META HTTP-EQUIV=Refresh CONTENT="10; URL='.$du->url.'">');
        echo '<p>We are sorry, we could not parse the page detail properly. You have a few options:<ul><li>Do nothing for 10 second and I will redirect to original page.<li><a href="'.$du->url.'">Open the original page immediately</a><li><a href="#" onclick="window.history.back();return false;">Go to previous page of your browser</a> (or just press your browser back button)</ul>';
        echo '<p>Thank you.';
        show_footer();
    }
    else {
        $du->Render();
    }
?>