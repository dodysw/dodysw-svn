<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class membership extends TableManager {
    var $db_table, $properties;
    function membership() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Membership';
        $html_title = $this->title;

        #~ $this->db_table = $GLOBALS['dbpre'].'media_client_tab';
        $this->properties['username'] = new Prop(array('label'=>'Username','colname'=>'username','required'=>True, 'is_key'=>True, 'length'=>25));
        $this->properties['password'] = new Prop(array('label'=>'Password','colname'=>'password', 'inputtype'=>'password','required'=>True,'hidden'=>True, 'length'=>50));
        $this->properties['confirm_password'] = new Prop(array('label'=>'Confirm Password','inputtype'=>'password','required'=>True,'hidden'=>True, 'length'=>50, 'on_edit_callback'=>'return $this->ds->password[$rowindex];', 'on_validate'=>'return ($value == $this->ds->password[$i])? 1: "password and confirmation do not match";'));
        $this->properties['email'] = new Prop(array('colname'=>'email','cdatatype'=>'email', 'required'=>True));
        $this->properties['register_date_time'] = new Prop(array('colname'=>'register_date_time', 'datatype'=>'datetime'));
        #~ $this->properties['last_order_date_time'] = new Prop(array('colname'=>'register_date_time', 'datatype'=>'datetime'));

        $this->properties['full_name'] = new Prop(array('colname'=>'full_name','required'=>True));
        $this->properties['priviledge_card_number'] = new Prop(array('colname'=>'priviledge_card_number', 'length'=>10));
        $this->properties['address1'] = new Prop(array('colname'=>'address1','required'=>True));
        $this->properties['address2'] = new Prop(array('colname'=>'address2',));
        $this->properties['zipcode'] = new Prop(array('colname'=>'zipcode','required'=>True, 'length'=>5));
        $this->properties['city'] = new Prop(array('colname'=>'city','required'=>True, 'length'=>16));
        #~ $this->properties['country'] = new Prop(array('colname'=>'country','required'=>True, 'inputtype'=>'combobox', 'enumerate'=>'membership'));
        $this->properties['contact_person'] = new Prop(array('colname'=>'contact_person','required'=>True, 'length'=>20));
        $this->properties['phone_number'] = new Prop(array('colname'=>'phone_number','required'=>True, 'length'=>12));

        $this->properties['creation_date_time'] = new Prop(array('cdatatype'=>'creation_date_time'));
        $this->properties['created_by'] = new Prop(array('cdatatype'=>'created_by'));
        $this->properties['last_update_date_time'] = new Prop(array('cdatatype'=>'last_update_date_time'));
        $this->properties['last_updated_by'] = new Prop(array('cdatatype'=>'last_updated_by'));
        $this->properties['last_updating_process'] = new Prop(array('cdatatype'=>'last_updating_process'));


        $this->unit = 'member';
        $this->enum_keyval = array('rowid','username,email');

        $this->childds = array('member_order','member_cart');
    }

    function go() {// called inside main content
        $this->basic_handler();
    }

    function insert($rowindex) {
        $this->ds->password[$rowindex] = md5($this->ds->password[$rowindex]);
        parent::insert($rowindex);
    }

    function update($rowindex) {
        # wee need to check whether the given pass is the same as the database, this is a trick for md5-style password entry
        $row = $this->get_row(array('rowid'=>$this->ds->_rowid[$rowindex]));
        if ($row['password'] != $this->ds->password[$rowindex]) {   # if change then md5 it
            $this->ds->password[$rowindex] = md5($this->ds->password[$rowindex]);
        }
        parent::update($rowindex);
    }

    function fe_register($rowid) {
        $this->allow_edit = False;   # bool, True to allow edit command
        $this->allow_delete = False;   # bool, True to allow delete command
        $this->allow_view = False;   # bool, True to allow view command
        $this->final_init();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            #~ $this->final_init();
            #~ $this->import2ds();
            #~ $this->db_count = $_REQUEST['num_row'];
            #~ if (!$this->validate_rows())    # don't continue if form does not pass validation
                #~ return False;

            #~ $this->insert(0);

            #~ header('Location: '.$this->_go);
            #~ exit();
        }

        #~ $this->clear();
        $this->action = 'new';
        $this->submit_label['new'] = 'Submit';
        $this->properties['register_date_time']->insertable = False;
        $this->properties['register_date_time']->editable = False;
        #~ $this->populate($rowid);
        $this->showform();


    }


}

?>
