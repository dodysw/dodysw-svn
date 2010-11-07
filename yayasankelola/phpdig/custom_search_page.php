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

if ((isset($_SERVER['SCRIPT_FILENAME'])) && (eregi("custom_search_page.php",$_SERVER['SCRIPT_FILENAME']))) {
  exit();
}
if ((isset($_SERVER['SCRIPT_URI'])) && (eregi("custom_search_page.php",$_SERVER['SCRIPT_URI']))) {
  exit();
}
if ((isset($_SERVER['SCRIPT_URL'])) && (eregi("custom_search_page.php",$_SERVER['SCRIPT_URL']))) {
  exit();
}
if ((isset($_SERVER['REQUEST_URI'])) && (eregi("custom_search_page.php",$_SERVER['REQUEST_URI']))) {
  exit();
}
if ((isset($_SERVER['SCRIPT_NAME'])) && (eregi("custom_search_page.php",$_SERVER['SCRIPT_NAME']))) {
  exit();
}
if ((isset($_SERVER['PATH_TRANSLATED'])) && (eregi("custom_search_page.php",$_SERVER['PATH_TRANSLATED']))) {
  exit();
}
if ((isset($_SERVER['PHP_SELF'])) && (eregi("custom_search_page.php",$_SERVER['PHP_SELF']))) {
  exit();
}

if (!empty($output['results'])) {
    $num_out = count($output['results']);
}
else {
    $num_out = 0;
}

$num_start = $lim_start + 1;
$num_end = $lim_start + $num_out;

$rdfli = "";
$rdfabout = "";
$thetime = time();
$thefile = $thetime.rand().$thefile;

for ($i=$num_start; $i<=$num_end; $i++) {
    $output2 = $output['results'][$i];

    $out_n = xmlentities(trim($i));
    $out_weight = xmlentities(trim($output2['weight'])); // in percent
    $out_img_tag = xmlentities(trim($output2['img_tag'])); // <img border="0" src="./tpl_img/weight.gif" width="50" height="5" alt="" />
    $out_page_link = xmlentities(trim($output2['page_link']));
    $out_limit_links = xmlentities(trim($output2['limit_links']));
    $out_filesize = xmlentities(trim($output2['filesize'])); // in kilobytes
    $out_update_date = xmlentities(trim($output2['update_date'])); // yy-mm-dd
    $out_complete_path = xmlentities(trim($output2['complete_path']));
    $out_link_title = xmlentities(trim($output2['link_title']));
    $out_text = xmlentities(trim($output2['text']));

    $rdfli .= "\n			<rdf:li rdf:resource=\"".$out_complete_path."\" />";
    $rdfabout .= "\n<item rdf:about=\"".$out_complete_path."\">\n";
    $rdfabout .= "	<title>".$out_link_title."</title>\n";
    $rdfabout .= "	<link>".$out_complete_path."</link>\n";
    $rdfabout .= "	<description>".$out_text."</description>\n";
    $rdfabout .= "	<dc:date>".$out_update_date."</dc:date>\n";
    $rdfabout .= "	<dc:number>".$out_n."</dc:number>\n";
    $rdfabout .= "	<dc:weight>".$out_weight."</dc:weight>\n";
    $rdfabout .= "	<dc:image>".$out_img_tag."</dc:image>\n";
    $rdfabout .= "	<dc:link>".$out_page_link."</dc:link>\n";
    $rdfabout .= "	<dc:limit>".$out_limit_links."</dc:limit>\n";
    $rdfabout .= "	<dc:size>".$out_filesize."</dc:size>\n";
    $rdfabout .= "</item>\n";
}

if ($num_end >= $num_start) {
$rssinfo = <<<END
<?xml version="1.0" encoding="$theenc"?>
<rdf:RDF
	xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns="http://purl.org/rss/1.0/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
>
<channel rdf:about="$theurl">
	<title>$thetitle</title>
	<link>$theurl</link>
	<description>$thedesc</description>
	<items>
		<rdf:Seq>$rdfli
		</rdf:Seq>
	</items>
</channel>
<!-- RSS-Items -->
$rdfabout
<!-- / RSS-Items PHP/RSS -->
</rdf:RDF>
END;
}
else {
$rssinfo = "No Results: No RSS Feed";
}

$f_handler = fopen($thedir."/".$thefile,'wb');
fwrite($f_handler,$rssinfo);
fclose($f_handler);

if (is_dir($thedir)) {
    $dir_handle = opendir($thedir);
    while ($rssfile = readdir($dir_handle)) {
        if ((is_file($thedir."/".$rssfile)) && ($rssfile != ".") && ($rssfile != "..")) {
            $timestrlen = strlen($thetime);
            $rssfiletime = substr($rssfile,0,$timestrlen);
            if ((is_numeric($rssfiletime)) && ($rssfiletime < $thetime - 300)) {
                unlink($thedir."/".$rssfile);
            }
        }
    }
    closedir($dir_handle);
}

function xmlentities($string, $quote_style=ENT_COMPAT) {
    $trans = get_html_translation_table(HTML_ENTITIES, $quote_style);
//    foreach ($trans as $key => $value) {
//        $trans[$key] = '&#'.ord($key).';';
//    }
//    return strtr($string, $trans);
    $encoded = str_replace("&","&amp;",strtr($string, $trans));
//    $encoded = str_replace("&amp;amp;amp;","&amp;amp;",$encoded);
    return $encoded;
}

?>
