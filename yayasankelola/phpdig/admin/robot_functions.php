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

//=================================================
//Add or retrieve a site from an URI
//Returns array($site_id,$exclude)
function phpdigGetSiteFromUrl($id_connect,$url,$linksper,$linksper_flag) {
    //format url
    $pu = parse_url($url);

    if (!isset($pu['scheme'])) {
      $pu['scheme'] = "http";
    }
    if (!isset($pu['host'])) {
      echo 'Specify a valid host ! ';
      die;
    }

    settype($pu['path'],'string');
    settype($pu['query'],'string');
    settype($pu['user'],'string');
    settype($pu['pass'],'string');
    settype($pu['port'],'integer');
    if ($pu['port'] == 0 || $pu['port'] == 80) {
         $pu['port'] = '';
    }
    else {
         settype($pu['port'],'integer');
    }

    $url = $pu['scheme']."://".$pu['host']."/";

    //build a complete url with user/pass and port
    $full_url = $pu['scheme']."://";
    if ($pu['user'] && $pu['pass']) {
        $full_url .= $pu['user'].':'.$pu['pass'].'@';
    }
    $full_url .= $pu['host'];
    if ($pu['port']) {
        $full_url .= ':'.$pu['port'];
    }
    $full_url .= '/';

    $subpu = phpdigRewriteUrl($pu['path']."?".$pu['query']);

    if (!$pu['port']) {
         $where_port = "and (port IS NULL OR port = 0)";
    }
    else {
          $where_port = "and port='".$pu['port']."'";
    }

    $query = "SELECT site_id FROM ".PHPDIG_DB_PREFIX."sites WHERE site_url = '$url' $where_port";
    $result = mysql_query($query,$id_connect);
    if (mysql_num_rows($result) > 0) {
        $exclude = phpdigReadRobotsTxt($full_url);
        $new_site = 0;
        //existing site
        list($site_id) = mysql_fetch_row($result);
        $query = "SELECT ex_id, ex_path FROM ".PHPDIG_DB_PREFIX."excludes WHERE ex_site_id='$site_id'";
        if (is_array($list_exclude = phpdigMySelect($id_connect,$query))) {
            foreach($list_exclude as $add_exclude) {
                $exclude[$add_exclude['ex_path']] = 1;
            }
        }
        $subpu['url'] = $full_url;
        $subpu = phpdigDetectDir($subpu,$exclude);
        mysql_free_result($result);

        if ($subpu['ok'] == 1) {
            set_time_limit(0);
            if (isset($subpu['path']) && (strlen($subpu['path']) > 0) && LIMIT_TO_DIRECTORY) {
                $query_tempspider = "INSERT INTO ".PHPDIG_DB_PREFIX."includes SET in_site_id = ".$site_id.", in_path = '".$subpu['path']."';";
                mysql_query($query_tempspider,$id_connect);
            }
            $query_tempspider = "INSERT INTO ".PHPDIG_DB_PREFIX."tempspider (site_id,file,path) VALUES ('$site_id','".$subpu['file']."','".$subpu['path']."')";
            mysql_query($query_tempspider,$id_connect);
        }
    }
    else {
         //new site
         $query = "INSERT INTO ".PHPDIG_DB_PREFIX."sites SET site_url='$url',upddate=NOW(),username='".$pu['user']."',password='".$pu['pass']."',port='".$pu['port']."'";
         mysql_query($query,$id_connect);
         $site_id = mysql_insert_id($id_connect);
         $new_site = 1;

         //new spidering = insert first row in tempspider
         $subpu['url'] = $full_url;

         $exclude = phpdigReadRobotsTxt($full_url);
         $subpu = phpdigDetectDir($subpu,$exclude);

         if ($subpu['ok'] == 1) {
            set_time_limit(0);
            if (isset($subpu['path']) && (strlen($subpu['path']) > 0) && LIMIT_TO_DIRECTORY) {
                $query = "INSERT INTO ".PHPDIG_DB_PREFIX."includes SET in_site_id = ".$site_id.", in_path = '".$subpu['path']."';";
                mysql_query($query,$id_connect);
            }
            $query = "INSERT INTO ".PHPDIG_DB_PREFIX."tempspider SET file='".$subpu['file']."',path='".$subpu['path']."',level=0,site_id='$site_id'";
            mysql_query($query,$id_connect);
         }
    }

    $query_num_page = "SELECT num_page FROM ".PHPDIG_DB_PREFIX."site_page WHERE site_id = '$site_id'";
    $result_num_page = mysql_query($query_num_page,$id_connect);
    if (mysql_num_rows($result_num_page) == 0) {
        $sql = "INSERT INTO ".PHPDIG_DB_PREFIX."site_page (site_id,num_page) VALUES ('$site_id', '$linksper')";
    }
    elseif ($linksper_flag == 0) {
        $sql = "UPDATE ".PHPDIG_DB_PREFIX."site_page SET num_page='$linksper' WHERE site_id='$site_id'";
    }
    if (isset($sql)) { mysql_query($sql,$id_connect); }

    return array('site_id'=>$site_id,'exclude'=>$exclude,'new_site'=>$new_site);
}

//=================================================
//converts an iso date to a mysql date
function phpdigReadHttpDate($date) {
global $month_names;
if (eregi('(([a-z]{3})\, ([0-9]{1,2}) ([a-z]+) ([0-9]{4}) ([0-9:]{8}) ([a-z]+))',$date,$regs))
    {
    $month = sprintf('%02d',$month_names[strtolower($regs[4])]);
    $year = sprintf('%04d',$regs[5]);
    $day = sprintf('%02d',$regs[3]);
    $hour = sprintf('%06d',str_replace(':','',$regs[6]));
    return "$year$month$day$hour";
    }
}

//=================================================
//advanced striptags function.
//returns text and title
function phpdigCleanHtml($text) {
//htmlentities
global $spec;

//replace blank characters by spaces
$text = ereg_replace("[\r\n\t]+"," ",$text);

//extracts title
if ( eregi("<title *>([^<>]*)</title *>",$text,$regs) ) {
    $title = $regs[1];
}
else {
    $title = "";
}
//delete content of head, script, and style tags
$text = eregi_replace("<head[^>]*>.*</head>"," ",$text);
//$text = eregi_replace("<script[^>]*>.*</script>"," ",$text); // more conservative
$text = preg_replace("/<script[^>]*?>.*?<\/script>/is","",$text); // less conservative
$text = eregi_replace("<style[^>]*>.*</style>"," ",$text);
// clean tags
$text = preg_replace("/<[\/\!]*?[^<>]*?>/is","",$text);

//tries to replace htmlentities by ascii equivalent
foreach ($spec as $entity => $char) {
      $text = eregi_replace ($entity."[;]?",$char,$text);
      $title = eregi_replace ($entity."[;]?",$char,$title);
}

while (eregi('&#([0-9]{3});',$text,$reg)) {
    $text = str_replace($reg[0],chr($reg[1]),$text);
}
while (eregi('&#x([a-f0-9]{2});',$text,$reg)) {
    $text = str_replace($reg[0],chr(base_convert($reg[1],16,10)),$text);
}

//replace blank characters by spaces
$text = eregi_replace("--|[*{}()\"]+|[\r\n\t]+",' ',$text);

//replace any group of blank characters by a unique space
$text = ereg_replace("[[:space:]]+"," ",eregi_replace("<[^>]*>","",$text));

// get any stranglers
$text = eregi_replace('( -> | <- | > | < )','',$text);

//strip characters used in highlighting
$text = str_replace("^#_","",str_replace("_#^","",$text));
$text = str_replace("@@@","",str_replace("@#@","",$text));

$retour['content'] = $text;
$retour['title'] = $title;
return $retour;
}

