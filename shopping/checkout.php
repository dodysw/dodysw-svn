<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; $must_logged_in = 1; include 'auth.php'; ob_start();
    $mdl = instantiate_module('member_cart');
    if ($_REQUEST['act'] == 'save')
        $mdl->fe_order($_SESSION['shop_login_id']);


?>
<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<h3>Confirm your order and shipping data</h3>

<? $mdl->fe_list_readonly($_SESSION['shop_login_id'],True);?>

<? $mdl->fe_update_and_checkout($_SESSION['shop_login_id']);?>

<hr><a href="index.php">&lt; Back to demo</a>
</body></html>