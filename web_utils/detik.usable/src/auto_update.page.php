<?

    $target_filename = basename(__FILE__);
    ShowHeader();
    echo '<div id="info">';
    echo '<h1>Update Versi Baru</h1>';

    echo '<ul>';
    echo '<li>Memeriksa izin tulis...';
    //check permission to write
    if (!is_writable(__FILE__)) {
        echo 'Gagal</li>';
        echo '<li>Uji merubah izin tulis...';
        if (@!chmod(__FILE__,0777)) {    //test ubah permission
            echo 'Gagal</li>';
            echo '<li>Uji menyimpan ke nama file lain di direktori yang sama...';
            if (!is_writable(dirname(__FILE__))) {  // coba simpan ke file yg berbeda di folder yg sama
                echo 'Gagal</li>';
                echo '</ul><p>Menyerah</p>';
                echo '<p>Maaf, program ini tidak memiliki izin tulis ke "'.__FILE__.'". Coba rubah file permission-nysa: <strong>chmod 777 '.__FILE__.'</strong>.</p>';
                echo '</div>';
                show_footer();
                die();
            }
            else {
                $target_filename = 'index2.php';
                echo 'OK. Update akan ditulis ke file '.dirname(__FILE__).'/'.$target_filename.'</li>';
            }
        }
        else {
            echo 'OK</li>';
        }
    }
    else {
        echo 'OK</li>';
    }
    flush();

    //compare version
    $url_parsed = parse_url($app['update_url']);
    $port = $url_parsed['port']!=''? $url_parsed['port'] : 80;
    $sock = new DuSock($url_parsed['host'],$port);
    $addr = $url_parsed['scheme'].'://'.$url_parsed['host'].':'.$port;
    echo '<li>Menghubungi repositori di <a href="'.$addr.'">'.$addr.'</a> ...';
    flush();
    if (!$sock->socket_open()) {
        echo 'Gagal</li>';
        echo '</ul><p>Menyerah</p>';
        echo '</div>';
        show_footer();
        die();
    }
    echo 'OK</li>';
    $addr_wp = $addr.$url_parsed['path'].($url_parsed['query']==''?'':'?'.$url_parsed['query']);
    echo '<li>Mengambil versi terakhir di <a href="'.$addr_wp.'">'.$addr_wp.'</a> ...';
    $sock->sock_send_request($url_parsed['path'].'?'.$url_parsed['query']);
    $sock->sock_recv_header();
    $buffers = $sock->sock_recv_all();
    if ($buffers == '') {
        echo 'Gagal</li>';
        echo '</ul><p>Menyerah</p>';
        echo '</div>';
        show_footer();
        die();
    }
    echo 'OK</li>';

    if (!$_REQUEST['commit']) {
        echo '<li>Memeriksa versi ...';
        if (preg_match('/\$app\[\'version\'\]\s*=\s*"([^"]*)"/i',$buffers,$remote_res))
            $remote_version = $remote_res[1];
        else
            $remote_version = '0.0';
        list($remote_major, $remote_minor) = explode('.',$remote_version,2);    # split into major and minor version
        list($local_major, $local_minor) = explode('.',$app['version'],2);
        echo 'detik.usable ini: '.$local_major.'.'.$local_minor.', yang terbaru: '.$remote_major.'.'.$remote_minor.'</li>';
        echo '</ul>';

        echo '<form method="get" action="'.$_SERVER['PHP_SELF'].'"><input type="hidden" name="au" value="1"><input type="hidden" name="commit" value="1">';
        #~ echo '<input type="hidden" name="target_filename" value="'.htmlentities($target_filename).'">';

        if ($remote_major > $local_major or ($remote_major == $local_major and $remote_minor > $local_minor)) {
            echo '<p>Versi yang lebih baru telah tersedia. <input type="submit" value="Update ke '.$remote_version.'">';
        }
        else {
            echo '<p>detik.usable ini sudah versi terbaru. Namun bila mau, <input type="submit" value="Paksa perbarui lagi"></p>';
        }
        echo '</form>';
        echo '</div>';
        show_footer();
        die();
    }
    else {
        $target = dirname(__FILE__).'/'.$target_filename;
        echo '<li>Menulis ke '.$target.' ...';
        $fp = fopen($target,'w');
        fwrite($fp,$buffers);
        fclose($fp);
        echo 'OK</li>';
        $redirect = dirname($_SERVER['PHP_SELF']).'/'.$target_filename;
        $redirect = str_replace('//','/',$redirect);
        echo '</ul><p>Update selesai. <a href="'.$redirect.'">Buka ulang detik.usable</a> untuk melihatnya.</p>';
        echo '</div>';
        show_footer();
        die();
    }
?>