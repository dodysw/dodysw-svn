<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
include_once('usrmgr.mod.php');

class usrmgr_de extends usrmgr {
    function usrmgr_de() {
        parent::usrmgr(); # must call base class

        global $html_title;
        $this->title = 'Data Entry Manager';
        $html_title = $this->title;

        $this->db_where = "`group`='DE'";

        $this->properties['level']->hidden = True;
        $this->properties['level']->insertable = False;
        $this->properties['level']->updatable = False;
        $this->properties['group']->hidden = True;
        $this->properties['group']->insertable = False;
        $this->properties['group']->updatable = False;

    }

    function insert($rowindex) {
        $this->ds->level[$rowindex] = 2;
        $this->ds->group[$rowindex] = 'DE';
        parent::insert($rowindex);
    }

}

?>
