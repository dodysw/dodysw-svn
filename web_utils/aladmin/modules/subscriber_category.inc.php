<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once('dbgrid.class.php');

class subscriber_category extends TableManager {
    var $db_table, $properties;
    function subscriber_category() {
        parent::TableManager(); # must call base class

        global $html_title;
        $html_title = 'Administrative - Subscriber';
        $this->db_table = $GLOBALS['dbpre'].'subscriber_category_tab';
        $this->properties['email'] = new Prop(array('label'=>'E-mail','colname'=>'email','required'=>True));
        $this->properties['cat_id'] = new Prop(array('label'=>'Category Id','colname'=>'cat_id','inputtype'=>'checkbox',enumerate));

    }

    function go() { // called inside main content
        #~ echo "<h3>Subscriber</h3>";
        $this->basic_handler();
    }


}

?>