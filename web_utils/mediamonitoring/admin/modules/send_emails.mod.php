<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class send_emails extends TableManager {
    var $db_table, $properties;
    function send_emails() {
        parent::TableManager(); # must call base class

        global $html_title;
        $html_title = 'Send emails';
        $this->db_table = $GLOBALS['dbpre'].'send_emails_tab';
        $this->properties['from'] = new Prop(array('label'=>'From','colname'=>'from','required'=>True));
        $this->properties['to'] = new Prop(array('label'=>'To','colname'=>'to','required'=>True,'inputtype'=>'textarea','rows'=>5));
        $this->properties['subject'] = new Prop(array('label'=>'Subject','colname'=>'subject','required'=>True,'size'=>30));
        $this->properties['body'] = new Prop(array('label'=>'Body','colname'=>'body','inputtype'=>'textarea','inputtype2'=>'htmlarea','rows'=>14,'browse_maxchar'=>30));
        $this->properties['author'] = new Prop(array('label'=>'Author','colname'=>'author','updatable'=>False,'insertable'=>False));
        $this->properties['create_date'] = new Prop(array('label'=>'Creation Date','colname'=>'create_date','updatable'=>False,'insertable'=>False));
        $this->properties['last_send'] = new Prop(array('label'=>'Last Sent Date','colname'=>'last_send','updatable'=>False,'insertable'=>False));
        $this->grid_command[] = array('send_all','Send');
        $this->grid_command[] = array('','-----');
        $this->enum_keyval = array('rowid','subject');
    }

    function go() { // called inside main content
        echo "<h3>Send Emails</h3>";
        $this->basic_handler();
    }

    function insert($rowindex) {
        $this->ds->create_date[$rowindex] = 'Now()';
        $this->ds->author[$rowindex] = $_SESSION['login_user'];
        parent::insert($rowindex);
    }

    function act_send_all ($post) {
        # insert a new field
        $this->properties['sendmail_method'] = new Prop(
            array('label'=>'Sendmail method',
            'inputtype'=>'combobox',
            'choices'=>array('mail'=>'pass to smtp daemon', 'smtp'=>'send it directly myself'),
            'required'=>True
            ));
        $this->import2ds(); # properties is modified, re-import to datasource

        if ($post) {
            if (!$this->_save) return;

            if (!$this->validate_rows()) {
                return False;
            }

            # construct full email, with (optional) article as attachment
            $this->populate($_REQUEST['rowid']);
            include_once(APP_INCLUDE_ROOT.'/htmlMimeMail/htmlMimeMail.php');

            # construct email
            $mail = new htmlMimeMail();
            $mail->setCrlf("\n");   # Some MTA confuse \r\n and translate it to \n\n, causing double lines. This will makes them happy:
            $mail->setText($this->ds->body[0]);
            $mail->setFrom($this->ds->from[0]);
            $mail->setSubject($this->ds->subject[0]);
            #~ $mail->setHeader('X-Mailer', (($GLOBALS['mail_xmailer'] == '')? 'supermailer/dsw/sh/2004': $GLOBALS['mail_xmailer']));
            $mail->setHeader('Reply-to', $this->ds->from[0]);
            $mail->setReturnPath($this->ds->from[0]);

            $emails = explode(',',$this->ds->to[0]);
            echo '<p>Sending with mode: '.$this->ds->sendmail_method[0];
            echo '<pre>';
            foreach ($emails as $email) {
                set_time_limit(30);
                $email = trim($email);
                if ($email == '') continue;
                echo "From: {$this->ds->from[0]}, To:&lt;{$email}&gt;\r\n";
                $mail->setReturnPath($this->ds->from[0]);
                $result = $mail->send(array($email), $this->ds->sendmail_method[0]);
            }
            echo '</pre>';

            $this->set_row(array('rowid'=>$this->ds->_rowid[0]), "last_send=Now()");    # save sent date

            # pass messages to result page
            $this->email_target_count = count($emails);

            return; # post must return to avoid potential html printing from non-post codes
        }

        if (!$this->showerror() and $this->_save) {   # this is a successful posted result
            echo '<p>email has been sent to '.$this->email_target_count.' email(s)</p>';
            echo '<p><b><a href="'.$this->_go.'">Continue</a></b></p>';
            return;
        }

        $this->populate($this->_rowid);
        echo '<p>This will send email "'.$this->ds->subject[0].'"</p>';

        $i = 0;
        echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
        echo '<input type=hidden name=act value="'.$this->action.'">';   # contains the action (edit/new)
        echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
        echo '<input type=hidden name=go value="'.htmlentities($this->_go).'">';         # url to go after successful submitation
        echo '<input type=hidden name="rowid[]" value="'.htmlentities($this->ds->_rowid[$i]).'">';         # url to go after successful submitation
        echo '<p>';
        $this->input_widget("field[sendmail_method][$i]", $this->ds->sendmail_method[$i], 'sendmail_method');
        echo '</p>';
        echo '<p><input type=submit value=" Send "> | ';
        echo '<b><a href="" onclick="window.history.back();return false;">Cancel</a></b></p>';
        echo '</form>';
    }

}

?>