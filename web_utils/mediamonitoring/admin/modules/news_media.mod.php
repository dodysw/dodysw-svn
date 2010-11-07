<?
/* news category, support subcategory
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class news_media extends TableManager {
    var $db_table, $properties;
    function news_media() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('News media');
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'news_media_tab';
        $this->properties['category'] = new Prop(array('label'=>lang('Media'),'colname'=>'name','required'=>True,'length'=>100));
        $this->properties['description'] = new Prop(array('label'=>lang('Description'),'colname'=>'description', 'required'=>True));
        $this->enum_keyval = array('name','description');

        $this->unit = 'tone';

    }

    function go() { # called inside main content
        $this->basic_handler();
    }

}


?>