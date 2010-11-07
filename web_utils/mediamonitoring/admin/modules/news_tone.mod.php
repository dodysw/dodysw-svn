<?
/* news category, support subcategory
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class news_tone extends TableManager {
    var $db_table, $properties;
    function news_tone() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('News tone');
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'news_tone_tab';
        $this->properties['category'] = new Prop(array('label'=>lang('Tone'),'colname'=>'name','required'=>True,'length'=>100));
        $this->properties['description'] = new Prop(array('label'=>lang('Description'),'colname'=>'description', 'required'=>True));
        $this->enum_keyval = array('name','description');

        $this->unit = 'tone';

    }

    function go() { # called inside main content
        $this->basic_handler();
    }

}


?>