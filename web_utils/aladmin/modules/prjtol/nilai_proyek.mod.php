<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class nilai_proyek extends TableManager {
    var $db_table, $properties;
    function nilai_proyek() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Nilai Proyek';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'nilai_proyek_tab';
        $this->properties['kode_proyek'] = new Prop(array('label'=>'Proyek','length'=>6,'colname'=>'kode_proyek', 'required'=>True, 'is_key'=>True,'parentkey'=>'kode_proyek'));
        $this->properties['tipe_tahapan'] = new Prop(array('label'=>'Tahapan','length'=>55,'colname'=>'tipe_tahapan', 'required'=>True, 'is_key'=>True));
		$this->properties['tahun'] = new Prop(array('label'=>'Tahun','colname'=>'tahun','updatable'=>True,'insertable'=>True,'datatype'=>'int'));
		$this->properties['nilai'] = new Prop(array('label'=>'Nilai','colname'=>'nilai','updatable'=>True,'insertable'=>True,'length'=>25));
        



        $this->enum_keyval = array('rowid','nama');

        
       // $this->grid_command[] = array('attach','Tambah Tahapan Pada Proyek');
        
        //$this->grid_command[] = array('','-----');
//		$this->childds[] = 'tahapan_kerjasama';
		$this->childds[] = 'proyek_tahapan_kerjasama';



        $prog->must_authenticated = True;

        #~ if ($_SESSION['login_level'] > 1)
            #~ $this->db_where="author='{$_SESSION['login_user']}'";

    }

    function go() { // called inside main content
        //debug($this);
		#~ echo "<h3>Ruas Jalan Tol</h3>";
        $this->basic_handler();

    }
    
    



    

}

?>