//=================================================
//purify urls from relative components like ./ or ../ and return an array
function phpdigRewriteUrl($eval)
{
settype($eval,'string');
//delete special links
if (eregi("[/]?mailto:|[/]?javascript:|[/]?news:",$eval)) {
   return -1;
}

$eval = str_replace(" ","%20",$eval);

// parse and remove quotes
$eval = preg_replace('/[\0]/is','',$eval); // remove null byte
$eval = preg_replace('/[\']/is','',$eval); // remove single quote
$eval = preg_replace('/["]/is','',$eval); // remove double quote
$eval = preg_replace('/[\\\\]/is','',$eval); // remove backslash
$eval = ereg_replace("[?]$","",$eval); // remove trailing question mark

if (PHPDIG_SESSID_REMOVE) {
    $eval = phpdigSessionRemove($eval);
}

$url = @parse_url(str_replace('\'"','',$eval));
if (isset($url['query'])) {
     $url['query'] = str_replace("&amp;","&",$url['query']);
}
if (!isset($url['path'])) { 
     $url['path'] = ''; 
}

// $path = $url['path'];
$path = str_replace('&amp;','&',$url['path']);

if (PHPDIG_DEFAULT_INDEX == true) {
    // considers (index|default)\.(php|phtml|asp|htm|html)$ as the same as none
    $path = ereg_replace('(.*/|^)(index|default)\.(php|phtml|asp|htm|html)$','\1',$path);
}

while (ereg('[^/]*/\.{2}/',$path,$regs)) {
   $path = ereg_replace('[^/]*/\.{2}/','',$path);
}

$path = str_replace("./","",ereg_replace("^[.]/","",ereg_replace("^[.]{2}/.*",'NOMATCH',ereg_replace("[^/]*/[.]{2}/","",ereg_replace("^[.]/","",ereg_replace("/+","/",$path))))));

if (ereg('([^/]+)$',$path,$regs)) {
   $file = $regs[1];
   $path = str_replace($file,"",$path);
}
else  {
    $file = '';
}

if ($path != '/') {
    $retour['path'] = ereg_replace('(.*[^/])/?$','\\1/',ereg_replace('^/(.*)','\\1',ereg_replace("/+","/",$path)));
}
else {
    $retour['path'] = '';
}

if (isset($url['query']) && strlen($url['query']) > 0) {
     $file .= "?".$url['query'];
     $retour['as_query'] = 1;
}

$retour['file'] = $file;

//path outside site tree
if ($retour['path'] == "NOMATCH") {
   return array('path' => '', 'file' => '');
}

return $retour;
}

//========================================
// Test presence and type of an url
function phpdigTestUrl($url,$mode='simple',$cookies=array()) {

$components = parse_url($url);

if ($components['scheme'] == "https") {
    $http_scheme = "HTTPS";
}
else {
    $http_scheme = "HTTP";
}

$lm_date = '';
$status = 'NOFILE';
$auth_string = '';
$redirs = 0;
$stop = false;

if (isset($components['host'])) {
    $host = $components["host"];
    if (isset($components['user']) && isset($components['pass']) &&
        $components['user'] && $components['pass']) {
           $auth_string = 'Authorization: Basic '.base64_encode($components['user'].':'.$components['pass']).END_OF_LINE_MARKER;
   }
}
else {
    $host = '';
}

if (isset($components['port'])) {
    $port = (int)$components["port"];
}
else {
    $port = 80;
}

if (isset($components['path'])) {
    $path = $components["path"];
}
else {
    $path = '';
}

if (isset($components['query'])) {
    $query = $components["query"];
}
else {
    $query = '';
}

$fp = @fsockopen($host,$port);

if ($port != 80) {
     $sport = ":".$port;
}
else {
    $sport = "";
}

if (!$fp) {
  //host domain not found
  $status = "NOHOST";
}
else {
  if ($query && strlen($query) > 0) {
     $path .= "?".$query;
  }

  $path = str_replace("//","/",$path);
  $path = ereg_replace("[?][/]$","/",$path);
  $path = ereg_replace("[/][(]$","/",$path);

  $cookiesSendString = phpDigMakeCookies($cookies,$path);

  //complete get
  $request =
  "HEAD $path $http_scheme/1.1".END_OF_LINE_MARKER
  ."Host: $host$sport".END_OF_LINE_MARKER
  .$cookiesSendString
  .$auth_string
  ."Accept: */*".END_OF_LINE_MARKER
  ."Accept-Charset: ".PHPDIG_ENCODING.END_OF_LINE_MARKER
  ."Accept-Encoding: identity".END_OF_LINE_MARKER
  ."User-Agent: PhpDig/".PHPDIG_VERSION." (+http://www.phpdig.net/robot.php)".END_OF_LINE_MARKER.END_OF_LINE_MARKER;

    fputs($fp,$request);

    //test return code

    $flag_to_stop_loop = 0;
    $redirs = 0;

    while (!$stop && !feof($fp)) {

    $flag_to_stop_loop++;

          $answer = fgets($fp,8192);

          if (ereg("HTTP[S]?/[0-9.]+ (([0-9])[0-9]{2})", $answer,$regs)) {

              if (($regs[1] == 404) && !(SILENCE_404S)) {
                  print "<br>\n".$answer." - ".$url."<br>\nSee http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html for explanation.<br>\n 404s are either dead links or something looked like a link to PhpDig so PhpDig tried to crawl it.<br>\n";
              }
              elseif ($regs[1] == 403) {
                  print "<br>\n".$answer." - ".$url."<br>\nSee http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html for explanation.<br>\n";
              }

          }

            if (isset($req1) && $req1) {
                 //close, and open a new connection
                 //on the new location
                 fclose($fp);
                 $fp = @fsockopen($host,$port);
                 if (!$fp) {
                      //host domain not found
                      $status = "NOHOST";
                      break;
                 }
                 else {
                      fputs($fp,$req1);
                      unset($req1);

                      unset($answer);
                      unset($request);

                      $answer = fgets($fp,8192);

                }
            }

            if (ereg("HTTP[S]?/[0-9.]+ (([0-9])[0-9]{2})", $answer,$regs)) {
                if ($regs[2] == 2 || $regs[2] == 3) {
                    $code = $regs[2];
                }
                elseif ($regs[1] >= 401 && $regs[1] <= 403) {
                    $status = "UNAUTH";
                    break;
                }
                else {
                    $status = "NOFILE";
                    break;
                }
            }

            elseif (eregi("^ *location: *(.*)",$answer,$regs) && $code == 3) {
                $redirs++;
                if ($redirs > 5) {
                     $stop = true;
                     $status = "LOOP";
                }
                $newpath = trim($regs[1]);
                $newurl = parse_url($newpath);

                if ((isset($newurl['scheme'])) && ($newurl['scheme'] == "https")) {
                    $new_http_scheme = "HTTPS";
                }
                else {
                    $new_http_scheme = "HTTP";
                }

                //search if relocation is absolute or relative
                if (!isset($newurl["host"])
                     && isset($newurl["path"])
                     && !ereg('^/',$newurl["path"])) {
                     $path = dirname($path).'/'.$newurl["path"];
                }
                else {
                    $path = $newurl["path"];
                }

                if (!isset($newurl['host']) || !$newurl['host'] || $host == $newurl['host']) {
                    if (isset($newurl['query']) && strlen($newurl['query']) > 0) {
                      $path .= "?".$newurl['query'];
                    }

                    $path = str_replace("//","/",$path);
                    $path = ereg_replace("[?][/]$","/",$path);
                    $path = ereg_replace("[/][(]$","/",$path);

                    $cookiesSendString = phpDigMakeCookies($cookies,$path);

                    $req1 = "HEAD $path $new_http_scheme/1.1".END_OF_LINE_MARKER
                       ."Host: $host$sport".END_OF_LINE_MARKER
                       .$cookiesSendString
                       .$auth_string
                       ."Accept: */*".END_OF_LINE_MARKER
                       ."Accept-Charset: ".PHPDIG_ENCODING.END_OF_LINE_MARKER
                       ."Accept-Encoding: identity".END_OF_LINE_MARKER
                       ."User-Agent: PhpDig/".PHPDIG_VERSION." (+http://www.phpdig.net/robot.php)".END_OF_LINE_MARKER.END_OF_LINE_MARKER;
                }
                else {
                   $stop = true;
                   $status = "NEWHOST";
                   $host = $newurl['host'];
                }
            }

            //parse cookies
            elseif (eregi("Set-Cookie: *(([^=]+)=[^; ]+) *(; *path=([^; ]+))* *(; *domain=([^; ]+))*",$answer,$regs)) {
                if(strlen($regs[1]) == 0) { $regs[1] = ''; }
                if(strlen($regs[4]) == 0) { $regs[4] = ''; }
                if(strlen($regs[6]) == 0) { $regs[6] = ''; }
                $cookies[$regs[2]] = array('string'=>$regs[1],'path'=>$regs[4],'domain'=>$regs[6]);
            }

            //Parse content-type header
            elseif (eregi("Content-Type: *([a-z]+)/([a-z.-]+)",$answer,$regs)) {
               if ($regs[1] == "text") {
                  switch ($regs[2]) {
                       case 'plain':
                         $status = 'PLAINTEXT';
                       break;
                       case 'html':
                         $status = 'HTML';
                       break;
                       default :
                         $status = "NOFILE";
                         $stop = true;
                  }
               }
               else if ($regs[1] == "application") {
                    if (($regs[2] == 'vnd.ms-word' || $regs[2] == 'msword') && PHPDIG_INDEX_MSWORD == true) {
                        $status = "MSWORD";
                    }
                    else if ($regs[2] == 'pdf' && PHPDIG_INDEX_PDF == true) {
                        $status = "PDF";
                    }
                    else if (($regs[2] == 'vnd.ms-excel' || $regs[2] == 'excel') && PHPDIG_INDEX_MSEXCEL == true) {
                        $status = "MSEXCEL";
                    }
                    else if (($regs[2] == 'vnd.ms-powerpoint' || $regs[2] == 'mspowerpoint') && PHPDIG_INDEX_MSPOWERPOINT == true) {
                        $status = "MSPOWERPOINT";
                    }
                    else {
                        $status = "NOFILE";
                        $stop = true;
                    }
               }
               else {
                    $status = "NOFILE";
                    $stop = true;
               }
             }

             elseif (eregi('Last-Modified: *([a-z0-9,: ]+)',$answer,$regs)) {
                //search last-modified header
                $lm_date = $regs[1];
             }
/*
             // ONLY USE IF Content-Type is NOT returned - CAN CAUSE PROBLEMS OTHERWISE
             elseif (!eregi("Content-Type: *([a-z]+)/([a-z.-]+)",$answer,$regs)) {
                $status = 'HTML'; // no content-type so force to be html
             }
*/
             if (!eregi('[a-z0-9]+',$answer)) {
                 $stop = true;
             }

         if ($flag_to_stop_loop == 50) { break; }

    }
@fclose($fp);
}

//returns variable or array
if ($mode == 'date') {
     return compact('status', 'lm_date', 'path', 'host', 'cookies');
}
else {
    return $status;
}
}

