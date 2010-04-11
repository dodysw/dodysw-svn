<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
require_once( LOG4PHP_DIR . '/LoggerManager.php' ); //log4php

class lapisan_perkerasan extends TableManager {
    var $db_table, $properties;

    function lapisan_perkerasan() {
        parent::TableManager(); # must call base class
		$this->_logger =& LoggerManager::getLogger('lapisan_perkerasan');

        global $html_title;
        $this->title = 'Lapisan Perkerasan';
        $html_title = $this->title;
        $this->db_table = $GLOBALS['dbpre'].'lapisan_perkerasan_tab';

		$this->properties['kode_lapisan_perkerasan'] = new Prop(array('label'=>'Kode Lapisan Perkerasan','length'=>10,'colname'=>'kode_lapisan_perkerasan', 'required'=>True, 'is_key'=>True));
		$this->properties['deskripsi'] = new Prop(array('label'=>'Lapisan Perkerasan  ','colname'=>'deskripsi','length'=>30,'required'=>True,'is_key'=>false));
		
		$this->enum_keyval = array('kode_lapisan_perkerasan','deskripsi');
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