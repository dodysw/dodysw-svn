<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class design_table_group extends TableManager {
    var $db_table, $properties;
    function design_table_group() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('DesignTableGroup');
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'aa_design_table_group_tab';
        $this->properties['group'] = new Prop(array('label'=>lang('Table Group'),'colname'=>'group','required'=>True,'length'=>20,'is_key'=>True));
        $this->properties['description'] = new Prop(array('label'=>lang('Description'),'colname'=>'description', 'required'=>True,'length'=>255,'browse_maxchar'=>50,'inputtype'=>'textarea'));
        #~ $this->enum_keyval = array('name','description');
        #~ $this->unit = 'category';
    }

    function go() { # called inside main content
        $this->basic_handler();
    }

}


?>