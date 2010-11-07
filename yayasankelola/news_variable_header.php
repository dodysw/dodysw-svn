<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; ?>
<?
$kategory = $_REQUEST['cat'] == ''? '*': $_REQUEST['cat'];
$cat_str = $kategory == '*'? 'any': $kategory;
@include $cat_str.'.header.inc.php';

?>
<p>Daftar artikel terbaru (5 terakhir) dengan custom header/footer</p>
<? $news = instantiate_module('news'); $news->fe_list(5,$kategory) ?>

<?
@include $cat_str.'.footer.inc.php';
?>

