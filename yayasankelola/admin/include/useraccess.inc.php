<?
/*
    simple text-based user access definition. also used by nav bar.

    level note:
    -2 always available, even when not authenticated,
    -1 always available, when authenticated,
    0 sysadmin, only for developer
    1 highest level of user permission,
    2+ lower and lower permission

    format: module_id, module_name, minimum level, groupa^groupb^groupc,
*/
$user_access = <<<__END__

,_____System_____,1
admfp,Home,-1
login,Login,-2
logout,Logout,-1
phpinfo,phpinfo,0
manage_modules,Manage Modules,0
project_invitation, Scheduled Project Invitation,0
seq_gen, Sequence Generator,0
usrmgr, User Manager,1
kode_area, Kode area,1

,_____User Manager_____,1
usrmgr_de, Data Entry User,1
usrmgr_po, Project Officer User,2, PO
#~ 'usrmgr_pa,Partner User,2
partner, Partner Profile, 2, DE^PA
partner_simple, Partner List, 2, DE
project, Projects, 2, PO^PA

,_____News_____,1
news,News Manager,1
#~ 'category,'Category,1

,_____Reports_____,1
partner_rpt1,Partner projects,2,PO
partner_rpt2,Partner project status,2,PO

__END__;



$manage_modules_access = <<<__END__
create_table,Create table,0
merge_table,Merge changes,0
do_sql,Sql,0
,______________,0
drop_table,Drop table,0
purge_table,Purge table,0
__END__;



?>