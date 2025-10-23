<?php
/* rm_event.php

    Main script for rm_event application

    Args
    eid   - id for event record in e_event  (set to 0 if not set)
    event - event nickname - optional mechanism to select event                                // fixme - do I need this
    page  - page to be displayed (set to 'list' if not set)
//    view  - code used to give access to user overriding entry restrictions - set in ini file    // fixme - do I need this
    year  - event year to display on list page (set to current year if not supplied)
    view  - sets viewing mode - standard or preview. Preview allows direct viewing of site even if it is currently only listed
            primarily for use by the event page developer. In preview mode the eid value must be set

    -----
    additional args if returning from entry form after entry created

       action   - indicates type of action made to entry record (newentry or updentry or delentry) (set to false if not supplied)
       status   - indicates status - success or fail (set to false if not supplied  // fixme what are the status values
       recordid - id for entry in e_entry (set to false if not supplied)
       junior   - indicates entry is for junior  (set to false if not supplied) // fixme what is its value if supplied
       waiting  -  indicates entry is on waiting list (set to false if not supplied) // fixme what is its value if supplied

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
foreach($cfg['rm_event'] as $k => $v) {$cfg[$k] = $v;}
unset($cfg['rm_event']);
$cfg['logfile'] = str_replace("_date", date("_Y"), $cfg['logfile']);

//   // need to send 19821 if preview
///
///  // check "view" status - allows user to ignore entry restrictions  // fixme make this preview - put previow in header
//$cfg['view_status'] = false;
//if (!empty($_REQUEST['view']))
//{
//    if ($_REQUEST['view'] == $cfg['view_code']) { $cfg['view_status'] = true; }
//}


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
    empty($_REQUEST['eid'])  ? $eid = 0 : $eid = $_REQUEST['eid'];
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
