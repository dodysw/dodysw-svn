<?
function show_html_title() {
    global $html_title, $appconf;
    if ($html_title == '' and $appconf['site_title'] == '')
        return 'Al-Admin Framework by Dody Suria Wijaya <dodysw@gmail.com> 2004';
    if ($appconf['site_title'] == '')
        return $html_title;
    return $appconf['site_title'].' - '.$html_title;
}
?>
<html>
<head>
<title><?=show_html_title()?></title>
<link rel="stylesheet" type="text/css" href="clean.css">
<link href="style.css" rel="stylesheet" type="text/css">
<script src="swapimage.js"></script>
<script type="text/javascript">
  _editor_url = "./include/htmlarea/";
  _editor_lang = "en";
</script>
<script type="text/javascript" src="./include/htmlarea/htmlarea.js"></script>
</head>


<body bgcolor="#FFFFFF" <?=$html_body_param?> LEFTMARGIN=0 TOPMARGIN=0 MARGINWIDTH=0 MARGINHEIGHT=0>
<!--<div style="background-color:77f;color:bdf"><b><i>Cisco Premier Incentive Program</i></b></div>-->

<!-- Begin Header-->

<table cellpadding="0" cellspacing="0" width="750">
  <tr>
    <td width="100%">

 <table cellpadding="0" cellspacing="0" width="750">
      <tr>
        <td width="110">
			<IMG SRC="../images/logo_cisco.gif" WIDTH=110 HEIGHT=73 ALT=""></td>
        <td valign="bottom">
        <table cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td width="100%">&nbsp;</td>
          </tr>
          <tr>
            <td width="100%">&nbsp;<table cellpadding="0" cellspacing="0">
              <tr>
                <td valign="bottom">
			<IMG SRC="../images/topgreen_stripe.gif" WIDTH=470 HEIGHT=8 ALT=""></td>
                <td><p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <a href="http://www.cisco.com">www.cisco.com</a></td>
              </tr>
            </table>
            </td>
          </tr>
          <tr>
            <td width="100%" bgcolor="#003433" height="5">
            <img border="0" src="../images/spacer.gif" width="8" height="8"></td>
          </tr>
        </table>
        </td>
      </tr>
    </table>
    <table cellpadding="0" cellspacing="0" width="100%">
      <tr>
        <td width="19" background="../images/left_greystripe.gif">
			<IMG SRC="../images/left_greystripe.gif" WIDTH=14 HEIGHT=107 ALT=""></td>
        <td>
        <table cellpadding="0" cellspacing="0" width="100%">
          <tr>
            <td bgcolor="#CCCCCC">&nbsp;</td>
            <td bgcolor="#CCCCCC">&nbsp;</td>
          </tr>
          <tr>
            <td bgcolor="#FFFFFF">
            <img border="0" src="../images/spacer.gif" width="2" height="2"></td>
            <td bgcolor="#FFFFFF">
            <img border="0" src="../images/spacer.gif" width="2" height="2"></td>
          </tr>
          <tr>
            <td>
			<IMG SRC="../images/partner_vis.jpg" WIDTH=187 HEIGHT=87 ALT=""></td>
            <td>
			<IMG SRC="../images/headline.gif" WIDTH=542 HEIGHT=87 ALT=""></td>
          </tr>
          <tr>
            <td bgcolor="#FFFFFF">
            <img border="0" src="../images/spacer.gif" width="2" height="2"></td>
            <td bgcolor="#FFFFFF">
            <img border="0" src="../images/spacer.gif" width="2" height="2"></td>
          </tr>
        </table>
        </td>
      </tr>
    </table>

<!-- note </td></tr></table> will be shown on footer-->

<!-- End Header-->
   <table cellpadding="0" cellspacing="0" width="750">
      <tr>
        <td width="100%" bgcolor="#FF9900">
        <table cellpadding="0" cellspacing="0">
          <tr>
            <td width="19">&nbsp;</td>
            <td>
<?
    if ($_SESSION['login_group'] == 'DE') {
        echo '<a href="'.$_SERVER['PHP_SELF'].'?m=admfp"><img border="0" src="images/menu_hom.gif" width="65" height="25"></a>
            <a href="'.$_SERVER['PHP_SELF'].'?m=news"><img border="0" src="images/menu_new.gif" width="72" height="25"></a>
            <a href="'.$_SERVER['PHP_SELF'].'?m=partner_simple"><img border="0" src="images/menu_par.gif" width="115" height="25"></a>

            <a href="'.$_SERVER['PHP_SELF'].'?m=logout"><img border="0" src="images/menu_log.gif" width="82" height="25"></a>&nbsp;&nbsp;&nbsp;&nbsp;';
            //<a href="'.$_SERVER['PHP_SELF'].'?m=partner"><img border="0" src="images/menu_pas.gif" width="132" height="25"></a>
    }
    elseif ($_SESSION['login_group'] == 'PA') {
        echo '<a href="'.$_SERVER['PHP_SELF'].'?m=admfp"><img border="0" src="images/menu_hoo.gif" width="65" height="25"></a>
            <a href="'.$_SERVER['PHP_SELF'].'?m=news"><img border="0" src="images/menu_nex.gif" width="72" height="25"></a>
            <a href="'.$_SERVER['PHP_SELF'].'?m=partner_simple"><img border="0" src="images/menu_myq.gif" width="95" height="25"></a>
            <a href="'.$_SERVER['PHP_SELF'].'?m=project"><img border="0" src="images/menu_prr.gif" width="87" height="25"></a>
            <a href="'.$_SERVER['PHP_SELF'].'?m=logout"><img border="0" src="images/menu_loi.gif" width="82" height="25"></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

    }
    elseif ($_SESSION['login_group'] == 'PO') {
        echo '<a href="'.$_SERVER['PHP_SELF'].'?m=admfp"><img border="0" src="images/menu_hon.gif" width="65" height="25"></a>
            <a href="'.$_SERVER['PHP_SELF'].'?m=usrmgr_po"><img border="0" src="images/menu_myp.gif" width="95" height="25"></a>
            <a href="'.$_SERVER['PHP_SELF'].'?m=project_google"><img border="0" src="images/menu_pro.gif" width="87" height="25"></a>
            <a href="'.$_SERVER['PHP_SELF'].'?m=partner_rpt1"><img border="0" src="images/menu_prp.gif" width="132" height="25"></a>
            <a href="'.$_SERVER['PHP_SELF'].'?m=partner_rpt2"><img border="0" src="images/menu_prq.gif" width="131" height="25"></a>
            <a href="'.$_SERVER['PHP_SELF'].'?m=logout"><img border="0" src="images/menu_loh.gif" width="82" height="25"></a>';
    }
?>
            &nbsp;</td>
            <td>
			&nbsp;</td>
            <td>
			&nbsp;</td>
          </tr>
        </table>
        </td>
      </tr>
    </table>