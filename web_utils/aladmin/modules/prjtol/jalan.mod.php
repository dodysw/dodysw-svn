<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
require_once( LOG4PHP_DIR . '/LoggerManager.php' ); //log4php

class jalan extends TableManager {
    var $db_table, $properties;

    function jalan() {
        parent::TableManager(); # must call base class
		$this->_logger =& LoggerManager::getLogger('jalan');

        global $html_title;
        $this->title = 'Jalan';
        $html_title = $this->title;
        $this->db_table = $GLOBALS['dbpre'].'jalan_tab';

        $this->properties['kode_cabang'] = new Prop(array('label'=>'Cabang','length'=>5,'colname'=>'kode_cabang', 'required'=>True, 'is_key'=>True,'inputtype'=>'combobox','enumerate'=>'cabang','parentkey'=>'kode_cabang'));
        $this->properties['kode_jalan'] = new Prop(array('label'=>'Kode jalan','length'=>8,'colname'=>'kode_jalan', 'required'=>True, 'is_key'=>True));
        $this->properties['jalan'] = new Prop(array('colname'=>'jalan','length'=>45,'required'=>True,'is_key'=>false));
//		$this->properties['kota_asal'] = new Prop(array('label'=>'Kota Asal','colname'=>'kota_asal','length'=>7,'required'=>False,'is_key'=>false,'inputtype'=>'combobox','enumerate'=>kota));
//	$this->properties['kota_tujuan'] = new Prop(array('label'=>'Kota Tujuan','colname'=>'kota_tujuan','length'=>7,'required'=>False,'is_key'=>false,'inputtype'=>'combobox','enumerate'=>kota));
        //$this->properties['jumlah_penduduk'] = new Prop(array('label'=>'Jumlah Penduduk','colname'=>'jumlah_penduduk','length'=>25,'required'=>False,'is_key'=>false));
        //$this->properties['tipe_jalan'] = new Prop(array('label'=>'Tipe Jalan','colname'=>'tipe_jalan','length'=>1,'hidden'=>True,'required'=>False,'is_key'=>false));
        //$this->properties['keterangan'] = new Prop(array('label'=>'Keterangan','colname'=>'keterangan','length'=>255,'hidden'=>false,'required'=>False,'is_key'=>false,'inputtype'=>'textarea'));

        $this->properties['luas_permukaan_a'] = new Prop(array('box_start'=>'Jalur A', 'label'=>'Jalur A: Luas Permukaan (m2)','colname'=>'luas_permukaan_a','length'=>17,'datatype'=>'double','required'=>False,'is_key'=>false));
        $this->properties['luas_kerusakan_a'] = new Prop(array('label'=>'Jalur A: Luas Kerusakan (%)','colname'=>'luas_kerusakan_a','length'=>17,'datatype'=>'double','required'=>False,'is_key'=>false));
        $this->properties['luas_permukaan_b'] = new Prop(array('box_start'=>'Jalur B', 'label'=>'Jalur B: Luas Permukaan (m2)','colname'=>'luas_permukaan_b','length'=>17,'datatype'=>'double','required'=>False,'is_key'=>false));
        $this->properties['luas_kerusakan_b'] = new Prop(array('label'=>'Jalur B: Luas Kerusakan (%)','colname'=>'luas_kerusakan_b','length'=>17,'datatype'=>'double','required'=>False,'is_key'=>false));

        $this->enum_keyval = array('kode_jalan','jalan');

        $this->childds[] = 'ruas';
        $this->childds[] = 'jalan_kondisi';
        $prog->must_authenticated = True;
        $this->browse_mode = 'table';

    }

    function go() { // called inside main content
        $this->basic_handler();

    }

}

?>