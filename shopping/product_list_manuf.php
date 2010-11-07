<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php';?>
<? $row_per_page = 3; ?>
<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<p><b>Product List by Manufacturer</b></p>

<h4>
<? $mdl = instantiate_module('product_manufacturer'); $mdl->db_where = 'rowid='.$_REQUEST['manuf']; $mdl->populate(); echo $mdl->ds->name[0]; ?>
</h4>

<? $mdl = instantiate_module('product'); $mdl->fe_list(5,0,0,$_REQUEST['manuf']) ?>
<hr><a href="index.php">&lt; Back to demo</a>
</body></html>