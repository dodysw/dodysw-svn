<!--nav panel-->
<?

function sl($m,$title) {
    if ($_REQUEST['m'] == $m) {
        echo "<tr><td>$title</td></tr>";
    }
    else {
        echo "<tr><td><a href='{$_SERVER['PHP_SELF']}?m=$m'>$title</a></td></tr>";
    }
}

function sn0($m,$title,$level,$indent=0) {
    # level note: -2 available even when not authenticated, -1 always available when authenticated, 1 highest level of permission, 2+ lower and lower permission
    if ($_SESSION['login_ok'] != 1 and $level != -2) return;
    if ($_SESSION['login_ok'] == 1 and $level != -1 and $_SESSION['login_level'] > $level) return;
    if ($indent) {
        for ($i = 0; $i < $indent; $i++) {
            echo '&nbsp;&nbsp;';
        }
        echo '- ';
    }
    if ($_REQUEST['m'] == $m) {
        echo "<b><a href='{$_SERVER['PHP_SELF']}?m=$m'>$title</a></b><br>";
    }
    else {
        echo "<a href='{$_SERVER['PHP_SELF']}?m=$m'>$title</a><br>";
    }
}

function sn($m,$title,$level,$group='',$indent=0) {
    /*
    Generate html proper 1 line of <options> considering level, group, title, module, and indentation
    @level: int
            -2 available even when not authenticated,
            -1 always available when authenticated,
            1 highest level of permission,
            2+ lower and lower permission
    @group: string or array
            group or groups allowed to use this program
    @indent: int
            number of space prefixed
    */
    if ($_SESSION['login_ok'] != 1 and $level != -2) return;
    if ($_SESSION['login_ok'] == 1 and $level != -1 and $_SESSION['login_level'] > $level) return;  # level permission, allow if login_level is smaller
    if ($_SESSION['login_level'] != 0 and $_SESSION['login_level'] == $level and $group != '') {   # check group permission if not '' or empty array
        if (is_array($group)) {
            $pass = False;
            foreach ($group as $grp) {
                if ($grp == $_SESSION['login_group']) {
                    $pass = True;
                    break;
                }
            }
            if (!$pass) return;
        }
        elseif ($group != $_SESSION['login_group']) return;
    }
    if ($indent) {
        for ($i = 0; $i < $indent; $i++) {
            echo '&nbsp;&nbsp;';
        }
        echo '- ';
    }
    if ($_REQUEST['m'] == $m) {
        echo '<option value="'.$m.'" selected>'.$title.'</option>';
    }
    else {
        echo '<option value="'.$m.'">'.$title.'</option>';
    }
}
if ($_SESSION['login_ok'] == 1) {
    echo '<table width="100%" summary="main navigator"><tr>';
    echo '<td>';
    echo '<form method=get action="'.$_SERVER['PHP_SELF'].'">';
    echo '<select name="m" onchange="this.form.submit()">';
    # level note: -2 available even when not authenticated,
    # -1 always available when authenticated,
    # 0 sysadmin, only for developer,
    # 1 highest level of user permission,
    # 2+ lower and lower permission
    sn('','_____'.lang('System').'_____',1);
    sn('admfp',lang('Home'),-1);
    #~ sn('login','Login',-2);
    sn('logout','Logout',-1);
    sn('phpinfo','phpinfo',0);
    sn('manage_modules',lang('Manage Modules'),0);
    sn('seq_gen',lang('Sequence Generator'),0);
    sn('usrmgr',lang('User Manager'),1);

    sn('','_____'.lang('News').'_____',1);
    sn('news','Berita',1);
    sn('news_cat','Kategori Berita',1);
    sn('','_____'.lang('Directory').'_____',1);
    sn('dirstruct','Direktori',1);
    sn('dir_entry','Entri Direktori',1);
    sn('','_____'.lang('Links').'_____',1);
    sn('linkstruct','Link',1);
    sn('link_entry','Entri Link',1);

    echo '</select>';
    echo '<input type=submit value='.lang('Go').'>';
    echo '</form>';
    echo '</td>';


    if ($_SESSION['login_ok'] == 1 and $_SESSION['login_level'] == 0) {
        echo '<td>';
        echo '<form method=get action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name="m" value="manage_modules">';
        echo '<input type=hidden name="m2" value="'.$_REQUEST['m'].'">';
        echo '<select name="act" onchange="this.form.submit()">';
        sn('','__'.lang('Manage Modules').'__',0);
        if ($prog->db_table != '') {
            sn('ingen_csv',lang('Generate CSV for input (Comma)'),0);
            sn('ingen_csv_tab',lang('Generate CSV for input (Tab)'),0);
            sn('enter_ingen_csv',lang('Enter CSV for input'),0);
            sn('','______________',0);
            sn('create_table',lang('Create table'),0);
            sn('merge_table',lang('Merge changes'),0);
            sn('do_sql','Sql',0);
            sn('','______________',0);
            sn('drop_table',lang('Drop table'),0);
            sn('purge_table',lang('Purge table'),0);

        }
        echo '</select>';
        echo '<input type=submit value='.lang('Go').'>';
        echo '</form>';
        echo '</td>';

    }


    if ($prog->allow_query and $prog->action == 'browse' and !$prog->logical_parent) {
        $prog->showquery();
    }

    echo '</table>';
}


?>