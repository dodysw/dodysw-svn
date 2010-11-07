<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; include_once('auth.php');?>

<html>

<head>
<title>Media Monitoring</title>
<link href="style.css" rel="stylesheet" type="text/css">
</head>

<body>

<? $f=$_SESSION['mmclient_login_user'].'_header.inc.php'; if (file_exists($f)) {include $f;} else {include 'header.inc.php';} ?>

<? $news = instantiate_module('news'); $news->fe_stats_form(5,'*') ?>


<? $f=$_SESSION['mmclient_login_user'].'_footer.inc.php'; if (file_exists($f)) {include $f;} else {include 'footer.inc.php';} ?>
</body>
</html>
