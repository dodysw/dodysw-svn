<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; ?>
<html><head><title>Frontend Example</title><link rel="stylesheet" type="text/css" href="clean.css"></head><body>
<p><b>Daftar arsip artikel kategori "politik"</b></p>
<? $news = instantiate_module('news'); $news->show_list_archieve(0,array('luarnegeri','olahraga')) ?>
<hr><a href="index.php">&lt; Back to demo</a>
</body></html>