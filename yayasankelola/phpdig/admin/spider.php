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

//---------------spider script.
//---------------operates both indexing and spidering
$debut = time();

set_time_limit(86400); // 1 full day
$date = date("YmdHis",time());
$progress = 1;
$no_connect = 0;

//test on cgi or http
//set string messages (shell or browser)
if (!isset($REMOTE_ADDR) && !isset($_SERVER['REMOTE_ADDR'])) {
// register_argc_argv should be set to on: if you cannot turn this option on, try 
// uncommenting the next two lines. if you then get an undefine index message, just 
// recomment these same two lines. Also see http://www.php.net/reserved.variables
//  $argc = $_SERVER["argc"];
//  $argv = $_SERVER["argv"];
    $br = "\n";
    $hr = "\n-----------------------------\n";
    $run_mode = 'cgi';
    $s_yes = "+";
    $s_no  = "X";
    $s_link = "@url";
    $pwd = dirname($argv[0]);
    if (!$pwd || $pwd == '.') {
        $relative_script_path = '..';
    }
    else {
        $path_part = explode('/',$pwd);
        array_pop($path_part);
        if (!ereg('^/',$pwd)) {
             array_unshift($path_part,'.');
        }
        $relative_script_path = implode('/',$path_part);
    }
    //here parse the parameters for the the reindexing...
    if ($argc > 1) {
        // where the spider was launched ?
        switch($argv[1]) {
            case 'all':
            $respider_mode = 'all';
            break;

            case 'forceall':
            $respider_mode = 'reindex_all';
            break;

            default:
            if (ereg('^http[s]?://',$argv[1])) {
//            if (ereg('^http://',$argv[1]) || ereg('^https://',$argv[1])) {
                $url = $argv[1];
                $respider_mode = 'site';
            }
            else if (file_exists($argv[1])) {
                $urlsFile = $argv[1];
                $respider_mode = 'all';
            }
            else {
                die("Usage: php -f spider.php [option]\n"
                    ."Opts:"
                    ."\tall (default)\n"
                    ."\tforceall\n"
                    ."\thttp://something or https://something\n"
                    ."\tfilename [containing list of urls]\n");
            }
        }
    }
    else {
        $respider_mode = 'all';
    }

    //echo $relative_script_path;

    include "$relative_script_path/includes/config.php";
    //low priority if allowed
    if (USE_RENICE_COMMAND == 1) {
        print @exec('renice 18 '.getmypid()).$br;
    }

}
else {
    //include "$relative_script_path/libs/auth.php";
    $run_mode = 'http';
    $br = "<br />\n";
    $hr = "<hr />\n";
    $s_yes = "<img src='yes.gif' width='10' height='10' border='0' align='middle' alt='' />";
    $s_no  = "<img src='no.gif' width='10' height='10' border='0' align='middle' alt='' />";
    $s_link = " <a href='@url' target='_blank'>@url</a> ";
    $relative_script_path = '..';
    include "$relative_script_path/includes/config.php";
    include "$relative_script_path/libs/auth.php";
}

// include "$relative_script_path/includes/config.php";
include "$relative_script_path/admin/robot_functions.php";
// include "$relative_script_path/admin/debug_functions.php";

// header of page
if ($run_mode == 'http') {
?>
<?php include $relative_script_path.'/libs/htmlheader.php' ?>
<head>
<?php include $relative_script_path.'/libs/htmlmetas.php' ?>
</head>
<body bgcolor="white">
<img src="../phpdig_logo_2.png" width="200" height="114" alt="PhpDig <?php print PHPDIG_VERSION ?>" /><br />
<h3><?php phpdigPrnMsg('spidering'); ?></h3>
<?php
}
else {
  phpdigPrnMsg('spidering').$br;
}

// set the User-Agent for the file() function
@ini_set('user_agent','PhpDig/'.PHPDIG_VERSION.' (+http://www.phpdig.net/robot.php)');

