<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class tipe_kerjasama extends TableManager {
    var $db_table, $properties;
    function tipe_kerjasama() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Tipe Kerjasama';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'tipe_kerjasama_tab';
        $this->properties['kode_kerjasama'] = new Prop(array('label'=>'Kode Kerjasama','colname'=>'kode_kerjasama','length'=>4,'required'=>True, 'is_key'=>True));
        $this->properties['kerjasama'] = new Prop(array('label'=>'Tipe Kerjasama','colname'=>'kerjasama','required'=>True,'length'=>40));
        $this->enum_keyval = array('kode_kerjasama','kerjasama');

    }

    function go() { # called inside main content
        #~ echo "<h3>Tipe  Kerjasama</h3>";
        $this->basic_handler();
    }

    #~ function enum_list() { # return list of id/desc
        #~ $enumlist = array();
        #~ foreach ($this->get_rows('', 'cat_id,name','row') as $row) $enumlist[$row[0]] = $row[1];
        #~ return $enumlist;
    #~ }

    #~ function enum_decode($id) { # return desc of id
        #~ $row = $this->get_row(array('cat_id'=>$id), 'description');
        #~ return $row['description'];
    #~ }

}

?>