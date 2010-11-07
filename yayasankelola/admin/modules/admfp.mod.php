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
        global $navdata;
        echo '<h3>Sistem Administrasi Yayasan Kelola</h3>';
        foreach ($navdata['main'] as $row) {
            if (!check_module_access($row[0])) continue;
            if ($row[0] == '') {    # i guess its a grouping row
                echo '<p><b>'.$row[1].'</b><br>';
            }
            else {
                echo '<a href="'.$_SERVER['PHPSELF'].'?m='.$row[0].'">'.$row[1].'</a><br>';
            }
        }
    }



    function post_handler() {
    }
}

?>