<?php
/*
 * Adds placeholder content elements to the e_content table
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

// initialise application
$cfg = u_set_config("../config/common.ini", array(), true);
$cfg['rm_event'] = u_set_config("../config/rm_event.ini", array("rm_event"), true);
foreach($cfg['rm_event'] as $k => $v) { $cfg[$k] = $v; }
unset($cfg['rm_event']);

$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

$err = "";
// check that we have eventid
if (u_checkarg("eid","checkintnotzero","set", ""))
{
    // set list of placeholder templates into a list for use in sql statement
    $list = "";
    $arr = explode(",", $cfg['std_content']);
    foreach ($arr as $item) { $list.= "'$item',"; }
    $list = substr($list, 0, -1);

    // find the relevant placeholder records (eid = 0 and name is in list)
    $content_records = $db_o->run("SELECT * FROM e_content WHERE eid = 0 and name in ($list)", array() )->fetchall();
    $count = count($content_records);

    if ($count > 0)
    {
        $content_num = 0;
        if (!empty($cfg['std_content']))
        {
            $copy = $db_o->run("INSERT INTO e_content (`open_type`,`eid`,`page`,`name`,`description`,`content-label`,`content`,`link`,`link-label`,`image`,`image-label`,`image_posn`,`reusable`,`updby`) 
                            SELECT `open_type`,{$_REQUEST['eid']},`page`,`name`,`description`,`content-label`,`content`,`link`,`link-label`,`image`,`image-label`,`image_posn`,0,'{$_REQUEST['userid']}'
                             FROM e_content WHERE name in ($list) and eid = 0" , array());
        }
    }
    else
    {
        $err.= "- could not find the placeholder records<br><br>";
    }
}
else
{
    $err.= "- the event id was not provided<br><br>";
}

if (empty($err))
{
    echo <<<EOT
<div style="font-family: Kalinga, Arial, Helvetica, sans-serif; margin-top: 20px;">
<h2>Copying Placeholder Content Records </h2>
<p><b>$count</b> records have been added to this event with placeholder content - you can now edit the records to add the required information </p>
<p>Close this browser tab to return to the open events page.  You WILL need to refresh the page (F5) to see the new records.</p>
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






/*

//$tmpl_o = new TEMPLATE(array( "./templates/util_layouts_tm.php"));

//if (empty($_REQUEST['access']) OR $_REQUEST['access'] != $cfg['access_key'])
//{
//    exit("Sorry - you are not authorised to use this script ... ");  // FIXME -  change to exit_nicely
//}

// check pagestate
key_exists("pagestate", $_REQUEST) ? $pagestate = $_REQUEST['pagestate'] : $pagestate = "init";

// find event information
if (key_exists("eid", $_REQUEST))
{
    $eid = $_REQUEST['eid'];
    $event = $db_o->run("SELECT * FROM e_event WHERE id = ?", array($eid) )->fetch();
}

// set page navbar
$navbar = $tmpl_o->get_template("navbar_utils", array("util-name"=> "Set Tally Numbers",
    "release" => $cfg['sys_release'], "version" =>$cfg['sys_version'], "year"=>date("Y") ), array());



//if (!isset($eid) or empty($event))
//{
//    echo "exit nicely error message - event not found";  // FIXME -  change to exit_nicely
//    exit("script stopped");
//}

//// check if fleet information is required
//key_exists("fleet", $_REQUEST) ? $fleet = true : $fleet = false;

//// get output configuration
//$output = get_report_config($_REQUEST['output'], $fleet);

if (($pagestate != "init" and $pagestate != "submit") or (!isset($eid)))
{
    // deal with bad arguments
}
elseif ($pagestate == "init")
{
    // output instructions
    $fields = array(
        "event-title" => $event['title'],
        "version"     => $cfg['sys_release']." ".$cfg['sys_version'],
    );
    $body = $tmpl_o->get_template("set_tally_info", $fields, array("eid"=>$eid, "state" => "instructions"));

}
elseif ($pagestate == "submit")
{
    // add tally numbers + link to tally report

    // get all confirmed entry data (excluding waiting list - class, sailnumber)
    $sql = "SELECT `id`, `b-class`, `b-sailno`, `b-fleet`, `b-division`, `h-name`, `h-club`, `h-age`, `h-emergency`, 
               `c-name`, `c-age`, `c-emergency`, `e-tally` FROM e_entry  WHERE eid = ? and `e-exclude` = 0 and `e-waiting` = 0 
                ORDER BY `b-class` ASC, `b-sailno` * 1 ASC";
    $entries = $db_o->run($sql, array($eid) )->fetchall();

    $count = count($entries);

    $i = 0;
    foreach ($entries as $row)
    {
        $i++;
        $sql = "UPDATE e_entry SET `e-tally` = $i WHERE id = {$row['id']}";
        $upd = $db_o->run($sql, array());
    }

    $fields = array(
        "event-title" => $event['title'],
        "version"     => $cfg['sys_release']." ".$cfg['sys_version'],
    );
    $body = $tmpl_o->get_template("set_tally_info", $fields, array("eid"=>$eid, "state"=>"results", "count"=>$count));
}
else
{
    echo "exit nicely error message - event not found";  // FIXME -  change to exit_nicely
    exit("script stopped");
}

$fields = array(
    'tab-title'   => "Set Tally",
    'styletheme'  => "sandstone_",
    'page-navbar' => $navbar,
    'page-title'  => $event['title'],
    'page-main'   => $body,
    'page-footer' => "&nbsp;",
    'page-modals' => "&nbsp;",
    'page-js'     => "&nbsp;"
);
$params = array();
echo $tmpl_o->get_template("utils_page", $fields, array());
exit();

*/


