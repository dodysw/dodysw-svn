<?
/*
custom datatype definitions, used to speedup field definition, by just using often-used field defs
*/
global $all_cdatatypes;

$all_cdatatypes = array(
    'creation_date_time' => array('colname'=>'creation_date_time', 'datatype'=>'datetime', 'updatable'=>False, 'insertable'=>False, 'on_insert_callback'=>'return date("Y-m-d H:i:s");'),
    'created_by' => array('colname'=>'created_by', 'datatype'=>'int', 'updatable'=>False, 'inputtype'=>'combobox', 'insertable'=>False, 'enumerate'=>'usrmgr', 'on_insert_callback'=>'return $_SESSION[\'login_id\'];'),
    'last_update_date_time' => array('colname'=>'last_update_date_time', 'datatype'=>'datetime', 'updatable'=>False, 'insertable'=>False, 'on_update_callback'=>'return date("Y-m-d H:i:s");'),
    'last_updated_by' => array('colname'=>'last_updated_by', 'datatype'=>'int', 'updatable'=>False, 'inputtype'=>'combobox', 'insertable'=>False, 'enumerate'=>'usrmgr', 'on_update_callback'=>'return $_SESSION[\'login_id\'];'),
    'last_updating_process' => array('colname'=>'last_updating_process', 'length'=>'30', 'updatable'=>False, 'insertable'=>False, 'on_update_callback'=>'return $this->module;', 'queryable'=>False),
    'image' => array('inputtype'=>'file', 'datatype'=>'int'),
    'file' => array('inputtype'=>'file', 'datatype'=>'int'),
    'money' => array('datatype'=>'float','prefix_text'=>'Rp '),
    'email' => array('datatype'=>'text', 'on_validate'=>'if ($value=="") return 1; return (eregi("^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$", $value))? True: "There is a problem with the email";'),
    'fkey' => array('datatype'=>'int', 'inputtype'=>'combobox'),
    'bool' => array('datatype'=>'bool', 'inputtype'=>'checkbox'),
    'client_ip_addr' => array('length'=>15, 'on_insert_callback'=>'return $_SERVER["REMOTE_ADDR"];'),
    );

$moncoong = '100';
?>