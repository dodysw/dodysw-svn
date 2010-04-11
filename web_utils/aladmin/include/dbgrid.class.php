<?
/*
    DBGRID CLASS
    class for easy admin-table management
    Copyright 2004,2005 Dody Suria Wijaya <dodysw@gmail.com>
    dody suria wijaya's software house - http://miaw.tcom.ou.edu/~dody/
*/

include_once('field_prop.class.php');

class DataSource {
    /* empty class for datasource */
    function get_row($rowindex, $key_as_colvar = False) {
        /* return array equivalent of row, given rowindex
        @key_as_colvar = True to set colvar as key instead of the normal column name
        */
        $new_array = array();
        foreach (get_object_vars($this) as $colvar=>$values) $new_array[$colvar] = $values[$rowindex];
        return $new_array;
    }
}

class TableManager {
    function TableManager() {
        $this->_version = 3.1;
        $this->title = '';  # the title of this datasource
        $this->description = '';  # a few words describing the purpose of this module. If defined, will be shown on right panel at browse mode
        $this->notes = '';  # long text explaining the background, usage, etc..If defined, will be shown on right panel at browse mode.
        $this->error_msgs = array();
        $this->error_rows = array();    # list of rowindex having error on previous submit
        $this->allow_new = True;   # bool, True to allow new command
        $this->allow_query = True; # bool, True to show query form
        $this->allow_edit = True;   # bool, True to allow edit command
        $this->allow_delete = True;   # bool, True to allow delete command
        $this->allow_view = True;   # bool, True to allow view command
        $this->confirm_delete = True;   # bool, True to ask confirmation before doing delete
        $this->confirm_duplicate = True;   # bool, True to allow duplicate command
        $this->db_where = '';   # string, default sql where statement, all sql select statement will be forced to use this
        $this->db_user_where = '';  # string, sql where statement used by query. does not override db_where
        $this->db_table = $GLOBALS['dbpre'].get_class($this).'_tab';   # string, database table name. the default value is PRE + CLASS NAME + _tab
        $this->db_orderby = '';   # string, "order by" sql statement, used at browse
        $this->db_groupby = '';   # string, "group by" sql statement, used at browse
        $this->properties = array(); # array, array of Prop instance, defined at each module inheritting this class.
        $this->browse_mode = 'table';   # string, choices: table, form. default browse mode.
        $this->browse_mode_forced = False;   # bool, True, will not give options for user to change browse mode
        $this->browse_form_cols = 2;   # int, the number of columns in browse-form mode
        $this->browse_rows = (AADM_ON_BACKEND==1)? 20 : 0; # int, rows displayed per page. 0 for all rows.
        $this->query_only = False; # bool, True will disable ALL command (new/edit/delete/checkbox/duplicate etc)
        $this->grid_command = array();  # array, array of string defining additional commands available for this program
        $this->must_authenticated = True;   # bool, True will check session for authentication. If fail, will redirect to login.
        $this->childds = array();   # ie: 'newsletter_article','...','...' etc, will be treated as detail datasource
        $this->logical_parent = ''; # TableManager instance, for detail, pointer to master instance. will be set by parent at final_init.
        $this->body = array();  # ie: $this->body['new']['prefix'] = 'blabla'. array 1: new/edit/browse, array 2: prefix/suffix
        $this->submit_label['new'] = ' '.lang('Save').' ';  # label for submit button at 'new' action
        $this->submit_label['edit'] = ' '.lang('Save').' ';  # label for submit button at 'edit' action
        $this->preview['new'] = False; # bool, True to present preview page before saving at 'new' action
        $this->preview['edit'] = False; # bool, True to present preview page before saving at 'edit' action
        $this->grid_no_headersort = False;   #bool, True to disable grid header's sort function
        $this->browse_form_statictext = False;   # bool, True to use static text instead of readonly input widget on form browse mode
        $this->browse_wait_query = False;   #bool, True to populate only on query (default false will populate immediately)
        $this->unit = 'record'; # string, the unit of record, will used on display, eg: "Add 3 new [unit]". wil
        $this->navbar = 1;  # set to 0 to hint framework to hide navigational bar
        $this->custom_header = 0;  # set to 1 to hint framework not to draw default header

        # internal variables
        $this->ds = new DataSource;
        $this->db_count = 0;    # the number of rows
        $this->action = ($_REQUEST['act'] == '')? 'browse': $_REQUEST['act'];
        $this->module = get_class($this);
		#~ $this->logger->debug('isi variable module ' . $this->module );
        $this->_go = $_REQUEST['go'];
        $this->_query = $_REQUEST['query'];
        $this->_rowid = $_REQUEST['rowid'];
        $this->_save = $_REQUEST['save'];   # int, 1 if this is a posted update/new form and should be save. -1 to show preview page first.
        $this->_sortdir = (AADM_ON_BACKEND==1)? $_REQUEST['sortdir']: ''; #browse: ASC or DESC
        $this->_orderby = (AADM_ON_BACKEND==1)? $_REQUEST['orderby']: ''; #browse: order by chosen column
        $this->_rowstart = (AADM_ON_BACKEND==1 and $_REQUEST['row'] != '')? $_REQUEST['row']:0;    #browse: row index to start page browsing
        $this->_populated = False;  #true if this->populate has been done
        $this->_cursor = ($_REQUEST['cursor'] != '')? $_REQUEST['cursor']:0;     # used for browse-form, integer pointing to datasource's current row index
        $this->_preview = False;    # bool, True if I should show preview of submitation, set by post_handler() after successful validation
        #~ $this->language = 'id';     # en = will include en.lang.inc.php
    }

    function final_init() {
        /* called just after constructor, usually to provide first-time post handler, before outputting
        rule: you may not output anything in this function
        */

        if ($this->query_only) {
            $this->allow_new = False;   # bool, True to allow new command
            $this->allow_edit = False;   # bool, True to allow edit command
            $this->allow_delete = False;   # bool, True to allow delete command
            $this->allow_view = False;   # bool, True to allow view command
        }

        # give change for fields to init them self after all definitions
		foreach ($this->properties as $key=>$col) {
            $this->properties[$key]->colvarname = $key;    # notify field of their called-name
            $this->properties[$key]->init($this);
        }

        # set session for each module's browse-mode default value
        if (!$this->browse_mode_forced and $_REQUEST['set_browse_mode'] != '')
            $_SESSION['module_browse_mode'][$this->module] = $_REQUEST['set_browse_mode'];


        if ($this->module == $_REQUEST['m'] and $_SERVER['REQUEST_METHOD'] == 'POST') {    # only do this if I'm the currently active module in page
            $this->post_handler();
        }
        if ($this->module == $_REQUEST['m'] and $_SERVER['REQUEST_METHOD'] == 'GET') {    # only do this if I'm the currently active module in page
            $this->get_handler();
        }

    }

    function get_handler() {
        /* called by final_init to provide GET request handling before outputing something
        is this necessary? I could do the same in act_x handler by doing ob_clean()
        */
    }

    function post_handler() {
        /* called aumatically by final_init to provide post request handling*/

        if ($this->action == 'new' or $this->action == 'edit') { # handle new-save
            # check permission first
            if ( ($this->action == 'new' and !$this->allow_new) or ($this->action == 'edit' and !$this->allow_edit) )
                die('Permission not given for "'.$this->action.'" action.');

            $_REQUEST['num_row'] = intval($_REQUEST['num_row']) > 0? intval($_REQUEST['num_row']): 1;   # new row should get the number of row to insert from num_row
            # import suggest field into current datasource (param sugg_field[..]). note: suggest field valid for all rows
            if ($this->action == 'new')
                $this->import_suggest_field_to_ds();
            # only do this if post come from edit form. bground: browse mode now can contain <input>, which value got passed to edit/new mode
            if ($this->_save == '') {   # to accomodate -1 (preview)
				return False;
			}

            $this->import2ds(); # only do this if post come from edit form. bground: browse mode now can contain <input>, which value got passed to edit/new mode
            $this->db_count = $_REQUEST['num_row'];   # new row should get the number of row to insert from num_row
            # check requirement

            if (!$this->validate_rows())    # don't continue if form does not pass validation
                return False;

            if ($this->action == 'new') {
                for ($i = 0; $i < $this->db_count; $i++) {
                    if (!$this->check_datatype($i)) {   # check duplicate index
                        return False;
                    }

                    if (!$this->check_duplicate_index($i)) {   # check duplicate index
                        return False;
                    }
                    if (!$this->check_insert($i)) {   # check insertion
                        return False;
                    }
                }

                if ($this->preview[$this->action] and $this->_save == -1) {   # show preview page instead
                    $this->_preview = True;
                    return False;
                }

                for ($i = 0; $i < $this->db_count; $i++) {
                    $this->insert($i);
                }

                if ($this->_go != '') {
                    while (@ob_end_clean());
                    header('Location: '.$this->_go);
                    exit;
                }
            }
            elseif ($this->action == 'edit') {
                $this->populate($this->_rowid, True);
                for ($i = 0; $i < $this->db_count; $i++) {
                    #~ if (!$this->check_duplicate_index($i)) {   # check duplicate index
                        #~ return False;
                    #~ }
                    if (!$this->check_update($i)) {   # check insertion
                        return False;
                    }
                }

                if ($this->preview[$this->action] and $this->_save == -1) {   # show preview page instead
                    $this->_preview = True;
                    return False;
                }

                for ($i = 0; $i < $this->db_count; $i++) {
                    $this->update($i);
                }

                if ($this->_go != '') {
                    #~ include('footer.inc.php');
                    header('Location: '.$this->_go);
                    exit;
                }
            }
        }
        elseif ($this->action == 'csv') {
            /* generate CSV representation of loaded datasource
            http://www.creativyst.com/Doc/Articles/CSV/CSV01.htm
            */
            if (AADM_ON_BACKEND!=1)
                die('Permission not given for "'.$this->action.'" action.');
            $this->browse_rows = 0;    # show all data
            $this->populate();
            $rows = array();
            $fields = array();
            foreach ($this->properties as $colvar=>$col) {
                $vtemp = $this->ds->{$colvar}[$i];
                $vtemp = str_replace('"','""',$vtemp);
                $vtemp = (strpos($vtemp,',') === false and strpos($vtemp,'"') === false and strpos($vtemp,"\n") === false)? $vtemp: '"'.$vtemp.'"';
                $fields[] = (strpos($col->label,',') === false)? $col->label: '"'.$col->label.'"';
            }
            $rows[] = join(',',$fields);
            for ($i = 0; $i < $this->db_count; $i++) {
                $fields = array();
                foreach ($this->properties as $colvar=>$col) {
                    $vtemp = $this->ds->{$colvar}[$i];
                    $vtemp = str_replace('"','""',$vtemp);
                    $vtemp = (strpos($vtemp,',') === false and strpos($vtemp,'"') === false and strpos($vtemp,"\n") === false)? $vtemp: '"'.$vtemp.'"';
                    $fields[] = $vtemp;
                }
                $rows[] = join(',',$fields);
            }
            #~ header('Content-type: application/vnd.ms-excel');
            header('Content-type: text/comma-separated-values');
            header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Content-Disposition: inline; filename="dump.csv"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Expires: 0');

            echo join("\r\n",$rows);
            exit();
        }
        else {  # no-act handler, call its post function callbacks if available
            if (AADM_ON_BACKEND==1) {
                $callback = 'act_'.$this->action;
                if (method_exists($this, $callback)) {
                    $this->$callback(True);
                }
            }
        }
    }

