<?
/* main news
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class product extends TableManager {
    var $db_table, $properties;
    function product() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('Product');
        $html_title = $this->title;

        #~ $this->db_table = $GLOBALS['dbpre'].'news_tab';
        $this->properties['product_code'] = new Prop(array('colname'=>'product_code','required'=>True, 'inputtype'=>'text', 'length'=>15, 'is_key'=>True));
        $this->properties['product_name'] = new Prop(array('colname'=>'product_name','required'=>True,'length'=>100));
        $this->properties['product_category'] = new Prop(array('colname'=>'product_category_id','cdatatype'=>'fkey','enumerate'=>'product_category'));
        $this->properties['manufacturer'] = new Prop(array('colname'=>'manufacturer_id','cdatatype'=>'fkey','enumerate'=>'product_manufacturer'));
        $this->properties['product_type'] = new Prop(array('colname'=>'type','inputtype'=>'text', 'length'=>8));
        $this->properties['price'] = new Prop(array('colname'=>'price','cdatatype'=>'money'));
        $this->properties['priviledge_price'] = new Prop(array('colname'=>'priviledge_price','cdatatype'=>'money'));
        $this->properties['thumb_img'] = new Prop(array('cdatatype'=>'image', 'colname'=>'thumb_img'));
        $this->properties['lg_img'] = new Prop(array('label'=>lang('Large Image'), 'cdatatype'=>'image', 'colname'=>'lg_img'));
        $this->properties['notes'] = new Prop(array('colname'=>'notes','length'=>1000, 'inputtype'=>'textarea'  ));
        $this->properties['show_on_frontpage'] = new Prop(array('colname'=>'show_on_frontpage','cdatatype'=>'bool'));
        $this->properties['parent_id'] = new Prop(array('colname'=>'parent_id','cdatatype'=>'fkey','enumerate'=>'product','insertable'=>False, 'updatable'=>False, 'hidden'=>True));

        $this->properties['creation_date_time'] = new Prop(array('cdatatype'=>'creation_date_time'));
        $this->properties['created_by'] = new Prop(array('cdatatype'=>'created_by'));
        $this->properties['last_update_date_time'] = new Prop(array('cdatatype'=>'last_update_date_time'));
        $this->properties['last_updated_by'] = new Prop(array('cdatatype'=>'last_updated_by'));
        $this->properties['last_updating_process'] = new Prop(array('cdatatype'=>'last_updating_process'));

        $this->unit = 'product';
        $this->enum_keyval = array('rowid','product_code,product_name');
        $this->childds[] = 'sub_product';

        $this->browse_rows = 40;
    }

    function go() { // called inside main content
        $this->basic_handler();
    }

    function fe_list($fil=0,$frontpage_only=1,$cat_id=0,$manuf_id=0) {
        # called by frontend to show maxrow latest articles
        # @_REQUEST['pg'] == current page
        # global @row_per_page == number of row per page
        global $row_per_page;

        //start configure these
        $page_var_name = 'pg1'; # needed since if depending on _REQUEST['row'] also modifies ALL query using ->populate()
        //end configure

        if ($_REQUEST[$page_var_name]) $this->_rowstart = $_REQUEST[$page_var_name];

        $this->browse_rows = $maxrow;
        if ($row_per_page) $this->browse_rows = $row_per_page;  #override max row if paging is enabled

        $this->db_orderby = 'last_update_date_time desc';
        $this->db_where = '1=1';
        if ($frontpage_only)
            $this->db_where .= ' and show_on_frontpage=1';
        if ($cat_id != '*') {
            $this->db_where .= " and product_category_id = '".myaddslashes($cat_id)."'";
        }
        if ($manuf_id != '')
            $this->db_where .= " and manufacturer_id = '".myaddslashes($manuf_id)."'";
        $this->clear();
        $this->populate();
        if ($this->db_count == 0) {
            echo '<p>No product available</p>';
            return;
        }

        $col_num = 3;
        #~ echo '<table border=1 cellspacing=0 cellpadding=0>';
        echo '<table>';
        for ($i = 0; $i < $this->db_count; $i++) {
            # yg tampil hanya Manufacturer logo, Product Name, Thumbnail image, Price dan Priviledge Price (kalau ada).

            if (($i+1) % $col_num == 1) {   # start left
                echo '<tr><td valign="top">';
            }
            elseif (($i+1) % $col_num == 0) {  # right part
                echo '<td valign="top">';
            }
            else {  # middle part
                echo '<td valign="top">';
            }

            $this->show_item_nice($this->ds->get_row($i));


            if (($i+1) % $col_num == 1) {   # start left
                echo '</td>';
            }
            elseif (($i+1) % $col_num == 0) {  # right part
                echo '</td></tr>';
            }
            else {  # middle part
                echo '</td>';
            }

        }
        echo '</table>';

        # show paging
        if ($row_per_page and $this->db_count) {
            $max_rownum = $this->max_rownum();
            if ($max_rownum > $this->db_count) {    # split into pages
                echo lang('Pages').': ';
                $pages = array();
                for ($rowidx = 0, $pg = 1; $rowidx < $max_rownum; $rowidx += $this->browse_rows, $pg += 1) {
                    if ($this->_rowstart == $rowidx)
                        $pages[] = "<b>$pg</b>";
                    else {
                        $pages[] = '<a href="'.$_SERVER['PHP_SELF'].'?'.merge_query(array($page_var_name=>$rowidx)).'">'.$pg.'</a>';
                    }
                }
                echo join(' | ',$pages);
            }
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

        echo '<p><b><a href="news_send.php?id='.$rowid.'">Send to friend</a></b></p>';
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
        $buffer .= '<small><b>('.$this->ds->create_date[$i] .')</b></small>';
        $buffer .= '<h2><b>'.$this->ds->title[$i].'</b><br></h2>';
        $buffer .= '<p><i>'.$this->ds->summary[$i].'</i></p>';
        $buffer .= '<p>'.nl2br($this->ds->body[$i]).'<small>('.$this->ds->author[$i].')</small></p>';
        if ($this->ds->url[$i])
            $buffer .= '<p><small>Original url: <a href="'.$this->ds->url[$i].'">'.$this->ds->url[$i].'</a></small></p>';
        $um = instantiate_module('upload_manager');
        $atts = array();
        #~ print_r($this->ds->{'att_1'}[0]);
        for ($i = 1; $i<=6; $i++) {
            $att_rowid = $this->ds->{'att_'.$i}[0];
            if ($att_rowid == '') continue;
            $row = $um->get_row(array('rowid'=>$att_rowid));
            $atts[] = '<a href="get_news_att.php?id='.$rowid.'&attid='.$row['rowid'].'">'.$row['filename'].'</a> ('.sprintf('%0.2f',$row['size']/1024).' KB - '.$row['type'].')';
        }
        $buffer .= join('<br>',$atts);
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

    function fe_search_form($modifier='') {
        if ($modifier == 'admin') {
            echo '<form method="POST">';
            echo '<input type=hidden name=m value="'.$this->module.'">';
            echo '<input type=hidden name="custom_search" value="1">';
        }
        else
            echo '<form method="POST" action="product_search.php">';
        echo '<input type="text" name="keyword" value="'.$_REQUEST['keyword'].'">';
        echo '<input type=submit value="Go"></form>';
    }

    function show_search_result() {
        $kw = $_REQUEST['keyword'];
        # get a list of column name which datatype is varchar, then decorate with proper db condition
        $searchable_fields = array();
        foreach ($this->properties as $colval=>$col)
            if ($col->datatype == 'varchar' and $col->queryable)
                $searchable_fields[] = '`'.$col->colname."` like '%$kw%'";

        $wheres = array();
        $wheres[] = '('.implode(' or ', $searchable_fields).')';
        $this->db_where = join(' and ', $wheres);
        $this->clear();
        $this->populate();
        if (!$this->db_count) {
            echo '<p align="center" ><b>No result returned from search</b></p>';
            return;
        }
        for ($i=0; $i < $this->db_count; $i++)
            $this->show_item_nice($this->ds->get_row($i));
    }

    function show_item_nice($row) {
        $manuf = instantiate_module('product_manufacturer');
        $rowmanuf = $manuf->get_row(array('rowid'=>$row['manufacturer']));
        echo '<table border="0"><tr><td align="center" valign="top">';
        if ($rowmanuf['logo'])
            echo '<img src="getfile.php?id='.$rowmanuf['logo'].'&secure='.secure_hash($rowmanuf['logo']).'"><br>';

        if ($row['thumb_img'])
            echo '<img src="getfile.php?id='.$row['thumb_img'].'&secure='.secure_hash($row['thumb_img']).'">';

        #~ echo '</td><td valign="top">';
        echo '</td></tr>';

        echo '<tr><td valign="top" align="center">';

        echo '<b><font face="Verdana" size="1"><a href="product_view.php?'.merge_query(array('id'=>$row['_rowid'])).'">'.$row['product_name'].'</a></font></b>';
        echo '<br><font face="Verdana" size="1">Price: Rp '.number_format($row['price']).'</font>';
        if ($row['priviledge_price'] != '')
            echo '<br><font face="Verdana" size="1">Priviledge Price: Rp '.number_format($row['priviledge_price']).'</font>';
        #~ echo '<br><b><a href="cart.php?id='.$row['_rowid'].'&act=add">Add to cart</a></b>';
        echo '<form method="POST" action="cart.php">';
        echo '<input type="hidden" name="id" value="'.$row['_rowid'].'">';
        echo '<input type="hidden" name="act" value="add">';
        echo '<input type="submit" name="addtocart" value="Buy">';
        echo '</form>';
        #~ echo '<br><b><a href="cart.php?id='.$row['_rowid'].'&act=add">Add to cart</a></b>';

        #~ echo '</td><td>';
        #~ if ($rowmanuf['logo'])
            #~ echo '<img src="getfile.php?id='.$rowmanuf['logo'].'&secure='.secure_hash($rowmanuf['logo']).'">';
        echo '</td></tr></table>';
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