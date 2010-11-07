<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; include_once('auth.php');?>

<? $news = instantiate_module('news'); $row = $news->get_row(array('rowid'=>$_REQUEST['id'],'media_client'=>$_SESSION['mmclient_login_user'])) ?>

<html>

<head>
<title>Media Monitoring</title>
<link href="style.css" rel="stylesheet" type="text/css">
</head>

<body>

<? $f=$_SESSION['mmclient_login_user'].'_header.inc.php'; if (file_exists($f)) {include $f;} else {include 'header.inc.php';} ?>

        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" width="100%">
          <tr>
            <td width="265" bgcolor="#F2F2F2" valign="top">

<? include 'left.inc.php';?>

            </td>
            <td valign="top">
            <div style="margin: 10">

<p>News: <b><?=$row['title']?></b>
<p>Send this to your friends via email (max 5):
<form method=POST action="news_send_do.php">
<input type=hidden name="news_id" value="<?=$row['rowid']?>">
<br>Friend 1: <input type=text name=email_1 value="<?=$_REQUEST['email_1']?>">
<br>Friend 2: <input type=text name=email_2 value="<?=$_REQUEST['email_2']?>">
<br>Friend 3: <input type=text name=email_3 value="<?=$_REQUEST['email_3']?>">
<br>Friend 4: <input type=text name=email_4 value="<?=$_REQUEST['email_4']?>">
<br>Friend 5: <input type=text name=email_5 value="<?=$_REQUEST['email_5']?>">
<p><input type=submit>
</form>

            </div>
            <br>
            <br>
&nbsp;<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br></td>
          </tr>
        </table>
<? $f=$_SESSION['mmclient_login_user'].'_footer.inc.php'; if (file_exists($f)) {include $f;} else {include 'footer.inc.php';} ?>
</body>
</html>
