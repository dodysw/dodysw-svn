<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
import_module('project');
class project_simple extends project {
    var $db_table, $properties;
    function project_simple() {
        parent::project(); # must call base class

        $this->db_table = $GLOBALS['dbpre'].'project_tab';

        # make all hidden first
        foreach ($this->properties as $k=>$v) {
            $this->properties[$k]->hidden = True;
        }

        # then enable 3 fields
        $this->properties['project_id']->hidden = False;
        #~ $this->properties['cu_name']->hidden = False;
        #~ $this->properties['name']->hidden = False;

        $this->query_only = True;
        $this->browse_mode = 'table';
        $this->browse_mode_force = True;

        $this->final_init();

    }

}

?>
