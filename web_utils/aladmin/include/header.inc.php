<html>
<head>
<title><?=show_html_title()?></title>
<link rel="stylesheet" type="text/css" href="clean-sm.css">
<script type="text/javascript">_editor_url = "./include/htmlarea/";_editor_lang = "en";</script>
<script type="text/javascript" src="./include/htmlarea/htmlarea.js"></script>
</head>
<body bgcolor="#F5F5F5" leftmargin="0" topmargin="0" rightmargin="0" bottommargin="0" <?=$html_body_param?>>
<table id="Table_01" width="100%" border="0" cellpadding="0" cellspacing="0" summary="format header">
  <tr>
    <td><img src="images/logo.gif" alt="al-admin"></td>
    <td align="right">
<?
if ($_SESSION['login_ok'] == 1) {
    #~ echo '<small>'.lang('Hello').', '.$_SESSION['login_user'].'</small>';
    echo lang('Hello').', <b>'.$_SESSION['login_user'].'</b>. <a href="'.$_SERVER['PHP_SELF'].'?m=logout">Logout</a>';
}
?>
    </td>
  </tr>
</table>