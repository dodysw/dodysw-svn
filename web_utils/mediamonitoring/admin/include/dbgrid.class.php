<?
/*
    DBGRID CLASS
    class for easy admin-table management

    Copyright 2004,2005 Dody Suria Wijaya <dodysw@gmail.com>
    dody suria wijaya's software house - http://miaw.tcom.ou.edu/~dody/

*/

class Prop {
    /* bass class for all database fields */
    function Prop($param=array()) {
        # set default properties
        $col->colname = '';
        $this->label = '';
        $this->datatype = 'varchar';    # future use for datatype validation: varchar, date, datetime, time, float, int
        $this->inputtype = 'text';    # set input type at edit/new: text, password, textarea, combobox, checkbox, file
        $this->inputtype2 = '';    # additional information for given input type, ie: htmlarea (textarea)
        $this->rows = 3;    # int, for textarea, number of rows
        $this->cols = 60;    # int, for textarea, number of rows
        $this->length = 0;  # maximum length of this field, (also used for db datatype length ie: varchar(40))
        $this->required = False;    # required = True for datatype = checkbox means that there are at least 2 checkboxes and user must choose at least 1 of them. you must understand that 1 checkbox cannot be forced to be required!!
        #~ $this->browsable = True;    # set this to True to show field at browse
        $this->updatable = True;    # set this to True to show field at edit
        $this->insertable = True;   # set this to True to show field at new
        $this->hidden = False;  # set this to False to hide field at browse
        $this->is_key = False;  # set this to True to show key field (field will be forced to non-updatable, required, and bold at browse)
        $this->choices = array();   # set this to your own key=>value to simulate enumerate
        $this->browse_maxchar = 0;  # on browse-table, set the number of max characters to display (0 for no limitatDataSouncnrceion). on browse-form, will decide the size param in <input type=text..> (if 0, will use $this->length)
        $this->enumerate = '';  # if string, will be considered as module, if array, direct enumerate list. required for combobox
        $this->queryable = True;    # if true, column will appear in query combo
        /* parent key: signal framework that this field is the link for master-detail relationship
        this can be set to string or boolean which has different meanings:
        - boolean: if true, framework will get value from parent's ds-field which of the same colname (this->properties->colname)
        - string: if not empty, framework will directly get value from parent's ds-field of the specified colvar
        */
        $this->parentkey = False;
        $this->enum_keyval = array();
        $this->notes = '';  # string which will be appended after field in form-edit/new mode
        $this->prefix_text = '';  # string which will be prepended before field in form-edit/new mode
        $this->box_start = '';  # string, if not empty, will start a box with string as title
        $this->box_end = False;  # bool, if True, mark the end of box
        $this->colspan = 1; # int, like HTML->TABLE->TD colspan. group multi field into one column
        $this->colspan_label = ''; # string, if filled, colspanned field will use this string as label (instead of ->label). must put on first field of colspanned fields.
        $this->table = '';  # string, if defined, this field is from different table (will be joined by create_sql_select)
        $this->join_on = array();  # array, define the left join on (...) if field came from different table
        $this->join_order = 1;  # array, define ordering of the left join, for > 2 tables join
        $this->hyperlink = '';  # function, callback expected accept $rowindex param and to return array of "url" and "target", if url not empty, will be used as hyperlink at this column
                                # if method callback is not defined, will consider data as URI and activate its <a> HTML link

        # ==========END OF DEFAULT PROPERTIES. YOU MUST NOT PUT INITIALIZATION BELOW THIS LINE=======================
        foreach ($param as $k=>$v) {    # overwrite default var with var inside argunent
            $this->$k = $v;
        }
    }
}

class DataSource {
    /* simple bass class for datasource */
}

