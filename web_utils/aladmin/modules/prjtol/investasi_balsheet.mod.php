<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

#~ include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class investasi_balsheet extends TableManager {
    var $db_table, $properties;
    function investasi_balsheet() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Neraca Investasi';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'investasi_balsheet_tab';
        $this->properties['kode_proyek'] = new Prop(array('label'=>'Kode Proyek','length'=>6,'colname'=>'kode_proyek', 'required'=>True, 'is_key'=>True,'parentkey'=>'kode_proyek','inputtype'=>'combobox','enumerate'=>'proyek'));
        $this->properties['account_no'] = new Prop(array('label'=>'Kode Account','length'=>4,'colname'=>'account_no', 'required'=>True, 'is_key'=>True,'inputtype'=>'combobox','enumerate'=>'account'));
        $this->properties['tahun_in'] = new Prop(array('label'=>'Tahun','colname'=>'tahun_in','required'=>True,'datatype'=>'int', 'is_key'=>True));
        #~ $this->properties['bulan_in'] = new Prop(array('label'=>'Bulan','colname'=>'bulan_in','required'=>True, 'is_key'=>False));
        $this->properties['amount'] = new Prop(array('label'=>'Jumlah','colname'=>'amount', 'required'=>True,'rows'=>5,'datatype'=>'int'));

        $prog->must_authenticated = True;

    }

    function go() { // called inside main content
        $this->basic_handler();
    }

}

?>