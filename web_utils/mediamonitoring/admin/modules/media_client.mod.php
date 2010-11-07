<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class media_client extends TableManager {
    var $db_table, $properties;
    function media_client() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Client manager';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'media_client_tab';
        $this->properties['username'] = new Prop(array('label'=>'Username','colname'=>'username','required'=>True, 'is_key'=>True, 'length'=>25));
        $this->properties['password'] = new Prop(array('label'=>'Password','colname'=>'password', 'inputtype'=>'password','required'=>True,'hidden'=>True, 'length'=>50));
        $this->properties['email'] = new Prop(array('label'=>'Email','colname'=>'email','length'=>100,'browse_maxchar'=>25));
        #~ $this->properties['param_1'] = new Prop(array('label'=>'Param 1','colname'=>'param_1','length'=>200,'browse_maxchar'=>25)); # used for po1/2/3
        #~ $this->properties['last_activity'] = new Prop(array('label'=>'Last Activity','colname'=>'last_activity','datatype'=>'datetime'));
        $this->unit = 'media';
        $this->enum_keyval = array('username','username');

        $this->confirm_delete = False;
        $this->allow_delete = True;
        $this->must_authenticated = False;
        #~ $this->description = '<p>Let\'s you manage username and password for authentication, module level and group permission, email address, and their last activity date.';

        $this->final_init();    # must be call before end of initialization
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


}

?>
