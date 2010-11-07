<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
include_once('usrmgr.mod.php');

class usrmgr_po extends usrmgr {
    function usrmgr_po() {
        parent::usrmgr(); # must call base class

        global $html_title;
        $this->title = 'Project Officer Manager';
        $html_title = $this->title;

        $this->db_where = "`group`='PO'";
        if ($_SESSION['login_group'] == 'PO') {
            $this->db_where = "`group`='PO' and `username`='{$_SESSION['login_user']}'";
            $this->allow_new = False;
            $this->allow_delete = False;
        }

        $this->properties['level']->hidden = True;
        $this->properties['level']->insertable = False;
        $this->properties['level']->updatable = False;
        $this->properties['group']->hidden = True;
        $this->properties['group']->insertable = False;
        $this->properties['group']->updatable = False;

    }

    function insert($rowindex) {
        $this->ds->level[$rowindex] = 2;
        $this->ds->group[$rowindex] = 'PO';
        parent::insert($rowindex);
    }

}

?>
