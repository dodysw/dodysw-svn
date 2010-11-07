<?
/*
 copyright 2004 - dody suria wijaya, dsw software house - contact: dswsh@plasa.com */

include 'config.inc.php';
include $include_dir.'func.inc.php';

# select project_invitation_tab with project_id = $project_id, get $po1_exp_date, $po2_exp_date,
# po_stage = 1,2,3 indicating waiting for acceptance from that po

# construct current datetime
$now = date("Y-m-d H:i:s");
$curdate = date("Y-m-d");

# findout projects which is waiting for po1 (stage = 1) and po1 has expired
echo '<h3>Checking Expired PO1 invitation</h3>';
$sql = "select rowid,project_id, po1_exp_date, po2_exp_date, po_stage from {$GLOBALS['dbpre']}project_tab where po_stage='1' and po1_exp_date < '$now'";
echo '<p>'.$sql;
$temp = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
if (!mysql_num_rows($temp))
    echo '<p>no pending projects';
while ($row = mysql_fetch_row($temp)) {
    list($rowid, $project_id, $po1_exp_date, $po2_exp_date) = $row;
    echo '<br>', $rowid, $project_id, $po2_exp_date, $po2_exp_date;

    # move project to stage 2
    $email_cookie = md5($now);
    $sql = "update {$GLOBALS['dbpre']}project_tab set po_stage='2', email_cookie='$email_cookie',status='waiting for po2' where project_id='$project_id'";
    mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());

    # send invitation email to po2
    $project = instantiate_module('project');
    $project->populate($rowid);
    $project->send_invitation('po2',0);

}

# findout projects which is waiting for po2 (stage = 2) and po2 has expired
echo '<h3>Checking Expired PO2 invitation</h3>';
$sql = "select rowid,project_id, po1_exp_date, po2_exp_date, po_stage from {$GLOBALS['dbpre']}project_tab where po_stage='2' and po2_exp_date < '$now'";
echo '<p>'.$sql;
$temp = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
if (!mysql_num_rows($temp))
    echo '<p>no pending projects';
while ($row = mysql_fetch_row($temp)) {
    list($rowid, $project_id, $po1_exp_date, $po2_exp_date) = $row;
    echo '<br>',$rowid, $project_id, $po1_exp_date, $po2_exp_date;

    # move project to stage 3
    $email_cookie = md5($now);
    $sql = "update {$GLOBALS['dbpre']}project_tab set po_stage='3', email_cookie='$email_cookie',status='waiting for po3' where project_id='$project_id'";
    mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());

    # send invitation email to po3
    $project = instantiate_module('project');
    $project->populate($rowid);
    $project->send_invitation('po3',0);

    # update po3_email_sent_date to today (used for daily sending in stage 3
    $sql = "update {$GLOBALS['dbpre']}project_tab set po3_email_sent_date=Now() where project_id='$project_id'";
    mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
}

# ----
# projects in stage 3 (waiting for po3) will need to be send daily invitation
echo '<h3>Checking PO3 daily invitation</h3>';
$sql = "select rowid,project_id, po1_exp_date, po2_exp_date, po_stage from {$GLOBALS['dbpre']}project_tab where po_stage='3' and po2_exp_date < '$now' and '$now' <= adddate(po2_exp_date, interval 7 day) and '$curdate' != po3_email_sent_date";
echo '<p>'.$sql;
$temp = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
if (!mysql_num_rows($temp))
    echo '<p>no row';
while ($row = mysql_fetch_row($temp)) {
    list($rowid, $project_id, $po1_exp_date, $po2_exp_date) = $row;
    echo '<br>',$rowid, $project_id, $po1_exp_date, $po2_exp_date;

    # send invitation email to po3
    $project = instantiate_module('project');
    $project->populate($rowid);
    $project->send_invitation('po3',0);

    # update po3_email_sent_date to today (used for daily sending in stage 3
    $sql = "update {$GLOBALS['dbpre']}project_tab set po3_email_sent_date='$curdate' where project_id='$project_id'";
    mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
}
# ----


# projects in stage 3 (waiting for po3) after 7 days, will need to be send daily notification to ADMIN
echo '<h3>Checking Admin daily notification</h3>';
$sql = "select rowid,project_id, po1_exp_date, po2_exp_date, po_stage from {$GLOBALS['dbpre']}project_tab where po_stage='3' and po2_exp_date < '$now' and '$now' > adddate(po2_exp_date, interval 7 day) and '$curdate' != po3_email_sent_date";
echo '<p>'.$sql;
$temp = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
if (!mysql_num_rows($temp))
    echo '<p>no row';
while ($row = mysql_fetch_row($temp)) {
    list($rowid, $project_id, $po1_exp_date, $po2_exp_date) = $row;
    echo '<br>',$rowid, $project_id, $po1_exp_date, $po2_exp_date;

    # send notification email to ADMIN
    $project = instantiate_module('project');
    $project->populate($rowid);
    $project->send_notification('admin',0);

    # update po3_email_sent_date to today (used for daily sending in stage 3
    $sql = "update {$GLOBALS['dbpre']}project_tab set po3_email_sent_date='$curdate' where project_id='$project_id'";
    mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
}
# ----



#~ # findout projects which is waiting for po3 (stage = 3) and po3 has expired
#~ echo '<h3>Checking Expired PO3 invitation</h3>';
#~ $sql = "select rowid,project_id, po1_exp_date, po2_exp_date, po_stage from {$GLOBALS['dbpre']}project_tab where po_stage='3' and po3_exp_date < '$now'";
#~ echo '<p>'.$sql;
#~ $temp = mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());
#~ if (!mysql_num_rows($temp))
    #~ echo '<p>no pending projects';
#~ while ($row = mysql_fetch_row($temp)) {
    #~ list($rowid, $project_id, $po1_exp_date, $po2_exp_date) = $row;
    #~ echo '<br>',$rowid, $project_id, $po1_exp_date, $po2_exp_date;

    #~ # move project to stage 4 (admin)
    #~ $email_cookie = md5($now);
    #~ $sql = "update {$GLOBALS['dbpre']}project_tab set po_stage='4', email_cookie='$email_cookie',status='waiting for admin' where project_id='$project_id'";
    #~ mysql_query($sql) or die('<br>'.$sql.'<br>'.mysql_error());

    #~ # send invitation email to admin
    #~ $project = instantiate_module('project');
    #~ $project->populate($rowid);
    #~ $project->send_invitation('po3',0);
#~ }

?>
