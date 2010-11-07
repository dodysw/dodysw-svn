<hr>
<?
#~ echo '<pre>';print_r($_REQUEST);echo '</pre>';
if ($_SESSION['login_ok'] == 1) {
    echo "<small>".lang("You're logged as").": {$_SESSION['login_user']} ({$_SESSION['login_level']},{$_SESSION['login_group']}) [".date('Y-m-d H:i:s').']</small>';
}
?> <a href='mailto:dodysw@gmail.com'><img src="images/poweredby.png" border="0" alt="powered by al-admin"></a>
<?=$html_footer_strings?>
</body>
</html>