<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; ?>
<? $e = instantiate_module('dir_entry'); $de = $e->front_view($_REQUEST['eid']);?>
<?
//persiapan tampilan
// Ruang Pertunjukan (6 x 10) kapasitas 350 orang, ruang pameran (4 x 8) kapasitas 100 orang.
$fsl = array();
if ($de['fas_ruang_pertunjukan']) {
    $t = ($de['fas_ruang_pertunjukan_1_panjang']? '('.$de['fas_ruang_pertunjukan_1_panjang'].' x '.$de['fas_ruang_pertunjukan_1_lebar'].') ' :' ');
    $t .= ($de['fas_ruang_pertunjukan_1_kapasitas'] != '')? 'kapasitas '.$de['fas_ruang_pertunjukan_1_kapasitas'].' orang' : '';
    $fsl[] = 'Ruang Pertunjukan '.$t;
}
if ($de['fas_ruang_pameran']) {
    $t = ($de['fas_ruang_pameran_1_panjang']? '('.$de['fas_ruang_pameran_1_panjang'].' x '.$de['fas_ruang_pameran_1_lebar'].') ' :' ');
    $t .= ($de['fas_ruang_pameran_1_kapasitas'] != '')? 'kapasitas '.$de['fas_ruang_pameran_1_kapasitas'].' orang' : '';
    $fsl[] = 'Ruang Pameran '.$t;

}
$fsl = join(', ',$fsl);

$prd = array();
if ($de['prod_buku_tersedia']) $prd[] = 'Buku';
if ($de['prod_newsletter_tersedia']) $prd[] = 'Newsletter';
if ($de['prod_jurnal_tersedia']) $prd[] = 'Jurnal';
if ($de['prod_rekaman_audio_tersedia']) $prd[] = 'Rekaman audio';
if ($de['prod_rekaman_video_tersedia']) $prd[] = 'Rekaman video';
if ($de['prod_barang_kerajinan_tersedia']) $prd[] = 'Barang kerajinan';
if ($de['prod_benda_seni_tersedia']) $prd[] = 'Benda seni';
if ($de['prod_lainlain_tersedia']) $prd[] = 'Lain lain';
if ($de['prod_hasil_lain']) $prd[] = $de['prod_hasil_lain'];
$prd = join(', ',$prd);


$mdl = instantiate_module('jenis_organisasi');
$orgbtk = $mdl->enum_decode($de['org_jenis']);
?>


<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<h3>Contoh detail entry</h3>

<p><? $e->front_trail($_REQUEST['eid']);?>
<p>
<?=$de['org_nama']?>, <?=$orgbtk?>
<br><?=$de['org_alamat_1']?>
<br><?=$de['org_alamat_2']?>
<br><?=$de['org_kota']?>
<br><?=$de['org_kode_pos']?>
<br><?=$de['org_propinsi']?>
<br>Telpon: <?=$de['org_telepon_1']?>, <?=$de['org_telepon_1']?>
<br>Fax: <?=$de['org_fax']?>
<br>HP: <?=$de['org_hp']?>
<br>email: <?=$de['org_email']?>
<br>Website: <?=$de['org_website']?>

<p>Pimpinan: <?=$de['pimpinan_nama']?>, <?=$de['pimpinan_jabatan']?>
<br>Direktur Artistik: <?=$de['direktur_artistik']?>
<br>Penghubung: <?=$de['cp_nama']?>, <?=$de['cp_jabatan']?>
<br>Telpon: <?=$de['cp_telepon_1']?>, <?=$de['cp_telepon_1']?>
<br>Fax: <?=$de['cp_fax']?>
<br>HP: <?=$de['cp_hp']?>
<br>email: <?=$de['cp_email']?>

<p>Didirikan: <?=$de['tanggal_berdiri']?>
<br>Kegiatan Utama:
<br><?=$de['kegiatan_tujuan']?>
<br>Pencapaian dan Prestasi Organisasi:
<br><?=$de['prestasi']?>
<br>Fasilitas: <?=$fsl?>
<br><?=$de['fas_pendukung_ruang']?>
<br><?=$de['fas_pendukung_lainnya']?>
<br>Produk: <?=$prd?>
<p><a href="directory.php?pa=<?=$de['parent_dir']?>">&lt; Lihat entry lain di kategori yang sama</a>

<hr><a href="index.php">&lt; Back to demo</a>
</body></html>
