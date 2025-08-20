<?php
/* rm_event.php

    Main script for rm_event application

*/

// start session
session_id('sess-rmevent');
session_start();
error_reporting(E_ALL);
// error_reporting(E_ERROR);  // turn off warnings for live operation
require_once("../common/classes/db.php");
require_once("../common/lib/rm_lib.php");
require_once("../common/lib/rm_event_lib.php");
require_once("classes/pages.php");
require_once("classes/template.php");
require_once("include/rm_event_fields.php");

// initialise application
$cfg = set_config("../config/common.ini", array(), false);
$cfg['rm_event'] = set_config("../config/rm_event.ini", array("rm_event"), true);
foreach($cfg['rm_event'] as $k => $v)
{
    $cfg[$k] = $v;
}
unset($cfg['rm_event']);
$cfg['logfile'] = str_replace("_date", date("_Y"), $cfg['logfile']);

// check "view" status - allows user to ignore entry restrictions
$cfg['view_status'] = false;
if (!empty($_REQUEST['view']))
{
    if ($_REQUEST['view'] == $cfg['view_code']) { $cfg['view_status'] = true; }
}

$if_o = new PAGES($cfg);
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// arguments
if (key_exists("event", $_REQUEST))   // requesting single nicknamed event
{
    // find event matching nick name
    $eid = $db_o->run("SELECT id FROM e_event WHERE nickname = ?", array($_REQUEST['event']) )->fetchColumn();
    if ($eid)
    {
        empty($_REQUEST['page']) ? $page = "details" : $page = $_REQUEST['page'];
    }
    else
    {
        $page = "list";
    }
}
else
{
    empty($_REQUEST['eid']) ? $eid = 0 : $eid = $_REQUEST['eid'];
    empty($_REQUEST['page']) ? $page = "list" : $page = $_REQUEST['page'];
    empty($_REQUEST['view']) ? $view = "0" : $view = $_REQUEST['view'];
}
empty($_REQUEST['year']) ? $year = date("Y") : $year = $_REQUEST['year'];

// create array if an entry record has been added
$entryupdate = array();
empty($_REQUEST['action'])   ? $entryupdate['action']   = false : $entryupdate['action'] = $_REQUEST['action'];
empty($_REQUEST['status'])   ? $entryupdate['status']   = false : $entryupdate['status'] = $_REQUEST['status'];
empty($_REQUEST['recordid']) ? $entryupdate['recordid'] = false : $entryupdate['recordid'] = $_REQUEST['recordid'];
empty($_REQUEST['junior'])   ? $entryupdate['junior']   = false : $entryupdate['junior'] = $_REQUEST['junior'];
empty($_REQUEST['waiting'])  ? $entryupdate['waiting']  = false : $entryupdate['waiting'] = $_REQUEST['waiting'];

if ($page == "list")                        // events list page
{
    $if_o->pg_list($db_o, $year);
}
else                                        // specific event page
{
    $if_o->pg_event($db_o, $page, $eid, $entryupdate);
}
