<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
require_once( LOG4PHP_DIR . '/LoggerManager.php' ); //log4php

class tipe_lapisan_permukaan extends TableManager {
    var $db_table, $properties;

    function tipe_lapisan_permukaan() {
        parent::TableManager(); # must call base class
		$this->_logger =& LoggerManager::getLogger('tipe_lapisan_permukaan');

        global $html_title;
        $this->title = 'Tipe Lapisan Permukaan' ;
        $html_title = $this->title;
        $this->db_table = $GLOBALS['dbpre'].'tipe_lapisan_permukaan_tab';

		$this->properties['kode_tipe_lapisan_permukaan'] = new Prop(array('label'=>'Kode Tipe Lapisan Permukaan','length'=>10,'colname'=>'kode_tipe_lapisan_permukaan', 'required'=>True, 'is_key'=>True));
		$this->properties['deskripsi'] = new Prop(array('label'=>'Tipe Lapisan Permukaan  ','colname'=>'deskripsi','length'=>30,'required'=>True,'is_key'=>false));
		
		$this->enum_keyval = array('kode_tipe_lapisan_permukaan','deskripsi');
        //$this->grid_command[] = array('attach','Attach to newsletter...');
        //$this->grid_command[] = array('','-----');


        $prog->must_authenticated = True;

        #~ if ($_SESSION['login_level'] > 1)
            #~ $this->db_where="author='{$_SESSION['login_user']}'";
        #~ $this->enum_keyval = array('rowid','nama');
        $this->browse_mode = 'form';

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