//========================================
// Get content of an url
function phpdigGetUrl($url,$cookies=array()) {

$components = parse_url($url);

if ($components['scheme'] == "https") {
    $http_scheme = "HTTPS";
}
else {
    $http_scheme = "HTTP";
}

$auth_string = '';
$stop = false;
$lines = array();
$no_host_flag = 0;

if (isset($components['host'])) {
    $host = $components["host"];
    if (isset($components['user']) && isset($components['pass']) &&
        $components['user'] && $components['pass']) {
           $auth_string = 'Authorization: Basic '.base64_encode($components['user'].':'.$components['pass']).END_OF_LINE_MARKER;
   }
}
else {
    $host = '';
}

if (isset($components['port'])) {
    $port = (int)$components["port"];
}
else {
    $port = 80;
}

if (isset($components['path'])) {
    $path = $components["path"];
}
else {
    $path = '';
}

if (isset($components['query'])) {
    $query = $components["query"];
}
else {
    $query = '';
}

$fp = @fsockopen($host,$port);

if ($port != 80) {
     $sport = ":".$port;
}
else {
    $sport = "";
}

if (!$fp) {
  //host domain not found
  $no_host_flag = 1;
}
else {
  if ($query && strlen($query) > 0) {
     $path .= "?".$query;
  }

  $path = str_replace("//","/",$path);
  $path = ereg_replace("[?][/]$","/",$path);
  $path = ereg_replace("[/][(]$","/",$path);

  $cookiesSendString = phpDigMakeCookies($cookies,$path);

  //complete get
  $request =
  "GET $path $http_scheme/1.1".END_OF_LINE_MARKER
  ."Host: $host$sport".END_OF_LINE_MARKER
  .$cookiesSendString
  .$auth_string
  ."Accept: */*".END_OF_LINE_MARKER
  ."Accept-Charset: ".PHPDIG_ENCODING.END_OF_LINE_MARKER
  ."Accept-Encoding: identity".END_OF_LINE_MARKER
  ."User-Agent: PhpDig/".PHPDIG_VERSION." (+http://www.phpdig.net/robot.php)".END_OF_LINE_MARKER.END_OF_LINE_MARKER;

    fputs($fp,$request);

    //get return page

    $flag_to_stop_loop = 0;
    $flag_to_stop_check = 0;
    $flag_to_mark_start = 0;
    $flag_for_chunk = 0;
    $lotsa_chars = " ¡¢£¤¥¦§¨©ª«¬_®¯°±²³´µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿ";

    while (!$stop && !feof($fp)) {

          $flag_to_stop_loop++;

          $answer = fgets($fp,8192);

          if (($flag_to_stop_check == 0) && (eregi('Transfer-encoding: *chunked',$answer))) {
              $flag_for_chunk = 1;
          }

          if (($flag_to_stop_check == 0) && (eregi("^[[:space:]]+$",$answer))) {
              $flag_to_stop_check = 1;
              $flag_to_mark_start = $flag_to_stop_loop + 1;
          }

          if (($flag_to_stop_check == 1) && ($flag_to_stop_loop >= $flag_to_mark_start)) {
             if (!eregi('[0-9a-z[:space:]'.$lotsa_chars.']+',$answer)) {
                 $stop = true;
             }
             else {
                if ($flag_for_chunk == 1) {
                   if (eregi("^[0][[:space:]]+$",$answer)) {
                       $stop = true;
                   }
                   elseif (!eregi("^[0-9a-f]+[[:space:]]+$",$answer)) {
                      $lines[] = $answer;
                   }
                }
                else {
                    $lines[] = $answer;
                }
             }
          }

          if ($flag_to_stop_loop == 10000) { break; }

    }
@fclose($fp);
}

//returns variable or array
if ($no_host_flag == 1) {
    return $no_host_flag;
}
else {
    return $lines;
}
}

//=================================================
// makes a string for cookies
function phpDigMakeCookies($cookiesToSend,$path) {
$cookiesSendString = '';
  if (is_array($cookiesToSend)) {
      foreach($cookiesToSend as $cookieString) {
           if (isset($cookieString['string'])
               && ( !isset($cookieString['path'])
                   || trim($cookieString['path']) == '/'
                   ||
                     ereg('^'.preg_quote(ereg_replace('^/','',$cookieString['path'])),ereg_replace('^/','',$path))
               )) {
               $cookiesSendString .= "Cookie: ".$cookieString['string'].END_OF_LINE_MARKER;
           }
      }
  }
return $cookiesSendString;
}

