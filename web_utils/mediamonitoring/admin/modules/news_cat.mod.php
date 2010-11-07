<?
/* news category, support subcategory
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');

class news_cat extends TableManager {
    var $db_table, $properties;
    function news_cat() {
        parent::TableManager(); # must call base class

        global $html_title;
        $this->title = lang('News category');
        $html_title = $this->title;

        $this->db_table = $GLOBALS['dbpre'].'news_cat_tab';
        $this->properties['category'] = new Prop(array('label'=>lang('Category'),'colname'=>'name','required'=>True,'is_key'=>True, 'length'=>100));
        $this->properties['description'] = new Prop(array('label'=>lang('Description'),'colname'=>'description', 'required'=>True));
        $this->properties['parent_category'] = new Prop(array('label'=>lang('Parent Category'),'colname'=>'parent_category','enumerate'=>'news_cat','inputtype'=>'combobox','notes'=>'Empty to not have parent'));
        $this->enum_keyval = array('name','description');

        $this->unit = 'category';

    }

    function go() { # called inside main content
        $this->basic_handler();
    }

    //override to only display level-1 parent (subcat only support 2 level)
    function enum_list() {  # return list of id/desc
        /* by default, if available, expects enum_keyval to be filled with array('field1','field2') */
        if ($this->enum_keyval) {
            $enumlist = array();
            foreach ($this->get_rows(array('parent_category'=>''), $this->enum_keyval[0].',description','row') as $row) {
                $enumlist[$row[0]] = $row[1];
            }
            return $enumlist;
        }
    }

    //override to display cat and subcat also. see news module
    function enum_list2() {  # return list of id/desc
        /* by default, if available, expects enum_keyval to be filled with array('field1','field2') */
        if ($this->enum_keyval) {
            $enumlist = array();
            foreach ($this->get_rows('', $this->enum_keyval[0].',description,parent_category','row') as $row) {
                if ($row[2] == '') $prefix = '';
                else {
                    $rowdesc = $this->get_row(array('name'=>$row[2]),'description','row');
                    $prefix = $rowdesc[0].'/';
                }
                $enumlist[$row[0]] = $prefix.$row[1];
            }
            return $enumlist;
        }
    }

    function show_combo($varname, $keyvalarr, $row=1, $with_all=0) {    #override
        if ($row > 1)
            echo '<select name="'.$varname.'[]" row="'.$row.'" multiple>';
        else
            echo '<select name="'.$varname.'" name="D1">';
        if ($with_all)
            echo '<option value="*">all</option>';

        foreach ($keyvalarr as $key=>$val) {
            if ($row > 1)
                $selected = (in_array($key,$_REQUEST[$varname]))? 'selected': '';
            else
                $selected = ($_REQUEST[$varname] == $key)? 'selected': '';
            echo '<option value="'.$key.'" '.$selected.'>'.$val.'</option>';
        }
        echo '</select>';
    }


}


?>