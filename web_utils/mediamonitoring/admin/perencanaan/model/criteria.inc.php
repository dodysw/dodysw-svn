<?php

class Criteria{
	

	var $tableName;
	var $fields;
	var $sql;
	var $sqlWhere;
	var $sqlSelect;
	var $sqlLimit;
	var $isBuild;
	var $limit;
	var $offset;
	var $orderby;
	var $groupby;
	var $isGroup;
	var $isOrder;
	
	
	function Criteria($table,$fields)
	{
		
		$this->fields=$fields;
		$this->sqlWhere= array();
		$this->tableName = array();
		$this->tableName[]=$table;
		$this->sqlSelect=join(",",$fields);
		$this->sql="select " . $this->sqlSelect . " from ";
		$this->isBuild=false;
		$this->sqlLimit=" limit ";
		$this->groupby= array();
		$this->isGroup=false;
		$this->isOrder=false;
		
		$this->limit=0;
		$this->offset=0;
		
	}
	
	/*SELECT nama, an_cabang_tab.kode_cabang
FROM an_proyek_tab, an_cabang_tab
WHERE an_proyek_tab.kode_cabang = an_cabang_tab.kode_cabang
*/
	
	function add($field,$value) 
	{
		
		if( count($this->sqlWhere)==0)
		{
				$this->sqlWhere[]= "where " .$field . "='" . $value . "'";
		}
		else 
		{
			    $this->sqlWhere[]= "and " .$field . "='" . $value . "'";
		}
	}
	
	function addOperator($field,$value,$operator) 
	{
		
		if( count($this->sqlWhere)==0)
		{
				$this->sqlWhere[]= "where " .$field . "='" . $value . "'";
		}
		else 
		{
			    $this->sqlWhere[]= "and " .$field . $operator . "'" . $value . "'";
		}
	}
	
	function addOr($field,$value)
	{
		if( count($this->sqlWhere)==0)
		{
				$this->sqlWhere[]= "where " .$field . "='" . $value . "'";
		}
		else 
		{
			    $this->sqlWhere[]= "or " .$field . "='" . $value . "'";
		}
		
	}
	
	function addOrOperator($field,$value,$operator)
	{
		if( count($this->sqlWhere)==0)
		{
				$this->sqlWhere[]= "where " .$field . "='" . $value . "'";
		}
		else 
		{
			    $this->sqlWhere[]= "or " .$field . $operator . "'" . $value . "'";
		}
		
	}
	
	function addGroupby($field)
	{
		$this->groupby[]=$field;
		$this->isGroup=true;
		
	}
	
	function addOrderby($field,$type)
	{
		$this->orderby=$field . " " . $type;
		$this->isOrder=true;
		
	}
	
	function setLimit($limit)
	{
		$this->limit=$limit;
	}
	
	function setOffset($offset)
	{
		$this->offset=$offset;	
	}
	
	function getSql()
	{
		if(! $this->isBuild )
		{
			if($this->limit==0)
			{
				$this->sql.= join(",",$this->tableName) . " " . join(" ",$this->sqlWhere);
				
				if($this->isGroup) $this->sql.=" " . join(",",$this->groupby);
				if($this->isOrder) $this->sql.=" " . $this->orderby;
			}
			else
			{
				
				$this->sql.= join(",",$this->tableName) . " " . join(" ",$this->sqlWhere);
				
				if($this->isGroup) $this->sql.=" " . join(",",$this->groupby);
				if($this->isOrder) $this->sql.=" " . $this->orderby;
				
				$this->sql.= $this->sqlLimit . $this->offset .",".$this->limit;
			}
			
		}
		return $this->sql;
		
	}
	
}

?>