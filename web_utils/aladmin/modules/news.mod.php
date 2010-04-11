<?
/* admin frontpage
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
        $this->properties['summary'] = new Prop(array('label'=>lang('Summary'),'colname'=>'summary', 'datatype'=>'text','required'=>True,'inputtype'=>'textarea','rows'=>5,'browse_maxchar'=>30));
        $this->properties['body'] = new Prop(array('label'=>lang('Body'),'colname'=>'body','datatype'=>'text','inputtype'=>'textarea','inputtype2'=>'htmlarea','rows'=>10,'browse_maxchar'=>30));
        $this->properties['category'] = new Prop(array('label'=>lang('Category'),'colname'=>'cat_id','inputtype'=>'combobox','enumerate'=>'news_cat'));
        $this->properties['author'] = new Prop(array('label'=>lang('Author'),'colname'=>'author','updatable'=>False,'insertable'=>False));
        $this->properties['create_date'] = new Prop(array('label'=>lang('Creation Date'),'colname'=>'create_date','datatype'=>'datetime','updatable'=>False,'insertable'=>False));

        $this->unit = 'news';
        $this->enum_keyval = array('rowid','title');

        #~ $this->grid_command[] = array('attach','Attach to newsletter...');
        #~ $this->grid_command[] = array('','-----');
        $prog->must_authenticated = True;

    }

    function go() { // called inside main content
        $this->basic_handler();
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
        $cat = instantiate_module('news_cat');
        for ($i = 0; $i < $this->db_count; $i++) {
            $catdesc = $cat->enum_decode($this->ds->category[$i]);
            echo '<p><b><a href="news_view.php?'.merge_query(array('id'=>$this->ds->_rowid[$i], 'cat'=>$this->ds->category[$i])).'">'.$this->ds->title[$i].'</a></b>';
            echo '<small>('.$this->ds->author[$i].' - '.$this->ds->create_date[$i].' - '.$catdesc.')</small>';
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

    function fe_view($rowid) {
        /* called by frontend to show maxrow latest articles */
        echo $this->construct_view($rowid);
    }

    function construct_view($rowid) {
        /* return given article's rowid visual */
        $buffer = '';
        $this->populate($rowid);

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

}

?>