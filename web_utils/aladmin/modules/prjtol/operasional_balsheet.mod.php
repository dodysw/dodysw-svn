<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

#~ include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class operasional_balsheet extends TableManager {
    var $db_table, $properties;
    function operasional_balsheet() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Neraca Operasional';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'operasional_balsheet_tab';
        $this->properties['kode_cabang'] = new Prop(array('label'=>'Kode Cabang','length'=>4,'colname'=>'kode_cabang', 'required'=>True, 'is_key'=>True));
        $this->properties['account_no'] = new Prop(array('label'=>'Kode Account','length'=>4,'colname'=>'account_no', 'required'=>True, 'is_key'=>True));
        $this->properties['tahun_op'] = new Prop(array('label'=>'Tahun','colname'=>'tahun_op','required'=>True,'datatype'=>'int', 'is_key'=>True));
        $this->properties['bulan_op'] = new Prop(array('label'=>'Bulan','colname'=>'bulan_op','required'=>True, 'is_key'=>True));
        $this->properties['amount'] = new Prop(array('label'=>'Jumlah','colname'=>'amount', 'required'=>True,'rows'=>5,'datatype'=>'int'));
		
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