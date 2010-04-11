<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
require_once( LOG4PHP_DIR . '/LoggerManager.php' ); //log4php

class propinsi extends TableManager {
    var $db_table, $properties;

    function propinsi() {
        parent::TableManager(); # must call base class
		$this->_logger =& LoggerManager::getLogger('Propinsi');

        global $html_title;
        $this->title = 'Propinsi';
        $html_title = $this->title;
        $this->db_table = $GLOBALS['dbpre'].'propinsi_tab';

        $this->properties['kode_propinsi'] = new Prop(array('label'=>'Kode Propinsi','length'=>2,'colname'=>'kode_propinsi', 'required'=>True, 'is_key'=>True));
        $this->properties['nama_propinsi'] = new Prop(array('label'=>'Propinsi','colname'=>'nama_propinsi','length'=>45,'required'=>True,'is_key'=>false));
        $this->properties['luas_wilayah'] = new Prop(array('label'=>'Luas Wilayah','colname'=>'luas_wilayah','length'=>25,'required'=>False,'is_key'=>false));
        $this->properties['jumlah_penduduk'] = new Prop(array('label'=>'Jumlah Penduduk','colname'=>'jumlah_penduduk','length'=>25,'required'=>False,'is_key'=>false));


        $this->enum_keyval = array('kode_propinsi','nama_propinsi');

        $prog->must_authenticated = True;

        $this->browse_mode = 'form';

    }

    function go() { // called inside main content
        $this->basic_handler();
    }

}

?>