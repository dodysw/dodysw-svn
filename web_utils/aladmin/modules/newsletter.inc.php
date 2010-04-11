<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once('dbgrid.class.php');

class newsletter extends TableManager {
    var $db_table, $properties;
    function newsletter() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Newsletter';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'newsletter_tab';
        $this->properties['subject'] = new Prop(array('label'=>'Subject','colname'=>'subject','required'=>True));
        $this->properties['body'] = new Prop(array('label'=>'Header','colname'=>'body','inputtype'=>'textarea','inputtype2'=>'htmlarea','rows'=>14,'browse_maxchar'=>30));
        $this->properties['footer'] = new Prop(array('label'=>'Footer','colname'=>'footer','inputtype'=>'textarea','inputtype2'=>'htmlarea','rows'=>8,'browse_maxchar'=>30));
        #~ $this->properties['article'] = new Prop(array('label'=>'Attach article','colname'=>'article_id','inputtype'=>'checkbox','enumerate'=>'article'));
        $this->properties['author'] = new Prop(array('label'=>'Author','colname'=>'author','updatable'=>False,'insertable'=>False));
        $this->properties['create_date'] = new Prop(array('label'=>'Creation Date','colname'=>'create_date','updatable'=>False,'insertable'=>False));
        $this->properties['last_send'] = new Prop(array('label'=>'Last Sent Date','colname'=>'last_send','updatable'=>False,'insertable'=>False));

        $this->grid_command[] = array('send_all','Send to all subscribers');
        $this->grid_command[] = array('','-----');

