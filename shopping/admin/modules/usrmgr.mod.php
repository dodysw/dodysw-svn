<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class usrmgr extends TableManager {
    var $db_table, $properties;
    function usrmgr() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'User manager';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'user_tab';
        $this->properties['username'] = new Prop(array('label'=>'Username','colname'=>'username','required'=>True, 'is_key'=>True, 'length'=>25, 'on_validate'=>'return $value == "supervisor"? "you can not use that username": 1;'));
        $this->properties['password'] = new Prop(array('label'=>'Password','colname'=>'password', 'inputtype'=>'password','required'=>True,'hidden'=>True, 'length'=>50));
        $this->properties['confirm_password'] = new Prop(array('label'=>'Confirm Password','inputtype'=>'password','required'=>True,'hidden'=>True, 'length'=>50, 'on_edit_callback'=>'return $this->ds->password[$rowindex];', 'on_validate'=>'return ($value == $this->ds->password[$i])? 1: "password and confirmation do not match";'));
        $this->properties['level'] = new Prop(array('colname'=>'level','datatype'=>'int','required'=>True, 'inputtype'=>'combobox','choices'=>array(0=>'Supervisor',1=>'Administrator',2=>'User level 2', 3=>'User level 3', 4=>'User level 4', 5=>'User level 5'), 'on_validate'=>'return ($value === 0 and $_SESSION["login_level"] != 0)? "only supervisor can create supervisor":1;'));
        $this->properties['group'] = new Prop(array('colname'=>'group', 'length'=>25,'inputtype'=>'combobox', 'choices'=>array('admin'=>'Administrators','user'=>'Users')));
        $this->properties['email'] = new Prop(array('colname'=>'email','cdatatype'=>'email'));
        $this->properties['param_1'] = new Prop(array('label'=>'Param 1','colname'=>'param_1','length'=>200,'browse_maxchar'=>25)); # used for po1/2/3
        #~ $this->properties['last_activity'] = new Prop(array('label'=>'Last Activity','colname'=>'last_activity','datatype'=>'datetime'));

        $this->properties['creation_date_time'] = new Prop(array('cdatatype'=>'creation_date_time'));
        $this->properties['created_by'] = new Prop(array('cdatatype'=>'created_by'));
        $this->properties['last_update_date_time'] = new Prop(array('cdatatype'=>'last_update_date_time'));
        $this->properties['last_updated_by'] = new Prop(array('cdatatype'=>'last_updated_by'));
        $this->properties['last_updating_process'] = new Prop(array('cdatatype'=>'last_updating_process'));

        $this->unit = 'user';

        $this->confirm_delete = False;
        $this->allow_delete = True;
        $this->must_authenticated = False;
        #~ $this->description = '<p>Let\'s you manage username and password for authentication, module level and group permission, email address, and their last activity date.';
        $this->childds[] = 'usrmgr_login';

        $this->enum_keyval = array('rowid','`username`,`group`');
    }

    function go() {// called inside main content
        $this->basic_handler();
    }

    function insert($rowindex) {
        $this->ds->password[$rowindex] = md5($this->ds->password[$rowindex]);
        return parent::insert($rowindex);
    }

    function update($rowindex) {
        #~ if ($this->ds->level[$rowindex] == 0) $this->ds->level[$rowindex] = 1;  # 0 is reserved for supervisor
        # wee need to check whether the given pass is the same as the database, this is a trick for md5-style password entry
        $row = $this->get_row(array('rowid'=>$this->ds->_rowid[$rowindex]));
        if ($row['password'] != $this->ds->password[$rowindex]) {   # if change then md5 it
            $this->ds->password[$rowindex] = md5($this->ds->password[$rowindex]);
        }
        parent::update($rowindex);
    }

    function enum_decode($id) { # super-supervisor does not in the usrmgr, so we need to fake it out
        if ($id == '0') return 'supervisor';
        return parent::enum_decode($id);
    }

}

?>
