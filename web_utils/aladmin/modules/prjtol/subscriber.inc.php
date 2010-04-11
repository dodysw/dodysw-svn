<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once('dbgrid.class.php');

class subscriber extends TableManager {
    var $db_table, $properties;
    function subscriber() {
        parent::TableManager(); # must call base class

        global $html_title;
        $html_title = 'Administrative - Subscriber';
        $this->db_table = $GLOBALS['dbpre'].'subscriber_tab';
        $this->properties['email'] = new Prop(array('label'=>'E-mail','colname'=>'email','required'=>True,'is_key'=>True));
        $this->properties['status'] = new Prop(array('label'=>'Status','required'=>True,'colname'=>'rowstatus','inputtype'=>'combobox','enumerate'=>array('PRELIMINARY'=>'Preliminary','SUBSCRIBED'=>'Subscribed','UNSUBSCRIBED'=>'Unsubscribed')));
        $this->properties['category'] = new Prop(array('hidden'=>True, 'required'=>True,'label'=>'Category','inputtype'=>'checkbox','enumerate'=>'category','queryable'=>True));
        $this->properties['confirmation_code'] = new Prop(array('hidden'=>True, 'label'=>'Confirm Code','colname'=>'confirmation_code','updatable'=>False,'insertable'=>False));
        $this->properties['preliminary_date'] = new Prop(array('label'=>'Preliminary Date','colname'=>'preliminary_date','updatable'=>False,'insertable'=>False));
        $this->properties['subscribed_date'] = new Prop(array('label'=>'Subscribed Date','colname'=>'subscribed_date','updatable'=>False,'insertable'=>False));
        $this->properties['unsubscribed_date'] = new Prop(array('label'=>'Unsubscribed Date','colname'=>'unsubscribed_date','updatable'=>False,'insertable'=>False));

    }

    function go() { // called inside main content
        echo "<h3>Subscriber</h3>";
        $this->basic_handler();
    }

    function prepare_insert($rowindex) {
        $this->ds->status[$rowindex] = 'SUBSCRIBED';
        return True;
    }

    function prepare_update($rowindex) {
        # populate subscriber category's value (Detail)
        $this->ds->category[$rowindex] = array();
        $sql = 'select cat_id from '.$GLOBALS['dbpre'].'subscriber_category_tab'." where email='{$this->ds->email[$rowindex]}'";
        #~ echo '<br>'.$sql;
        $res = mysql_query($sql) or die(mysql_error());
        while ($row = mysql_fetch_row($res)) {
            $this->ds->category[$rowindex][] = $row[0];
        }
        #~ print_r($_REQUEST);
        return True;
    }

    function update($rowindex) {
        #~ debug();
        # also save chosen categories (Detail)
        #~ $this->populate($this->_rowid[$rowindex], True);    # since email is key, it's not being given through _request, we must retrieve the full row
        parent::update($rowindex);
        $this->handle_detail($rowindex);
    }

    function insert($rowindex) {
        # subscriber inserted through here, will subscribe him directly
        $this->ds->preliminary_date[$rowindex] = 'Now()';
        $this->ds->subscribed_date[$rowindex] = 'Now()';
        parent::insert($rowindex);
        $this->handle_detail($rowindex);
    }

    function handle_detail($rowindex) {
        #~ echo 'handle detail';
        # delete first
        $sql = 'delete from '.$GLOBALS['dbpre'].'subscriber_category_tab'." where email='{$this->ds->email[$rowindex]}'";
        #~ echo '<br>'.$sql;
        $res = mysql_query($sql) or die(mysql_error());

        foreach ($this->ds->category[$rowindex] as $cat_id) {
            $sql = "insert into {$GLOBALS['dbpre']}subscriber_category_tab (email,cat_id) values ('{$this->ds->email[$rowindex]}','$cat_id')";
            #~ echo '<br>'.$sql;
            $res = mysql_query($sql) or die(mysql_error());
        }
    }

