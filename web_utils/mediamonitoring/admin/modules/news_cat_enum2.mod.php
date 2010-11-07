<?
/* news category, support subcategory
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include_once(APP_INCLUDE_ROOT.'/dbgrid.class.php');
import_module('news_cat');

class news_cat_enum2 extends news_cat {
    var $db_table, $properties;
    function news_cat_enum2() {
        parent::news_cat(); # must call base class
    }

    //override to display cat and subcat also. see news module
    function enum_list() {  # return list of id/desc
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

}

?>