<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; ?>
<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<h4>Search Form</h4>
<form action="where.php" method="get">
  <input type="text" size="15" maxlength="50" name="query" value="<?=$_REQUEST['query']?>"/>
  <select name="query_type"><option value="news" <?=$_REQUEST['query_type']=='news'?'selected':''?>>News/Articles<option value="directory" <?=$_REQUEST['query_type']=='directory'?'selected':''?>>Directory</select>
  <select name="row_per_page"><option <?=$_REQUEST['row_per_page']=='10'?'selected':''?>>10<option <?=$_REQUEST['row_per_page']=='20'?'selected':''?>>20<option <?=$_REQUEST['row_per_page']=='50'?'selected':''?>>50<option <?=$_REQUEST['row_per_page']=='100'?'selected':''?>>100</select> entry per page
  <p><input type="submit" name="search" value="Go"/>
</form>
<hr>
<?
if ($_REQUEST['query_type'] == 'news') include 'where_news.php';
if ($_REQUEST['query_type'] == 'directory') include 'where_dir.php';
?>
<hr>
</body></html>