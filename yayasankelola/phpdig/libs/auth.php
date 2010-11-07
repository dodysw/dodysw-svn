<?php
/*
--------------------------------------------------------------------------------
PhpDig Version 1.8.x
This program is provided under the GNU/GPL license.
See the LICENSE file for more information.
All contributors are listed in the CREDITS file provided with this package.
PhpDig Website : http://www.phpdig.net/
--------------------------------------------------------------------------------
*/

// basic cookie authentication
// error_reporting(E_ALL);
$relative_script_path = "..";

if (isset($_POST['no_connect'])) {
    $no_connect = $_POST['no_connect'];
}

if  ($no_connect == 1) {
    $redir_file = "install.php";
    $hidden_val = "<input type=\"hidden\" name=\"no_connect\" value=\"1\">";
}
else {
    $redir_file = "index.php";
    $hidden_val = "<input type=\"hidden\" name=\"no_connect\" value=\"0\">";
}

if (!defined('PHPDIG_ADM_AUTH')) {
    clearstatcache();
    if (is_file("$relative_script_path/includes/config.php")) {
        include "$relative_script_path/includes/config.php";
    }
    else {
        die("Unable to find config.php file.\n");
    }
}

if (PHPDIG_ADM_AUTH == 1) {

$user = PHPDIG_ADM_USER;
$pwd = PHPDIG_ADM_PASS;

$testaccess = 0;

if (isset($_POST['username']) && isset($_POST['password'])) {

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (get_magic_quotes_gpc()) {
        $username = stripslashes($username);
        $password = stripslashes($password);
    }

    if (($user == $username) && ($pwd == $password)) {
        $testaccess = 1;
        $cookieinfo = $username.":".$password.":".rand();
        $cookievals = base64_encode($cookieinfo);
        setcookie("phpdigadmin", $cookievals, time()+172800, "/"); // 172800 is two days
echo <<<END
<html>
<head>
<meta http-equiv="Refresh" content="0;url=$relative_script_path/admin/$redir_file">
</head>
<body>
<center>
<br><br><a href="$relative_script_path/admin/$redir_file">You are being redirected. Click to continue...</a>
</center>
</body>
</html>
END;

    }

} 
elseif (isset($_COOKIE['phpdigadmin'])) {

    $phpdigadmin = $_COOKIE['phpdigadmin'];

    $cookievals = base64_decode($phpdigadmin);
    $cookievals = explode(":", $cookievals);

    $username = stripslashes($cookievals[0]);
    $password = stripslashes($cookievals[1]);

    if (($user == $username) && ($pwd == $password)) {
        $testaccess = 1;
    }

}
else {

$phpdigvernum = PHPDIG_VERSION;
echo <<<END
<html>
<head>
<title>PhpDig Admin Login</title>
</head>

<body>
<center>
<br><br>

<form action="$relative_script_path/libs/auth.php" method="post">
<table><tr>
<td colspan="2">PhpDig v.$phpdigvernum Admin Login</td>
</tr><tr>
<td>Username</td><td><input type="text" name="username"></td>
</tr><tr>
<td>Password</td><td><input type="password" name="password">
$hidden_val</td>
</tr><tr>
<td colspan="2">use name &amp; pwd set in config file</td>
</tr><tr>
<td>&nbsp;</td><td><input type="submit" value="Enter"></td>
</tr></table>
</form>

</center>
</body>
</html>
END;

exit();

}

if ($testaccess == 0) {

    if (isset($_COOKIE['phpdigadmin'])) {
        setcookie("phpdigadmin", "", time()-3600);
    }
    header("Location: $relative_script_path/admin/$redir_file");
    exit();

}

}

?>