<!--right panel-->
<?
    #~ if ($prog->allow_query and $prog->action == 'browse' and !$prog->logical_parent) {
        #~ $prog->showquery();
    #~ }

    if ($prog->action == 'browse' and !$prog->logical_parent and ($prog->description != '' or $prog->notes != '')) {
        echo '<p align=center><b>'.$prog->title.'</b>';
        echo ': '.$prog->description;
        echo '<p><small>'.$prog->notes.'</small>';
    }
?>