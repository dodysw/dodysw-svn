<?php

echo "<html><body>";
$count = 0;
$relative_script_path = '..';
$no_connect = 0;

include "$relative_script_path/includes/config.php";
include "$relative_script_path/libs/auth.php";
include "$relative_script_path/admin/robot_functions.php";

$query = mysql_query("SELECT spider_id FROM ".PHPDIG_DB_PREFIX."spider WHERE file = '';");

while ($row = mysql_fetch_array($query)) {
  mysql_query("DELETE FROM ".PHPDIG_DB_PREFIX."engine WHERE spider_id=".$row['spider_id'].";");
  mysql_query("DELETE FROM ".PHPDIG_DB_PREFIX."spider WHERE spider_id=".$row['spider_id'].";");
  phpdigDelText($relative_script_path,$spider_id);
  $count++;
  echo $count . " ";
}

echo "<br>Done. <a href=\"index.php\" target=\"_top\">[Back]</a> to admin interface.";

echo "</body></html>";

?>
