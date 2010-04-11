<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class saham_disetor extends TableManager {
    var $db_table, $properties;
    function saham_disetor() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Saham Disetor';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'saham_disetor_tab';
        $this->properties['kode_proyek'] = new Prop(array('label'=>'Proyek','length'=>6,'colname'=>'kode_proyek', 'required'=>True, 'is_key'=>True,'parentkey'=>'kode_proyek'));
		$this->properties['saham'] = new Prop(array('label'=>'Nama','colname'=>'saham','updatable'=>True,'insertable'=>True,'length'=>35));
        



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