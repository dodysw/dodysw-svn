<!--nav panel-->
<?
/*
    horizontal navigation bar, designed to be placed on top page
*/

if ($_SESSION['login_ok'] == 1) {
    #~ echo '<table width="100%" summary="main navigator"><tr>';
    echo '<td>';
    echo '<form method=get action="'.$_SERVER['PHP_SELF'].'">';
    echo '<select name="m" onchange="this.form.submit()">';
    foreach ($navdata['main'] as $row) {
        sn($row[0],$row[1],$row[2]);
    }
    echo '</select>';
    echo '<input type=submit value='.lang('Go').'>';
    echo '</form>';

    echo '<table><tr>';
    echo '</tr></table>';
    echo '</td>';

    if ($_SESSION['login_ok'] == 1 and $_SESSION['login_level'] == 0) {
        echo '<td>';
        echo '<form method=get action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name="m" value="manage_modules">';
        echo '<input type=hidden name="m2" value="'.$_REQUEST['m'].'">';
        echo '<select name="act" onchange="this.form.submit()">';
        sn('','__'.lang('Manage Modules').'__',0);
        if ($prog->db_table != '') {
            foreach ($navdata['manage_module'] as $row) {
                sn($row[0],$row[1]);
            }
        }
        echo '</select>';
        echo '<input type=submit value='.lang('Go').'>';
        echo '</form>';
        echo '</td>';
    }
    #~ if ($prog->allow_query and $prog->action == 'browse' and !$prog->logical_parent) {
        #~ $prog->showquery();
    #~ }
    #~ echo '</table>';
}


?>