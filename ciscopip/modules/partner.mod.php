<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class partner extends TableManager {
    var $db_table, $properties;
    function partner() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Partner Profile';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'partner_tab';
        $this->properties['partner_id'] = new Prop(array('box_start'=>'Login Name', 'label'=>'ID','colname'=>'partner_id','required'=>True,'length'=>3,'is_key'=>True));
        $this->properties['password_old'] = new Prop(array('label'=>'Current Password','inputtype'=>'password','required'=>True,'hidden'=>True, 'length'=>50, 'insertable'=>False, 'updatable'=>False));
        $this->properties['password'] = new Prop(array('label'=>'Password','colname'=>'password', 'inputtype'=>'password','required'=>True,'hidden'=>True, 'length'=>50));
        $this->properties['password_confirm'] = new Prop(array('label'=>'Confirm Password','inputtype'=>'password','required'=>True,'hidden'=>True, 'length'=>50));

        $this->properties['name'] = new Prop(array('box_start'=>'Company Profile', 'label'=>'Company Name','colname'=>'name','required'=>True,'notes'=>'PT, CV ditulis di belakang'));
        $this->properties['email'] = new Prop(array('label'=>'Email','colname'=>'email','length'=>100,'browse_maxchar'=>25,'required'=>True));
        $this->properties['address_1'] = new Prop(array('label'=>'Address 1','colname'=>'address_1','browse_maxchar'=>30,'required'=>True));
        $this->properties['address_2'] = new Prop(array('label'=>'Address 2','colname'=>'address_2','browse_maxchar'=>30));
        $this->properties['city'] = new Prop(array('label'=>'City','colname'=>'city'));
        $this->properties['state'] = new Prop(array('label'=>'Province/State','colname'=>'state'));
        $this->properties['website'] = new Prop(array('label'=>'Website','colname'=>'website'));
        $this->properties['phone_1_area'] = new Prop(array('colspan_label'=>'Phone 1', 'colspan'=>2,'label'=>'Phone 1 (area)','colname'=>'phone_1_area','required'=>True,'length'=>4, 'enumerate'=>'kode_area', 'inputtype'=>'combobox'));
        $this->properties['phone_1'] = new Prop(array('label'=>'Phone 1','colname'=>'phone_1','required'=>True));
        $this->properties['phone_2_area'] = new Prop(array('colspan_label'=>'Phone 2', 'colspan'=>2,'label'=>'Phone 2 (area)','colname'=>'phone_2_area','length'=>4, 'enumerate'=>'kode_area', 'inputtype'=>'combobox'));
        $this->properties['phone_2'] = new Prop(array('label'=>'Phone 2','colname'=>'phone_2'));

        $this->properties['fax_1_area'] = new Prop(array('colspan_label'=>'Fax 1', 'colspan'=>2,'label'=>'Fax 1 (area)','colname'=>'fax_1_area','required'=>True,'length'=>4, 'enumerate'=>'kode_area', 'inputtype'=>'combobox'));
        $this->properties['fax_1'] = new Prop(array('label'=>'Fax 1','colname'=>'fax_1','required'=>True));
        $this->properties['fax_2_area'] = new Prop(array('colspan_label'=>'Fax 2', 'colspan'=>2,'label'=>'Fax 2 (area)','colname'=>'fax_2_area','length'=>4, 'enumerate'=>'kode_area', 'inputtype'=>'combobox'));
        $this->properties['fax_2'] = new Prop(array('label'=>'Fax 2','colname'=>'fax_2'));

        $this->properties['contact_person'] = new Prop(array('box_start'=>'Contact Person','label'=>'Name','colname'=>'contact_person','required'=>True));
        $this->properties['mobile_phone'] = new Prop(array('label'=>'','colname'=>'mobile_phone','required'=>True));

        #~ $this->properties['con1_name'] = new Prop(array('label'=>'Contact Name','colname'=>'con1_name'));
        #~ $this->properties['con1_title'] = new Prop(array('label'=>'Title','colname'=>'con1_title'));
        #~ $this->properties['con1_phone'] = new Prop(array('label'=>'Phone','colname'=>'con1_phone'));
        #~ $this->properties['con1_cell'] = new Prop(array('label'=>'Mobile','colname'=>'con1_cell'));
        #~ $this->properties['con1_email'] = new Prop(array('label'=>'e-mail','colname'=>'con1_email'));

        #~ $this->properties['con2_name'] = new Prop(array('label'=>'Contact Name','colname'=>'con2_name'));
        #~ $this->properties['con2_title'] = new Prop(array('label'=>'Title','colname'=>'con2_title'));
        #~ $this->properties['con2_phone'] = new Prop(array('label'=>'Phone','colname'=>'con2_phone'));
        #~ $this->properties['con2_cell'] = new Prop(array('label'=>'Mobile','colname'=>'con2_cell'));
        #~ $this->properties['con2_email'] = new Prop(array('label'=>'e-mail','colname'=>'con2_email'));

        $this->properties['operator'] = new Prop(array('hidden'=>True, 'colname'=>'author','updatable'=>False,'insertable'=>False));
        $this->properties['create_date'] = new Prop(array('hidden'=>True, 'colname'=>'create_date','updatable'=>False,'insertable'=>False));

        #~ $this->grid_command[] = array('send_all','Send to all subscribers');
        #~ $this->grid_command[] = array('','-----');

        $this->enum_keyval = array('partner_id','name');

        $this->browse_mode = 'form';
        $this->browse_mode_forced = True;

        if ($_SESSION['login_group'] == 'PA') { # for partner group
            $this->allow_new = False;
            $this->allow_delete = False;
            $this->db_where = "partner_id='{$_SESSION['login_user']}'";
            $this->childds[] = 'project_simple';
            # only password is updatable
            # - make all non updatable
            foreach ($this->properties as $k=>$v) {
                $this->properties[$k]->updatable = False;
            }
            # - updatable passwords
            $this->properties['partner_id']->updatable = True;  # only as a reference
            $this->properties['password']->updatable = True;
            $this->properties['password']->label = 'New Password';
            $this->properties['password_confirm']->updatable = True;
            $this->properties['password_old']->updatable = True;

            #~ $this->body['browse']['suffix'] = '<p><a href="'.$_SERVER['PHP_SELF'].'?m=partner_simple&act=edit&go='.urlencode($GLOBALS['full_self_url']).'">Change Password</a>';
            $this->body['browse']['suffix'] = '<p><form>
            <input type=hidden name=m value="partner_simple">
            <input type=hidden name=act value="edit">
            <input type=hidden name=go value="'.htmlentities($GLOBALS['full_self_url']).'">
            <input type=submit value="Change Password">
            </form>';


        }



    }

    function go() { // called inside main content
        $this->basic_handler();
    }

    function prepare_update($i) {
        # prefill confirm password
        #~ $this->ds->password_confirm[$i] = $this->ds->password[$i];
        $this->ds->password_confirm[$i] = '';
        $this->ds->password[$i] = '';
        return True;
    }

    function check_insert($i) {
        $no_error = True;
        foreach ($this->properties as $colvar=>$col) {
            #~ if (strpos($colvar,'_phone') !== false or strpos($colvar,'_fax') !== false) {
            if (strpos($colvar,'phone') === false and strpos($colvar,'fax_') === false) continue;
            #~ if (!preg_match('/[,\-\/]/',$this->ds->{$colvar}[$i])) continue;
            if (!preg_match('/[^0-9 ]/',$this->ds->{$colvar}[$i])) continue;
            $this->error_msgs[] = "[".($i+1)."] {$col->label} must be numeric only";
            $this->error_rows[$i] = True;
            $no_error = False;
        }

        # make sure password matches confirm
        if ($this->ds->password[$i] != $this->ds->password_confirm[$i]) {
            $this->error_msgs[] = "[".($i+1)."] Password confirmation does not match. Please check your entry.";
            $this->error_rows[$i] = True;
            $no_error = False;
        }
        # partner id must 3 character
        if (strlen($this->ds->partner_id[$i]) != 3) {
            $this->error_msgs[] = "[".($i+1)."] Partner ID length must exactly 3 characters.";
            $this->error_rows[$i] = True;
            $no_error = False;
        }
        return $no_error;
    }

    function check_update($i) {
        # get current password
        $row = $this->get_row(array('username'=>$_SESSION['login_user']),'*','array','user_tab');

        if ($this->properties['password_old']->updatable) {
            # compare with old password
            if (md5($this->ds->password_old[$i]) != $row['password']) {
                $this->error_msgs[] = "[".($i+1)."] Current password is incorrect.";
                $this->error_rows[$i] = True;
                return False;
            }
        }

        # make sure password matches confirm
        if ($this->ds->password[$i] != $this->ds->password_confirm[$i]) {
            $this->error_msgs[] = "[".($i+1)."] Password confirmation does not match. Please check your entry.";
            $this->error_rows[$i] = True;
            return False;
        }
        return True;
    }

    function insert($i) {
        $this->ds->password[$i] = md5($this->ds->password[$i]);
        parent::insert($i);

        # insert here will also insert new row at usrmgr
        $usrmgr = instantiate_module('usrmgr');
        $usrmgr->remove_row(array('username'=>$this->ds->partner_id[$i]));   # delete first with same user
        $usrmgr->insert_row(array(
            'username'=>$this->ds->partner_id[$i],
            'password'=>$this->ds->password[$i],
            'level'=>2,
            'group'=>'PA',
            ));

        # also delete/reset project sequence (if same partner has alrady been entered previously)
        # format: PROJECTID_pa1
        $seq = instantiate_module('seq_gen');
        $seq->remove_row(array('seq_id'=>'PROJECTID_'.$this->ds->partner_id[$i]));

    }

    function update($rowindex) {
        # wee need to check whether the given pass is the same as the database, this is a trick for md5-style password entry
        $row = $this->get_row(array('rowid'=>$this->ds->_rowid[$rowindex]));
        if ($row['password'] != $this->ds->password[$rowindex]) {   # password changed!
            $this->ds->password[$rowindex] = md5($this->ds->password[$rowindex]);   # then md5 it
        }

        if ($_SESSION['login_group'] == 'PA') {
            # set/force rowid to this value
            $this->ds->_rowid[$rowindex] = $row['rowid'];
        }

        parent::update($rowindex);

        # update here will also update row at usrmgr
        $usrmgr = instantiate_module('usrmgr');
        $usrmgr->remove_row(array('username'=>$this->ds->partner_id[$rowindex]));   # delete first with same user
        $usrmgr->insert_row(array(
            'username'=>$this->ds->partner_id[$rowindex],
            'password'=>$this->ds->password[$rowindex],
            'level'=>2,
            'group'=>'PA',
            ));

    }

    function remove($i) {
        # extend to also delete data related to partner: sequence and user id
        parent::remove($i);

        # also delete/reset project sequence (if same partner has alrady been entered previously)
        # format: PROJECTID_pa1
        $seq = instantiate_module('seq_gen');
        $seq->remove_row(array('seq_id'=>'PROJECTID_'.$this->ds->partner_id[$i]));

        # also delete user
        $usrmgr = instantiate_module('usrmgr');
        $usrmgr->remove_row(array('username'=>$this->ds->partner_id[$i]));   # delete first with same user

        # also project
        $project = instantiate_module('project');
        $project->remove_row(array('partner_id'=>$this->ds->partner_id[$i]));   # delete first with same user

    }



}

?>
