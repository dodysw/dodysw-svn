<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
require_once( LOG4PHP_DIR . '/LoggerManager.php' ); //log4php

class volume_lalulintas_harian extends TableManager {
    var $db_table, $properties;

    function volume_lalulintas_harian() {
        parent::TableManager(); # must call base class
		$this->_logger =& LoggerManager::getLogger('volume_lalulintas_harian');

        global $html_title;
        $this->title = 'Volume Lalu Lintas Harian';
        $html_title = $this->title;
        $this->db_table = $GLOBALS['dbpre'].'volume_lalulintas_harian_tab';

		$this->properties['kode_cabang'] = new Prop(array('label'=>'Cabang','length'=>5,'colname'=>'kode_cabang', 'required'=>True, 'is_key'=>True,'inputtype'=>'combobox','enumerate'=>'cabang'));
		$this->properties['tahun_op'] = new Prop(array('label'=>'Tahun','colname'=>'tahun_op','required'=>True,'datatype'=>'int', 'is_key'=>True));
        $this->properties['bulan_op'] = new Prop(array('label'=>'Bulan','colname'=>'bulan_op','required'=>True, 'is_key'=>True));
        $this->properties['kode_gerbang_asal'] = new Prop(array('label'=>'Gerbang Asal','length'=>4,'colname'=>'kode_gerbang_asal', 'required'=>True, 'is_key'=>True));
        $this->properties['kode_gerbang_tujuan'] = new Prop(array('label'=>'Gerbang Tujuan','length'=>4,'colname'=>'kode_gerbang_tujuan', 'required'=>True, 'is_key'=>True));
        $this->properties['gol_I'] = new Prop(array('label'=>'Golongan I','colname'=>'gol_I','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['gol_IIA'] = new Prop(array('label'=>'Golongan IIA','colname'=>'gol_IIA','datatype'=>'int','required'=>False,'is_key'=>false));
        $this->properties['gol_IIB'] = new Prop(array('label'=>'Golongan IIA','colname'=>'gol_IIB','datatype'=>'int','required'=>False,'is_key'=>false));

        $prog->must_authenticated = True;
        $this->browse_mode = 'form';
    }

    function go() { // called inside main content
        $this->basic_handler();
    }

}

?>