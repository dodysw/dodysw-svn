<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; include 'auth.php'?>
<? $row_per_page = 30; ?>
<? $f=$_SESSION['mmclient_login_user'].'_header.inc.php'; if (file_exists($f)) {include $f;} else {include 'header.inc.php';} ?>

<table>
<tr><td valign="top"><!-- col 1 -->

<p><a href="admin/">Go to administrator page</a></p>
<!--
<h4>Product Categories</h4>
<form action="product_arch.php">
<? $mdl = instantiate_module('product_category'); $mdl->show_combo('cat',$mdl->enum_list(),1,1) ?>
<input type=submit>
</form>
-->

<h4>Search Product</h4>
<? $mdl = instantiate_module('product'); $mdl->fe_search_form() ?>

<h4>Search Manufacturer</h4>
<? $mdl = instantiate_module('product_manufacturer'); $mdl->fe_search_form() ?>

<h4>By Manufacturer</h4>
<? $mdl = instantiate_module('product_manufacturer'); $mdl->fe_list() ?>

<h4>By Categories</h4>
<? $mdl = instantiate_module('product_category'); $mdl->fe_list_flat() ?>


</td><td valign="top"><!-- col 2 -->
<h1>Demonstration</h1>

<p><?#='Hello, <b>'.$_SESSION['mmclient_login_user'].'</b>. <a href="logout.php?go='.urlencode($_SERVER['PHP_SELF']).'">Log out</a>' ?>

<? $mdl = instantiate_module('product'); $mdl->fe_list(5,'*') ?>


</td><td valign="top"><!-- col 3 -->

<? if (!$_SESSION['shop_login_ok']) { ?>

<h4>Member Login</h4>
<form action="login.func.php" method="POST">
<input type=hidden name=go value="<?=$_REQUEST['go']?>">
<table>
<tr><td>Username:</td><td><input type="text" name="username" value="<?=$_REQUEST['username']?>"></td>
<tr><td>Password:</td><td><input type=password name=password value="<?=$_REQUEST['password']?>"></td>
<tr><td>&nbsp</td><td><input type=submit></td>
</table>
<b><a href="register.php?go=<?=$_SERVER['PHP_SELF']?>">Not member yet? Register here</a></b>
</form>

<? } else { ?>

<p>Hello, <?=$_SESSION['shop_login_user']?>. <b><a href="logout.php?go=index.php">Logout</a></b>
<p><a href="cart.php">View your shopping cart</a>

<? } ?>




</td></tr></table>
<? $f=$_SESSION['mmclient_login_user'].'_footer.inc.php'; if (file_exists($f)) {include $f;} else {include 'footer.inc.php';} ?>