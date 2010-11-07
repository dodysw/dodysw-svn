<?
/* admin frontpage
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class article extends TableManager {
    var $db_table, $properties;
    function article() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = 'Artikel';
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'article_tab';
        $this->properties['title'] = new Prop(array('label'=>'Title','colname'=>'title','required'=>True));
        $this->properties['summary'] = new Prop(array('label'=>'Summary','colname'=>'summary', 'datatype'=>'text','required'=>True,'inputtype'=>'textarea','rows'=>5,'browse_maxchar'=>30));
        $this->properties['body'] = new Prop(array('label'=>'Body','colname'=>'body','datatype'=>'text','inputtype'=>'textarea','rows'=>10,'browse_maxchar'=>30));
        $this->properties['category'] = new Prop(array('label'=>'Category','colname'=>'cat_id','inputtype'=>'combobox','enumerate'=>'category'));
        $this->properties['author'] = new Prop(array('label'=>'Author','colname'=>'author','updatable'=>False,'insertable'=>False));
        $this->properties['create_date'] = new Prop(array('label'=>'Creation Date','colname'=>'create_date','datatype'=>'datetime','updatable'=>False,'insertable'=>False));

        $this->enum_keyval = array('rowid','title');

        $this->grid_command[] = array('attach','Attach to newsletter...');
        $this->grid_command[] = array('','-----');


        $prog->must_authenticated = True;

        #~ if ($_SESSION['login_level'] > 1)
            #~ $this->db_where="author='{$_SESSION['login_user']}'";

    }

    function go() { // called inside main content
        #~ echo "<h3>Article</h3>";
        $this->basic_handler();
    }

    function act_attach ($post) {
        $this->properties['newsletter_id'] = new Prop(array('label'=>'Newsletter','colname'=>'newsletter_id','required'=>True,'inputtype'=>'combobox','enumerate'=>'newsletter'));
        $this->import2ds(); # properties is modified, re-import to datasource

        if ($post) {

            if (!$this->_save) return;

            if (!$this->validate_rows()) {
                return False;
            }

            # start insertion
            foreach ($this->_rowid as $rowid) {
                $sql = "insert into {$GLOBALS['dbpre']}newsletter_article_tab (newsletter_id,article_id) values ('{$this->ds->newsletter_id[0]}','$rowid')";
                $res = mysql_query($sql) or die(mysql_error()); #do to database
            }
            return;
        }

        if (!$this->showerror() and $this->_save) {   # this is a successful posted result
            echo '<p> article(s) has been attached</p>';
            echo '<p><b><a href="'.$this->_go.'">Continue</a></b></p>';
            return;
        }

        echo '<p>Attach selected article(s) to this newsletter:</p>';
        echo '<form method=post action="'.$_SERVER['PHP_SELF'].'">';
        echo '<input type=hidden name=m value="'.$this->module.'">';   # this module
        echo '<input type=hidden name=act value="'.$this->action.'">';   # contains the action (edit/new)
        echo '<input type=hidden name=save value="1">';         # marker to indicate form submitation
        echo '<input type=hidden name=go value="'.htmlentities($this->_go).'">';         # url to go after successful submitation
        #~ debug($this);
        foreach ($this->_rowid as $rowid)
            echo '<input type=hidden name="rowid[]" value="'.htmlentities($rowid).'">';         # url to go after successful submitation
        echo '<p>';
        $this->input_widget("field[newsletter_id][$i]", $this->ds->newsletter_id[$i], 'newsletter_id');
        echo '<p><input type=submit value=" OK "> | ';
        echo '<b><a href="" onclick="window.history.back();return false;">Cancel</a></b></p>';
        echo '</form>';

    }

    function insert($rowindex) {
        $this->ds->create_date[$rowindex] = 'Now()';
        $this->ds->author[$rowindex] = $_SESSION['login_user'];
        parent::insert($rowindex);
    }

    function showgrid() {
        if ($this->action == 'browse') {

            echo '<table><tr><td>';

            # add browse by year/monthly
            $month_text = array('','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','Nopember','Desember');
            echo '<form name="browseyearmonth" method=get action="'.$_SERVER['PHP_SELF'].'"> Browse monthly: ';
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
            echo '<input type=submit value=Go>';
            echo '</form>';

            echo '</td><td>';

            echo '</td></tr></table>';

        }

        parent::showgrid();
    }

    function populate($rowid='',$merge=False) {  # override to provide custom where based on month/year param
        # note: populate is done before showgrid

        $_REQUEST['year'] = $_REQUEST['year'] == ''? date('Y'):$_REQUEST['year'];
        $_REQUEST['month'] = $_REQUEST['month'] == ''? date('n'):$_REQUEST['month'];
        if ($_REQUEST['viewmonthly']) {
            $this->db_where = "year(create_date)='{$_REQUEST['year']}' and month(create_date)='{$_REQUEST['month']}'";
            $this->browse_rows = 0; # disable paging
        }

        parent::populate($rowid,$merge);
    }

    function check_del() {
        # for normal user, make sure they can only modify their own
        if ($_SESSION['login_level'] > 1) {
            foreach ($this->_rowid as $rowid) {
                if (!$this->get_row(array('rowid'=>$rowid,'author'=>$_SESSION['login_user'])))
                    echo '<p>You may not modify this row</p>';
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
                    echo '<p>You may not modify this row</p>';
                    return False;
                }
            }
        }
        return True;
    }

    function fe_list($maxrow,$cat_id='*') {
        # called by frontend to show maxrow latest articles
        $this->browse_rows = $maxrow;
        $this->db_orderby = 'create_date desc';
        if ($cat_id != '*') {
            $this->db_where = "cat_id = '".myaddslashes($cat_id)."'";
        }
        $this->populate();
        if ($this->db_count == 0) {
            echo '<p>No article available</p>';
            return;
        }
        include_once('category.inc.php');
        $cat = new category();
        for ($i = 0; $i < $this->db_count; $i++) {
            $catdesc = $cat->enum_decode($this->ds->category[$i]);
            echo "<p><b><a href='article.php?id={$this->ds->_rowid[$i]}'>{$this->ds->title[$i]}</a></b> <small>({$this->ds->author[$i]} - {$this->ds->create_date[$i]} - $catdesc)</small> ";
            echo '<br>'.$this->ds->summary[$i].'</p>';
        }
    }


    function show_list_archieve($maxrow,$cat_id='*',$year='',$month='') {
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
        if ($cat_id != '*') {
            $this->db_where = "cat_id = '".myaddslashes($cat_id)."'";
        }
        $this->populate();
        if ($this->db_count == 0) {
            echo '<p>No article available</p>';
            return;
        }
        include_once('category.inc.php');
        $cat = new category();

        # iterate all articles to get distinct years-month in articles, order by year-month-asc
        $yearly = array();
        for ($i = 0; $i < $this->db_count; $i++) {
            list($_year,$_month,$_day ) = sscanf($this->ds->create_date[$i],"%d-%d-%d");
            #~ echo '<br>'.$this->ds->create_date[$i];
            #~ echo "$day, $month, $year";
            $yearly[$_year][$_month][] = $i;
        }

        $month_text = array('','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','Nopember','Desember');

        if ($year != '' and $month != '') {
            # show list of articles in this year/month
            echo '<ul>';
            foreach ($yearly[$year][$month] as $i) {
                $catdesc = $cat->enum_decode($this->ds->category[$i]);
                echo '<li>';
                echo "<a href='article.php?id={$this->ds->_rowid[$i]}'>{$this->ds->title[$i]}</a> <small>({$this->ds->author[$i]} - {$this->ds->create_date[$i]} - $catdesc)</small>";
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
                    echo "<li><a href='{$_SERVER['PHP_SELF']}?year=$year&month=$month'>{$month_text[$month]}</a></li>";
                }

            }
        }
            #~ echo '<br>'.$this->ds->summary[$i].'</p>';
    }

    function fe_view($rowid) {
        /* called by frontend to show maxrow latest articles */
        echo $this->construct_view($rowid);
    }

    function construct_view($rowid) {
        /* return given article's rowid visual */
        $buffer = '';
        $this->populate($rowid);

        if ($this->db_count == 0) {
            $buffer .= '<p>article not available</p>';
            return $buffer;
        }
        include_once('category.inc.php');
        $cat = new category();
        $i = 0;

        $catdesc = $cat->enum_decode($this->ds->category[$i]);

        $buffer .= "<p><b>{$this->ds->title[$i]}</b> <small>({$this->ds->author[$i]} - {$this->ds->create_date[$i]} - $catdesc)</small> ";
        $buffer .= '<p><i>'.$this->ds->summary[$i].'</i></p>';
        $buffer .= '<p>'.$this->ds->body[$i].'</p>';
        return $buffer;
    }

    function construct_list_email($rowid) {
        /* return given article's rowid visual */
        $buffer = '';
        # ugh, ugly datasource clearing..w
        $this->ds = new DataSource;    #should be emptied, if not, populate will append to datasource
        $this->db_count = 0;
        $this->populate($rowid);
        #~ debug();

        if ($this->db_count == 0) {
            $buffer .= '<p>article not available</p>';
            return $buffer;
        }
        include_once('category.inc.php');
        $cat = new category();
        for ($i=0;$i<$this->db_count;$i++) {
            #~ $buffer .= '<ul>';
            $catdesc = $cat->enum_decode($this->ds->category[$i]);
            #~ $buffer .= '<li>';
            $buffer .= '<p>';
            $buffer .= "<a href='".get_fullpath()."article.php?id={$this->ds->_rowid[$i]}'>{$this->ds->title[$i]}</a> <small>({$this->ds->author[$i]} - {$this->ds->create_date[$i]} - $catdesc)</small>";
            $buffer .= '<br><i>'.$this->ds->summary[$i].'</i>';
            #~ $buffer .= '</li>';
        }
        #~ $buffer .='</ul>';
        return $buffer;
    }

}

?>