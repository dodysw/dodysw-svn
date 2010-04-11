<?
    $display = '';
    # iterate cache file list
    if (($dh = @opendir('cache')) === FALSE) {
        ShowCacheBrowseWarning();
    }

    $cached_news = array();
    while (false !== ($filename = readdir($dh))) {
        if ($filename == '.' or $filename == '..') continue;
        # open and unserialize into var
        ob_start();
        readfile('cache/'.$filename);
        $buffer = ob_get_contents();
        ob_end_clean();
        $news = unserialize($buffer);
        unset($news['content']); # preserve memory
        # get date, title, url and append into list
        if ($news['date'] == '')
            $str_date = 'unknown';
        else {
            $tgl = getdate($news['date']);
            $str_date = mktime(0,0,0,$tgl['mon'],$tgl['mday'],$tgl['year']);
        }
        $cached_news[$str_date][] = $news;  # memory hogging for large cache. should be parsed at display iteration.
    }
    if (!$cached_news) {
        ShowCacheBrowseWarning();
    }
    # sort by date desc, group by same date
    # - sort daily date first
    # new: con
    #~ echo HtmlHeader();
    ShowHeader();
    krsort($cached_news,SORT_NUMERIC);

    function cmp_by_date($a,$b) {
        if ($a['date'] == $b['date']) {
            return 0;
        }
        return ($a['date'] < $b['date']) ? 1 : -1;
    }

    echo '<div id="content-headlines">';
    foreach ($cached_news as $str_date=>$news_list) {
        if ($str_date == 'unknown') continue;
        $unx_date_group = $str_date;
        $str_date = $hari[date('w',$unx_date_group)].',&nbsp;'.date('j',$unx_date_group).'&nbsp;'.$bulan[date('n',$unx_date_group)].date(' Y',$unx_date_group);
        echo '<p class="date">'.$str_date.'</p>';
        # sort by date key
        usort($news_list,'cmp_by_date');
        echo '<ul>';
        foreach ($news_list as $news) {
            $dateme = date('H:i',$news['date']);
            if ($dateme == '00:00') $dateme = '';
            else $dateme .= ' - ';
            $title = trim(strip_tags($news['title']));
            if ($title == '') $title = 'Tanpa Judul';
            echo '<li>'.$dateme.'<a href="'.$_SERVER['PHP_SELF'].'?url='.urlencode($news['url']).'" target="m">'.$title.'</a></li>';
        }
        echo '</ul>';
    }
    if ($cached_news['unknown']) {
        echo '<p class="date">Tanpa Tanggal</p>';
        echo '<ul>';
        foreach ($cached_news['unknown'] as $news) {
            $title = trim(strip_tags($news['title']));
            if ($title == '') $title = 'Tanpa Judul';
            echo '<li><a href="'.$_SERVER['PHP_SELF'].'?url='.urlencode($news['url']).'" target="m">'.$title.'</a></li>';
        }
        echo '</ul>';
    }
    echo '</div>';
    show_footer();
?>