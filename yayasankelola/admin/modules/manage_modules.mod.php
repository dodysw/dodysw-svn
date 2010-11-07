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

        $this->unit = 'module';
        #~ $this->properties['level'] = new Prop(array('label'=>'Level','colname'=>'level'));
        #~ $this->allow_delete = False;
        #~ $this->allow_edit = False;
        #~ $this->allow_new = False;
        #~ $this->allow_view = False;

        $this->grid_command[] = array('refresh_modules','Refresh Modules');
        $this->grid_command[] = array('','_________');
        $this->grid_command[] = array('ingen_csv',lang('Generate CSV for input (Comma)'));
        $this->grid_command[] = array('enter_ingen_csv',lang('Enter CSV for input'));
        $this->grid_command[] = array('','_________');
        $this->grid_command[] = array('create_table',lang('Create Table'));
        $this->grid_command[] = array('drop_table',lang('Drop Table'));
        $this->grid_command[] = array('purge_table',lang('Purge Table'));
        $this->grid_command[] = array('','_________');
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
            # echo '<p>'.$sql;
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
            echo '<p>'.lang('Are you sure').'?</p>';
            echo '<p><input type=submit value=" OK "> | ';
            echo '<b><a href="" onclick="window.history.back();return false;">'.lang('Cancel').'</a></b></p>';
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
            echo '<p>'.lang('Are you sure').'?</p>';
            echo '<p><input type=submit value=" OK "> | ';
            echo '<b><a href="" onclick="window.history.back();return false;">'.lang('Cancel').'</a></b></p>';
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
            echo '<p>'.lang('Are you sure').'?</p>';
            echo '<p><input type=submit value=" OK "> | ';
            echo '<b><a href="" onclick="window.history.back();return false;">'.lang('Cancel').'</a></b></p>';
            echo '</form>';
            return;
        }
    }

    function act_do_sql($post) {
        if ($post) {
            if (!$this->_save) return;
            # split $sql with ;\n
            $_REQUEST['sql'] = mystripslashes($_REQUEST['sql']);
            $sqls = split(";\r\n", $_REQUEST['sql']);
            foreach ($sqls as $sql) {
                $res = mysql_query($sql);
                if ($res)
                    $this->__message .= '<p><b>Success</b>:'.$sql.'</p>';
                else
                    $this->__message .= '<p class="error"><b>Error</b>: '.$sql.'<br>'.mysql_error().'</p>';
                if ($this->_go) {
                    if (!$res) die($this->__message);
                    header('Location: '.$this->_go);
                    exit;
                }
            }
            return;
        }
        #~ if ($this->_save) {
            #~ echo '<h3>SQL has been executed</h3>';
        #~ }
        echo $this->__message;
        echo '<p>Run SQL query/queries on database: (for multi sql, separate different sql statement with ";" followed by new line)';
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
               $suggest_sql[] = "alter table `{$prog->db_table}` drop `{$colname}`;";
               continue;  # avoid alter change
           }
           # check if col has different property, and offer to alter change
           # - datatype ("int", "real", "string", "blob", ...)
           if ($z_db[$colname]['datatype'] == 'varchar')
               $z_db[$colname]['datatype'] = 'varchar('.$z_db[$colname]['length'].')';
           if ($z_db[$colname]['datatype'] != $z_fw[$colname]['datatype']) {
               # offer to alter change
               $suggest_sql[] = "alter table `{$prog->db_table}` change `{$colname}` `{$colname}` {$z_fw[$colname]['datatype']} not null;";
            }
        }

        foreach ($z_fw as $colname=>$val) {
            # check colname exist in prog, but not exist in db
            if (!array_key_exists($colname,$z_db)) { # offter to alter add
                $suggest_sql[] = "alter table `{$prog->db_table}` add `{$colname}` {$z_fw[$colname]['datatype']} not null;";
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



    function csv_separa ($str, $delim=';', $qual="\"")
    {
           // Largo de la línea
           $largo=strlen($str);
           // Dentro
           $dentro=false;
           // Palabra
           $palabra="";
           // Bucle
           for ( $i=0; $i<$largo; $i++)
           {
                   if ( $str[$i] == $delim && !$dentro )
                   {
                           $salida[] = $palabra;
                           $palabra="";
                   }
                   else if ( $str[$i] == $qual && ( $i<$largo && $str[$i+1] == $qual ) )
                   {
                           $palabra .= $qual;
                           $i++;
                   }
                   else if ( $str[$i] == $qual )
                   {
                           $dentro = !$dentro;
                   }
                   else
                   {
                           $palabra .= $str[$i];
                   }
           }
           // Devolvemos la matriz
           $salida[]=$palabra;
           return $salida;
    }

    function act_enter_ingen_csv($post) {
        if ($post) {
            $field_splitter = ($_REQUEST['field_splitter'] == 'tab')? "\t" : ',';
            # parse csv input

            //fgetcsv must receive non-slashed string, so if magic quote is on, we should un-slashed it for a moment
            $body = mystripslashes($_REQUEST['body']);
            $fp = tmpfile();
            fwrite($fp,$body);
            fseek($fp, 0);
            $rows = array();
            while ($row = fgetcsv($fp,10000,$field_splitter)) {
                $rows[] = $row;
            }
            fclose($fp);
            if (count($rows) < 3) {
                $this->__message = '<p><b>Warning: Inserted CSV Row is less than 3 (only '.count($rows).')!! You must paste WITH the first 2 row intact, as given by Generate CSV for Input.';
                return;
            }

            #~ print_r($rows);exit;

            $prog = instantiate_module($_REQUEST['m2']);

            # check first row for consistency
            $dedupes = array();
            #~ $row = $this->csv_separa($rows[0],$field_splitter);
            $row = $rows[0];
            for ($i = 0; $i < count($row); $i++) {
                if ($i == count($row)-1 and $row[$i] == '') continue;
                if (array_key_exists($row[$i],$dedupes)) {
                    $this->__message = '<p><b>Colvar "'.$row[$i].'" defined more than one time in csv.</b>';
                    return;
                }
                $dedupes[$row[$i]] = 1;
                if (!array_key_exists($row[$i], $prog->properties)) {
                    $this->__message = '<p><b>Colvar "'.$row[$i].'" does not exist in program.</b';
                    return;
                }
            }
            $colvars = $row;

            # skip second row

            # parse third row and so on
            for ($i = 2; $i < count($rows); $i++) {
                #~ $row = $this->csv_separa($rows[$i],$field_splitter);
                $row = $rows[$i];
                for ($i2 = 0; $i2 < count($row); $i2++) {
                    #~ $this->ds->{$colvars[$i2]}[$i] = $row[$i2];
                    //return the "slash states" previously unslashed (for magic quote) so that codes relying on detecting magic quote afterward would behave as expectedly
                    $_REQUEST['field'][$colvars[$i2]][$i-2] = get_magic_quotes_gpc()? addslashes($row[$i2]) : $row[$i2];
                }
                $_REQUEST['rowid'][$i-2] = '';
            }
            $_REQUEST['num_row'] = count($rows)-2;
            # $this->db_count = count($rows)-2;
            # $this->_save = 1;
            $_REQUEST['save'] = '';
            $prog->_save == '';
            $prog->action = 'new';
            $prog->import2ds();
            $prog->post_handler();
            if (!$prog->showerror()) {
                $this->__message = '<p><b>'.$prog->db_count.' rows inserted</b>';
            }
            $_REQUEST['body'] = mystripslashes($_REQUEST['body']);
            return;
        }
        echo $this->__message;
        echo '<p>Paste CSV input from Excel:';
        echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
        echo '<input type=hidden name=m2 value="'.$_REQUEST['m2'].'">';
        echo '<input type=hidden name=act value="enter_ingen_csv">';   # contains the action (edit/new)
        echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
        echo '<textarea rows=10 cols=80 name=body>'.htmlentities($_REQUEST['body']).'</textarea>';
        echo '<input type=hidden name=go value="'.$_REQUEST['go'].'">';         # url to go after successful submitation
        echo '<p>Field splitter: <input type=radio name="field_splitter" value="tab" checked>Tab | <input type=radio name="field_splitter" value="comma">Comma';
        echo '<p><input type=submit value=" OK ">';
        echo '</form>';
        echo '<p>&lt; &lt; <a href="'.$_SERVER['PHP_SELF'].'?m='.$_REQUEST['m2'].'">Back to module '.$_REQUEST['m2'].'</a></p>';

    }

    function act_ingen_csv_tab($post) {
        return $this->act_ingen_csv($post,"\t");
    }

    function act_ingen_csv($post,$field_separator=',') {
        /* generate CSV representation of loaded datasource
        http://www.creativyst.com/Doc/Articles/CSV/CSV01.htm
        */
        #~ if (!$post) {
            #~ return;
        #~ }
        $m2 = $_REQUEST['m2'];
        import_module($m2);
        $prog = new $m2();
        $prog->populate();
        $rows = array();

        # colvar row
        $fields = array();
        foreach ($prog->properties as $colvar=>$col) {
            $vtemp = $colvar;
            $vtemp = str_replace('"','""',$vtemp);  # replace double quote in field data with double double quote
            $vtemp = (strpos($vtemp,$field_separator) === false and strpos($vtemp,'"') === false and strpos($vtemp,"\n") === false)? $vtemp: '"'.$vtemp.'"';
            $vtemp = str_replace("\r\n","\n",$vtemp);  # replace new line in field data with \n (as expected by Excel)
            $fields[] = $vtemp;
        }
        $rows[] = join($field_separator,$fields);

        # col label row
        $fields = array();
        foreach ($prog->properties as $colvar=>$col) {
            $vtemp = $col->label == ''? ucwords(str_replace('_',' ',$colvar)) : $col->label;
            $vtemp = str_replace('"','""',$vtemp);
            $vtemp = (strpos($vtemp,$field_separator) === false and strpos($vtemp,'"') === false and strpos($vtemp,"\n") === false)? $vtemp: '"'.$vtemp.'"';
            $vtemp = str_replace("\r\n","\n",$vtemp);  # replace new line in field data with \n (as expected by Excel)
            $fields[] = $vtemp;
        }
        $rows[] = join($field_separator,$fields);

        # col content row
        for ($i = 0; $i < $prog->db_count; $i++) {
            $fields = array();
            foreach ($prog->properties as $colvar=>$col) {
                $vtemp = $prog->ds->{$colvar}[$i];
                $vtemp = str_replace('"','""',$vtemp);
                $vtemp = (strpos($vtemp,$field_separator) === false and strpos($vtemp,'"') === false and strpos($vtemp,"\n") === false)? $vtemp: '"'.$vtemp.'"';
                $vtemp = str_replace("\r\n","\n",$vtemp);  # replace new line in field data with \n (as expected by Excel)
                $fields[] = $vtemp;
            }
            $rows[] = join($field_separator,$fields);
        }
        while (@ob_end_clean());
        header('Content-type: application/vnd.ms-excel');
        header('Content-type: text/comma-separated-values');
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Content-Disposition: inline; filename="dump.csv"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header("Expires: 0");

        echo join("\r\n",$rows);
        exit();

        #~ echo '<textarea rows=10 cols=120>'.join("\r\n",$rows).'</textarea>';

    }



}

?>