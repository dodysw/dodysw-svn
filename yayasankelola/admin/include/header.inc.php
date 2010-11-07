<?
function show_html_title() {
    global $html_title, $appconf;
    if ($html_title == '' and $appconf['site_title'] == '')
        return 'Al-Admin by Dody Suria Wijaya <dodysw@gmail.com> 2004,2005';
    if ($appconf['site_title'] == '')
        return $html_title;
    return $appconf['site_title'].' - '.$html_title;
}
?>
<html>
<head>
<title><?=show_html_title()?></title>
<link rel="stylesheet" type="text/css" href="clean.css">
<script type="text/javascript">_editor_url = "./include/htmlarea/";_editor_lang = "en";</script>
<script type="text/javascript" src="./include/htmlarea/htmlarea.js"></script>
</head>
<body bgcolor="#F5F5F5" <?=$html_body_param?>>
<table width=100% id="Table_01" border="0" cellpadding="0" cellspacing="0" summary="format header">
  <tr>
    <td width=20% ><img src="images/topa1b.png" alt="Al-Admin: The Admin Toolkit for Web Based Software"></td>
    <td background="images/topa2.jpg" width="100%"></td>
    <td><img src="images/topa3.jpg" alt=""></td>
    <td background="images/topa4.jpg">&nbsp;</td>
  </tr>
</table>