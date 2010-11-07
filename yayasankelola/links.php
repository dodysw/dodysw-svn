<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; ?>
<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<? $link = instantiate_module('linkstruct'); $link->front_list();?>
<hr>
<a href="index.php">&lt; Back to demo</a>
</body></html>