<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class link_entry extends TableManager {
    var $db_table, $properties;
    function link_entry() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('Link entry');
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'link_entry_tab';
        $this->properties['entry_id'] = new Prop(array('label'=>'Id','colname'=>'entry_id','required'=>True,'length'=>4,'insertable'=>False));
        $this->properties['name'] = new Prop(array('label'=>lang('Name'),'colname'=>'name','required'=>True,'length'=>50));
        $this->properties['description'] = new Prop(array('label'=>lang('Description'),'colname'=>'description', 'required'=>False,'length'=>150));
        $this->properties['url'] = new Prop(array('label'=>'URI','colname'=>'url', 'required'=>False,'length'=>150,'hyperlink'=>True));
        $this->properties['parent_link'] = new Prop(array('label'=>lang('Parent'),'colname'=>'parent_link', 'required'=>True, 'inputtype'=>'combobox','enumerate'=>'linkstruct'));
        $this->enum_keyval = array('entry_id','name');

        $this->unit = 'link';

    }

    function go() { # called inside main content
        $this->basic_handler();
    }

    function prepare_insert($i) {
        $seq = instantiate_module('seq_gen');
        $this->ds->entry_id[$i] = $seq->simulate_next_number('linkentry_id');
        return True;
    }

    function insert($i) {
        $seq = instantiate_module('seq_gen');
        $this->ds->entry_id[$i] = $seq->get_next_number('linkentry_id');
        parent::insert($i);
    }

    function front_list($cat_id = '') {
        if ($cat_id == '') $cat_id = $_REQUEST['pa'];
        if ($cat_id == '') $cat_id = '*';
        $this->db_where = "parent_dir = '".myaddslashes($cat_id)."'";
        $this->browse_rows = 0;
        $this->populate();
        if ($this->db_count == 0) {
            echo '<p>'.lang($this->unit).' '.lang('not available').'</p>';
            return;
        }
        $child = new dirstruct();
        for ($i = 0; $i < $this->db_count; $i++) {
            echo '<p><b><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('pa'=>$this->ds->dir_id[$i])).'">'.$this->ds->name[$i].':'.$this->ds->description[$i].'</a></b>';
            # check if this category has direct children
            $child->db_where = "parent_dir = '".myaddslashes($this->ds->dir_id[$i])."'";
            $child->populate();
            #~ print_r($child);
            for ($j = 0; $j < $child->db_count; $j++) {
                echo '<br><a href="'.get_fullpath().'">'.$child->ds->name[$j].' '.$child->ds->description[$j].'</a>';
            }
            unset($child->ds);
        }



    }

}


?>