//=================================================
// Set headers for a cookie
function phpDigSetHeaders($cookiesToSend=array(),$path='') {
     if (is_array($cookiesToSend) && count($cookiesToSend) > 0) {
         @ini_set('user_agent','PhpDig/'.PHPDIG_VERSION.' (+http://www.phpdig.net/robot.php)'.END_OF_LINE_MARKER.phpDigMakeCookies($cookiesToSend,$path));
     }
}
//=================================================
// retrieve links from a file
function phpdigExplore($tempfile,$url,$path="",$file ="") {

$index = 0;
if (!is_file($tempfile)) {
     return -1;
}
else {
    $file_content = @file($tempfile);
    $my_file_base_content = implode("",$file_content);
    if (eregi("<head>(.*)</head>",$my_file_base_content,$base_regs1)) {
      $base_regs1 = $base_regs1[1];
      if (eregi("<base href[[:space:]]*=[[:space:]]*['\"]*([a-z]{3,5}://[.a-z0-9-]+[^'\"]*)['\"]*[[:space:]]*[/]?>",$base_regs1,$base_regs2)) {
        $new_base_path = parse_url($base_regs2[1]);
        if ((!isset($new_base_path["path"])) || ($new_base_path["path"] == "/")) {
          $path = "";
        }
        else {
          $new_base_path = eregi_replace("^/","",$new_base_path["path"]);
          if (eregi("/$",$new_base_path)) {
            $path = $new_base_path;
          }
          else {
            $path = dirname($new_base_path)."/";
          }
        }
      }
   }
}
if (!is_array($file_content)) {
     return -1;
}
else {
    $links = '';
    $http_scheme_array = '';
    foreach ($file_content as $eval) {
         //search hrefs and frames src
         while (eregi("(<frame[^>]*src[[:blank:]]*=|href[[:blank:]]*=|http-equiv=['\"]refresh['\"] *content=['\"][0-9]+;[[:blank:]]*url[[:blank:]]*=|window[.]location[[:blank:]]*=|window[.]open[[:blank:]]*[(])[[:blank:]]*[\'\"]?((([a-z]{3,5}://)+(([.a-zA-Z0-9-])+(:[0-9]+)*))*([:%/?=&;\\,._a-zA-Z0-9\|+ ()~-]*))(#[.a-zA-Z0-9-]*)?[\'\" ]?",$eval,$regs)) {

           $eval = str_replace($regs[0],"",$eval);
           //test no host or same than site
           if (strlen($regs[4]) == 0) { $regs[4] = ''; } // the scheme
           if (strlen($regs[5]) == 0) { $regs[5] = ''; } // domain name
           if (strlen($regs[8]) == 0) { $regs[8] = ''; } // path/file

             if (($regs[5] != "") && ($regs[8] == "")) {
                  $links[$index] = array("path" => "", "file" => "");
             }
             elseif (substr($regs[8],0,1) == "/") {
                  $links[$index] = phpdigRewriteUrl($regs[8]);
             }
             else {
                  $links[$index] = phpdigRewriteUrl($path.$regs[8]);
             }

             if (is_array($links[$index])) {
                if ($regs[5] != "" && $url != 'http://'.$regs[5].'/' && $url != 'https://'.$regs[5].'/')  {
                    $links[$index]['newhost'] = $regs[5].'/';
                }
                if ($regs[4] == "https") {
                    $http_scheme_array[$index] = array("the_http_scheme" => "https");
                }
                else {
                    $http_scheme_array[$index] = array("the_http_scheme" => "http");
                }
                $links[$index] = array_merge($links[$index],$http_scheme_array[$index]);
                $index++;
             }
             else {
                unset($links[$index]);
                unset($http_scheme_array[$index]);
             }

         }
    }
    return $links;
}
}

//=================================================
//test a link, search if is a file or dir, exclude robots.txt directives
function phpdigDetectDir($link,$exclude='',$cookies=array(),$site_id='',$id_connect='')
{
$test = parse_url($link['path'].$link['file']);

//test the exclude with robots.txt
if (phpdigReadRobots($exclude,$link['path'].$link['file']) == 1
    || isset($exclude['@ALL@'])
    ) {
    $link['ok'] = 0;
}
//dir (avoid extensions)
elseif (!isset($test['query'])
     && !eregi('[.][a-z0-9]{1,4}$',$link['path'].$link['file'])
     && $status = phpdigTestUrl($link['url'].$link['path'].$link['file'].'/','date',$cookies)
     && isset($status['status']) && $status['status'] == "HTML"
     ) {
      $link['path'] = ereg_replace ('/+$','/',$link['path'].$link['file'].'/');
      if ($link['path'] == '/') {
          $link['path'] = '';
      }
      $link['file'] = "";
      $link['ok'] = 1;
}
//file
else {
     $status = phpdigTestUrl($link['url'].$link['path'].$link['file'],'date',$cookies);
     if (!in_array($status['status'],array('NOHOST','NOFILE','LOOP','NEWHOST'))) {
         $link['ok'] = 1;
     }
     // none
     else {
         $link['ok'] = 0;
     }
}

if (is_numeric($site_id) && LIMIT_TO_DIRECTORY) {
   $query = "SELECT DISTINCT in_id, in_path FROM ".PHPDIG_DB_PREFIX."includes WHERE in_site_id='$site_id'";
   if (is_array($list_include = phpdigMySelect($id_connect,$query))) {
      foreach($list_include as $add_include) {
          if($link['path'] != $add_include['in_path']) {
              $link['ok'] = 0;
          }
      }
   }
}

if (!$link['ok'] && isset($status)) {
    $link['status'] = $status['status'];
    $link['host'] =   $status['host'];
    $link['path'] =   $status['path'];
    $link['cookies'] = $status['cookies'];
}

return $link;
}

//=================================================
//search robots.txt in a site
function phpdigReadRobotsTxt($site) { //don't forget the end backslash
  $site = eregi_replace("^https","http",$site);
  if (phpdigTestUrl($site.'robots.txt') == 'PLAINTEXT') {
    @ini_set("auto_detect_line_endings","1"); // needs PHP 4.3.0+
    $robots = @file($site.'robots.txt');
    while (list($id,$line) = @each($robots)) {
      if ((strpos(trim($line),"#") === 0) || (trim($line) == ""))
        continue;
      if (ereg('^user-agent:[ ]*([a-z0-9*]+)',strtolower($line),$regs)) {
        if ($regs[1] == "*") {
          $user_agent = "'$regs[1]'";
        }
        else {
          $user_agent = $regs[1];
        }
      }
      if (eregi('[[:blank:]]*disallow:[[:blank:]]*([/]?([a-z0-9_/*+%.-]*))',$line,$regs)) {
          if ($regs[1] == '/') {
             $exclude[$user_agent]['@ALL@'] = 1;
          }
          elseif (($user_agent == "'*'") && ($regs[1] == '')) {
             $exclude['@NONE@'] = 1;
             return $exclude;
          }
          else {
             $exclude[$user_agent][str_replace('*','.*',str_replace('+','\+',str_replace('.','\.',$regs[2])))] = 1;
          }
      }
      elseif (($user_agent == 'phpdig') && (eregi('[[:blank:]]*disallow:[[:blank:]]*',$line,$regs))) {
        $exclude[$user_agent]['@NONE@'] = 1;
        return $exclude[$user_agent];
      }
    }
    if (isset($exclude['phpdig']) && is_array($exclude['phpdig']))
      return $exclude['phpdig'];
    elseif (isset($exclude['\'*\'']) && is_array($exclude['\'*\'']))
      return $exclude['\'*\''];
  }
$exclude['@NONE@'] = 1;
return $exclude;
}

//=================================================
// Parse if path is in exclude
function phpdigReadRobots($exclude,$path) {
   $result = 0;
   //echo '<b>test '.$path.'</b><br />';
   while (list($path_exclude) = each($exclude))
          {
          //echo $path_exclude.'<br />';
          if ((ereg('^'.$path_exclude,$path)) || (ereg('^/'.$path_exclude,$path)))
              {
              $result = 1;
              //echo '<font color=red>EXCLUDE !</font><br />';
              }
          }
   return $result;
}

//=================================================
// parse result of getmetatags to extract those concerning Robots
function phpdigReadRobotsTags($tags)
{
if (is_array($tags))
{
while (list($id,$content) = each($tags))
       {
       if (eregi('robots',$id))
           {
           $directive = 0;

           if (eregi('nofollow',$content))
               $directive += 1;
           if (eregi('noindex',$content))
               $directive += 2;
           if (eregi('none',$content))
               $directive += 4;
           //test the bitwise return > 0 : & 5 nofollow, & 6 noindex.
           return $directive;
           }
       }
}
}

