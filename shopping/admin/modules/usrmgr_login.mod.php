<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class usrmgr_login extends TableManager {
    var $db_table, $properties;
    function usrmgr_login() {
        parent::TableManager(); # must call base class
        global $html_title;
        $this->title = 'User login log';
        $html_title = $this->title;
        $this->properties['parent_id'] = new Prop(array('colname'=>'parent_id','required'=>True, 'cdatatype'=>'fkey', 'enumerate'=>'usrmgr'));
        $this->properties['login_time'] = new Prop(array('colname'=>'login_time', 'datatype'=>'datetime',));
        $this->properties['fail'] = new Prop(array('colname'=>'fail', 'cdatatype'=>'bool',));
        $this->properties['logout_time'] = new Prop(array('colname'=>'logout_time', 'datatype'=>'datetime',));
        $this->properties['duration'] = new Prop(array('colname'=>'duration', 'datatype'=>'int',));
        $this->properties['client_ip_addr'] = new Prop(array('colname'=>'client_ip_addr','cdatatype'=>'client_ip_addr'));

        $this->unit = 'log';
        $this->allow_delete = False;
        $this->allow_new = False;
        $this->allow_edit = False;
        $this->allow_view = False;
    }

    function go() {// called inside main content
        $this->basic_handler();
    }

    function record_log($user_id, $direction = 'in', $fail = False) {
        $this->clear();
        if ($direction == 'in') {
            $this->ds->parent_id[0] = $user_id;
            $this->ds->fail[0] = $fail;
            $this->ds->login_time[0] = date('Y-m-d H:i:s');
            return $this->insert(0);
        }
        else {
            # populate back the relevant row
            if ($_SESSION['login_log_rowid'] == '') {
                $this->ds->parent_id[0] = $user_id;
                $this->ds->logout_time[0] = date('Y-m-d H:i:s');
                return $this->insert(0);
            }
            $now = time();
            $this->populate($_SESSION['login_log_rowid']);
            $this->ds->duration[0] = intval(round(($now - strtotime($this->ds->login_time[0])) / 60));
            $this->ds->logout_time[0] = date('Y-m-d H:i:s',$now);
            $this->update(0);
        }

    }

}

?>
