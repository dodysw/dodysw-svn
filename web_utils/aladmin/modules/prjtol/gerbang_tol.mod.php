<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
require_once( LOG4PHP_DIR . '/LoggerManager.php' ); //log4php

class gerbang_tol extends TableManager {
    var $db_table, $properties;

    function gerbang_tol() {
        parent::TableManager(); # must call base class
		$this->_logger =& LoggerManager::getLogger('gerbang_tol');

        global $html_title;
        $this->title = 'Gerbang Tol';
        $html_title = $this->title;
        $this->db_table = $GLOBALS['dbpre'].'gerbang_tol_tab';

		$this->properties['kode_cabang'] = new Prop(array('label'=>'Cabang','length'=>4,'colname'=>'kode_cabang', 'required'=>True, 'is_key'=>True,'enumerate'=>'cabang','parentkey'=>'kode_cabang', 'inputtype'=>'combobox'));
		$this->properties['kode_gerbang'] = new Prop(array('label'=>'Kode Gerbang','length'=>4,'colname'=>'kode_gerbang', 'required'=>True, 'is_key'=>True));
		$this->properties['gerbang'] = new Prop(array('label'=>'Gerbang','length'=>40,'colname'=>'gerbang', 'required'=>True, 'is_key'=>false));




        $this->enum_keyval = array('kode_gerbang','gerbang');

        //$this->grid_command[] = array('attach','Attach to newsletter...');
        //$this->grid_command[] = array('','-----');


        $prog->must_authenticated = True;

        #~ if ($_SESSION['login_level'] > 1)
            #~ $this->db_where="author='{$_SESSION['login_user']}'";
        #~ $this->enum_keyval = array('rowid','nama');
        $this->browse_mode = 'table';

    }

    function go() { // called inside main content
        //debug($this);

        //LoggerNDC::push('NDC generated by TestTest::testLog()');
    /*
        $logger->debug('Try the debug statement');
        $logger->info('Try the info statement');
        $logger->warn('Try the debug warn');
        $logger->error('Try the statement Error');
        $logger->fatal('Try the fatal statement');
	*/
		#~ echo "<h3>Tahapan Kerjasama</h3>";
        $this->basic_handler();

    }



    function insert($rowindex) {
    //    $this->ds->create_date[$rowindex] = 'Now()';
      //  $this->ds->author[$rowindex] = $_SESSION['login_user'];
        parent::insert($rowindex);
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