//=================================================
// retrieves an url and returns temp file parameters
function phpdigTempFile($uri,$result_test,$prefix='temp/',$suffix1='1.tmp',$suffix2='2.tmp') {

// $temp_filename = md5(time()+getmypid()).$suffix;
srand((double)microtime()*1000000);
$the_temp_filename = '';
for ($i=1; $i<=TEMP_FILENAME_LENGTH - 1; $i++) {
  $the_temp_filename .= rand(1,9);
}
$temp_filename1 = $the_temp_filename.$suffix1;
$temp_filename2 = $the_temp_filename.$suffix2;

if (USE_IS_EXECUTABLE_COMMAND == 1 && function_exists("is_executable")) {
  $is_exec_command_msword = is_executable(PHPDIG_PARSE_MSWORD);
  $is_exec_command_msexcel = is_executable(PHPDIG_PARSE_MSEXCEL);
  $is_exec_command_pdf = is_executable(PHPDIG_PARSE_PDF);
  $is_exec_command_mspowerpoint = is_executable(PHPDIG_PARSE_MSPOWERPOINT);
}
else {
  $is_exec_command_msword = 1;
  $is_exec_command_msexcel = 1;
  $is_exec_command_pdf = 1;
  $is_exec_command_mspowerpoint = 1;
}

if (is_array($result_test)
     && $result_test['status'] == 'HTML'
     || $result_test['status'] == 'PLAINTEXT'
     || $result_test['status'] == 'MSWORD' && PHPDIG_INDEX_MSWORD == true && file_exists(PHPDIG_PARSE_MSWORD) && $is_exec_command_msword
     || $result_test['status'] == 'MSEXCEL' && PHPDIG_INDEX_MSEXCEL == true && file_exists(PHPDIG_PARSE_MSEXCEL) && $is_exec_command_msexcel
     || $result_test['status'] == 'PDF' && PHPDIG_INDEX_PDF == true && file_exists(PHPDIG_PARSE_PDF) && $is_exec_command_pdf
     || $result_test['status'] == 'MSPOWERPOINT' && PHPDIG_INDEX_MSPOWERPOINT == true && file_exists(PHPDIG_PARSE_MSPOWERPOINT) && $is_exec_command_mspowerpoint
    ) {
    // $file_content = @file($uri); /////////////////////////////////////////////////

    if (in_array($result_test['status'],array('MSWORD','MSEXCEL','PDF','MSPOWERPOINT'))) {
        $file_content = array();
        $fp = fopen($uri,"rb");
        while (!feof($fp)) {
            $file_content[] = fread($fp,8192);
        }
        fclose($fp);
    }
    else {
        $file_content = phpdigGetUrl($uri,$result_test['cookies']);
    }

    if (!is_dir($prefix)) {
         if (!@mkdir($prefix,0660)) {
               die("Unable to create temp directory\n");
         }
    }
    $tempfile1 = $prefix.$temp_filename1;
    $tempfile2 = $prefix.$temp_filename2;

    $temp_filename_counter = 0;
    while(file_exists($tempfile1)) {
      $the_temp_filename = '';
      for ($i=1; $i<=TEMP_FILENAME_LENGTH - 1; $i++) {
        $the_temp_filename .= rand(1,9);
      }
      $temp_filename1 = $the_temp_filename.$suffix1;
      $temp_filename2 = $the_temp_filename.$suffix2;
      $tempfile1 = $prefix.$temp_filename1;
      $tempfile2 = $prefix.$temp_filename2;
      $temp_filename_counter++;
      if ($temp_filename_counter == 100) { die("Unable to create unique temp filename\n"); }
    }

    if (is_array($file_content) && count($file_content) > 0) {
       $f_handler = fopen($tempfile1,'wb');
       fwrite($f_handler,implode('',$file_content));
       fclose($f_handler);
       $tempfilesize = filesize($tempfile1);
    }
    else {
       return array('tempfile'=>0,'tempfilesize'=>0);
    }

    // There use external tools
    $usetool = false;
    switch ($result_test['status']) {
         case 'MSWORD':
         $usetool = true;
         $command = PHPDIG_PARSE_MSWORD.' '.PHPDIG_OPTION_MSWORD.' '.$tempfile2;
         break;

         case 'MSEXCEL':
         $usetool = true;
         $command = PHPDIG_PARSE_MSEXCEL.' '.PHPDIG_OPTION_MSEXCEL.' '.$tempfile2;
         break;

         case 'PDF':
         $usetool = true;
         $command = PHPDIG_PARSE_PDF.' '.PHPDIG_OPTION_PDF.' '.$tempfile2;
         break;

         case 'MSPOWERPOINT':
         $usetool = true;
         $command = PHPDIG_PARSE_MSPOWERPOINT.' '.PHPDIG_OPTION_MSPOWERPOINT.' '.$tempfile2;
         break;
    }
    if ($usetool) {
        rename($tempfile1,$tempfile2);
        exec($command,$result,$retval);
        unlink($tempfile2);
        if (!$retval) {
             // the replacement if š is for unbreaking spaces
             // returned by catdoc parsing msword files
             // and '0xAD' "tiret quadratin" returned by pstotext
             // in iso-8859-1
             // Adjust with your encoding and/or your tools
             if ((is_array($result)) && (count($result) > 0)) {
                $f_handler = fopen($tempfile1,'wb');
                fwrite($f_handler,str_replace('š',' ',str_replace(chr(0xad),'-',implode(' ',$result))));
                fclose($f_handler);
             }
        }
        else {
              return array('tempfile'=>0,'tempfilesize'=>0);
        }
    }
    // return array('tempfile'=>$tempfile,'tempfilesize'=>$tempfilesize);
    switch ($result_test['status']) {
             case 'MSWORD':
             if(strlen(PHPDIG_MSWORD_EXTENSION) > 0) {
               $my_new_tempfile = $tempfile2.PHPDIG_MSWORD_EXTENSION;
             }
             else {
               $my_new_tempfile = $tempfile1;
             }
             break;

             case 'MSEXCEL':
             if(strlen(PHPDIG_MSEXCEL_EXTENSION) > 0) {
               $my_new_tempfile = $tempfile2.PHPDIG_MSEXCEL_EXTENSION;
             }
             else {
               $my_new_tempfile = $tempfile1;
             }
             break;

             case 'PDF':
             if(strlen(PHPDIG_PDF_EXTENSION) > 0) {
               $my_new_tempfile = $tempfile2.PHPDIG_PDF_EXTENSION;
             }
             else {
               $my_new_tempfile = $tempfile1;
             }
             break;

             case 'MSPOWERPOINT':
             if(strlen(PHPDIG_MSPOWERPOINT_EXTENSION) > 0) {
               $my_new_tempfile = $tempfile2.PHPDIG_MSPOWERPOINT_EXTENSION;
             }
             else {
               $my_new_tempfile = $tempfile1;
             }
             break;

             default:
             $my_new_tempfile = $tempfile1;
    }

    if (!file_exists($my_new_tempfile)) {
      return array('tempfile'=>0,'tempfilesize'=>0);
    }
    else {
      return array('tempfile'=>$my_new_tempfile,'tempfilesize'=>$tempfilesize);
    }
}
else {
      return array('tempfile'=>0,'tempfilesize'=>0);
}
}

//=================================================
// update a spider row
function phpdigUpdSpiderRow($id_connect,$site_id,
                            $path,$file,$first_words,
                            $upddate,$md5,$lastmodified,
                            $num_words,$filesize) {

$path = str_replace(" ","%20",$path); 
$file = str_replace(" ","%20",$file);

// parse and remove quotes
$path = preg_replace('/[\0]/is','',$path); // remove null byte
$path = preg_replace('/[\']/is','',$path); // remove single quote
$path = preg_replace('/["]/is','',$path); // remove double quote
$path = preg_replace('/[\\\\]/is','',$path); // remove backslash

// parse and remove quotes
$file = preg_replace('/[\0]/is','',$file); // remove null byte
$file = preg_replace('/[\']/is','',$file); // remove single quote
$file = preg_replace('/["]/is','',$file); // remove double quote
$file = preg_replace('/[\\\\]/is','',$file); // remove backslash
$file = ereg_replace("[?]$","",$file); // remove trailing question mark

if (PHPDIG_SESSID_REMOVE) {
    $file = phpdigSessionRemove($file);
}

//retrieves the spider_id
$query_select = "SELECT spider_id FROM ".PHPDIG_DB_PREFIX."spider WHERE site_id=".(int)$site_id." AND path = '$path' AND file = '$file'";
$result_double = phpdigMySelect($id_connect,$query_select);

if (!get_magic_quotes_runtime()) {
    $first_words = addslashes($first_words);
}

if (!is_array($result_double)) {
    $requete = "INSERT INTO ".PHPDIG_DB_PREFIX."spider SET path='$path',file='$file',first_words='$first_words',upddate='$upddate',md5='$md5',site_id='$site_id',num_words='$num_words',last_modified='$lastmodified',filesize=".(int)$filesize;
    $result_insert = mysql_query($requete,$id_connect);
    $spider_id = mysql_insert_id($id_connect);
}
else {
    //update reccord
    $spider_id = $result_double[0]['spider_id'];
    $query = "UPDATE ".PHPDIG_DB_PREFIX."spider SET first_words='$first_words',upddate='$upddate',md5='$md5',num_words='$num_words',last_modified='$lastmodified',filesize=".(int)$filesize." WHERE spider_id=".(int)$spider_id;
    $result_update = mysql_query($query,$id_connect);
}
return $spider_id;
}

