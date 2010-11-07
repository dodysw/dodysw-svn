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

//ob_start();

$relative_script_path = '..';
$no_connect = 0;
include "$relative_script_path/includes/config.php";
include "$relative_script_path/libs/auth.php";
include "$relative_script_path/admin/robot_functions.php";

// extract http vars
extract(phpdigHttpVars(array('type' => 'string')));

set_time_limit(300);
?>
<?php include $relative_script_path.'/libs/htmlheader.php' ?>
<head>
<title>PhpDig : <?php phpdigPrnMsg('limit') ?> </title>
<?php include $relative_script_path.'/libs/htmlmetas.php' ?>
</head>
<body bgcolor="white">
<table border="0">
<tr>
	<td valign="top">
	<h1><?php phpdigPrnMsg('limit') ?></h1>
	<p class='grey'>
	<?=phpdigPrnMsg('upd_sites')?>
	</p>
	<a href="index.php" target="_top">
		[<?php phpdigPrnMsg('back') ?>]</a> 
	<?php phpdigPrnMsg('to_admin') ?>.
	</td>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td valign="top">Here you can manage:<br> 
		<?php 
		if (CRON_ENABLE){
			?>- the number of <b>days</b> crontab waits to reindex (0 = ignore)<br><?php 
		} ?>
		- the max number of <b>links</b> per depth per site (0 = unlimited)<br><br>
            HTTP Depth and Shell Depth are the maximum depths set in the config file.<br>
            You can set a different <i>by site</i> HTTP search depth using the URI form <a href="index.php">here</a>.<br>
            Maximum links found is ((depth * links) + 1) when links is greater than zero.<br><br>

<?php 
if ((isset($_REQUEST['upd'])) && ($_REQUEST['upd'] == 1)) { 
	?><p class='grey'><?=phpdigPrnMsg('upd2')?></p><br><? 
} 

if ((isset($_REQUEST['num_page'])) && (!$_REQUEST['num_page'])) { /* SHOW the form to enter the days*/ 	
	form_cron_limits($id_connect);

} else {
	if (CRON_ENABLE) {
    	/*
    	* template record for crontab
    	*/
    	$tpl_cron  = '0 0 1-31/DAY * * '.PHPEXEC.' -f '.ABSOLUTE_SCRIPT_PATH.'/admin/spider.php URL'."\n"; 
    	/*
    	* read itself
    	*/
    	$self_cron = '0 0 1-31/1 * * '.CRON_EXEC_FILE.' '.CRON_CONFIG_FILE."\n";
    	/**
    	* the file that holds all this stuff
    	*/
    	$site_fp = fopen (CRON_CONFIG_FILE, "w"); // file opening
    	fputs ($site_fp, $self_cron, 4096); // write the first row 
    		/**
    	* Delete records all at once.
    	*/
    	// $insert_d = mysql_query('DELETE FROM '.PHPDIG_DB_PREFIX.'sites_days_upd',$id_connect);
      if (isset($_REQUEST['days'])) { $days = $_REQUEST['days']; } else { $days = null; }
    		/**
    	* write the cron file and update the table.
    		*/
      if ((isset($_REQUEST['days'])) && ($days != null)) {
     	  foreach($_REQUEST['days'] as $id => $days) {
    		if (((int)$days)>=0){
    			$site_id_sql = "select site_url from ".PHPDIG_DB_PREFIX."sites where site_id ='$id'";
    			$res_id = mysql_query($site_id_sql,$id_connect);
    			list($url) = mysql_fetch_row($res_id);
    			// Insert only if a value has been passed
        		$site_cron = eregi_replace('DAY',$days,$tpl_cron);
        		$site_cron = eregi_replace('URL',$url,$site_cron);
                  if ($days != 0) {
        		fputs ($site_fp, $site_cron, 4096);
                  }
		      $query_days = "SELECT days FROM ".PHPDIG_DB_PREFIX."sites_days_upd WHERE site_id = '$id'";
    	            $result_days = mysql_query($query_days,$id_connect);
    	            if (mysql_num_rows($result_days) > 0) {
			    $sql_ins = "UPDATE ".PHPDIG_DB_PREFIX."sites_days_upd SET days='$days' "
					." WHERE site_id='$id'";
		      } else {
			    $sql_ins = "INSERT INTO ".PHPDIG_DB_PREFIX."sites_days_upd (site_id,days) VALUES  "
							."('$id', '$days') ";
		      }

        		// $sql_ins = "INSERT INTO ".PHPDIG_DB_PREFIX."sites_days_upd VALUES ('$id','".$days."')";
        		$insert_d = mysql_query($sql_ins,$id_connect);	
    		}
    	  }
      }
    	fclose($site_fp); // closing time
	}

      if (isset($_REQUEST['num_page'])) {
	  foreach($_REQUEST['num_page'] as $id => $num_page) {
		$query_num_page = "SELECT num_page FROM ".PHPDIG_DB_PREFIX."site_page WHERE site_id = '$id'";
    	      $result_num_page = mysql_query($query_num_page,$id_connect);
    	      if (mysql_num_rows($result_num_page) > 0) {
			$sql = "UPDATE ".PHPDIG_DB_PREFIX."site_page SET num_page='$num_page' "
					." WHERE site_id='$id'";
		} else {
			$sql = "INSERT INTO ".PHPDIG_DB_PREFIX."site_page (site_id,num_page) VALUES  "
							."('$id', '$num_page') ";
		}
		$res = mysql_query($sql,$id_connect);
	  }
      }
      if (isset($_REQUEST['sent'])) { $upd_done = 1; } else { $upd_done = 0; }
	form_cron_limits($id_connect, $upd_done);
	//header('Location: limit_upd.php?upd=1'); 
} 

