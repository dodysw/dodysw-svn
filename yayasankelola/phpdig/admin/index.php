<?php
/*
--------------------------------------------------------------------------------
PhpDig Version 1.8.x
This program is provided under the GNU/GPL license.
See the LICENSE file for more information.
All contributors are listed in the CREDITS file provided with this package.
PhpDig Website : http://www.phpdig.net/
--------------------------------------------------------------------------------
*/

// prevent caching code from php.net
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false); // HTTP/1.1
header("Pragma: no-cache"); // HTTP/1.0

$relative_script_path = '..';
$no_connect = 0;
include "$relative_script_path/includes/config.php";
include "$relative_script_path/libs/auth.php";

// extract vars
extract( phpdigHttpVars(
     array('message'=>'string')
     ));

?>
<?php include $relative_script_path.'/libs/htmlheader.php' ?>
<head>
<title>PhpDig : <?php phpdigPrnMsg('admin') ?></title>
<?php include $relative_script_path.'/libs/htmlmetas.php' ?>
</head>
<body bgcolor="white">
<div align='center'>
<table><tr><td>
 <a href="<?php print $relative_script_path ?>"><img src="../phpdig_logo_2.png" width="200" height="114" alt="PhpDig <?php print PHPDIG_VERSION ?>" border="0" /></a> 
PhpDig v.<?php print PHPDIG_VERSION ?> Admin Panel<br />
 </td><td>
 <div align='center'>
<?php
$phpdig_tables = array('sites'=>'Hosts','spider'=>'Pages','engine'=>'Index','keywords'=>'Keywords','tempspider'=>'Temporary table');
print "<table class=\"borderCollapse\">\n";
print "<tr><td class=\"greyFormDark\" colspan='2' align='center'><b>".phpdigMsg('databasestatus')."</b></td></tr>\n";
while (list($table,$name) = each($phpdig_tables))
       {
       $result = mysql_fetch_array(mysql_query("SELECT count(*) as num FROM ".PHPDIG_DB_PREFIX."$table"),MYSQL_ASSOC);
       print "<tr>\n\t<td class=\"greyFormLight\">\n$name : </td>\n\t<td class=\"greyForm\">\n<b>".$result['num']."</b>".phpdigMsg('entries')."</td>\n</tr>\n";
       }
print "</table>\n";
?>
 </div>
</td></tr>
<tr>
<td>&nbsp;</td><td>&nbsp;</td>
</tr>
<tr><td valign="top">
<h3><?php phpdigPrnMsg('index_uri') ?></h3>
<form class="grey" action="spider.php" method="post">
<input class="phpdigSelect" type="text" name="url" value="http://" size="56"/>
<br/>
<?php phpdigPrnMsg('spider_depth') ?> :
<select class="phpdigSelect" name="limit">
<?php
//select list for the depth limit of spidering
for($i = 0; $i <= SPIDER_MAX_LIMIT; $i++) {
    print "\t<option value=\"$i\">$i</option>\n";
} ?>
</select>
<?php phpdigPrnMsg('links_per') ?> :
<select class="phpdigSelect" name="linksper">
<?php
//select list for the max links per each depth
for($i = 0; $i <= LINKS_MAX_LIMIT; $i++) {
    print "\t<option value=\"$i\">$i</option>\n";
} ?>
</select>
<input type="submit" name="spider" value="Dig this !" />
</form>
<p class="blue">
- To empty tempspider table click delete button <i>without</i> selecting a site<br>
- Search depth of zero tries to crawl just that page if site is new index<br>
- Set links per depth to the max number of links to check at each depth<br>
- Links per depth of zero means to check for all links at each seach depth<br>
- Clean dashes removes '-' index pages from blue arrow listings of pages<br>
</p>
<p class="blue">
<?php if ($message) { phpdigPrnMsg($message); } ?>
</p>
<div class='grey'>
<a href="cleanup_engine.php"><?php print phpdigMsg('clean')." ".phpdigMsg('t_index'); ?></a> | 
<a href="cleanup_keywords.php"><?php print phpdigMsg('clean')." ".phpdigMsg('t_dic'); ?></a> | 
<a href="cleanup_common.php"><?php print phpdigMsg('clean')." ".phpdigMsg('t_stopw'); ?></a> | 
<a href="cleanup_dashes.php"><?php print phpdigMsg('clean')." ".phpdigMsg('t_dash'); ?></a>
<br><br>
<a href="limit_upd.php"><?php print phpdigMsg('upd_sites'); ?></a> | 
<a href="statistics.php"><?php print phpdigMsg('statistics') ?></a> | 
<a href="logout.php"><?php print phpdigMsg('logout') ?></a>
</div>
</td><td valign="top">
<div align='center'>
<h3><?php phpdigPrnMsg('site_update') ?></h3>
<form action="update_frame.php" >
<select class="phpdigSelect" name="site_ids[]" multiple="multiple" size="10">
<?php
//list of sites in the database
$query = "SELECT site_id,site_url,port,locked FROM ".PHPDIG_DB_PREFIX."sites ORDER BY site_url";
$result_id = mysql_query($query,$id_connect);
while (list($id,$url,$port,$locked) = mysql_fetch_row($result_id))
    {
    if ($port)
        $url .= " (port #$port)";
    if ($locked) {
        $url = '*'.phpdigMsg('locked').'* '.$url;
    }
    print "\t<option value='$id'>$url</option>\n";
    }
?>
</select>
<br/>
<input type="submit" name="update" value="<?php phpdigPrnMsg('updateform'); ?>" />
<input type="submit" name="delete" value="<?php phpdigPrnMsg('deletesite'); ?>" />
</form>
<br/>
</div>
</td></tr></table>
</div>
</body>
</html>