<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class design_table extends TableManager {
    var $db_table, $properties;
    function design_table() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('DesignTable');
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'aa_design_table_tab';
        $this->properties['name'] = new Prop(array('label'=>lang('Table Name'),'colname'=>'name','required'=>True,'length'=>30));
        $this->properties['group'] = new Prop(array('label'=>lang('Table Group'),'colname'=>'group','required'=>True,'length'=>20));
        $this->properties['description'] = new Prop(array('label'=>lang('Description'),'colname'=>'description', 'required'=>True,'length'=>255));
        #~ $this->enum_keyval = array('name','description');
        #~ $this->unit = 'category';

    }

    function go() { # called inside main content
        $this->basic_handler();
    }

}


?>