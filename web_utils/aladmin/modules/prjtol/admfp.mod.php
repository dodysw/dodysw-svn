<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

class admfp {
    function admfp() {
        // initialize instance
        global $html_title;
        $this->title = 'Frontpage';
        $html_title = $this->title;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') { // validate user logon
            $this->post_handler();
        }
        $this->must_authenticated = True;
    }

    function go() {
        // called inside main content
        echo "<h1>Al-Admin</h1>";
        echo "<p>Copyright 2004 - Dody Suria Wijaya - <a href='mailto:dodysw@gmail.com'>dodysw@gmail.com</a>. All right reserved.</p>";
    }

    function post_handler() {
    }
}

?>