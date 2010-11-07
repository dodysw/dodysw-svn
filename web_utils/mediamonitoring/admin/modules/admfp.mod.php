<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

class admfp {
    function admfp() {
        // initialize instance
        global $html_title;
        $this->title = 'Frontpage';
        $html_title = $this->title;
        $this->description = 'Al-Admin Frontpage';
    }

    function go() {
        // called inside main content
        echo "<h1>Al-Admin</h1>";
        global $navdata;
        foreach ($navdata['main'] as $row) {
            if (!check_module_access($row[0])) continue;
            if ($row[0] == '') {    # i guess its a grouping row
                echo '<p><b>'.$row[1].'</b><br>';
            }
            else {
                echo '<a href="'.$_SERVER['PHPSELF'].'?m='.$row[0].'">'.$row[1].'</a><br>';
            }
        }

        echo " ";
    }



    function post_handler() {
    }
}

?>