//=================================================
//tests if the reccord of spider_id is a double.
function phpdigTestDouble($id_connect,$site_id,$md5,$new_upddate,$last_modified) {
//tests if there is a double an if yes, update the modifying date
$query_double = "SELECT spider_id FROM ".PHPDIG_DB_PREFIX."spider WHERE site_id='$site_id' AND md5 = '$md5'";
$result_double = phpdigMySelect($id_connect,$query_double);
if (is_array($result_double)) {
     $exists_spider_id = $result_double[0]['spider_id'];
     $query = "UPDATE ".PHPDIG_DB_PREFIX."spider SET upddate=$new_upddate,last_modified='$last_modified' WHERE spider_id=$exists_spider_id";
     $result_update = mysql_query($query,$id_connect);
     return $exists_spider_id;
}
else {
    return 0;
}
}

//=================================================
//index a file and returns a spider_id
function phpdigIndexFile($id_connect,$tempfile,$tempfilesize,
                         $site_id,$origine,$localdomain,
                         $path,$file,$content_type,$upddate,
                         $last_modified,$tags,$ftp_id='') {
//globals
global $phpdig_words_chars,$common_words,$relative_script_path,$s_yes,$s_no,$br;

//current_date
$date = date("YmdHis",time());
//settype($tempfile,'string');

if (!isset($tempfile) || !is_file($tempfile)) {
   return 0;
}

settype($page_desc,'string');
settype($page_keywords,'string');

if (APPEND_TITLE_META) {
    if (is_array($tags)) {
        if (isset($tags['description'])) {
          $page_desc = phpdigCleanHtml($tags['description']);
        }
        if (isset($tags['keywords'])) {
          $page_keywords = phpdigCleanHtml($tags['keywords']);
        }
    }
}

$file_content = file($tempfile);
$textalts = "";

//verify the array $text is empty
$n_chunk = 0;
$n_cline = 0;
$text[0] = '';
$exclude = false;

foreach ($file_content as $num => $line) {
    if (trim($line)) {
        if ($content_type == 'HTML' && trim($line) == PHPDIG_EXCLUDE_COMMENT) {
            $exclude = true;
        }
        else if (trim($line) == PHPDIG_INCLUDE_COMMENT) {
            $exclude = false;
            continue;
        }
        if (!$exclude) {
            //extract alt attributes of images
            if (eregi("(alt=|title=)[[:blank:]]*[\'\"][[:blank:]]*([ a-z0-9\xc8-\xcb]+)[[:blank:]]*[\'\"]",$line,$regs)) {
                $textalts .= $regs[2];
            }
            //extract the domains names not local and not banned to add in keywords
            while(eregi("<a([^>]*href[[:blank:]]*=[[:blank:]]*[\'\"]?((([a-z]{3,5}://)+(([.a-zA-Z0-9-])+(:[0-9]+)*))*([:%/?=&;\\,._a-zA-Z0-9 ()~-]*))[#\'\" ]?)",$line,$regs)) {
                 $line = str_replace($regs[1],"",$line);
                 if ($regs[5] && $regs[5] != $localdomain && !eregi(BANNED,$regs[5]) && ereg('[a-z]+',$regs[5])) {
                       if (!isset($nbre_mots[$regs[5]])) {
                           $nbre_mots[$regs[5]] = 1;
                       }
                       else {
                           $nbre_mots[$regs[5]] ++;
                       }
                  }
            }
            $n_cline ++;
            //cut the text after $n_chunk characters
            if (strlen($text[$n_chunk]) > CHUNK_SIZE) {
                 //cut only before an opening tag
                 if ($content_type != 'HTML' or eregi("^[[:blank:]]*<[a-z]+[^>]*>",$line)) {
                      $n_cline = 0;
                      $n_chunk ++;
                      $text[$n_chunk] = '';
                 }
            }
            $text[$n_chunk] .= trim($line)." ";
        }
    }
}

//store the number of chunks
$max_chunk = $n_chunk;
//free the array containing file content
unset($file_content);

$doc_title = "";

//purify from html tags and store the title
if (is_array($text) && $content_type == 'HTML') {
   foreach ($text as $n_chunk => $chunk) {
       $chunk = phpdigCleanHtml($chunk);
       $text[$n_chunk] = $chunk['content'];
       $doc_title .= $chunk['title'];
   }
}

//set the title in order <title>, filename, or unknown
if (isset($doc_title) && $doc_title) {
     $titre_resume = $doc_title;
}
elseif (isset($file) && $file) {
    $titre_resume =  $file;
}
else {
    $titre_resume = "Untitled";
}

//title and small description
if (!is_array($page_desc)) {
     $page_desc['content'] = '';
}
else {
    $page_desc['content'] = ' '.$page_desc['content'];
}

$db_some_text = preg_replace("/([ ]{2}|\n|\r|\r\n)/"," ",implode(" ",$text));
$db_some_text = substr($db_some_text,0,strpos($db_some_text,". ")+1);
if (strlen($db_some_text) > SUMMARY_DISPLAY_LENGTH) {
  $db_some_text = substr($db_some_text,0,SUMMARY_DISPLAY_LENGTH)."...";
}

$first_words = $titre_resume."\n".$page_desc['content'].$db_some_text;

/*
$first_words = $titre_resume."\n".ereg_replace('(@@@.*)','',wordwrap($page_desc['content'].$text[0], SUMMARY_LENGTH, '@@@'));
*/
//hashed string to detect doubles
$md5 = md5($titre_resume.$page_desc['content'].$text[$max_chunk]).'_'.$tempfilesize;

//double test :
$phpdigTestDouble = phpdigTestDouble($id_connect,$site_id,$md5,$upddate,$last_modified);

//if no double detected, continue indexing
if ($phpdigTestDouble == 0) {
$text_title = "";

//weight of title and description is there
if (APPEND_TITLE_META) {
    for ($itl = 0;$itl < TITLE_WEIGHT; $itl++) {
        $text_title .= $doc_title." ".$page_desc['content']." ";
    }
    $add_text = $text_title;
    if (is_array($textalts) && isset($textalts['content'])) {
        $add_text .= $textalts['content'];
    }
    if (is_array($page_keywords) && isset($page_keywords['content'])) {
        $add_text .= " ".$page_keywords['content'];
    }
    array_push($text,$add_text);
}

//words list and occurence of each of them
$total = 0;
foreach($text as $n_chunk => $text2) {

$text2 = phpdigEpureText($text2,SMALL_WORDS_SIZE);

$separators = " ";
unset($token);
for ($token = strtok($text2, $separators); $token !== FALSE; $token = strtok($separators))
      {
      if (!isset($nbre_mots[$token]))
          $nbre_mots[$token] = 1;
      else
          $nbre_mots[$token]++;
      $total++;
      }
}

$distinct_words = @count($nbre_mots);

//modify the spider reccord
$spider_id = phpdigUpdSpiderRow($id_connect,$site_id,
                                $path,$file,$first_words,$upddate,
                                $md5,$last_modified,$distinct_words,
                                $tempfilesize);

//here store extract the textual content (return a new ftp_id in case of reconnection)
$ftp_id = phpdigWriteText($relative_script_path,$spider_id,$text,$ftp_id);

//end of textual.

//delete old engine reccord
$query = "DELETE FROM ".PHPDIG_DB_PREFIX."engine WHERE spider_id=$spider_id";
mysql_query($query,$id_connect);

//database insert
$it = 0;
$sqlvalues = "";
while (list($key, $value) = @each($nbre_mots))
       {
        $key = trim($key);
        if (!get_magic_quotes_runtime()) {
          $key = addslashes($key);
        }
        //no small words nor stop words
//        if (strlen($key) > SMALL_WORDS_SIZE and strlen($key) <= MAX_WORDS_SIZE and !isset($common_words[$key]) and ereg('^[0-9a-zßðþ]',$key))
          if (strlen($key) > SMALL_WORDS_SIZE and strlen($key) <= MAX_WORDS_SIZE and !isset($common_words[$key]) and ereg('^['.$phpdig_words_chars[PHPDIG_ENCODING].'#$]',$key))
        {
        //if keyword exists, retrieve id, else insert it
        $requete = "SELECT key_id FROM ".PHPDIG_DB_PREFIX."keywords WHERE keyword = '".$key."'";
        $result_insert = mysql_query($requete,$id_connect);
        $num = mysql_num_rows($result_insert);
        if ($num == 0)
            {
            //inserts new keyword
            $requete = "INSERT INTO ".PHPDIG_DB_PREFIX."keywords (keyword,twoletters) VALUES ('".$key."','".addslashes(substr(str_replace('\\','',$key),0,2))."')";
            mysql_query($requete,$id_connect);
            $key_id = mysql_insert_id($id_connect);
            }
        else
            {
            //existing keyword
            $keyid = mysql_fetch_row($result_insert);
            mysql_free_result($result_insert);
            $key_id = $keyid[0];
            }
        //New index record
        if ($it == 0)
             {
             $sqlvalues .= "($spider_id,$key_id,$value)";
             $it = 1;
             }
        else
             $sqlvalues .= ",\n($spider_id,$key_id,$value)";

        }
       }

       unset($nbre_mots);

       //One query for the entire page
       $requete = "INSERT INTO ".PHPDIG_DB_PREFIX."engine (spider_id,key_id, weight) VALUES $sqlvalues\n";
       $result_insert = mysql_query($requete,$id_connect);
print $s_yes;
}
else
    {
    $spider_id = -1;
    print $s_no.phpdigMsg('double').$br;
    }

if (isset($text))
    {
    unset($text);
    }
return $spider_id;
}

