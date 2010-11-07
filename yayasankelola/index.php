<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; ?>
<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<h1>Demonstration</h1>
<p><a href="admin/">Go to administrator page</a></p>
<p><a href="directory.php">Directory</a></p>
<p><a href="links.php">Links</a></p>
<p><a href="news_latest.php">Latest News</a></p>
<p><a href="news_arch.php">News Archieve (all category)</a></p>
<p><a href="news_arch2.php">News Archieve (specific category)</a></p>
<h4>Search</h4>
<form action='search.php' method='get'>
  <input type='hidden' name='site' value='0'/>

  <input type='hidden' name='path' value=''/>
  <input type='hidden' name='template_demo' value=''/>
  <input type='hidden' name='result_page' value='search.php'/>

  <input type='text' class='phpdiginputtext' size='15' maxlength='50' name='query_string' value='sby'/>
  <input type='submit' class='phpdiginputsubmit' name='search' value='Go'/>
  <br><br>
</form>

<h4>Search Form</h4>
<form action="where.php" method="get">
  <input type="text" size="15" maxlength="50" name="query" value="<?=$_REQUEST['query']?>"/>
  <select name="query_type"><option value="news" <?=$_REQUEST['query_type']=='news'?'selected':''?>>News/Articles<option value="directory" <?=$_REQUEST['query_type']=='directory'?'selected':''?>>Directory</select>
  <select name="row_per_page"><option <?=$_REQUEST['row_count']=='10'?'selected':''?>>10<option <?=$_REQUEST['row_count']=='20'?'selected':''?>>20<option <?=$_REQUEST['row_count']=='50'?'selected':''?>>50<option <?=$_REQUEST['row_count']=='100'?'selected':''?>>100</select> entry per page
  <p><input type="submit" name="search" value="Go"/>
</form>

</body></html>