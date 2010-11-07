<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
import_module('partner_rpt2');
class project_google extends partner_rpt2 {
    var $db_table, $properties;

    function project_google() {
        parent::partner_rpt2(); # must call base class

        $this->title = '';  # move title from each form/grid to page
        $this->browse_wait_query = True;
        $this->allow_query = False;

    }

    function go() { // called inside main content
        $this->show_doogle();
        $this->basic_handler();
    }

    function show_doogle() {
        echo '<center><h3>Search Projects</h3><form name="search" method=POST action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name=m value="'.$this->module.'">';
        echo '<input type=hidden name=act value="browse">';
        echo '<input type=text name=query value="'.$this->_query.'" size=30>';
        echo '<input type=hidden name=qf value="*">';
        echo '<input type=submit value="Search">';
        echo '</form></center><br><br>';
    }

}

?>
