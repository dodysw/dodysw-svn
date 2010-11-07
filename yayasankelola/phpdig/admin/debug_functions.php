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
function phpdigDspTable($datas)
{
if(!is_array($datas))
    $datas[0] = 1;
else
    {
    list($id_one) = each($datas);
    reset($datas);
    }
if(!is_array($datas[$id_one]))
    {
    $id = 0;
    while (list($index,$value) = each($datas))
           {
           $datacopy[$id]['index'] = $index;
           $datacopy[$id]['value'] = $value;
           $id++;
           }
    $datas = $datacopy;
    }

    $rows = count($datas);
    $columns = count($datas[$id_one]);
    print "$rows rows & $columns columns<br />";
    print "<table border='1' cellspacing='0' cellpadding='3'>\n";
    print "\t<tr>\n";
    while(list($index) = each($datas[$id_one]))
          {
          print "\t\t<td style='font-weight:bold; background-color:#CCCCCC'>$index</td>\n";
          }
    print "\t</tr>\n";
    reset($datas);
    while(list($index) = each($datas))
           {
           print "\t<tr>\n";
           reset($datas[$index]);
           while(list($useless,$value) = each($datas[$index]))
                 {
                 print "\t\t<td>$value</td>\n";
                 }
           print "\t<tr>\n";
           }
    print "</table>\n";

}
?>