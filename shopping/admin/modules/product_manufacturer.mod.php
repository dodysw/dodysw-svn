<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class product_manufacturer extends TableManager {
    var $db_table, $properties;
    function product_manufacturer() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Manufacturer';
        $html_title = $this->title;

        #~ $this->db_table = $GLOBALS['dbpre'].'kategori_tab';
        $this->properties['manufacturer_code'] = new Prop(array('colname'=>'manufacturer_code','required'=>True,'length'=>3));
        $this->properties['name'] = new Prop(array('colname'=>'name', 'required'=>True,'length'=>55));
        $this->properties['description'] = new Prop(array('colname'=>'description', 'length'=>200));
        $this->properties['logo'] = new Prop(array('colname'=>'logo','cdatatype'=>'image'));

        $this->properties['creation_date_time'] = new Prop(array('cdatatype'=>'creation_date_time'));
        $this->properties['created_by'] = new Prop(array('cdatatype'=>'created_by'));
        $this->properties['last_update_date_time'] = new Prop(array('cdatatype'=>'last_update_date_time'));
        $this->properties['last_updated_by'] = new Prop(array('cdatatype'=>'last_updated_by'));
        $this->properties['last_updating_process'] = new Prop(array('cdatatype'=>'last_updating_process'));

        $this->enum_keyval = array('rowid','manufacturer_code,name');
        $this->unit = 'company';

        $this->childds = array('product');

    }

    function go() { # called inside main content
        $this->basic_handler();
    }

    function fe_search_form($modifier='') {
        echo '<form method="POST" action="manufacturer_search.php">';
        echo '<input type="text" name="keyword" value="'.$_REQUEST['keyword'].'">';
        echo '<input type=submit value="Go"></form>';
    }

    function show_search_result() {
        $kw = $_REQUEST['keyword'];

        $wheres = array();

        # get a list of column name which datatype is varchar, then decorate with proper db condition
        $searchable_fields = array();
        foreach ($this->properties as $colval=>$col)
            if ($col->datatype == 'varchar' and $col->queryable)
                $searchable_fields[] = '`'.$col->colname."` like '%$kw%'";

        $wheres[] = '('.implode(' or ', $searchable_fields).')';
        $where_str = join(' and ', $wheres);
        $sql = 'select * from '.$this->db_table." where ".$where_str;
        #~ echo $sql;

        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
        if (!mysql_num_rows($res)) {
            echo '<p align="center" ><b>No result returned from search</b></p>';
            return;
        }
        $prd = instantiate_module('product');
        $rows = array();
        while ($row = mysql_fetch_array($res)) {
            # get all products of this manufacturer
            $prd->db_where = "manufacturer_id = '".$row['rowid']."'";
            $prd->clear();
            $prd->populate();
            for ($i = 0; $i < $prd->db_count; $i++) {
                $prd->show_item_nice($prd->ds->get_row($i));
            }

            #~ $url = 'product_view.php?'.merge_query(array('id'=>$row['rowid']));
            #~ echo '<table><tr><td>';
            #~ echo '<img src="getfile.php?id='.$row['logo'].'&secure='.secure_hash($row['logo']).'">';
            #~ echo '</td><td>';
            #~ echo '<p><a href="'.$url.'">'.$row['manufacturer_code'].' - '.$row['name'].'</a>';
            #~ echo '<br>'.$row['description'];
            #~ echo '</td></tr></table>';
        }
    }

    function fe_list() {
        $this->clear();
        #~ $this->db_where = "$parent_field = '".myaddslashes($cat_id)."'";
        $this->db_orderby = 'manufacturer_code';
        $this->browse_rows = 0;
        $this->populate();
        for ($i=0; $i < $this->db_count; $i++) {
            #~ echo '<br>'.$this->ds->manufacturer_code[$i];
            #~ echo '<br><a href="product_list.php?manuf='.$this->ds->_rowid[$i].'">'.$this->ds->name[$i].' - '.$this->ds->description[$i].'</a>';
            echo '<br><a href="product_list_manuf.php?manuf='.$this->ds->_rowid[$i].'">'.$this->ds->name[$i].'</a>';
        }

    }

}

?>