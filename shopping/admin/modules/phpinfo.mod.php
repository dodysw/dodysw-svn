<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

class phpinfo {

    function phpinfo() {
        // initialize instance
        global $html_title;
        $this->title = 'Frontpage';
        $html_title = $this->title;

        $this->must_authenticated = True;
    }

    function final_init() {
        // called inside main content
        phpinfo();
        exit();
    }

}

?>