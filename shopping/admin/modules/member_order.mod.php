<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class member_order extends TableManager {
    var $db_table, $properties;
    function member_order() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Member Order';
        $html_title = $this->title;

        #~ $this->db_table = $GLOBALS['dbpre'].'media_client_tab';
        $this->properties['order_code'] = new Prop(array('colname'=>'order_code', 'length'=>30, 'on_new_callback'=>'return date("Ymdhis");', 'on_insert_callback'=>'return date("Ymdhis")."-".$this->ds->membership_id[$rowindex];'));
        $this->properties['membership_id'] = new Prop(array('colname'=>'membership_id', 'required'=>True, 'cdatatype'=>'fkey', 'enumerate'=>'membership'));
        $this->properties['address1'] = new Prop(array('colname'=>'address1','required'=>True));
        $this->properties['address2'] = new Prop(array('colname'=>'address2',));
        $this->properties['zipcode'] = new Prop(array('colname'=>'zipcode','required'=>True, 'length'=>5));
        $this->properties['city'] = new Prop(array('colname'=>'city','required'=>True, 'length'=>16));
        #~ $this->properties['country'] = new Prop(array('colname'=>'country','required'=>True, 'inputtype'=>'combobox', 'enumerate'=>'membership'));
        $this->properties['contact_person'] = new Prop(array('colname'=>'contact_person','required'=>True, 'length'=>20));
        $this->properties['phone_number'] = new Prop(array('colname'=>'phone_number','required'=>True, 'length'=>12));

        $this->properties['creation_date_time'] = new Prop(array('cdatatype'=>'creation_date_time'));
        $this->properties['created_by'] = new Prop(array('cdatatype'=>'created_by'));
        $this->properties['last_update_date_time'] = new Prop(array('cdatatype'=>'last_update_date_time'));
        $this->properties['last_updated_by'] = new Prop(array('cdatatype'=>'last_updated_by'));
        $this->properties['last_updating_process'] = new Prop(array('cdatatype'=>'last_updating_process'));

        # new: keys definition. type: u=unique, i=index, f=foreignkey
        $this->db_keys = array(
            array('type'=>'u', 'fields'=>array('order_code',)),
            );

        $this->unit = 'order';
        $this->enum_keyval = array('rowid','order_code');
        $this->childds = array('member_order_items');
    }

    function go() {// called inside main content
        $this->basic_handler();
    }

    function construct_list_email($rowid) {
        # @rowid = member_order row id
        # get the order info header
        $this->clear();
        $this->populate($rowid);
        $r = $this->ds->get_row(0);

        # get the details
        $prd = instantiate_module('product');
        $moi = instantiate_module('member_order_items');
        $moi->clear();
        $moi->db_where = 'order_id='.$rowid;
        $moi->populate();
        $items = array();
        $subtotals = 0;
        for ($i=0;$i<$moi->db_count;$i++) {
            $prd->db_where = 'rowid='.$moi->ds->product_id[$i]; $prd->clear(); $prd->populate();
            $subtotal = $moi->ds->price[$i]*$moi->ds->qty[$i];
            $subtotals +=$subtotal;
            $items[] = $moi->ds->qty[$i].' x '.$prd->ds->product_code[0].' @ '.number_format($moi->ds->price[$i]).' = '.number_format($subtotal);
        }
        $items = implode("\r\n",$items);
        $subtotals = number_format($subtotals);

        $body = <<<__END__
Order Information
=================
Order Code: {$r['order_code']}
Order Date: {$r['creation_date_time']}

Member Id: {$r['membership_id']}
Address1: {$r['address1']}
Address2: {$r['address2']}
zipcode: {$r['zipcode']}
city: {$r['city']}
Contact Person: {$r['contact_person']}
Phone Number: {$r['phone_number']}

Items:
$items

Total: $subtotals

__END__;
    return $body;

    }

    function send_order_email_to_member ($rowid) {
        # get this order's member's email address to send
        $this->clear();
        $this->populate($rowid);

        $m = instantiate_module('membership');
        $m->db_where = 'rowid='.$this->ds->membership_id[0];
        $m->clear(); $m->populate();

        #~ print_r($m->ds);exit();

        $emails = array($m->ds->email[0]);

        $body = $this->construct_list_email($rowid);
        $body = <<<__END__

Thank you for ordering. Here is a copy of the order that you have placed.

$body

Your order will be processed immediately, and we will be in contact shortly.

Thank you.

--
  shopping bot
__END__;

        # send it
        include_once(APP_INCLUDE_ROOT.'/htmlMimeMail/htmlMimeMail.php');

        # construct email
        $subject = 'Your placed order receipt';
        $body = str_replace("\r\n","\n",$body); # damn BUGS on MTA!!!!
        $mail = new htmlMimeMail();
        $mail->setCrlf("\n");   # Some MTA confuse \r\n and translate it to \n\n, causing double lines. This will makes them happy:
        $mail->setText($body);

        $mail->setFrom($GLOBALS['mail_from']);
        $mail->setSubject($subject);
        $mail->setHeader('X-Mailer', (($GLOBALS['mail_xmailer'] == '')? 'aladmin': $GLOBALS['mail_xmailer']));
        $mail->setReturnPath($GLOBALS['mail_from']);
        $mail_method = 'single';
        if ($mail_method == 'bcc') {
            $mail_bcc = join(',',$emails);
            $mail->setBcc($mail_bcc);
            $result = $mail->send(array($GLOBALS['mail_to']), 'mail');

        }
        elseif ($mail_method == 'single') {
            foreach ($emails as $email) {
                $result = $mail->send(array($email), 'mail');
            }
        }
        else {
            die('Unsupported emailing method:'.$this->ds->mail_method[0]);
        }
        return True; # post must return to avoid potential html printing from non-post codes



    }

    function send_order_email ($rowid) {
        $emails = array($GLOBALS['mail_to']);
        $body = $this->construct_list_email($rowid);
        $body = <<<__END__
$body

Please follow up the order right away. Thank You.
--
  al-admin bot
__END__;

        $this->clear();$this->populate($rowid);

        $m = instantiate_module('membership');
        $m->db_where = 'rowid='.$this->ds->membership_id[0];
        $m->clear(); $m->populate();
        $email_from = $m->ds->email[0];

        # send it
        include_once(APP_INCLUDE_ROOT.'/htmlMimeMail/htmlMimeMail.php');

        # construct email
        $subject = 'Order #'.$this->ds->order_code[0].' incoming';
        $body = str_replace("\r\n","\n",$body); # damn BUGS on MTA!!!!
        $mail = new htmlMimeMail();
        $mail->setCrlf("\n");   # Some MTA confuse \r\n and translate it to \n\n, causing double lines. This will makes them happy:
        $mail->setText($body);

        $mail->setFrom($email_from);
        $mail->setSubject($subject);
        $mail->setHeader('X-Mailer', (($GLOBALS['mail_xmailer'] == '')? 'aladmin': $GLOBALS['mail_xmailer']));
        #~ $mail->setHeader('Reply-to', $GLOBALS['mail_replyto']);
        $mail->setReturnPath($GLOBALS['mail_from']);
        #~ $mail->setCc($mail_bcc);
        #$mail->setCc('Carbon Copy <cc@example.com>');
        $mail_method = 'single';
        if ($mail_method == 'bcc') {
            $mail_bcc = join(',',$emails);
            $mail->setBcc($mail_bcc);
            $result = $mail->send(array($GLOBALS['mail_to']), 'mail');

        }
        elseif ($mail_method == 'single') {
            foreach ($emails as $email) {
                $result = $mail->send(array($email), 'mail');
            }
        }
        else {
            die('Unsupported emailing method:'.$this->ds->mail_method[0]);
        }
        return True; # post must return to avoid potential html printing from non-post codes

    }

}

?>
