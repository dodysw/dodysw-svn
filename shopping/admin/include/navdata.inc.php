<?
/*
each member of $navdata array is an array consisting these consecutive data:
1. module name (string)
2. label (string)
3. user level (int):
    -2  allow access even without authentication, eg: login form
    -1  allow access for all authenticated users, eg: logout form
    0   only application developer (user "supervisor") has access, eg: drop table
    1   highest level of normal user permission, only access by level 0 user (supervisor) and 1 (admin). eg: access to all normal module.
    2  only level 2 or lower user has access. eg: access for data entry user.
    3  only level 3 or lower user has access. eg: access for guest user.
    and so on...
4. optional: group access (string separated by '^' mark)
    - after user level has passed (see no.3 above), this would check access by its group memberships.
    - to notate more than one groups, join the group id with '^' character, eg: FINANCE^ACCOUNTING
    - which imply that user be allowed for access if he/she is joined to ONE OR MORE of given groups.
    - note: the specified group id must exist!
    - to not use this access constraint, pass an empty string ('' or "").
*/
$navdata = array();
$subdir = 'prjtol/';
$navdata['main'] = array(
    array('',lang('System'),1),
    array('admfp',lang('Home'),-1),
    array('login','Login',-2),
    array('logout','Logout',-1),
    array('phpinfo','phpinfo',0),
    array('manage_modules',lang('ManageModules'),0),
    array('seq_gen',lang('SequenceGenerator'),0),
    array('usrmgr',lang('UserManager'),1),
    array('usrmgr_login',lang('UserManagerLogin'),1),
    array('upload_manager',lang('UploadManager'),1),
    #~ array('aa_detikusable',lang('Detik.Usable'),-2),

    array('',lang('Product'),1),
    array('product','Product',1),
    array('product_category','ProductCategory',1),
    array('product_manufacturer','ProductManufacturer',1),
    array('sub_product','SubProduct',1),

    array('',lang('Customer'),1),
    array('membership','Membership',1),
    array('member_order','MemberOrder',1),
    array('member_order_items','MemberOrderItems',1),
    array('member_cart','MemberCart',1),

    #~ array('',lang('ModuleDesigner'),0),
    #~ array('design_table_group',lang('DesignTableGroup'),0),
    #~ array('design_table',lang('DesignTable'),0),
    #~ array('design_table',lang('DesignSmartCode'),0),
    #~ array('design_table',lang('DesignProgram'),0),

    );

$navdata['manage_module'] = array(
    array('',lang('BatchData'),1),
    array('ingen_csv',lang('CsvForInputComma'),0),
    array('ingen_csv_tab',lang('CsvForInputTab'),0),
    array('enter_ingen_csv',lang('InputCsv'),0),
    array('auto_test_data',lang('AutoTestData'),0),

    array('',lang('MaintainDatabase'),1),
    array('create_table',lang('CreateTable'),0),
    array('merge_table',lang('MergeTableChanges'),0),
    array('do_sql','SQL',0),
    array('drop_table',lang('DropTable'),0),
    array('purge_table',lang('PurgeTable'),0),



    );
?>