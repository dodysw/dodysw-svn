<?=$doctype?>
<html>
    <head><title>detik.usable (<?=$tgl_lengkap?>)</title></head>
    <frameset cols="50%,*">
    <frame name="c" target="m" src="<?=$_SERVER['PHP_SELF']?>?x=i">
    <frame name="m" target="_top" src="<?=$_SERVER['PHP_SELF']?>?x=w">
    <noframes>
    <body>
    Looks like u need the <a href="<?=$_SERVER['PHP_SELF']?>?no=frame">non-frame version</a>.
    </body>
    </noframes>
    </frameset>
</html>