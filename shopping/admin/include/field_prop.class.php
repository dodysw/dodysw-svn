<?
/*
    Copyright 2004,2005 Dody Suria Wijaya <dodysw@gmail.com>
*/

include_once('cdatatypes.class.php');

class Prop {
    /* bass class for all database fields */
    function Prop($param=array()) {
        # set default properties
        $this->colvarname = '';  # name variable of this field, as known by dbgrid (will be set at TableManager.final_init())
        $this->colname = '';
        $this->label = '';
        $this->datatype = 'varchar';    # future use for datatype validation: varchar, date, datetime, time, float, int, blob
        $this->inputtype = 'text';    # set input type at edit/new: text, password, textarea, combobox, checkbox, file
        $this->inputtype2 = '';    # additional information for given input type, ie: htmlarea (textarea)
        $this->rows = 3;    # int, for textarea, number of rows
        $this->cols = 60;    # int, for textarea, number of rows
        $this->length = 255;  # maximum length of this field, (also used for db datatype length ie: varchar(40))
        $this->required = False;    # required = True for datatype = checkbox means that there are at least 2 checkboxes and user must choose at least 1 of them. you must understand that 1 checkbox cannot be forced to be required!!
        #~ $this->browsable = True;    # set this to True to show field at browse
        $this->updatable = True;    # set this to True to show field at edit
        $this->insertable = True;   # set this to True to show field at new
        $this->hidden = False;  # set this to False to hide field at browse
        $this->is_key = False;  # set this to True to show key field (field will be forced to non-updatable, required, and bold at browse)
        $this->choices = array();   # set this to your own key=>value to simulate enumerate
        $this->browse_maxchar = 15;  # on browse-table, set the number of max characters to display (0 for no limitatDataSouncnrceion). on browse-form, will decide the size param in <input type=text..> (if 0, will use $this->length)
        $this->enumerate = '';  # if string, will be considered as module, if array, direct enumerate list. required for combobox
        $this->queryable = True;    # if true, column will appear in query combo
        /* parent key: signal framework that this field is the link for master-detail relationship
        this can be set to string or boolean which has different meanings:
        - boolean: if true, framework will get value from parent's ds-field which of the same colname (this->properties->colname)
        - string: if not empty, framework will directly get value from parent's ds-field of the specified colvar
        */
        $this->parentkey = False;       # true if this field is the child's foreign key to its current master
        $this->parentkey_value = '';    # the value of which the master is connected to this field
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
        $this->cdatatype = '';  # class datatype, see cdatatypes.class.php
        $this->on_edit_callback = '';
        $this->on_update_callback = '';
        $this->on_new_callback = '';
        $this->on_insert_callback = '';
        $this->on_delete_callback = '';
        $this->on_validate = '';

        # ==========END OF DEFAULT PROPERTIES. YOU MUST NOT PUT INITIALIZATION BELOW THIS LINE=======================
        global $all_cdatatypes; # from cdatatypes.class.php
        if ($param['cdatatype'] != '') {
            foreach ($all_cdatatypes[$param['cdatatype']] as $k=>$v) {    # overwrite default var with var inside argunent
                $this->$k = $v;
            }
        }

        foreach ($param as $k=>$v) {    # overwrite default var with var inside argunent
            $this->$k = $v;
        }
    }

    function init($dbgrid) {
        if ($this->label == '') { # fix empty label
            $this->label = ucwords(str_replace('_',' ',$this->colvarname));
        }
        if ($this->table == '') {
            $this->table = $dbgrid->db_table;
        }
        if ($this->is_key) { # parse property for property mangling
            $this->required = True;
        }
    }
}
?>