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
$relative_script_path = '..';
$no_connect = 0;
include "$relative_script_path/includes/config.php";
include "$relative_script_path/libs/auth.php";
?>
<?php include $relative_script_path.'/libs/htmlheader.php' ?>
<head>
<title>PhpDig : Cleaning index</title>
<?php include $relative_script_path.'/libs/htmlmetas.php' ?>
</head>
<body bgcolor="white">
<h2><?php print phpdigMsg('cleaningindex'); ?></h2>
<?php
$del = 0;
set_time_limit(3600);
$locks = phpdigMySelect($id_connect,'SELECT locked FROM '.PHPDIG_DB_PREFIX.'sites WHERE locked = 1');
if (is_array($locks)) {
    phpdigPrnMsg('onelock');
}
else {
mysql_query('UPDATE '.PHPDIG_DB_PREFIX.'sites SET locked=1',$id_connect);
print phpdigMsg('pwait')." ...<br />";
$query = "SET OPTION SQL_BIG_SELECTS=1";
mysql_query($query,$id_connect);
//list of key_id's in engine table
$query = "SELECT key_id FROM ".PHPDIG_DB_PREFIX."engine GROUP BY key_id";
$id = mysql_query($query,$id_connect);
while (list($key_id) = mysql_fetch_row($id))
       {
       //search this id in the keywords table
       $query = "SELECT key_id FROM ".PHPDIG_DB_PREFIX."keywords WHERE key_id=$key_id";
       $id_key = mysql_query($query,$id_connect);
       if (mysql_num_rows($id_key) < 1)
           {
           //if non-existent, delete this useless id from the engine table
           $del ++;
           print "X ";
           $query_delete = "DELETE FROM ".PHPDIG_DB_PREFIX."engine WHERE key_id=$key_id";
           $id_del = mysql_query($query_delete,$id_connect);
           }
       else
           print ". ";
              mysql_free_result($id_key);
       }

//explore keywords to find bad values
// $query = "SELECT key_id FROM ".PHPDIG_DB_PREFIX."keywords WHERE twoletters REGEXP \"^[^0-9a-zίπώ]\"";
$query = "SELECT key_id FROM ".PHPDIG_DB_PREFIX."keywords WHERE twoletters REGEXP \"^[^".$phpdig_words_chars[PHPDIG_ENCODING]."#$]\"";
$id = mysql_query($query,$id_connect);
if (mysql_num_rows($id) > 0) {
  while (list($key_id) = mysql_fetch_row($id)) {
       echo '° ';
       $query_delete = "DELETE FROM ".PHPDIG_DB_PREFIX."engine WHERE key_id=$key_id";
       mysql_query($query_delete,$id_connect);
  }
}
//list of spider_id from engine table
$query = "SELECT spider_id FROM ".PHPDIG_DB_PREFIX."engine GROUP BY spider_id";
$id = mysql_query($query,$id_connect);
while (list($spider_id) = mysql_fetch_row($id))
       {
       $query = "SELECT spider_id FROM ".PHPDIG_DB_PREFIX."spider WHERE spider_id=$spider_id";
       $id_spider = mysql_query($query,$id_connect);
       if (mysql_num_rows($id_spider) < 1)
           {
           //if no-existent in the spider page, delete from engine
           $del ++;
           print "X ";
           $query_delete = "DELETE FROM ".PHPDIG_DB_PREFIX."engine WHERE spider_id=$spider_id";
           $id_del = mysql_query($query_delete,$id_connect);
           }
       else
           print "- ";
              mysql_free_result($id_spider);
       }

if ($del)
print "<br />$del".phpdigMsg('enginenotok');
else
print "<br />".phpdigMsg('engineok');
mysql_query('UPDATE '.PHPDIG_DB_PREFIX.'sites SET locked=0',$id_connect);
}
?>
<br />
<a href="index.php" target="_top">[<?php phpdigPrnMsg('back'); ?>]</a> <?php phpdigPrnMsg('to_admin'); ?>.
</body>
</html>