    function act_fe_form($post) {

        if ($_REQUEST['sub_type'] == 'unsub') {
            $this->properties['category']->required = False;  # category is not required
        }

        if (!$this->validate_rows()) # check required fields
            return False;

        $email = $_REQUEST['field']['email'][0];

        if ($_REQUEST['sub_type'] == 'sub') {   # subscription processing

            # check if this email exist
            $row = $this->get_row(array('email'=>$email));
            if ($row and $row['rowstatus'] == 'SUBSCRIBED')
                $this->error_msgs[] = '"'.$email.'" is already subscribed';

            if (count($this->error_msgs)>0) return False;

            if (!$row) {
                # generate random 32 digit token for confirmation code
                $better_token = md5(uniqid(rand(), true));  // better, difficult to guess

                # insert user to database, status preliminary, waiting for confirmation url to be tripped
                $rowid = $this->insert_row(array('email'=>$email, 'rowstatus'=>'PRELIMINARY',
                    'confirmation_code'=>$better_token,'preliminary_date'=>'Now()'));
            }
            else {
                $better_token = $row['confirmation_code'];  #reuse confirmation code
            }

            # delete its detail first
            $sql = 'delete from '.$GLOBALS['dbpre'].'subscriber_category_tab'." where email='{$email}'";
            $res = mysql_query($sql) or die(mysql_error());

            # insert details
            include_once('subscriber_category.inc.php');
            $subcat = new subscriber_category();
            foreach ($_REQUEST['field']['category'][0] as $cat_id) {
                $subcat->insert_row(array('email'=>$email,'cat_id'=>$cat_id));
            }

            $confirm_url = get_fullpath().'subscribe.php?t=sub&email='.urlencode($email).'&cc='.urlencode($better_token);

            # send confirmation email to user
            supermailer(array('from'=>$GLOBALS['mail_from'], 'to'=>$email,
                'subject'=>"Confirm your newsletters subscription", 'replyto'=>$GLOBALS['mail_replyto'],
                'body'=>"Please visit this url to confirm your subscription:\r\n\r\n$confirm_url"));

            # follow with please check your mailbox email
            # just redirect to specific page
            header($GLOBALS['redirect'].'confirm_subscribe.php');
            exit;

        }
        elseif ($_REQUEST['sub_type'] == 'unsub') {   # subscription processing

            # check if this email exist
            $row = $this->get_row(array('email'=>$email));
            if (!$row or $row['rowstatus'] != 'SUBSCRIBED') {
                $this->error_msgs[] = '"'.$email.'" is not subscribed';
            }

            if (count($this->error_msgs)>0) return False;

            # generate random 32 digit token for confirmation code
            $better_token = md5(uniqid(rand(), true));  // better, difficult to guess

            # insert user to database, status preliminary, waiting for confirmation url to be tripped
            $rowid = $this->set_row(array('email'=>$email), "confirmation_code='$better_token'");

            $confirm_url = get_fullpath().'unsubscribe.php?t=unsub&email='.urlencode($email).'&cc='.$better_token;

            # send confirmation email to user
            supermailer(array('from'=>$GLOBALS['mail_from'], 'to'=>$email,
                'subject'=>"Confirm your newsletters unsubscription", 'replyto'=>$GLOBALS['mail_replyto'],
                'body'=>"Please visit this url to confirm your unsubscription:\r\n\r\n$confirm_url"));

            # follow with please check your mailbox email
            # just redirect to specific page
            header($GLOBALS['redirect'].'confirm_unsubscribe.php');
            exit;
        }

    }

    function fe_subscribe_form() {
        /* called by frontend to view subscription form */

        $this->showerror();

        echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name=act value="fe_form">';   # contains the action (edit/new)
        echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
        echo '<input type=hidden name=go value="'.htmlentities($this->_go).'">';         # url to go after successful submitation
        echo '<input type=hidden name=sub_type value="sub">';         # marker to indicate form submitation

        echo '<p>Email:<br>';$this->input_widget("field[email][0]", $this->ds->email[0], 'email');echo '</p>';
        echo '<p>Pick newsletter category to subscribe:<br>';$this->input_widget("field[category][0]", $this->ds->category[0], 'category');echo '</p>';
        echo '<p><input type=submit value="Subscribe"></p>';
        echo '</form>';

    }

    function fe_unsubscribe_form() {
        /* called by frontend to view unsubscription form */

        $this->showerror();

        echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name=act value="fe_form">';   # contains the action (edit/new)
        echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
        echo '<input type=hidden name=go value="'.htmlentities($this->_go).'">';         # url to go after successful submitation
        echo '<input type=hidden name=sub_type value="unsub">';         # marker to indicate form submitation

        echo '<p>Email:<br>';$this->input_widget("field[email][0]", $_REQUEST['field'][email][0], 'email');echo '</p>';
        echo '<p><input type=submit value="Unsubscribe"></p>';
        echo '</form>';

    }


    function process_confirmation($sub_type,$email,$confirmation_code) {
        # check email + confirmation code memang exist
        $row = $this->get_row(array('email'=>$email,'confirmation_code'=>$confirmation_code));
        #~ print_r($row);exit;
        if (!row)
            die('invalid confirmation code. please register your subscription again.');

        if ($sub_type == 'sub') {
            if ($row['rowstatus'] == 'SUBSCRIBED') {
                die('email already subscribed');
            }
            $this->set_row(array('rowid'=>$row['rowid']), "rowstatus='SUBSCRIBED', subscribed_date=Now()");
            #~ header($GLOBALS['redirect'].'confirmed_sub.php');
            header('Location: '.get_fullpath().'confirmed_sub.php');
            exit;
        }
        elseif ($sub_type == 'unsub') {
            if ($row['rowstatus'] != 'SUBSCRIBED') {
                die('email already unsubscribed');
            }
            $this->set_row(array('rowid'=>$row['rowid']), "rowstatus='UNSUBSCRIBED', unsubscribed_date=Now()");
            #~ header($GLOBALS['redirect'].'confirmed_unsub.php');
            header('Location: '.get_fullpath().'confirmed_unsub.php');
            exit;
        }
        else
            die('invalid sub type');
    }
}

?>