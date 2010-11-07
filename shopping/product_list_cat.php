<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php';?>
<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<p><b>Product List by Category</b></p>

<? $mdl = instantiate_module('product'); $mdl->fe_list(5,0,$_REQUEST['cat']) ?>

<hr><a href="index.php">&lt; Back to demo</a>
</body></html>