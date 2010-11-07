<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; include_once('auth.php');?>

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

<?
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $news = instantiate_module('news');
        $emails = array();
        if ($_REQUEST['email_1'] != '') $emails[] = $_REQUEST['email_1'];
        if ($_REQUEST['email_2'] != '') $emails[] = $_REQUEST['email_2'];
        if ($_REQUEST['email_3'] != '') $emails[] = $_REQUEST['email_3'];
        if ($_REQUEST['email_4'] != '') $emails[] = $_REQUEST['email_4'];
        if ($_REQUEST['email_5'] != '') $emails[] = $_REQUEST['email_5'];
        $news->news_send_email($_REQUEST['news_id'], $emails, $_SESSION['mmclient_login_user']);
        print "<SCRIPT> alert('News has been sent!'); window.history.go(-1); </SCRIPT>\n";
        echo '<h3>News has been send</h3>';
        exit();
    }
?>

            </div>
            <br>
            <br>
&nbsp;<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br></td>
          </tr>
        </table>
<? $f=$_SESSION['mmclient_login_user'].'_footer.inc.php'; if (file_exists($f)) {include $f;} else {include 'footer.inc.php';} ?>
</body>
</html>
