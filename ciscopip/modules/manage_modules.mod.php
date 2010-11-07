<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */
include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class manage_modules extends TableManager {
    var $db_table, $properties;
    function manage_modules() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Manage modules';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'sys_manage_modules_tab';
        $this->properties['name'] = new Prop(array('label'=>'Name','required'=>True, 'colname'=>'module_id', 'is_key'=>True));
        $this->properties['description'] = new Prop(array('label'=>'Description','colname'=>'description'));
        $this->properties['tables'] = new Prop(array('colname'=>'table_name', ));
        #~ $this->properties['level'] = new Prop(array('label'=>'Level','colname'=>'level'));
        #~ $this->allow_delete = False;
        #~ $this->allow_edit = False;
        #~ $this->allow_new = False;
        #~ $this->allow_view = False;

        $this->grid_command[] = array('refresh_modules','Refresh Modules');
        $this->grid_command[] = array('create_table','Create Table');
        $this->grid_command[] = array('drop_table','Drop Table');
        $this->grid_command[] = array('purge_table','Purge Table');
        $this->grid_command[] = array('','-----');
    }

    function go() {// called inside main content
        #~ echo "<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&act=refresh_modules'>Refresh modules</a>";
        $this->basic_handler();
    }

    #~ function populate($rowid='',$merge=False) {
        #~ $this->db_count = 0;
        #~ foreach (glob(APP_MODULE_ROOT.'/*.mod.php') as $filename) {
            #~ $this->ds->name[] = str_replace('.mod.php','',basename($filename));
            #~ $this->ds->name[] = str_replace('.mod.php','',basename($filename));
            #~ $this->db_count += 1;
        #~ }
        #~ # exit;
    #~ }

    function act_refresh_modules($post) {
        $sql = 'delete from '.$this->db_table;
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
        foreach (glob(APP_MODULE_ROOT.'/*.mod.php') as $filename) {
            include_once($filename);
            $module_id = str_replace('.mod.php','',basename($filename));
            $prog = new $module_id();
            $sql = "insert into {$this->db_table} (`module_id`,`description`,`table_name`) values ('$module_id','{$prog->title}','{$prog->db_table}')";
            #~ echo '<p>'.$sql;
            $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
        }
        $this->action = 'browse';
    }

    function act_create_table($post) {
        # this function can be triggered from nav panel or manage_module module itself
        $m2 = '';
        if ($this->_rowid) {
            $this->populate($this->_rowid);
            $m2 = $this->ds->name[0];
        }
        else {
            $m2 = $_REQUEST['m2'];
        }
        import_module($m2);
        $prog = new $m2();
        $create_columns = array();
        $key_columns = array();
        $create_columns[] = '`rowid` int unsigned not null auto_increment';
        $alter_add = array();
        $slter_drop = array();
        foreach ($prog->properties as $key=>$col) {
            $datatype = $col->datatype;
            if ($datatype == 'varchar') {
                $len = $col->length == 0? 255: $col->length;
                if ($len>255)
                    $datatype = 'text';
                else
                    $datatype = 'varchar('.$len.')';
            }
            $create_columns[] = "`{$col->colname}` $datatype not null";
            if ($col->is_key) {
                $key_columns[] = '`'.$col->colname.'`';
            }
            $alter_add[] = "alter table `{$prog->db_table}` add `{$col->colname}` $datatype not null;";
            $alter_drop[] = "alter table `{$prog->db_table}` drop `{$col->colname}`;";

        }
        $create_columns[] = 'PRIMARY KEY (`rowid`)';
        if ($key_columns) {
            $key_columns = implode(",",$key_columns);
            $create_columns[] = "UNIQUE KEY `key_1` ($key_columns)";
        }

        $create_columns = implode(",\r\n",$create_columns);
        $sql = "CREATE TABLE `{$prog->db_table}` (\r\n$create_columns\r\n);";
        if (!$post) {
            echo '<pre>'.$sql.'</pre>';
            echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
            echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
            echo '<input type=hidden name=act value="do_sql">';   # contains the action (edit/new)
            echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
            echo '<input type=hidden name=sql value="'.htmlentities($sql).'">';   # contains the action (edit/new)
            echo '<input type=hidden name=go value="'.$_SERVER['PHP_SELF'].'?m='.$_REQUEST['m2'].'">';         # url to go after successful submitation
            echo '<p>Are you sure?</p>';
            echo '<p><input type=submit value=" OK "> | ';
            echo '<b><a href="" onclick="window.history.back();return false;">Cancel</a></b></p>';
            echo '</form>';

            echo '<hr>';
            echo '<pre>'.join("\r\n",$alter_add).'</pre>';
            echo '<hr>';
            echo '<pre>'.join("\r\n",$alter_drop).'</pre>';
            return;
        }


    }

    function act_drop_table($post) {
        import_module($_REQUEST['m2']);
        $prog = new $_REQUEST['m2']();
        $sql = "DROP TABLE `{$prog->db_table}`";
        if (!$post) {
            echo '<pre>'.$sql.'</pre>';
            echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
            echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
            echo '<input type=hidden name=act value="do_sql">';   # contains the action (edit/new)
            echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
            echo '<input type=hidden name=sql value="'.htmlentities($sql).'">';   # contains the action (edit/new)
            echo '<input type=hidden name=go value="'.$_SERVER['PHP_SELF'].'?m='.$_REQUEST['m2'].'">';         # url to go after successful submitation
            echo '<p>Are you sure?</p>';
            echo '<p><input type=submit value=" OK "> | ';
            echo '<b><a href="" onclick="window.history.back();return false;">Cancel</a></b></p>';
            echo '</form>';
            return;
        }
    }

    function act_purge_table($post) {
        import_module($_REQUEST['m2']);
        $prog = new $_REQUEST['m2']();
        $sql = "DELETE FROM `{$prog->db_table}`";
        if (!$post) {
            echo '<pre>'.$sql.'</pre>';
            echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
            echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
            echo '<input type=hidden name=act value="do_sql">';   # contains the action (edit/new)
            echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
            echo '<input type=hidden name=sql value="'.htmlentities($sql).'">';   # contains the action (edit/new)
            echo '<input type=hidden name=go value="'.$_SERVER['PHP_SELF'].'?m='.$_REQUEST['m2'].'">';         # url to go after successful submitation
            echo '<p>Are you sure?</p>';
            echo '<p><input type=submit value=" OK "> | ';
            echo '<b><a href="" onclick="window.history.back();return false;">Cancel</a></b></p>';
            echo '</form>';
            return;
        }
    }

    function act_do_sql($post) {
        if ($post) {
            if (!$this->_save) return;
            $sql = $_REQUEST['sql'];

            $res = mysql_query($sql);
            if ($this->_go) {
                if (!$res) die('<br>'.$sql.'<br>'.mysql_error());
                header('Location: '.$this->_go);
                exit;
            }
            else {
                if ($res)
                    $this->__message = '<p><b>Success</b></p><i>'.$sql.'</i>';
                else
                    $this->__message = '<p class="error">Error</p><p><i>'.$sql.'</i><br>'.mysql_error();
            }
            return;
        }
        #~ if ($this->_save) {
            #~ echo '<h3>SQL has been executed</h3>';
        #~ }
        echo $this->__message;
        echo '<p>Run SQL query/queries on database:';
        echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
        echo '<input type=hidden name=act value="do_sql">';   # contains the action (edit/new)
        echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
        echo '<textarea rows=10 cols=80 name=sql>'.htmlentities($_REQUEST['sql']).'</textarea>';
        #~ echo '<input type=hidden name=go value="'.$_SERVER['PHP_SELF'].'?m='.$_REQUEST['m2'].'">';         # url to go after successful submitation
        echo '<p><input type=submit value=" OK ">';
        echo '</form>';

    }

    function act_merge_table($post) {
        # this function can be triggered from nav panel or manage_module module itself
        $m2 = '';
        if ($this->_rowid) {
            $this->populate($this->_rowid);
            $m2 = $this->ds->name[0];
        }
        else {
            $m2 = $_REQUEST['m2'];
        }
        import_module($m2);
        $prog = new $m2();

        # parse properties first
        $z_fw = array();
        foreach ($prog->properties as $key=>$col) {
            $len = $col->length == 0? 255: $col->length;
            $z_fw[$col->colname] = array();
            $z_fw[$col->colname]['length'] = $len;
            if ($col->datatype == 'varchar') {
                if ($len>255)
                    $col->datatype = 'text';
                else
                    $col->datatype = 'varchar('.$len.')';
            }
            $z_fw[$col->colname]['datatype'] = $col->datatype;
            $z_fw[$col->colname]['is_key'] = $col->is_key ? True: False;
        }

        $z_db = array();
        $suggest_sql = array();

        # real fields form database
        global $appconf,$dbconn;
        $res = mysql_list_fields($appconf['dbname'],$prog->db_table,$dbconn);
        $num_cols = mysql_num_fields($res);
        for ($i = 0; $i < $num_cols; $i++) {
            $colname = mysql_field_name($res, $i);
            # skip for rowid
            if ($colname == 'rowid') continue;
            $length = mysql_field_len($res, $i);
            $flag = mysql_field_flags($res, $i);
            $datatype = mysql_field_type($res, $i);
            $z_db[$colname] = array();
            if ($datatype == 'string')
                $datatype = 'varchar';
            elseif ($datatype == 'real')
                $datatype = 'double';

            $z_db[$colname]['datatype'] = $datatype;
            $z_db[$colname]['length']= $length;
            $z_db[$colname]['flag']= $flag;

           # check colname exist in db, but not exist in prog
           if (!array_key_exists($colname,$z_fw)) { # offter to alter drop
               $suggest_sql[] = "alter table `{$prog->db_table}` drop `{$colname}`      # not exist in program";
               continue;  # avoid alter change
           }
           # check if col has different property, and offer to alter change
           # - datatype ("int", "real", "string", "blob", ...)
           if ($z_db[$colname]['datatype'] == 'varchar')
               $z_db[$colname]['datatype'] = 'varchar('.$z_db[$colname]['length'].')';
           if ($z_db[$colname]['datatype'] != $z_fw[$colname]['datatype']) {
               # offer to alter change
               $suggest_sql[] = "alter table `{$prog->db_table}` change `{$colname}` `{$colname}` {$z_fw[$colname]['datatype']} not null        #".$z_db[$colname]['datatype'].' ==> '.$z_fw[$colname]['datatype'];
            }
        }

        foreach ($z_fw as $colname=>$val) {
            # check colname exist in prog, but not exist in db
            if (!array_key_exists($colname,$z_db)) { # offter to alter add
                $suggest_sql[] = "alter table `{$prog->db_table}` add `{$colname}` {$z_fw[$colname]['datatype']} not null       #not exist in database";
            }
        }


        #~ $sql = "CREATE TABLE `{$prog->db_table}` (\r\n$create_columns\r\n);";
        #~ if (!$post) {
            #~ echo '<pre>'.$sql.'</pre>';
            #~ echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
            #~ echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
            #~ echo '<input type=hidden name=act value="do_sql">';   # contains the action (edit/new)
            #~ echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
            #~ echo '<input type=hidden name=sql value="'.htmlentities($sql).'">';   # contains the action (edit/new)
            #~ echo '<input type=hidden name=go value="'.$_SERVER['PHP_SELF'].'?m='.$_REQUEST['m2'].'">';         # url to go after successful submitation
            #~ echo '<p>Are you sure?</p>';
            #~ echo '<p><input type=submit value=" OK "> | ';
            #~ echo '<b><a href="" onclick="window.history.back();return false;">Cancel</a></b></p>';
            #~ echo '</form>';

            echo '<hr>';
            if ($suggest_sql)
                echo '<pre>'.join("\r\n",$suggest_sql).'</pre>';
            else
                echo '<p><b>Tables already synched</b>';
            #~ echo '<hr>';
            #~ echo '<pre>'.join("\r\n",$alter_drop).'</pre>';
            #~ return;
        #~ }
        echo '<hr>';
        echo '<h3>Complete fields in db</h3>';
        foreach ($z_db as $colname=>$val) {
            echo '<br>'.$colname.' // '.$val['datatype'].' // '.$val['flag'];
        }


    }


}

?>