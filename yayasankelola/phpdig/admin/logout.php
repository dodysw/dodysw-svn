<?php

// basic cookie logout

$relative_script_path = '..';
$no_connect = 0;
include "$relative_script_path/includes/config.php";
include "$relative_script_path/libs/auth.php";

if (isset($_COOKIE['phpdigadmin'])) {
    setcookie("phpdigadmin", "", time()-3600, "/");
}
$relative_script_path = '.';
header("Location: $relative_script_path/index.php");
exit();

?>