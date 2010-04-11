<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/auth.inc.php');
include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class progress_kerjasama extends TableManager {
    var $db_table, $properties;
    function progress_kerjasama() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Progress Kerjasama';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'progress_kerjasama_tab';
        //$this->properties['tahapan'] = new Prop(array('hidden'=>false, 'label'=>'Tahapan','colname'=>'tahapan','required'=>True,'datatype'=>'int','parentkey'=>'rowid','insertable'=>true));
        $this->properties['tahapan'] = new Prop(array('hidden'=>false, 'label'=>'Tahapan Kerjasama','colname'=>'tahapan','required'=>True,'datatype'=>'int','insertable'=>true,'enumerate'=>'tahapan_kerjasama'));
        $this->properties['proyek_id'] = new Prop(array('hidden'=>false, 'label'=>'Proyek','colname'=>'proyek_id','required'=>True,'parentkey'=>'kerjasama_id','datatype'=>'int','insertable'=>true,'enumerate'=>'proyek'));
        $this->properties['valid_from'] = new Prop(array('label'=>'Tanggal progress','colname'=>'valid_from','required'=>True,'datatype'=>'date'));
        $this->properties['persentase'] = new Prop(array('label'=>'Persentase','colname'=>'persentase','required'=>True,'datatype'=>'int'));
        #~ $this->enum_keyval = array('rowid','nama');
		 $this->_tahapan = $_REQUEST['tahapan'];
		 $this->baris=$_REQUEST['proyek'];

    }

    function go() { # called inside main content
        #~ echo "<h3>Progress Kerjasama</h3>";
        $this->basic_handler();
    }


    #~ function create_sql_select() {
        #~ $this->_mapping_index = array('tahapan', 'tipe_kerjasama', 'valid_from', 'persentase');
        #~ return $sql = "select tahapan, tipe_kerjasama, valid_from, persentase from {$this->db_table} where valid_from <= '2004-04-01'";

    #~ }

    #~ function enum_list() { # return list of id/desc
        #~ $enumlist = array();
        #~ foreach ($this->get_rows('', 'cat_id,name','row') as $row) $enumlist[$row[0]] = $row[1];
        #~ return $enumlist;
    #~ }

    #~ function enum_decode($id) { # return desc of id
        #~ $row = $this->get_row(array('cat_id'=>$id), 'description');
        #~ return $row['description'];
    #~ }
	 function showuniq() {
        # if this datasource has detail, and in update/show mode, show it as grid
       // if (($this->action=='edit' or $this->action=='view' or $this->action=='browse') and $this->childds) {
            //foreach ($this->childds as $child_modulename) {
                include_once(APP_MODULE_ROOT .'/'. 'tahapan_kerjasama'.'.mod.php');
                $child_module = new tahapan_kerjasama();
                //$child_module->logical_parent = $this; # 1. for parent: bind children to parent
                $child_module->populate();
                #~ echo '<h4><i>'.$child_module->title.'</i></h4>';
                echo '<hr>';
				
                $child_module->showgrid('browse');
            //}
        //}
    }

	function shownewrecord() {
        if ($this->allow_new) {
				$this->logger->debug('show new record for progress only overriden'  );

            #~ echo "<p><a href='{$_SERVER['PHP_SELF']}?m={$this->module}&act=new&go=".urlencode($GLOBALS['full_self_url'])."'>Insert new row</a>";
            echo '<form method=POST action="'.$_SERVER['PHP_SELF'].'">';
            echo '<input type=hidden name="m" value="'.'proyek'.'">';
            echo '<input type=hidden name="act" value="req_list">';
			echo '<input type=hidden name="proyek" value="'.$this->baris.'">';
            echo '<input type=hidden name="go" value="index.php?m=proyek&act=view&num_row=1">';         # url to go after successful submitation
            # if i'm a detail, get master-detail field's value and pass it to new-form
            if ($this->logical_parent) {
                foreach ($this->properties as $colvar=>$col) {
                    if ($col->parentkey and is_bool($col->parentkey)) {   # get the value from parent's same column name ds fields
                        foreach ($this->logical_parent->properties as $p_colvar=>$p_col) {
                            if ($col->colname == $p_col->colname) {
                                echo '<input type=hidden name="field['.$colvar.'][0]" value="'.htmlentities($this->logical_parent->ds->{$p_colvar}[0]).'">';
                            }
                        }
                    }
                    elseif ($col->parentkey and is_string($col->parentkey)) {
                        $sql_where[] = $col->colname."='".addslashes($this->logical_parent->ds->{$col->parentkey}[0])."'";    # parent must only has 1 row
                        echo '<input type=hidden name="field['.$colvar.'][0]" value="'.htmlentities($this->logical_parent->ds->{$col->parentkey}[0]).'">';
                    }
                }
            }
            echo 'Add new record: <input type=text name="num_row" size=2 value="1"> <input type=submit value=Go></p>';
            #~ echo "<p><a href='{$_SERVER['PHP_SELF']}?m={$this->module}&act=new&go=".urlencode($GLOBALS['full_self_url'])."'>Insert new row</a>";
            echo '</form>';
            #~ print_r($this->logical_parent->ds);
        }
    }

	 function showgrid($action='',$baris) {
		 $this->baris=$baris;
        # show data grid, for query, multi edit, and multi new
        $action = ($action == '')? $this->action: $action;
	$this->logger->debug('show grid progress' );
		$this->logger->debug('show grid baris' . $baris);

        $this->showerror();
#~ debug(); # BUG: UPDATE, POST-ERROR, ENTERED VALUE DOES NOT GET PASSED TO INPUT-VALUE
        global $last_message;
        if ($last_message != '') echo $last_message;    # place holder for anyone who whises to put msg above grid

        if ($this->allow_delete) {
            $this->grid_command[] = array('del','Delete');  # command, need to be list of list, since key may be duplicated
        }
        if ($this->allow_edit) {
            $this->grid_command[] = array('edit','Edit');  # command, need to be list of list, since key may be duplicated
        }
        if ($this->allow_new) {
            $this->grid_command[] = array('duplicate','Duplicate');  # command, need to be list of list, since key may be duplicated
        }
        if ($this->allow_view) {
            $this->grid_command[] = array('view','View');  # command, need to be list of list, since key may be duplicated
        }
        #~ if ($this->allow_query and $action == 'browse' and !$this->logical_parent) {
            #~ $this->showquery();
        #~ }

        // parse property for fields to display, and show datasource browse

        # decide, which columns to show in table
        $this->colgrid = array();
        foreach ($this->properties as $key=>$col) {
            if ($action == 'browse' and $col->hidden) continue;
            $this->colgrid[] = $key;

        }
        # prepare javascript validation and confirmation function
        echo '<script>
            function submit_confirm(myform) {
                action = myform.elements[\'act\'].value;
                cb = myform.elements[\'rowid[]\'];
                num_checked = 0;
                for (var i = 0; i < cb.length; i++) if (cb[i].checked) num_checked++;
                if ((action == \'del\' || action == \'edit\' || action == \'duplicate\') && num_checked == 0) {
                    alert(\'You need to select at least 1 record\');
                    return false;
                }
                if (action == \'del\' && confirm(\'Are you sure you want to delete?\')) {
                    myform.submit();
                }
                else if (action == \'duplicate\' && confirm(\'Are you sure you want to duplicate?\')) {
                    myform.submit();
                }
                else {
                    myform.submit();
                }
            }
            </script>
            ';

        # create table view
        echo '<form name="gridform" method=post action="'.$_SERVER['PHP_SELF'].'" onSubmit="return submit_confirm(this)">';
        echo '<input type=hidden name=m value="'.$this->module.'">';
        if ($action == 'browse') {
            echo '<input type=hidden name=go value="'.htmlentities($GLOBALS['full_self_url']).'">';         # url to go after successful submitation
        }
        else {
            echo '<input type=hidden name=go value="'.htmlentities($this->_go).'">';         # url to go after successful submitation
            echo '<input type=hidden name="act" value="'.$action.'">';
            echo '<input type=hidden name="num_row" value="'.$this->db_count.'">';
            echo '<input type=hidden name="save" value="1">';         # marker to indicate form submitation
        }

        echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">';  //style="border-collapse: collapse;"
        echo '<tr>';
        echo '<td><b>'.ucfirst($this->action).": {$this->title}</b> <small>[<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&set_browse_mode=form'>form</a>]</small> </td>";
        echo '<td align="right">';
        if ($this->allow_new) {
            $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&act=new&num_row=1&go='.urlencode($GLOBALS['full_self_url']);
            echo '<a href="'.$url.'"><img src="images/b_new.png" border="0"> new</a>';
        }
        echo '</td>';
        echo '</tr></table>';  //outer table

        echo '<table border="0" cellpadding="2" cellspacing="1">';  //style="border-collapse: collapse;"
        echo '<tr class="greyformtitle">';
        echo '<th colspan=3>&nbsp;</th>';   # for command
        foreach ($this->colgrid as $colvar) {
            $col = $this->properties[$colvar];
            if ($action == 'edit' and !$col->updatable) continue;
            if ($action == 'new' and !$col->insertable) continue;
            if ($action == 'browse') {    # browse: enable sort by table col
                $_sortdir = $this->_sortdir != ''? $this->_sortdir : 'ASC';
                if ($this->_orderby == $col->colname and $this->_sortdir != '') {
                    $_sortdir = ($_sortdir == 'ASC')? 'DESC': 'ASC'; # swap ASC/DESC for currently sorted column name
                }
                echo "<th><a href='{$_SERVER['PHP_SELF']}?m={$this->module}&act=browse&row={$this->_rowstart}&orderby={$col->colname}&sortdir={$_sortdir}'>{$col->label}</a>";
                if ($this->_orderby == $col->colname and $this->_sortdir != '') {
                    echo ' <img src="images/'.(($_sortdir == 'ASC')? 'asc_order.png': 'desc_order.png').'" border="0">';
                }
                echo '</th>';
            }
            else {
                echo '<th>'.$col->label.'</th>';
            }

        }
        echo "</tr>\r\n";

        if ($this->db_count > 0 or $_REQUEST['row'] != '') {
            for ($i = 0; $i < $this->db_count; $i++) {
                /*  for edit, we want the row to be sorted just as how it was selected in browse
                    this is the striking difference between this->ds->_rowid (coming from database, sorted as "sort by") and this->_rowid (coming from request, sorted as from request)
                */
                $rowindex = $i;
                if ($action == 'edit') {
                    $rowindex = array_search($this->_rowid[$i], $this->ds->_rowid); # mangle rowindex
                }

                $rowcolour = ($i % 2 == 0)? 'greyformlight': 'greyformdark';
                if ($this->error_rows[$i]) $rowcolour = 'error';
                echo '<tr class="'.$rowcolour.'" valign="top">';

                if ($action == 'browse')
				{	//echo "dasd afa";
                    echo "<td><input type=checkbox name='rowid[]' value='{$this->ds->_rowid[$rowindex]}'></td>";
				}
                else {
                    echo '<input type=hidden name="rowid['.$rowindex.']" value="'.$this->_rowid[$rowindex].'">';   # for edit-action
                    echo '<td colspan="3">'.($i+1).'</td>';
                }

                if ($this->allow_edit and $action == 'browse')
                    echo "<td>&nbsp;<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&act=edit&rowid[]={$this->ds->_rowid[$rowindex]}&go=".urlencode($GLOBALS['full_self_url'])."'><img src='images/button_edit.png' border=0></a></td>";
                #~ else
                    #~ echo '<td>&nbsp;</td>';

                if ($this->allow_view and $action == 'browse')
                    echo "<td>&nbsp;<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&act=view&rowid[]={$this->ds->_rowid[$rowindex]}&go=".urlencode($GLOBALS['full_self_url'])."'><img src='images/button_view.png' border=0></a>&nbsp;</td>";
                #~ else
                    #~ echo '<td>&nbsp;</td>';

                foreach ($this->colgrid as $colvar) {
                    $col = $this->properties[$colvar];
                    if ($action == 'edit' and !$col->updatable) continue;
                    if ($action == 'new' and !$col->insertable) continue;
                    if ($action == 'edit' or $action == 'new') {
                        echo '<td>';
                        if ($action == 'edit' and $col->is_key)
                            echo '<b>'.$this->ds->{$colvar}[$rowindex].'</b>';
                        else {


							
								$this->logger->debug('masuk elsenya colname = ' . $col->colname  );

								$this->input_widget("field[$colvar][$rowindex]", $this->ds->{$colvar}[$rowindex], $colvar);
							

                        }
                        echo '</td>';
                    }
                    else {
                        if ($col->enumerate) { # if field is enumerated, get the enumerate value instead
                            if (is_string($col->enumerate)) {
                                include_once(APP_MODULE_ROOT .'/'. $col->enumerate.'.mod.php');
                                $e = new $col->enumerate();
                                $value = $e->enum_decode($this->ds->{$colvar}[$rowindex]);
								$this->logger->debug('show grid ..String' . $e->enum_decode($this->ds->{$colvar}[$rowindex]));
                            }
                            elseif (is_array($col->enumerate)) {
                                $value = $col->enumerate[$this->ds->{$colvar}[$rowindex]];
								$this->logger->debug('show grid ..Array' . $col->enumerate[$this->ds->{$colvar}[$rowindex]]);
                            }

                        }
                        else {
                            $value = $this->ds->{$colvar}[$rowindex];
                        }
                        $maxchar = $col->browse_maxchar;
                        if ($maxchar > 0 and strlen($value) > $maxchar) {
                            $value = substr($value,0,$maxchar).'..';
                        }
                        $value = htmlentities($value);
                        if ($value == '') $value = '&nbsp;';
                        if ($col->is_key)
                            echo '<td><b>'.$value.'</b></td>';
                        else
                            echo '<td>'.$value.'</td>';
                    }
                }
                echo "</tr>\r\n";
            }
            echo '</table>';
        }
        else {
            echo '</table>';
            echo '<p><i>No records</i>';
        }

        if ($action == 'edit' or $action == 'new') {
            echo '<p><input type=submit value=" Save "> | ';
            echo '<b><a href="'.$this->_go.'">Cancel</a></b></p>';
            #~ echo '<b><a href="'.$this->_go.'" onclick="window.history.back();return false;">Cancel</a></b></p>';
        }

        if ($action == 'browse' and $this->db_count > 0 ) {
            echo '<script>function setCheckBoxes(do_check) { var cb = document.forms[\'gridform\'].elements[\'rowid[]\']; if (cb.length) {for (var i = 0; i < cb.length; i++) cb[i].checked = do_check} else {cb.checked = do_check}; return true;}</script>';
            echo '<img src="images/arrow_ltr.png" border="0">';
            echo '<a href="'.$_SERVER['PHP_SELF'].'" onclick="setCheckBoxes(true);return false;">Check All</a> / ';
            echo '<a href="'.$_SERVER['PHP_SELF'].'" onclick="setCheckBoxes(false);return false;">Uncheck All</a> ';
            echo '<select name=act onchange="return submit_confirm(this.form)"><option value="">With selected:</option>';
            foreach ($this->grid_command as $command) {
                echo "<option value='{$command[0]}'>{$command[1]}</option>";
            }
            echo '</select>';
            echo '<input type=submit value=Do>';

            # build page browsing below table
            echo '<br>';
            $max_rownum = $this->max_rownum();
            if ($this->browse_rows > 0) {
                #~ $current_row = $_REQUEST['row'] == ''? 0: $_REQUEST['row'];
                if ($max_rownum > $this->db_count) {    # split into pages
                    echo 'Pages: ';
                    $pages = array();
                    for ($rowidx = 0, $pg = 1; $rowidx < $max_rownum; $rowidx += $this->browse_rows, $pg += 1) {
                        if ($this->_rowstart == $rowidx)
                            $pages[] = "<b>$pg</b>";
                        else
                            #~ $pages[] = "<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&act=browse&row={$rowidx}'>$pg</a>";
                            $pages[] = "<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&act=browse&row={$rowidx}&orderby={$this->_orderby}&sortdir={$this->_sortdir}'>$pg</a>";
                    }
                    echo join(' | ',$pages);
                    echo ' / ';
                }
            }
            echo 'Total: '.$max_rownum.'';
            if ($this->browse_rows > 0 and $max_rownum > $this->db_count) {
                echo " - <a href='{$_SERVER['PHP_SELF']}?m={$this->module}&act=browse&maxrows=0&orderby={$this->_orderby}&sortdir={$this->_sortdir}'>Show All</a>";
            }


        }
        echo '</form>';

        if ($action == 'browse') {
            $this->shownewrecord();
        }
    }
	    function showform() {

        $this->showerror();

        # make sure cursor does not point to invalid index
        if ($this->_cursor > ($this->db_count-1)) $this->_cursor = $this->db_count-1;
        if ($this->_cursor < 0) $this->_cursor = 0;

        echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
        echo '<input type=hidden name=act value="'.$this->action.'">';   # contains the action (edit/new)
        echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
        echo '<input type=hidden name=go value="index.php?m=proyek&act=view&rowid[]='.$this->baris.'">';         # url to go after successful submitation
        echo '<input type=hidden name="num_row" value="'.$this->db_count.'">';
        echo '<input type=hidden name="rowid['.$this->_cursor.']" value="'.$this->ds->_rowid[$this->_cursor].'">';   # for edit-action

        echo '<table width="100%" border="0" cellpadding="0" cellspacing="0">';  //style="border-collapse: collapse;"
        echo '<tr>';
        echo '<td><b>'.ucfirst($this->action).": {$this->title}</b> <small>[<a href='{$_SERVER['PHP_SELF']}?m={$this->module}&set_browse_mode=table'>table</a>]</small></td>";
        if ($this->action == 'browse') {
            echo '<td align="center">';
            echo ' ['.($this->_cursor+1).'/'.$this->db_count.'] ';
            # determine on which index current rowid is
            if ($this->_cursor > 0) {
                $r = 0;
                $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&act=browse&cursor='.$r.'&orderby='.$this->_orderby.'&sortdir='.$this->_sortdir;
                echo '<a href="'.$url.'"><img src="images/b_firstpage.gif" border="0"></a> ';
                $r = $this->_cursor - 1;
                $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&act=browse&cursor='.$r.'&orderby='.$this->_orderby.'&sortdir='.$this->_sortdir;
                echo '<a href="'.$url.'"><img src="images/b_prevpage.gif" border="0"></a> ';
            }
            else {
                echo '<img src="images/bd_firstpage.gif" border="0"> ';
                echo '<img src="images/bd_prevpage.gif" border="0"> ';
            }

            if ($this->_cursor < ($this->db_count -1)) {
                $r = $this->_cursor + 1;
                $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&act=browse&cursor='.$r.'&orderby='.$this->_orderby.'&sortdir='.$this->_sortdir;
                echo '<a href="'.$url.'"><img src="images/b_nextpage.gif" border="0"></a> ';
                $r = $this->db_count - 1;
                $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&act=browse&cursor='.$r.'&orderby='.$this->_orderby.'&sortdir='.$this->_sortdir;
                echo '<a href="'.$url.'"><img src="images/b_lastpage.gif" border="0"></a> ';
            }
            else {
                echo '<img src="images/bd_nextpage.gif" border="0"> ';
                echo '<img src="images/bd_lastpage.gif" border="0"> ';
            }
            echo '</td>';

            echo '<td>';
            if ($this->allow_new) {
                $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&act=new&num_row=1&go='.urlencode($GLOBALS['full_self_url']);
                echo '<a href="'.$url.'"><img src="images/b_new.png" border="0"> new</a> | ';
            }
            if ($this->db_count and $this->allow_edit) {
                $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&act=edit&rowid[]='.$this->ds->_rowid[$this->_cursor].'&go='.urlencode($GLOBALS['full_self_url']);
                echo '<a href="'.$url.'"><img src="images/b_edit.png" border="0"> edit</a> | ';
            }
            if ($this->db_count and $this->allow_delete) {
                $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&act=del&rowid[]='.$this->ds->_rowid[$this->_cursor].'&go='.urlencode($GLOBALS['full_self_url']);
                $onclick = $this->confirm_delete? 'onClick="return confirm(\'Are you sure you want to delete this record?\');"': '';
                echo '<a href="'.$url.'" '.$onclick.'><img src="images/b_drop.png" border="0"> delete</a> | ';
            }
            if ($this->db_count and $this->allow_new) {
                $url = $_SERVER['PHP_SELF'].'?m='.$this->module.'&act=duplicate&rowid[]='.$this->ds->_rowid[$this->_cursor].'&go='.urlencode($GLOBALS['full_self_url']);
                $onclick = $this->confirm_duplicate? 'onClick="return confirm(\'Are you sure you want to duplicate this record?\');"': '';
                echo '<a href="'.$url.'" '.$onclick.'><img src="images/duplicate.png" border="0"> dupe</a> | ';
            }

            echo '</td>';

        }
        echo '</tr></table>';  //outer table

        echo '<table border="0">';
        # decide, which columns to show in form
        $this->colgrid = array();
        foreach ($this->properties as $key=>$col) {
            #~ if ($col->hidden) continue;
            if ($this->action == 'edit' and !$col->updatable) continue;
            if ($this->action == 'new' and !$col->insertable) continue;
            $this->colgrid[] = $key;
        }
        $i = 0; # html table rows
        $i2 = 0; # datasource columns
        foreach ($this->colgrid as $colvar) {
            $i2++;
            $col = $this->properties[$colvar];
            if ($this->action != 'browse' or $this->browse_mode != 'form' or ($i2 % $this->browse_form_cols == 1)) {
                $rowcolour = ($i++ % 2 == 0)? 'greyformlight': 'greyformdark';
                echo '<tr class="'.$rowcolour.'">';
            }

            if ($col->is_key)
                echo '<td><b>'.$col->label.'</b></td>';
            else
                echo '<td>'.$col->label.'</td>';
            echo '<td>';
            if ($this->action == 'edit' and $col->is_key)
                echo '<b>'.$this->ds->{$colvar}[$this->_cursor].'</b>';
            else
			{
                
				if( $col->colname == 'tahapan')
				{
						$this->logger->debug('masuk if colname = ' . $col->colname  );
//						$this->input_widget("field[$colvar][$rowindex]", $this->_tahapan, $colvar);

						$this->input_widget("field[$colvar][{$this->_cursor}]", $this->_tahapan, $colvar);

				}
				elseif( $col->colname == 'proyek_id')
				{
						$this->logger->debug('masuk if colname = ' . $col->colname  );
//						$this->input_widget("field[$colvar][$rowindex]", $this->_tahapan, $colvar);

						$this->input_widget("field[$colvar][{$this->_cursor}]", $this->baris, $colvar);

				}
				else
				{
						$this->logger->debug('masuk else colname = ' . $col->colname  );
						$this->input_widget("field[$colvar][{$this->_cursor}]", $this->ds->{$colvar}[$this->_cursor], $colvar);

//						$this->input_widget("field[$colvar][$rowindex]", $this->ds->{$colvar}[$rowindex], $colvar);
				}


			}
            echo '</td>';

            if ($this->action != 'browse' or $this->browse_mode != 'form' or ($i2 % $this->browse_form_cols == 0)) {
                echo "</tr>\r\n";
            }

        }
        echo '</table>';
        if ($this->action != 'browse') {
            echo '<input type=submit value="Save">  | ';
            echo '<b><a href="'.$this->_go.'">Cancel</a></b></p>';
            #~ echo '<b><a href="" onclick="window.history.back();return false;">Cancel</a></b>';
        }
        echo '</form>';

        #~ if ($this->action == 'browse') {
            #~ $this->shownewrecord();
        #~ }
    }


}

?>