//=================================================
//list a spider reccord
function phpdigGetSpiderRow($id_connect,$site_id,$path,$file)
{
$requete = "SELECT spider_id,
                   file,
                   first_words,
                   spider.upddate,
                   md5,
                   sites.site_id,
                   path,
                   num_words,
                   last_modified
             FROM ".PHPDIG_DB_PREFIX."spider as spider LEFT JOIN ".PHPDIG_DB_PREFIX."sites as sites ON spider.site_id = sites.site_id
             WHERE spider.site_id='$site_id' AND spider.path = '$path' AND spider.file = '$file'";
$result = phpdigMySelect($id_connect,$requete);
if (is_array($result))
     {
     return $result[0];
     }
}

//=================================================
//metatags in lowercase
function phpdigFormatMetaTags($file) {
$tag = get_meta_tags($file);
if (is_array($tag)) {
    //format type of metatags
    while (list($id,$value) = each($tag))
           $tag[strtolower($id)] = $tag[$id];

    settype($tag['robots'],'string');
    settype($tag['revisit-after'],'string');
    settype($tag['description'],'string');
    settype($tag['keywords'],'string');
    return $tag;
}
}

//=================================================
//read meta http-equiv
function phpdigGetHttpEquiv($file) {
    $return = array();
    if (is_file($file)) {
       $fh = fopen($file,'r');
       // analyze 20 lines max
       $count = 0;
       while (($line = fgets($fh,4096)) && $count++ < 20) {
            if (eregi('<meta +http-equiv *= *["\']?([^\'"]+)["\']? *content *= *["\']?([^\'"]+)["\']? */?>',$line,$regs)) {
                $return[strtolower($regs[1])] = $regs[2];
            }
       }
       fclose($fh);
    }
    return $return;
}


//=================================================
//parse the revisit-after tag
function phpdigRevisitAfter($revisit_after,$limit_days=0)
{
$delay = 0;
if (eregi('([0-9]+) *((day).*|(week).*|(month).*|(year).*)',$revisit_after,$regs))
    {
    $delay = 86400*$regs[1];
    if ($regs[4])
         $delay *= 7;
    if ($regs[5])
         $delay *= 30;
    if ($regs[6])
         $delay *= 365;
    }
//set default value
if (!$delay)
      $delay = 86400*$limit_days;

return($delay);
}

//=================================================
//delete a spider reccord and content file
function phpdigDelSpiderRow($id_connect,$spider_id,$ftp_id='')
{
global $relative_script_path,$ftp_id;
$query = "DELETE FROM ".PHPDIG_DB_PREFIX."engine WHERE spider_id=$spider_id";
$result_id = mysql_query($query,$id_connect);
$query = "DELETE FROM ".PHPDIG_DB_PREFIX."spider WHERE spider_id=$spider_id;";
$result_id = mysql_query($query,$id_connect);
phpdigDelText($relative_script_path,$spider_id,$ftp_id);
}

//=================================================
//store a content_text from a spider_id
function phpdigWriteText($relative_script_path,$spider_id,$text,$ftp_id='') {
global $br;
if (CONTENT_TEXT == 1) {

    $file_text_path = $relative_script_path.'/'.TEXT_CONTENT_PATH.$spider_id.'.txt';
    if ($f_handler = @fopen($file_text_path,'w')) {
     reset($text);
     while (list($n_chunk,$text_to_store) = each($text)) {
           fputs($f_handler,wordwrap($text_to_store));
     }
     fclose($f_handler);
     @chmod($file_text_path,0666);
        //here the ftp case
        if (FTP_ENABLE) {
            $ftp_id = phpdigFtpKeepAlive($ftp_id);
            @ftp_delete($ftp_id,$spider_id.'.txt');
            $res_ftp = false;
            $try_count = 0;
            while (!$res_ftp && $try_count++ < 10) {
                 $res_ftp = @ftp_put($ftp_id,$spider_id.'.txt',$file_text_path,FTP_ASCII);
                 if (!$res_ftp) {
                      sleep(2);
                 }
            }
            if (!$res_ftp) {
                 print "Ftp_put error !".$br;
            }

         }
    }
    else {
        print "Warning : Unable to create the content file $file_text_path ! $br";
    }
}
return $ftp_id;
}

//=================================================
//delete a content_text from a spider_id
function phpdigDelText($relative_script_path,$spider_id,$ftp_id='')
{
if (CONTENT_TEXT == 1)
{
$file_text_path = $relative_script_path.'/'.TEXT_CONTENT_PATH.$spider_id.'.txt';
if (@is_file($file_text_path))
    @unlink($file_text_path);

//there delete the ftp file
if (FTP_ENABLE && $ftp_id)
    @ftp_delete($ftp_id,$spider_id.'.txt');
}
}

//=================================================
//connect to the ftp if the ftp is on and the connection ok.
//the content files are stored locally and could be uploaded
//manually later.
function phpdigFtpConnect()
{
if (CONTENT_TEXT == 1 && FTP_ENABLE == 1) {
    $count = 0;
    global $br;
    while ($count++ < 10) {
        //launch connect procedure
        if ($ftp_id = ftp_connect(FTP_HOST,FTP_PORT)) {
            //login
            if (ftp_login ($ftp_id, FTP_USER, FTP_PASS)) {
                ftp_pasv ($ftp_id, FTP_PASV);
                //echo ftp_pwd($ftp_id);
                //change to phpdig directory
                if (ftp_chdir ($ftp_id, FTP_PATH)) {
                    //if content_text doesnt exists, create it
                    if (!@ftp_chdir ($ftp_id, FTP_TEXT_PATH)) {
                         ftp_mkdir ($ftp_id, FTP_TEXT_PATH);
                         ftp_chdir ($ftp_id, FTP_TEXT_PATH);
                    }
                    return $ftp_id;
                }
             }
        }
        sleep(2);
    }
    print "Error : Ftp connect failed !".$br;
}
//else return empty string
}

//=================================================
//close the ftp if exists
function phpdigFtpClose($ftp_id)
{
if ($ftp_id)
    @ftp_quit($ftp_id);
}

//=================================================
//reconnect to ftp if the connexion fails or in case of timout
function phpdigFtpKeepAlive($ftp_id,$relative_script_path=false) {
if (!$ftp_id) {
   return phpdigFtpConnect();
}
elseif (!@ftp_pwd($ftp_id)) {
        phpdigFtpClose($ftp_id);
        return phpdigFtpConnect();
}
else {
    @ftp_pasv($ftp_id, FTP_PASV);
    if ($relative_script_path) {
        phpdigWriteText($relative_script_path,'keepalive',array('.'),$ftp_id);
    }
    return $ftp_id;
}
}

//=================================================
//Find if an url is same domain than another
function phpdigCompareDomains($url1,$url2) {
    $url1 = parse_url($url1);
    $url2 = parse_url($url2);
    if (isset($url1['host']) && isset($url2['host'])
    && eregi('^([a-z0-9_-]+)\.(.+)',$url1['host'],$from_url)
    && eregi('^([a-z0-9_-]+)\.(.+)',$url2['host'],$to_url)
    && (($from_url[2] == $to_url[2]) || ($from_url[2] == $to_url[0]) || ($from_url[0] == $to_url[2]))) {
       return true;
    }
    else {
       return false;
// be careful setting this to true as indexing
// could take a very, VeRy, VERY looooong time
//       return true;
    }
}

