<?
/* main news
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class sub_product extends TableManager {
    var $db_table, $properties;
    function sub_product() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('Sub Product');
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'product_tab';
        $this->properties['parent_id'] = new Prop(array('colname'=>'parent_id','cdatatype'=>'fkey','enumerate'=>'product','required'=>True));
        $this->properties['product_code'] = new Prop(array('colname'=>'product_code','required'=>True, 'inputtype'=>'text', 'length'=>15));
        $this->properties['product_name'] = new Prop(array('colname'=>'product_name','required'=>True,'length'=>100));
        $this->properties['price'] = new Prop(array('colname'=>'price','cdatatype'=>'money'));
        $this->properties['priviledge_price'] = new Prop(array('colname'=>'priviledge_price','cdatatype'=>'money'));

        $this->properties['creation_date_time'] = new Prop(array('cdatatype'=>'creation_date_time'));
        $this->properties['created_by'] = new Prop(array('cdatatype'=>'created_by'));
        $this->properties['last_update_date_time'] = new Prop(array('cdatatype'=>'last_update_date_time'));
        $this->properties['last_updated_by'] = new Prop(array('cdatatype'=>'last_updated_by'));
        $this->properties['last_updating_process'] = new Prop(array('cdatatype'=>'last_updating_process'));

        $this->unit = 'subproduct';
        $this->enum_keyval = array('rowid','sub_product_code');

        $this->db_where = 'parent_id != 0';
    }

    function go() { // called inside main content
        $this->basic_handler();
    }

    function fe_list($product_id) {
        $this->db_where = 'parent_id='.$product_id;
        $this->clear();
        $this->browse_rows = 0;
        $this->populate();
        if (!$this->db_count) {
            echo 'No subproduct for this item';
            return;
        }
        #~ echo '<ul>';
        for ($i=0; $i < $this->db_count; $i++) {
            #~ echo '<li><b>'.$this->ds->product_code[$i].' - '.$this->ds->product_name[$i].'</b> - Price: Rp '.number_format($this->ds->price[$i]);
            echo '<table><tr><td>';
            echo '<b>'.$this->ds->product_code[$i].' - '.$this->ds->product_name[$i].'</b> - Price: Rp '.number_format($this->ds->price[$i]);
            if ($this->ds->priviledge_price[$i])
                echo ' - Priviledge Price: Rp '.number_format($this->ds->priviledge_price[$i]);
            echo '</td><td>';
            echo '
            <form method="GET" action="cart.php">
            <input type="hidden" name="id" value="'.$this->ds->_rowid[$i].'">
            <input type="hidden" name="act" value="add">
            <input type="submit" name="addtocart" value="Buy">
            </form>';
            echo '</td></tr></table>';
            #~ echo '</li>';
        }
        #~ echo '</ul>';
    }

}

?>