<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

#~ include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class periode_in extends TableManager {
    var $db_table, $properties;
    function periode_in() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Periode Investasi';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'periode_in_tab';
        $this->properties['tahun'] = new Prop(array('label'=>'Tahun','colname'=>'tahun','required'=>True,'datatype'=>'int'));
        $this->properties['bulan'] = new Prop(array('label'=>'Bulan','colname'=>'bulan','required'=>True));
        $this->properties['deskripsi'] = new Prop(array('label'=>'Deskripsi','colname'=>'deskripsi', 'required'=>True,'rows'=>5));
		
        //$this->enum_keyval = array('rowid','title');

        //$this->grid_command[] = array('attach','Attach to newsletter...');
        //$this->grid_command[] = array('','-----');


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