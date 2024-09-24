<?php
/* rm_event.php

    Main script for rm_event application

*/

// start session
session_id('sess-rmevent');
session_start();

// error_reporting(E_ERROR);  // turn off warnings for live operation
require_once("include/rm_event_lib.php");
require_once("classes/pages.php");
require_once("classes/template.php");
require_once("classes/db.php");
require_once("../common/lib/rm_lib.php");

// initialise application
$cfg = set_config("config.ini", array("rm_event"), true);   // FIXME location of ini file
$cfg['logfile'] = str_replace("_date", date("_Y"), $cfg['logfile']);

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
}
empty($_REQUEST['year']) ? $year = date("Y") : $year = $_REQUEST['year'];

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
