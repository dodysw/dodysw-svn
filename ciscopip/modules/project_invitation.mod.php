<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class project_invitation extends TableManager {
    var $db_table, $properties;
    function project_invitation() {
        parent::TableManager(); # must call base class

        $this->title = 'Scheduled Projects Invitation to PO';
        global $html_title;
        $html_title = $this->title;

        #~ $this->db_table = $GLOBALS['dbpre'].'project_invitation_tab';
        $this->properties['project_id'] = new Prop(array('label'=>'Registration Number','colname'=>'project_id','is_key'=>True,'insertable'=>False,'updatable'=>False));
        $this->properties['po1_exp_date'] = new Prop(array('label'=>'PO1 Expiry Date','colname'=>'po1_exp_date','insertable'=>False,'updatable'=>False));
        $this->properties['po2_exp_date'] = new Prop(array('label'=>'PO2 Expiry Date','colname'=>'po2_exp_date','insertable'=>False,'updatable'=>False));
        $this->properties['po_stage'] = new Prop(array('label'=>'Waiting for Acceptance at PO','colname'=>'po_stage','insertable'=>False,'updatable'=>False));
        #~ $this->browse_mode = 'table';
        #~ $this->browse_mode_forced = True;

    }

    function go() { // called inside main content
        $this->basic_handler();
    }

}

?>
