<!--nav panel-->
<?
/*
    horizontal navigation bar, designed to be placed on top page

*/

if ($_SESSION['login_ok'] == 1 and $prog->navbar) {
    foreach ($navdata['main'] as $row) {
        # check level
        if ($row[2] != -1 and $row[2] < $_SESSION['login_level']) continue;
        if ($row[0] == '') { # title
            echo '<p><b>'.$row[1].'</b><br>';
        }
        elseif ($row[0] == $_REQUEST['m']) {
            echo '&nbsp;&nbsp;<b><font color="#CC9900"><a href="'.$_SERVER['PHP_SELF'].'?m='.$row[0].'">'.$row[1].'</a></font></b><br>';
        }
        else {
            echo '&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?m='.$row[0].'">'.$row[1].'</a><br>';
        }
    }
    if ($_SESSION['login_ok'] == 1 and $_SESSION['login_level'] == 0) {
        echo '<p>';
        if ($prog->db_table != '') {
            foreach ($navdata['manage_module'] as $row) {
                if ($row[0] == '') { # title
                    echo '<p><b>'.$row[1].'</b><br>';
                }
                else
                    echo '&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?m=manage_modules&m2='.$_REQUEST['m'].'&act='.$row[0].'">'.$row[1].'</a><br>';
            }
        }
    }
    #~ if ($prog->allow_query and $prog->action == 'browse' and !$prog->logical_parent) {
        #~ $prog->showquery();
    #~ }
}


?>