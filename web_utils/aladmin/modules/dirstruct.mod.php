<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class dirstruct extends TableManager {
    var $db_table, $properties;
    function dirstruct() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('Directory');
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'dirstruct_tab';
        $this->properties['id'] = new Prop(array('label'=>'Id','colname'=>'id','required'=>True,'length'=>4,'insertable'=>False));
        $this->properties['name'] = new Prop(array('label'=>lang('Name'),'colname'=>'name','required'=>True,'length'=>50));
        $this->properties['description'] = new Prop(array('label'=>lang('Description'),'colname'=>'description', 'required'=>False,'length'=>150));
        #~ $this->properties['parent_dir'] = new Prop(array('label'=>lang('Parent'),'colname'=>'parent_dir', 'required'=>True, 'inputtype'=>'combobox','enumerate'=>'dirstruct'));
        $this->properties['parent_dir'] = new Prop(array('label'=>lang('Parent'),'colname'=>'parent_dir', 'required'=>True, 'enumerate'=>'dirstruct'));
        $this->enum_keyval = array('id','name');
        $this->unit = 'direktori';
    }

    function go() { # called inside main content
        $this->basic_handler();
        if ($this->action == 'browse')
            echo '<p><b><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('m'=>$this->module,'act'=>'browse_structure')).'">Insert data by navigating structure</a></b></p>';
        else
            echo '<p><a href="'.$_SERVER['PHP_SELF'].'?m='.$this->module.'">&lt; Back to module</a></p>';
    }

    function act_browse_structure($post) {
        $this->front_list($_REQUEST['pa'],True);
    }

    function prepare_insert($i) {
        $seq = instantiate_module('seq_gen');
        $this->ds->id[$i] = $seq->simulate_next_number('dir_id');
        return True;
    }

    function insert($i) {
        $seq = instantiate_module('seq_gen');
        $this->ds->id[$i] = $seq->get_next_number('dir_id');
        parent::insert($i);
    }

    function front_list($cat_id = '', $link_edit = False) {
        $parent_field = 'parent_dir';
        $url_key = 'pa';
        $entry_module = 'dir_entry';

        if ($cat_id == '') $cat_id = $_REQUEST[$url_key];
        if ($cat_id == '') $cat_id = '*';
        $this->front_trail($cat_id);
        $this->db_where = "id = '".myaddslashes($cat_id)."'";
        $this->populate();

        if ($cat_id != '*') {
            echo '<h3>'.$this->ds->name[0].'</h3>';
            echo '<p>'.$this->ds->description[0].'</p>';
        }
        $this->clear();
        $this->db_where = "$parent_field = '".myaddslashes($cat_id)."'";
        $this->db_orderby = 'name';
        $this->populate();

        $child = instantiate_module(get_class($this));
        $col_num = 2;
        echo '<table border=0 cellspacing=0 cellpadding=0>';
        for ($i = 0; $i < $this->db_count; $i++) {
            if ($i % $col_num == 0) {   # start left
                echo '<tr><td valign="top">';
            }
            elseif ($i % $col_num == $i) {  # right part
                echo '<td valign="top">';
            }
            else {  # middle part
                echo '<td valign="top">';
            }

            echo '<span style="font-size:15"><b><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array($url_key=>$this->ds->id[$i])).'">'.$this->ds->name[$i].'</a></b></span>';

            /*
            for level 1 (parent = '*'), we want to show 2 level of children. but for level 2>, we only want to show 1 level of children.
            */
            if ($cat_id != '*') continue;
            # check if this category has direct children
            $child->db_where = "$parent_field = '".myaddslashes($this->ds->id[$i])."'";
            $child->populate();
            for ($j = 0; $j < $child->db_count; $j++) {
                #~ echo '<br>- <a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('pa'=>$child->ds->id[$j])).'">'.$child->ds->name[$j].'</a>: '.$child->ds->description[$j];
                echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array($url_key=>$child->ds->id[$j])).'">'.$child->ds->name[$j].'</a>';
            }
            $child->clear();


            if ($i % $col_num == 0) {   # start left
                echo '</td>';
            }
            elseif ($i % $col_num == $i) {  # right part
                echo '</td></tr>';
            }
            else {  # middle part
                echo '</td>';
            }



        }
        echo '</table>';
        if ($link_edit) {
            echo '<form method=POST action="'.$_SERVER['PHP_SELF'].'">';
            echo '<input type=hidden name="m" value="'.$this->module.'">';
            echo '<input type=hidden name="act" value="new">';
            echo '<input type=hidden name="go" value="'.htmlentities($GLOBALS['full_self_url']).'">';         # url to go after successful submitation
            echo '<input type=hidden name="field['.$parent_field.'][0]" value="'.$cat_id.'">';
            echo '<input type=submit value="Insert new '.$this->unit.' here">';
            echo '</form>';
        }
        if ($cat_id=='*') return;
        # view entry linked to this directory
        echo '<hr>';
        $this->front_entry($cat_id,$link_edit);
    }

    function front_entry($cat_id,$link_edit=False) {
        $entry_module = 'dir_entry';
        $parent_field = 'parent_dir';

        #~ echo '<h3>Entry</h3>';
        $entry = instantiate_module($entry_module);
        $entry->db_where = "$parent_field = '".myaddslashes($cat_id)."'";
        $entry->populate();
        if ($entry->db_count == 0) {
            echo '<p>No entry yet</p>';
        }
        for ($i = 0; $i < $entry->db_count; $i++) {
            echo '<p><b><a href="'.get_fullpath().$entry_module.'.php?'.merge_query(array('eid'=>$entry->ds->entry_id[$i])).'">'.$entry->ds->name[$i].'</a></b>: '.$entry->ds->description[$i];
        }

        if ($link_edit) {
            #~ echo '<p><b><a href="'.$_SERVER['PHP_SELF'].'?">Insert new '.$entry->unit.' here</a>';
            echo '<form method=POST action="'.$_SERVER['PHP_SELF'].'">';
            echo '<input type=hidden name="m" value="'.$entry->module.'">';
            echo '<input type=hidden name="act" value="new">';
            echo '<input type=hidden name="go" value="'.htmlentities($GLOBALS['full_self_url']).'">';         # url to go after successful submitation
            echo '<input type=hidden name="field['.$parent_field.'][0]" value="'.$cat_id.'">';
            echo '<input type=submit value="Insert new '.$entry->unit.' here">';
            echo '</form>';
        }
    }

    function front_trail($cat_id = '') {
        $parent_field = 'parent_dir';
        $url_key = 'pa';
        if ($cat_id == '') $cat_id = $_REQUEST[$url_key];
        if ($cat_id == '') $cat_id = '*';
        # strategy: trace BACK to *
        $trace = array();
        $cid = $cat_id;
        do {
            $sql = "select id,name,$parent_field from {$this->db_table} where id='{$cid}'";
            $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
            if (!mysql_num_rows($res)) break;
            list($id,$name,$parent) = mysql_fetch_row($res);
            $cid = $parent;
            $trace[] = array($id,$name);
        } while ($cid != '*');
        $trace[] = array('*','Home');   # put first element
        $trace = array_reverse($trace);
        foreach ($trace as $t) {
            if ($t[0] == $cat_id) { # no anchor
                echo '<b>'.$t[1].'</b>';
                continue;
            }
            echo '<a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array($url_key=>$t[0])).'">'.$t[1].'</a>  &gt; ';
        }
    }

}


?>