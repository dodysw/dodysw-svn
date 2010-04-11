<?
    if (!$_REQUEST['commit'] or ($_REQUEST['confirm_text'] != $_REQUEST['confirm_text2'])) {
        $dirsize = 0;
        $dh = opendir('cache');
        while (false !== ($filename = readdir($dh))) if (($file_name != '.' && $file_name != '..')) $dirsize += filesize('cache/'.$filename);
        $cache_size = round($dirsize/1024,2);
        $crazy_number = rand(1000,9999);
        ShowHeader();
        echo '<div id="info">';
        echo '<h1>Hapus Cache</h1>';
        echo '<form action="'.$_SERVER['PHP_SELF'].'"><input type="hidden" name="cm" value="1"><input type="hidden" name="commit" value="1">';
        echo '<p>Cache saat ini menempati ruang sebesar '.$cache_size.' KB. Ketik angka <strong>'.$crazy_number.'</strong> bila yakin ingin mengosongkan.</p>';
        if ($_REQUEST['confirm_text'] != $_REQUEST['confirm_text2']) {
            echo '<p>Angka belum benar, coba lagi.</p>';
        }
        echo '<input type="text" name="confirm_text"><input type="hidden" name="confirm_text2" value="'.$crazy_number.'"> <input type="submit" value="Kosongkan"></form>';
        echo '</div>';
        show_footer();
    }
    else {
        $dh = opendir('cache');
        while (false !== ($filename = readdir($dh))) if (($file_name != '.' && $file_name != '..')) @unlink('cache/'.$filename);
        ShowHeader();
        echo '<h1>Cache telah dikosongkan</h1>';
        echo "<p><a href={$_SERVER['PHP_SELF']}?x=w>Kembali ke awal</a></p>";
    }
?>