<?
/* the mother of all scripts
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include '../config.inc.php';
include '../'.$include_dir.'func.inc.php';

session_start();

include 'model/entitymanager.inc.php';
// check m (module) param






//start output
include '../'.$include_dir.'headerpage.inc.php';
echo '<table border="0" width="100%"><tr><td width="200" valign="top">';
include '../'.$include_dir.'navpage.inc.php';


echo "\r\n</td>\r\n<td valign=top>";
//$prog->go();

$model = new EntityManager();

$cabangs = $model->getCabangs(0,0);

for($i=0;$i<count($cabangs);$i++)
{
	$cabang= new tahapan_kerjasama_model();
	$cabang =$cabangs[$i];
	echo 'ini adalah cabang ' . $cabang->getKodeKerjasama() . " = " . $cabang->getRowid() . '<br>';
	
}

/*
$sql="select * from an_kota_tab";
$res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
        $rows = array();
        
            while ($row = mysql_fetch_array($res,MYSQL_ASSOC)) {
                $rows[] = $row;
                echo $row['nama_kota'] . "<br>";
            }
            
            for ($i=0;$i<count($rows);$i++)
            {
            	echo "halo " . $rows[0]['nama_kota'] . '<br>';
            	
            }
 */
        

#~ echo "\r\n</td>\r\n<td width=150 valign=top>";
echo "\r\n</td>\r\n<td valign=top>";
include '../'.$include_dir.'right.inc.php';
echo "\r\n</td>\r\n</tr>\r\n</table>";
include '../'.$include_dir.'footer.inc.php';

?>