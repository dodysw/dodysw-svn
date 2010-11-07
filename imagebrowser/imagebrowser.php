<?
$_description = '<pre>
================================================
  ImageBrowser v1.0
  Copyright 2004 - Dody Suria Wijaya, 0818-117420
  dsw s/h - dswsh@plasa.com
  Description: 
  Drop + include anywhere-ready 1-file image browser which searches files with this formats:
      - thumb_xxxxxx.YYY: thumbnails
      - large_xxxxxx.YYY: large image
  Note: xxxxxx = product/image ID, YYY = any extensions
  To use this script correctly, you must prepare these files:
   1. index.php: the html page which then "include" this file, and echo supported variables ($view_1, $view_2,...) at your desired place
   2. data.php: description for the images/products (scroll to bottom for a sample)
   3. at least 1 thumb_xxx.YYY file and its coresponding large_xxx.YYY file
================================================
</pre>';

//constant
define("PAGE_COUNT", 5); 

//convenient stuff
$self = $_SERVER['PHP_SELF'];
$page = $_REQUEST['page'] or 1;
$id = $_REQUEST['id'];

//----------------------------------
//--scan current folder for images
//----------------------------------
$fdir = './';
$dir = opendir($fdir);
while(false !== ($file = readdir($dir))) 
{    
    if($file == "." or $file == "..") continue;    
    $marks = explode('_',$file,2);
    $type = filetype($fdir.$file);
    $info = pathinfo($file);
    if($type != "dir") 
    {
        #print_r($marks);
        if($marks[0] == "thumb")
        {
            #$file_extension = $info["extension"];
            $thumbs_all[] = $file;
        }
        elseif ($marks[0] == "large")
        {
            $larges[] = $file;
        }
    }
}
closedir($dir);
//no thumbnails?
if (count($thumbs_all) == 0) { echo "No image is available in this location".$_description; exit; }

//----------------------------------
//--get and process image description file
//----------------------------------
@include 'data.php';
if (!$data_loaded) { echo 'No data is available for these product, please install a data.php file'.$_description; exit; }
if (gettype($pid) == 'string')
{
    foreach (explode("\r\n",trim($pid)) as $row)
    {
        list($key,$value) = explode('=',$row);
        $_pid[trim($key)] = explode('^',$value);
    }
    $pid = $_pid;
}

//----------------------------------
//--page division logic
//----------------------------------
$max_page = ceil(count($thumbs_all)/PAGE_COUNT);
if ($page > $max_page) $page = $max_page;
elseif ($page < 1) $page = 1;
$start = ($page - 1) * PAGE_COUNT;
$thumbs = array_slice($thumbs_all,$start, PAGE_COUNT);

//----------------------------------
//--which product to show
//----------------------------------
if ($id == '')
{
    #point to the first item in this page's image
    $url = $thumbs[0];
    if (!preg_match('|thumb_(.*)\.\w+|',$url,$result)) { echo '1:Cannot parse product ID from $url';exit;}
    $id = $result[1];
}
$focused_url = "large_{$id}.jpg";

//----------------------------------
//--focused product image
//----------------------------------
$view_1 .= "<img src='$focused_url' border=1>";
if (array_key_exists($id,$pid))
{
    list($name,$desc,$price) = $pid[$id];
    $view_1 .= "<p>$name<br><small>$desc</small><br><b>$price</b>";    
}

//----------------------------------
//--show thumbnails
//----------------------------------
foreach ($thumbs as $url)
{
    #format: thumb_xxxxxx.jpg
    if (!preg_match('|thumb_(.*)\.\w+|',$url,$result)) { echo '2:Cannot parse product ID from $url';exit;}
    $thumb_id = $result[1];
    if ($id == $thumb_id)
        $view_2 .= "<p><img src='$url' border=4>";
    else
        $view_2 .= "<p><a href='$self?page=$page&id=$thumb_id'><img src='$url' border=1></a>";
    if (array_key_exists($thumb_id,$pid))
    {
        list($name,$desc,$price) = $pid[$thumb_id];
        $view_2 .= "<br><small>$name</small>";
    }
        
}