    function basic_handler() {
        /* called by go() */

        $this->action = ($this->action == '')? 'browse': $this->action;    #default to browse

        if ($this->action == 'new' or $this->action == 'duplicate') {
            # duplicate is like new, but before prepare insert, field is prefilled from another row
            $this->db_count = intval($_REQUEST['num_row']) > 0? intval($_REQUEST['num_row']): 1;   # new row should get the number of row to insert from num_row
            if ($this->action == 'duplicate') {
                $this->populate($this->_rowid); # populate rows
                $this->action = 'new';
            }
            # prepare insert, this should only be done once at entry to new, since prepare_insert codes might overwrite submit-error newly entried data
            if (!$this->_save) {
                for ($i = 0; $i < $this->db_count; $i++) {
                    if (!$this->prepare_insert($i))     # prepare insertion
                        return False;
                }
            }
            if ($this->db_count == 1) {
				$this->showform();
            }
            else {
                $this->showgrid();
            }
        }
        elseif ($this->action == 'del') {
            echo '<h4>'.lang('Delete record').'</h4>';
            $this->populate($this->_rowid); # populate first so check_del/remove override has access to row's complete data
            #~ $this->db_count = count($this->_rowid);    # delete provided from rowid checkbox
                # taken from populate
            for ($i = 0; $i < $this->db_count; $i++) {
                if (!$this->check_del($i))     # prepare update
                    return False;
            }
            #~ $this->delrow();    # delrow handle all rowids
            for ($i = 0; $i < $this->db_count; $i++) {
                $this->remove($i);    # delrow handle all rowids
            }
            echo '<p>'.$this->db_count.' '.lang($this->unit).' '.lang('has been deleted').'</p>';
            echo '<p><b><a href="'.$this->_go.'">'.lang('Continue').'</a></b></p>';
        }
        elseif ($this->action == 'XXXduplicate') {
            echo '<h4>Duplicate record</h4>';
            $this->db_count = count($this->_rowid);    # delete provided from rowid checkbox
            $this->duplicate_row();    # delrow handle all rowids
        }

        elseif ($this->action == 'edit') {
            if (!$this->_populated) { # may already been populated at post handler
                $merge = ($this->_save != ''); # bground: browse now can be form, which will post fields, but must not be consider as posted from edit.
                $this->populate($this->_rowid,$merge);    # merge = True will request+datasource
            }
            # prepare update, this should only be done once at entry to edit, since prepare_update codes might overwrite submit-error newly entried data
            if (!$this->_save) {
                for ($i = 0; $i < $this->db_count; $i++) {
                    if (!$this->prepare_update($i))
                        return False;
                }
            }

            if (count($this->_rowid) > 1) {
                $this->showgrid();
            }
            else {
                $this->showform();
                #~ $this->show_childgrid();
            }
        }
        elseif ($this->action == 'view') {
            $this->populate($this->_rowid);
            $this->showview();
            #~ $this->show_childgrid();
        }
        # moved from posthandler
        elseif ($this->action == 'browse' and $_REQUEST['qf'] and $this->_query) {
            $fields = array();
            if ($_REQUEST['qf'] == '*') {
                foreach ($this->properties as $key=>$col) {
                    if (!$col->queryable or $col->colname == '') continue;
                    $fields[] = $key;
                }
            }
            else {
                $fields[] = $_REQUEST['qf'];
            }

            $where_words = array();
            foreach ($fields as $colvar) {
                $col = $this->properties[$colvar];
                $where_words[] = $col->table.'.`'.$col->colname.'` like \'%'.myaddslashes($this->_query).'%\'';
            }
            $this->db_user_where = join(' or ',$where_words);
            $this->browse_rows = 0; # disable paging
        }
        elseif ($this->action == 'browse') {
            # pass, will be handled below
        }
        else {  # no-act handler, call its function callbacks if available
            $callback = 'act_'.$this->action;
            if (method_exists($this, $callback))
                $this->$callback(False);
            else
                die(get_class($this).': Unhandled action "'.$this->action.'". Stopping...');
        }

        # note: new, del, and edit handler can set act=browse to display browse
        if (($this->action == 'browse' and !$this->browse_wait_query) or ($this->action == 'browse' and $this->browse_wait_query and $this->_query != '')) {
            $this->browse_rows = ($_REQUEST['maxrows'] != '')? $_REQUEST['maxrows']: $this->browse_rows;
            if (!$this->browse_mode_forced and $_SESSION['module_browse_mode'][$this->module] != '') { # last browsemode of given module
                if ($_SESSION['module_browse_mode'][$this->module] == 'table') {
                    $this->populate();
                    $this->showgrid();
                }
                else {
                    $this->browse_rows = 0; # disable paging for form
                    $this->populate($this->_rowid);
                    $this->showform();
                    $this->show_childgrid();
                }
            }
            else {
                if ($this->browse_mode == 'table') {
                    $this->populate();
                    $this->showgrid();
                }
                else {
                    $this->browse_rows = 0; # disable paging for form
                    $this->populate($this->_rowid);
                    $this->showform();
                    $this->show_childgrid();
                }
            }
        }
    }

#----- helper functions ----- #

    function query($keys,$orders=array()) {
        /* almost similar to get_rows, but 1) accept colvars=>value instead of colname=>value 2) result in populate() to ->ds object
        */
        $tmp_arr = array();
        foreach ($keys as $colvar=>$value) {
            $tmp_arr[] = '`'.$this->properties[$colvar]->colname.'`=\''.$val.'\'';
        }
        $this->db_where = join(' and ',$tmp_arr);
        if ($orders) {
            $tmp_arr = array();
            foreach ($orders as $colvar) {
                $col = $this->properties[$colvar];
                $tmp_arr[] = '`'.$col->colname.'`';
            }
            $this->db_orderby = join(',',$tmp_arr);
        }
        $this->clear();
        return $this->populate();
    }

    function create_sql_select($rowid = '',$maxcount = False) {
        /* build and return sql based on this->properties
        note: this function sets _mapping_index, used eventualy by populate
        $maxcount: True, will return sql without "limit" and with "select count(1)", specially used by browse
        */
        # construct sql statement
        $sql_select = '';
        $sql_tables = array();
        $temp = array();
        $this->_mapping_index = array();    # map select index to col var
        $temp[] = '`'.$this->db_table.'`.`rowid`';  # always add rowid
        $this->_mapping_index[] = '_rowid'; # make rowid maps to colvar _rowid...why? dunno.. ;)
        $where_on = array();
        foreach ($this->properties as $key=>$col) {
            if ($col->colname == '') continue;    # a virtual field, usually used for display purpose
            if ($col->table == '') $col->table = $this->db_table;
            $temp[] = (strpos($col->colname,')') === False) ? '`'.$col->table.'`.`'.$col->colname.'`' : $col->colname;
            $this->_mapping_index[] = $key;
            # special condition: field from other table
            if ($col->table != '' and $col->table != $this->db_table) {
                $w = array();
                assert($col->join_on);
                foreach ($col->join_on as $cond) {
                    $ckv = explode('.',$cond);
                    if (count($ckv) == 1)
                        $ckv = array($this->db_table,$ckv[0]);
                    $w[] = $ckv[0].'.'.$ckv[1].'='.$col->table.'.'.$ckv[1];
                }
                $where_on[$col->table] = array($col->join_order, join(' and ',$w));
            }
        }
        $sql_tables[] = $this->db_table;
        if ($where_on) {
            function cmp($a, $b) {
                if ($where_on[$a][0] == $where_on[$b][0]) return 0;
                return ($where_on[$a][0] > $where_on[$b][0])?-1:1;
            }
            uksort($where_on,'cmp');
            foreach ($where_on as $k=>$v) {
                $sql_tables[] = ' left join '.$k.' on ('.$v[1].')';
            }
        }
        $sql_select = join(',',$temp);
        $sql_where = array();
        # - where - NEW: db_where is ALWAYS used, for program which enforces security in db_where
        if ($this->db_where != '') {
            $sql_where[] = $this->db_where;
        }
        if ($rowid != '') {
            if (is_array($rowid)) {
                $rowids = array();
                foreach ($rowid as $id) $rowids[] = '\''.myaddslashes($id).'\'';
                $sql_where[] = 'rowid in ('.join(',',$rowids).')';
            }
            else {
                $sql_where[] = 'rowid=\''.myaddslashes($rowid).'\'';
            }

        }
        else {
            if ($this->db_user_where != '') {
                $sql_where[] = '('.$this->db_user_where.')';    # must inside parentheses since user_where may contain 'or'
            }

            # master-detail sql binding, for children: get parentkey from parent coresponding value
            if ($this->logical_parent) {
                foreach ($this->properties as $colvar=>$col) {
                    if ($col->parentkey and is_bool($col->parentkey)) {   # get the value from parent's same column name ds fields
                        foreach ($this->logical_parent->properties as $p_colvar=>$p_col) {
                            if ($col->colname == $p_col->colname) { # note, the asumption of master detail is of EQUAL COLUMN NAME!!!
                                $sql_where[] = $col->colname.'=\''.addslashes($this->logical_parent->ds->{$p_colvar}[$this->logical_parent->_cursor]).'\'';    # parent must only has 1 row
								#~ $this->logger->debug('colname for logical-parent.....416 '. $col->colname);
                            }
                        }
                    }
                    elseif ($col->parentkey and is_string($col->parentkey)) {
                        $sql_where[] = $col->colname.'=\''.addslashes($this->logical_parent->ds->{$col->parentkey}[$this->logical_parent->_cursor]).'\'';    # parent must only has 1 row
						#~ $this->logger->debug('colname for logical-parent.....416 '. $col->colname);
                    }
                }
            }
            # - group by -
            $sql_groupby = '';
            if ($this->db_groupby != '') {
                $sql_groupby = ' group by '.$this->db_groupby;
            }

            # - order by -
            $sql_orderby = '';
            if ($this->db_orderby != '') {
                $sql_orderby = ' order by '.$this->db_orderby;
            }
            if ($this->action == 'browse' and $this->_orderby != '') {  # let's try #1: _orderby override db_orderby
                $sql_orderby = ' order by '.$this->_orderby.' '.$this->_sortdir;
            }

            $sql_limit = '';
            if ($this->browse_rows > 0) {   # act = browse, paging = on
                #~ $current_row = $_REQUEST['row'] == ''? 0: $_REQUEST['row'];
                $sql_limit = ' limit '.$this->_rowstart.','.$this->browse_rows;
            }
        }

        if ($sql_where) {
            $sql_where = ' where '.join(' and ',$sql_where);
        }
        else {
            $sql_where = '';
        }

        $sql_tables = join(' ',$sql_tables);
        if ($maxcount) {
            $sql = 'select count(1) from '.$sql_tables.$sql_where.$sql_groupby;
        }
        else {
            $sql = 'select '.$sql_select.' from '.$sql_tables.$sql_where.$sql_groupby.$sql_having.$sql_orderby.$sql_limit;
        }
        #~ echo '<br>'.$sql;
		#~ $this->logger->debug('sql query '. $sql);
        return $sql;
    }

