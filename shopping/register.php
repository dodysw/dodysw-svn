<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; include 'auth.php'; ob_start();
    $mdl = instantiate_module('membership');

?>
<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<h3>Register as member</h3>

<? $mdl->fe_register($_SESSION['shop_login_id']);?>

<hr><a href="index.php">&lt; Back to demo</a>
</body></html>