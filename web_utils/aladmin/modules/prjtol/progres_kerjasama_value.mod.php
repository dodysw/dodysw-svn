<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

#~ include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class progres_kerjasama_value extends TableManager {
    var $db_table, $properties;
    function progres_kerjasama_value() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Histori Progres Kerjasama';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'progres_kerjasama_value_tab';
        $this->properties['kode_tahapan'] = new Prop(array('hidden'=>false, 'label'=>'Tahapan Kerjasama','colname'=>'kode_tahapan','parentkey'=>'kode_tahapan','required'=>True,'datatype'=>'int','insertable'=>true,'enumerate'=>'tahapan_kerjasama', 'is_key'=>True));
        $this->properties['kode_proyek'] = new Prop(array('hidden'=>false, 'label'=>'Proyek','colname'=>'kode_proyek','required'=>True,'parentkey'=>'kode_proyek','datatype'=>'int','insertable'=>true,'enumerate'=>'proyek', 'is_key'=>True));
        $this->properties['kategori1'] = new Prop(array('label'=>'Kategori Utama','colname'=>'kategori1','required'=>True, 'is_key'=>True,'parentkey'=>'kategori1'));
        $this->properties['kategori2'] = new Prop(array('label'=>'Sub Kategori','colname'=>'kategori2','required'=>True, 'is_key'=>True,'parentkey'=>'kategori2'));
        $this->properties['tahun_in'] = new Prop(array('label'=>'Tahun','colname'=>'tahun','required'=>True,'datatype'=>'int','is_key'=>true));
        $this->properties['bulan_in'] = new Prop(array('label'=>'Bulan','colname'=>'bulan','required'=>True,'is_key'=>true));
        $this->properties['nilai'] = new Prop(array('label'=>'Progres','colname'=>'nilai', 'required'=>True,'datatype'=>'int'));

        $prog->must_authenticated = True;

        #~ if ($_SESSION['login_level'] > 1)
            #~ $this->db_where="author='{$_SESSION['login_user']}'";

    }

    function go() { // called inside main content
        //debug($this);
		#~ echo "<h3>Ruas Jalan Tol</h3>";
        $this->basic_handler();

    }



    function insert($rowindex) {
    //    $this->ds->create_date[$rowindex] = 'Now()';
      //  $this->ds->author[$rowindex] = $_SESSION['login_user'];
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