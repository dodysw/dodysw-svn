<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
require_once( LOG4PHP_DIR . '/LoggerManager.php' ); //log4php

class ruas_kerusakan extends TableManager {
    var $db_table, $properties;

    function ruas_kerusakan() {
        parent::TableManager(); # must call base class
        $this->_logger =& LoggerManager::getLogger('ruas');

        global $html_title;
        $this->title = 'Ruas';
        $html_title = $this->title;
        $this->db_table = $GLOBALS['dbpre'].'ruas_tab';

        $this->properties['kode_cabang'] = new Prop(array('label'=>'Cabang','length'=>4,'colname'=>'kode_cabang', 'required'=>True, 'input_type'=>'combobox', 'is_key'=>True,'enumerate'=>'cabang','parentkey'=>'kode_cabang'));
        # $this->properties['kode_jalan'] = new Prop(array('label'=>'Kode jalan','length'=>4,'colname'=>'kode_jalan', 'required'=>True, 'is_key'=>True,'enumerate'=>'jalan','parentkey'=>'kode_jalan'));
        $this->properties['kode_ruas'] = new Prop(array('label'=>'Kode Ruas','length'=>4,'colname'=>'kode_ruas', 'required'=>True, 'is_key'=>True));
        $this->properties['ruas'] = new Prop(array('label'=>'Ruas','colname'=>'ruas','length'=>45,'required'=>True,'is_key'=>false));
        //$this->properties['sta_awal'] = new Prop(array('label'=>'STA Awal','colname'=>'sta_awal','length'=>17,'required'=>False,'is_key'=>false));
        //$this->properties['sta_akhir'] = new Prop(array('label'=>'STA Akhir','colname'=>'sta_akhir','length'=>17,'required'=>False,'is_key'=>false));
        //$this->properties['panjang_jalan'] = new Prop(array('label'=>'Panjang Jalan','colname'=>'panjang_jalan','browse_maxchar'=>30));

        //$this->properties['sistem_operasi'] = new Prop(array('label'=>'Sistem Operasi','colname'=>'sistem_operasi','updatable'=>True,'insertable'=>True));
        //$this->properties['kecepatan_rencana'] = new Prop(array('label'=>'Kecepatan Rencana','colname'=>'kecepatan_rencana','updatable'=>True,'insertable'=>True));
        //$this->properties['daerah_milik_jalan'] = new Prop(array('label'=>'Daerah Milik Jalan','colname'=>'daerah_milik_jalan','updatable'=>True,'insertable'=>True));
        //$this->properties['penampang_melintang'] = new Prop(array('label'=>'Penampang Melintang','colname'=>'penampang_melintang','browse_maxchar'=>30));
        //$this->properties['jenis_perkerasan'] = new Prop(array('label'=>'Jenis Perkerasan','colname'=>'jenis_perkerasan','updatable'=>True,'insertable'=>True));
        //$this->properties['simpang_susun'] = new Prop(array('label'=>'Simpang Susun','colname'=>'simpang_susun','browse_maxchar'=>30));

        $this->properties['luas_permukaan_a'] = new Prop(array('label'=>'Jalur A: Luas Permukaan (m2)','colname'=>'luas_permukaan_a','length'=>17,'datatype'=>'double','required'=>False,'is_key'=>false));
        $this->properties['luas_kerusakan_a'] = new Prop(array('label'=>'Jalur A: Luas Kerusakan (%)','colname'=>'luas_kerusakan_a','length'=>17,'datatype'=>'double','required'=>False,'is_key'=>false));
        $this->properties['luas_permukaan_b'] = new Prop(array('label'=>'Jalur B: Luas Permukaan (m2)','colname'=>'luas_permukaan_b','length'=>17,'datatype'=>'double','required'=>False,'is_key'=>false));
        $this->properties['luas_kerusakan_b'] = new Prop(array('label'=>'Jalur B: Luas Kerusakan (%)','colname'=>'luas_kerusakan_b','length'=>17,'datatype'=>'double','required'=>False,'is_key'=>false));

        $this->enum_keyval = array('kode_ruas','ruas');
        $prog->must_authenticated = True;

        $this->browse_mode = 'table';

    }

    function go() { // called inside main content

        $this->basic_handler();

    }



}

?>