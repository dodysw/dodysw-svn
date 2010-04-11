<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class status_tahapan extends TableManager {
    var $db_table, $properties;
    function status_tahapan() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Status Tahapan';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'status_tahapan_tab';
        $this->properties['kode_status'] = new Prop(array('label'=>'Kode Status','colname'=>'kode_status','required'=>True,'length'=>30, 'is_key'=>True));
        $this->properties['status'] = new Prop(array('label'=>'Status','colname'=>'status', 'required'=>True,'length'=>55));
        $this->enum_keyval = array('kode_status','status');

    }

    function go() { # called inside main content
        #~ echo "<h3>Category</h3>";
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