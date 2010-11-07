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
include "$relative_script_path/admin/robot_functions.php";

// extract http vars
extract(phpdigHttpVars(array('type' => 'string')));

set_time_limit(300);
?>
<?php include $relative_script_path.'/libs/htmlheader.php' ?>
<head>
<title>PhpDig : <?php phpdigPrnMsg('statistics') ?> </title>
<?php include $relative_script_path.'/libs/htmlmetas.php' ?>
</head>
<body bgcolor="white">
<table><tr><td valign="top">
<img src="../phpdig_logo_2.png" width="200" height="114" alt="PhpDig <?php print PHPDIG_VERSION ?>" /><br />
<h1><?php phpdigPrnMsg('statistics') ?></h1>
<p class='grey'>
<a href="statistics.php?type=mostkeys"><?php phpdigPrnMsg('mostkeywords') ?></a>
<br /><a href="statistics.php?type=mostpages"><?php phpdigPrnMsg('richestpages') ?></a>
<br /><a href="statistics.php?type=mostterms"><?php phpdigPrnMsg('mostterms') ?></a>
<br /><a href="statistics.php?type=largestresults"><?php phpdigPrnMsg('largestresults') ?></a>
<br /><a href="statistics.php?type=mostempty"><?php phpdigPrnMsg('mostempty') ?></a>
<br /><a href="statistics.php?type=lastqueries"><?php phpdigPrnMsg('lastqueries') ?></a>
<br /><a href="statistics.php?type=responsebyhour"><?php phpdigPrnMsg('responsebyhour') ?></a>
<br /><a href="statistics.php?type=lastclicks"><?php phpdigPrnMsg('lastclicks') ?></a>
</p>
<a href="index.php" target="_top">[<?php phpdigPrnMsg('back') ?>]</a> <?php phpdigPrnMsg('to_admin') ?>.
</td><td valign="top">
<?php
if ($type)
    {
    $query = "SET OPTION SQL_BIG_SELECTS=1";
    mysql_query($query,$id_connect);

    $start_table_template = "<table class=\"borderCollapse\">\n";
    $end_table_template = "</table>\n";
    $line_template = "<tr>%s</tr>\n";
    $title_cell_template = "\t<td class=\"blueForm\">%s</td>\n";
    $cell_template[0] = "\t<td class=\"greyFormDark\">%s</td>\n";
    $cell_template[1] = "\t<td class=\"greyForm\">%s</td>\n";
    $cell_template[2] = "\t<td class=\"greyFormLight\">%s</td>\n";
    $cell_template[3] = "\t<td class=\"greyForm\">%s</td>\n";

    $mod_template = count($cell_template);
    flush();

    $result = phpdigGetLogs($id_connect,$type);

    if ((is_array($result)) && (count($result) > 0)) {
        print $start_table_template;
        // title line
        $title_line = '';
        list($i,$titles) = each($result);
        foreach($titles as $field => $useless) {
            $title_line .= sprintf($title_cell_template,ucwords(str_replace('_',' ',$field)));
        }
        printf($line_template,$title_line);
        foreach($result as $id => $row) {
           $this_line = '';
           $id_row_style = $id % $mod_template;
           foreach ($row as $value) {
                $this_line .= sprintf($cell_template[$id_row_style],$value);
           }
           printf($line_template,$this_line);
        }
        print $end_table_template;
    }
    }
?>
</td></tr></table>
</body>
</html>