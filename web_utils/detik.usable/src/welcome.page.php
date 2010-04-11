<?

//welcome page is considered static
#~ httpcache_by_lastupdate();

function ShowAboutPage($page) {
    global $contributors,$app;
    if ($page == '1') {
        foreach ($contributors as $c)
            $con .= '<li><a href="mailto:'.$c[1].'">'.$c[0].'</a>, '.$c[2].'</li>';
        $temp = array();
        foreach (preg_split('/^-\s*/m',$app['version_description']) as $row)
            $temp[] = '<li>'.$row.'</li>';
        array_shift($temp);
        $last_changes = join('',$temp);

        echo <<< __E__
<div id="main">
<h2>Tentang Detik.Usable</h2>
<p>Awalnya...."dipicu oleh salah satu disain <a href="http://www.detik.com">situs berita populer di Indonesia</a> yang menurut opini kami menyengsarakan
pengunjung yang datang dengan niat untuk membaca berita, script ini ingin menunjukkan potensi sebuah situs berita yg <em>usable</em>: cepat, bersih,
dan mudah digunakan".</p>

<p>Teknis cara kerjanya adalah dengan pencocokan <em>regular expression</em> terhadap pola kode HTML di semua halaman sumber untuk mendapatkan judul,
deskripsi, tanggal, link, dan isi berita, lalu dikeluarkan dalam format HTML baru. Aspek yang inheren dalam metode ini adalah tingginya maintenance terhadap sedikit perubahan pada susunan HTML di situs sumber,
sehingga kegagalan parsing akan kadang-kadang anda temui selama menggunakan script ini. Pada situasi demikian, anda dengan mudah dapat membuka
halaman orisinilnya sebagai ganti.</p>

<h2>Pasang Sendiri</h2>
<p>Bila Anda ingin memasang detik.usable untuk sendiri, anda dapat menyalin <a href="{$_SERVER['PHP_SELF']}?x=s" target="_top">source code dari situs ini</a>
(hanya satu file .php), atau mengambil <a href="{$app['update_url']}">kode sumber terbaru dari pusat</a>, lalu upload ke web hosting yg mendukung <a href="http://php.net">php</a>, dan selesai!
</p>
<p>Mengingat script ini berpotensi melakukan penerbitan ulang materi berhak cipta, maka kami menghimbau anda menggunakan materinya <em>hanya untuk keperluan yang wajar, atau pribadi</em>,
-- sebagai mana sebuah web browser -- dan tidak mempublikasikannya kepada umum tanpa seijin pemegang hak.</p>
<p>Kami tidak bertanggung jawab atas segala efek dan akibat dari penggunaan script ini.</p>

</div>

<div id="secondary">
<h2>Perubahan Terakhir</h4>
<ul>{$last_changes}</ul>

<h2>Materi Terkait</h2>
<ul>
<li><a href="http://groups-beta.google.com/group/detikusable/subscribe">Milis detik.usable</a> (pengumuman rilis baru)</li>
<li>Tampilan <a href="http://miaw.tcom.ou.edu/~dody/du/images/DetikScreen1.png">screenshot di PDA</a> (<a href="mailto:pursena@advokasi.com">Bagus Pursena</a>)</li>
</ul>
<h2>Kontributor</h2>
<p>Mohon maaf bila ada yang terlewat:</p>
<ul>{$con}</ul>

<h2>Lisensi</h2>
<p>Lisensi script ini adalah milik umum (<em>public domain</em>) dan Anda dipersilahkan memodifikasi dan mempergunakannya tanpa batas.</p>


</div>
__E__;

    }
}

function ShowAnyNewsPage($page) {
    global $an_m;
    $an_welcome_list = array();
    foreach ($an_m as $m)
        $an_welcome_list[] = '<li><a href="'.$_SERVER['PHP_SELF'].'?x=i&anurl='.$m['url'].'">'.$m['name'].'</a> </li>';
    $an_welcome_list = join('',$an_welcome_list);
    echo <<< __E__
<div id="main">
<div id="content-headlines">
<h2>AnyNews<sup>Beta</sup></h2>

<div id="nav">
<ul>
{$an_welcome_list}
</ul>
</div>

<form action="{$_SERVER['PHP_SELF']}">
<input type="hidden" name="x" value="i">
<p>Situs lain: <input type="text" name="anurl" value="http://" size="30"><input type="submit" value="Buka"></p>

</div>
</div>

<div id="secondary"><div id="content-channels">
<h2>Tentang AnyNews</h2>
<p>AnyNews adalah pendekatan pengenalan elemen daftar berita, dengan cara pencocokan <em>regular expression</em> di dalam komponen URI (hyperlink) sebuah situs, pengelompokan kanal berita, dan analisa statistik berdasarkan panjang karakter URI (makin panjang, makin cenderung merujuk ke berita). Hal ini kontras dengan Detik.Usable yang hanya melakukan pecocokan <em>regular expression</em> terhadap pola kode sumber HTML.</p>
<p>Karena hanya pola URI yang diperlukan, AnyNews
1) lebih tahan terhadap perubahan layout/pola HTML,
2) lebih sederhana <em>regular expression</em>-nya,
3) namun seperti Detik.Usable, elemen non-link seperti isi/detail berita tetap harus dikenali dengan teknik <em>regular expression</em> ke layout/kode sumber HTML.
<p>Oleh sebabnya lebih cocok untuk keperluan murni tampilan daftar/list judul berita, misal untuk RSS.</p>

</form>
</div></div>
__E__;

}
?>