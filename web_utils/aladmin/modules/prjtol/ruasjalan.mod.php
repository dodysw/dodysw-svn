<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class ruasjalan extends TableManager {
    var $db_table, $properties;
    function ruasjalan() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Ruas jalan tol';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'ruasjalan_tab';
        $this->properties['nama'] = new Prop(array('label'=>'Nama','colname'=>'nama','required'=>True));
        $this->properties['deskripsi'] = new Prop(array('label'=>'Deskripsi','colname'=>'deskripsi', 'required'=>True,'inputtype'=>'textarea','rows'=>5,'browse_maxchar'=>30));
        $this->properties['panjang_jalan'] = new Prop(array('label'=>'Panjang Jalan','colname'=>'panjang_jalan','browse_maxchar'=>30));
        //$this->properties['category'] = new Prop(array('label'=>'Category','colname'=>'cat_id','inputtype'=>'combobox','enumerate'=>'category'));

		$this->properties['sistem_operasi'] = new Prop(array('label'=>'Sistem Operasi','colname'=>'sistem_operasi','updatable'=>True,'insertable'=>True));
		$this->properties['kecepatan_rencana'] = new Prop(array('label'=>'Kecepatan Rencana','colname'=>'kecepatan_rencana','updatable'=>True,'insertable'=>True));
		$this->properties['daerah_milik_jalan'] = new Prop(array('label'=>'Daerah Milik Jalan','colname'=>'daerah_milik_jalan','updatable'=>True,'insertable'=>True));
		$this->properties['penampang_melintang'] = new Prop(array('label'=>'Penampang Melintang','colname'=>'penampang_melintang','browse_maxchar'=>30));
		$this->properties['jenis_perkerasan'] = new Prop(array('label'=>'Jenis Perkerasan','colname'=>'jenis_perkerasan','updatable'=>True,'insertable'=>True));
		$this->properties['simpang_susun'] = new Prop(array('label'=>'Simpang Susun','colname'=>'simpang_susun','browse_maxchar'=>30));
//        $this->properties['create_date'] = new Prop(array('label'=>'Creation Date','colname'=>'create_date','updatable'=>False,'insertable'=>False));

        $this->enum_keyval = array('rowid','nama');
        $this->must_authenticated = True;
        $this->browse_mode = 'form';

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