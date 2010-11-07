<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php';?>
<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<p><b>Product List</b></p>
<? $mdl = instantiate_module('product_manufacturer'); $mdl->show_search_result($_SESSION['mmclient_login_user']) ?>
<hr><a href="index.php">&lt; Back to demo</a>
</body></html>