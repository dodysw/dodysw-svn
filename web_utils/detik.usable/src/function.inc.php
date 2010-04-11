<?

function merge_query($newquery) {
    /* merge new key/val array with query string to a new array copy and return the copy in an url ready format */
    foreach (array_merge($_GET,$newquery) as $k=>$v) $temparr[] = urlencode($k).'='.urlencode($v);
    return implode('&amp;',$temparr);
}

function show_footer($temp_orig=False, $fields=array()) {
    global $author_email, $author_website, $app, $list_footer;
    $e = array();
    #~ $e[] = '<a href="mailto:'.$author_email.'" target="_top">author</a>';
    #~ $e[] = '<a href="'.$_SERVER['PHP_SELF'].'?x=w">rumah</a>';
    if ($temp_orig)
        $e[] = '<a href="'.$temp_orig.'" target="_top">sumber berita</a>';
    $e[] = '<a href="'.$author_website.'" target="_top">dibangkitkan '.$app['name'].' v'.$app['version'].'</a>';
    $e[] = 'pemiliknya '.$app['hosted_by'];
    #~ $e[] = 'DU Proxy: '.($app['proxy_mode']? 'Yeah': 'Nope');
    #~ $e[] = 'HTTP Proxy: '.($app['http_proxy']['enable']? 'Yeah': 'Nope');
    #~ if ($temp_stream)
        #~ $e[] = $temp_stream;
    #~ $e[] = "Compression Support: ".(extension_loaded('zlib')? 'Yeah':'Nope');
    echo $list_footer;  # additional footer (Set by cache optimization)
    echo '<div id="footer"><p>'.join(' | ',array_merge($e,$fields)).'</p></div></body></html>';
}

function DieError($msg) {
    show_footer();
}

function mystripslashes($val) {
    return get_magic_quotes_gpc()? stripslashes($val) : $val;
}

function httpcache_by_lastupdate($modif_time = -1) {
    // Handle If-Modified-Since HTTP browser cache function.
    // If the last modified date on web browser equals the server, then return HTTP 304 response without body.
    // http://www.web-caching.com/mnot_tutorial/how.html
    if ($modif_time == -1) {
        global $app;
        $modif_time = $app['last-modified'];
    }

    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) and gmdate('D, d M Y H:i:s', $modif_time).' GMT' == trim($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        header('HTTP/1.0 304 Not Modified');
        header('Content-Length: 0');
        exit();
    }
    header('Last-Modified: '.gmdate('D, d M Y H:i:s',$modif_time).' GMT');
}

function ShowHeader($meta='') {
    global $tgl_lengkap;
    echo HtmlHeader(2,$meta);
    echo '<div id="header">';
    echo '<p class="date">'.$tgl_lengkap.'</p>';
    echo '<h1><a href="'.$_SERVER['PHP_SELF'].'"><span class="detik">detik.</span><span class="usable">usable</span></a></h1>';
    echo '<div id="nav">';
    echo '<ul>';
    if ($_REQUEST['x']=='x')
        echo '<li><a href="'.$_SERVER['PHP_SELF'].'?cache_reload=1">Reload</a> </li>';
    else
        echo '<li><a href="'.$_SERVER['PHP_SELF'].'">Awal</a> </li>';
    echo '<li><a href="'.$_SERVER['PHP_SELF'].'?x=s">Kode sumber</a> </li>';
    echo '<li><a href="'.$_SERVER['PHP_SELF'].'?au=1">Ada versi baru?</a> </li>';
    echo '<li><a href="'.$_SERVER['PHP_SELF'].'?no=bcache">Lihat</a> / <a href="'.$_SERVER['PHP_SELF'].'?cm=1">Hapus cache</a> </li>';
    #~ echo '<li><a href="'.$app['update_url'].'">Download</a> </li>';
    echo '<li><a href="'.$app['update_url'].'?x=y">AnyNews</a> </li>';
    echo '<li><a href="'.$_SERVER['PHP_SELF'].'?x=w">Tentang detik.usable</a> </li>';
    echo '</ul>';
    echo '</div>';
    echo '</div>';
}

function ShowCacheBrowseWarning() {
    ShowHeader();
    echo '<div id="info">';
    echo '<h1>Cache belum ada isi</h1>';
    echo '<p>Sepertinya belum ada berita yang disimpan dalam cache. Bila detik.usable diatur untuk boleh menyimpan berita, lihat lagi di sini setelah membuka beberapa berita.</p>';
    echo '</div>';
    show_footer();
    die();
}

?>