<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

#~ include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class progres_kerjasama extends TableManager {
    var $db_table, $properties;
    function progres_kerjasama() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Histori Progres Kerjasama';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'progres_kerjasama_tab';
        $this->properties['kode_proyek'] = new Prop(array('hidden'=>false, 'label'=>'Proyek','colname'=>'kode_proyek','required'=>True,'parentkey'=>'kode_proyek','insertable'=>true,'enumerate'=>'proyek', 'is_key'=>True,'length'=>6));
        $this->properties['kode_tahapan'] = new Prop(array('hidden'=>false, 'label'=>'Tahapan Kerjasama','colname'=>'kode_tahapan','parentkey'=>'kode_tahapan','required'=>True,'insertable'=>true,'enumerate'=>'tahapan_kerjasama', 'is_key'=>True,'length'=>6));
        $this->properties['kode_kerjasama'] = new Prop(array('hidden'=>false, 'label'=>'Tipe Kerjasama','colname'=>'kode_kerjasama','required'=>True,'parentkey'=>'kode_kerjasama','insertable'=>true,'enumerate'=>'tipe_kerjasama', 'is_key'=>True,'length'=>4));
        $this->properties['kategori1'] = new Prop(array('label'=>'Kategori Utama','colname'=>'kategori1','required'=>True, 'is_key'=>True,'parentkey'=>'kategori1','length'=>4));
        $this->properties['kategori2'] = new Prop(array('label'=>'Sub Kategori','colname'=>'kategori2','required'=>True, 'is_key'=>True,'parentkey'=>'kategori2','length'=>4));
        $this->properties['tahun_in'] = new Prop(array('label'=>'Tahun','colname'=>'tahun_in','required'=>True,'datatype'=>'int','is_key'=>true,'length'=>8));
        $this->properties['bulan_in'] = new Prop(array('label'=>'Bulan','colname'=>'bulan_in','required'=>True,'is_key'=>true,'length'=>5));
        $this->properties['nilai'] = new Prop(array('label'=>'Progres','colname'=>'nilai', 'required'=>True,'datatype'=>'int'));

        $prog->must_authenticated = True;

        #~ if ($_SESSION['login_level'] > 1)
            #~ $this->db_where="author='{$_SESSION['login_user']}'";
        $this->browse_mode = 'table';

    }

    function go() { // called inside main content
        //debug($this);
		#~ echo "<h3>Ruas Jalan Tol</h3>";
        $this->basic_handler();

    }

}

?>