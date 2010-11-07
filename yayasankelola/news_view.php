<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; ?>
<?
if ($_REQUEST['cat'] == '') echo '<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>';
else {
    $kategory = $_REQUEST['cat'] == ''? '*': $_REQUEST['cat'];
    $cat_str = $kategory == '*'? 'any': $kategory;
    @include $cat_str.'.header.inc.php';
}
?>

<? $news = instantiate_module('news'); $news->fe_view($_REQUEST['id']) ?>

<?
if ($_REQUEST['cat'] == '') echo '<hr><a href="index.php">&lt; Back to demo</a></body></html>';
else {
    @include $cat_str.'.footer.inc.php';
}

?>
