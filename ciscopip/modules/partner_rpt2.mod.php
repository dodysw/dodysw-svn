<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class partner_rpt2 extends TableManager {
    var $db_table, $properties;
    function partner_rpt2() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Partner\'s Project Status';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'project_tab';
        $this->properties['partner_id'] = new Prop(array('label'=>'ID','colname'=>'partner_id','length'=>3,'is_key'=>True));
        $this->properties['partner_name'] = new Prop(array('label'=>'Company Name','colname'=>'name','table'=>$GLOBALS['dbpre'].'partner_tab', 'join_on'=>array('partner_id')));
        $this->properties['project_id'] = new Prop(array('label'=>'Registration Number','colname'=>'project_id', 'hyperlink'=>'hyper1'));
        $this->properties['project_name'] = new Prop(array('label'=>'Project Name','colname'=>'name'));
        $this->properties['total'] = new Prop(array('label'=>'Total Project','colname'=>'total','datatype'=>'double','required'=>True,'notes'=>'(in 000 US$)'));
        $this->properties['list_price'] = new Prop(array('box_end'=>True, 'label'=>'Cisco List Price','colname'=>'list_price','datatype'=>'double','required'=>True,'notes'=>'(in 000 US$)'));
        $this->properties['cu_name'] = new Prop(array('label'=>'Customer Name','colname'=>'cu_name'));
        $this->properties['status'] = new Prop(array('label'=>'Status','colname'=>'status'));
        $this->properties['po_stage'] = new Prop(array('no_csv'=>True, 'label'=>'Waiting for Acceptance at PO','datatype'=>'int','colname'=>'po_stage','insertable'=>False,'updatable'=>False,'hidden'=>True));
        #~ $this->properties['num'] = new Prop(array('label'=>'Number of Projects','colname'=>'count(1)', 'table'=>$GLOBALS['dbpre'].'project_tab', 'join_on'=>array('partner_id')));
        # select a.name, count(1) from partner_tab a left join project_tab b on (a.partner_id = b.partner_id) group by a.partner_id order by a.name

        $this->enum_keyval = array('partner_id','name');

        $this->browse_mode = 'table';
        $this->query_only = True;
        $this->allow_query = True;
        #~ $this->db_groupby = $this->db_table.'.partner_id';

    }

    function hyper1($i) {
        if ($_SESSION['login_param_1'] == 'po'.$this->ds->po_stage[$i]) {
            return array('url'=>$_SERVER['PHP_SELF'].'?m=project&rowid='.$this->ds->_rowid[$i]);
        }

    }

    function go() { // called inside main content
        $this->basic_handler();
    }

}

?>
