<?
include "funcsimulate.php";
test_funcfv();
?>

<?
$setoran_per_bulan = $_REQUEST['setoran_per_bulan']? $_REQUEST['setoran_per_bulan'] : 1000000;
$bunga_per_tahun_persen = $_REQUEST['bunga_per_tahun_persen']? $_REQUEST['bunga_per_tahun_persen'] : 8.0;
$durasi_dlm_bulan = $_REQUEST['durasi_dlm_bulan']? $_REQUEST['durasi_dlm_bulan']:780;
?>
<hr>
<form>
<p>Setoran/bulan: <input name="setoran_per_bulan" value="<?=$setoran_per_bulan?>">
<p>Pertumbuhan/tahun (%): <input name="bunga_per_tahun_persen" value="<?=$bunga_per_tahun_persen?>">
<p>Bulan: <input name="durasi_dlm_bulan" value="<?=$durasi_dlm_bulan?>">
<input type="submit" value="Hitung">
<p>Investasi: Rp. <?=number_format(funcfv($setoran_per_bulan, $bunga_per_tahun_persen, $durasi_dlm_bulan), 2, '.', ',')?>
</form>