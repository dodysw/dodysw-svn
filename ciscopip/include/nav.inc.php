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
if ($_SESSION['login_ok'] == 1 and $_SESSION['login_group'] == '') {
    echo '<table width="100%"><tr>';
    echo '<form method=get action="'.$_SERVER['PHP_SELF'].'">';
    echo '<td>';
    echo '<select name="m" onchange="this.form.submit()">';
    # level note: -2 available even when not authenticated,
    # -1 always available when authenticated,
    # 0 sysadmin, only for developer,
    # 1 highest level of user permission,
    # 2+ lower and lower permission
    sn('','_____System_____',1);
    sn('admfp','Home',-1);
    #~ sn('login','Login',-2);
    sn('logout','Logout',-1);
    sn('phpinfo','phpinfo',0);
    sn('manage_modules','Manage Modules',0);
    sn('project_invitation','Scheduled Project Invitation',0);
    sn('seq_gen','Sequence Generator',0);
    sn('usrmgr','User Manager',1);
    sn('kode_area','Kode area',1);

    sn('','_____User Manager_____',1);
    sn('usrmgr_de','Data Entry User',1);
    sn('usrmgr_po','Project Officer User',2,array('PO'));
    #~ sn('usrmgr_pa','Partner User',2);
    sn('partner','Partner Profile',2,array('DE','PA'));
    sn('partner_simple','Partner List',2,array('DE'));
    sn('project','Projects',2,array('PO','PA'));

    sn('','_____News_____',1);
    sn('news','News Manager',1);
    #~ sn('category','Category',1);

    sn('','_____Reports_____',1);
    sn('partner_rpt1','Partner\'s projects',2,'PO');
    sn('partner_rpt2','Partner\'s project status',2,'PO');
    echo '</select>';
    echo '<input type=submit value=Go>';
    echo '</td>';
    echo '</form>';

    if ($_SESSION['login_ok'] == 1 and $_SESSION['login_level'] == 0) {
        echo '<td>';
        echo '<form method=get action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name="m" value="manage_modules">';
        echo '<input type=hidden name="m2" value="'.$_REQUEST['m'].'">';
        echo '<select name="act" onchange="this.form.submit()">';
        sn('','Manage modules',0);
        if ($prog->db_table != '') {
            sn('create_table','Create table',0);
            sn('merge_table','Merge changes',0);
            sn('do_sql','Sql',0);
            sn('','______________',0);
            sn('drop_table','Drop table',0);
            sn('purge_table','Purge table',0);

        }
        echo '</select>';
        echo '<input type=submit value=Go>';
        echo '</td>';
        echo '</form>';
    }


    if ($prog->allow_query and $prog->action == 'browse' and !$prog->logical_parent) {
        $prog->showquery();
    }

    echo '</table>';
}


?>