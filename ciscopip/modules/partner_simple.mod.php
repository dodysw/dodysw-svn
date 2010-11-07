<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
import_module('partner');
class partner_simple extends partner {
    var $db_table, $properties;
    function partner_simple() {
        parent::partner(); # must call base class

        global $html_title;
        $this->title = 'Partner Profile';
        $html_title = $this->title;

        # make all hidden first
        foreach ($this->properties as $k=>$v) {
            $this->properties[$k]->hidden = True;
        }

        $this->db_table = $GLOBALS['dbpre'].'partner_tab';
        $this->properties['partner_id']->hidden = False;
        $this->properties['partner_id']->hyperlink = 'hyper1';
        $this->properties['name']->hidden = False;
        $this->properties['phone_1_area']->hidden = False;
        $this->properties['phone_1_area']->label = 'Area Code';
        $this->properties['phone_1']->hidden = False;
        $this->properties['phone_1']->label = 'Phone';
        $this->properties['contact_person']->hidden = False;
        $this->properties['contact_person']->label = 'Contact Person';
        $this->properties['mobile_phone']->hidden = False;

        $this->query_only = True;
        $this->browse_mode = 'table';
        #~ $this->browse_mode_forced = True;
        $this->allow_query = True;

        if ($_SESSION['login_group'] == 'DE') {

            $this->body['browse']['suffix'] = '
    <form method=POST action="'.$_SERVER['PHP_SELF'].'">
    <input type=hidden name="m" value="'.$this->module.'">
    <input type=hidden name="act" value="new">
    <input type=hidden name="go" value="'.htmlentities($GLOBALS['full_self_url']).'">
    <br><input type=hidden name="num_row" value="1"> <input type=submit value="Add Partner"></p>
    </form>';
        }

    }

    function hyper1($i) {
        return array('url'=>$_SERVER['PHP_SELF'].'?m=partner&rowid='.$this->ds->_rowid[$i]);
    }


}

?>
