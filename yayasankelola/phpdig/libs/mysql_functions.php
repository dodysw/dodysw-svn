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
//===============================================
//executes a select and returns a whole resultset
function phpdigMySelect($id_connect,$query_select)
{
if (!eregi('^[^a-z]*select',$query_select))
     return -1;
$res_id = mysql_query($query_select,$id_connect);
if (!$res_id) {
     // print mysql_error();
     return 0;
}
if (mysql_num_rows($res_id) > 0)
    {
    $result = array();
    while ($res_datas = mysql_fetch_array($res_id,MYSQL_ASSOC))
           {
           array_push($result,$res_datas);
           }
    return $result;
    }
else
    return 0;
}

//===============================================
// verify phpdig_tables
function phpdigCheckTables($id_connect,$tables=array()) {
     $res_id = mysql_query('SHOW TABLES',$id_connect);
     if (!$res_id) {
        die('Unable to check table. Check connection parameters'."\n");
     }
     $num_to_reach = count($tables);
     $num_find = 0;
     foreach ($tables as $id => $table) {
         $tabname[PHPDIG_DB_PREFIX.$table] = 0;
     }
     while ($row = mysql_fetch_row($res_id)) {
         if (isset($tabname[$row[0]])) {
             $tabname[$row[0]] = 1;
             $num_find ++;
         }
     }
     if ($num_find != $num_to_reach) {
         foreach ($tabname as $tablename => $exists) {
             if (!$exists) {
                  print "Table $tablename missing.\n";
             }
         }
         die("\n");
     }
}
?>