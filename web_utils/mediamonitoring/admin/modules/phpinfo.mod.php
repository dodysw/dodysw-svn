<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

class phpinfo {

    function phpinfo() {
        // initialize instance
        global $html_title;
        $this->title = 'Frontpage';
        $html_title = $this->title;

        #~ if ($_SERVER['REQUEST_METHOD'] == 'POST') { // validate user logon
            #~ $this->post_handler();
        #~ }
        $this->must_authenticated = True;
    }

    function final_init() {
        // called inside main content
        phpinfo();
        exit();
    }

}

?>