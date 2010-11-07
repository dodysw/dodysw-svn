<?php

include('entity.inc.php');
include('criteria.inc.php');

class EntityManager{

	
	function EntityManager()
	{
		
	}
	
	function getCabangs($start=0,$limit=0)
	{
		//$fields=array("rowid","kode_cabang","nama_cabang","keterangan");
		
		$tahapan = new tahapan_kerjasama_model();
		
		$kriteria = new Criteria("an_tahapan_kerjasama_tab",$tahapan->getFields());
		
		$sql = 'SELECT `rowid` , `kode_cabang` , `nama_cabang` , `keterangan` ';
		$sql .= 'FROM `an_cabang_tab` ';
		if($limit!=0 )
		{
			
			$sql .= 'WHERE 1 LIMIT ' . $start . ',' . $limit;
			
		}
		else 
		{
			$sql .= 'WHERE 1 LIMIT 0, 30';
		}
		
		//$kriteria->add("rowid",2);
		//$kriteria->add("nama_cabang","Cikampek");
		$kriteria->setOffset($start);
		$kriteria->setLimit($limit);

		
				
		$row= $this->getEntitys($tahapan,$kriteria);

			
		return $row;
		
        
     

		
	}
	
	function getEntitys($entity,$criteria)
	{
		$sql=$criteria->getSql();
		//echo $sql;
			$res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
        	$rows = array();

        	while ($row = mysql_fetch_array($res,MYSQL_ASSOC)) {
                
            	//$cabang = new CabangModel();            					            	
            	//$test="nama_cabang";
            	//$cabang->setFields($row['rowid'],$row['kode_cabang'],$row[$test],
            	//$row['keterangan']);
            	
            	$fieldsName=$entity->getFields();
            	$fieldsMethod=$entity->getFieldsMethod();
            	for($i=0;$i<count($fieldsName);$i++)
            	{
            		call_user_func(array(&$entity,$fieldsMethod[$i]),$row[$fieldsName[$i]]);
            		
            	}
            	
            	
            	//add objek to array
            	//echo "HUuuuuuuuuuuuuuuu" .$entity->getKode_cabang();
            	$rows[] = $entity;
                
            }
            
            return $rows;
		
	}
	
	function findProyekById($rowid)
	{
		//$fields=array("rowid","kode_cabang","nama_cabang","keterangan");

		$proyek = new proyek_model();

		$kriteria = new Criteria(an_proyek_tab,$proyek->getFields());

		$kriteria->add("rowid",$rowid);


		$kriteria->setOffset(0);
		$kriteria->setLimit(0);



		$row= $this->getEntity($proyek,$kriteria);


		return $row;


	}
	
	
	
	
	
	
}

?>