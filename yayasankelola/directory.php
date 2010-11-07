<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; ?>
<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<? $dir = instantiate_module('dirstruct'); $dir->front_list();?>
<hr><a href="index.php">&lt; Back to demo</a>
</body></html>