    function max_rownum() {
        $sql = $this->create_sql_select('',True);
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
        $row = mysql_fetch_row($res);
        return $row[0];
    }

    function get_row($keys,$sql_select='*',$type='array',$db_table='') {
        /* return one row given key-val param  (dict)
        @keys: array('rowid'=>10), or string (rowid='10')
        @type can be "array" or "row"
        */
        $db_table = $db_table == '' ? $this->db_table : $GLOBALS['dbpre'].$db_table;
        $sql_where = '';
        if (is_array($keys) and count($keys) > 0) {
            $sql_wheres = array();
            foreach ($keys as $key=>$val) {
                if (is_array($val)) die('getrow: multi value does not supported');
                $sql_wheres[] = "`$key`='$val'";
            }
            $sql_where = ' where '.join(' and ',$sql_wheres);
        }
        elseif (is_string($keys) and $keys != '') {
            $sql_where = ' where '.$keys;
        }
        $sql = 'select '.$sql_select.' from '.$db_table.$sql_where.' limit 0,1';
        #~ echo '<br>'.$sql;
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
        if ($type == 'array')
            return mysql_fetch_array($res,MYSQL_ASSOC);
        else
            return mysql_fetch_row($res);

    }

    function is_rowid_exist($rowid,$db_table='') {
        /* return 1 if row with given rowid is exist on db_table
        */
        $db_table = $db_table == '' ? $this->db_table : $GLOBALS['dbpre'].$db_table;
        $sql = 'select 1 from '.$db_table.' where rowid='.intval($rowid);
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
        return mysql_num_rows($res);
    }

