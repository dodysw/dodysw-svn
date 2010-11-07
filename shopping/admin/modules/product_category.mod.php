<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class product_category extends TableManager {
    var $db_table, $properties;
    function product_category() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('Product Category');
        $html_title = $this->title;

        #~ $this->db_table = $GLOBALS['dbpre'].'dirstruct_tab';
        $this->properties['code'] = new Prop(array('colname'=>'code','required'=>True,'length'=>8));
        $this->properties['description'] = new Prop(array('colname'=>'description', 'length'=>255));
        $this->properties['parent_id'] = new Prop(array('colname'=>'parent_id', 'cdatatype'=>'fkey', 'enumerate'=>'product_category'));
        #~ $this->properties['parent_id'] = new Prop(array('colname'=>'parent_id', 'required'=>True, 'enumerate'=>'product_category'));

        $this->properties['creation_date_time'] = new Prop(array('cdatatype'=>'creation_date_time'));
        $this->properties['created_by'] = new Prop(array('cdatatype'=>'created_by'));
        $this->properties['last_update_date_time'] = new Prop(array('cdatatype'=>'last_update_date_time'));
        $this->properties['last_updated_by'] = new Prop(array('cdatatype'=>'last_updated_by'));
        $this->properties['last_updating_process'] = new Prop(array('cdatatype'=>'last_updating_process'));

        $this->enum_keyval = array('rowid','code,description');
        $this->unit = 'product category';

        $this->childds = array('product');
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

    function front_list($cat_id = '', $link_edit = False) {
        $parent_field = 'parent_id';
        $url_key = 'pa';
        $entry_module = 'product';
        if ($cat_id == '') $cat_id = $_REQUEST[$url_key];
        $this->front_trail($cat_id);

        if ($cat_id != '') {
            $this->clear();
            $this->db_where = "rowid = '".myaddslashes($cat_id)."'";
            $this->browse_rows = 0;
            $this->populate();
            echo '<h3>'.$this->ds->code[0].'</h3>';
            echo '<p>'.$this->ds->description[0].'</p>';
        }
        $this->clear();
        $this->db_where = "$parent_field = '".myaddslashes($cat_id)."'";
        $this->db_orderby = 'code';
        $this->browse_rows = 0;
        $this->populate();

        $child = instantiate_module(get_class($this));
        $col_num = 1;
        echo '<table cellspacing=0 cellpadding=0>';
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
            #~ print_r($this->ds);
            echo '<span style="font-size:15"><b><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array($url_key=>$this->ds->_rowid[$i])).'">'.$this->ds->code[$i].'</a></b> - '.$this->ds->description[$i].'</span>';

            /*
            for level 1 (parent = '*'), we want to show 2 level of children. but for level 2>, we only want to show 1 level of children.
            */
            if ($cat_id != '*') continue;
            # check if this category has direct children
            $child->db_where = "$parent_field = '".myaddslashes($this->ds->id[$i])."'";
            $child->browse_rows = 0;
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
            echo '<input type=hidden name="sugg_field['.$parent_field.']" value="'.$cat_id.'">';
            echo '<input type=submit value="Insert new '.$this->unit.' here">';
            echo '</form>';
        }
        if ($cat_id=='*') return;
        # view entry linked to this directory
        echo '<hr>';
        $this->front_entry($cat_id,$link_edit);
    }

    function front_entry($cat_id,$link_edit=False) {
        $entry_module = 'product';
        $parent_colvar = 'product_category';
        $parent_field = 'product_category_id';
        $entry_key_field = 'product_code';
        $entry_description_field = 'product_name';

        $entry = instantiate_module($entry_module);
        $entry->db_where = "$parent_field = '".myaddslashes($cat_id)."'";
        $entry->browse_rows = 0;
        $entry->populate();
        if ($entry->db_count == 0) {
            echo '<p>No entry yet</p>';
        }
        for ($i = 0; $i < $entry->db_count; $i++) {
            #~ echo '<p><b><a href="'.get_fullpath().$entry_module.'.php?'.merge_query(array('eid'=>$entry->ds->entry_id[$i])).'">'.$entry->ds->{$entry_key_field}[$i].'</a></b>: '.$entry->ds->{$entry_description_field}[$i];
            $entry->show_item_nice($entry->ds->get_row($i));
        }

        if ($link_edit) {
            #~ echo '<p><b><a href="'.$_SERVER['PHP_SELF'].'?">Insert new '.$entry->unit.' here</a>';
            echo '<form method=POST action="'.$_SERVER['PHP_SELF'].'">';
            echo '<input type=hidden name="m" value="'.$entry->module.'">';
            echo '<input type=hidden name="act" value="new">';
            echo '<input type=hidden name="go" value="'.htmlentities($GLOBALS['full_self_url']).'">';         # url to go after successful submitation
            echo '<input type=hidden name="sugg_field['.$parent_colvar.']" value="'.$cat_id.'">';
            echo '<input type=submit value="Insert new '.$entry->unit.' here">';
            echo '</form>';
        }
    }

    function front_trail($cat_id = '') {
        $parent_field = 'parent_id';
        $url_key = 'pa';
        if ($cat_id == '') $cat_id = $_REQUEST[$url_key];
        if ($cat_id == '') $cat_id = '*';
        # strategy: trace BACK to *
        $trace = array();
        $cid = $cat_id;
        do {
            $sql = "select rowid,code,$parent_field from {$this->db_table} where rowid='{$cid}'";
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

    function fe_list_flat() {
        /* flat, tree-lie category view
        */
        $this->clear();
        $this->db_orderby = 'parent_id,code';
        $this->browse_rows = 0;
        $this->populate();
        #parse each row, to get to know the level
        $this->fe_recursive_list(0);
    }

    function fe_recursive_list($id,$lvl=0) {
        for ($i=0;$i<$this->db_count;$i++) {
            if ($this->ds->parent_id[$i] != $id) continue;
            echo str_repeat('&nbsp;&nbsp;', $lvl);
            if ($lvl) echo '-';
            echo ' <a href="product_list_cat.php?cat='.$this->ds->_rowid[$i].'">'.$this->ds->description[$i].'</a><br>';
            $this->fe_recursive_list($this->ds->_rowid[$i],$lvl+1);
        }
    }

}


?>