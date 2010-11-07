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
		$this->properties['filename'] = new Prop(array('label'=>'','colname'=>'filename','required'=>True,'is_key'=>false, 'inputtype'=>'file'));
		$this->properties['size'] = new Prop(array('label'=>'','colname'=>'size','datatype'=>'int', 'insertable'=>False, 'updatable'=>False));
		$this->properties['type'] = new Prop(array('label'=>'','colname'=>'type','insertable'=>False, 'updatable'=>False));
		$this->properties['module'] = new Prop(array('label'=>'','colname'=>'module','inputtype'=>'combobox', 'enumerate'=>'manage_modules'));
		$this->properties['param_1'] = new Prop(array('colname'=>'param_1',));
		$this->properties['param_2'] = new Prop(array('colname'=>'param_2',));
		$this->properties['param_3'] = new Prop(array('colname'=>'param_3',));
		$this->properties['param_4'] = new Prop(array('colname'=>'param_4',));
        $this->properties['create_date'] = new Prop(array('label'=>'Create Date','colname'=>'create_date','datatype'=>'datetime', 'insertable'=>False, 'updatable'=>False));

        $this->grid_command[] = array('download','Download single selected row file');
        $this->grid_command[] = array('','-----');
        $this->enum_keyval = array('rowid','filename');

    }

    function go() { // called inside main content
        $this->basic_handler();
    }

    function insert($rowindex) {
        # move file to proper place
        #~ echo '<pre>';print_r($_FILES);echo '</pre>';exit();
        $f = $this->get_files_array('filename',$rowindex);
        move_uploaded_file($f['tmp_name'], $f['suggest_path']);
        $this->ds->create_date[$rowindex] = 'Now()';
        $this->ds->size[$rowindex] = $f['size'];
        $this->ds->filename[$rowindex] = $f['name'];
        $this->ds->type[$rowindex] = $f['type'];
        $this->ds->path[$rowindex] = $f['suggest_path'];
        parent::insert($rowindex);
    }

    function update($rowindex) {
        # move file to proper place
        $f = $this->get_files_array('filename',$rowindex);
        if ($f['name'] != '') {
            move_uploaded_file($f['tmp_name'], $f['suggest_path']);
            $this->ds->size[$rowindex] = $f['size'];
            $this->ds->filename[$rowindex] = $f['name'];
            $this->ds->type[$rowindex] = $f['type'];
            $this->ds->path[$rowindex] = $f['suggest_path'];
        }
        parent::update($rowindex);
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
        #~ print_r($f);
        if ($f['name'] == '')
            return '';
        #~ echo 'Go go go!';
        move_uploaded_file($f['tmp_name'], $f['suggest_path']);
        $rowid = $this->insert_row(array('filename'=>$f['name'], 'size'=>$f['size'], 'type'=>$f['type'], 'path'=>$f['suggest_path'], 'create_date'=>date('Y-m-d H:i:s'), 'module'=>$module, 'param_1'=>$param_1, 'param_2'=>$param_2, 'param_3'=>$param_3, 'param_4'=>$param_4));
        #~ echo 'Got!'.$rowid;
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