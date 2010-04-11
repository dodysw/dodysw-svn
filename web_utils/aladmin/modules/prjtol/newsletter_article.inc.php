<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once('dbgrid.class.php');

class newsletter_article extends TableManager {
    var $db_table, $properties;
    function newsletter_article() {
        parent::TableManager(); # must call base class

        global $html_title;
        $html_title = 'Administrative - newsletter';
        $this->title= 'Newsletter-Articles';
        $this->db_table = $GLOBALS['dbpre'].'newsletter_article_tab';
        $this->properties['newsletter_id'] = new Prop(array('hidden'=>True, 'label'=>'newsletter_id','colname'=>'newsletter_id','required'=>True,'inputtype'=>'combobox','enumerate'=>'newsletter','parentkey'=>'_rowid'));
        $this->properties['article_id'] = new Prop(array('label'=>'Article','colname'=>'article_id','inputtype'=>'combobox','enumerate'=>'article'));

    }

    function go() { // called inside main content
        $this->basic_handler();
    }


}

?>