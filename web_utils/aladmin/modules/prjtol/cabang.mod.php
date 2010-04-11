<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
require_once( LOG4PHP_DIR . '/LoggerManager.php' ); //log4php

class cabang extends TableManager {
    var $db_table, $properties;

    function cabang() {
        parent::TableManager(); # must call base class
		$this->_logger =& LoggerManager::getLogger('Cabang');

        global $html_title;
        $this->title = 'Cabang';
        $html_title = $this->title;
        $this->db_table = $GLOBALS['dbpre'].'cabang_tab';

		$this->properties['kode_cabang'] = new Prop(array('label'=>'Kode Cabang','length'=>5,'colname'=>'kode_cabang', 'required'=>True, 'is_key'=>True));
		$this->properties['nama_cabang'] = new Prop(array('label'=>'Nama Cabang','colname'=>'nama_cabang','length'=>50,'required'=>True,'is_key'=>false));
		$this->properties['keterangan'] = new Prop(array('label'=>'Keterangan','colname'=>'keterangan','length'=>2000,'required'=>False,'is_key'=>false,'browse_maxchar'=>60,'inputtype'=>'textarea'));
		$this->properties['kode_propinsi'] = new Prop(array('label'=>'Kode Propinsi','length'=>2,'colname'=>'kode_propinsi', 'required'=>True, 'is_key'=>false, 'inputtype'=>'combobox','enumerate'=>'propinsi'));
		$this->properties['main'] = new Prop(array('label'=>'Main','colname'=>'main','datatype'=>'double','required'=>False,'is_key'=>false));
		$this->properties['akses'] = new Prop(array('label'=>'Akses','colname'=>'akses','datatype'=>'double','required'=>False,'is_key'=>false));
		$this->properties['status_pengelola'] = new Prop(array('label'=>'Status Pengelola','colname'=>'status_pengelola','required'=>False,'is_key'=>false, 'inputtype'=>'combobox','choices'=>array('PT Jasa Marga','Mitra Swasta')));
		$this->properties['status'] = new Prop(array('label'=>'Status','colname'=>'status','required'=>False,'is_key'=>false, 'inputtype'=>'combobox', 'enumerate'=>'status_tahapan'));
        $this->properties['status_detail'] = new Prop(array('label'=>'Status Detail','colname'=>'status_detail','required'=>False,'is_key'=>false,'datatype'=>'text','inputtype'=>'textarea'));
        $this->properties['tarif'] = new Prop(array('label'=>'Tarif','colname'=>'tarif','required'=>False, 'inputtype'=>'textarea','length'=>2000));
        $this->properties['panjang_jalan'] = new Prop(array('colname'=>'panjang_jalan','required'=>False, 'inputtype'=>'textarea','length'=>2000));
        $this->properties['tanggal_mulai_operasi'] = new Prop(array('colname'=>'tanggal_mulai_operasi','required'=>False,));
        $this->properties['sistem_operasional'] = new Prop(array('colname'=>'sistem_operasional','required'=>False));
        $this->properties['kecepatan_rencana'] = new Prop(array('colname'=>'kecepatan_rencana','required'=>False, 'inputtype'=>'textarea','length'=>2000));
        $this->properties['daerah_milik_jalan'] = new Prop(array('colname'=>'daerah_milik_jalan','required'=>False));
        $this->properties['penampang_melintang'] = new Prop(array('colname'=>'penampang_melintang','required'=>False, 'inputtype'=>'textarea','length'=>2000));
        $this->properties['jenis_perkerasan'] = new Prop(array('colname'=>'jenis_perkerasan','required'=>False));
        $this->properties['simpang_susun'] = new Prop(array('colname'=>'simpang_susun','required'=>False, 'inputtype'=>'textarea','length'=>2000));
        $this->properties['volume_lalin'] = new Prop(array('colname'=>'volume_lalin','required'=>False, 'inputtype'=>'textarea','length'=>2000));

        $this->enum_keyval = array('kode_cabang','nama_cabang');

        $this->childds[] = 'jalan';
        $this->childds[] = 'gerbang_tol';
        $prog->must_authenticated = True;
        $this->browse_mode = 'form';

    }

    function go() { // called inside main content
        $this->basic_handler();
    }


}

?>