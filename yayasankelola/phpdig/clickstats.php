<?php
// clickstats.php?num=X&url=www.foo.bar&val=query
//=================================================
//insert an entry in clicks
$relative_script_path = '.';
$no_connect = 0;

if (is_file("$relative_script_path/includes/config.php")) {
    include "$relative_script_path/includes/config.php";
}
else {
    die("Cannot find config.php file.\n");
}

extract(phpdigHttpVars(
     array('num'=>'integer',
           'url'=>'string',
           'val'=>'string'
           )
     ));

phpdigClickLog($id_connect,$num,$url,$val);

function phpdigClickLog($id_connect,$num='',$url='',$val='') {

  if ($num != '' && $url != '' && $val != '') {
    $num = (int) $num;
    $url = addslashes(str_replace("\\","",stripslashes(urldecode($url))));
    $val = addslashes(str_replace("\\","",stripslashes(urldecode($val))));

    $query = "INSERT INTO ".PHPDIG_DB_PREFIX."clicks (c_num,c_url,c_val,c_time) VALUES ($num,'".$url."','".$val."',NOW())";
    @mysql_query($query,$id_connect);

    //return mysql_insert_id($id_connect);
  }

}

?>