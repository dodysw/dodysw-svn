<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class proyek_tahapan_kerjasama extends TableManager {
    var $db_table, $properties;
    function proyek_tahapan_kerjasama() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Proyek Tahapan Kerjasama';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'proyek_tahapan_kerjasama_tab';
        //$this->properties['tahapan'] = new Prop(array('hidden'=>false, 'label'=>'Tahapan','colname'=>'tahapan','required'=>True,'datatype'=>'int','parentkey'=>'rowid','insertable'=>true));
        $this->properties['kode_proyek'] = new Prop(array('hidden'=>false, 'label'=>'Proyek','colname'=>'kode_proyek','required'=>True,'parentkey'=>'kode_proyek','insertable'=>true,'enumerate'=>'proyek', 'is_key'=>True,'length'=>6));
        $this->properties['kode_tahapan'] = new Prop(array('hidden'=>false, 'label'=>'Tahapan Kerjasama','colname'=>'kode_tahapan','required'=>True,'insertable'=>true,'enumerate'=>'tahapan_kerjasama', 'is_key'=>True,'inputtype'=>'combobox', 'length'=>6));
        $this->properties['kode_kerjasama'] = new Prop(array('hidden'=>false, 'label'=>'Tipe Kerjasama','colname'=>'kode_kerjasama','required'=>True,'parentkey'=>'kode_kerjasama','insertable'=>true,'enumerate'=>'tipe_kerjasama', 'is_key'=>True,'length'=>4));
        $this->properties['kategori1'] = new Prop(array('label'=>'Kategori Utama','colname'=>'kategori1','required'=>True, 'is_key'=>True,'length'=>4));
        $this->properties['kategori2'] = new Prop(array('label'=>'Sub Kategori','colname'=>'kategori2','required'=>True, 'is_key'=>True,'length'=>4));
        $this->properties['rencana'] = new Prop(array('label'=>'Rencana','colname'=>'rencana','required'=>False,'length'=>30));
        $this->properties['selesai'] = new Prop(array('label'=>'Selesai','colname'=>'selesai','required'=>false,'length'=>30));

        $this->_tahapan = $_REQUEST['tahapan'];
		 $this->baris=$_REQUEST['proyek'];
		$this->childds[] = 'progres_kerjasama';

    }

    function go() { # called inside main content
        #~ echo "<h3>Progress Kerjasama</h3>";
        $this->basic_handler();
    }


    #~ function create_sql_select() {
        #~ $this->_mapping_index = array('tahapan', 'tipe_kerjasama', 'valid_from', 'persentase');
        #~ return $sql = "select tahapan, tipe_kerjasama, valid_from, persentase from {$this->db_table} where valid_from <= '2004-04-01'";

    #~ }

    #~ function enum_list() { # return list of id/desc
        #~ $enumlist = array();
        #~ foreach ($this->get_rows('', 'cat_id,name','row') as $row) $enumlist[$row[0]] = $row[1];
        #~ return $enumlist;
    #~ }

    #~ function enum_decode($id) { # return desc of id
        #~ $row = $this->get_row(array('cat_id'=>$id), 'description');
        #~ return $row['description'];
    #~ }
	 


}

?>