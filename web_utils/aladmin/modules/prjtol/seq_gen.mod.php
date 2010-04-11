<?
/* sequence generator
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class seq_gen extends TableManager {
    var $db_table, $properties;
    function seq_gen() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Sequence Generator';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'seq_gen_tab';
        $this->properties['seq_id'] = new Prop(array('label'=>'','colname'=>'seq_id','required'=>True, 'is_key'=>True));
        $this->properties['value'] = new Prop(array('label'=>'','colname'=>'value', 'datatype'=>int));
        $this->properties['description'] = new Prop(array('label'=>'','colname'=>'description'));
        $this->properties['last_used'] = new Prop(array('label'=>'','colname'=>'last_used','datatype'=>'datetime'));
        #~ $this->final_init();    # must be call before end of initialization
    }

    function go() {// called inside main content
        $this->basic_handler();
    }

    function get_next_number($seq_id) {
        assert($seq_id != '');
        $row = $this->get_row(array('seq_id'=>$seq_id),'value');
        if ($row) {
            $value = $row['value'];
            $value += 1;
            $this->set_row(array('seq_id'=>$seq_id),"value='$value',last_used=Now()");
            return $value;
        }
        else {
            $this->insert_row(array('seq_id'=>$seq_id, 'value'=>1, 'last_used'=>'Now()', 'description'=>'Auto-Created by Framework'));
            return 1;
        }
    }


}

?>
