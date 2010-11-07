<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class jenis_organisasi extends TableManager {
    var $db_table, $properties;
    function jenis_organisasi() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('Jenis Organisasi');
        $html_title = $this->title;

        #~ $this->db_table = $GLOBALS['dbpre'].'jenis_organisasi_tab';
        $this->properties['jenis'] = new Prop(array('colname'=>'jenis','required'=>True,'length'=>100));
        $this->properties['description'] = new Prop(array('colname'=>'description'));
        $this->enum_keyval = array('rowid','jenis');

        $this->unit = 'jenis';

    }

    function go() { # called inside main content
        $this->basic_handler();
    }

}


?>