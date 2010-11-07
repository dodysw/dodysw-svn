<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; ?>
<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<p>Daftar artikel terbaru (5 terakhir) semua kategori</p>
<? $news = instantiate_module('news'); $news->fe_list(5,array("olahraga","luarnegeri")) ?>
<p>Daftar artikel terbaru (5 terakhir) kategori "politik"</p>
<? $news = instantiate_module('news'); $news->fe_list(5,'politik') ?>

<hr>
<a href="index.php">&lt; Back to demo</a>
</body></html>