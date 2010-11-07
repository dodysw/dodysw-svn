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

$navdata['main'] = array(
    array('',lang('System'),1),
    array('admfp',lang('Home'),-1),
    array('login','Login',-2),
    array('logout','Logout',-1),
    array('phpinfo','phpinfo',0),
    array('manage_modules',lang('Manage Modules'),0),
    array('seq_gen',lang('Sequence Generator'),0),
    array('usrmgr',lang('User Manager'),1),
    #~ array('aa_detikusable',lang('Detik.Usable'),-2),

    array('',lang('News'),1),
    array('news','Berita',1),
    array('news_cat','Kategori Berita',1),
    array('',lang('Directory'),1),
    array('dirstruct','Direktori',1),
    array('dir_entry','Entri Direktori',1),
    array('jenis_organisasi','Jenis Organisasi',1),
    array('',lang('Links'),1),
    array('linkstruct','Link',1),
    array('link_entry','Entri Link',1),
    );

$navdata['manage_module'] = array(
    array('ingen_csv',lang('Generate CSV for input (Comma)'),0),
    array('ingen_csv_tab',lang('Generate CSV for input (Tab)'),0),
    array('enter_ingen_csv',lang('Enter CSV for input'),0),
    array('','______________',0),
    array('create_table',lang('Create table'),0),
    array('merge_table',lang('Merge changes'),0),
    array('do_sql','Sql',0),
    array('','______________',0),
    array('drop_table',lang('Drop table'),0),
    array('purge_table',lang('Purge table'),0),
    );
?>