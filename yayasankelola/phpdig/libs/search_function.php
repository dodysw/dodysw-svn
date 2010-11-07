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

//===============================================
// do the search and display the results
// can be called in any page
function phpdigSearch($id_connect, $query_string, $option='start', $refine=0,
                       $refine_url='', $lim_start=0, $limite=10, $browse=0,
                       $site=0, $path='', $relative_script_path = '.', $template='', $rssdf='')
{

// check input

// $id_connect set in connect.php file
// $query_string cleaned in $query_to_parse in search_function.php file
if (($option != "start") && ($option != "any") && ($option != "exact")) { $option = SEARCH_DEFAULT_MODE; }
if (($refine != 0) && ($refine != 1)) { $refine = 0; }
// refine_url set in search_function.php file
settype($limite,'integer');
if (($limite != 10) && ($limite != 30) && ($limite != 100)) { $limite = SEARCH_DEFAULT_LIMIT; }
settype($limit_start,'integer');
if (isset($limit_start)) { $limit_start = $limite * floor($limit_start / $limite); }
if (($browse != 0) && ($browse != 1)) { $browse = 0; }
if (eregi("^[0-9]+[,]",urldecode($site))) { $tempbust = explode(",",urldecode($site)); $site = $tempbust[0]; $path = $tempbust[1]; }
settype($site,'integer'); // now set to integer
if (!get_magic_quotes_gpc()) { $my_path = addslashes($path); }
else { $my_path = addslashes(stripslashes($path)); $path = stripslashes($path); }
// $relative_script_path set in search.php file
// $template set in config.php file
// $rssdf set in search.php file

$timer = new phpdigTimer('html');
$timer->start('All');

// init variables
global $phpdig_words_chars;
settype($maxweight,'integer');
$ignore = '';
$ignore_common = '';
$wheresite = '';
$wherepath = '';
$table_results = '';
$final_result = '';
$search_time = 0;
$strings = array();
$num_tot = 0;
$leven_final = "";

$mtime = explode(' ',microtime());
$start_time = $mtime[0]+$mtime[1];

$timer->start('All backend');
$timer->start('parsing strings');

if (!$option) {
     $option = SEARCH_DEFAULT_MODE;
}
if (!in_array($option,array('start','any','exact'))) {
     return 0;
}
// the query was filled
if ($query_string) {

$common_words = phpdigComWords("$relative_script_path/includes/common_words.txt");

$like_start = array( "start" => "", // is empty
                     "any" => "", // was a percent
                     "exact" => "" // is empty
                     );
$like_end = array( "start" => "%", // is a percent
                     "any" => "%", // is a percent
                     "exact" => "%" // was empty
                     );
$like_operator = array( "start" => "like", // is a like
                     "any" => "like", // is a like
                     "exact" => "like" // was an =
                     );

if ($refine) {
     $query_string = urldecode($query_string);
     $wheresite = "AND spider.site_id = $site ";
     if (($path) && (strlen($path) > 0)) {
          $wherepath = "AND spider.path like '$my_path' ";
     }
     $refine_url = "&refine=1&site=$site&path=$path";
}
else {
     $refine_url = "";
}

// query string was passed by url
if ($browse) {
     $query_string = urldecode($query_string);
}

if ($limite) {
     settype ($limite,"integer");
}
else {
    $limite = SEARCH_DEFAULT_LIMIT;
}

settype ($lim_start,"integer");
if ($lim_start < 0) {
     $lim_start = 0;
}

$n_words = count(explode(" ",$query_string));

$ncrit = 0;
$tin = "0";

if (!get_magic_quotes_gpc()) {
    $query_to_parse = addslashes($query_string);
}
else {
    $query_to_parse = $query_string;
}
$my_query_string_link = stripslashes($query_to_parse);

$query_to_parse = str_replace('_','\_',$query_to_parse); // avoid '_' in the query
$query_to_parse = str_replace('%','\%',$query_to_parse); // avoid '%' in the query
//$query_to_parse = str_replace('\'','_',$query_to_parse); // avoid ''' in the query
$query_to_parse = str_replace('\"',' ',$query_to_parse); // avoid '"' in the query

//$query_to_parse = phpdigStripAccents(strtolower(ereg_replace("[\"']+"," ",$query_to_parse))); //made all lowercase
$query_to_parse = phpdigStripAccents(strtolower($query_to_parse)); //made all lowercase

//$query_to_parse = ereg_replace("([^ ])-([^ ])","\\1 \\2",$query_to_parse); // avoid '-' in the query
//$query_to_parse = str_replace('_','\_',$query_to_parse); // avoid '_' in the query

$what_query_chars = "[^".$phpdig_words_chars[PHPDIG_ENCODING]." \'.\_~@#$:&\%/;,=-]+"; // epure chars \'._~@#$:&%/;,=-

if (eregi($what_query_chars,$query_to_parse)) {
	$query_to_parse = eregi_replace($what_query_chars," ",$query_to_parse);
}

$query_to_parse = ereg_replace('(['.$phpdig_words_chars[PHPDIG_ENCODING].'])[\'.\_~@#$:&\%/;,=-]+($|[[:space:]]$|[[:space:]]['.$phpdig_words_chars[PHPDIG_ENCODING].'])','\1\2',$query_to_parse);

$query_to_parse = trim(ereg_replace(" +"," ",$query_to_parse)); // no more than 1 blank

$query_for_strings = $query_to_parse;
$query_for_phrase = $query_to_parse;
$test_short = $query_to_parse;
$query_to_parse2 = explode(" ",$query_to_parse);
usort($query_to_parse2, "by_length");
$query_to_parse = implode(" ",$query_to_parse2);
unset($query_to_parse2);

if (SMALL_WORDS_SIZE >= 1) {

$ignore_short_flag = 0;
$test_short_counter = 0;
$test_short2 = explode(" ",$test_short);
for ($i=0; $i<count($test_short2); $i++) {
    $test_short2[$i] = trim($test_short2[$i]);
}
$test_short2 = array_unique($test_short2);
sort($test_short2);
$test_short3 = array();
for ($i=0; $i<count($test_short2); $i++) {
  if ((strlen($test_short2[$i]) <= SMALL_WORDS_SIZE) && (strlen($test_short2[$i]) > 0)) {
    $test_short2[$i].=" ";
    $test_short_counter++;
    $test_short3[] = $test_short2[$i];
  }
}
$test_short = implode(" ",$test_short3);
unset($test_short2);
unset($test_short3);

//while (ereg(' ([^ ]{1,'.SMALL_WORDS_SIZE.'}) | ([^ ]{1,'.SMALL_WORDS_SIZE.'})$|^([^ ]{1,'.SMALL_WORDS_SIZE.'}) ',$test_short,$regs)) {
  while (ereg('( [^ ]{1,'.SMALL_WORDS_SIZE.'} )|( [^ ]{1,'.SMALL_WORDS_SIZE.'})$|^([^ ]{1,'.SMALL_WORDS_SIZE.'} )',$test_short,$regs)) {
     for ($n=1; $n<=3; $n++) {
        if (($regs[$n]) || ($regs[$n] == 0)) {
            $ignore_short_flag++;
            if (!eregi("\"".trim(stripslashes($regs[$n]))."\", ",$ignore)) {
              $ignore .= "\"".trim(stripslashes($regs[$n]))."\", ";
            }
            $test_short = trim(str_replace($regs[$n],"",$test_short));
        }
     }
  }
  if (strlen($test_short) <= SMALL_WORDS_SIZE) {
    if (!eregi("\"".$test_short."\", ",$ignore)) {
      $ignore_short_flag++;
      $ignore .= "\"".stripslashes($test_short)."\", ";
    }
    $test_short = trim(str_replace($test_short,"",$test_short));
  }
}

$ignore = str_replace("\"\", ","",$ignore);

if ($option != "exact") {
  if (($ignore) && ($ignore_short_flag > 1) && ($test_short_counter > 1)) {
    $ignore_message = $ignore.' '.phpdigMsg('w_short_plur');
  }
  elseif ($ignore) {
    $ignore_message = $ignore.' '.phpdigMsg('w_short_sing');
  }
}

$ignore_common_flag = 0;
while (ereg("(-)?([^ ]{".(SMALL_WORDS_SIZE+1).",}).*",$query_for_strings,$regs)) {

        $query_for_strings = trim(str_replace($regs[2],"",$query_for_strings));
        if (!isset($common_words[stripslashes($regs[2])])) {
             $spider_in = "";
             if ($regs[1] == '-') {
                 $exclude[$ncrit] = $regs[2];
                 $query_for_phrase = trim(str_replace("-".$regs[2],"",$query_for_phrase));
             }
             else {
                 $strings[$ncrit] = $regs[2];
             }

             $kconds[$ncrit] = '';

             if ($option != 'any') {
                 $kconds[$ncrit] .= " AND k.twoletters = '".addslashes(substr(str_replace('\\','',$regs[2]),0,2))."' ";
             }
             $kconds[$ncrit] .= " AND k.keyword ".$like_operator[$option]." '".$like_start[$option].$regs[2].$like_end[$option]."' ";

             $ncrit++;
        }
        else {
             $ignore_common_flag++;
             $ignore_common .= "\"".stripslashes($regs[2])."\", ";
        }
}

if ($option != "exact") {
  if (($ignore_common) && ($ignore_common_flag > 1)) {
    $ignore_commess = $ignore_common.' '.phpdigMsg('w_common_plur');
  }
  elseif ($ignore_common) {
    $ignore_commess = $ignore_common.' '.phpdigMsg('w_common_sing');
  }
}

$timer->stop('parsing strings');

if ($ncrit && is_array($strings)) {
     $query = "SET OPTION SQL_BIG_SELECTS = 1";
     mysql_query($query,$id_connect);

     $my_spider2site_array = array();
     $my_sitecount_array = array();

     for ($n = 0; $n < $ncrit; $n++) {
           $timer->start('spider queries');

           $query = "SELECT spider.spider_id,sum(weight) as weight, spider.site_id
           FROM ".PHPDIG_DB_PREFIX."keywords as k,".PHPDIG_DB_PREFIX."engine as engine, ".PHPDIG_DB_PREFIX."spider as spider
           WHERE engine.key_id = k.key_id
           ".$kconds[$n]."
           AND engine.spider_id = spider.spider_id $wheresite $wherepath
           GROUP BY spider.spider_id,spider.site_id ";

           $result = mysql_query($query,$id_connect);
           $num_res_temp = mysql_num_rows($result);

           $timer->stop('spider queries');
           $timer->start('spider fills');

           if ($num_res_temp > 0) {
               if (!isset($exclude[$n])) {
               $num_res[$n] = $num_res_temp;
                    while (list($spider_id,$weight,$site_id) = mysql_fetch_array($result)) {
                         $s_weight[$n][$spider_id] = $weight;
                         $my_spider2site_array[$spider_id] = $site_id;
                         $my_sitecount_array[$site_id] = 0;
                    }
               }
               else {
               $num_exclude[$n] = $num_res_temp;
                     while (list($spider_id,$weight) = mysql_fetch_array($result)) {
                            $s_exclude[$n][$spider_id] = 1;
                     }
               mysql_free_result($result);
               }
           }
           elseif (!isset($exclude[$n])) {
                   $num_res[$n] = 0;
                   $s_weight[$n][0] = 0;
          }
          $timer->stop('spider fills');
     }

     $timer->start('reorder results');

     if ($option != "any") {
     if (is_array($num_res)) {
           asort ($num_res);
           list($id_most) = each($num_res);
           reset ($s_weight[$id_most]);
           while (list($spider_id,$weight) = each($s_weight[$id_most]))  {
                  $weight_tot = 1;
                  reset ($num_res);
                  while(list($n) = each($num_res)) {
                        settype($s_weight[$n][$spider_id],'integer');
                        $weight_tot *= sqrt($s_weight[$n][$spider_id]);
                  }
                  if ($weight_tot > 0) {
                       $final_result[$spider_id]=$weight_tot;
                  }
           }
     }
     }
     else {
     if (is_array($num_res)) {
           asort ($num_res);
           while (list($spider_id,$site_id) = each($my_spider2site_array))  {
                  $weight_tot = 0;
                  reset ($num_res);
                  while(list($n) = each($num_res)) {
                        settype($s_weight[$n][$spider_id],'integer');
                        $weight_tot += sqrt($s_weight[$n][$spider_id]);
                  }
                  if ($weight_tot > 0) {
                       $final_result[$spider_id]=$weight_tot;
                  }
           }
     }
     }

    if (isset($num_exclude) && is_array($num_exclude)) {
           while (list($id) = each($num_exclude)) {
                  while(list($spider_id) = each($s_exclude[$id])) {
                        unset($final_result[$spider_id]);
                  }
           }
    }

    if ($option == "exact") {
    if ((is_array($final_result)) && (count($final_result) > 0)) {
        $exact_phrase_flag = 0;
        arsort($final_result);
        reset($final_result);
        $query_for_phrase_array = explode(" ",$query_for_phrase);
        $reg_strings = str_replace('@#@',' ',phpdigPregQuotes(str_replace('\\','',implode('@#@',$query_for_phrase_array))));
        $stop_regs = "[][(){}[:blank:]=&?!&#%\$£*@+%:;,/\.'\"]";
        $reg_strings = "($stop_regs{1}|^)($reg_strings)($stop_regs{1}|\$)";
        while (list($spider_id,$weight) = each($final_result)) {
          $content_file = $relative_script_path.'/'.TEXT_CONTENT_PATH.$spider_id.'.txt';
          if (is_file($content_file)) {
            $f_handler = fopen($content_file,'r');
            $extract_content = preg_replace("/([ ]{2}|\n|\r|\r\n)/"," ",fread($f_handler,filesize($content_file)));
              if(!eregi($reg_strings,$extract_content)) {
                $exact_phrase_flag = 1;
              }
            fclose($f_handler);
          }
          if ($exact_phrase_flag == 1) {
            unset($final_result[$spider_id]);
            $exact_phrase_flag = 0;
          }
        }
      }
    }

//    if(NUMBER_OF_RESULTS_PER_SITE != -1) {
    if((!$refine) && (NUMBER_OF_RESULTS_PER_SITE != -1)) {
    if ((is_array($final_result)) && (count($final_result) > 0)) {
           arsort($final_result);
           reset($final_result);
           while (list($spider_id,$weight) = each($final_result)) {
                  $site_id = $my_spider2site_array[$spider_id];
                  $current_site_counter = $my_sitecount_array[$site_id];
                  if ($current_site_counter < NUMBER_OF_RESULTS_PER_SITE) {
                         $my_sitecount_array[$site_id]++;
                  }
                  else {
                         unset($final_result[$spider_id]);
                  }
           }
    }
    }

    $timer->stop('reorder results');

}

$timer->stop('All backend');
$timer->start('All display');

if ((is_array($final_result)) && (count($final_result) > 0)) {
    arsort($final_result);
    $n_start = $lim_start+1;
    $num_tot = count($final_result);
    if ($n_start+$limite-1 < $num_tot) {
           $n_end = ($lim_start+$limite);
           $more_results = 1;
    }
    else {
          $n_end = $num_tot;
          $more_results = 0;
    }

    // ereg for text snippets and highlighting

    if ($option == "exact") {
        $reg_strings = str_replace('@#@',' ',phpdigPregQuotes(str_replace('\\','',implode('@#@',$query_for_phrase_array))));
    }
    else {
        $reg_strings = str_replace('@#@','|',phpdigPregQuotes(str_replace('\\','',implode('@#@',$strings))));
    }
    $stop_regs = "[][(){}[:blank:]=&?!&#%\$£*@+%:;,/\.'\"]";

    switch($option) {
        case 'any':
        $reg_strings = "($stop_regs{1}|^)($reg_strings)()";
        break;
        case 'exact':
        $reg_strings = "($stop_regs{1}|^)($reg_strings)($stop_regs{1}|\$)";
        break;
        default:
        $reg_strings = "($stop_regs{1}|^)($reg_strings)()";
    }

/*
    switch($option) {
        case 'any': // or
        $reg_strings = "()($reg_strings)()";
        break;
        case 'exact':
        $reg_strings = "($stop_regs{1}|^)($reg_strings)($stop_regs{1}|\$)";
        break;
        default: // and
        $reg_strings = "($stop_regs{1}|^)($reg_strings)()";
    }
*/

    $timer->start('Result table');

    //fill the results table
    reset($final_result);
    for ($n = 1; $n <= $n_end; $n++) {
        list($spider_id,$s_weight) = each($final_result);
        if (!$maxweight) {
              $maxweight = $s_weight;
        }
        if ($n >= $n_start) {
             $timer->start('Display queries');

             $query = "SELECT sites.site_url, sites.port, spider.path,spider.file,spider.first_words,sites.site_id,spider.spider_id,spider.last_modified,spider.md5 "
                      ."FROM ".PHPDIG_DB_PREFIX."spider AS spider, ".PHPDIG_DB_PREFIX."sites AS sites "
                      ."WHERE spider.spider_id=$spider_id AND sites.site_id = spider.site_id";
             $result = mysql_query($query,$id_connect);
             $content = mysql_fetch_array($result,MYSQL_ASSOC);
             mysql_free_result($result);
             if ($content['port']) {
                 $content['site_url'] = ereg_replace('/$',':'.$content['port'].'/',$content['site_url']);
             }
             $weight = sprintf ("%01.2f", (100*$s_weight)/$maxweight);
             $url = eregi_replace("([a-z0-9])[/]+","\\1/",$content['site_url'].$content['path'].$content['file']);

             $js_url = urlencode(eregi_replace("^[a-z]{3,5}://","",$url));

             $l_site = "<a class='phpdig' href='".SEARCH_PAGE."?refine=1&amp;query_string=".urlencode($my_query_string_link)."&amp;site=".$content['site_id']."&amp;limite=$limite&amp;option=$option'>".$content['site_url']."</a>";
             if ($content['path']) {
                  $l_path = ", ".phpdigMsg('this_path')." : <a class='phpdig' href='".SEARCH_PAGE."?refine=1&amp;query_string=".urlencode($my_query_string_link)."&amp;site=".$content['site_id']."&amp;path=".$content['path']."&amp;limite=$limite&amp;option=$option' >".ereg_replace('%20*',' ',$content['path'])."</a>";
             }
             else {
                  $l_path="";
             }

             $first_words = str_replace('<','&lt;',str_replace('>','&gt;',$content['first_words']));

             $timer->stop('Display queries');
             $timer->start('Extracts');

             $extract = "";
             //Try to retrieve matching lines if the content-text is set to 1
             if (CONTENT_TEXT == 1 && DISPLAY_SNIPPETS) {
                 $content_file = $relative_script_path.'/'.TEXT_CONTENT_PATH.$content['spider_id'].'.txt';
                 if (is_file($content_file)) {
                     $num_extracts = 0;
                     $my_extract_size = 200;

                     $my_filesize_for_while = filesize($content_file);
                     while (($num_extracts == 0) && ($my_extract_size <= $my_filesize_for_while)) { // ***

                     $f_handler = fopen($content_file,'r');
//                     while($num_extracts < DISPLAY_SNIPPETS_NUM && $extract_content = fgets($f_handler,1024)) {
                     while($num_extracts < DISPLAY_SNIPPETS_NUM && $extract_content = preg_replace("/([ ]{2}|\n|\r|\r\n)/"," ",fread($f_handler,$my_extract_size))) {
                           if(eregi($reg_strings,$extract_content)) {
                              $extract_content = str_replace('<','&lt;',str_replace('>','&gt;',trim($extract_content)));
                              $match_this_spot = eregi_replace($reg_strings,"\\1<\\2>\\3",$extract_content);
                              $first_bold_spot = strpos($match_this_spot,"<");
                              $first_bold_spot = max($first_bold_spot - round((SNIPPET_DISPLAY_LENGTH / 2),0), 0);
                              $extract_content = substr($extract_content,$first_bold_spot,max(SNIPPET_DISPLAY_LENGTH, 2 * strlen($query_string)));
                              $extract .= ' ...'.phpdigHighlight($reg_strings,$extract_content).'... ';
                              $num_extracts++;
                           }
                     }
                     fclose($f_handler);

                         if ($my_extract_size < $my_filesize_for_while) {
                             $my_extract_size *= 100;
                             if ($my_extract_size > $my_filesize_for_while) {
                                 $my_extract_size = $my_filesize_for_while;
                             }
                         }
                         else {
                             $my_extract_size++;
                         }

                     } // ends ***

                 }
             }

             list($title,$text) = explode("\n",$first_words);

             $title = phpdigHighlight($reg_strings,$title);

             $timer->stop('Extracts');

             $table_results[$n] = array (
                    'weight' => $weight,
                    'img_tag' => '<img border="0" src="'.WEIGHT_IMGSRC.'" width="'.ceil(WEIGHT_WIDTH*$weight/100).'" height="'.WEIGHT_HEIGHT.'" alt="" />',
                    'page_link' => "<a class=\"phpdig\" href=\"".$url."\" onmousedown=\"return clickit(".$n.",'".$js_url."')\" target=\"".LINK_TARGET."\" >".ereg_replace('%20*',' ',$title)."</a>",
                    'limit_links' => phpdigMsg('limit_to')." ".$l_site.$l_path,
                    'filesize' => sprintf('%.1f',(ereg_replace('.*_([0-9]+)$','\1',$content['md5']))/1024),
                    'update_date' => ereg_replace('^([0-9]{4})([0-9]{2})([0-9]{2}).*',PHPDIG_DATE_FORMAT,$content['last_modified']),
                    'complete_path' => $url,
                    'link_title' => $title
                    );

             $table_results[$n]['text'] = '';
             if (DISPLAY_SUMMARY) {
                 $table_results[$n]['text'] = phpdigHighlight($reg_strings,ereg_replace('(@@@.*)','',wordwrap($text, SUMMARY_DISPLAY_LENGTH, '@@@')));
             }
             if (DISPLAY_SUMMARY && DISPLAY_SNIPPETS) {
                 $table_results[$n]['text'] .= "\n<br/><br/>\n";
             }
             if (DISPLAY_SNIPPETS) {
                 if ($extract) {
                     $table_results[$n]['text'] .= $extract;
                 }
                 else if (!$table_results[$n]['text']){
                     $table_results[$n]['text'] = phpdigHighlight($reg_strings,ereg_replace('(@@@.*)','',wordwrap($text, SUMMARY_DISPLAY_LENGTH, '@@@')));
                 }
             }
        }
    }

    $timer->stop('Result table');
    $timer->start('Final strings');

    $nav_bar = '';
    $pages_bar = '';
    $url_bar = SEARCH_PAGE."?browse=1&amp;query_string=".urlencode($my_query_string_link)."$refine_url&amp;limite=$limite&amp;option=$option&amp;lim_start=";
    if ($lim_start > 0) {
        $previous_link = $url_bar.($lim_start-$limite);
        $nav_bar .= "<a class=\"phpdig\" href=\"$previous_link\" >&lt;&lt;".phpdigMsg('previous')."</a>&nbsp;&nbsp;&nbsp; \n";
    }
    $tot_pages = ceil($num_tot/$limite);
    $actual_page = $lim_start/$limite + 1;
    $page_inf = max(1,$actual_page - 5);
    $page_sup = min($tot_pages,max($actual_page+5,10));
    for ($page = $page_inf; $page <= $page_sup; $page++) {
      if ($page == $actual_page) {
           $nav_bar .= " <span class=\"phpdigHighlight\">$page</span> \n";
           $pages_bar .= " <span class=\"phpdigHighlight\">$page</span> \n";
           $link_actual =  $url_bar.(($page-1)*$limite);
      }
      else {
          $nav_bar .= " <a class=\"phpdig\" href=\"".$url_bar.(($page-1)*$limite)."\" >$page</a> \n";
          $pages_bar .= " <a class=\"phpdig\" href=\"".$url_bar.(($page-1)*$limite)."\" >$page</a> \n";
      }
    }

    if ($more_results == 1) {
        $next_link = $url_bar.($lim_start+$limite);
        $nav_bar .= " &nbsp;&nbsp;&nbsp;<a class=\"phpdig\" href=\"$next_link\" >".phpdigMsg('next')."&gt;&gt;</a>\n";
    }

    $mtime = explode(' ',microtime());
    $search_time = sprintf('%01.2f',$mtime[0]+$mtime[1]-$start_time);
    $result_message = stripslashes(ucfirst(phpdigMsg('results'))." $n_start-$n_end, $num_tot ".phpdigMsg('total').", ".phpdigMsg('on')." \"".htmlspecialchars($query_string)."\" ($search_time ".phpdigMsg('seconds').")");

    $timer->stop('Final strings');
}
else {
    $num_in_strings_arr = count($strings);
    if (($num_in_strings_arr > 0) && (strlen($path) == 0)) {
        $leven_final = "";
        for ($i=0; $i<$num_in_strings_arr; $i++) {
            $soundex_query = "SELECT keyword FROM ".PHPDIG_DB_PREFIX."keywords WHERE SOUNDEX(CONCAT('Q',keyword)) = SOUNDEX(CONCAT('Q','".$strings[$i]."')) LIMIT 500";
            $soundex_results = mysql_query($soundex_query,$id_connect);
            if (mysql_num_rows($soundex_results) > 0) {
                 $leven_ind = 0;
                 $leven_amt1 = 256;
                 $leven_keyword = array();
                 while (list($soundex_keyword) = mysql_fetch_array($soundex_results)) {
                     $leven_amt2 = min(levenshtein($strings[$i],$soundex_keyword),$leven_amt1);
                     if (($leven_amt2 < $leven_amt1) && ($leven_amt2 > 0) && ($leven_amt2 <= 5)) {
                         $leven_keyword[$leven_ind] = stripslashes($soundex_keyword);
                         $leven_ind++;
                     }
                     $leven_amt1 = $leven_amt2;
                 }
                $leven_count = count($leven_keyword);
                $leven_final .= $leven_keyword[$leven_count-1] . " ";
                unset($leven_keyword);
            }
        }
    }

    $num_tot = 0;
    $result_message = phpdigMsg('noresults');

    if (strlen(trim($leven_final)) > 0) {
        $leven_query = trim($leven_final);
        $result_message .= ". " . phpdigMsg('alt_try') ." <a class=\"phpdigMessage\" href=\"".SEARCH_PAGE."?template_demo=".$_GET['template_demo']."&query_string=".urlencode($leven_query)."\"><i>".htmlspecialchars($leven_query)."</i></a>?";
    }

}

if (isset($tempresult)) {
    mysql_free_result($tempresult);
}

$title_message = phpdigMsg('s_results');
}
else {
   $title_message = 'PhpDig '.PHPDIG_VERSION;
   $result_message = phpdigMsg('no_query').'.';
}

$timer->start('Logs');
if (PHPDIG_LOGS == true && !$browse && !$refine) {
   if (!isset($exclude)) {
        $exclude = array();
   }
   if (is_array($final_result)) {
       phpdigAddLog ($id_connect,$option,$strings,$exclude,count($final_result),$search_time);
   }
   else {
       phpdigAddLog ($id_connect,$option,$strings,$exclude,0,$search_time);
   }
}
$timer->stop('Logs');

$timer->start('Template parsing');

$powered_by_link = "<font size=\"1\" face=\"verdana,arial,sans-serif\">";
if (ALLOW_RSS_FEED) {
  $powered_by_link .= "<a href=\"".$rssdf."\">View RSS for this Page</a><br>";
}
$powered_by_link .= "<a href=\"http://www.phpdig.net/\">Powered by PhpDig</a><br></font>";

$js_string = implode(" ",$strings);
$js_for_clicks = "
<script language=\"JavaScript\">
<!--
function clickit(cn,clink) {
  if(document.images) {
     (new Image()).src=\"clickstats.php?num=\"+cn+\"&url=\"+clink+\"&val=".urlencode($js_string)."\";
  }
  return true;
}
//-->
</script>
";

if ($template == 'array' || is_file($template)) {
    $phpdig_version = PHPDIG_VERSION;
    $t_mstrings = compact('js_for_clicks','rss_feed_link','powered_by_link','title_message','phpdig_version','result_message','nav_bar','ignore_message','ignore_commess','pages_bar','previous_link','next_link','templates_links');
    $t_fstrings = phpdigMakeForm($query_string,$option,$limite,SEARCH_PAGE,$site,$path,'template',$template,$num_tot);
    if ($template == 'array') {
        return array_merge($t_mstrings,$t_fstrings,array('results'=>$table_results));
    }
    else {
        $t_strings = array_merge($t_mstrings,$t_fstrings);
        phpdigParseTemplate($template,$t_strings,$table_results);
    }
}
else {
?>
<?php include $relative_script_path.'/libs/htmlheader.php' ?>
<head>
<title><?php print $title_message ?></title>
<?php include $relative_script_path.'/libs/htmlmetas.php' ?>
<style>
.phpdigHighlight {color:<?php print HIGHLIGHT_COLOR ?>;
                 background-color:<?php print HIGHLIGHT_BACKGROUND ?>;
                 font-weight:bold;
                 }
.phpdigMessage {padding:1px;background-color:#002288;color:white;}
</style>
<script language="JavaScript">
<!--
function clickit(cn,clink) {
  if(document.images) {
    (new Image()).src="clickstats.php?num="+cn+"&url="+clink+"&val=<?php echo urlencode($js_string); ?>";
  }
  return true;
}
//-->
</script>
</head>
<body bgcolor="white">
<div align="center">
<img src="phpdig_logo_2.png" width="200" height="114" alt="phpdig <?php print PHPDIG_VERSION ?>" border="0" />
<br />
<?php
phpdigMakeForm($query_string,$option,$limite,SEARCH_PAGE,$site,$path,'classic','',$num_tot);
?>
<h3><span class="phpdigMsg"><?php print $result_message ?></span>
<br /><span class="phpdigAlert"><?php print $ignore_message ?></span>
<br /><span class="phpdigAlert"><?php print $ignore_commess ?></span>
</h3>
</div>
<?php
if (is_array($table_results)) {
       while (list($n,$t_result) = each($table_results)) {
             print "<p style='background-color:#CCDDFF;'>\n";
             print "<b>$n. <font style='font-size:10;'>[".$t_result['weight']." %]</font>&nbsp;&nbsp;".$t_result['page_link']."</b>\n<br />\n";
             print "<font style='font-size:10;background-color:#BBCCEE;'>".$t_result['limit_links']."</font>\n<br />\n";
             print "</p>\n";
             print "<blockquote style='background-color:#EEEEEE;font-size:10;'>\n";
             print $t_result['text'];
             print "</blockquote>\n";
       }
}
print "<p style='text-align:center;background-color:#CCDDFF;font-weight:bold'>\n";
print $nav_bar;
print "</p>\n";
?>
<hr />
<div align="center">
<?php
if ($query_string) {
    phpdigMakeForm($query_string,$option,$limite,SEARCH_PAGE,$site,$path,'classic','',$num_tot);
}
?>
</div>
<div align='center'>
<a href='http://www.phpdig.net/' target='_blank'><img src='phpdig_powered_2.png' width='88' height='28' border='0' alt='Powered by PhpDig' /></a> &nbsp;
</div>
</body>
</html>
<?php
}
$timer->stop('Template parsing');
$timer->stop('All display');
$timer->stop('All');
//$timer->display();
}

function by_length($a, $b) {
$len_a = strlen($a);
$len_b = strlen($b);
if ($len_a == $len_b) { return 0; }
return ($len_a < $len_b) ? 1 : -1;
}
?>
