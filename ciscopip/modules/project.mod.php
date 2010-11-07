<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class project extends TableManager {
    var $db_table, $properties;
    function project() {
        parent::TableManager(); # must call base class

        $this->invitation_duration_po1 = 2 * 24 * 60 * 60;  # 2 x 24 jam
        $this->invitation_duration_po2 = 1 * 24 * 60 * 60;  # 1 x 24 jam
        #~ $this->invitation_duration_po3 = 1 * 24 * 60 * 60;  # 1 x 24 jam

        #~ $this->invitation_duration_po1 = 60;  # 2 x 24 jam
        #~ $this->invitation_duration_po2 = 60;  # 1 x 24 jam


        global $html_title;
        $this->title = 'Projects';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'project_tab';
        #~ $this->properties['partner_id'] = new Prop(array('label'=>'Partner ID','colname'=>'partner_id', 'updatable'=>False,'hidden'=>True,'enumerate'=>'partner','inputtype'=>'combobox','parentkey'=>True));
        $this->properties['partner_id'] = new Prop(array('label'=>'Partner ID','colname'=>'partner_id', 'updatable'=>False,'hidden'=>True,'enumerate'=>'partner','parentkey'=>True));
        $this->properties['project_id'] = new Prop(array('label'=>'Registration Number','colname'=>'project_id','is_key'=>True,'insertable'=>False,'updatable'=>False));

        $this->properties['cu_name'] = new Prop(array('box_start'=>'Customer Information', 'label'=>'Company Name','colname'=>'cu_name','required'=>True,'notes'=>'PT, CV ditulis di belakang', 'custom_previewhidenotes'=>True,'browse_maxchar'=>40));
        $this->properties['cu_address_1'] = new Prop(array('label'=>'Address 1','colname'=>'cu_address_1','browse_maxchar'=>40,'required'=>True));
        $this->properties['cu_address_2'] = new Prop(array('label'=>'Address 2','colname'=>'cu_address_2','browse_maxchar'=>40));
        $this->properties['cu_state'] = new Prop(array('label'=>'Province/State','colname'=>'cu_state','inputtype'=>'combobox', 'choices'=>array('IJ'=>'Irian Jaya Barat', 'PA'=>'Papua', 'DJ'=>'DKI Jakarta', 'BT'=>'Banten', 'JB'=>'Jawa Barat', 'JA'=>'Jawa Tengah', 'JT'=>'Jawa Timur', 'YG'=>'DI Yogyakarta', 'KA'=>'Kalimantan Tengah', 'KT'=>'Kalimantan Timur', 'KS'=>'Kalimantan Selatan', 'KB'=>'Kalimantan Barat', 'MK'=>'Maluku', 'MU'=>'Maluku Utara', 'BA'=>'Bali', 'NT'=>'Nusa Tenggara Timur', 'NB'=>'Nusa Tenggara Barat', 'SU'=>'Sulawesi Utara', 'SA'=>'Sulawesi Tengah', 'SG'=>'Sulawesi Tenggara', 'SS'=>'Sulawesi Selatan', 'GO'=>'Gorontalo', 'ND'=>'Nanggroe Aceh Darussalam', 'BB'=>'Bangka-Belitung', 'BK'=>'Bengkulu', 'JM'=>'Jambi', 'LM'=>'Lampung', 'ST'=>'Sumatera Utara', 'RI'=>'Riau', 'KR'=>'Kepulauan Riau', 'SL'=>'Sumatera Selatan','SB'=>'Sumatera Barat')));
        $this->properties['cu_city'] = new Prop(array('label'=>'City','colname'=>'cu_city'));
        $this->properties['cu_website'] = new Prop(array('label'=>'Website','colname'=>'cu_website'));
        $this->properties['cu_phone_1_area'] = new Prop(array('custom_isphonearea'=>True,'colspan_label'=>'Phone 1', 'colspan'=>2,'label'=>'Phone 1 (area)','colname'=>'cu_phone_1_area','required'=>True, 'length'=>4, 'enumerate'=>'kode_area', 'inputtype'=>'combobox'));
        $this->properties['cu_phone_1'] = new Prop(array('label'=>'Phone 1','colname'=>'cu_phone_1','required'=>True));
        $this->properties['cu_phone_2_area'] = new Prop(array('custom_isphonearea'=>True,'colspan_label'=>'Phone 2', 'colspan'=>2,'label'=>'Phone 2 (area)','colname'=>'cu_phone_2_area','length'=>4, 'enumerate'=>'kode_area', 'inputtype'=>'combobox'));
        $this->properties['cu_phone_2'] = new Prop(array('label'=>'Phone 2','colname'=>'cu_phone_2'));
        $this->properties['cu_fax_1_area'] = new Prop(array('custom_isphonearea'=>True,'colspan_label'=>'Fax 1', 'colspan'=>2,'label'=>'Fax 1 (area)','colname'=>'cu_fax_1_area','required'=>True, 'length'=>4, 'enumerate'=>'kode_area', 'inputtype'=>'combobox'));
        $this->properties['cu_fax_1'] = new Prop(array('label'=>'Fax 1','colname'=>'cu_fax_1','required'=>True));
        $this->properties['cu_fax_2_area'] = new Prop(array('custom_isphonearea'=>True,'colspan_label'=>'Fax 2', 'colspan'=>2,'label'=>'Fax 2 (area)','colname'=>'cu_fax_2_area','length'=>4, 'enumerate'=>'kode_area', 'inputtype'=>'combobox'));
        $this->properties['cu_fax_2'] = new Prop(array('box_end'=>True, 'label'=>'Fax 2','colname'=>'cu_fax_2'));

        $this->properties['cc1_name'] = new Prop(array('box_start'=>'Customer Contact Information','label'=>'Contact Name (Primary)','colname'=>'cc1_name','required'=>True,));
        $this->properties['cc1_title'] = new Prop(array('label'=>'Title','colname'=>'cc1_title','required'=>True,));
        $this->properties['cc1_phone_area'] = new Prop(array('custom_isphonearea'=>True, 'colspan_label'=>'Phone', 'colspan'=>3,'label'=>'Phone (area)','colname'=>'cc1_phone_area','required'=>True, 'length'=>4, 'enumerate'=>'kode_area', 'inputtype'=>'combobox'));
        $this->properties['cc1_phone'] = new Prop(array('label'=>'Phone','colname'=>'cc1_phone','required'=>True,));
        $this->properties['cc1_phone_ext'] = new Prop(array('custom_isext'=>True, 'label'=>'Phone (ext)','colname'=>'cc1_phone_ext','required'=>False, 'length'=>6));
        $this->properties['cc1_cell'] = new Prop(array('label'=>'Mobile','colname'=>'cc1_cell'));
        $this->properties['cc1_email'] = new Prop(array('box_end'=>True, 'label'=>'e-mail','colname'=>'cc1_email','required'=>True,));

        $this->properties['cc2_name'] = new Prop(array('label'=>'Contact Name (Secondary)','colname'=>'cc2_name'));
        $this->properties['cc2_title'] = new Prop(array('label'=>'Title','colname'=>'cc2_title'));
        $this->properties['cc2_phone_area'] = new Prop(array('custom_isphonearea'=>True,'colspan_label'=>'Phone', 'colspan'=>3,'label'=>'Phone (area)','colname'=>'cc2_phone_area','length'=>4, 'enumerate'=>'kode_area', 'inputtype'=>'combobox'));
        $this->properties['cc2_phone'] = new Prop(array('label'=>'Phone','colname'=>'cc2_phone'));
        $this->properties['cc2_phone_ext'] = new Prop(array('custom_isext'=>True, 'label'=>'Phone (ext)','colname'=>'cc2_phone_ext','length'=>6));
        $this->properties['cc2_cell'] = new Prop(array('label'=>'Mobile','colname'=>'cc2_cell'));
        $this->properties['cc2_email'] = new Prop(array('box_end'=>True, 'label'=>'e-mail','colname'=>'cc2_email'));

        $this->properties['name'] = new Prop(array('box_start'=>'Project Information', 'label'=>'Project Name','colname'=>'name','required'=>True));
        $this->properties['total'] = new Prop(array('label'=>'Total Project','colname'=>'total','datatype'=>'double','required'=>True,'notes'=>'(in 000 US$)'));
        $this->properties['list_price'] = new Prop(array('box_end'=>True, 'label'=>'Cisco List Price','colname'=>'list_price','datatype'=>'double','required'=>True,'notes'=>'(in 000 US$)'));
        #~ $this->properties['bom'] = new Prop(array('label'=>'Cisco Bill of Materials','colname'=>'bom','required'=>True,'notes'=>'(attach excel file)','inputtype'=>'file'));
        #~ $this->properties['netdiagram'] = new Prop(array('label'=>'Network Diagram','colname'=>'netdiagram','required'=>True,'notes'=>'(attach ppt/vsd file)','inputtype'=>'file'));

        $this->properties['pc1_name'] = new Prop(array('box_start'=>'Partner Contact Information','label'=>'Sales Name (Primary)','colname'=>'pc1_name','required'=>True,));
        $this->properties['pc1_title'] = new Prop(array('label'=>'Title','colname'=>'pc1_title','required'=>True,));
        $this->properties['pc1_phone_area'] = new Prop(array('custom_isphonearea'=>True,'colspan_label'=>'Phone', 'colspan'=>3,'label'=>'Phone (area)','colname'=>'pc1_phone_area','required'=>True, 'length'=>4, 'enumerate'=>'kode_area', 'inputtype'=>'combobox'));
        $this->properties['pc1_phone'] = new Prop(array('label'=>'Phone','colname'=>'pc1_phone','required'=>True,));
        $this->properties['pc1_phone_ext'] = new Prop(array('custom_isext'=>True, 'label'=>'Phone (ext)','colname'=>'pc1_phone_ext','required'=>False, 'length'=>6));
        $this->properties['pc1_cell'] = new Prop(array('label'=>'Mobile','colname'=>'pc1_cell','required'=>True,));
        $this->properties['pc1_email'] = new Prop(array('box_end'=>True, 'label'=>'e-mail','colname'=>'pc1_email','required'=>True,));

        $this->properties['pc2_name'] = new Prop(array('label'=>'Sales Name (Secondary)','colname'=>'pc2_name'));
        $this->properties['pc2_title'] = new Prop(array('label'=>'Title','colname'=>'pc2_title'));
        $this->properties['pc2_phone_area'] = new Prop(array('custom_isphonearea'=>True,'colspan_label'=>'Phone', 'colspan'=>3,'label'=>'Phone (area)','colname'=>'pc2_phone_area','length'=>4, 'enumerate'=>'kode_area', 'inputtype'=>'combobox'));
        $this->properties['pc2_phone'] = new Prop(array('label'=>'Phone','colname'=>'pc2_phone'));
        $this->properties['pc2_phone_ext'] = new Prop(array('custom_isext'=>True, 'label'=>'Phone (ext)','colname'=>'pc2_phone_ext', 'length'=>6));
        $this->properties['pc2_cell'] = new Prop(array('label'=>'Mobile','colname'=>'pc2_cell'));
        $this->properties['pc2_email'] = new Prop(array('box_end'=>True, 'label'=>'e-mail','colname'=>'pc2_email'));

        $this->properties['author'] = new Prop(array('label'=>'Author','colname'=>'author','insertable'=>False,'updatable'=>False,'hidden'=>True));
        $this->properties['create_date'] = new Prop(array('label'=>'Creation Date','colname'=>'create_date','datatype'=>'datetime','updatable'=>False,'insertable'=>False, ));
        $this->properties['status'] = new Prop(array('label'=>'Status','colname'=>'status','updatable'=>False,'insertable'=>False, ));  #pending, accepted
        $this->properties['po_id'] = new Prop(array('label'=>'PO ID','colname'=>'po_id','updatable'=>False,'insertable'=>False, 'hidden'=>True));

        $this->properties['po1_exp_date'] = new Prop(array('no_csv'=>True, 'label'=>'PO1 Expiry Date','datatype'=>'datetime', 'colname'=>'po1_exp_date','insertable'=>False,'updatable'=>False,'hidden'=>True));
        $this->properties['po2_exp_date'] = new Prop(array('no_csv'=>True, 'label'=>'PO2 Expiry Date','datatype'=>'datetime','colname'=>'po2_exp_date','insertable'=>False,'updatable'=>False,'hidden'=>True));
        #~ $this->properties['po3_exp_date'] = new Prop(array('no_csv'=>True, 'label'=>'PO3 Expiry Date','datatype'=>'datetime','colname'=>'po3_exp_date','insertable'=>False,'updatable'=>False,'hidden'=>True));
        $this->properties['po3_email_sent_date'] = new Prop(array('no_csv'=>True, 'label'=>'PO3 Last Email Sent Date','datatype'=>'date','colname'=>'po3_email_sent_date','insertable'=>False,'updatable'=>False,'hidden'=>True));
        #~ $this->properties['admin_hasbeen_notified'] = new Prop(array('no_csv'=>True, 'label'=>'PO3 Last Email Sent Date','datatype'=>'date','colname'=>'po3_email_sent_date','insertable'=>False,'updatable'=>False,'hidden'=>True));
        $this->properties['po_stage'] = new Prop(array('no_csv'=>True, 'label'=>'Waiting for Acceptance at PO','datatype'=>'int','colname'=>'po_stage','insertable'=>False,'updatable'=>False,'hidden'=>True));
        $this->properties['po_accept_date'] = new Prop(array('no_csv'=>True, 'label'=>'PO Accept date','datatype'=>'datetime','colname'=>'po_accept_date','insertable'=>False,'updatable'=>False,'hidden'=>True));
        $this->properties['email_cookie'] = new Prop(array('no_csv'=>True, 'colname'=>'email_cookie','insertable'=>False,'updatable'=>False,'hidden'=>True));

        $term_url = 'http://ciscopartners.interactive.web.id/tc.html';
        $this->body['new']['suffix'] = '<p><input type="checkbox" name="cb_cisco_ack" '.($_REQUEST['cb_cisco_ack']?'checked':'').'> We acknowledge acceptance of Cisco PIP\'s <a href="'.$term_url.'" onclick="window.open(\''.$term_url.'\',\'\',\'toolbar=0,location=0,directories=0,status=1,menubar=0,scrollbars=yes,resizable=yes,width=550,height=310\');return false;">terms and conditions</a>. Cisco Systems, Inc. ("Cisco") reserves the right to change these rules and regulations from time to time at its sole discretion.';
        #~ $this->body['edit']['suffix'] = $this->body['new']['suffix'];
        #~ $this->submit_label['new'] = ' Preview ';
        $this->preview['new'] = True;
        $this->preview['edit'] = True;

        $this->browse_mode = 'form';
        $this->browse_mode_forced = True;
        $this->allow_query = True;

        if ($_SESSION['login_group'] == 'PA') { # for partner group
            $this->browse_mode = 'table';
            $this->db_where = "partner_id='{$_SESSION['login_user']}'";
            $this->properties['partner_id']->updatable = False;
            $this->properties['partner_id']->insertable = False;
            #~ $this->description = 'Projects created by '.$_SESSION['login_user'];

            # only show 3 field
            foreach ($this->properties as $k=>$v) $this->properties[$k]->hidden = True; # make all hidden first
            #~ $this->properties['partner_id']->hidden = False;
            #~ $this->properties['cu_name']->hidden = False;
            $this->properties['project_id']->hidden = False;
            $this->properties['total']->hidden = False;
            $this->properties['create_date']->hidden = False;
            $this->properties['status']->hidden = False;
            #~ $this->allow_view = False;
            $this->allow_edit = False;
            $this->allow_delete = False;
            #~ $this->query_only = True;

        }
        elseif ($_SESSION['login_group'] == 'PO') { # for PO group
            #~ $this->db_where = "po_id='{$_SESSION['login_user']}'";
            $now = date("Y-m-d H:i:s");
            if ($_SESSION['login_param_1'] == 'po1')
                $this->db_where = "po_id='' and po1_exp_date > '$now'";
            elseif ($_SESSION['login_param_1'] == 'po2')
                $this->db_where = "po_id='' and po1_exp_date <= '$now' and po2_exp_date > '$now'";
            #~ elseif ($_SESSION['login_user'] == 'po3')
                #~ $this->db_where = "po_id='' and po2_exp_date <= '$now' and po3_exp_date > '$now'";
            else    # po3
                $this->db_where = "po_id='' and po2_exp_date <= '$now'";

            $this->allow_new = False;
            $this->allow_delete = False;
            $this->allow_edit = False;
            $this->properties['partner_id']->updatable = False;
            $this->properties['partner_id']->insertable = False;
            $this->properties['partner_id']->hidden = False;
            $this->properties['po_accept_date']->hidden = False;
            #~ $this->description = 'Pending projects for '.$_SESSION['login_user'];
            $this->body['browse']['suffix2'] = 'form_acceptdeny';

        }

        #~ $this->properties['po1_exp_date']->hidden = False;
        #~ $this->properties['po2_exp_date']->hidden = False;
        #~ $this->properties['po_stage']->hidden = False;
        #~ $this->properties['email_cookie']->hidden = False;

    }

    function go() { // called inside main content
        $this->basic_handler();
    }

    function shownewrecord() {
        if ($this->allow_new) {
            echo '<form method=POST action="'.$_SERVER['PHP_SELF'].'">';
            echo '<input type=hidden name="m" value="'.$this->module.'">';
            echo '<input type=hidden name="act" value="new">';
            echo '<input type=hidden name="go" value="'.htmlentities($GLOBALS['full_self_url']).'">';         # url to go after successful submitation
            echo '<br><input type=hidden name="num_row" value="1"> <input type=submit value="Register New Project"></p>';
            echo '</form>';
        }
    }

    function form_acceptdeny() {
        return <<< __EDIT__
<form method=post action="{$_SERVER['PHP_SELF']}">
<input type=hidden name=m value="{$this->module}">
<input type=hidden name=project_id value="{$this->ds->project_id[$this->_cursor]}">
<hr>
<h3>Accept/Deny this project</h3>
<table>
<tr>
    <td valign="top" nowrap><input type="radio" name="act" value="po_accept"> Accept.</td>
    <td valign="top">Appointed Cisco sales person:<br>
        <select name="appointed_po">
        <option>Adam
        <option>Agus
        <option>Debbie
        </select>
    </td>
    </tr>
<tr>
    <td valign="top"  nowrap><input type="radio" name="act" value="po_deny"> Deny.</td>
    <td>Reason:<br>
        <select name="reason">
        <option>Project has been registered by other partner
        <option>Customer is not under Commercial Territory
        <option>Non Cisco End to End Solution
        <option>List Price Below 30K
        <option>Cisco has identified the project
        <option>Project has been announced as public tender
        </select>
    </td>
    </tr>
<tr>
    <td colspan="2">Notes:<br><textarea name="notes" cols="50" rows="5"></textarea></td>
    </tr>
<tr>
    <td><p><br><br><input type="submit" value="Submit"></td>
    </tr>
</table>
</form>
__EDIT__;
    }

    function form_submit_confirm_new() {    #client side checking, on new submit
        /* echoed before enterim form, inside <script>, define form_submit_confirm javascript function behaviour in your code
        */
        return <<<__END__
        function form_submit_confirm(myform) {
            action = myform.elements['act'].value;
            if (!myform.elements['cb_cisco_ack'].checked) {
                alert('You must acknowledge the agreement');
                return false;
            }
            else {
                return true;
            }
        }
__END__;
    }

    function check_insert($i) {
        # check all phone+Ext for "invalid char" (,-/)
        $no_error = True;

        foreach ($this->properties as $colvar=>$col) {
            #~ if (strpos($colvar,'_phone') !== false or strpos($colvar,'_fax') !== false) {
            if (strpos($colvar,'_phone') === false and strpos($colvar,'_fax') === false) continue;
            #~ if (!preg_match('/[,\-\/]/',$this->ds->{$colvar}[$i])) continue;
            if (!preg_match('/[^0-9 ]/',$this->ds->{$colvar}[$i])) continue;
            $this->error_msgs[] = "[".($i+1)."] {$col->label} must be numeric only";
            #~ $this->error_msgs[] = "[".($i+1)."] {$col->label} must not contain these characters: , / -";
            $this->error_rows[$i] = True;
            $no_error = False;
        }

        # place holder function
        if ($this->ds->total[$i] == 0) {
            $this->error_msgs[] = "[".($i+1)."] Total must not zero";
            $this->error_rows[$i] = True;
            $no_error = False;
        }

        if ($this->ds->list_price[$i] == 0) {
            $this->error_msgs[] = "[".($i+1)."] List price must not zero";
            $this->error_rows[$i] = True;
            $no_error = False;
        }
        $this->ds->cu_name[$i] = strtoupper($this->ds->cu_name[$i]);

        return $no_error;
    }

    function insert($i) {
        # generate project number # XXX-YYMMDD-NNN# xxx=partner id, nnn=sequence number
        $this->ds->partner_id[$i] = $this->ds->partner_id[$i] == ''? $_SESSION['login_user']: $this->ds->partner_id[$i];
        $partner_id = strtoupper($this->ds->partner_id[$i]);    # get partner id
        $seq_gen = instantiate_module('seq_gen');
        $seq_number = $seq_gen->get_next_number('PROJECTID_'.$partner_id);
        $seq_number = sprintf('%03d', $seq_number);     #zero padded length 3
        $this->ds->project_id[$i] = $partner_id.'-'.date('ymd').'-'.$seq_number;

        # set invitation expiration date
        $nowunix = time();
        $now = date("Y-m-d H:i:s",$nowunix);
        $this->ds->create_date[$i] = $now;
        $this->ds->po1_exp_date[$i] = date("Y-m-d H:i:s",$nowunix + $this->invitation_duration_po1);
        $this->ds->po2_exp_date[$i] = date("Y-m-d H:i:s",$nowunix + $this->invitation_duration_po1 + $this->invitation_duration_po2);
        #~ $this->ds->po3_exp_date[$i] = date("Y-m-d H:i:s",$nowunix + $this->invitation_duration_po1 + $this->invitation_duration_po2 + $this->invitation_duration_po3);
        $this->ds->po_stage[$i] = 1;
        $this->ds->email_cookie[$i] = md5($nowunix);
        $this->ds->status[$i] = 'waiting for po1';

        $ret = parent::insert($i);

        # send success email to partner
        $partner = instantiate_module('partner');
        $row = $partner->get_row(array('partner_id'=>$partner_id));
        assert($row);
        $row['email'];

        # get email
        $h = array();
        $h['from'] = $GLOBALS['mail_from'];
        $h['to'] = $row['email'];
        $h['subject'] = 'Thank you for entering new project';
        $h['body'] = <<<__END__
Thank you for entering new project. Here are some details:

Registration Number: {$this->ds->project_id[$i]}
Project Name: {$this->ds->name[$i]}
Total: {$this->ds->total[$i]} (in 000 USD)
List Price: {$this->ds->list_price[$i]} (in 000 USD)

Your project will be reviewed by Cisco personnels, and we will contact you shortly.

--
 admin

__END__;
        #~ print_r($h);
        supermailer($h);

        $this->send_invitation('po1',$i);

        return $ret;
    }

    function send_notification($user,$i) {
        # send notification email to admin
        # get PO1 email address
        # @$po_num = string, po1, po2, po3
        $usrmgr = instantiate_module('usrmgr');
        $row = $usrmgr->get_row(array('username'=>$user));
        assert($row);
        $row['email'];

        $h = array();
        $h['from'] = $GLOBALS['mail_from'];
        $h['to'] = $row['email'];
        $h['subject'] = 'PO3 Has not accept new project after 7 days from partner '.$this->ds->partner_id[$i];
        $h['body'] = <<<__END__
Hello $user, PO3 has not accepted this project entered by Partner {$this->ds->partner_id[$i]}

Registration Number: {$this->ds->project_id[$i]}
Project Name: {$this->ds->name[$i]}

--
 dswbot

__END__;

        supermailer($h);


    }

    function send_invitation($po_num,$i) {
        # send download project email to PO1
        # get PO1 email address
        # @$po_num = string, po1, po2, po3
        $usrmgr = instantiate_module('usrmgr');
        #~ $row = $usrmgr->get_row(array('username'=>$po_num));
        $row = $usrmgr->get_row(array('param_1'=>$po_num));
        assert($row);
        $h = array();
        $h['from'] = $GLOBALS['mail_from'];
        $h['to'] = $row['email'];
        $h['subject'] = 'New project from partner '.$this->ds->partner_id[$i];
        $accept_url = get_fullpath().'index.php?m=project&act=accept&project_id='.$this->ds->project_id[$i].'&email_cookie='.$this->ds->email_cookie[$i];
        $exp_date = $this->ds->{$po_num.'_exp_date'}[$i];
        $exp_date = $exp_date == ''? 'This invitation to accept project will not expire.':'Invitation to accept project will expire on '.$exp_date;
        $h['body'] = <<<__END__
Hello $po_num. A new project has been entered by Partner {$this->ds->partner_id[$i]}

Registration Number: {$this->ds->project_id[$i]}
Project Name: {$this->ds->name[$i]}
Total: {$this->ds->total[$i]} (in 000 USD)
List Price: {$this->ds->list_price[$i]} (in 000 USD)

To retrieve information about this project via email, click this URL:
$accept_url

--
 admin

__END__;
        #~ print_r($h);
        supermailer($h);

        # done

    }

    function act_accept($post) {
        /* @post = bool, TRUE if I'm called with POST, before outputting anything.
        #~ http://localhost/ciscopip/adm/index.php?m=project&act=accept&rowid[]=4
        echo 'Hello!';*/
        if ($post) return;
        $project_id = $_REQUEST['project_id'];
        $email_cookie = $_REQUEST['email_cookie'];

        # check if i'm a group po
        if ($_SESSION['login_group'] != 'PO') {
            echo 'Your group id is not Project Officers ';
            return;
        }

        # check if a row exist with this spec
        $row = $this->get_row(array('project_id'=>$project_id));
        if (!$row) {
            echo 'Invalid #144';
            return;
        }

        # check expiry date
        $now = time();
        $nd = dbdatetime2unix($row['po'.$row['po_stage'].'_exp_date']); # convert to unix time

        if ($row['po_id'] != '') {  # check if project has already been accepted
            echo 'Project '.$row['project_id'].' has already been accepted by '.$row['po_id'];
            return;
        }

        if ($row['email_cookie'] != $email_cookie) {    # check email cookie
            echo 'Invalid email cookie, your invitation may has expired or check the url again.';
            return;
        }

        if ('po'.$row['po_stage'] != $_SESSION['login_param_1']) { # check authorized PO user
            echo 'Project '.$row['project_id'].' can now only be viewed by po'.$row['po_stage'];
            return;
        }

        # generate CSV representation of current row
        # - title
        $rows = array();
        $fields = array();
        foreach ($this->properties as $colvar=>$col) {
            if ($col->no_csv) continue;
            $vtemp = $this->ds->{$colvar}[$i];
            $vtemp = str_replace('"','""',$vtemp);
            $vtemp = (strpos($vtemp,',') === false and strpos($vtemp,'"') === false and strpos($vtemp,"\n") === false)? $vtemp: '"'.$vtemp.'"';
            $fields[] = (strpos($col->label,',') === false)? $col->label: '"'.$col->label.'"';
        }
        $rows[] = join(',',$fields);
        # - body
        $fields = array();
        foreach ($this->properties as $colvar=>$col) {
            if ($col->no_csv) continue;
            $vtemp = $row[$col->colname];
            $vtemp = str_replace('"','""',$vtemp);
            $vtemp = (strpos($vtemp,',') === false and strpos($vtemp,'"') === false and strpos($vtemp,"\n") === false)? $vtemp: '"'.$vtemp.'"';
            $fields[] = $vtemp;
        }

        $rows[] = join(',',$fields);
        $csv_content = join("\r\n",$rows);

        # update project status
        $this->set_row(array('rowid'=>$row['rowid']),array('status'=>'processed by '.$_SESSION['login_user']));

        # send feedback email + attach csv to po
        # - get po email
        $row_user = $this->get_row(array('username'=>$_SESSION['login_user']),'email','row','user_tab');
        assert($row_user);
        $subject = 'Information for project '.$row['project_id'];
        $to = $row_user[0];
        $body = <<<__END__

Attached is complete project {$row['project_id']} data in csv format.
--
  admin

__END__;

        include_once(APP_INCLUDE_ROOT.'/htmlMimeMail/htmlMimeMail.php');

        # construct email
        $mail = new htmlMimeMail();
        $mail->setCrlf("\n");   # Some MTA confuse \r\n and translate it to \n\n, causing double lines. This will makes them happy:
        #~ $mail->setHTML($body);
        $mail->setText($body);  # send as normal text
        #~ $mail->addAttachment($csv_content, $row['project_id'].'.csv', 'text/comma-separated-values');
        $mail->addAttachment($csv_content, $row['project_id'].'.csv');
        $mail->setFrom($GLOBALS['mail_from']);
        $mail->setSubject($subject);
        $mail->setHeader('X-Mailer', (($GLOBALS['mail_xmailer'] == '')? 'supermailer/dsw/sh/2004': $GLOBALS['mail_xmailer']));
        $mail->setHeader('Reply-to', $GLOBALS['mail_replyto']);

        #~ $mail->setHeader('To', $to);
        $mail->setReturnPath($GLOBALS['mail_from']);
        $ret = $mail->send(array($to), 'mail');

        # show feedback
        echo '<h3>Information for project '.$row['project_id'].' has been emailed to you ('.$_SESSION['login_user'].')</h3>';
        #~ echo '<p>Please check your mail, or <a href="'.get_fullpath().'index.php?m=project&rowid='.$row['rowid'].'">view this project profile</a>.';


    }

    function act_po_accept($post) {
        /* @post = bool, TRUE if I'm called with POST, before outputting anything.
        #~ http://localhost/ciscopip/adm/index.php?m=project&act=accept&rowid[]=4
        echo 'Hello!';*/
        if ($post) return;

        # get project row
        $project_id = addslashes($_REQUEST['project_id']);
        $where[] = "project_id='$project_id'";
        $where[] = "po_id=''";
        # make sure it's not expired
        $now = date("Y-m-d H:i:s");
        if ($_SESSION['login_param_1'] == 'po1' or $_SESSION['login_param_1'] == 'po2')
            $where[] = "{$_SESSION['login_user']}_exp_date > '$now'";

        else    # po3
            $where[] = "po_id='' and po2_exp_date <= '$now'";

        $row = $this->get_row(join(' and ',$where));
        if (!$row) {
            echo '<p>Project does not exist or your invitation has expired.';
            return;
        }

        # update po_id
        $this->set_row(array('rowid'=>$row['rowid']),"po_id='{$_SESSION['login_user']}', status='accepted by {$_SESSION['login_user']}', po_accept_date='".unix2dbdatetime($now)."'");

        # get again
        $row = $this->get_row(array('project_id'=>$project_id));

        # send feedback email to partner
        # - get partner id
        $row['partner_id'];
        # - get partner email
        $row_partner = $this->get_row(array('partner_id'=>$row['partner_id']), 'email', 'row', 'partner_tab');
        $h = array();
        $h['from'] = $GLOBALS['mail_from'];
        $h['to'] = $row_partner[0];
        $h['subject'] = 'Your new project has been accepted';
        $h['body'] = <<<__END__
Your new project:

Registration Number: {$row['project_id']}
Project Name: {$row['name']}
Total: {$row['total']} (in 000 USD)
List Price: {$row['list_price']} (in 000 USD)

has been accepted. Appointed Cisco sales {$_REQUEST['appointed_po']} will be in contact
very soon.

Notes:
{$_REQUEST['notes']}

--
 admin

__END__;
        supermailer($h);

        $this->delete_project_data($row['rowid']);

        # show feedback
        echo '<h3>You have accepted project '.$row['project_id'].'</h3><p>Partner has been notified to contact Cisco sales. Thank you</p>';

    }


    function act_po_deny($post) {
        /* @post = bool, TRUE if I'm called with POST, before outputting anything.
        #~ http://localhost/ciscopip/adm/index.php?m=project&act=accept&rowid[]=4
        echo 'Hello!';*/
        if ($post) return;

        # get project row
        $project_id = addslashes($_REQUEST['project_id']);
        $where[] = "project_id='$project_id'";
        $where[] = "po_id=''";
        # make sure it's not expired
        $now = date("Y-m-d H:i:s");
        if ($_SESSION['login_param_1'] == 'po1' or $_SESSION['login_param_1'] == 'po2')
            $where[] = "{$_SESSION['login_user']}_exp_date > '$now'";

        else    # po3
            $where[] = "po_id='' and po2_exp_date <= '$now'";

        $row = $this->get_row(join(' and ',$where));
        if (!$row) {
            echo '<p>Project does not exist or your invitation has expired.';
            return;
        }

        # update po_id
        $this->set_row(array('rowid'=>$row['rowid']),"po_id='{$_SESSION['login_user']}', status='denied by {$_SESSION['login_user']}', po_accept_date='".unix2dbdatetime($now)."'");

        # get again
        $row = $this->get_row(array('project_id'=>$project_id));

        # send feedback email to partner
        # - get partner id
        $row['partner_id'];
        # - get partner email
        $row_partner = $this->get_row(array('partner_id'=>$row['partner_id']), 'email', 'row', 'partner_tab');
        $h = array();
        $h['from'] = $GLOBALS['mail_from'];
        $h['to'] = $row_partner[0];
        $h['subject'] = 'Your project has been denied';
        $h['body'] = <<<__END__
Your new project:

Registration Number: {$row['project_id']}
Project Name: {$row['name']}
Total: {$row['total']} (in 000 USD)
List Price: {$row['list_price']} (in 000 USD)

has been denied. The reason is:
{$_REQUEST['reason']}

Notes:
{$_REQUEST['notes']}

--
 admin

__END__;
        supermailer($h);

        $this->delete_project_data($row['rowid']);

        # show feedback
        echo '<h3>You have denied project '.$row['project_id'].'</h3><p>Partner has been notified of the denial. Thank you</p>';

    }


    function delete_project_data($rowid) {
        # delete project fields
        $sure = True;
        if ($sure)
            /*
            cu_name='',
            author
            create_date
            status
            po_id
            po1_exp_date
            po2_exp_date
            po3_exp_date
            po_stage
            po_accept_date
            */
            $this->set_row(array('rowid'=>$rowid), "

            cu_address_1='',
            cu_address_2='',
            cu_state='',
            cu_city='',
            cu_website='',
            cu_phone_1_area='',
            cu_phone_1='',
            cu_phone_2_area='',
            cu_phone_2='',
            cu_fax_1_area='',
            cu_fax_1='',
            cu_fax_2_area='',
            cu_fax_2='',
            cc1_name='',
            cc1_title='',
            cc1_phone_area='',
            cc1_phone='',
            cc1_phone_ext='',
            cc1_cell='',
            cc1_email='',
            cc2_name='',
            cc2_title='',
            cc2_phone_area='',
            cc2_phone='',
            cc2_phone_ext='',
            cc2_cell='',
            cc2_email='',
            name='',
            total='',
            list_price='',
            pc1_name='',
            pc1_title='',
            pc1_phone_area='',
            pc1_phone='',
            pc1_phone_ext='',
            pc1_cell='',
            pc1_email='',
            pc2_name='',
            pc2_title='',
            pc2_phone_area='',
            pc2_phone='',
            pc2_phone_ext='',
            pc2_cell='',
            pc2_email='',
            email_cookie=''
            ");

    }



}

?>
