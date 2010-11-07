<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; include_once('auth.php');?>
<? $f=$_SESSION['mmclient_login_user'].'_header.inc.php'; if (file_exists($f)) {include $f;} else {include 'header.inc.php';} ?>

<table>
<tr><td valign="top"><!-- col 1 -->

<p><a href="admin/">Go to administrator page</a></p>
<h4>Archieve by Category</h4>
<form action="news_arch.php">
<? $cat = instantiate_module('news_cat'); $cat->show_combo('cat',$cat->enum_list(),1,1) ?>
<input type=submit>
</form>
<p><a href="stats.php">Statistics</a></p>
<h4>Search</h4>
<? $news = instantiate_module('news'); $news->fe_search_form() ?>

</td><td valign="top"><!-- col 2 -->
<h1>Demonstration</h1>
<p><?='Hello, <b>'.$_SESSION['mmclient_login_user'].'</b>. <a href="logout.php?go='.urlencode($_SERVER['PHP_SELF']).'">Log out</a>' ?>

<? $news = instantiate_module('news'); $news->fe_list(5,'*',$_SESSION['mmclient_login_user']) ?>


</td></tr></table>
<? $f=$_SESSION['mmclient_login_user'].'_footer.inc.php'; if (file_exists($f)) {include $f;} else {include 'footer.inc.php';} ?>