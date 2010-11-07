<? include 'admin/config.inc.php'; include 'admin/include/func.inc.php'; ?>
<?
    /*
    client-side file retrieval, used for image viewing. just need the rowid of upload manager's row
    */
    function download_file($rowid) {
        $um = instantiate_module('upload_manager');
        while (@ob_end_clean());
        if (is_array($rowid)) $rowid = $rowid[0];
        $row = $um->get_row(array('rowid'=>$rowid));
        if (!$row) return False;
        header('Content-type: '.$row['type']);
        header('Content-Length: '.$row['size']);
        header('Content-Disposition: inline; filename="'.$row['filename'].'"');
        header('Pragma: public');
        readfile($row['path']);
        exit();
    }
    if (secure_hash_ok($_REQUEST['id'],$_REQUEST['secure']))
        download_file($_REQUEST['id']);
    else
        die('parameter has been tempered');
?>