class TableManager {

//var $logger;
    function TableManager() {

		#~ $this->logger= & LoggerManager::getLogger('Table Manager');
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
        $this->browse_rows = 20; # int, rows displayed per page. 0 for all rows.
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
        $this->browse_form_statictext = True;   # bool, True to use static text instead of readonly input widget on form browse mode
        $this->browse_wait_query = False;   #bool, True to populate only on query (default false will populate immediately)
        $this->unit = 'record'; # string, the unit of record, will used on display, eg: "Add 3 new [unit]". wil

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
        $this->_sortdir = $_REQUEST['sortdir']; #browse: ASC or DESC
        $this->_orderby = $_REQUEST['orderby']; #browse: order by chosen column
        $this->_rowstart = ($_REQUEST['row'] == '')? 0: $_REQUEST['row'];    #browse: row index to start page browsing
        $this->_populated = False;  #true if this->populate has been done
        $this->_cursor = ($_REQUEST['cursor'] == '')? 0: $_REQUEST['cursor'];     # used for browse-form, integer pointing to datasource's current row index
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

		foreach ($this->properties as $key=>$col) {
            if ($col->label == '') { # fix empty label
                $this->properties[$key]->label = ucwords(str_replace('_',' ',$key));
            }
            if ($col->table == '') {
                $this->properties[$key]->table = $this->db_table;
            }
            if ($col->is_key) { # parse property for property mangling
                $this->properties[$key]->required = True;
                #~ $this->properties[$key]->updatable = False;
            }
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
        $this->import2ds(); # import _REQUEST['field'] var to ds

        if ($this->action == 'new' or $this->action == 'edit') { # handle new-save

            #~ if ($this->_save != '1') {
            if ($this->_save == '') {   # to accomodate -1 (preview)
				return False;
			}
            $this->db_count = intval($_REQUEST['num_row']) > 0? intval($_REQUEST['num_row']): 1;   # new row should get the number of row to insert from num_row
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
            header("Expires: 0");

            echo join("\r\n",$rows);
            exit();
        }
        else {  # no-act handler, call its post function callbacks if available
            $callback = 'act_'.$this->action;
            if (method_exists($this, $callback)) {
                $this->$callback(True);
            }
            #~ else {
                #~ die('Unhandled post-action "'.$this->action.'". Stopping...');
            #~ }
        }
    }

    function basic_handler() {
        /* called by go() */

        $this->action = ($this->action == '')? 'browse': $this->action;    #default to browse
		#~ $this->logger->debug('Starting basic_handler......264' . $this->action);

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
            #~ echo '<h4>Edit record</h4>';

            if (!$this->_populated) # may already been populated at post handler
                $this->populate($this->_rowid,True);    # merge = True to merge request with datasource

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
            $this->show_childgrid();
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
        $temp[] = $this->db_table.'.`rowid`';  # always add rowid
        $this->_mapping_index[] = '_rowid';
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
        #~ echo $sql;
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
        $sql = 'select '.$sql_select.' from '.$this->db_table.$sql_where;

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
        #set given key-val param (dict)
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
        #~ echo '<br>'.$sql;
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
        return mysql_insert_id();
    }

    function validate_field($colvar,$i) {
        $col = $this->properties[$colvar];
        if  (
                ($col->required and $col->inputtype == 'file' and $_FILES['field']['name'][$colvar][$i] == '') or
                ($col->required and isset($_REQUEST['field'][$colvar][$i]) and $_REQUEST['field'][$colvar][$i] == '') or
                ($col->required and $col->inputtype == 'checkbox' and !isset($_REQUEST['field'][$colvar][$i])) # case for required "multiple checkbox", user need to check at least 1 checkbox. we can't just detect using isset since non-checked checkbox does not generate empty key/val.
            )
            {
            #~ echo "<SCRIPT>alert('{$col->label} is required. Please retry.'); window.history.go(-1);</SCRIPT>\n";
            $this->error_msgs[] = "[".($i+1)."] {$col->label} ".lang('is required');
            $this->error_rows[$i] = True;
            return False;
        }
        return True;
    }

    function validate_rows() {
        #~ $db_count = $_REQUEST['num_row'];   # rowid in insert is actually dummy, used only for knowing how many rows to insert
        $ret = True;
        for ($i = 0; $i < $this->db_count; $i++) {
            foreach ($this->properties as $colvar=>$col) {
                if (!$this->validate_field($colvar,$i))  # make sure this field is not empty
                    $ret = False;
            }
        }
        return $ret;
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
        #~ $this->import2ds();     # import remaining virtual columns
        $this->_populated = True;
    }

    function import2ds() {
        /* copy _REQUEST['field'][*1][*2] to this->ds->*1[*2]
        used exclusively by new-post post-handlers
        */
        $ok = 0;
        foreach ($this->properties as $colvar=>$col) {
            if (isset($_REQUEST['field'][$colvar]) and !isset($this->ds->$colvar)) {    # i hazardously ignore action state (updatable, insertable...)
			#~ $this->logger->debug('moving into foreach...and if 611' . $_REQUEST['field'][$colvar] . 'ds =' . $this->ds->$colvar );

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
            elseif ($col->required and $col->inputtype == 'checkbox' and !isset($_REQUEST['field'][$colvar][$i]) and !isset($this->ds->$colvar)) { # case for required "multiple checkbox", user need to check at least 1 checkbox. we can't just detect using isset since non-checked checkbox does not generate empty key/val.
                $this->ds->$colvar = array();   # assign it to empty array
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
        echo '<td align=right>';    // see nav.inc.php for reason
        echo '<form name="search" method=POST action="'.$_SERVER['PHP_SELF'].'">';
        #~ echo '<img src="images/b_search.png" border="0">';
        echo '<b>'.lang('Search').'</b> ';
        echo '<input type=hidden name=m value="'.$this->module.'">';
        echo '<input type=hidden name=act value="browse">';
        echo '<input type=text name=query value="'.$this->_query.'" size=10>';
        echo ' <b>'.lang('in').'</b> ';
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
        echo '</td>';    // see nav.inc.php for reason

        #~ echo '</td></tr></table>';
    }

    function showerror() {
    /* display errors in form submitation */
        if (count($this->error_msgs)>0) {
            echo '<p><b>'.lang('Error in form').'</b>:<ul>';
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
        $action = ($action == '')? $this->action: $action;
        $this->showerror();
        global $last_message;
        if ($last_message != '') echo $last_message;    # place holder for anyone who whises to put msg above grid

        if ($this->allow_delete) {
            $this->grid_command[] = array('del',lang('Delete'));  # command, need to be list of list, since key may be duplicated
        }
        if ($this->allow_edit) {
            $this->grid_command[] = array('edit',lang('Edit'));  # command, need to be list of list, since key may be duplicated
        }
        if ($this->allow_new) {
            $this->grid_command[] = array('duplicate',lang('Duplicate'));  # command, need to be list of list, since key may be duplicated
        }
        if ($this->allow_view) {
            $this->grid_command[] = array('view',lang('View'));  # command, need to be list of list, since key may be duplicated
        }
        $this->grid_command[] = array('csv',lang('Generate CSV'));
        #~ if ($this->allow_query and $action == 'browse' and !$this->logical_parent) {
            #~ $this->showquery();
        #~ }

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
        echo '<form method=post enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'" onSubmit="return submit_confirm(this)">';
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

        echo '<table width="100%" border="0" cellpadding="0" cellspacing="0" summary="main container">';  //style="border-collapse: collapse;"
        echo '<tr>';
        #~ echo '<th class="heading"><b>'.ucfirst($this->action).": {$this->title}</b>";
        echo '<th class="heading"><b>'.$this->title.'</b>';
        #~ echo '<td align="right">';
        #~ if ($this->allow_new) {
            #~ $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&act=new&num_row=1&go='.urlencode($GLOBALS['full_self_url']);
            #~ echo '<a href="'.$url.'"><img src="images/b_new.png" border="0"> new</a>';
        #~ }
        echo '</th>';
        if (!$this->browse_mode_forced)
            echo "<td><small>[<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&amp;set_browse_mode=form'>".lang('switch to form')."</a>]</small></td>";
        echo '</tr></table>';  //outer table

        echo $this->body[$this->action]['prefix'];  # show prefix/suffix body

        echo '<table border="0" cellpadding="2" cellspacing="1" summary="format combo">';  //style="border-collapse: collapse;"
        echo '<tr class="greyformtitle">';
        if (!$this->query_only and ($this->allow_edit or $this->allow_view)) {
            $_cspan_num = 1;
            $_cspan_num += $this->allow_edit? 1: 0;
            $_cspan_num += $this->allow_view? 1: 0;
            echo '<th colspan="'.$_cspan_num.'">&nbsp;</th>';   # for command
        }
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
                    echo "<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&amp;act=browse&amp;row={$this->_rowstart}&amp;orderby={$col->colname}&amp;sortdir={$_sortdir}'>{$col->label}</a>";
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

                if (!$this->query_only and ($this->allow_edit or $this->allow_view)) {
                    if ($action == 'browse') {
                        echo "<td><input type=checkbox name='rowid[]' value='{$this->ds->_rowid[$rowindex]}'></td>";
                    }
                    else {
                        echo '<input type=hidden name="rowid['.$rowindex.']" value="'.$this->_rowid[$rowindex].'">';   # for edit-action
                        $_cspan_num = 1;
                        $_cspan_num += $this->allow_edit? 1: 0;
                        $_cspan_num += $this->allow_view? 1: 0;
                        echo '<td colspan="'.$_cspan_num.'">'.($i+1).'</td>';
                    }
                }

                if ($this->allow_edit and $action == 'browse')
                    echo "<td>&nbsp;<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&amp;act=edit&amp;rowid[]={$this->ds->_rowid[$rowindex]}&amp;go=".urlencode($GLOBALS['full_self_url'])."'><img src='images/button_edit.png' border=0 alt=edit></a></td>";
                #~ else
                    #~ echo '<td>&nbsp;</td>';

                if ($this->allow_view and $action == 'browse')
                    echo "<td>&nbsp;<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&amp;act=view&amp;rowid[]={$this->ds->_rowid[$rowindex]}&amp;go=".urlencode($GLOBALS['full_self_url'])."'><img src='images/button_view.png' border=0 alt=view></a>&nbsp;</td>";
                #~ else
                    #~ echo '<td>&nbsp;</td>';

                foreach ($this->colgrid as $colvar) {
                    $col = $this->properties[$colvar];
                    if ($action == 'edit' and !$col->updatable) continue;
                    if ($action == 'new' and !$col->insertable) continue;
                    if ($action == 'edit' or $action == 'new') {
                        echo '<td>';
                        if ($action == 'edit' and $col->is_key) {
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
                    else {
                        if ($col->enumerate) { # if field is enumerated, get the enumerate value instead
                            $value = '';
                            if (is_string($col->enumerate) and $this->ds->{$colvar}[$rowindex] != '') {
                                $e = instantiate_module($col->enumerate);
                                $value = $e->enum_decode($this->ds->{$colvar}[$rowindex]);
                                if ($value === False) {
                                    #~ $value = $this->ds->{$colvar}[$rowindex].' <span style="color:f00"><b>(ref?)</b></span>';
                                    $col->notes = '<span style="color:f00"><b>(ref?)</b></span> '.$col->notes;
                                }
								#~ $this->logger->debug('show grid ..String' . $e->enum_decode($this->ds->{$colvar}[$rowindex]));
                            }
                            elseif (is_array($col->enumerate)) {
                                $value = $col->enumerate[$this->ds->{$colvar}[$rowindex]];
								#~ $this->logger->debug('show grid ..Array' . $col->enumerate[$this->ds->{$colvar}[$rowindex]]);
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
                        #~ if ($value == '') $value = '&nbsp;';
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
                        if ($col->inputtype=='file' and $value != '') {
                            $value = '<a href="'.$_SERVER['PHP_SELF'].'?m=upload_manager&act=download&rowid='.$this->ds->{$colvar}[$rowindex].'">'.$value.'</a>';
                        }
                        if ($col->is_key)
                            echo '<td><b>'.$value.'</b></td>';
                        else
                            echo '<td>'.$value.'</td>';
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

        if ($action == 'browse' and $this->db_count > 0 and !$this->query_only and $this->grid_command) {
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
            echo '<img src="images/arrow_ltr.png" border="0" alt="arrow to checkbox">';
            # echo '<a href="" onclick="setCheckBoxes(this.form, true);return false;">Check All</a> / ';
            echo '<input type="button" onclick="setCheckBoxes(this.form, true);" value="'.lang('Check All').'"> / ';
            # echo '<a href="'.$_SERVER['PHP_SELF'].'" onclick="setCheckBoxes(this.form,false);return false;">Uncheck All</a> ';
            echo '<input type="button" onclick="setCheckBoxes(this.form, false);" value="'.lang('Uncheck All').'"> ';
            echo '<select name="act" onchange="return submit_confirm(this.form)"><option value="">'.lang('With selected').':</option>';
            foreach ($this->grid_command as $command) {
                echo "<option value='{$command[0]}'>{$command[1]}</option>";
            }
            echo '</select>';
            echo '<input type="submit" value="'.lang('Do').'">';

            # build page browsing below table
            echo '<br>';
            $max_rownum = $this->max_rownum();
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
            echo 'Total: '.$max_rownum.'';
            if ($this->browse_rows > 0 and $max_rownum > $this->db_count) {
                echo " - <a href='{$_SERVER['PHP_SELF']}?m={$this->module}&amp;act=browse&amp;maxrows=0&amp;orderby={$this->_orderby}&amp;sortdir={$this->_sortdir}'>".lang('Show All')."</a>";
            }


        }
        echo '</form>';

        if ($action == 'browse') {
            $this->shownewrecord();
        }
    }

    function shownewrecord() {
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
                    if ($col->parentkey and is_bool($col->parentkey)) {   # get the value from parent's same column name ds fields
                        foreach ($this->logical_parent->properties as $p_colvar=>$p_col) {
                            if ($col->colname == $p_col->colname) {
                                echo '<input type=hidden name="field['.$colvar.'][0]" value="'.htmlentities($this->logical_parent->ds->{$p_colvar}[$this->logical_parent->_cursor]).'">';
                            }
                        }
                    }
                    elseif ($col->parentkey and is_string($col->parentkey)) {
                        $sql_where[] = $col->colname."='".addslashes($this->logical_parent->ds->{$col->parentkey}[0])."'";    # parent must only has 1 row
                        echo '<input type=hidden name="field['.$colvar.'][0]" value="'.htmlentities($this->logical_parent->ds->{$col->parentkey}[$this->logical_parent->_cursor]).'">';
                    }
                }
            }
            echo '<p>'.lang('Add').' <input type=text name="num_row" size=2 value="1"> '.lang($this->unit).' <input type=submit value="'.lang('Go').'">';
            #~ echo "<p><a href='{$_SERVER['PHP_SELF']}?m={$this->module}&amp;act=new&amp;go=".urlencode($GLOBALS['full_self_url'])."'>Insert new row</a>";
            echo '</form>';
            #~ print_r($this->logical_parent->ds);
        }
    }

    function showform() {
    /* display row one by one in a form-style */
        $this->showerror();

        # make sure cursor does not point to invalid index
        if ($this->_cursor > ($this->db_count-1)) $this->_cursor = $this->db_count-1;
        if ($this->_cursor < 0) $this->_cursor = 0;

        if ($this->allow_new) {
            $this->grid_command[] = array('new',lang('New'));  # command, need to be list of list, since key may be duplicated
        }
        if ($this->allow_delete and $this->db_count) {
            $this->grid_command[] = array('del',lang('Delete'));  # command, need to be list of list, since key may be duplicated
        }
        if ($this->allow_edit and $this->db_count) {
            $this->grid_command[] = array('edit',lang('Edit'));  # command, need to be list of list, since key may be duplicated
        }
        if ($this->allow_new and $this->db_count) {
            $this->grid_command[] = array('duplicate',lang('Duplicate'));  # command, need to be list of list, since key may be duplicated
        }
        if ($this->allow_view and $this->db_count) {
            $this->grid_command[] = array('view',lang('View'));  # command, need to be list of list, since key may be duplicated
        }
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
        echo '<th class="heading"><b>'.ucfirst($this->action).": {$this->title}</b></th>";

        if ($this->action == 'browse') {
            if ($this->db_count > 1) {
                echo '<th valign="top">&nbsp;&nbsp;';
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
                echo '</th>';
                echo '<th>&nbsp;&nbsp;';
                echo ' ('.($this->_cursor+1).'/'.$this->db_count.') ';
                echo '</th>';
            }

            if ($this->grid_command) {
                echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
                echo '<input type=hidden name=m value="'.$this->module.'">';
                echo '<input type=hidden name=go value="'.htmlentities($GLOBALS['full_self_url']).'">';         # url to go after successful submitation
                #~ echo '<input type=hidden name=num_row value="1">';
                #~ echo '<input type=hidden name="rowid['.$this->_cursor.']" value="'.$this->ds->_rowid[$this->_cursor].'">';   # for edit-action
                echo '<input type=hidden name="rowid[]" value="'.$this->ds->_rowid[$this->_cursor].'">';   # for edit-action
                echo '<th>&nbsp;&nbsp;';

                echo '<select name=act onchange="return form_submit_confirm(this.form)">';
                echo '<option value="">__'.lang('Command').'__</option>';
                foreach ($this->grid_command as $command) {
                    echo "<option value='{$command[0]}'>{$command[1]}</option>";
                }
                echo '</select>';
                echo '<input type=submit value='.lang('Do').'>';
                echo '</th>';
                echo '</form>';
            }

            #~ if ($this->allow_new) {
                #~ $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&amp;act=new&amp;num_row=1&amp;go='.urlencode($GLOBALS['full_self_url']);
                #~ echo '<a href="'.$url.'"><img src="images/b_new.png" border="0"> new</a> | ';
            #~ }
            #~ if ($this->db_count and $this->allow_edit) {
                #~ $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&amp;act=edit&amp;rowid[]='.$this->ds->_rowid[$this->_cursor].'&amp;go='.urlencode($GLOBALS['full_self_url']);
                #~ echo '<a href="'.$url.'"><img src="images/b_edit.png" border="0"> edit</a> | ';
            #~ }
            #~ if ($this->db_count and $this->allow_delete) {
                #~ $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&amp;act=del&amp;rowid[]='.$this->ds->_rowid[$this->_cursor].'&amp;go='.urlencode($GLOBALS['full_self_url']);
                #~ $onclick = $this->confirm_delete? 'onClick="return confirm(\'Are you sure you want to delete this record?\');"': '';
                #~ echo '<a href="'.$url.'" '.$onclick.'><img src="images/b_drop.png" border="0"> delete</a> | ';
            #~ }
            #~ if ($this->db_count and $this->allow_new) {
                #~ $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&amp;act=duplicate&amp;rowid[]='.$this->ds->_rowid[$this->_cursor].'&amp;go='.urlencode($GLOBALS['full_self_url']);
                #~ $onclick = $this->confirm_duplicate? 'onClick="return confirm(\'Are you sure you want to duplicate this record?\');"': '';
                #~ echo '<a href="'.$url.'" '.$onclick.'><img src="images/duplicate.png" border="0"> dupe</a> | ';
            #~ }


        }

        if (!$this->browse_mode_forced and $this->action=='browse') {
            $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&amp;set_browse_mode=table';
            echo '<td>&nbsp;&nbsp;<small>[<a href="'.$url.'">'.lang('switch to table').'</a>]</small></td>';
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

        if ($this->action != 'browse')
            echo '<p><span class="asterix">*</span>='.lang('Indicates required fields');

        echo '<table border="0" summary="form format">';
        # form for new record
        echo '<form method=post enctype="multipart/form-data" action="'.$_SERVER['PHP_SELF'].'" onSubmit="return form_submit_confirm(this);">';
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
        #~ foreach ($this->colgrid as $colvar) {
        for ($ci = 0; $ci < count($this->colgrid); $ci++) {
            $colvar = $this->colgrid[$ci];
            $i2++;
            $col = $this->properties[$colvar];

            if ($col->box_start != '') {    # box start append a title line
                echo '<tr><td colspan="2"><br><b>'.$col->box_start.'</b></td></tr>';
            }

            if ($this->action != 'browse' or $this->browse_mode != 'form' or ($i2 % $this->browse_form_cols == 1)) {
                $rowcolour = ($i++ % 2 == 0)? 'greyformlight': 'greyformdark';
                echo '<tr class="'.$rowcolour.'">';
            }

            echo '<td>';
            if ($this->action != 'browse' and $col->required)
                echo '<span class="asterix">*</span>';
            $label = $col->colspan_label != ''? $col->colspan_label: $col->label;
            if ($col->is_key)
                echo '<b>'.$label.'</b>';
            else
                echo $label;
            echo '</td>';

            echo '<td>';
            $max_colspan = $col->colspan;   # save this first, since $col will be change on subsequent loops
            for ($colspan=0; $colspan < $max_colspan; $colspan++) {
                $colvar = $this->colgrid[$ci + $colspan];
                $col = $this->properties[$colvar];
                if ($this->action == 'edit' and $col->is_key) {
                    echo '<b>'.$this->ds->{$colvar}[$this->_cursor].'</b>';
                }
                elseif ($this->_preview) {   # preview me
                    echo '<input type="hidden" name="field['.$colvar.']['.$this->_cursor.']" value="'.$this->ds->{$colvar}[$this->_cursor].'">';
                    echo ' '.$this->ds->{$colvar}[$this->_cursor].' ';
                }
                else {
                    echo $col->prefix_text;
                    if ($this->action == 'browse' and $this->browse_form_statictext) {
                        echo '<b>';
                        echo ' '.$this->ds->{$colvar}[$this->_cursor].' ';
                        echo '</b>';
                    }
                    else
                        $this->input_widget("field[$colvar][{$this->_cursor}]", $this->ds->{$colvar}[$this->_cursor], $colvar);
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
            echo '<p>';
            echo '<input type=submit name="bt_datachange" value="Data change">  | ';
            echo '<input type=submit name="bt_save" value="'.$_submitlabel.'">  | ';
            #~ echo '<b><a href="'.$this->_go.'">Cancel</a></b></p>';
            echo '<input type=button name="bt_cancel" value="'.lang('Cancel').'" onclick="window.location=\''.$this->_go.'\'">';
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
                $child_module->populate();
                #~ echo '<h4><i>'.$child_module->title.'</i></h4>';
                echo '<hr>';
                $child_module->showgrid('browse');
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
            $readonly = 'readonly';
            $style= 'style="background-color:#ddd;font-weight:bold;"';
        }
        switch ($col->inputtype) {
            case 'text':
                if ($col->length > 0 and $col->browse_maxchar == 0) {   # use length as size, but limit to 50
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

                echo '<input '.$style.' type="text" name="'.$fieldname.'" value="'.$value.'" '.$param_size.' '.$readonly.'>';
                if ($col->enumerate != '') {
                    #~ echo '<a href="lov.php?'.merge_query(array('m'=>$col->enumerate)).'"><b>LOV</b></a>';
                    echo <<<___END___
<script type="text/javascript" language="javascript">
var lovwindow = '';
function open_lovwindow(obj) {
    if (!lovwindow.closed && lovwindow.location) {
        lovwindow.focus();
    } else {
        lovwindow = window.open('lov.php?m={$col->enumerate}', '','toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=yes,resizable=yes,width=550,height=310');
    }
    if (!lovwindow.opener) {
        lovwindow.opener = self;
    }
    lovwindow.origfield = obj;
    if (window.focus) {
        lovwindow.focus();
    }
    return false;
}
</script>
___END___;

                    #~ echo '<a href="lov.php?m='.$col->enumerate.'" onclick="javascript:open_lovwindow(); return false;"><b>LOV</b></a>';
                    echo '<input type="button" value="LOV" onclick="javascript:open_lovwindow(this.form.elements[\''.$fieldname.'\']); return false;">';
                }
                break;
            case 'password':
                echo '<input '.$style.' type="password" name="'.$fieldname.'" value="'.$value.'" '.$readonly.'>';
                break;
            case 'checkbox':
                if ($col->enumerate == '') {    # case 1, no enumerate, a boolean checkbox
                    $value == '1'? $ischecked = 'checked': $ischecked = '';
                    echo "<input $style type=checkbox name='$fieldname' value='1' $ischecked $readonly>";
                }
                else {  # case 2, a multi-selections checkboxes
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
                        echo "<input $style type=checkbox name='{$fieldname}[]' value='".htmlentities($id)."' $ischecked $readonly>".htmlentities($text)."<br>";
                    }
                }
                break;
            case 'textarea':
                $param_cols = ($this->action == 'browse')? round(70 / $this->browse_form_cols): 70;

                echo "<textarea $style id='$fieldname' name='$fieldname' cols='$param_cols' rows='{$col->rows}' $readonly>$value</textarea>";
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
                echo "<select $style name='$fieldname' $readonly>";
                echo "<option value=''></option>";
                foreach ($enum_list as $id=>$text) {
                    ($value != '' and $value == $id)? $ischecked = 'selected': $ischecked = '';
                    echo "<option value='$id' $ischecked>$text</option>";
                }
                echo '</select>';
                break;
            case 'file':    # file upload field
                if ($readonly) {
                    if ($col->length > 0) {
                        $param_size = 'size="'.($col->browse_maxchar==0? $col->length : $col->browse_maxchar).'"';
                    }
                    echo "<input $style type=text name='$fieldname' value='{$value}' $param_size $readonly>";
                }
                else {
                    if ($this->action == 'edit' and $value != '') {
                        $um = instantiate_module('upload_manager');
                        echo '<b>'.$value.'='.$um->enum_decode($value).'</b> - ';
                        echo '<input type="checkbox" name="delete_'.$fieldname.'" value="1"> Erase';
                        echo '<br>';
                    }
                    echo '<input '.$style.' type="file" name="'.$fieldname.'" value="'.$value.'" '.$readonly.'>';
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
        $sql = 'delete from `'.$this->db_table.'` where rowid=\''.$this->_rowid[$rowindex].'\' limit 1';    # with safeguard
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
            $this->error_msgs[] = "[".($i+1)."] There's already a record with ".join('/',array_keys($keys_label))." = ".join('/',array_values($keys_label));
            $this->error_rows[$i] = True;
            return False;
        }
        return True;
    }

    function prepare_insert($rowindex) {
        # place holder function
        return True;
    }

    function check_insert($rowindex) {
        # place holder function
        return True;
    }

    function insert($rowindex) {
        # construct sql insert
        $tf = array();
        $tv = array();
        foreach ($this->properties as $colvar=>$col) {
            if ($col->colname == '') continue;    # a virtual field, usually used for display purpose
            $value = $this->ds->{$colvar}[$rowindex];
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
        #~ echo $sql;exit;
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
    }

    function prepare_update($rowindex) {    # just before displaying the first update form
        # place holder function
        return True;
    }

    function check_update($rowindex) {  # just before updating
        # place holder function
        return True;
    }

    function update($rowindex) {  # updating
        /* update given ds index to database */
        $tf = array();
        foreach ($this->properties as $colvar=>$col) {
            if ($col->colname == '') continue;    # a virtual field, usually used for display purpose
            #~ if (!isset($_REQUEST['field'][$colvar][$rowindex])) continue;
            if (!$col->updatable) continue; # hidden/non-updateable/key field
            $tf[] = '`'.$col->colname."`='".myaddslashes($this->ds->{$colvar}[$rowindex])."'";
        }
        $sql_set = join(',',$tf);
        $sql = 'update `'.$this->db_table.'` set '.$sql_set.' where rowid=\''.$this->ds->_rowid[$rowindex].'\'';
        #~ echo '<br>'.$sql;
        #~ exit();
        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
    }

    function enum_list() {  # return list of id/desc
        /* by default, if available, expects enum_keyval to be filled with array('field1','field2') */
        if ($this->enum_keyval) {
            $enumlist = array();
            foreach ($this->get_rows('', $this->enum_keyval[0].','.$this->enum_keyval[1],'row') as $row) $enumlist[$row[0]] = $row[1];
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