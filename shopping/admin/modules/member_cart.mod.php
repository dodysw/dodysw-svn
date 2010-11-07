<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class member_cart extends TableManager {
    var $db_table, $properties;
    function member_cart() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Member Cart';
        $html_title = $this->title;

        #~ $this->db_table = $GLOBALS['dbpre'].'media_client_tab';
        $this->properties['membership_id'] = new Prop(array('colname'=>'membership_id','required'=>True, 'cdatatype'=>'fkey', 'enumerate'=>'membership'));
        $this->properties['product_id'] = new Prop(array('colname'=>'product_id', 'cdatatype'=>'fkey', 'enumerate'=>'product'));
        $this->properties['qty'] = new Prop(array('colname'=>'qty', 'datatype'=>'int'));

        $this->properties['creation_date_time'] = new Prop(array('cdatatype'=>'creation_date_time'));
        $this->properties['created_by'] = new Prop(array('cdatatype'=>'created_by'));
        $this->properties['last_update_date_time'] = new Prop(array('cdatatype'=>'last_update_date_time'));
        $this->properties['last_updated_by'] = new Prop(array('cdatatype'=>'last_updated_by'));
        $this->properties['last_updating_process'] = new Prop(array('cdatatype'=>'last_updating_process'));

        $this->unit = 'item';
        #~ $this->enum_keyval = array('rowid','username,email');
    }

    function go() {// called inside main content
        $this->basic_handler();
    }

    function fe_list($member_id) {
    /* view shopping cart content
    */
        $this->db_where = "membership_id=$member_id";
        $this->clear();
        $this->browse_rows = 0;
        $this->populate();
        if (!$this->db_count) {
            echo '<font face="Verdana" size="1">No item yet</font>'; return;
        }
        echo '<form method="POST">';
        echo '<input type="hidden" name="act" value="">';
        echo '<input type="hidden" name="num_row" value="'.$this->db_count.'">';
        echo '<table><tr><th><font face="Verdana" size="1">Product Code</font></th><th><font face="Verdana" size="1">Name</font></th><th><font face="Verdana" size="1">Qty</font></th><th><font face="Verdana" size="1">Price</font></th><th><font face="Verdana" size="1">Subtotal</font></th></tr>';
        $prd = instantiate_module('product');
        $subtotals = 0; $qtys = 0;
        for ($i=0; $i < $this->db_count; $i++) {
            $prd->db_where = 'rowid='.$this->ds->product_id[$i];
            $prd->clear();
            $prd->populate();
            $subtotal = $prd->ds->price[0] * $this->ds->qty[$i];
            $subtotals += $subtotal;
            $qtys += $this->ds->qty[$i];
            echo '<input type="hidden" name="rowid['.$i.']" value="'.$this->ds->_rowid[$i].'">';
            echo '<td><font face="Verdana" size="1"><a href="product_view.php?id='.$this->ds->product_id[$i].'">'.$prd->ds->product_code[0].'</font></a></td><td><font face="Verdana" size="1">'.$prd->ds->product_name[0].'</font></td><td><font face="Verdana" size="1"><input type="text" name="field[qty]['.$i.']" value="'.$this->ds->qty[$i].'" size="3"></font></td><td><font face="Verdana" size="1">'.number_format($prd->ds->price[0]).'</font></td><td><font face="Verdana" size="1">'.number_format($subtotal).'</font></td></tr>';
        }
        echo '<tr><td colspan="5"><hr></td></tr>';
        echo '<tr><td>&nbsp</td><td>&nbsp;</td><th><font face="Verdana" size="1">'.$qtys.'</font></th>'.'<td>&nbsp;</td><th><font face="Verdana" size="1">'.number_format($subtotals).'</font></th></tr>';

        echo '</table>';
        echo '<p><input type="submit" name="act_update" value="Press this after modifying quantity"><br><small>Note: set quantity to 0 to delete item</small>';
        #~ echo '<input type="submit" name="act_checkout" value="Check Out / Buy">';
        echo '</form>';
        echo '<p>Continue to <a href="checkout.php">check out order</a></font>';
    }

    function fe_list_readonly($member_id) {
    /* view shopping cart content
    */
        $this->db_where = "membership_id=$member_id";
        $this->clear();
        $this->browse_rows = 0;
        $this->populate();
        if (!$this->db_count) {
            echo 'No item yet'; return;
        }
        echo '<table><tr><th><font face="Verdana" size="1">Product Code</font></th><th><font face="Verdana" size="1">Name</font></th><th><font face="Verdana" size="1">Qty</font></th><th><font face="Verdana" size="1">Price</font></th><th><font face="Verdana" size="1">Subtotal</font></th></tr>';
        $prd = instantiate_module('product');
        $subtotals = 0; $qtys = 0;
        for ($i=0; $i < $this->db_count; $i++) {
            $prd->db_where = 'rowid='.$this->ds->product_id[$i];
            $prd->clear();
            $prd->populate();
            $subtotal = $prd->ds->price[0] * $this->ds->qty[$i];
            $subtotals += $subtotal;
            $qtys += $this->ds->qty[$i];
            echo '<td><font face="Verdana" size="1"><a href="product_view.php?id='.$this->ds->product_id[$i].'">'.$prd->ds->product_code[0].'</a></font></td><td><font face="Verdana" size="1">'.$prd->ds->product_name[0].'</font></td><td><font face="Verdana" size="1">'.$this->ds->qty[$i].'</font></td><td><font face="Verdana" size="1">'.number_format($prd->ds->price[0]).'</font></td><td><font face="Verdana" size="1">'.number_format($subtotal).'</font></td></tr>';
        }
        echo '<tr><td colspan="5"><hr></td></tr>';
        echo '<tr><td>&nbsp</td><td>&nbsp;</td><th><font face="Verdana" size="1">'.$qtys.'</font></th>'.'<td>&nbsp;</td><th><font face="Verdana" size="1">'.number_format($subtotals).'</font></th></tr>';

        echo '</table>';
    }

    function fe_add_item($member_id, $product_id) {
        /* add item into cart. item is taken from $_REQUEST['id']
        */
        # check if there's already an item in cart
        $this->db_where = "membership_id={$member_id} and product_id=$product_id";
        $this->browse_rows = 0;
        $this->clear();
        $this->populate();
        if ($this->db_count) {
            # update that row's qty + 1
            $this->ds->qty[0] += 1;
            $this->update(0);
        }
        else {
            # create a new row
            $this->clear();
            $this->ds->membership_id[] = $member_id;
            $this->ds->product_id[] = $product_id;
            $this->ds->qty[] = 1;
            $this->validate_rows(); # we just want to make sure the product is really exists
            $this->insert(0);
            $_REQUEST['id'];
        }
    }

    function fe_update($member_id) {
        /* update cart based on submitted fields */
        $this->import2ds();
        $this->db_count = $_REQUEST['num_row'];
        $this->populate($this->_rowid, True);
        for ($i = 0; $i < $this->db_count; $i++) {
            # security checking
            if ($this->ds->membership_id[$i] != $_SESSION['shop_login_id'])
                continue;
            # a 0 qty means user wants to delete the item
            if ($this->ds->qty[$i] == 0) {
                #~ echo $this->_rowid[$i];exit();
                $this->remove($i);
            }
            else
                $this->update($i);
        }
    }

    function fe_update_and_checkout($member_id) {
        assert($member_id);
        /* update then checkout */
        $this->fe_update($member_id);
        $this->fe_checkout($member_id);
    }

    function fe_checkout($member_id) {
        # create a new member order
        $mbr_order = instantiate_module('member_order');
        $mbr_order->allow_edit = False;   # bool, True to allow edit command
        $mbr_order->allow_delete = False;   # bool, True to allow delete command
        $mbr_order->allow_view = False;   # bool, True to allow view command
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            /*
            we need to save the order header, but
                1) don't want to be redirected to previous page
                2) need to find out the last inserted row id
            so I might better do it my self
            */
            $mbr_order->import2ds();
            $mbr_order->ds->membership_id[0] = $member_id;
            $order_rowid = $mbr_order->insert(0);   # i know that there's only 1 order

            # next, copy items from cart into member order's detail
            $moi = instantiate_module('member_order_items');
            $prd = instantiate_module('product');
            $moi->clear();
            $this->db_where = "membership_id=$member_id";
            $this->clear();
            $this->browse_rows = 0;
            $this->populate();

            for ($i=0; $i<$this->db_count; $i++) {
                $moi->ds->order_id[$i] = $order_rowid;
                $moi->ds->product_id[$i] = $this->ds->product_id[$i];
                $moi->ds->qty[$i] = $this->ds->qty[$i];
                $prd->populate($this->ds->product_id[$i]);
                $moi->ds->price[$i] = $prd->ds->price[0]; # record price at the time of order
            }

            for ($i=0; $i < $this->db_count; $i++) {
                $moi->insert($i);
            }

            # clean member's cart
            for ($i=0; $i<$this->db_count; $i++) {
                $this->remove($i);
            }
            #~ exit();

            # send order email to sales/admin
            $mo = instantiate_module('member_order');
            $mo->send_order_email($order_rowid);
            # send order email confirmation to user
            $mo->send_order_email_to_member($order_rowid);

            # redirect to post_order.php page
            while (@ob_end_clean());
            header('Location: post_order.php?id='.$order_rowid);
            exit();

        }
        $mbr_order->final_init();
        $mbr_order->clear();
        #~ $mbr_order->populate();
        $mbr_order->properties['membership_id']->insertable = False;
        $mbr_order->properties['order_code']->insertable = False;


        $mbr = instantiate_module('membership');
        $mbr->populate($member_id);
        $mbr_order->ds->address1[0] = $mbr->ds->address1[0];
        $mbr_order->ds->address2[0] = $mbr->ds->address2[0];
        $mbr_order->ds->zipcode[0] = $mbr->ds->zipcode[0];
        $mbr_order->ds->city[0] = $mbr->ds->city[0];
        $mbr_order->ds->contact_person[0] = $mbr->ds->contact_person[0];
        $mbr_order->ds->phone_number[0] = $mbr->ds->phone_number[0];
        $mbr_order->action = 'new';
        $mbr_order->_go = 'cart.php';
        $mbr_order->submit_label['new'] = 'Confirm';
        $mbr_order->showform();




    }

}

?>
