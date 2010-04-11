<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class upload_manager extends TableManager {
    var $db_table, $properties;

    function upload_manager() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Upload Manager';
        $html_title = $this->title;
        #~ $this->db_table = $GLOBALS['dbpre'].'account_tab';
        $this->properties['path'] = new Prop(array('label'=>'','colname'=>'path','required'=>True,'is_key'=>False,'insertable'=>False,));
		$this->properties['filename'] = new Prop(array('label'=>'','colname'=>'filename','is_key'=>false));
		#~ $this->properties['upload_filename'] = new Prop(array('cdatatype'=>'file', 'colname'=>'upload_filename', 'required'=>True));
		$this->properties['size'] = new Prop(array('colname'=>'size','datatype'=>'int', 'insertable'=>False, 'updatable'=>False));
		$this->properties['type'] = new Prop(array('colname'=>'type','insertable'=>False, 'updatable'=>False));
		$this->properties['module'] = new Prop(array('colname'=>'module','inputtype'=>'combobox', 'enumerate'=>'manage_modules'));
		$this->properties['param_1'] = new Prop(array('colname'=>'param_1',));
		$this->properties['param_2'] = new Prop(array('colname'=>'param_2',));
		$this->properties['param_3'] = new Prop(array('colname'=>'param_3',));
		$this->properties['param_4'] = new Prop(array('colname'=>'param_4',));

        $this->properties['creation_date_time'] = new Prop(array('cdatatype'=>'creation_date_time'));
        $this->properties['created_by'] = new Prop(array('cdatatype'=>'created_by'));
        $this->properties['last_update_date_time'] = new Prop(array('cdatatype'=>'last_update_date_time'));
        $this->properties['last_updated_by'] = new Prop(array('cdatatype'=>'last_updated_by'));
        $this->properties['last_updating_process'] = new Prop(array('cdatatype'=>'last_updating_process'));

        $this->grid_command[] = array('download','Download');
        $this->enum_keyval = array('rowid','filename');

    }

    function go() { // called inside main content
        $this->basic_handler();
    }

    function remove($rowindex) {
        @unlink($this->ds->path[$rowindex]);
        parent::remove($rowindex);
    }

    function get_files_array($fieldname, $rowindex) {
        $arr = array();
        #~ print_r($_FILES);
        $arr['name'] = $_FILES['field']['name'][$fieldname][$rowindex];
        $arr['size'] = $_FILES['field']['size'][$fieldname][$rowindex];
        $arr['type'] = $_FILES['field']['type'][$fieldname][$rowindex];
        $arr['tmp_name'] = $_FILES['field']['tmp_name'][$fieldname][$rowindex];
        $arr['suggest_path'] = APP_PATH_ROOT.'/upload_files/'.md5(time().$_FILES['field']['size'][$fieldname][$rowindex]);
        return $arr;
    }

    function put_file($fieldname, $rowindex, $module='', $param_1='', $param_2='', $param_3='', $param_4='') {
        /* move uploaded file to proper folder, add an entry to this module, and return the rowid to the callee
        @fieldname = html field name, containing <input type='file'>
        @rowindex = on form, this should always be 0, on table, this should be the row number for the field
        */
        $f = $this->get_files_array($fieldname, $rowindex);
        if ($f['name'] == '')
            return '';
        move_uploaded_file($f['tmp_name'], $f['suggest_path']);
        #~ $rowid = $this->insert_row(array('filename'=>$f['name'], 'size'=>$f['size'], 'type'=>$f['type'], 'path'=>$f['suggest_path'], 'create_date'=>date('Y-m-d H:i:s'), 'module'=>$module, 'param_1'=>$param_1, 'param_2'=>$param_2, 'param_3'=>$param_3, 'param_4'=>$param_4));
        $data = array('filename'=>$f['name'], 'size'=>$f['size'], 'type'=>$f['type'], 'path'=>$f['suggest_path'], 'module'=>$module, 'param_1'=>$param_1, 'param_2'=>$param_2, 'param_3'=>$param_3, 'param_4'=>$param_4);
        $this->clear();
        foreach ($data as $k=>$v) $this->ds->{$k}[] = $v;   # populate datasource from array
        $rowid = $this->insert(0);
        return $rowid;
    }

    function del_file($rowid) {
        /* given rowid, remote its entry, and delete its file
        */
        # get the path first
        $row = $this->get_row(array('rowid'=>$rowid));
        if (!$row) return False;
        @unlink($row['path']);       # delete the file
        $this->remove_row(array('rowid'=>$rowid));      # delete the row
        return True;
    }

    function download_file($rowid) {
        while (@ob_end_clean());
        if (is_array($rowid)) $rowid = $rowid[0];
        $row = $this->get_row(array('rowid'=>$rowid));
        if (!$row) return False;
        header('Content-type: '.$row['type']);
        header('Content-Length: '.$row['size']);
        header('Content-Disposition: inline; filename="'.$row['filename'].'"');
        header('Pragma: public');
        readfile($row['path']);
        exit();
    }

    function act_download($is_post) {
        $this->download_file($_REQUEST['rowid']);
    }

}

?>