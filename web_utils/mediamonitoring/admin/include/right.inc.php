<!--right panel-->
<?
    #~ if ($prog->allow_query and $prog->action == 'browse' and !$prog->logical_parent) {
        #~ $prog->showquery();
    #~ }

    if ($prog->action == 'browse' and !$prog->logical_parent and ($prog->description != '' or $prog->notes != '')) {
        echo '<table align="right" bgcolor="#B4D0DC" border="0" cellspacing="0" width="150"><tr><td><table border="0" cellpadding="3" cellspacing="0" width="100%"><tr><td bgcolor="#ECF8FF">';
        echo '<p align=center><b>'.$prog->title.'</b>';
        echo ': '.$prog->description;
        echo '<p><small>'.$prog->notes.'</small>';
        echo '</td></tr></table></td></tr></table>';
    }
?>