    function get_rows($keys,$sql_select='*',$type='array',$db_table='') {
        /*  return database rows given key-val param (list of dict)
        @ keys: array('rowid'=>10)
        @type can be "array" or "row"
        */
        $db_table = $db_table == '' ? $this->db_table : $GLOBALS['dbpre'].$db_table;
        $sql_where = '';
        if ($keys) {
            $sql_wheres = array();
            foreach ($keys as $key=>$val) {
                if (is_array($val)) {   //multivalue, handle with sql "in" operator
                    $val2 = array();
                    foreach ($val as $v) {
                        if (is_array($v)) die('getrows: array in array does not supported');
                        $val2[] = "'$v'";
                    }
                    $val2 = join(',',$val2);
                    $sql_wheres[] = "`$key` in ($val2)";
                }
                else {
                    $sql_wheres[] = "`$key`='$val'";
                }
            }
            $sql_where = ' where '.join(' and ',$sql_wheres);
        }
        $sql = 'select '.$sql_select.' from '.$db_table.$sql_where;

        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
        $rows = array();
        if ($type == 'array') {
            while ($row = mysql_fetch_array($res,MYSQL_ASSOC)) {
                $rows[] = $row;
            }
        }
        else {
            while ($row = mysql_fetch_row($res)) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    function set_row($keys,$sql_set) {
        #set given key-val param (dict)
        $sql_where = '';
        if (count($keys) > 0) {
            $sql_wheres = array();
            foreach ($keys as $key=>$val) {
                if (is_array($val)) die('setrow: multi value does not supported');
                $sql_wheres[] = "`$key`='".myaddslashes($val)."'";
            }
            $sql_where = ' where '.join(' and ',$sql_wheres);
        }
        if (is_array($sql_set)) {   # Note, if using array, I DONOT support mysql function/expression!! use string for that.
            $sql_sets = array();
            foreach ($sql_set as $key=>$val) {
                $sql_sets[] = '`'.$key.'` = \''.addslashes($url).'\'';
            }
            $sql_set = join(',',$sql_sets);
        }
        assert($sql_where != '');   # safeguard against setting all rows
        $sql = 'update `'.$this->db_table.'` set '.$sql_set.$sql_where;
        #~ echo $sql;exit;
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
    }

    function remove_row($keys) {
        #remove given key-val param (dict)
        $sql_where = '';
        if (count($keys) > 0) {
            $sql_wheres = array();
            foreach ($keys as $key=>$val) {
                if (is_array($val)) die('remove_row: multi value does not supported');
                $sql_wheres[] = "`$key`='".myaddslashes($val)."'";
            }
            $sql_where = ' where '.join(' and ',$sql_wheres);
        }
        assert($sql_where != '');   # safeguard against setting all rows
        $sql = 'delete from `'.$this->db_table.'`'.$sql_where.' limit 1'; # with limit safe guard
        #~ echo '<br>'.$sql;
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
    }

    function insert_row($keys) {
        /* set given key-val param (dict)
        warning: this function bypass check insert, and any available on update rows mangling. if u require those mangling, then use ->insert()
        */
        if (count($keys) > 0) {
            $sql_keys = array();
            $sql_values = array();
            foreach ($keys as $key=>$val) {
                if (is_array($val)) die('insertrow: multi value does not supported');
                $sql_keys[] = '`'.$key.'`';
                if ($val == 'Now()')
                    $sql_values[] = myaddslashes($val);
                else
                    $sql_values[] = "'".myaddslashes($val)."'";
            }
            $sql_keys = join(',',$sql_keys);
            $sql_values = join(',',$sql_values);
        }
        $sql = 'insert into `'.$this->db_table.'` ('.$sql_keys.') values ('.$sql_values.')';
        #~ echo '<br>'.$sql;exit();
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
        return mysql_insert_id();
    }

    function validate_field($colvar,$i) {
        $col = $this->properties[$colvar];
        $ok = True;
        $value = $_REQUEST['field'][$colvar][$i];
        if  (
                ($col->required and $col->inputtype == 'file' and $_FILES['field']['name'][$colvar][$i] == '') or
                ($col->required and isset($_REQUEST['field'][$colvar][$i]) and $value == '') or
                ($col->required and $col->inputtype == 'checkbox' and !isset($_REQUEST['field'][$colvar][$i])) # case for required "multiple checkbox", user need to check at least 1 checkbox. we can't just detect using isset since non-checked checkbox does not generate empty key/val.
            )
            {
            $this->error_msgs[] = "[".($i+1)."] {$col->label} ".lang('is required');
            $this->error_rows[$i] = True;
            $ok = False;
        }

        if ($col->on_validate != '') {
            $ret = eval($col->on_validate);
            if ($ret != 1) {
                $this->error_msgs[] = '['.($i+1).'] '.$ret;
                $this->error_rows[$i] = True;
                $ok = False;
            }
        }

        # for foreign key/enumerate, check that the value is really exists on the foreign table
        if ($value != '' and $col->datatype == 'int' and $col->enumerate != '' and ($col->updatable or $col->insertable)) {    # int -> just to make sure
            $foreign_mdl = instantiate_module($col->enumerate);
            if (!$foreign_mdl->is_rowid_exist($value)) {
                $this->error_msgs[] = '['.($i+1).'] '.$col->label.'\'s value does not exist in '.$foreign_mdl->title;
                $this->error_rows[$i] = True;
                $ok = False;
            }
        }

        return $ok;
    }

    function validate_rows() {
        #~ $db_count = $_REQUEST['num_row'];   # rowid in insert is actually dummy, used only for knowing how many rows to insert
        $ok = True;
        for ($i = 0; $i < $this->db_count; $i++) {
            foreach ($this->properties as $colvar=>$col) {
                if (!$this->validate_field($colvar,$i))  { # make sure this field is not empty
                    $ok = False;
                }
            }
        }
        return $ok;
    }

#----- end of helper functions ----- #

    function clear() {
        /* clear datasource. you should call this before calling another populate() to avoid datasource gets appended
        */
        $this->ds = new DataSource;
        $this->db_count = 0;
    }

    function populate($rowid='',$merge=False, $sql='') {
        /* construct sql query given field property, query database, fetch, and fill up datasource instance
        in case edit-prepare post when merge True and  _request['field'] var is set and it's an updatable field, then populate will use that value instead

        @sql = string, custom sql statement. if you define this, please complete $this->_mapping_index
        */
        if ($sql == '')
            $sql = $this->create_sql_select($rowid);
        #~ echo "\n<br>".$sql;
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
        $this->db_count = mysql_num_rows($res);
        $i = 0;
        while ($row = mysql_fetch_row($res)) {
            foreach ($this->_mapping_index as $idx=>$colvar) {
                # bugs! in case colvar has no column name (Virtual), then it's not available in mapping_index!!!
                # case found at subscriber case
                $value = $row[$idx];
                if ($rowid != '' and $merge and $this->properties[$colvar]->updatable and isset($_REQUEST['field'][$colvar][$i])) {
                    $value = $_REQUEST['field'][$colvar][$i];
                }
                $this->ds->{$colvar}[] = $value;
            }
            $i += 1;
        }
        $this->_populated = True;
    }

    function import_suggest_field_to_ds () {
        foreach ($this->properties as $colvar=>$col) {
            if (isset($_REQUEST['sugg_field'][$colvar]) and !isset($this->ds->$colvar)) {    # i hazardously ignore action state (updatable, insertable...)
                for ($i=0; $i < $_REQUEST['num_row']; $i++) {   # reminder: num_row is a param given by new button form to hint how many add-rows to show
                    $this->ds->{$colvar}[$i] = $_REQUEST['sugg_field'][$colvar];
                }
            }
        }
    }

    function import2ds() {
        /* copy _REQUEST['field'][*1][*2] to this->ds->*1[*2]
        used exclusively by new/update-post post-handlers
        */
        $ok = 0;
        foreach ($this->properties as $colvar=>$col) {
            if (isset($_REQUEST['field'][$colvar]) and !isset($this->ds->$colvar)) {    # i hazardously ignore action state (updatable, insertable...)
                # note: below hasbeen moved to import_suggest_field_to_ds()
                if ($this->action == 'new' and $col->parentkey) {# special case for detail-new, parent-linked field val only supplied by post as field[fieldname][0]=val. let's copied this to all indices
                    $value = $_REQUEST['field'][$colvar][0];
                    $this->ds->$colvar = array();
                    for ($i=0; $i<$_REQUEST['num_row']; $i++) {
                        $this->ds->{$colvar}[$i] = $value;
                    }
                }
                else {
                    $this->ds->$colvar = $_REQUEST['field'][$colvar];
                }
                $ok = 1;
            }
            elseif ($col->inputtype == 'checkbox' and !isset($_REQUEST['field'][$colvar][$i]) and !isset($this->ds->$colvar)) {
                # special case for checkbox. unchecked checkboxes do not generate empty key/val. so depending whether this is group checkboxes or single checkbox, we initialize it to correct value.
                # if we dont explicitly say its value is (ie, value=0), and the previous value in db is 1, then checkbox would never be saved as unchecked, since populate will passess current value in db.
                if ($col->enumerate != '') {
                    $value = array();   # assign it to empty array. TODO: should test this.
                }
                else {
                    $value = 0; # BOOLEAN 0/1
                }
                $this->ds->$colvar = array();
                for ($i=0; $i<$_REQUEST['num_row']; $i++) {
                    $this->ds->{$colvar}[$i] = $value;
                }
                $ok = 1;
            }
            else {
                #~ echo 'not ok';
            }
        }

        $this->db_count = $ok;
    }

    #~ function request2ds() {
        #~ /* copy _REQUEST['field'][*1][*2] to this->ds->*1[*2]
        #~ called by act new and edit so that program can have access to ds in unified way
        #~ */
        #~ if ($rowid != '' and ($overwrite or !isset($_REQUEST['field'][$colvar][$i]))) {
            #~ $_REQUEST['field'][$colvar][$i] = $row[$idx];
        #~ }
    #~ }

/* presentation functions */
    function showview() {
    /* show row in a readonly view-style */
        for ($rowidx = 0; $rowidx < $this->db_count; $rowidx++) {
            echo '<p><table border=1 style="border-collapse: collapse;" summary="view table">';
            # decide, which columns to show in form
            $this->colgrid = array();
            foreach ($this->properties as $key=>$col) {
                #~ if ($col->hidden) continue;
                if ($this->action == 'edit' and !$col->updatable) continue;
                if ($this->action == 'new' and !$col->insertable) continue;
                $this->colgrid[] = $key;
            }
            $i = 0;
            foreach ($this->colgrid as $colvar) {
                $rowcolour = ($i++ % 2 == 0)? 'greyformlight': 'greyformdark';
                $col = $this->properties[$colvar];
                echo '<tr class="'.$rowcolour.'">';
                echo '<td>'.$col->label.':&nbsp</td>';
                echo '<td>';
                echo $this->ds->{$colvar}[$rowidx];
				#~ $this->logger->debug('show view DS ' . $this->ds->{$colvar}[$rowidx]);
                echo '</td>';
                echo "</tr>\r\n";
            }
            echo '</table>';
        }

        # option to view child datasources?

        #~ echo '<input type=submit name=edit value="Edit">  | ';
        #~ echo '<input type=submit name=del value="Delete">  | ';
        #~ echo '<b><a href="'.$this->_go.'">Cancel</a></b>';
        echo '<p><b><a href="" onclick="window.history.back();return false;">'.lang('Cancel').'</a></b>';
        #~ echo '<input type=button value="Cancel" onclick="window.location=\''.$this->_go.'\'">';
        #~ echo '</form>';
    }

    function showquery() {
    /* show text + combo box for query form */
        #~ echo '<td align=right>';    // see nav.inc.php for reason
        echo '<form name="search" method=POST action="'.$_SERVER['PHP_SELF'].'">';
        #~ echo '<img src="images/b_search.png" border="0">';
        echo lang('Search').' ';
        echo '<input type=hidden name=m value="'.$this->module.'">';
        echo '<input type=hidden name=act value="browse">';
        echo '<input type=text name=query value="'.$this->_query.'" size=10>';
        echo ' '.lang('in').' ';
        echo '<select name="qf">';
        #~ echo '<option value="">With selected:</option>';
        echo '<option value="*">'.lang('All fields').'</option>';
        echo "<option value=''>______________</option>";
        foreach ($this->properties as $key=>$col) {
            if (!$col->queryable or $col->colname=='') continue;
            $key == $_REQUEST['qf']? $ischecked = 'selected': $ischecked = '';
            echo "<option value='$key' $ischecked>{$col->label}</option>";
        }
        echo '</select>';
        #~ echo '<input type=submit value="Query">';
        echo '</form>';
        #~ echo '</td>';    // see nav.inc.php for reason

        #~ echo '</td></tr></table>';
    }

    function showerror() {
    /* display errors in form submitation */
        if (count($this->error_msgs)>0) {
            echo '<p><img src="images/eri.gif"><b>'.lang('Error in form').'</b>:<ul>';
            foreach ($this->error_msgs as $errormsg) {
                echo '<li>'.$errormsg;
            }
            echo '</ul></p>';
            return True;
        }
        return False;
    }

    function showgrid($action='') {
    /* show data grid, for query, multi edit, and multi new */
        global $last_message;
        $action = ($action == '')? $this->action: $action;
        $this->showerror();
        if ($last_message != '') echo $last_message;    # place holder for anyone who whises to put msg above grid

        $this->grid_command[] = array('csv',lang('ExportCsv'));

        // parse property for fields to display, and show datasource browse

        # decide, which columns to show in table
        $this->colgrid = array();
        foreach ($this->properties as $key=>$col) {
            if ($action == 'browse' and $col->hidden) continue;
            $this->colgrid[] = $key;

        }

        # prepare javascript validation and confirmation function
        echo '<!-- 912 --><script type="text/javascript">';
        if ($this->action == 'browse') {
            echo '
function submit_confirm(myform) {
    action = myform.elements[\'act\'].value;
    cb = myform.elements[\'rowid[]\'];
    num_checked = 0;
    if (cb.length) {
        for (var i = 0; i < cb.length; i++) if (cb[i].checked) num_checked++;
    } else {
        if (cb.checked) num_checked++;
    }

    if ((action == \'del\' || action == \'edit\' || action == \'duplicate\') && num_checked == 0) {
        alert(\''.lang('You need to pick at least one row').'\');
        return false;
    }
    if (action == \'del\') {
        if (!confirm(\''.lang('Are you sure you want to delete').'?\'))
            return false;
        myform.submit();
        return true;
    }
    else if (action == \'duplicate\') {
        myform.submit();
        return true;
    }
    else {
        myform.submit();
        return true;
    }
    return false;
}';
        }
        else {  # callback to function like $this->grid_submit_confirm_new()
            if (method_exists($this,'grid_submit_confirm_'.$this->action))
                echo $this->{'grid_submit_confirm_'.$this->action}();
        }
        echo '</script>';

        # create table view
        echo '<form method="POST" enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'" onSubmit="return submit_confirm(this)" autocomplete="off">';
        echo '<input type=hidden name="m" value="'.$this->module.'">';
        if ($action == 'browse') {
            echo '<input type=hidden name="go" value="'.htmlentities($GLOBALS['full_self_url']).'">';         # url to go after successful submitation
        }
        else {
            echo '<input type=hidden name="go" value="'.htmlentities($this->_go).'">';         # url to go after successful submitation
            echo '<input type=hidden name="act" value="'.$action.'">';
            echo '<input type=hidden name="num_row" value="'.$this->db_count.'">';
            #~ echo '<input type=hidden name="save" value="1">';         # marker to indicate form submitation
            if (!$this->_preview)
                echo '<input type="hidden" name="save" value="-1">';         # marker to indicate form submitation
            else
                echo '<input type="hidden" name="save" value="1">';         # marker to indicate form submitation
        }


        if ($action == 'browse' and !$this->query_only and $this->grid_command) {
        #~ and $this->db_count > 0
            echo '<!-- 1109 -->
            <script type="text/javascript">
            function setCheckBoxes(myform, do_check) {
                var cb = myform.elements[\'rowid[]\'];
                if (cb.length) {
                    for (var i = 0; i < cb.length; i++) cb[i].checked = do_check;
                } else {
                    cb.checked = do_check;
                }
                return true;
            }
            </script>';
            #~ echo '<img src="images/arrow_ltr.png" border="0" alt="arrow to checkbox">';
            echo '<input type="hidden" name="act" value="">';
            #~ echo '<input type="hidden" name="cb" value="">';
            echo 'Selected: ';
            # echo '<a href="" onclick="setCheckBoxes(this.form, true);return false;">Check All</a> / ';
            echo '<input type="button" onclick="setCheckBoxes(this.form, true);" value="'.lang('CheckAll').'" '.(($this->db_count > 0 ) ?'':'disabled').'> ';
            #~ echo '<a href="#" onclick="setCheckBoxes(this.form, true);">'.lang('Check All').'</a> | ';
            # echo '<a href="'.$_SERVER['PHP_SELF'].'" onclick="setCheckBoxes(this.form,false);return false;">Uncheck All</a> ';
            echo '<input type="button" onclick="setCheckBoxes(this.form, false);" value="'.lang('Clear').'" '.(($this->db_count > 0 ) ?'':'disabled').'> ';
            echo '<input type="button" onclick="this.form.act.value = \'edit\';submit_confirm(this.form);" value="'.lang('Edit').'" '.(($this->allow_edit and $this->db_count > 0 ) ?'':'disabled').'> ';
            echo '<input type="button" onclick="this.form.act.value = \'del\';submit_confirm(this.form);" value="'.lang('Delete').'" '.(($this->allow_delete and $this->db_count > 0 )?'':'disabled').'> ';
            echo '<input type="button" onclick="this.form.act.value = \'duplicate\';submit_confirm(this.form);" value="'.lang('Duplicate').'" '.(($this->allow_duplicate and $this->db_count>0)?'':'disabled').'> ';
            echo '<input type="button" onclick="this.form.act.value = \'view\';submit_confirm(this.form);" value="'.lang('View').'" '.(($this->allow_view and $this->db_count > 0 )?'':'disabled').'> ';

            #~ echo '<a href="#" onclick="setCheckBoxes(this.form, false);">'.lang('Uncheck All').'</a> | ';
            #~ echo '<a href="#" onclick="setCheckBoxes(this.form, false);"><b>'.lang('Edit').'</b></a> | ';
            #~ echo '<a href="#" onclick="setCheckBoxes(this.form, false);"><b>'.lang('Delete').'</b></a> | ';
            #~ echo '<select name="act" onchange="return submit_confirm(this.form)"><option value="">'.lang('With selected').':</option>';
            #~ foreach ($this->grid_command as $command) {
                #~ echo "<option value='{$command[0]}'>{$command[1]}</option>";
            #~ }
            #~ echo '</select>';
            if (count($this->grid_command))
                echo ' | Other: ';

            foreach ($this->grid_command as $command) {
                #~ echo '<input type="button" onclick="this.form.act.value = \''.$command[0].'\';submit_confirm(this.form);" value="'.lang($command[1]).'"> ';
                echo '<input type="button" onclick="this.form.act.value = \''.$command[0].'\';this.form.submit();" value="'.lang($command[1]).'"> ';
            }

            if ($this->_query != '') {
                if ($this->db_count == 0) {
                    echo '<p><b>'.lang('The search').' "'.$this->_query.'" '.lang('returns no record').'</b>. <a href="'.$_SERVER['PHP_SELF'].'?m='.$this->module.'">'.lang('Reset Search').'</a>.</p>';
                    return;
                }
                else {
                    echo '<p><b>'.lang('The search').' "'.$this->_query.'" '.lang('found').' '.$this->db_count.' '.lang($this->unit).'</b>. <a href="'.$_SERVER['PHP_SELF'].'?m='.$this->module.'">'.lang('Reset Search').'</a>.</p>';
                }
            }

            # build page browsing below table
            if ($this->browse_rows > 0 and $this->db_count) {
                echo '<br>';
                $max_rownum = $this->max_rownum();
            }
            if ($this->browse_rows > 0) {
                #~ $current_row = $_REQUEST['row'] == ''? 0: $_REQUEST['row'];
                if ($max_rownum > $this->db_count) {    # split into pages
                    echo lang('Pages').': ';
                    $pages = array();
                    for ($rowidx = 0, $pg = 1; $rowidx < $max_rownum; $rowidx += $this->browse_rows, $pg += 1) {
                        if ($this->_rowstart == $rowidx)
                            $pages[] = "<b>$pg</b>";
                        else
                            #~ $pages[] = "<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&act=browse&row={$rowidx}'>$pg</a>";
                            $pages[] = "<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&amp;act=browse&amp;row={$rowidx}&amp;orderby={$this->_orderby}&amp;sortdir={$this->_sortdir}'>$pg</a>";
                    }
                    echo join(' | ',$pages);
                    echo ' / ';
                }
            }
            #~ echo 'Total: '.$max_rownum.'';
            if ($this->browse_rows > 0 and $max_rownum > $this->db_count) {
                echo " - <a href='{$_SERVER['PHP_SELF']}?m={$this->module}&amp;act=browse&amp;maxrows=0&amp;orderby={$this->_orderby}&amp;sortdir={$this->_sortdir}'>".lang('Show All')."</a>";
            }
        }

        #~ echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" summary="main container">';  //style="border-collapse: collapse;"
        #~ echo '<tr>';
        #~ echo '</tr></table>';  //outer table

        echo $this->body[$this->action]['prefix'];  # show prefix/suffix body
        echo '<table border="0" cellpadding="2" cellspacing="1" summary="format combo">';  //style="border-collapse: collapse;"
        echo '<tr class="greyformtitle">';
        #~ if (!$this->query_only and ($this->allow_edit or $this->allow_view)) {
            #~ $_cspan_num = 1;
            echo '<th colspan="'.$_cspan_num.'">&nbsp;</th>';   # for command. new: now always show.
        #~ }
        foreach ($this->colgrid as $colvar) {
            $col = $this->properties[$colvar];
            if ($action == 'edit' and !$col->updatable) continue;
            if ($action == 'new' and !$col->insertable) continue;
            if ($action == 'browse') {    # browse: enable sort by table col
                $_sortdir = $this->_sortdir != ''? $this->_sortdir : 'ASC';
                if ($this->_orderby == $col->colname and $this->_sortdir != '')
                    $_sortdir = ($_sortdir == 'ASC')? 'DESC': 'ASC'; # swap ASC/DESC for currently sorted column name
                echo '<th>';
                if (!$this->grid_no_headersort) {

                    echo '<a href="'.$_SERVER['PHP_SELF'].'?m='.$this->module.'&act=browse&row='.$this->_rowstart.'&orderby='.$col->colname.'&sortdir='.$_sortdir.'">'.$col->label.'</a>';
                    if ($this->_orderby == $col->colname and $this->_sortdir != '') {
                        echo ' <img src="images/'.(($_sortdir == 'ASC')? 'asc_order.png': 'desc_order.png').'" border="0" alt="'.$_sortdir.'">';
                    }
                }
                else {
                    echo $col->label;
                }
                echo '</th>';
            }
            else {
                echo '<th>'.$col->label.'</th>';
            }

        }
        echo "</tr>\r\n";

        if ($this->db_count > 0 or $_REQUEST['row'] != '') {
            for ($i = 0; $i < $this->db_count; $i++) {
                /*  for edit, we want the row to be sorted just as how it was selected in browse
                    this is the striking difference between this->ds->_rowid (coming from database, sorted as "sort by") and this->_rowid (coming from request, sorted as from request)
                */
                $rowindex = $i;
                if ($action == 'edit') {
                    $rowindex = array_search($this->_rowid[$i], $this->ds->_rowid); # mangle rowindex
                }

                $rowcolour = ($i % 2 == 0)? 'greygridlight': 'greygriddark';
                if ($this->error_rows[$i]) $rowcolour = 'error';
                echo '<tr class="'.$rowcolour.'" valign="top">';

                #~ if (!$this->query_only and ($this->allow_edit or $this->allow_view)) {
                    if ($action == 'browse') {
                        echo "<td><input type=checkbox name='rowid[]' value='{$this->ds->_rowid[$rowindex]}'></td>";
                    }
                    else {
                        echo '<input type=hidden name="rowid['.$rowindex.']" value="'.$this->_rowid[$rowindex].'">';   # for edit-action
                        $_cspan_num = 1;
                        echo '<td colspan="'.$_cspan_num.'">'.($i+1).'</td>';
                    }
                #~ }

                // Per Row Display, All Modes
                foreach ($this->colgrid as $colvar) {
                    $col = &$this->properties[$colvar];
                    if ($action == 'edit' and !$col->updatable) continue;
                    if ($action == 'new' and !$col->insertable) continue;
                    // Per Field Display - Edit and New mode
                    if ($action == 'edit' or $action == 'new') {
                        echo '<td>';
                        if ($action == 'edit' and $col->is_key) {   # will be deleted on future!
                            $value = $this->ds->{$colvar}[$rowindex];
                            if ($col->enumerate) {
                                if (is_string($col->enumerate)) {
                                    $e = instantiate_module($col->enumerate);
                                    $value = $e->enum_decode($this->ds->{$colvar}[$rowindex]);
                                    if ($value === False) {
                                        $value = $this->ds->{$colvar}[$rowindex].' <span style="color:f00"><b>(ref?)</b></span>';
                                    }
                                }
                                elseif (is_array($col->enumerate)) {
                                    $value = $col->enumerate[$this->ds->{$colvar}[$rowindex]];
                                }
                            }
                            echo '<b>'.$value.'</b>';
                        }
                        elseif ($this->_preview) {   # preview me
                            echo '<input type="hidden" name="field['.$colvar.']['.$rowindex.']" value="'.$this->ds->{$colvar}[$rowindex].'">';
                            echo ' '.$this->ds->{$colvar}[$rowindex].' ';
                        }
                        else {
                            $this->input_widget("field[$colvar][$rowindex]", $this->ds->{$colvar}[$rowindex], $colvar);
                        }
                        echo '</td>';
                    }
                    // Per Field Display - Browse Mode
                    else {
                        if ($this->browse_form_statictext and $col->inputtype=='file' and $value != '') {
                            $value = '<a href="'.$_SERVER['PHP_SELF'].'?m=upload_manager&act=download&rowid='.$this->ds->{$colvar}[$rowindex].'">'.$value.'</a>';
                            //why not echo?
                        }
                        elseif ($this->browse_form_statictext) {
                            // decide the value to show
                        if ($col->enumerate) { # if field is enumerated, get the enumerate value instead
                            $value = '';
                            if (is_string($col->enumerate) and $this->ds->{$colvar}[$rowindex] != '') {
                                $e = instantiate_module($col->enumerate);
                                $value = $e->enum_decode($this->ds->{$colvar}[$rowindex]);
                                if ($value === False) {
                                    $col->notes = '<span style="color:f00"><b>(ref?)</b></span> '.$col->notes;
                                }
                            }
                            elseif (is_array($col->enumerate)) {
                                $value = $col->enumerate[$this->ds->{$colvar}[$rowindex]];
                            }
                        }
                        elseif ($col->inputtype=='combobox' and $col->choices) {    # if field is using simple enumeration, also get the choice value instead
                            $value = $col->choices[$this->ds->{$colvar}[$rowindex]];
                        }
                        else {
                            $value = $this->ds->{$colvar}[$rowindex];
                        }

                        $maxchar = $col->browse_maxchar;
                        if ($maxchar > 0 and strlen($value) > $maxchar) {
                            $value = substr($value,0,$maxchar).'..';
                        }
                        $value = htmlentities($value);  # escape html characters

                        if ($col->hyperlink != '') { # evaluate URL call back
                            if (method_exists($this,$col->hyperlink)) {
                                $ret = $this->{$col->hyperlink}($rowindex);   # callback expected to return array of "url" and "target"
                                if ($ret) {
                                    $ret['target'] = ($ret['target'] != '')? ' target="'.$ret['target'].'" ' : '';
                                    $value = '<a href="'.$ret['url'].'"'.$ret['target'].'>'.$value.'</a>';
                                }
                            }
                            elseif ($col->hyperlink === 'lov') {
                                $value = '<a href="javascript:set_lov(\''.$this->ds->{$this->enum_keyval[0]}[$rowindex].'\')">'.$this->ds->{$colvar}[$rowindex].'</a>';
                            }
                            else {  # consider data as URI
                                $value = '<a href="'.$this->ds->{$colvar}[$rowindex].'">'.$this->ds->{$colvar}[$rowindex].'</a>';
                            }
                        }

                            echo '<td>'.$value.'</td>';
                        }
                        else {
                            $value = $this->ds->{$colvar}[$rowindex];
                            # make combo and textarea as text
                            #~ $col = $this->properties[$colvar];
                            if ($col->inputtype == 'combobox') {
                                $col->inputtype = 'text';
                            }

                            if ($col->enumerate != '') {
                                $enum_list = array();
                                if (is_string($col->enumerate)) {
                                    $e = instantiate_module($col->enumerate);
                                    $value = $e->enum_decode($value);
                                }
                                elseif (is_array($col->enumerate)) {
                                    $value = $col->enumerate[$value];
                                }
                                else {
                                    die($colvar.':'.$col->inputtype.' enumerate parameter type does not supported');
                                }
                            }
                            elseif ($col->choices) {
                                $value = $col->choices[$value];
                            }

                            elseif ($col->inputtype == 'textarea') {
                                $col->inputtype = 'text';
                            }

                            echo '<td nowrap>';
                            $this->input_widget("field[$colvar][{$this->_cursor}]", $value, $colvar);
                            echo '</td>';
                        }
                    }
                }
                echo "</tr>\r\n";
            }
            echo '</table>';
        }
        else {
            echo '</table>';
            echo '<p align="center"><b>'.lang('Table has no').' '.lang($this->unit).'</b>';
        }

        echo $this->body[$this->action]['suffix'];  # show prefix/suffix body

        $_submitlabel = ($this->preview[$this->action] and !$this->_preview)? ' '.lang('Preview').' ': ' '.$this->submit_label['new'].' ';

        if ($action == 'edit' or $action == 'new') {
            echo '<p><input type=submit value="'.$_submitlabel.'"> | ';
            #~ echo '<b><a href="'.$this->_go.'">Cancel</a></b></p>';
            echo '<input type=button value="'.lang('Cancel').'" onclick="window.location=\''.$this->_go.'\'">';
            #~ echo '<b><a href="'.$this->_go.'" onclick="window.history.back();return false;">Cancel</a></b></p>';
        }

        echo '</form>';

        if ($action == 'browse') {
            $this->show_new_record();
        }
    }

    function show_new_record() {
    /* show "add X records below grid" */
        if ($this->allow_new) {
            #~ echo "<p><a href='{$_SERVER['PHP_SELF']}?m={$this->module}&amp;act=new&amp;go=".urlencode($GLOBALS['full_self_url'])."'>Insert new row</a>";
            echo '<form method=POST action="'.$_SERVER['PHP_SELF'].'">';
            echo '<input type=hidden name="m" value="'.$this->module.'">';
            echo '<input type=hidden name="act" value="new">';
            echo '<input type=hidden name="go" value="'.htmlentities($GLOBALS['full_self_url']).'">';         # url to go after successful submitation
            # if i'm a detail, get master-detail field's value and pass it to new-form
            if ($this->logical_parent) {
                foreach ($this->properties as $colvar=>$col) {
                    if ($col->parentkey) { # foreign key always int
                            echo '<input type=hidden name="sugg_field['.$colvar.']" value="'.htmlentities($col->parentkey_value).'">';
                    }
                }
            }
            echo '<p>'.lang('Add').' <input type=text name="num_row" size=2 value="1"> '.lang($this->unit).' <input type=submit value="'.lang('Go').'">';
            echo '</form>';
        }
    }

    function showform() {
    /* display row one by one in a form-style */
        $this->showerror();

        # make sure cursor does not point to invalid index
        if ($this->_cursor > ($this->db_count-1)) $this->_cursor = $this->db_count-1;
        if ($this->_cursor < 0) $this->_cursor = 0;

        $this->grid_command[] = array('csv',lang('Generate CSV'));

        # prepare javascript validation and confirmation function for each action
        echo '<!-- 1223 --> <script type="text/javascript">';
        if ($this->action == 'browse') {
            echo 'function form_submit_confirm(myform) {
                    action = myform.elements[\'act\'].value;
                    if (action == \'del\') {
                        if (!confirm(\''.lang('Are you sure you want to delete').'?\')) {
                            return false;
                        }
                        myform.submit();
                        return true;
                    }
                    else {
                        myform.submit();
                        return true;
                    }
                    return false;
                }
                ';
        } else {  # callback to function like $this->form_submit_confirm_new()
            echo $this->{'form_submit_confirm_'.$this->action}();
        }

        echo '</script>';

        echo '<table border="0" cellpadding="0" cellspacing="0" summary="paging">';  //style="border-collapse: collapse;"
        echo '<tr valign="top">';

        if ($this->action == 'browse') {
            if ($this->db_count > 1) {
                echo '<td valign="top" nowrap>&nbsp;&nbsp;';
                # determine on which index current rowid is
                if ($this->_cursor > 0) {
                    $r = 0;
                    $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&amp;act=browse&amp;cursor='.$r.'&amp;orderby='.$this->_orderby.'&amp;sortdir='.$this->_sortdir.'&amp;query='.urlencode($this->_query).'&amp;qf='.urlencode($_REQUEST['qf']);
                    echo '<a href="'.$url.'"><img src="images/b_firstpage.gif" border="0" alt="first record"></a> ';
                    $r = $this->_cursor - 1;
                    $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&amp;act=browse&amp;cursor='.$r.'&amp;orderby='.$this->_orderby.'&amp;sortdir='.$this->_sortdir.'&amp;query='.urlencode($this->_query).'&amp;qf='.urlencode($_REQUEST['qf']);
                    echo '<a href="'.$url.'"><img src="images/b_prevpage.gif" border="0" alt="prev record"></a> ';
                }
                else {
                    echo '<img src="images/bd_firstpage.gif" border="0" alt="first record"> ';
                    echo '<img src="images/bd_prevpage.gif" border="0" alt="prev record"> ';
                }

                if ($this->_cursor < ($this->db_count -1)) {
                    $r = $this->_cursor + 1;
                    $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&amp;act=browse&amp;cursor='.$r.'&amp;orderby='.$this->_orderby.'&amp;sortdir='.$this->_sortdir.'&amp;query='.urlencode($this->_query).'&amp;qf='.urlencode($_REQUEST['qf']);
                    echo '<a href="'.$url.'"><img src="images/b_nextpage.gif" border="0" alt="next record"></a> ';
                    $r = $this->db_count - 1;
                    $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&amp;act=browse&amp;cursor='.$r.'&amp;orderby='.$this->_orderby.'&amp;sortdir='.$this->_sortdir.'&amp;query='.urlencode($this->_query).'&amp;qf='.urlencode($_REQUEST['qf']);
                    echo '<a href="'.$url.'"><img src="images/b_lastpage.gif" border="0" alt="last record"></a> ';
                }
                else {
                    echo '<img src="images/bd_nextpage.gif" border="0" alt="next record"> ';
                    echo '<img src="images/bd_lastpage.gif" border="0" alt="last record"> ';
                }
                echo '</td>';
            }
            echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
                echo '<input type=hidden name=m value="'.$this->module.'">';
                echo '<input type=hidden name=go value="'.htmlentities($GLOBALS['full_self_url']).'">';         # url to go after successful submitation
                echo '<input type=hidden name="rowid[]" value="'.$this->ds->_rowid[$this->_cursor].'">';   # for edit-action
            echo '<input type="hidden" name="act">';
                echo '<td>&nbsp;&nbsp;';
            echo '<input type="button" onclick="this.form.act.value = \'edit\';form_submit_confirm(this.form);" value="'.lang('Edit').'" '.(($this->allow_edit and $this->db_count > 0)?'':'disabled').'> ';
            echo '<input type="button" onclick="this.form.act.value = \'del\';form_submit_confirm(this.form);" value="'.lang('Delete').'" '.(($this->allow_delete and $this->db_count > 0)?'':'disabled').'> ';
            echo '<input type="button" onclick="this.form.act.value = \'duplicate\';form_submit_confirm(this.form);" value="'.lang('Duplicate').'" '.(($this->allow_duplicate and $this->db_count > 0)?'':'disabled').'> ';
            echo '<input type="button" onclick="this.form.act.value = \'view\';form_submit_confirm(this.form);" value="'.lang('View').'" '.(($this->allow_view and $this->db_count > 0)?'':'disabled').'> ';
            echo '<input type="button" onclick="this.form.act.value = \'new\';form_submit_confirm(this.form);" value="'.lang('New').'" '.($this->allow_new?'':'disabled').'> ';

            if (count($this->grid_command))
                echo ' | Other: ';

            foreach ($this->grid_command as $command) {
                #~ echo '<input type="button" onclick="this.form.act.value = \''.$command[0].'\';submit_confirm(this.form);" value="'.lang($command[1]).'"> ';
                echo '<input type="button" onclick="this.form.act.value = \''.$command[0].'\';this.form.submit();" value="'.lang($command[1]).'"> ';
            }
            echo '</form>';
                echo '</td>';
            }
        echo '</tr></table>';  //outer table

        if ($this->_query != '') {
            if ($this->db_count == 0) {
                echo '<p><b>'.lang('The search').' "'.$this->_query.'" '.lang('returns no record').'</b>. <a href="'.$_SERVER['PHP_SELF'].'?m='.$this->module.'">'.lang('Reset Search').'</a>.</p>';
                return;
            }
            else {
                echo '<p><b>'.lang('The search').' "'.$this->_query.'" '.lang('found').' '.$this->db_count.' '.lang($this->unit).'</b>. <a href="'.$_SERVER['PHP_SELF'].'?m='.$this->module.'">'.lang('Reset Search').'</a>.</p>';
            }
        }

        echo $this->body[$this->action]['prefix'];  # show prefix body

        echo '<table border="0" summary="form format">';
        # form for new record
        echo '<form method=post enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'" onSubmit="return form_submit_confirm(this);" autocomplete="off">';
        echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
        echo '<input type=hidden name=act value="'.$this->action.'">';   # contains the action (edit/new)
        #~ echo '<input type=hidden name=save value="'.$this->_save.'">';         # marker to indicate form submitation
        if (!$this->_preview)
            echo '<input type=hidden name=save value="-1">';         # marker to indicate form submitation
        else
            echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
        #~ echo '<input type=hidden name=save value="-1">';         # marker to indicate form submitation
        echo '<input type=hidden name=go value="'.htmlentities($this->_go).'">';         # url to go after successful submitation
        echo '<input type=hidden name="num_row" value="'.$this->db_count.'">';
        echo '<input type=hidden name="rowid['.$this->_cursor.']" value="'.$this->ds->_rowid[$this->_cursor].'">';   # for edit-action

        # decide, which columns to show in form
        $this->colgrid = array();
        foreach ($this->properties as $key=>$col) {
            if ($this->action == 'browse' and $col->hidden) continue;
            if ($this->action == 'edit' and !$col->updatable) continue;
            if ($this->action == 'new' and !$col->insertable) continue;
            $this->colgrid[] = $key;
        }
        $i = 0; # html table rows
        $i2 = 0; # datasource columns

        for ($ci = 0; $ci < count($this->colgrid); $ci++) {
            $colvar = $this->colgrid[$ci];
            $i2++;
            $col = &$this->properties[$colvar];

            if ($col->box_start != '') {    # box start append a title line
                echo '<tr><td colspan="2"><br><b>'.$col->box_start.'</b></td></tr>';
            }

            if ($this->action != 'browse' or $this->browse_mode != 'form' or ($i2 % $this->browse_form_cols == 1)) {
                $rowcolour = ($i++ % 2 == 0)? 'greyformlight': 'greyformdark';
                echo '<tr class="'.$rowcolour.'">';
            }

            echo '<td>';
            #~ if ($this->action != 'browse' and $col->required)
                #~ echo '<span class="asterix">*</span>';
            $label = $col->colspan_label != ''? $col->colspan_label: $col->label;
            #~ if ($col->is_key)
                #~ echo '<b>'.$label.'</b>';
            #~ else
            echo $label;
            echo '</td>';

            echo '<td>';
            $max_colspan = $col->colspan;   # save this first, since $col will be change on subsequent loops
            for ($colspan=0; $colspan < $max_colspan; $colspan++) {
                $colvar = $this->colgrid[$ci + $colspan];
                $col = &$this->properties[$colvar];
                $value = $this->ds->{$colvar}[$this->_cursor];
                if ($this->_preview) {   # preview me
                    echo '<input type="hidden" name="field['.$colvar.']['.$this->_cursor.']" value="'.$value.'">';
                    echo ' '.$value.' ';
                }
                else {
                    echo $col->prefix_text;
                    if ($this->action == 'browse') {
                        if ($col->enumerate) { # if field is enumerated, get the enumerate value instead
                            $value = '';
                            if (is_string($col->enumerate) and $value != '') {
                                $e = instantiate_module($col->enumerate);
                                $value = $e->enum_decode($value);
                                if ($value === False) {
                                    $col->notes = '<span style="color:f00"><b>(ref?)</b></span> '.$col->notes;
                                }
                            }
                            elseif (is_array($col->enumerate)) {
                                $value = $col->enumerate[$value];
                            }
                        }
                        elseif ($col->inputtype=='combobox' and $col->choices) {    # if field is using simple enumeration, also get the choice value instead
                            $value = $col->choices[$value];
                        }
                        else {
                            #~ $value = $this->ds->{$colvar}[$rowindex];
                            #pass
                        }

                        if ($col->inputtype == 'combobox') {
                            $col->inputtype = 'text';
                        }
                    }

                    if ($this->action == 'browse' and $this->browse_form_statictext) {
                        echo '<b>';
                        echo ' '.$value.' ';
                        echo '</b>';
                    }
                    else {
                        $this->input_widget("field[$colvar][{$this->_cursor}]", $value, $colvar);
                    }
                }
            }
            $ci += $max_colspan - 1;    # since ->colspan starts at 1
            if ($this->action != 'browse' and $this->_save != -1)   # edit/add mode and not preview
                echo '&nbsp;'.$col->notes;
            echo '</td>';

            if ($this->action != 'browse' or $this->browse_mode != 'form' or ($i2 % $this->browse_form_cols == 0)) {
                echo "</tr>\r\n";
            }

            if ($col->box_end) {
                echo '<tr><td colspan="2"><br></td></tr>';
            }


        }
        echo '</table>';

        # show prefix/suffix body
        if (method_exists($this, $this->body[$this->action]['suffix'])) # give chance for suffix to execute them self.
            echo $this->{$this->body[$this->action]['suffix']}();
        else
            echo $this->body[$this->action]['suffix'];

        $_submitlabel = ($this->preview[$this->action] and !$this->_preview)? ' '.lang('Preview').' ': ' '.$this->submit_label['new'].' ';
        if ($this->action != 'browse') {
            echo '<p><input type=submit value="'.$_submitlabel.'">  | ';
            #~ echo '<b><a href="'.$this->_go.'">Cancel</a></b></p>';
            echo '<input type=button value="'.lang('Cancel').'" onclick="window.location=\''.$this->_go.'\'">';
            #~ echo '<b><a href="" onclick="window.history.back();return false;">Cancel</a></b>';
        }
        echo '</form>';

        # show suffix2 body (after previous big form)
        if (method_exists($this, $this->body[$this->action]['suffix2'])) # give chance for suffix to execute them self.
            echo $this->{$this->body[$this->action]['suffix2']}();
        else
            echo $this->body[$this->action]['suffix2'];
    }

    function show_childgrid() {
    /*  if this datasource has detail, and in update/show mode, show it as grid */
        if (($this->action=='edit' or $this->action=='view' or $this->action=='browse') and $this->childds) {
            foreach ($this->childds as $child_modulename) {
                $child_module = instantiate_module($child_modulename);
                $child_module->logical_parent = $this; # 1. for parent: bind children to parent
                # bind child's foreign key to me
                # - search which field is the foreign key
                foreach ($child_module->properties as $k=>$col) {
                    if ($col->enumerate == $this->module) { # foreign key always int
                        $child_module->properties[$k]->hidden = 1; # hide this field from browse
                        $child_module->properties[$k]->parentkey = 1; # flag that this field IS the connecting master/detail key. will be used by add-suggestive-field
                        $child_module->properties[$k]->parentkey_value = $this->ds->_rowid[$this->_cursor];
                        $child_module->db_where = '`'.$col->colname.'`='.$this->ds->_rowid[$this->_cursor];
                        break;
                    }
                }
                $child_module->final_init();
                if ($this->db_count)    # if parent empty, do not populate child
                $child_module->populate();
                #~ echo '<hr>';
                echo '<br>';
                echo '<table>';
                echo '<tr><td style="color:white; background-color:rgb(99,140,181);">'.$child_module->title.'</td></tr>';
                echo '<tr><td>';
                $child_module->showgrid('browse');
                echo '</tr></td></table>';
            }
        }
    }

    function show_combo($varname, $keyvalarr, $row=1, $with_all=0) {
        if ($row > 1)
            echo '<select name="'.$varname.'[]" row="'.$row.'" multiple>';
        else
            echo '<select name="'.$varname.'">';
        if ($with_all)
            echo '<option value="*">all</option>';

        foreach ($keyvalarr as $key=>$val) {
            if ($row > 1)
                $selected = (in_array($key,$_REQUEST[$varname]))? 'selected': '';
            else
                $selected = ($_REQUEST[$varname] == $key)? 'selected': '';
            echo '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
        }
        echo '</select>';
    }

    function input_widget ($fieldname,$value,$colvar){
        $col = $this->properties[$colvar];
        $param_size = '';
        $readonly = '';
        $style = '';
        if ($this->action == 'browse') {
            $readonly = 'disable';
            $style= 'style="background-color:#ddd;"';
        }
        if ($col->required)
            $fld_class = 'class="required"';
        switch ($col->inputtype) {
            case 'text':
                if ($col->length > 0 and ($col->browse_maxchar == 0 or $col->browse_maxchar > $col->length)) {   # use length as size, but limit to 50
                    # sometimes I'm lazy to define proper browse_maxchar for field with 255chars, so limit to 80 if browse_maxchat is not defined (0)
                    $temp_maxchar = ($col->length > 50)? 50: $col->length;  # limit to 50 chars in widget (realistic aja!)
                    $param_size = 'size="'.$temp_maxchar.'"';
                }
                elseif ($col->browse_maxchar > 0) { # use browse_maxchar
                    $param_size = 'size="'.$col->browse_maxchar.'"';
                }
                else {  # use default input text size
                    $param_size = '';
                }
                $max_size = '';
                if ($this->action == 'browse') {
                    # grid + !browse_static_text => remove "name" parameter to avoid all of the fields get submitted (only the checkboxes are needed)
                    $param_name = '';
                }
                else {
                    $max_size = 'maxlength="'.$col->length.'"'; # new! limit size according to data type length
                    $param_name = 'name="'.$fieldname.'"';
                }

                echo '<input '.$style.' type="text" '.$param_name.' value="'.$value.'" '.$param_size.' '.$max_size.' '.$readonly.' '.$fld_class.'>';
                break;
            case 'password':
                echo '<input '.$style.' type="password" name="'.$fieldname.'" value="'.$value.'" '.$readonly.' '.$fld_class.'>';
                break;
            case 'checkbox':
                if ($this->action == 'browse') {
                    # grid + !browse_static_text => remove "name" parameter to avoid all of the fields get submitted (only the checkboxes are needed)
                    $param_name = '';
                }
                else {
                    $param_name = 'name="'.$fieldname.'"';
                }
                if ($col->enumerate == '') {    # case 1, a boolean checkbox
                    $value == '1'? $ischecked = 'checked': $ischecked = '';
                    echo '<input '.$style.' type="checkbox" '.$param_name.' value="1" '.$ischecked.' '.$readonly.'>';
                }
                else {  # case 2, a multi-selections checkboxes (alternative for comboboxes)
                    $enum_list = array();
                    if (is_string($col->enumerate)) {
                        $e = instantiate_module($col->enumerate);
                        $enum_list = $e->enum_list();
                    }
                    elseif (is_array($col->enumerate)) {
                        $enum_list = $col->enumerate;
                    }
                    else {
                        die($colvar.':'.$col->inputtype.' enumerate parameter type does not supported');
                    }
                    foreach ($enum_list as $id=>$text) {
                        if (is_array($value))
                            in_array($id,$value)? $ischecked = 'checked': $ischecked = '';
                        echo '<input '.$style.' type="checkbox" name="'.$fieldname.'[]" value="'.htmlentities($id).'" '.$ischecked.' '.$readonly.'>'.htmlentities($text).'<br>';
                    }
                }
                break;
            case 'textarea':
                $param_cols = ($this->action == 'browse')? round(70 / $this->browse_form_cols): 70;

                echo '<textarea '.$style.' id="'.$fieldname.'" name="'.$fieldname.'" cols="'.$param_cols.'" rows="'.$col->rows.'" '.$readonly.' '.$fld_class.'>'.$value.'</textarea>';
                if ($col->inputtype2 == 'htmlarea' and $this->action != 'browse') {
                    global $html_footer_strings;
                    $html_footer_strings .= '<script type="text/javascript">HTMLArea.replace("'.$fieldname.'");</script>';
                }
                #~ echo $html_footer_strings;
                #~ echo "<textarea id='TA' name='$fieldname' cols='80' rows='20'>$value</textarea>";
                #~ global $html_body_param;
                #~ $html_body_param = 'onload="HTMLArea.replaceAll();"';
                break;
            case 'combobox':
                if ($col->enumerate != '') {
                    $enum_list = array();
                    if (is_string($col->enumerate)) {
                        $e = instantiate_module($col->enumerate);
                        $enum_list = $e->enum_list();
                    }
                    elseif (is_array($col->enumerate)) {
                        $enum_list = $col->enumerate;
                    }
                    else {
                        die($colvar.':'.$col->inputtype.' enumerate parameter type does not supported');
                    }
                }
                elseif ($col->choices) {
                    $enum_list = $col->choices;
                }
                else {
                    die($colvar.':'.$col->inputtype.' need enumerate or choices parameter');
                }
                echo '<select '.$style.' name="'.$fieldname.'" '.$readonly.' '.$fld_class.'>';
                echo '<option value=""></option>';
                foreach ($enum_list as $id=>$text) {
                    ($value != '' and $value == $id)? $ischecked = 'selected': $ischecked = '';
                    echo '<option value="'.$id.'" '.$ischecked.'>'.$text.'</option>';
                }
                echo '</select>';
                break;
            case 'file':    # file upload field
                if ($this->action == 'browse') {
                    $um = instantiate_module('upload_manager');
                    $value2 = $um->enum_decode($value);
                    if ($col->length > 0) {
                        $param_size = 'size="'.($col->browse_maxchar==0? $col->length : $col->browse_maxchar).'"';
                    }
                    if ($value != '') {# show a link to download the file
                        echo '<a href="'.$_SERVER['PHP_SELF'].'?m=upload_manager&act=download&rowid='.$value.'"><img src="images/sp.gif" border="0"></a>';
                    }
                    # grid + !browse_static_text => remove "name" parameter to avoid all of the fields get submitted (only the checkboxes are needed)
                    echo '<input '.$style.' type="text" value="'.$value2.'" '.$param_size.' '.$readonly.'>';
                }
                else {
                        $um = instantiate_module('upload_manager');
                    $value2 = $um->enum_decode($value);
                    if ($this->action == 'edit' and $value != '') {
                        echo $value2;
                        echo '<input type="checkbox" name="delete_'.$fieldname.'" value="1"> Erase';
                        echo '<br>';
                    }
                    echo '<input '.$style.' type="file" name="'.$fieldname.'" value="'.$value2.'" '.$readonly.'>';
                }
                break;
            default:
                die($colvar.': "'.$col->inputtype.'" not supported');
                break;
        }
    }

/* ==== Overrideable functions */

    function check_del($rowindex) {
    /* called on row delete validation */
        return True;
    }

    function duplicate_row() {
        $rowid = $this->_rowid;
        if ($rowid == '') {
            global $last_message;
            $last_message = '<p>'.lang('You need to pick at least one row').'</p>';
            $this->action = 'browse';
            return;
        }
        # confirmation now uses javascript confirm()
        # directly duplicate row
        $this->populate($rowid); # populate rows
        for ($i = 0; $i < $this->db_count; $i++) {
            $this->insert($i);
        }
        echo '<p>'.count($rowid).' row(s) has been duplicated</p>';
        echo '<p><b><a href="'.$this->_go.'">Continue</a></b></p>';
        return;
    }

    function remove($rowindex) {    # remove a row by providing row index (solved to rowid)
        assert($this->ds->_rowid[$rowindex] != '');
        # before deleting row, check if there's a file field, and its value is exist, then delete the assocaiated file first
        $um = NULL;
        $row = NULL;
        foreach ($this->properties as $colvar=>$col) {
            if ($col->inputtype == 'file' and $col->datatype == 'int') {
                # populate this row first (just once, in case there is more than 1 file field)
                if (!$row)
                    $row = $this->get_row(array('rowid'=>$this->ds->_rowid[$rowindex]));
                if (!$row) continue;
                $file_id = $row[$col->colname];
                if (!$um)
                    $um = instantiate_module('upload_manager');
                $um->del_file($file_id);    # del the file
            }
        }

        $sql = 'delete from `'.$this->db_table.'` where rowid='.$this->ds->_rowid[$rowindex].' limit 1';    # with safeguard
        #~ echo '<br>'.$sql;
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
    }

    function check_datatype($i) {
        $keys = array();
        $keys_label = array();
        foreach ($this->properties as $colvar=>$col) {
            if ($this->ds->{$colvar}[$i] != '' and ($col->datatype == 'double' or $col->datatype == 'int' or $col->datatype == 'float') and !is_numeric($this->ds->{$colvar}[$i])) {
                $this->error_msgs[] = '['.($i+1).'] '.$col->label.' only accept numeric data';
                $this->error_rows[$i] = True;
                return False;
            }
            elseif ($this->ds->{$colvar}[$i] != '' and $col->datatype == 'int' and round(floatval($this->ds->{$colvar}[$i])) != floatval($this->ds->{$colvar}[$i])) {
                $this->error_msgs[] = '['.($i+1).'] '.$col->label.' only accept round (integer) number';
                $this->error_rows[$i] = True;
                return False;
            }
            #~ elseif (($col->datatype == 'varchar' or $col->datatype == 'text') and strlen($this->ds->{$colvar}[$i]) > $col->length) {
                #~ $this->error_msgs[] = '['.($i+1).'] '.$col->label.' must be '.$col->length.' characters or less';
                #~ $this->error_rows[$i] = True;
                #~ return False;
            #~ }
        }
        return True;
    }

    function check_duplicate_index($i) {
        $keys = array();
        $keys_label = array();
        foreach ($this->properties as $colvar=>$col) {
            if ($col->is_key) {
                $keys[$col->colname] = $this->ds->{$colvar}[$i];
                $keys_label[$col->label] = $this->ds->{$colvar}[$i];
            }
        }

        if ($keys and $this->get_row($keys,'1')) {
            $this->error_msgs[] = '['.($i+1)."] There's already a {$this->unit} with ".join('/',array_keys($keys_label))." = ".join('/',array_values($keys_label));
            $this->error_rows[$i] = True;
            return False;
        }
        return True;
    }

    function prepare_insert($rowindex) {
        foreach ($this->properties as $colvar=>$col) {
            if ($col->on_new_callback != '')
                $this->ds->{$colvar}[$rowindex] = eval($col->on_new_callback);
        }
        return True;
    }

    function check_insert($rowindex) {
        # place holder function
        return True;
    }

    function insert($rowindex) {
        /* construct sql insert
        this function expects these variable to be ready:
        - $this->properties
        - $this->ds->ALL COLVARS->rowindex  = the value
        */
        $tf = array();
        $tv = array();
        #~ $value = '';
        $um = NULL;
        foreach ($this->properties as $colvar=>$col) {
            if ($col->colname == '') continue;    # a virtual field, usually used for display purpose
            $value = $this->ds->{$colvar}[$rowindex];
            if ($col->on_insert_callback != '')
                $value = eval($col->on_insert_callback);
            if ($col->inputtype == 'file' and $col->datatype == 'int') {
                if (!$um) $um = instantiate_module('upload_manager');
                $value = $um->put_file($colvar, $rowindex, $this->module);
            }

            $tf[] = '`'.$col->colname.'`';
            if ($value == 'Now()') {
                $tv[] = myaddslashes($value);
            }
            else {
                $tv[] = "'".myaddslashes($value)."'";
            }
        }
        $sql_fields = join(',',$tf);
        $sql_values = join(',',$tv);
        $sql = 'insert into `'.$this->db_table.'` ('.$sql_fields.') values ('.$sql_values.')';
        #~ echo $sql;exit();
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
        $ret = mysql_insert_id();   # new!
        return $ret;
    }

    function prepare_update($rowindex) {    # just before displaying the first update form
        foreach ($this->properties as $colvar=>$col) {
            if ($col->on_edit_callback != '') {
                $this->ds->{$colvar}[$rowindex] = eval($col->on_edit_callback);
            }
        }
        return True;
    }

    function check_update($rowindex) {  # just before updating
        # place holder function
        return True;
    }

    function update($rowindex) {  # updating
        /* update given ds index to database */
        $tf = array();

        # special for file field, if delete_field preset, then remove the file
        $um = NULL;
        if ($_REQUEST['delete_field']) {
            $um = instantiate_module('upload_manager');
            foreach ($_REQUEST['delete_field'] as $key=>$val) {
                if ($_REQUEST['delete_field'][$key][$rowindex] == '1') {
                    $um->del_file($this->ds->{$key}[$rowindex]);
                    $this->ds->{$key}[$rowindex] = '';
                }
            }
        }

        foreach ($this->properties as $colvar=>$col) {
            if ($col->colname == '') continue;    # a virtual field, usually used for display purpose
            #~ if (!isset($_REQUEST['field'][$colvar][$rowindex])) continue;
            #~ if (!$col->updatable and $col->on_update_callback == '') continue; # hidden/non-updateable/key field
            $value = $this->ds->{$colvar}[$rowindex];
            if ($col->on_update_callback != '')
                $value = eval($col->on_update_callback);

            if ($col->inputtype == 'file' and $col->datatype == 'int') {
                if (!$um) $um = instantiate_module('upload_manager');
                $temp_rowid = $um->put_file($colvar, $rowindex, $this->module);
                if ($temp_rowid != '') $value = $temp_rowid;
            }

            $tf[] = '`'.$col->colname."`='".myaddslashes($value)."'";
        }
        $sql_set = join(',',$tf);
        $sql = 'update `'.$this->db_table.'` set '.$sql_set.' where rowid=\''.$this->ds->_rowid[$rowindex].'\'';
        #~ echo '<br>'.$sql;exit;
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
    }

    function enum_list() {  # return list of id/desc
        /* by default, if available, expects enum_keyval to be filled with array('field1','field2')
        new: if enum_keyval second member is comma delimited, assume to show them as multiple columns
        */
        if ($this->enum_keyval) {
            $enumlist = array();
            foreach ($this->get_rows('', $this->enum_keyval[0].','.$this->enum_keyval[1],'row') as $row) {
                $enumlist[$row[0]] = join(' | ',array_slice($row,1));
            }
            return $enumlist;
        }
    }

    function enum_encode($desc) {   # return id of desc
        /* by default, if available, expects enum_keyval to be filled with array('field1','field2') */
        if ($desc == '') {
            return '';
        }
        elseif ($this->enum_keyval) {
            $row = $this->get_row(array($this->enum_keyval[1]=>$desc), $this->enum_keyval[0],'row');
            if ($row === False)
                return False;
            else
            return $row[0];
        }
    }

    function enum_decode($id) { # return desc of id
    /* by default, if available, expects enum_keyval to be filled with array('field1','field2') */
        if ($id == '') {
            return '';
        }
        elseif ($this->enum_keyval) {
            $row = $this->get_row(array($this->enum_keyval[0]=>$id), $this->enum_keyval[1],'row');
            if ($row === False)
                return False;
            else
                return $row[0];
        }
    }

    function form_submit_confirm_new() {    #client side checking, on new submit
    /* echoed before enterim form, inside <script>, define form_submit_confirm javascript function behaviour in your code
    */
        return <<<__END__
        function form_submit_confirm(myform) {
            //action = myform.elements['act'].value;
            //myform.submit();
        }
__END__;
    }

    function form_submit_confirm_edit() {    #client side checking, on edit submit
    /* echoed before enterim form, inside <script>, define form_submit_confirm javascript function behaviour in your code
    */
        return <<<__END__
        function form_submit_confirm(myform) {
            //action = myform.elements['act'].value;
            //myform.submit();
        }
__END__;
    }

    function grid_submit_confirm_new() {    #client side checking, on new submit
    /* echoed before enterim form, inside <script>, define form_submit_confirm javascript function behaviour in your code
    */
        return <<<__END__
        function submit_confirm(myform) {
            //action = myform.elements['act'].value;
            //myform.submit();
        }
__END__;
    }

    function grid_submit_confirm_edit() {    #client side checking, on edit submit
    /* echoed before enterim form, inside <script>, define form_submit_confirm javascript function behaviour in your code
    */
        return <<<__END__
        function submit_confirm(myform) {
            //action = myform.elements['act'].value;
            //myform.submit();
        }
__END__;

    }

}
?>