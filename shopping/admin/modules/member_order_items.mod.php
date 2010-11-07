<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class member_order_items extends TableManager {
    var $db_table, $properties;
    function member_order_items() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Member Order Items';
        $html_title = $this->title;

        #~ $this->db_table = $GLOBALS['dbpre'].'media_client_tab';
        $this->properties['order_id'] = new Prop(array('colname'=>'order_id', 'cdatatype'=>'fkey', 'enumerate'=>'member_order'));
        $this->properties['product_id'] = new Prop(array('colname'=>'product_id', 'cdatatype'=>'fkey', 'enumerate'=>'product'));
        $this->properties['qty'] = new Prop(array('colname'=>'qty', 'datatype'=>'int'));
        $this->properties['price'] = new Prop(array('colname'=>'price', 'cdatatype'=>'money'));

        $this->properties['creation_date_time'] = new Prop(array('cdatatype'=>'creation_date_time'));
        $this->properties['created_by'] = new Prop(array('cdatatype'=>'created_by'));
        $this->properties['last_update_date_time'] = new Prop(array('cdatatype'=>'last_update_date_time'));
        $this->properties['last_updated_by'] = new Prop(array('cdatatype'=>'last_updated_by'));
        $this->properties['last_updating_process'] = new Prop(array('cdatatype'=>'last_updating_process'));

        $this->unit = 'order';
        $this->enum_keyval = array('rowid','order_code');
    }

    function go() {// called inside main content
        $this->basic_handler();
    }


}

?>
