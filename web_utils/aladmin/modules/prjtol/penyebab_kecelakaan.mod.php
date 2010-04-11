<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
require_once( LOG4PHP_DIR . '/LoggerManager.php' ); //log4php

class penyebab_kecelakaan extends TableManager {
    var $db_table, $properties;

    function penyebab_kecelakaan() {
        parent::TableManager(); # must call base class
		$this->_logger =& LoggerManager::getLogger('penyebab_kecelakaan');

        global $html_title;
        $this->title = 'Penyebab Kecelakaan' ;
        $html_title = $this->title;
        $this->db_table = $GLOBALS['dbpre'].'penyebab_kecelakaan_tab';

		$this->properties['kode_penyebab_kecelakaan'] = new Prop(array('label'=>'Kode Penyebab Kecelakaan','length'=>10,'colname'=>'penyebab_kecelakaan', 'required'=>True, 'is_key'=>True));
		$this->properties['deskripsi'] = new Prop(array('label'=>'Penyebab Kecelakaan','colname'=>'deskripsi','length'=>30,'required'=>True,'is_key'=>false));

		$this->enum_keyval = array('kode_penyebab_kecelakaan','deskripsi');

        $prog->must_authenticated = True;

        $this->browse_mode = 'form';

    }

    function go() { // called inside main content
        $this->basic_handler();
    }

}

 ?>