<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class partner_rpt1 extends TableManager {
    var $db_table, $properties;
    function partner_rpt1() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Partner\'s Number of Projects';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'partner_tab';
        $this->properties['partner_id'] = new Prop(array('label'=>'ID','colname'=>'partner_id','length'=>3,'is_key'=>True));
        $this->properties['name'] = new Prop(array('label'=>'Company Name','colname'=>'name'));
        $this->properties['num'] = new Prop(array('label'=>'Number of Projects','colname'=>'count(1)', 'table'=>$GLOBALS['dbpre'].'project_tab', 'join_on'=>array('partner_id')));
        # select a.name, count(1) from partner_tab a left join project_tab b on (a.partner_id = b.partner_id) group by a.partner_id order by a.name

        $this->enum_keyval = array('partner_id','name');

        $this->browse_mode = 'table';
        $this->query_only = True;
        $this->db_groupby = $this->db_table.'.partner_id';
        #~ $this->allow_query = True;


    }

    function go() { // called inside main content
        $this->basic_handler();
    }

}

?>
