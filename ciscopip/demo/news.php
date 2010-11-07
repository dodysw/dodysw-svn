<?
include('../config.inc.php');
include('../include/func.inc.php');
$news = instantiate_module('news');
?>

<? $news->fe_view($_REQUEST['id']) ?>