//=================================================
//Add a site while spidering and returns an array
//with informations of $list_sites array
function phpdigSpiderAddSite($id_connect,$url,$linksper,$linksper_flag) {
/*
    $added_site = phpdigGetSiteFromUrl($id_connect,$url,$linksper,$linksper_flag);
    if (is_array($added_site)) {
      $query= "SELECT site_id,site_url,username as user,password as pass,port,locked FROM ".PHPDIG_DB_PREFIX."sites where site_id=".$added_site['site_id'];
      $added_site = phpdigMySelect($id_connect,$query);
      if (is_array($added_site)) {
          return $added_site[0];
      }
    }
*/
    $pu = parse_url($url);

    settype($pu['path'],'string');
    settype($pu['query'],'string');
    settype($pu['port'],'integer');

    if ($pu['port'] == 0 || $pu['port'] == 80) {
         $pu['port'] = '';
    }
    else {
         settype($pu['port'],'integer');
    }

    $url = $pu['scheme']."://".$pu['host']."/";

    if (!$pu['port']) {
         $where_port = "and (port IS NULL OR port = 0)";
    }
    else {
          $where_port = "and port='".$pu['port']."'";
    }

    $query = "SELECT site_id FROM ".PHPDIG_DB_PREFIX."sites WHERE site_url = '$url' $where_port";
    $result = mysql_query($query,$id_connect);

    if (mysql_num_rows($result) > 0) {
        list($site_id) = mysql_fetch_row($result);
        $query = "DELETE FROM ".PHPDIG_DB_PREFIX."tempspider WHERE site_id = $site_id AND file = '".$pu['query']."' and path = '".$pu['path']."'";
        mysql_query($query,$id_connect);
        $added_site['site_id'] = "zzz";
        return $added_site['site_id'];
    }
    else {
        $added_site = phpdigGetSiteFromUrl($id_connect,$url,$linksper,$linksper_flag);
        if (is_array($added_site)) {
            $query= "SELECT site_id,site_url,username as user,password as pass,port,locked FROM ".PHPDIG_DB_PREFIX."sites where site_id=".$added_site['site_id'];
            $added_site = phpdigMySelect($id_connect,$query);
            if (is_array($added_site)) {
                return $added_site[0];
            }
        }
    }
}

//=================================================
//Strip session IDs and vars from links
function phpdigSessionRemove($eval) {
    $my_test_comma = stristr(PHPDIG_SESSID_VAR,","); 
    if ($my_test_comma !== FALSE) { 
        $my_test_comma_array = explode(",",PHPDIG_SESSID_VAR); 
        $my_test_comma_count = count($my_test_comma_array); 
        for ($i=0; $i<$my_test_comma_count; $i++) { 
            $eval = phpdigSessionRemoveIt($my_test_comma_array[$i],$eval);
        }
    }
    else {
        $eval = phpdigSessionRemoveIt(PHPDIG_SESSID_VAR,$eval);
    }
    return $eval;
}

//=================================================
// What to strip from links
function phpdigSessionRemoveIt($what,$eval) {
    $what = trim($what);
    $eval = ereg_replace($what.'=[a-zA-Z0-9.,;=/-]*','',$eval);
    $eval = str_replace("&amp;&amp;","&amp;",$eval);
    $eval = str_replace("?&amp;","?",$eval);
    $eval = eregi_replace("&amp;$","",$eval);
    $eval = str_replace("&&","&",$eval); 
    $eval = eregi_replace("[?][&]","?",$eval); 
    $eval = eregi_replace("&$","",$eval);
    $eval = ereg_replace("[?]$","",$eval); // remove trailing question mark
    return $eval;
}

//=================================================
// Returns a table of 30 lines of logs
// Type is the type of logs in mostkeys, mostpages, lastqueries,
// mostterms, largestresults, mostempty, lastqueries, responsebyhour, lastclicks.
function phpdigGetLogs($id_connect,$type='lastqueries') {
$result='';
switch ($type) {
    case 'mostkeys':
          $query = 'SELECT k.keyword ,sum(e.weight) as num
          FROM '.PHPDIG_DB_PREFIX.'keywords k, '.PHPDIG_DB_PREFIX.'engine e
          WHERE k.key_id = e.key_id
          GROUP BY k.keyword
          ORDER BY num DESC LIMIT 30';
          $result = phpdigMySelect($id_connect,$query);
    break;

    case 'mostpages':
          $query = 'SELECT CONCAT(st.site_url,s.path,s.file) as page,s.num_words
          FROM '.PHPDIG_DB_PREFIX.'spider s, '.PHPDIG_DB_PREFIX.'sites st
          WHERE s.site_id = st.site_id
          ORDER BY num_words DESC LIMIT 30';
          $result = phpdigMySelect($id_connect,$query);
    break;

    case 'mostterms':
          $query = 'SELECT l_includes as search_terms,
          count(l_id) as num_time,
          sum(l_num) as total_results,
          round(avg(l_time),2) as avg_time
          FROM '.PHPDIG_DB_PREFIX.'logs
          WHERE l_includes <> \'\'
          GROUP BY search_terms
          ORDER BY num_time DESC LIMIT 30';
          $result = phpdigMySelect($id_connect,$query);
    break;

    case 'largestresults':
          $query = 'SELECT count(l_id) as queries,
          l_includes as with_terms,
          l_excludes as and_without,
          round(avg(l_num)) as average_results,
          round(avg(l_time),2) as avg_time
          FROM '.PHPDIG_DB_PREFIX.'logs
          GROUP BY with_terms, and_without
          HAVING average_results > 0
          ORDER BY average_results DESC LIMIT 30';
          $result = phpdigMySelect($id_connect,$query);
    break;

    case 'mostempty':
          $query = 'SELECT count(l_id) as queries,
          l_includes as with_terms,
          l_excludes as and_without
          FROM '.PHPDIG_DB_PREFIX.'logs
          WHERE l_num = 0
          AND l_includes <> \'\'
          GROUP BY with_terms, and_without
          ORDER BY queries DESC LIMIT 30';
          $result = phpdigMySelect($id_connect,$query);
    break;

    case 'lastqueries':
         $query = 'SELECT DATE_FORMAT(l_ts,\'%Y-%m-%d %H:%i%:%S\') as date,
          l_includes as with_terms,
          l_excludes as and_without,
          l_num as results,
          l_mode as "start/any/exact",
          l_time as search_time
          FROM '.PHPDIG_DB_PREFIX.'logs
          ORDER BY l_ts DESC LIMIT 30';
          $result = phpdigMySelect($id_connect,$query);
    break;

    case 'responsebyhour':
         $query = 'SELECT DATE_FORMAT(l_ts,\'%H:00\') as hour,
          round(avg(l_time),2) as avg_time,
          count(l_id) as num_queries
          FROM '.PHPDIG_DB_PREFIX.'logs
          WHERE l_time > 0
          GROUP BY hour';
          $result = phpdigMySelect($id_connect,$query);
          // fill empty hours
          for ($i = 0; $i < 24; $i++) {
             $hour[$i] = sprintf('%02d:00',$i);
          }
          $tempresult = array();
          if ($result) {
            foreach($result as $row) {
               while ($row['hour'] != ($this_hour = array_shift($hour))) {
                    array_push($tempresult,array('hour'=>$this_hour,
                                                 'avg_time'=>0,
                                                 'num_queries'=>0));
               }
               array_push($tempresult,$row);
            }
            if (count($hour) > 0) {
              foreach($hour as $this_hour) {
                  array_push($tempresult,array('hour'=>$this_hour,
                                               'avg_time'=>0,
                                               'num_queries'=>0));
              }
            }
          }
          $result = $tempresult;
    break;

    case 'lastclicks':
         $query = 'SELECT DATE_FORMAT(c_time,\'%Y-%m-%d %H:%i%:%S\') as date,
          c_num as link_num,
          c_url as link_url,
          c_val as link_query
          FROM '.PHPDIG_DB_PREFIX.'clicks
          ORDER BY c_time DESC LIMIT 30';
          $result = phpdigMySelect($id_connect,$query);
    break;

    }
return $result;
}
?>
