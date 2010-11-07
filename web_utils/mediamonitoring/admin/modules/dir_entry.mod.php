<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class dir_entry extends TableManager {
    var $db_table, $properties;
    function dir_entry() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('Directory entry');
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'dir_entry_tab';
        $this->properties['entry_id'] = new Prop(array('label'=>'Id','colname'=>'entry_id','required'=>True,'length'=>4,'insertable'=>False));
        $this->properties['name'] = new Prop(array('label'=>lang('Name'),'colname'=>'name','required'=>True,'length'=>50));
        $this->properties['description'] = new Prop(array('label'=>lang('Description'),'colname'=>'description', 'required'=>False,'length'=>150));
        $this->properties['addr_jalan'] = new Prop(array('label'=>lang('Address'),'colname'=>'addr_jalan', 'required'=>False,'length'=>150));
        $this->properties['addr_kota'] = new Prop(array('label'=>lang('City'),'colname'=>'addr_kota', 'required'=>False,'length'=>150));
        $this->properties['addr_prov'] = new Prop(array('label'=>lang('Province'),'colname'=>'addr_prov', 'required'=>False,'length'=>150));
        $this->properties['addr_zip'] = new Prop(array('label'=>lang('Zip code'),'colname'=>'addr_zip', 'required'=>False,'length'=>150));
        $this->properties['pimpinan'] = new Prop(array('label'=>lang('Head of company'),'colname'=>'pimpinan', 'required'=>False,'length'=>150));
        $this->properties['contact_person'] = new Prop(array('label'=>lang('Contact person'),'colname'=>'contact_person', 'required'=>False,'length'=>150));
        $this->properties['url'] = new Prop(array('label'=>'URI','colname'=>'url', 'required'=>False,'length'=>150,'hyperlink'=>True));
        $this->properties['parent_dir'] = new Prop(array('label'=>lang('Directory'),'colname'=>'parent_dir', 'inputtype'=>'combobox','required'=>True, 'enumerate'=>'dirstruct'));
        $this->enum_keyval = array('entry_id','name');

        $this->unit = 'entry';

    }

    function go() { # called inside main content
        $this->basic_handler();
    }

    function prepare_insert($i) {
        $seq = instantiate_module('seq_gen');
        $this->ds->dir_id[$i] = $seq->simulate_next_number('direntry_id');
        return True;
    }

    function insert($i) {
        $seq = instantiate_module('seq_gen');
        $this->ds->dir_id[$i] = $seq->get_next_number('direntry_id');
        parent::insert($i);
    }

    function front_list($cat_id = '') {
        if ($cat_id == '') $cat_id = $_REQUEST['pa'];
        if ($cat_id == '') $cat_id = '*';
        $this->db_where = "parent_dir = '".myaddslashes($cat_id)."'";
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

    function front_view($id) {
        return $this->get_row(array('entry_id'=>$id));
    }

    function front_trail($id) {
        $parent_field = 'parent_dir';
        $url_key = 'pa';
        #~ if ($cat_id == '') $cat_id = $_REQUEST[$url_key];
        #~ if ($cat_id == '') $cat_id = '*';
        # strategy: trace BACK to *
        $trace = array();

        $row = $this->get_row(array('entry_id'=>$id));
        #~ $trace[] = array($row[$parent_field],$row['name']);   # put first element
        $trace[] = array('',$row['name']);   # put first element
        $cid = $row[$parent_field];
        do {
            $sql = "select id,name,$parent_field from {$GLOBALS['dbpre']}dirstruct_tab where id='{$cid}'";
            $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
            if (!mysql_num_rows($res)) break;
            list($id,$name,$parent) = mysql_fetch_row($res);
            $cid = $parent;
            $trace[] = array($id,$name);
        } while ($cid != '*');
        $trace[] = array('*','Home');   # put first element
        $trace = array_reverse($trace);
        foreach ($trace as $t) {
            if ($t[0] == '') { # no anchor
                echo '<b>'.$t[1].'</b>';
                continue;
            }
            echo '<a href="directory.php?'.merge_query(array($url_key=>$t[0])).'">'.$t[1].'</a>  &gt; ';
        }
    }

}


?>