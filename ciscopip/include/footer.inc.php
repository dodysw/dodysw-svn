<p><br><?
if ($_SESSION['login_ok'] == 1) {
    echo "<small>You're logged as: {$_SESSION['login_user']} ({$_SESSION['login_level']},{$_SESSION['login_group']}) | ".date('Y-m-d H:i:s').'</small>';
}
?>

<!-- Begin footer-->

</td></tr></table><!-- from header -->

    <table cellpadding="0" cellspacing="0" width="750">
      <tr>
        <td width="100%" bgcolor="#CCCCCC">
        <img border="0" src="../images/spacer.gif" width="10" height="10"></td>
      </tr>
      <tr>
        <td width="100%" bgcolor="#003433">
        <img border="0" src="../images/spacer.gif" width="10" height="10"></td>
      </tr>
      <tr>
        <td width="100%">
        <p align="center">Copyright ® 2004 Cisco Systems</td>
      </tr>
    </table>

<!-- End footer-->

<!--<small>powered by <a href='mailto:dodysw@gmail.com'>al-admin</a></small>-->
<?=$html_footer_strings?>
</body>
</html>