<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class category extends TableManager {
    var $db_table, $properties;
    function category() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Category';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'category_tab';
        $this->properties['category'] = new Prop(array('label'=>'Kategori','colname'=>'name','required'=>True,'length'=>100));
        $this->properties['description'] = new Prop(array('label'=>'Description','colname'=>'description', 'required'=>True));
        $this->enum_keyval = array('rowid','name');

    }

    function go() { # called inside main content
        #~ echo "<h3>Category</h3>";
        $this->basic_handler();
    }

}


?>