//----------------------------------
//--show navigator
//----------------------------------
if ($max_page > 1)
{
    $page_prev = $page - 1;
    $page_next = $page + 1;
    if ( ($page > 1) and ($page < $max_page) )
        $view_3 .= "<p><a href='$self?page={$page_prev}'>Previous</a> | <a href='$self?page={$page_next}'>Next</a>";
    elseif ($page == 1)
        $view_3 .= "<p>Previous  | <a href='$self?page={$page_next}'>Next</a>";
    elseif ($page == $max_page)
        $view_3 .= "<p><a href='$self?page={$page_prev}'>Previous</a> | Next";
}

//----------------------------------
//-- data.php file example
//----------------------------------
/*
    <?
    $data_loaded = true;
    
    //use array-style (faster)
    $pid = array( 
    613 => array('Kereta Pejompongan 1','Hempit-hempitan penumpang KRL','Rp 150.000,-'),
    6131 => array('Kereta Pejompongan 2','Kilasan kereta listrik (KRL) Bintaro-Matraman','Rp 150.000,-'),
    6132 => array('Bajaj Biru','Sore telah datang namun tukang bajaj tetap menantang','Rp 350.000,-'),
    6133 => array('Cari Air 1','Bahkan di tengah lokasi parkir semua tetap mencari air','Rp 150.000,-'),
    6134 => array('Cari Air 2','Seorang satpam penasaran dengan pekerjaan kuli','Rp 150.000,-'),
    6135 => array('Sabar Menanti','Sepasang pengendara sepeda motor sabar menanti lamu merah','Rp 150.000,-'),
    6136 => array('Motor Lewat','Serbuan pengendara motor setelah lampu menjadi hijau','Rp 100.000,-'),
    6137 => array('Bis Lewat','Sebuah bis steady safe jurusan Depok-Jakarta','Rp 550.000,-'),
    6138 => array('Chinese Food','Restoran cina di Margonda Depok menawarkan diskon bagi mahasiswa','Rp 150.000,-'),
    6139 => array('Bis dan Motor','Kebut-kebutan motor mendahului kebutnya bis','Rp 150.000,-'),
    61310 => array('Manusia Excavator','Walaupun rambunya menggambarkan sosok manusia','Rp 550.000,-'),
    61311 => array('Tukang Ojek','Seorang pengojek yang spesialis antar barang jarak jauh','Rp 50.000,-'),
    61312 => array('Depok 2','Hamparan tanah kosong perumahan baru di daerah Depok 2','Rp 250.000,-'),
    61313 => array('Raksasa Kesepian','Giant baru di jembatan semanggi sepi pengunjung','Rp 250.000,-'),
    61314 => array('Malam Karapan','Kiblatan cahaya keringat meramaikan malam jakarta','Rp 250.000,-'),
    );
    
    //use string-style (slower)    
    $pid = "
    613 = Kereta Pejompongan 1^Hempit-hempitan penumpang KRL^Rp 150.000,-
    6131 = Kereta Pejompongan 2^Kilasan kereta listrik (KRL) Bintaro-Matraman^Rp 150.000,-
    6132 = Bajaj Biru^Sore telah datang namun tukang bajaj tetap menantang^Rp 350.000,-
    6133 = Cari Air 1^Bahkan di tengah lokasi parkir semua tetap mencari air^Rp 150.000,-
    6134 = Cari Air 2^Seorang satpam penasaran dengan pekerjaan kuli^Rp 150.000,-
    6135 = Sabar Menanti^Sepasang pengendara sepeda motor sabar menanti lamu merah^Rp 150.000,-
    6136 = Motor Lewat^Serbuan pengendara motor setelah lampu menjadi hijau^Rp 100.000,-
    6137 = Bis Lewat^Sebuah bis steady safe jurusan Depok-Jakarta^Rp 550.000,-
    6138 = Chinese Food^Restoran cina di Margonda Depok menawarkan diskon bagi mahasiswa^Rp 150.000,-
    6139 = Bis dan Motor^Kebut-kebutan motor mendahului kebutnya bis^Rp 150.000,-
    61310 = Manusia Excavator^Walaupun rambunya menggambarkan sosok manusia^Rp 550.000,-
    61311 = Tukang Ojek^Seorang pengojek yang spesialis antar barang jarak jauh^Rp 50.000,-
    61312 = Depok 2^Hamparan tanah kosong perumahan baru di daerah Depok 2^Rp 250.000,-
    61313 = Raksasa Kesepian^Giant baru di jembatan semanggi sepi pengunjung^Rp 250.000,-
    61314 = Malam Karapan^Kiblatan cahaya keringat meramaikan malam jakarta^Rp 250.000,-
    ";
    ?>
*/

?>