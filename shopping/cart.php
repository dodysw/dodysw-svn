<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; $must_logged_in = 1; include 'auth.php';
    $mdl = instantiate_module('member_cart');
    if ($_REQUEST['act'] == 'add') {
        $mdl->fe_add_item($_SESSION['shop_login_id'],$_REQUEST['id']);
        header('Location: cart.php');
    }
    elseif ($_REQUEST['act_update'] != '')
        $mdl->fe_update($_SESSION['shop_login_id']);
?>
<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<p><b>Your Shopping Cart</b></p>

<? $mdl->fe_list($_SESSION['shop_login_id']);?>

<hr><a href="index.php">&lt; Back to demo</a>
</body></html>