<?
/* connection to db
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

    if (!@function_exists('mysql_connect')) {   # in php5, mysql is considered just another extension, so we need to load it
        $extension = 'mysql';
        if (strtoupper(substr(PHP_OS, 0,3) == 'WIN'))
            dl($extension.'.dll');
        else
            dl($extension.'.so');
    }

    $dbconn = mysql_connect($appconf['dbhostname'], $appconf['dbuser'], $appconf['dbpassword']) or die(mysql_error());
    mysql_select_db($appconf['dbname'],$dbconn) or die ("Could not select database");
?>