// extract and/or init vars
extract( phpdigHttpVars(
  array('respider_mode'=>'string',
        'mode'=>'string',
        'origine'=>'string',
        'localdomain'=>'string',
        'force_first_reindex'=>'string',
        'site_id'=>'integer',
        'nofollow'=>'string',
        'tempfile'=>'string')
  ));

if ($run_mode == 'http') {
  extract(phpdigHttpVars(array('url'=>'string')));
}

$linksper_flag = 0;
if (!isset($linksper) or (int)$linksper > LINKS_MAX_LIMIT) {
 $linksper_flag = 1;
 if ($run_mode != 'cgi') {
    $linksper = RELINKS_LIMIT;
 }
 else {
    $linksper = LINKS_MAX_LIMIT;
 }
}

// from a file
if (isset($urlsFile)) {
    $urlsFile = file($urlsFile);
    foreach($urlsFile as $urlFileLine) {
        if (ereg('^http[s]?://',trim($urlFileLine))) {
           phpdigGetSiteFromUrl($id_connect,trim(str_replace("\n\r\t",'',$urlFileLine)),$linksper,$linksper_flag);
        }
    }
}

$common_words = phpdigComWords("$relative_script_path/includes/common_words.txt");

//connect to distant ftp for text content (if constants are defined)
$ftp_id = phpdigFtpConnect();

//mode url : test new or existing site
if (isset($url) && $url && $url != 'http://' && $url != 'https://' && (!$respider_mode || $respider_mode == 'site')) {
    extract(phpdigGetSiteFromUrl($id_connect,$url,$linksper,$linksper_flag));
}

//retrieve list of urls
if ($site_id) {
    $site_id = (int) $site_id;
    $where_site =  "WHERE site_id=$site_id";
}
else {
    $where_site = '';
}

if (isset($urlsFile)) {
$query = "SELECT DISTINCT(".PHPDIG_DB_PREFIX."sites.site_id),".PHPDIG_DB_PREFIX."sites.site_url,"
.PHPDIG_DB_PREFIX."sites.username as user,".PHPDIG_DB_PREFIX."sites.password as pass,"
.PHPDIG_DB_PREFIX."sites.port FROM ".PHPDIG_DB_PREFIX."sites,".PHPDIG_DB_PREFIX."tempspider WHERE "
.PHPDIG_DB_PREFIX."sites.site_id = ".PHPDIG_DB_PREFIX."tempspider.site_id";
}
else {
$query = "SELECT site_id,site_url,username as user,password as pass,port FROM ".PHPDIG_DB_PREFIX."sites $where_site";
}

$list_sites = phpdigMySelect($id_connect,$query);

if (!isset($limit) or (int)$limit > SPIDER_MAX_LIMIT) {
 if ($run_mode != 'cgi') {
    $limit = RESPIDER_LIMIT;
 }
 else {
    $limit = SPIDER_MAX_LIMIT;
 }
}