        #~ $this->mail_method = 'single';    # bcc, single
        $this->childds[] = 'newsletter_article';
        #~ $this->childds[] = 'newsletter_article';
        $this->enum_keyval = array('rowid','subject');


    }

    function go() { // called inside main content
        #~ echo "<h3>Newsletter</h3>";
        $this->basic_handler();
    }


    function insert($rowindex) {
        $this->ds->create_date[$rowindex] = 'Now()';
        $this->ds->author[$rowindex] = $_SESSION['login_user'];
        parent::insert($rowindex);
    }

    function act_send_all ($post) {
        # insert a new field
        $this->properties['category'] = new Prop(array('hidden'=>True, 'required'=>True, 'label'=>'Category','inputtype'=>'checkbox','enumerate'=>'category'));
        $this->properties['mail_method'] = new Prop(array('label'=>'Send method','required'=>True,'inputtype'=>'combobox','enumerate'=>array('bcc'=>'Bcc header','single'=>'One-by-one')));
        #~ echo 'POST IS'.$post;
        $this->import2ds(); # properties is modified, re-import to datasource

        #~ debug();

        if ($post) {
            if (!$this->_save) return;

            if (!$this->validate_rows()) {
                return False;
            }

            # get the list of emails subscribing to given category(s)
            $cats = array();
            foreach ($this->ds->category[0] as $cat) $cats[] = "'$cat'";
            $cats = join(',',$cats);
            $sql = "select distinct(a.email) from {$GLOBALS['dbpre']}subscriber_tab a, {$GLOBALS['dbpre']}subscriber_category_tab b where a.email = b.email and a.rowstatus='SUBSCRIBED' and b.cat_id in ($cats)";
            #~ echo $sql;exit;
            $res = mysql_query($sql) or die(mysql_error()); #do to database
            if (!mysql_num_rows($res)) {
                $this->error_msgs[] = "No subscriber subscribed to selected category";
                return;
            }
            $rows = array();
            while ($row = mysql_fetch_row($res)) {
                $rows[] = $row[0];
            }
            $emails = $rows;

            # check that the email is really subscribed

            # construct full email, with (optional) article as attachment
            $this->populate($_REQUEST['rowid']);

            # retrieve attached article
            $body = '';
            include_once('article.inc.php');
            include_once('newsletter_article.inc.php');
            $art = new article();
            $na = new newsletter_article();
            $rows = $na->get_rows(array('newsletter_id'=>$this->ds->_rowid[0]),'article_id','row');
            foreach ($rows as $row) {
                $article_id = $row[0];
                #~ echo '<br>'.$article_id;
                $body .= $art->construct_list_email($article_id);
            }

            $body = $this->ds->body[0].$body.$this->ds->footer[0];
            #~ echo $body;
            #~ exit;

            # send it
            #~ $this->mail_method = $this->ds->mail_method;
            include_once('lib/htmlMimeMail.php');

            # construct email
            $mail = new htmlMimeMail();
            $mail->setCrlf("\n");   # Some MTA confuse \r\n and translate it to \n\n, causing double lines. This will makes them happy:
            $mail->setHTML($body);
            $mail->setFrom($GLOBALS['mail_from']);
            $mail->setSubject($this->ds->subject[0]);
            $mail->setHeader('X-Mailer', (($GLOBALS['mail_xmailer'] == '')? 'supermailer/dsw/sh/2004': $GLOBALS['mail_xmailer']));
            $mail->setHeader('Reply-to', $GLOBALS['mail_replyto']);
            $mail->setReturnPath($GLOBALS['mail_from']);
            $mail->setBcc($mail_bcc);
            #~ $mail->setCc($mail_bcc);
            #$mail->setCc('Carbon Copy <cc@example.com>');

            if ($this->ds->mail_method[0] == 'bcc') {
                $mail_bcc = join(',',$emails);
                $result = $mail->send(array($GLOBALS['mail_to']), 'mail');

            }
            elseif ($this->ds->mail_method[0] == 'single') {
                foreach ($emails as $email) {
                    $result = $mail->send(array($email), 'mail');
                }
            }
            else {
                die('Unsupported emailing method:'.$this->ds->mail_method[0]);
            }

            $this->set_row(array('rowid'=>$this->ds->_rowid[0]), "last_send=Now()");    # save sent date

            # pass messages to result page
            $this->email_target_count = count($emails);

            return; # post must return to avoid potential html printing from non-post codes
        }

        if (!$this->showerror() and $this->_save) {   # this is a successful posted result
            echo '<p>1 newsletter(s) has been sent to '.$this->email_target_count.' subscriber email(s) using "'.$this->ds->mail_method[0].'" send method</p>';
            echo '<p><b><a href="'.$this->_go.'">Continue</a></b></p>';
            return;
        }

        $this->populate($this->_rowid);
        echo '<p>This will send newsletter "'.$this->ds->subject[0].'" to members subscribed to selected category(s):</p>';

        $i = 0;
        echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
        echo '<input type=hidden name=act value="'.$this->action.'">';   # contains the action (edit/new)
        echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
        echo '<input type=hidden name=go value="'.htmlentities($this->_go).'">';         # url to go after successful submitation
        echo '<input type=hidden name="rowid[]" value="'.htmlentities($this->ds->_rowid[$i]).'">';         # url to go after successful submitation
        echo '<p>';

        $this->input_widget("field[category][$i]", $this->ds->category[$i], 'category');
        echo '<p>Send method:<br>';$this->input_widget("field[mail_method][$i]", $this->ds->mail_method[$i], 'mail_method');

        echo '</p>';
        echo '<p><input type=submit value=" Send "> | ';
        echo '<b><a href="" onclick="window.history.back();return false;">Cancel</a></b></p>';
        echo '</form>';
    }

    #~ function enum_list() {
        #~ $enumlist = array();
        #~ foreach ($this->get_rows('', 'rowid,subject','row') as $row) $enumlist[$row[0]] = $row[1];
        #~ return $enumlist;
    #~ }

    #~ function enum_decode($id) { # return desc of id
        #~ $row = $this->get_row(array('rowid'=>$id), 'subject','row');
        #~ return $row[0];
    #~ }

}

?>