?>
</td></tr></table>
</body>
</html>

<?
//ob_end_flush();

Exit;

/**
* 
* */
function form_cron_limits($id_connect, $upd=0){
	if ((isset($_GET['dir'])) && ($_GET['dir'] == 'DESC')) $dir='ASC'; else $dir='DESC';
	
	if($upd == 1) { 
	  ?><p class='grey'><?=phpdigPrnMsg('upd2')?></p><br><? 
      } 
	?>	
	<table class="borderCollapse" border="0">
		<tr>
		 <td class="blueForm"><a href="limit_upd.php?OB=site_id&dir=<?=$dir?>">ID</a></td>
		 <td class="blueForm"><a href="limit_upd.php?OB=site_url&dir=<?=$dir?>">URL</a></td>
		 <?php 
		 if(CRON_ENABLE){
		 	?><td class="blueForm"><a href="limit_upd.php?OB=days&dir=<?=$dir?>"><?=phpdigPrnMsg('days')?></a></td><?php 
		 }?>
		 <td class="blueForm"><a href="limit_upd.php?OB=num_page&dir=<?=$dir?>"><?=phpdigPrnMsg('links')?></a></td>
		 <td class="blueForm"><?=phpdigPrnMsg('HTTP Depth')?></td>
		 <td class="blueForm"><?=phpdigPrnMsg('Shell Depth')?></td>
		</tr>
		<form class="grey" action="limit_upd.php" method="post">
		<?php
		//list of sites in the database
	$query = "SELECT S.site_id,S.site_url,D.days,P.num_page 
				FROM ".PHPDIG_DB_PREFIX."sites AS S  
				LEFT JOIN ".PHPDIG_DB_PREFIX."sites_days_upd AS D ON  S.site_id=D.site_id
				LEFT JOIN ".PHPDIG_DB_PREFIX."site_page AS P ON S.site_id=P.site_id ";
		/* stabiliamo il campo su cui ordinare i dati */
      if (isset($_GET['OB'])) {
 	   switch($_GET['OB']) {

		case("site_id"):
		$query .= ' ORDER BY S.site_id';
		break;

		case('site_url'):
		$query .= ' ORDER BY S.site_url';
		break;
	
		case('days'):
		$query .= ' ORDER BY D.days';
		break;

		case('num_page'):
		$query .= ' ORDER BY P.num_page';
		break;
		
 		default:
			$query .= ' ORDER BY S.site_url';
		break; 
 
 	   }
      }
      else { $query .= ' ORDER BY S.site_url'; }

		//echo $query;
	// ordinamento discendente
	if ((isset($_GET['dir'])) && ($_GET['dir'] == 'DESC')) $query .= ' DESC';
	else $query .= ' ASC';	
		//echo $query;
		/**
		* Build the query
		*/
	$col = 1;	
	$result_id = mysql_query($query,$id_connect);
	while (list($id,$url,$days_db,$num_page) = mysql_fetch_row($result_id)) { 
		switch($col) {
			case 1:
			$class = 'greyFormDark'; 
			$col++; 
			break;
	
			case 2:
			$class = 'greyForm'; 
			$col++; 
			break;
		
			case 3:
			$class = 'greyFormLight'; 
			$col++;
			break;
		
			case 4:
			$class = 'greyForm'; 
			$col++;
			break;
		}
		if($col == 5) $col = 1;?>
		<tr class="<?=$class?>">		
		 <td class="<?=$class?>"><?=$id?></td>	
		 <td class="<?=$class?>"><?=$url?></td>
		 <?php 
		 if(CRON_ENABLE){
		 	?><td class="<?=$class?>">
			<input class="phpdigSelect" type="text" name="days[<?=$id?>]" value="<?=$days_db?>" size="10"/>
		  	</td><?php 
		  } ?>
		<td class="<?=$class?>">
			<input class="phpdigSelect" type="text" name="num_page[<?=$id?>]" value="<?=$num_page?>" size="10"/>
		  </td>
		 <td class="<?=$class?>"><?php echo RESPIDER_LIMIT; ?></td>
		 <td class="<?=$class?>"><?php echo SPIDER_MAX_LIMIT; ?></td>
		</tr>
		<?

	} ?>
	<tr><td><input type="submit" name="sent" value="<?php echo phpdigPrnMsg('go'); ?>"></td></tr>
	</form>
	</table><?


}
?>

