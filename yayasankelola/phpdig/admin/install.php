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
//PhpDig simple install script : create database and connect script.
$relative_script_path = '..';

$no_connect = 1;
$error = '';

include $relative_script_path.'/includes/config.php';
include $relative_script_path.'/libs/auth.php';

extract( phpdigHttpVars(
  array('step'=>'integer',
        'host'=>'string',
        'port'=>'string',
        'sock'=>'string',
        'dbuser'=>'string',
        'dbpass'=>'string',
        'dbprefix'=>'string',
        'dbopt'=>'string')
  ));

switch ($step)
        {
        case 2:
        //format form datas
        $dbhost = $host;
        if ($port)
            $dbhost .= ":".$port;
        if ($sock)
            $dbhost .= ":".$sock;

        //can i connect with those parameters ?
        if ($id_connect = @mysql_connect ($dbhost,$dbuser,$dbpass)) {
            //can i create database or no create db?
            if (@mysql_query("CREATE DATABASE $dbname",$id_connect)
                || ($dbopt && @mysql_select_db($dbname,$id_connect))) {

                mysql_select_db($dbname,$id_connect);
                //are all needed files existent ?
                if (!is_file($relative_script_path.'/includes/_connect.php')) {
                     $error .= phpdigMsg('error1');
                }
                else if (!is_writable($relative_script_path.'/includes/')) {
                     $error .= phpdigMsg('error2');
                }
                if ( !@is_file("$relative_script_path/sql/init_db.sql")
                      && $dbopt == 'cretb'
                    || !@is_file("$relative_script_path/sql/update_db.sql")
                      && $dbopt == 'upddb'
                    ) {
                     $error .= phpdigMsg('error3');
                }
                if (!$error) {
                    $connect_file = @file($relative_script_path.'/includes/_connect.php');
                    $f_id = fopen($relative_script_path.'/includes/connect.php',"w");

                    $dbprefix = substr(ereg_replace('[^a-z0-9_]','',strtolower($dbprefix)),0,16);
                    while (list($id,$line) = each($connect_file)) {
                           $line = eregi_replace("<host>",$dbhost,$line);
                           $line = eregi_replace("<user>",$dbuser,$line);
                           $line = eregi_replace("<pass>",$dbpass,$line);
                           $line = eregi_replace("<database>",$dbname,$line);
                           $line = eregi_replace("<dbprefix>",$dbprefix,$line);
                           fputs($f_id,trim($line)."\n");
                    }
                    fclose($f_id);

                    $cerror = 0;
                    $query = '';
                    if (!$dbopt || $dbopt == 'cretb') {
                        $db_file = @file("$relative_script_path/sql/init_db.sql");
                    }
                    else if ($dbopt == 'upddb') {
                        $db_file = @file("$relative_script_path/sql/update_db.sql");
                    }
                    else {
                         $db_file == '';
                    }
                    if ($db_file) {
                        while (list($id,$line) = each($db_file)) {
                               if (!ereg('^ *#',$line)) {
                                  $line = ereg_replace('(CREATE|ALTER|DROP) +TABLE +','\1 TABLE '.$dbprefix,$line);
                                  $query .= $line;
                               }
                               //end of a query
                               if (ereg(';$',trim($line))) {
                                   $query = ereg_replace(';$','',trim($query));
                                   $res = mysql_query(trim($query),$id_connect);
                                   //table creation failure
                                       if ($res < 1) {
                                           $cerror ++;
                                       }
                                   $query = "";
                               }
                        }
                    }
                    if ($cerror > 0) {
                        //clean partial installation
                        if (!$dbopt) {
                           // @mysql_drop_db($dbname,$id_connect);
                           $error .= phpdigMsg('error4');
                        }
                        $step = 1;
                    }
                    else {
                        header("location:$relative_script_path/admin/");
                    }
                }
                else {
                    if (!$dbopt) {
                         $error .= phpdigMsg('error5');
                         // @mysql_drop_db($dbname,$id_connect);
                    }
                    $step = 1;
                }
            }
            else {
                $error .= phpdigMsg('error6');
                $step = 1;
            }
        }
        else {
            $error .= phpdigMsg('error7');
            $step = 1;
        }
        break;

        default:
        $step = 1;
        $host = 'localhost';
        $dbuser = 'root';
        $dbname = 'phpdig';
        $dbprefix = '';
        }
?>
<?php include $relative_script_path.'/libs/htmlheader.php' ?>
<head>
<title>PhpDig : <?php phpdigPrnMsg('installation'); ?></title>
<?php include $relative_script_path.'/libs/htmlmetas.php' ?>
</head>
<body bgcolor="white">
<img src="../phpdig_logo_2.png" width="200" height="114" alt="PhpDig <?php print PHPDIG_VERSION ?>" border="0" />
<p class="blue"><?php print phpdigMsg('slogan')." ".PHPDIG_VERSION; ?></p>
<h1><?php phpdigPrnMsg('installation'); ?></h1>
<?php print $error ?><br />
<form method="post" action="install.php">
<table class="grey" cellpadding="5" cellspacing="0">
<tr>
<td colspan="3">
<input type="hidden" name="step" value="<?php print ($step+1); ?>" />
<?php phpdigPrnMsg('instructions'); ?>
</td>
</tr>
<tr>
<td><?php phpdigPrnMsg('hostname'); ?></td>
<td><input type="text" name="host" value="<?php print $host ?>" /></td>
<td>
<input type="radio" name="dbopt" value="" <?php if(!$dbopt) { print 'checked="checked"'; } ?>/>
<?php phpdigPrnMsg('createdb'); ?>.
</td>
</tr>
<tr>
<td><?php phpdigPrnMsg('port'); ?></td>
<td><input type="text" name="port" value="<?php print $port ?>" /></td>
<td>
<input type="radio" name="dbopt" value="cretb" <?php if($dbopt == 'cretb') { print 'checked="checked"'; } ?>/>
<?php phpdigPrnMsg('createtables'); ?>.
</td>
</tr>
<tr>
<td><?php phpdigPrnMsg('sock'); ?></td>
<td><input type="text" name="sock" value="<?php print $sock ?>" /></td>
<td>
<input type="radio" name="dbopt" value="upddb" <?php if($dbopt == 'upddb') { print 'checked="checked"'; } ?>/>
<?php phpdigPrnMsg('updatedb'); ?>.
</td>
</tr>
<tr>
<td><?php phpdigPrnMsg('user'); ?></td>
<td><input type="text" name="dbuser" value="<?php print $dbuser ?>" /></td>
<td>
<input type="radio" name="dbopt" value="none" <?php if($dbopt == 'none') { print 'checked="checked"'; } ?>/>
<?php phpdigPrnMsg('existingdb'); ?>.
</td>
</tr>
<tr>
<td><?php phpdigPrnMsg('password'); ?></td>
<td colspan="2"><input type="password" name="dbpass" value="<?php print $dbpass ?>" /></td>
</tr>
<tr>
<td><?php phpdigPrnMsg('phpdigdatabase'); ?></td>
<td colspan="2"><input type="text" name="dbname" value="<?php print $dbname ?>" /></td>
</tr>
<tr>
<td><?php phpdigPrnMsg('tablesprefix'); ?></td>
<td><input type="text" name="dbprefix" value="<?php print $dbprefix ?>" /></td>
<td><?php phpdigPrnMsg('instructions2'); ?></td>
</tr>
<tr>
<td> &nbsp;</td>
<td colspan="2"><input type="submit" name="submit" value="<?php phpdigPrnMsg('installdatabase'); ?>" /></td>
</tr>
</table>
</form>
</body>
</html>