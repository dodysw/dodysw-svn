<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
require_once( LOG4PHP_DIR . '/LoggerManager.php' ); //log4php

class kecelakaan_penyebab extends TableManager {
    var $db_table, $properties;

    function kecelakaan_penyebab() {
        parent::TableManager(); # must call base class
		$this->_logger =& LoggerManager::getLogger('KecelakaanPenyebab');

        global $html_title;
        $this->title = 'Kecelakaan Penyebab';
        $html_title = $this->title;
        $this->db_table = $GLOBALS['dbpre'].'kecelakaan_penyebab_tab';

		$this->properties['kode_cabang'] = new Prop(array('label'=>'Cabang','length'=>4,'colname'=>'kode_cabang', 'required'=>True, 'is_key'=>True,'inputtype'=>'combobox','enumerate'=>'cabang','parentkey'=>'kode_cabang'));
		$this->properties['kode_jalan'] = new Prop(array('label'=>'Kode jalan','length'=>4,'colname'=>'kode_jalan', 'required'=>True, 'is_key'=>True,'inputtype'=>'combobox','enumerate'=>'jalan','parentkey'=>'kode_jalan'));
		$this->properties['kode_ruas'] = new Prop(array('label'=>'Kode Ruas','length'=>4,'colname'=>'kode_ruas', 'required'=>True, 'is_key'=>True,'parentkey'=>'kode_ruas'));
		$this->properties['tahun_op'] = new Prop(array('label'=>'Tahun','colname'=>'tahun_op','required'=>True,'datatype'=>'int', 'is_key'=>True,'parentkey'=>'tahun_op'));
        $this->properties['bulan_op'] = new Prop(array('label'=>'Bulan','colname'=>'bulan_op','required'=>True, 'is_key'=>True,'parentkey'=>'bulan_op','length'=>20));
		//$this->properties['sta_awal'] = new Prop(array('label'=>'STA Awal','colname'=>'sta_awal','length'=>17,'required'=>False,'is_key'=>true,'parentkey'=>'sta_awal'));
		$this->properties['arah_ruas'] = new Prop(array('label'=>'Arah Ruas','colname'=>'arah_ruas','datatype'=>'int','required'=>False,'is_key'=>true,'parentkey'=>'arah_ruas'));
		$this->properties['jumlah'] = new Prop(array('label'=>'Jumlah','colname'=>'jumlah','datatype'=>'int','required'=>False,'is_key'=>false));
		$this->properties['kode_penyebab_kecelakaan'] = new Prop(array('label'=>'Penyebab Kecelakaan','colname'=>'kode_penyebab_kecelakaan','enumerate'=>'penyebab_kecelakaan','required'=>False,'is_key'=>false,'length'=>10));
		


    //    $this->enum_keyval = array('kode_ruas','nama');

        //$this->grid_command[] = array('attach','Attach to newsletter...');
        //$this->grid_command[] = array('','-----');


        $prog->must_authenticated = True;

        #~ if ($_SESSION['login_level'] > 1)
            #~ $this->db_where="author='{$_SESSION['login_user']}'";
        #~ $this->enum_keyval = array('rowid','nama');
        $this->browse_mode = 'form';

    }

    function go() { // called inside main content
      
        $this->basic_handler();

    }



    function insert($rowindex) {
   
        parent::insert($rowindex);
    }

    function showgrid() {

        parent::showgrid();
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