$flag_for_inserts_check1 = 0;
$flag_for_inserts_check2 = 0;
$links_found = array();
//retrieves sites
if (is_array($list_sites)) {
  while ($site_datas = array_pop($list_sites)) {
    $site_id = $site_datas['site_id'];
    $url = $site_datas['site_url'];
    $url = ereg_replace("[?]$","",$url);
    $cookies = array();

    $query_lev_page = "SELECT num_page FROM ".PHPDIG_DB_PREFIX."site_page WHERE site_id = '$site_id'";
    $result_lev_page = mysql_query($query_lev_page,$id_connect);
    if (mysql_num_rows($result_lev_page) > 0) {
         list($links_per_lev) = mysql_fetch_row($result_lev_page);
    }
    else {
         $links_per_lev = 0;
    }

    // verify locking status if not locked, lock it,
    // else wait two seconds and put in back in spidering queue
    $verify = phpdigMySelect($id_connect,'SELECT locked FROM '.PHPDIG_DB_PREFIX.'sites WHERE locked = 1 AND site_id='.$site_id);
    if (is_array($verify)) {
         print '*'.$url.' '.phpdigMsg('locked').'*'.$br;
         array_unshift($list_sites,$site_datas);
         sleep(2);
    }
    else {
        // lock site
        mysql_query('UPDATE '.PHPDIG_DB_PREFIX.'sites SET locked=1 WHERE site_id='.$site_id,$id_connect);
        //set a complete url for basic authentification and other ports than 80
        $full_url = '';
        if ($site_datas['user'] && $site_datas['pass']) {
            if (substr($site_datas['site_url'],0,5) == "https") {
                $full_url = 'https://'.$site_datas['user'].':'.$site_datas['pass'].'@'.ereg_replace('^https://(.*)','\1',$url);
            }
            else {
                $full_url = 'http://'.$site_datas['user'].':'.$site_datas['pass'].'@'.ereg_replace('^http://(.*)','\1',$url);
            }
        }
        else {
            $full_url = $url;
        }
        if ($site_datas['port']) {
            $full_url = ereg_replace('/$',':'.$site_datas['port'].'/',$full_url);
        }

        //just keep the reccords not indexed before
        $query = "DELETE FROM ".PHPDIG_DB_PREFIX."tempspider WHERE site_id = '$site_id' and (indexed = 1 or error = 1)";
        mysql_query($query,$id_connect);

        //refill the tempspider with not expired spiders reccords, eventually refined
        switch($respider_mode) {
               case "reindex_all":
               $andmore_tempspider = '';
               $force_first_reindex = 1;
               $delay_message = '';
               break;

               default:
               $andmore_tempspider = 'AND upddate < now()';
               $delay_message = '...'.phpdigMsg('id_recent').$br;
        }

        if (!(LIMIT_TO_DIRECTORY)) {
          if ($links_per_lev == 0) {
             $query_tempspider = "INSERT INTO ".PHPDIG_DB_PREFIX."tempspider (site_id,file,path) SELECT site_id,file,path FROM ".PHPDIG_DB_PREFIX."spider WHERE site_id=$site_id $andmore_tempspider";
             mysql_query($query_tempspider,$id_connect);
          }
          else {
             $query_count_lev = mysql_query("SELECT COUNT(*) as cnt FROM ".PHPDIG_DB_PREFIX."tempspider WHERE site_id = $site_id and level = 0",$id_connect);
             $query_count_arr = mysql_fetch_array($query_count_lev);
             $query_count_num = $query_count_arr['cnt'];
             if ($query_count_num > $links_per_lev) {
               $level_lim = $query_count_num - $links_per_lev;
               $query_tempspider = "DELETE FROM ".PHPDIG_DB_PREFIX."tempspider WHERE level = 0 LIMIT $level_lim";
               mysql_query($query_tempspider,$id_connect);
               $flag_for_inserts_check1 = 1;
             }
             elseif (($links_per_lev > $query_count_num) && ($flag_for_inserts_check1 == 0)) {
               $level_lim = $links_per_lev - $query_count_num;
               $query_tempspider = "INSERT INTO ".PHPDIG_DB_PREFIX."tempspider (site_id,file,path) SELECT site_id,file,path FROM ".PHPDIG_DB_PREFIX."spider WHERE site_id=$site_id $andmore_tempspider LIMIT $level_lim";
               mysql_query($query_tempspider,$id_connect);
             }
          }
        }

        //first level
        $level = 0;
        //store robots.txt datas
        $exclude = phpdigReadRobotsTxt($full_url);
        // parse exclude paths
        $query = "SELECT ex_id, ex_path FROM ".PHPDIG_DB_PREFIX."excludes WHERE ex_site_id='$site_id'";
        if (is_array($list_exclude = phpdigMySelect($id_connect,$query))) {
           foreach($list_exclude as $add_exclude) {
               $exclude[$add_exclude['ex_path']] = 1;
           }
        }

        print $hr.'SITE : '.$url.$br;
        if (is_array($exclude)) {
            print phpdigMsg('excludes').' :'.$br;
            foreach ($exclude as $ex_path => $tmp) {
                 $ex_path = str_replace("\\","",$ex_path);
                 print '- '.$ex_path.$br;
            }
        }
        $n_links = 0;

        // Spidering ...
        while($level <= $limit) {
              sleep(5);
              //retrieve list of links to index from this level
              $query = "SELECT id,path,file,indexed FROM ".PHPDIG_DB_PREFIX."tempspider WHERE level = $level AND indexed = 0 AND site_id=$site_id AND error = 0 limit 1";
              $result_id = mysql_query($query,$id_connect);
              $n_links = mysql_num_rows($result_id);
              if ($n_links > 0) {
                   while ($new_links = mysql_fetch_array($result_id)) {

                        //keep alive the ftp connection (if exists)
                        if (FTP_ENABLE) {
                            $ftp_id = phpdigFtpKeepAlive($ftp_id,$relative_script_path);
                        }

                        //indexing this page
                        $temp_path = $new_links['path'];
                        $temp_file = $new_links['file'];
                        $already_indexed = $new_links['indexed'];
                        $tempspider_id = $new_links['id'];

                        //reset variables
                        $spider_id = 0;
                        $nomodif = 0;
                        $ok_for_spider = 0;
                        $ok_for_index = 0;
                        $tag = '';
                        $revisit_after = '';

                           //Retrieve dates if page is already in database
                        $test_exists = phpdigGetSpiderRow($id_connect,$site_id,$temp_path,$temp_file);
                        if (is_array($test_exists)) {
                            settype($test_exists['spider_id'],'integer');
                            settype($test_exists['upddate'],'string');
                            settype($test_exists['last_modified'],'string');

                            $exists_spider_id = $test_exists['spider_id'];
                            $upddate = $test_exists['upddate'];
                            $last_modif_old = $test_exists['last_modified'];
                        }
                        else {
                             $exists_spider_id = 0;
                        }

                        $url_indexing = $full_url.$temp_path.$temp_file;
                        $url_indexing = ereg_replace("[?]$","",$url_indexing);
                        $url_print = $url.$temp_path.$temp_file;
                        $url_print = ereg_replace("[?]$","",$url_print);

                           //verify if 'revisit-after' date is expired or if page doesn't exists, or force is on.
                        if ($exists_spider_id == 0 || $upddate < $date || ($force_first_reindex == 1 && ($level==0 || $already_indexed==0))) {

                           //test content-type of this page if not excluded
                           $result_test_http = '';
                           if (!phpdigReadRobots($exclude,$temp_path) && !eregi(FORBIDDEN_EXTENSIONS,$temp_file)) {
                                $result_test_http = phpdigTestUrl($url_indexing,'date',$cookies);
                           }

                           if (is_array($result_test_http) && !in_array($result_test_http['status'],array('NOHOST','NOFILE','LOOP','NEWHOST'))) {

                               $tested_url = phpdigRewriteUrl($result_test_http['path']);
                               $cookies = $result_test_http['cookies'];

                               // update URI if redirect in same host...
                               if ($tested_url['path'] != $temp_path || $tested_url['file'] != $temp_file ) {
                                   $temp_path = $tested_url['path'];
                                   $temp_file = $tested_url['file'];
                                   $query = "UPDATE ".PHPDIG_DB_PREFIX."tempspider SET path='$temp_path', file='$temp_file', WHERE id=$tempspider_id";
                                   mysql_query($query,$id_connect);
                                   $url_indexing = $full_url.$temp_path.$temp_file;
                                   $url_indexing = ereg_replace("[?]$","",$url_indexing);
                                   $url_print = $url.$temp_path.$temp_file;
                                   $url_print = ereg_replace("[?]$","",$url_print);
                               }
                               // set user-agent and cookies
                               phpDigSetHeaders($cookies,$temp_path);

                               $last_modified = $result_test_http['lm_date']; //last_modified, content_type
                               $content_type =  $result_test_http['status'];
                               if ($last_modified) {
                                  $last_modified = phpdigReadHttpDate($last_modified);
                               }
                               else {
                                  $last_modified = date("YmdHis",time());
                               }
                               //if the saved last-modified date is sup or equal than the corresponding
                               //header, set $nomodif to 1
                               if ($exists_spider_id > 0 && $last_modif_old >= $last_modified) {
                                    $nomodif = 1;
                               }
                               else {
                                   //continue...
                                   $nomodif = 0;
                                   // sets $tempfile and $tempfilesize
                                   extract(phpdigTempFile($url_indexing,$result_test_http,$relative_script_path.'/admin/temp/'));

                                   //Retrieve meta-tags for this page
                                   if ($content_type == 'HTML') {
                                       if ($tempfile && is_file($tempfile)) {
                                           $tag = phpdigFormatMetaTags($tempfile);
                                           $httpEquiv = phpdigGetHttpEquiv($tempfile);
                                           if (isset($httpEquiv['set-cookie']) &&
                                               eregi('^(([^=]+)=[^;]+)(;.*path=([^[:blank:]]*))?',$httpEquiv['set-cookie'],$cookregs)) {
                                               $cookies[$cookregs[2]]=array('string'=>$cookregs[1],'path'=>$cookregs[4]);
                                           }
                                       }
                                   }
                                   phpDigSetHeaders($cookies,$temp_path);
                                   $noindex = 0;
                                   $nofollow = 0;
                                   if (is_array($tag)) {
                                       //biwise operation on robots tags for noindex
                                       $noindex = 6 & phpdigReadRobotsTags($tag);
                                       $nofollow = 5 & phpdigReadRobotsTags($tag);
                                       $revisit_after = $tag['revisit-after'];
                                   }

                                   //parse next update date with "revist-after" content
                                   $new_upddate = date("YmdHis",time()+phpdigRevisitAfter($revisit_after,LIMIT_DAYS));

                                   //load the file in an Array if all is ok
                                   if ($nomodif == 1) {
                                     $ok_for_spider = $force_first_reindex; //spider if force_first_reindex on
                                     $ok_for_index = 0;
                                     print "No modified : ";
                                     //set the next revisit date
                                     $query = "UPDATE ".PHPDIG_DB_PREFIX."spider SET upddate='$new_upddate' WHERE spider_id = '$exists_spider_id'";
                                     mysql_query($query,$id_connect);
                                   }
                                   elseif ($noindex > 0 || $already_indexed == 1) {
                                     print "Meta Robots = NoIndex, or already indexed : ";
                                     $ok_for_spider = 1;
                                     $ok_for_index = 0;
                                   }
                                   else {
                                     $ok_for_index = 1;
                                     if ($content_type == 'HTML') {
                                          $ok_for_spider = 1;
                                     }
                                   }
                               }

                               //let's go for indexing the content
                               if ($ok_for_index == 1) {
                                   $spider_id = phpdigIndexFile($id_connect,$tempfile,$tempfilesize,$site_id,$origine,$localdomain,$temp_path,$temp_file,$content_type,$new_upddate,$last_modified,$tag,$ftp_id);
                                   array_push($links_found,$url_indexing);
                               }
                               else if ($nomodif == 1) {
                                 print 'File date unchanged'.$br;
                                 $query = "UPDATE ".PHPDIG_DB_PREFIX."spider SET upddate = DATE_ADD(upddate,INTERVAL LIMIT_DAYS DAY) WHERE spider_id = '$exists_spider_id'";
                                 mysql_query($query,$id_connect);
                               }
                               else {
                                 print phpdigMsg('no_toindex').$br;
                               }
                               print ($progress++).':'.$url_print.$br;
                           }
                           else {
                               //none stored
                               if ($exists_spider_id) {
                                   //delete the existing spider_id
                                   print $s_no.phpdigMsg('error').' 404'.$br;
                                   phpdigDelSpiderRow($id_connect,$exists_spider_id);
                               }

                               //mark the tempspider reccord as error
                               $query = "UPDATE ".PHPDIG_DB_PREFIX."tempspider "
                                       ."SET error = 1 WHERE id = $tempspider_id "
                                       ."OR site_id = $site_id AND path LIKE '$temp_path' AND file LIKE '$temp_file'";
                               mysql_query($query,$id_connect);
                           }
                        }
                        else {
                           print $s_no.($progress++).":".str_replace('@url',$url_indexing,$s_link).phpdigMsg('id_recent').$br;
                        }
                        //display progress indicator
                        print "(".phpdigMsg('time')." : ".gmdate("H:i:s",time()-$debut).")".$br;

                        //update temp table with 'indexed' flag
                        $query = "UPDATE ".PHPDIG_DB_PREFIX."tempspider SET indexed=1 WHERE site_id=$site_id AND id=$tempspider_id";
                        $result_update = mysql_query($query,$id_connect);

                        //explore each page to find new links
                        if (isset($tempfile) && ($spider_id > 0 || $ok_for_spider || $force_first_reindex == 1) && $nofollow == 0 && $level < $limit) {
                            $urls = phpdigExplore($tempfile,$url,$temp_path,$temp_file);
                        }
                        //DELETE TEMPFILE
                        if (isset($tempfile) && is_file($tempfile)) {
                           @unlink($tempfile);
                           unset($tempfile);
                        }

                        if (isset($urls) && is_array($urls)) {
                            foreach($urls as $lien) { // urls found per page
                               // ici un nouveau host...
                               if (isset($lien['newhost'])) { 
                                   if (PHPDIG_IN_DOMAIN == true && phpdigCompareDomains($lien['the_http_scheme'].'://'.$lien['newhost'].$lien['path'].$lien['file'],$url)) {
                                     $added_site = phpdigSpiderAddSite($id_connect,$lien['the_http_scheme'].'://'.$lien['newhost'].$lien['path'].$lien['file'],$linksper,$linksper_flag);
                                     // verify the site is not already in the sites list
                                     $site_exists = false;
                                     foreach($list_sites as $verify_site) {
                                         if ($verify_site['site_id'] == $added_site['site_id']) {
                                             $site_exists = true;
                                         }
                                     }
                                     if (!$site_exists && is_array($added_site)) {
                                       if ($added_site['site_id'] != "zzz") {
                                         print 'Ok for '.$lien['the_http_scheme'].'://'.$lien['newhost'].$lien['path'].$lien['file'].' (site_id:'.$added_site['site_id'].')'.$br;
                                         array_unshift($list_sites,$added_site);
                                       }
                                     }
                                   }
                               }
                               //not an apache fancy index (with sorts by columns && not a newhost)
                               else if (!isset($apache_indexes[$lien['file']])) {
                                  $exists = 0;
                                  $exists_temp_spider = 0;

                                  if (!get_magic_quotes_runtime()) {
                                      $lien['path'] = addslashes($lien['path']);
                                      $lien['file'] = addslashes($lien['file']);
                                  }

                                  //is this link already in temp table ?
//                                  $query = "SELECT count(*) as num FROM ".PHPDIG_DB_PREFIX."tempspider WHERE path like '".str_replace("'",'',$lien['path'])."' AND file like '".str_replace("'",'',$lien['file'])."' AND site_id='$site_id'";
                                  $query = "SELECT count(*) as num FROM ".PHPDIG_DB_PREFIX."tempspider WHERE path like '".$lien['path']."' AND file like '".$lien['file']."' AND site_id='$site_id'";

                                  $test_id = mysql_query($query,$id_connect);
                                  if (mysql_num_rows($test_id) > 0) {
                                      $exist_results = mysql_fetch_array($test_id);
                                      $exists += $exist_results['num'];
                                      $exists_temp_spider = $exists;
                                      mysql_free_result($test_id);
                                  }

                                  if (isset($spider_root_id) && $spider_root_id) {
                                       $andmore = " AND spider_id <> '$spider_root_id' ";
                                  }
                                  else {
                                      $andmore = '';
                                  }
                                  //is this link already in spider ?
//                                  $query = "SELECT count(*) as num FROM ".PHPDIG_DB_PREFIX."spider WHERE path like '".str_replace("'",'',$lien['path'])."' AND file like '".str_replace("'",'',$lien['file'])."' AND site_id='$site_id' $andmore";
                                  $query = "SELECT count(*) as num FROM ".PHPDIG_DB_PREFIX."spider WHERE path like '".$lien['path']."' AND file like '".$lien['file']."' AND site_id='$site_id' $andmore";

                                  $test_id = mysql_query($query,$id_connect);
                                  if (mysql_num_rows($test_id) > 0) {
                                      $exist_results = mysql_fetch_array($test_id);
                                      $exists += $exist_results['num'];
                                      mysql_free_result($test_id);
                                  }
                                  $lien['url'] = $full_url;

                                  //test validity of the new link
                                  if ($exists < 1) {
                                      $cur_link = phpdigDetectDir($lien,$exclude,$cookies,$site_id,$id_connect);
                                  }
                                  else {
                                      $cur_link['ok'] = 0;
                                  }

                                  if ($cur_link['ok'] == 1) {
                                       $s_error = 0;
                                       print '+ ';
                                  }
                                  else {
                                      $s_error = 1;
                                      // redirection
                                      if (isset($cur_link['status']) && $cur_link['status'] == 'NEWHOST' && isset($lien['the_http_scheme'])) {
                                          if (PHPDIG_IN_DOMAIN == true && phpdigCompareDomains($lien['the_http_scheme'].'://'.$cur_link['host'].$cur_link['path'],$url)) {
                                              $added_site = phpdigSpiderAddSite($id_connect,$lien['the_http_scheme'].'://'.$cur_link['host'].$cur_link['path'],$linksper,$linksper_flag);
                                              // verify the site is not already in the sites list
                                              $site_exists = false;
                                              foreach($list_sites as $verify_site) {
                                                    if ($verify_site['site_id'] == $added_site['site_id']) {
                                                        $site_exists = true;
                                                    }
                                              }
                                              if (!$site_exists && is_array($added_site)) {
                                                if ($added_site['site_id'] != "zzz") {
                                                  print 'Ok for '.$lien['the_http_scheme'].'://'.$cur_link['host'].$cur_link['path'].' (site_id:'.$added_site['site_id'].')'.$br;
                                                  array_unshift($list_sites,$added_site);
                                                }
                                              }
                                          }
                                      }
                                  }
                                  //insert in temp table for next level
                                  if (($exists_temp_spider < 1) && ($cur_link['ok'])) {
                                    settype($cur_link['path'],'string');
                                    settype($cur_link['file'],'string');

                                    $values =  "('".$cur_link['path']."','".$cur_link['file']."',".($level+1).",$site_id,$s_error)";
                                    
                                      if ($links_per_lev == 0) {
                                        $query = "INSERT INTO ".PHPDIG_DB_PREFIX."tempspider (path, file, level, site_id, error) VALUES $values";
                                        mysql_query($query,$id_connect);
                                      }
                                      else {
                                        $query_count_lev = mysql_query("SELECT COUNT(*) as cnt FROM ".PHPDIG_DB_PREFIX."tempspider WHERE site_id = $site_id and level = $level+1",$id_connect);
                                        $query_count_arr = mysql_fetch_array($query_count_lev);
                                        $query_count_num = $query_count_arr['cnt'];
                                        if ($query_count_num > $links_per_lev) {
                                          $level_lim = $query_count_num - $links_per_lev;
                                          $query = "DELETE FROM ".PHPDIG_DB_PREFIX."tempspider WHERE level = $level+1 LIMIT $level_lim";
                                          mysql_query($query,$id_connect);
                                          $flag_for_inserts_check2 = 1;
                                        }
                                        elseif (($links_per_lev > $query_count_num) && ($flag_for_inserts_check2 == 0)) {
                                          $query = "INSERT INTO ".PHPDIG_DB_PREFIX."tempspider (path, file, level, site_id, error) VALUES $values";
                                          mysql_query($query,$id_connect);
                                        }
                                      }

                                  }
                                  //display something to avoid browser-side timeout
                                  flush();
                               }
                            }
                            unset($lien);
                            echo $br;
                        }
                   }
              }
              else {
                  // verify if there are not links deeper
                  $query = "SELECT id FROM ".PHPDIG_DB_PREFIX."tempspider WHERE indexed = 0 AND site_id=$site_id AND error = 0 limit 1";
                  $all_result_id = mysql_query($query,$id_connect);
                  $n_all_links = mysql_num_rows($all_result_id);
                  mysql_free_result($all_result_id);
                  if ($n_all_links == 0) {
                      print phpdigPrnMsg('no_temp').$br;
                      break;
                  }
                  else {
                       mysql_free_result($result_id);
                       $query = "SELECT id FROM ".PHPDIG_DB_PREFIX."tempspider WHERE level = $level AND indexed = 0 AND site_id=$site_id AND error = 0 limit 1";
                       $result_id = mysql_query($query,$id_connect);
                       $n_links = mysql_num_rows($result_id);
                       mysql_free_result($result_id);
                       if ($n_links == 0) {
                           $level++;
                           print phpdigMsg('level')." $level...".$br;
                       }
                  }
              }
        }

        if ($links_per_lev != 0) {
            mysql_query("DELETE FROM ".PHPDIG_DB_PREFIX."tempspider WHERE level = $level AND site_id = $site_id",$id_connect);
        }
        $n_links = count($links_found);

        if ($run_mode == 'http') {
           //results-in-http-mode-----------------
           print "<hr /><h3>".phpdigMsg('links_found')." : $n_links</h3>";
           foreach($links_found as $uri) {
              print "<a href=\"$uri\" target=\"_blank\" >".urldecode($uri)."</a><br />\n";
           }
        }
        else {
           print phpdigMsg('links_found')." : ".$n_links.$br;
        }

        if (!$n_links && $delay_message) {
           print $delay_message;
        }
        // clean the tempspider table
        $query = "DELETE FROM ".PHPDIG_DB_PREFIX."tempspider WHERE site_id=$site_id AND (error = 1 OR indexed = 1)";
        mysql_query($query,$id_connect);
        // clean includes table
        if (LIMIT_TO_DIRECTORY) {
            $query = "DELETE FROM ".PHPDIG_DB_PREFIX."includes WHERE in_site_id=$site_id";
            mysql_query($query,$id_connect);
        }
        // unlock site
        mysql_query('UPDATE '.PHPDIG_DB_PREFIX.'sites SET locked=0 WHERE site_id='.$site_id,$id_connect);
    }
  }
}

phpdigFtpClose($ftp_id);

print "Optimizing tables...".$br;
@mysql_query("OPTIMIZE TABLE ".PHPDIG_DB_PREFIX."spider",$id_connect);
@mysql_query("OPTIMIZE TABLE ".PHPDIG_DB_PREFIX."engine",$id_connect);
@mysql_query("OPTIMIZE TABLE ".PHPDIG_DB_PREFIX."keywords",$id_connect);

//display end of indexing
phpdigPrnMsg('id_end');

if ($run_mode == 'http')
{ ?>
<hr />
<a href="index.php" >[<?php phpdigPrnMsg('back') ?>]</a> <?php phpdigPrnMsg('to_admin') ?>.
<?php
if (isset($mode) && isset($site_id) && $mode == 'small') {
    print '<br /><a href="update_frame.php?site_id='.$site_id.'" >['.phpdigMsg('back').']</a> '.phpdigMsg('to_update').'.';
}
?>
</body>
</html>
<?php
}
else {
      print $br;
}
?>
