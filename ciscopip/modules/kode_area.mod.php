<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class kode_area extends TableManager {
    var $db_table, $properties;
    function kode_area() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Daftar Kode Area';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'kode_area_tab';
        $this->properties['kode_area'] = new Prop(array('label'=>'','colname'=>'kode_area','required'=>True, 'is_key'=>True, 'length'=>6));

        $this->enum_keyval = array('kode_area','kode_area');

        $this->final_init();    # must be call before end of initialization
    }

    function go() {// called inside main content
        $this->basic_handler();
    }


}

?>
