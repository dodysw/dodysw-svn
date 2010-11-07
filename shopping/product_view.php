<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; ?>
<? $mdl = instantiate_module('product'); $mdl->populate($_REQUEST['id']); $row = $mdl->ds->get_row(0);?>
<? $mdl = instantiate_module('product_manufacturer'); $mdl->populate($row['manufacturer']); $logo_id = $mdl->ds->logo[0] ?>


<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>

<h3>Product View</h3>


<h3>

<? $id = $logo_id; if ($id) echo '<img src="getfile.php?id='.$id.'&secure='.secure_hash($id).'">'; else echo 'Image not available'; ?>

<?=$row['product_code']?>

</h3>

<table><tr><td>

<? $id = $row['lg_img']; if ($id) echo '<img src="getfile.php?id='.$id.'&secure='.secure_hash($id).'">'; else echo 'Image not available'; ?>

</td><td valign="top">

<h4><?=$row['product_name']?></h4>
<b>
<p>Type: <?=$row['product_type']?>
<p>Price: Rp <?=number_format($row['price'])?>
<p>Priviledge Price: Rp <?=number_format($row['priviledge_price'])?>
<form method="GET" action="cart.php">
<input type="hidden" name="id" value="<?=$row['_rowid']?>">
<input type="hidden" name="act" value="add">
<input type="submit" name="addtocart" value="Buy">
</form>
<?=$row['notes'] ?>
</b>
</td></tr></table>

<h4>Sub Product</h4>
<? $mdl = instantiate_module('sub_product'); $mdl->fe_list($row['_rowid']); ?>