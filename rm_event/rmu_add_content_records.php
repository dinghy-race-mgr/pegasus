<?php
/*
 * Has two modes:
 *   all:  copies all content elements for one event to the e_content table for a new event
 *         args
 *            target_eid - event id for receiving event
 *            userid - rm_admin user id
 *
 *   single: copies a specific content record
 *         args
 *            target_eid - event id for receiving event
 *            content_id - id of e_content record to be copied
 *            userid - rm_admin user id
 *
 */

$loc = "..";
$today = date("Y-m-d");

require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/classes/db.php");
require_once ("{$loc}/common/classes/template_class.php");

// start session
session_id('sess-rmuevent');
session_start();

error_reporting(E_ERROR);  // set to E_ALL for debugging

// decode arguments
$mode = u_checkarg("mode","checkset","single", "all");
$userid = u_checkarg("userid","set", "", "unknown");
$target_eid = u_checkarg("eid","checkintnotzero", "", "");
$content_id = u_checkarg("content_id","checkintnotzero", "", "");

// initialise application
$cfg = u_set_config("../config/common.ini", array(), true);
$cfg['rm_event'] = u_set_config("../config/rm_event.ini", array("rm_event"), true);
foreach($cfg['rm_event'] as $k => $v) { $cfg[$k] = $v; }
unset($cfg['rm_event']);

$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

$err = "";
// check that we have eventid
if (!$target_eid)
{
    // get record(s) to be copied
    if ($mode=="all")
    {
        // set list of placeholder templates into a list for use in sql statement
        $list = "";
        $arr = explode(",", $cfg['std_content']);
        foreach ($arr as $item) { $list.= "'$item',"; }
        $list = substr($list, 0, -1);

        //find the relevant placeholder records (eid = 0 and name is in list)
        $content_records = $db_o->run("SELECT * FROM e_content WHERE eid = 0 and name in ($list)", array() )->fetchall();
        $count = count($content_records);
    }
    else
    {
        //find the relevant placeholder records (eid = 0 and name is in list)
        $content_records = $db_o->run("SELECT * FROM e_content WHERE id = $content_id", array() )->fetchall();
        $count = count($content_records);
    }

    if ($count > 0)
    {
        if (!empty($cfg['std_content']))
        {
            $copy = $db_o->run("INSERT INTO e_content (`open_type`,`eid`,`page`,`name`,`description`,`content-label`,`content`,`link`,`link-label`,`image`,`image-label`,`image_posn`,`reusable`,`updby`) 
                            SELECT `open_type`,`eid`,`page`,`name`,`description`,`content-label`,`content`,`link`,`link-label`,`image`,`image-label`,`image_posn`,0,'{$_REQUEST['userid']}'
                             FROM e_content WHERE id = $content_id" , array());
        }
    }
    else
    {
        $err.= "- could not find the placeholder content record(s) for the donating event <br><br>";
    }
}
else
{
    $err.= "- the event id to receive the new content records was not provided<br><br>";
}

if (empty($err))
{
    echo <<<EOT
<div style="font-family: Kalinga, Arial, Helvetica, sans-serif; margin-top: 20px;">
<h2>Copying Placeholder Content Records </h2>
<p><b>$count</b> records have been added to this event with placeholder content - you can now edit the record(s) to add to, or replace information </p>
<p>Close this browser tab to return to the open events page.  You WILL need to refresh the page (F5) to see the new record(s).</p>
</div>
EOT;
}
else
{
    echo <<<EOT
<div style="font-family: Kalinga, Arial, Helvetica, sans-serif; margin-top: 20px;">
    <h2><b>FAILED</b> - Copying Placeholder Content Records </h2>
    <p>The following problem(s) were encountered ...</p>
    <p style="margin-left:30px">$err</p>
    <p>Please contact your system administrator </p>
    <p>Close this browser tab to return to the open events page.</p>
</div>
EOT;
}
exit("processing complete");



