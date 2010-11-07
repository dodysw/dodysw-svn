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
        $this->navbar = 0;
    }

    function go() {
        // called inside main content
        echo '<div align=center><h1>al-admin</h1><p><i>Admin toolkit for web based software</i><p>';
        global $navdata;
        $all = array();
        $total_vlen = 0;
        foreach ($navdata['main'] as $row) {
            $total_vlen += 1;
            if (!check_module_access($row[0])) continue;
            if ($row[0] == '') {    # i guess its a grouping row
                #~ echo '<p><b>'.$row[1].'</b><br>';
                $all[] = array('<h2 style="background-color:#005a9c;color:#fff">'.$row[1].'</h2>',array());
            }
            else {
                #~ echo '<a href="'.$_SERVER['PHPSELF'].'?m='.$row[0].'">'.$row[1].'</a><br>';
                $all[count($all)-1][1][] = '<p><a href="'.$_SERVER['PHPSELF'].'?m='.$row[0].'">'.$row[1].'</a>';
            }
        }

        $count_switch = floor($total_vlen / 2);
        $curr_count = 0;
        $switch_now = 0;
        echo '<table border=0><tr><td valign="top">';
        foreach ($all as $row) {
            $curr_count++;
            if ($curr_count % $count_switch == 0)
                $switch_now = 1;
            echo $row[0];
            foreach ($row[1] as $row2) {
                $curr_count++;
                if ($curr_count % $count_switch == 0)
                    $switch_now = 1;

                echo $row2;
            }
            if ($switch_now) {
                echo '</td><td width=20>&nbsp;</td><td valign="top">';
                $switch_now = 0;
            }
        }
        echo '</td></tr></table>';

        echo "<p>Copyright 2004,2005 - Dody Suria Wijaya - <a href='mailto:dodysw@gmail.com'>dodysw@gmail.com</a>. All right reserved.</p>";
        echo '</div>';
    }



    function post_handler() {
    }
}

?>