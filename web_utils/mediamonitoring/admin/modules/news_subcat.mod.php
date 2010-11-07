<?
/* sub category (see news_cat)
 copyright 2004,2005 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class news_subcat extends TableManager {
    var $db_table, $properties;
    function news_subcat() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('News subcategory');
        $html_title = $this->title;

        #~ $this->db_table = $GLOBALS['dbpre'].'news_subcat_tab';
        $this->properties['category'] = new Prop(array('label'=>lang('Category'),'colname'=>'name','required'=>True,'length'=>100));
        $this->properties['description'] = new Prop(array('label'=>lang('Description'),'colname'=>'description', 'required'=>True));
        $this->properties['parent_category'] = new Prop(array('label'=>lang('Parent Category'),'colname'=>'parent_category','enumerate'=>'news_cat','inputtype'=>'combobox','notes'=>'Empty to not have parent'));
        $this->enum_keyval = array('name','description');

        $this->unit = 'category';

    }

    function go() { # called inside main content
        $this->basic_handler();
    }

}


?>