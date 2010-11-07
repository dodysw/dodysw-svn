<?
/* main news
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class news extends TableManager {
    var $db_table, $properties;
    function news() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('News');
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'news_tab';
        $this->properties['title'] = new Prop(array('label'=>lang('Title'),'colname'=>'title','required'=>True,'length'=>100));
        $this->properties['category'] = new Prop(array('label'=>lang('Category'),'colname'=>'cat_id','inputtype'=>'combobox','enumerate'=>'news_cat_enum2'));
        $this->properties['media'] = new Prop(array('label'=>lang('Media'),'colname'=>'media_id','inputtype'=>'combobox','enumerate'=>'news_media'));
        $this->properties['tone'] = new Prop(array('label'=>lang('Tone'),'colname'=>'tone_id','inputtype'=>'combobox','enumerate'=>'news_tone'));
        $this->properties['create_date'] = new Prop(array('label'=>lang('Date'),'colname'=>'create_date','datatype'=>'datetime'));
        $this->properties['summary'] = new Prop(array('label'=>lang('Summary'),'colname'=>'summary', 'datatype'=>'text','required'=>True,'inputtype'=>'textarea','rows'=>5,'browse_maxchar'=>30));
        $this->properties['body'] = new Prop(array('label'=>lang('Body'),'colname'=>'body','datatype'=>'text','inputtype'=>'textarea','inputtype2'=>'htmlarea','rows'=>10,'browse_maxchar'=>30));
        $this->properties['comm_value'] = new Prop(array('label'=>lang('Commercial value'),'colname'=>'comm_value','datatype'=>'float','prefix_text'=>'<b>Rp</b> '));
        #~ $this->properties['author'] = new Prop(array('label'=>lang('Author'),'colname'=>'author','updatable'=>False,'insertable'=>False));
        $this->properties['url'] = new Prop(array('label'=>lang('Original URL'),'colname'=>'url','hyperlink'=>TRUE, 'length'=>150));
        $this->properties['att_1'] = new Prop(array('label'=>lang('Attachment'),'colname'=>'att_1','inputtype'=>'file','inputtype2'=>'', 'enumerate'=>'upload_manager'));
        $this->properties['att_2'] = new Prop(array('label'=>lang('Attachment'),'colname'=>'att_2','inputtype'=>'file','inputtype2'=>'', 'enumerate'=>'upload_manager'));
        $this->properties['att_3'] = new Prop(array('label'=>lang('Attachment'),'colname'=>'att_3','inputtype'=>'file','inputtype2'=>'', 'enumerate'=>'upload_manager'));
        $this->properties['att_4'] = new Prop(array('label'=>lang('Attachment'),'colname'=>'att_4','inputtype'=>'file','inputtype2'=>'', 'enumerate'=>'upload_manager'));
        $this->properties['att_5'] = new Prop(array('label'=>lang('Attachment'),'colname'=>'att_5','inputtype'=>'file','inputtype2'=>'', 'enumerate'=>'upload_manager'));
        $this->properties['att_6'] = new Prop(array('label'=>lang('Attachment'),'colname'=>'att_6','inputtype'=>'file','inputtype2'=>'', 'enumerate'=>'upload_manager'));
        $this->properties['insert_date'] = new Prop(array('label'=>lang('Insert Date'),'colname'=>'insert_date','datatype'=>'datetime','updatable'=>False,'insertable'=>False));
        $this->properties['media_client'] = new Prop(array('label'=>lang('Client'),'colname'=>'media_client','inputtype'=>'combobox','enumerate'=>'media_client'));

        $this->unit = 'news';
        $this->enum_keyval = array('rowid','title');

        #~ $this->grid_command[] = array('attach','Attach to newsletter...');
        #~ $this->grid_command[] = array('','-----');
        $prog->must_authenticated = True;

    }

    function go() { // called inside main content
        if ($_REQUEST['custom_search']) {
            $this->show_search_result();
        }
        else
            $this->basic_handler();
        if ($this->action == 'browse') {
            echo '<h4>Admin Custom Search</h4>';
            $this->fe_search_form('admin');
        }
    }

    function prepare_insert($rowindex) {
        $this->ds->create_date[$rowindex] = date("Y-m-d H:i:s");
        return True;
    }

    function insert($rowindex) {
        $this->ds->insert_date[$rowindex] = 'Now()';
        $this->ds->author[$rowindex] = $_SESSION['login_user'];
        $um = instantiate_module('upload_manager');
        $this->ds->att_1[$rowindex] = $um->put_file('att_1', $rowindex, $this->module);
        $this->ds->att_2[$rowindex] = $um->put_file('att_2', $rowindex, $this->module);
        $this->ds->att_3[$rowindex] = $um->put_file('att_3', $rowindex, $this->module);
        $this->ds->att_4[$rowindex] = $um->put_file('att_4', $rowindex, $this->module);
        $this->ds->att_5[$rowindex] = $um->put_file('att_5', $rowindex, $this->module);
        $this->ds->att_6[$rowindex] = $um->put_file('att_6', $rowindex, $this->module);
        parent::insert($rowindex);
    }

    function update($rowindex) {  # updating
        $um = instantiate_module('upload_manager');
        if ($_REQUEST['delete_field']) {
            foreach ($_REQUEST['delete_field'] as $key=>$val) {
                if ($_REQUEST['delete_field'][$key][$rowindex] == '1') {
                    $um->del_file($this->ds->{$key}[$rowindex]);
                    $this->ds->{$key}[$rowindex] = '';
                }
            }
        }

        $rowid = $um->put_file('att_1', $rowindex, $this->module);
        if ($rowid != '') $this->ds->att_1[$rowindex] = $rowid;
        $rowid = $um->put_file('att_2', $rowindex, $this->module);
        if ($rowid != '') $this->ds->att_2[$rowindex] = $rowid;
        $rowid = $um->put_file('att_3', $rowindex, $this->module);
        if ($rowid != '') $this->ds->att_3[$rowindex] = $rowid;
        $rowid = $um->put_file('att_4', $rowindex, $this->module);
        if ($rowid != '') $this->ds->att_4[$rowindex] = $rowid;
        $rowid = $um->put_file('att_5', $rowindex, $this->module);
        if ($rowid != '') $this->ds->att_5[$rowindex] = $rowid;
        $rowid = $um->put_file('att_6', $rowindex, $this->module);
        if ($rowid != '') $this->ds->att_6[$rowindex] = $rowid;
        parent::update($rowindex);

    }

    function showgrid() {
        if ($this->action == 'browse') {

            echo '<table><tr><td>';

            # add browse by year/monthly
            $month_text = lang('month_array');
            echo '<form name="browseyearmonth" method=get action="'.$_SERVER['PHP_SELF'].'"> '.lang('Browse monthly').': ';
            $is_checked = $_REQUEST['viewmonthly']? 'checked': '';
            echo '<input type=checkbox name=viewmonthly value="1" '.$is_checked.'>';
            echo '<input type=hidden name=m value="'.$this->module.'">';
            echo '<select name=month>';
            for($id=1;$id<=12;$id++) {
                $ischecked = ($id == $_REQUEST['month'])? 'selected': '';
                echo "<option value='$id' $ischecked>{$month_text[$id]}</option>";
            }
            echo '</select>';
            echo '<input type=text name=year value="'.$_REQUEST['year'].'" size=5>';
            echo '<input type=submit value='.lang('Go').'>';
            echo '</form>';

            echo '</td><td>';

            echo '</td></tr></table>';

        }

        parent::showgrid();
    }

    function populate($rowid='',$merge=False) {  # override to provide custom where based on month/year param
        # note: populate is done before showgrid

        $year = $_REQUEST['year'] == ''? date('Y'):$_REQUEST['year'];
        $month = $_REQUEST['month'] == ''? date('n'):$_REQUEST['month'];
        if ($_REQUEST['viewmonthly']) {
            $this->db_where = "year(create_date)='{$year}' and month(create_date)='{$month}'";
            $this->browse_rows = 0; # disable paging
        }

        parent::populate($rowid,$merge);
    }

    function remove($rowindex) {
        # delete file first
        $um = instantiate_module('upload_manager');
        $um->del_file($this->ds->att_1[$rowindex]);
        $um->del_file($this->ds->att_2[$rowindex]);
        $um->del_file($this->ds->att_3[$rowindex]);
        $um->del_file($this->ds->att_4[$rowindex]);
        $um->del_file($this->ds->att_5[$rowindex]);
        $um->del_file($this->ds->att_6[$rowindex]);
        parent::remove($rowindex);
    }

    function check_del() {
        # for normal user, make sure they can only modify their own
        if ($_SESSION['login_level'] > 1) {
            foreach ($this->_rowid as $rowid) {
                if (!$this->get_row(array('rowid'=>$rowid,'author'=>$_SESSION['login_user'])))
                    echo '<p>Access denied. You are not the creator of this news.</p>';
                    return False;
            }
        }
        return True;
    }

    function prepare_update() {
        # for normal user, make sure they can only modify their own
        if ($_SESSION['login_level'] > 1) {
            foreach ($this->_rowid as $rowid) {
                if (!$this->get_row(array('rowid'=>$rowid,'author'=>$_SESSION['login_user']))) {
                    echo '<p>Access denied. You are not the creator of this news.</p>';
                    return False;
                }
            }
        }
        return True;
    }

    function fe_list($maxrow,$cat_id='*',$media_client='') {
        # called by frontend to show maxrow latest articles
        $this->browse_rows = $maxrow;
        $this->db_orderby = 'create_date desc';
        $this->db_where = '1=1';
        if ($cat_id != '*') {
            $this->db_where .= " and cat_id = '".myaddslashes($cat_id)."'";
        }
        if ($media_client != '')
            $this->db_where .= " and media_client = '".$media_client."'";
        $this->populate();
        if ($this->db_count == 0) {
            echo '<p>No article available</p>';
            return;
        }
        $cat = instantiate_module('news_cat');
        for ($i = 0; $i < $this->db_count; $i++) {
            $catdesc = $cat->enum_decode($this->ds->category[$i]);
            echo '<p><b><a href="news_view.php?'.merge_query(array('id'=>$this->ds->_rowid[$i], 'cat'=>$this->ds->category[$i])).'">'.$this->ds->title[$i].'</a></b>';
            echo '<small>('.$this->ds->author[$i].' - '.$this->ds->create_date[$i].' - '.$catdesc.')</small>';
            echo '<br>'.$this->ds->summary[$i].' <a href="news_view.php?'.merge_query(array('id'=>$this->ds->_rowid[$i], 'cat'=>$this->ds->category[$i])).'">see complete news</a></p>';
        }
    }


    function show_list_archieve($maxrow,$cat_id='*',$year='',$month='', $media_client='') {
        /* called by frontend to show maxrow latest articles index, categorized per monthly
        2004
          Januari
            - judul artikel blablabal
            - judul artikel 2
        */
        $year = ($year == '')? $_REQUEST['year']: $year;
        $month = ($month == '')? $_REQUEST['month']: $month;


        $this->browse_rows = $maxrow;
        $this->db_orderby = 'create_date asc';
        $this->db_where = '1=1';
        if ($cat_id != '*') {
            $this->db_where .= " and cat_id = '".myaddslashes($cat_id)."'";
        }
        if ($media_client != '')
            $this->db_where .= " and media_client = '".$media_client."'";
        $this->populate();
        if ($this->db_count == 0) {
            echo '<p>No news available</p>';
            return;
        }
        $cat = instantiate_module('news_cat');

        # iterate all articles to get distinct years-month in articles, order by year-month-asc
        $yearly = array();
        for ($i = 0; $i < $this->db_count; $i++) {
            list($_year,$_month,$_day ) = sscanf($this->ds->create_date[$i],"%d-%d-%d");
            $yearly[$_year][$_month][] = $i;
        }

        $month_text = lang('month_array');

        if ($year != '' and $month != '') {
            # show list of articles in this year/month
            echo '<ul>';
            foreach ($yearly[$year][$month] as $i) {
                $catdesc = $cat->enum_decode($this->ds->category[$i]);
                echo '<li>';
                echo '<a href="news_view.php?'.merge_query(array('id'=>$this->ds->_rowid[$i],'cat'=>$this->ds->category[$i])).'">'.$this->ds->title[$i].'</a> <small>('.$this->ds->author[$i].' - '.$this->ds->create_date[$i].' - '.$catdesc.'</small>';
                echo '</li>';
            }
            echo '</ul>';
        }
        else {
            foreach ($yearly as $year=>$monthly) {  # iterate years
                echo '<p>'.$year.'</p>';
                echo '<ul>';
                foreach ($monthly as $month=>$rowindices) { # iterate month
                    #~ echo '<h4>'.$month_text[$month].'</h4>';
                    echo '<li><a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array('year'=>$year,'month'=>$month)).'">'.$month_text[$month].'</a></li>';
                }
                echo '</ul>';

            }
        }
            #~ echo '<br>'.$this->ds->summary[$i].'</p>';
    }

    function fe_view($rowid, $media_client='') {
        /* called by frontend to show maxrow latest articles */
        echo $this->construct_view($rowid,$media_client);

        echo '<p align="right"><b><a href="news_send.php?id='.$rowid.'">Send to friend</a></b></p>';
    }

    function construct_view($rowid, $media_client='') {
        /* return given article's rowid visual */
        $buffer = '';
        $this->clear(); #should be emptied, if not, populate will append to datasource
        $this->populate($rowid);

        if ($media_client != '' and $this->db_count != 0 and $this->ds->media_client[0] != $media_client) {
            $buffer .= '<p>news not accessible</p>';
            return $buffer;
        }


        if ($this->db_count == 0) {
            $buffer .= '<p>news not available</p>';
            return $buffer;
        }

        $cat = instantiate_module('news_cat');
        $i = 0;

        $catdesc = $cat->enum_decode($this->ds->category[$i]);
        #~ $buffer .= '<small><b>('.$this->ds->create_date[$i] .')</b></small>';
        $buffer .= '<p class="title"><b>'.$this->ds->title[$i].'</b> <span class="datetime"><small><b>('.$this->ds->create_date[$i] .')</b></small></span></p>';
        $buffer .= '<p class="summary"><i>'.$this->ds->summary[$i].'</i></p>';
        $buffer .= '<p class="body">'.nl2br($this->ds->body[$i]).'</p>';
        if ($this->ds->url[$i])
            $buffer .= '<p class="bodytext">Original url: <a href="'.$this->ds->url[$i].'" target="_blank">'.$this->ds->url[$i].'</a></p>';

        $buffer .= '<p><b>Attachment:</b></p>';
        $um = instantiate_module('upload_manager');
        $atts = array();
        #~ print_r($this->ds->{'att_1'}[0]);
        for ($i = 1; $i<=6; $i++) {
            $att_rowid = $this->ds->{'att_'.$i}[0];
            #~ echo 'ROWID'.$att_rowid;
            if ($att_rowid == '') continue;
            $row = $um->get_row(array('rowid'=>$att_rowid));
            $atts[] = '<p class="body"><small><a href="get_news_att.php?id='.$rowid.'&attid='.$row['rowid'].'" target="_blank">'.$row['filename'].'</a> ('.sprintf('%0.2f',$row['size']/1024).' KB - '.$row['type'].')</small></p>';
        }
        $buffer .= join('<p>  ',$atts);
        return $buffer;
    }

    function get_news_att($rowid, $attid, $media_client='') {
        $buffer = '';
        $this->clear(); #should be emptied, if not, populate will append to datasource
        $this->populate($rowid);

        if ($media_client != '' and $this->db_count != 0 and $this->ds->media_client[0] != $media_client) {
            $buffer .= '<p>news not accessible</p>';
            return $buffer;
        }

        # makesure attid is in news' attachement
        $error = 1;
        for ($i = 1; $i<=6; $i++) {
            $att_rowid = $this->ds->{'att_'.$i}[0];
            if ($att_rowid == $_REQUEST['attid']) {
                $error = 0; break;
            }
        }
        $error and die('access deny');
        $um = instantiate_module('upload_manager');
        $um->download_file($_REQUEST['attid']);

    }

    function construct_list_email($rowid, $media_client='') {
        /* return given article's rowid visual */
        $buffer = '';
        $this->clear(); #should be emptied, if not, populate will append to datasource
        $this->populate($rowid);

        if ($this->db_count == 0) {
            $buffer .= '<p>article not available</p>';
            return $buffer;
        }
        $cat = instantiate_module('news_cat');
        for ($i=0;$i<$this->db_count;$i++) {
            #~ $buffer .= '<ul>';
            $catdesc = $cat->enum_decode($this->ds->category[$i]);
            #~ $buffer .= '<li>';
            $buffer .= '<p>';
            $buffer .= "<a href='".get_fullpath()."article.php?id={$this->ds->_rowid[$i]}'>{$this->ds->title[$i]}</a> <small>({$this->ds->author[$i]} - {$this->ds->create_date[$i]} - $catdesc)</small>";
            $buffer .= '<br><i>'.$this->ds->summary[$i].'</i>';
            $buffer .= '<p>'.nl2br($this->ds->body[$i]).'<small>('.$this->ds->author[$i].')</small></p>';
            #~ $buffer .= '</li>';
        }
        #~ $buffer .='</ul>';
        return $buffer;
    }

    function shownewrecord() {
        if ($this->allow_new) {
            echo '<form method=POST action="'.$_SERVER['PHP_SELF'].'">';
            echo '<input type=hidden name="m" value="'.$this->module.'">';
            echo '<input type=hidden name="act" value="new">';
            echo '<input type=hidden name="go" value="'.htmlentities($GLOBALS['full_self_url']).'">';         # url to go after successful submitation
            echo '<input type=hidden name="num_row" value="1"> <input type=submit value="'.lang('Add').' '.lang($this->unit).'"></p>';
            echo '</form>';
        }
    }

    function fe_stats_form() {
        /*
        new: get stats from currently logon client
        */
        echo '<table><tr><td width="265" valign=top bgcolor="#E4E4E4">';

        echo '<form method="POST">';
        echo '<p align="center">Media:<br>';
        $media = instantiate_module('news_media');
        $this->show_combo('media', $media->enum_list(), 5, 1);

        echo '<p align="center">Kategori:<br>';
        $cat = instantiate_module('news_cat_enum2');
        $this->show_combo('news_cat_enum2', $cat->enum_list(), 5, 1);

        echo '<p align="center">Tone:<br>';
        $tone = instantiate_module('news_tone');
        $this->show_combo('tone', $tone->enum_list(), 5, 1);

        echo '<p align="right">';
        echo '<input type=checkbox name="use_date" value="1" '.($_REQUEST['use_date']=='1'?'checked':'').'>';
        echo 'Batasi tanggal:<br>';
        $month_text = lang('month_array');
        unset($month_text[0]);
        if ($_REQUEST['use_date'] != 1) {
            $today = getdate();
            $_REQUEST['r1_day'] = $today['mday'];
            $_REQUEST['r1_month'] = $today['mon'];
            $_REQUEST['r1_year'] = $today['year'];
            $_REQUEST['r2_day'] = $today['mday'];
            $_REQUEST['r2_month'] = $today['mon'];
            $_REQUEST['r2_year'] = $today['year'];
        }
        $day_range = array();
        $year_range = array();
        for ($i = 1; $i <= 31; $i++) $day_range[$i] = $i;
        for ($i = 2005; $i <= 2010; $i++) $year_range[$i] = $i;
        echo 'Dari:';
        $this->show_combo('r1_day', $day_range, 1, 0);
        $this->show_combo('r1_month', $month_text, 1, 0);
        $this->show_combo('r1_year', $year_range, 1, 0);
        echo '<br>';
        echo 'Sampai:';
        $this->show_combo('r2_day', $day_range, 1, 0);
        $this->show_combo('r2_month', $month_text, 1, 0);
        $this->show_combo('r2_year', $year_range, 1, 0);

        echo '<p align="center">X axis is<br>';
        $this->show_combo('x_axis', array('media_id'=>'Media', 'cat_id'=>'Category', 'tone_id'=>'Tone', 'date(create_date)'=>'Date'), 1);

        echo '<p align="center"><input type=submit></form>';

        echo '</td><td valign=top>';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            #~ print_r($_REQUEST);
            //show statistic charts
            #~ echo '<hr>';
            echo '<h3 align="center">Statistik Jumlah Berita</h3>';
            $wheres = array();
            $wheres[] = "media_client='{$_SESSION['mmclient_login_user']}'";
            $st_1 = array('media'=>'media_id', 'news_cat_enum2'=>'cat_id', 'tone'=>'tone_id');
            foreach ($st_1 as $key=>$val) {
                if ($_REQUEST[$key] and !in_array('*',$_REQUEST[$key])) {
                    $tmp_arr = array();
                    foreach ($_REQUEST[$key] as $l)
                        $tmp_arr[] = '\''.myaddslashes($l).'\'';
                    $wheres[] = $val.' in ('.join(',', $tmp_arr).')';
                }
            }
            if ($_REQUEST['use_date'] == '1') {
                $r1 = myaddslashes($_REQUEST['r1_year'].'-'.$_REQUEST['r1_month'].'-'.$_REQUEST['r1_day']);
                $r2 = myaddslashes($_REQUEST['r2_year'].'-'.$_REQUEST['r2_month'].'-'.$_REQUEST['r2_day']);
                $wheres[] = "create_date >='{$r1}' and create_date <='{$r2} 23:59:59.999'";
            }

            $x_axis = $_REQUEST['x_axis'];
            $where_str = join($wheres,' and ');
            if ($where_str == '') $where_str = '1=1';
            $sql = 'select '.$x_axis.', count(*) from '.$this->db_table.' where '.$where_str.' group by '.$x_axis;
            #~ echo $sql;
            $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
            if (!mysql_num_rows($res)) {
                echo '<p align="center" ><b>No result</b></p>';
            }
            else {
                $x_label = array();
                $y_val = array();
                $rows = array();
                while ($row = mysql_fetch_row($res)) {
                    $x_label[] = $row[0];
                    $y_val[] = $row[1];
                    $rows[] = $row;
                }
                $url = 'stats_graph1.php?x_label='.urlencode(serialize($x_label)).'&y_val='.urlencode(serialize($y_val));
                echo '<p align="center"><img src="'.$url.'" border=0>';

                # table
                echo '<table align="center" border=1><tr><th>'.$x_axis.'</th><th>Jumlah Berita</th></tr>';
                foreach ($rows as $row) {
                    echo '<tr><td>'.$row[0].'</td><td>'.$row[1].'</td></tr>';
                }
                echo '</table>';
            }
        }
        echo '</td></tr></table>';
    }

    function fe_search_form($modifier='') {

        if ($modifier == 'admin') {
            echo '<form method="POST">';
            echo '<input type=hidden name=m value="'.$this->module.'">';
            echo '<input type=hidden name="custom_search" value="1">';
        }
        else
            echo '<form method="POST" action="news_search.php">';
        $cat = instantiate_module('news_cat');
        $media = instantiate_module('news_media');
        $month_text = lang('month_array');
        unset($month_text[0]);
        if ($_REQUEST['use_date'] != 1) {
            $today = getdate();
            $_REQUEST['r1_day'] = $today['mday'];
            $_REQUEST['r1_month'] = $today['mon'];
            $_REQUEST['r1_year'] = $today['year'];
            $_REQUEST['r2_day'] = $today['mday'];
            $_REQUEST['r2_month'] = $today['mon'];
            $_REQUEST['r2_year'] = $today['year'];
        }
        $day_range = array();
        $year_range = array();
        for ($i = 1; $i <= 31; $i++) $day_range[$i] = $i;
        for ($i = 2005; $i <= 2010; $i++) $year_range[$i] = $i;

        echo '<p align="right" style="margin-left: 10; margin-right: 10"><font face="Arial">'."\n";
        echo '<input type="text" size="21" style="float: right" name="keyword" value="'.$_REQUEST['keyword'].'">';
        echo '</font><br><br><font size="2" face="Arial">Category:</font><font face="Arial">';
        $this->show_combo('cat',$cat->enum_list(),1,1);
        echo '<br><br>';
        echo '</font><font size="2" face="Arial">Media:</font><font face="Arial">';
        $this->show_combo('media', $media->enum_list(), 1, 1);
        echo '<br><br>';
        echo '<input type=checkbox name="use_date" value="1" '.($_REQUEST['use_date']=='1'?'checked':'').'>';
        echo '</font><font size="2" face="Arial">Batasi tanggal<br>Dari : </font><font face="Arial">';
        $this->show_combo('r1_day', $day_range, 1, 0);
        $this->show_combo('r1_month', $month_text, 1, 0);
        $this->show_combo('r1_year', $year_range, 1, 0);
        echo '</font><font size="2" face="Arial"><br>Sampai :</font><font face="Arial">';

        $this->show_combo('r2_day', $day_range, 1, 0);
        $this->show_combo('r2_month', $month_text, 1, 0);
        $this->show_combo('r2_year', $year_range, 1, 0);

        echo '<br><br><input type="submit" value="Submit" name="B1"></font><br></form>';

    }

    function show_search_result($media_client='') {
        $kw = $_REQUEST['keyword'];

        $wheres = array();
        $wheres[] = "(title like '%$kw%' or summary like '%$kw%' or body like '%$kw%')";
        if ($_REQUEST['media'] != '' and $_REQUEST['media'] != '*')
            $wheres[] = "media_id='{$_REQUEST['media']}'";

        if ($_REQUEST['cat'] != '' and $_REQUEST['cat'] != '*')
            $wheres[] = "cat_id='{$_REQUEST['cat']}'";
        if ($_REQUEST['use_date'] == '1') {
            $r1 = myaddslashes($_REQUEST['r1_year'].'-'.$_REQUEST['r1_month'].'-'.$_REQUEST['r1_day']);
            $r2 = myaddslashes($_REQUEST['r2_year'].'-'.$_REQUEST['r2_month'].'-'.$_REQUEST['r2_day']);
            $wheres[] = "create_date >='{$r1}' and create_date <='{$r2} 23:59:59.999'";
        }
        if ($media_client != '')
            $wheres[] = "media_client='$media_client'";
        $where_str = join(' and ', $wheres);
        $sql = 'select * from '.$this->db_table." where ".$where_str;
        #~ echo $sql;

        $res = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error()); #do to database
        if (!mysql_num_rows($res)) {
            echo '<p align="center" ><b>No result returned from search</b></p>';
        }
        else {
            $rows = array();
            $cat = instantiate_module('news_cat');
            $media = instantiate_module('news_media');
            $i = 1;
            while ($row = mysql_fetch_array($res)) {
                #~ $rows[] = $row;
                $cat_desc = $cat->enum_decode($row['cat_id']);
                $media_desc = $media->enum_decode($row['media_id']);
                if ($media_client=='')  # admin!
                    $url = 'index.php?'.merge_query(array('m'=>$this->module, 'act'=>'edit', 'rowid[]'=>$row['rowid'], 'go'=>$GLOBALS['full_self_url']));
                else
                    $url = 'news_view.php?'.merge_query(array('id'=>$row['rowid'], 'cat'=>$row['category']));
                echo '<p><a href="'.$url.'">'.$row['title'].'</a>';
                echo '<br>Category: '.$cat_desc.' - Media: '.$media_desc;
                echo '<br>'.substr($row['summary'], 0, 50).'...';
                $i++;
            }
        }
    }

    function news_send_email ($rowid, $emails, $media_client='') {
        $body = $this->construct_list_email($rowid,$media_client);

        # send it
        include_once(APP_INCLUDE_ROOT.'/htmlMimeMail/htmlMimeMail.php');

        # construct email
        $mail = new htmlMimeMail();
        $mail->setCrlf("\n");   # Some MTA confuse \r\n and translate it to \n\n, causing double lines. This will makes them happy:
        $mail->setHTML($body);

        # get news attachment (if any)
        $buffer = '';
        $this->clear(); #should be emptied, if not, populate will append to datasource
        $this->populate($rowid);
        $um = instantiate_module('upload_manager');
        for ($i = 1; $i<=6; $i++) {
            $att_rowid = $this->ds->{'att_'.$i}[0];
            if ($att_rowid == '') continue;
            $row = $um->get_row(array('rowid'=>$att_rowid));
            $mail->addAttachment(join('',file($row['path'])), $row['filename'], $row['type']);
        }


        $mail->setFrom($GLOBALS['mail_from']);
        $mail->setSubject('Your friend send this news to you');
        $mail->setHeader('X-Mailer', (($GLOBALS['mail_xmailer'] == '')? 'supermailer/dsw/2005': $GLOBALS['mail_xmailer']));
        $mail->setHeader('Reply-to', $GLOBALS['mail_replyto']);
        $mail->setReturnPath($GLOBALS['mail_from']);
        #~ $mail->setCc($mail_bcc);
        #$mail->setCc('Carbon Copy <cc@example.com>');
        $mail_method = 'single';
        if ($mail_method == 'bcc') {
            $mail_bcc = join(',',$emails);
            $mail->setBcc($mail_bcc);
            $result = $mail->send(array($GLOBALS['mail_to']), 'mail');

        }
        elseif ($mail_method == 'single') {
            foreach ($emails as $email) {
                $result = $mail->send(array($email), 'mail');
            }
        }
        else {
            die('Unsupported emailing method:'.$this->ds->mail_method[0]);
        }
        return True; # post must return to avoid potential html printing from non-post codes

    }

}

?>