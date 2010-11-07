<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; include_once('auth.php');?>

<html>

<head>
<meta http-equiv="Content-Language" content="en-us">
<meta name="GENERATOR" content="Microsoft FrontPage 6.0">
<meta name="ProgId" content="FrontPage.Editor.Document">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>You need to sign up for a web site hosting account to make your web site
visible to the world</title>
</head>

<body>

<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#CC0000" width="100%" id="AutoNumber1">
  <tr>
    <td width="100%">
    <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="100%" id="AutoNumber2">
      <tr>
        <td width="100%">
        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="100%" id="AutoNumber3" background="images/inside_02.gif">
          <tr>
            <td width="50%">
            <img border="0" src="images/inside_01.gif" align="left" width="330" height="60"></td>
            <td width="50%" align="center">
            <p align="center">
            <img border="0" src="images/inside_04.gif" align="right" width="206" height="60"></td>
          </tr>
        </table>
        </td>
      </tr>
      <tr>
        <td width="100%" height="100%" valign="top">
        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="674" id="AutoNumber4">
          <tr>
            <td width="256" bgcolor="#F2F2F2" valign="top">
            <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="100%" id="AutoNumber5">
              <tr>
                <td width="100%" bgcolor="#E4E4E4">
                <p style="margin-left: 10; margin-right: 10">&nbsp;</td>
              </tr>
              <tr>
                <td width="100%">
                <p align="center"><b><font face="Arial" color="#971312"><br>
                Archieve
                by Category</font></b><font face="Arial"><br>
&nbsp;</font></td>
              </tr>
              <tr>


                <td width="100%">
<form action="news_arch.php">
<p align="center" style="margin-left: 10; margin-right: 10">
<font face="Arial">

<!-- start form archive by cat -->
<? $cat = instantiate_module('news_cat'); $cat->show_combo('cat',$cat->enum_list(),1,1) ?>
<!-- end form archive by cat -->
<input type="submit" value="Submit" name="B1">
</form>
</font><u><font size="2" face="Arial" color="#0000FF"><br>
</font></u>
&nbsp;

</td>


              </tr>
              <tr>

<!-- start Stats Title -->
                <td width="100%" bgcolor="#F2F2F2">
                <p align="center"><font size="2" face="Arial" color="#0000FF">
                Statistics</font><br></td>
<!-- end Stats Title -->

              </tr>
              <tr>
                <td width="100%" bgcolor="#F2F2F2"><br></td>
              </tr>
              <tr>

<!-- start Search Title -->
                <td width="100%" bgcolor="#E4E4E4">
                <p style="margin-left: 10; margin-right: 10" align="center"><b>
                <font face="Arial" color="#971312"><br>
                Search<br>
&nbsp;</font></b></td>
<!-- end Search Title -->

              </tr>
              <tr>

                <td width="100%" bgcolor="#E4E4E4">
<!-- start Search form -->
<? $news = instantiate_module('news'); $news->fe_search_form() ?>

<!-- end Search form -->
&nbsp;</td>


              </tr>
              </table>
            </td>
            <td width="418" valign="top">
            <p style="margin: 10">

<?='Hello, <b>'.$_SESSION['mmclient_login_user'].'</b>. <a href="logout.php?go='.urlencode($_SERVER['PHP_SELF']).'">Log out</a>' ?>

<? $news = instantiate_module('news'); $news->fe_list(5,'*',$_SESSION['mmclient_login_user']) ?>


            <br>
            <br>
&nbsp;<p style="margin: 10">&nbsp;<p style="margin: 10">&nbsp;<p style="margin: 10">&nbsp;<p style="margin: 10">&nbsp;<p style="margin: 10">&nbsp;<p style="margin: 10">&nbsp;<p style="margin: 10">&nbsp;<p style="margin: 10">&nbsp;<p style="margin: 10">&nbsp;<p style="margin: 10">&nbsp;<p style="margin: 10">&nbsp;<p style="margin: 10">&nbsp;<p style="margin: 10">&nbsp;</td>
          </tr>
        </table>
        </td>
      </tr>
      <tr>
        <td width="100%" bgcolor="#971312">&nbsp;</td>
      </tr>
    </table>
    </td>
  </tr>
</table>

</body>

</html>