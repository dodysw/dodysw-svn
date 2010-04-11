<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
require_once( LOG4PHP_DIR . '/LoggerManager.php' ); //log4php

class account extends TableManager {
    var $db_table, $properties;

    function account() {
        parent::TableManager(); # must call base class
		$this->_logger =& LoggerManager::getLogger('acount');

        global $html_title;
        $this->title = 'Account';
        $html_title = $this->title;
        $this->db_table = $GLOBALS['dbpre'].'account_tab';

		$this->properties['account_no'] = new Prop(array('label'=>'Kode Account','length'=>4,'colname'=>'account_no', 'required'=>True, 'is_key'=>True));
		$this->properties['description'] = new Prop(array('label'=>'Account  ','colname'=>'description','length'=>30,'required'=>True,'is_key'=>false));
		$this->properties['parent_account'] = new Prop(array('label'=>'Master Account','colname'=>'parent_account','length'=>4,'required'=>False,'is_key'=>false));


        

        //$this->grid_command[] = array('attach','Attach to newsletter...');
        //$this->grid_command[] = array('','-----');


        $prog->must_authenticated = True;

        #~ if ($_SESSION['login_level'] > 1)
            #~ $this->db_where="author='{$_SESSION['login_user']}'";
        $this->enum_keyval = array('account_no','description');
        $this->browse_mode = 'table';

    }

    function go() { // called inside main content
        
		#~ echo "<h3>Tahapan Kerjasama</h3>";
        $this->basic_handler();

    }

    function check_del() {
        # for normal user, make sure they can only modify their own
        if ($_SESSION['login_level'] > 1) {
            foreach ($this->_rowid as $rowid) {
                if (!$this->get_row(array('rowid'=>$rowid,'author'=>$_SESSION['login_user'])))
                    echo '<p>You may not modify this row</p>';
                    return False;
            }
        }
        return True;
    }

    function prepare_update() {
        # for normal user, make sure they can only modify their own
        if ($_SESSION['login_level'] > 1) {
            foreach ($this->_rowid as $rowid) {
                if (!$this->get_row(array('rowid'=>$rowid,'author'=>$_SESSION['login_user']))) {
                    echo '<p>You may not modify this row</p>';
                    return False;
                }
            }
        }
        return True;
    }



}

?>