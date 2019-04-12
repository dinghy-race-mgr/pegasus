<?php
/**
 * rbx_sc_raceinit - event initialisation functionality
 * 
 * The event session information is stored in a session array using the eventid
 * as an index, e.g $_SESSION[e_1234]['ev_name'] holds the eventname for event
 * 1234.
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * @param int   eventid     database id for event
 * 
 * %%copyright%%
 * %%license%%
 *     
 */

$loc        = "..";    //<-- path to root directory
$scriptname = basename(__FILE__);
$page = "raceinit";
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");

u_initpagestart($_REQUEST['eventid'], $page, false);

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/race_class.php");

include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");  // load language file

// check I have a valid eventid - else exit
$eventid = 0;
if (!empty($_REQUEST['eventid']))
{    
    if (is_numeric($_REQUEST['eventid'])) { $eventid = $_REQUEST['eventid']; }     // check it is a number
}

if ($eventid!=0)
{
    // clear event session
    unset($_SESSION["e_$eventid"]);
        
    // start event log
    u_starteventlogs($scriptname, $eventid);
    
    // initialise event from database
    $status = r_initialiseevent($_REQUEST['mode'], $eventid);

    if ($status == "event_error")
    {
        u_exitnicely($scriptname, $eventid, $lang['err']['sys002'],
            "requested event [$eventid] is not in the database");
    }
    elseif ($status == "fleet_error")
    {
        u_exitnicely($scriptname, $eventid, $lang['err']['sys004'],
            "fleet format for {$_SESSION["e_$eventid"]['ev_name']} {$_SESSION["e_$eventid"]['ev_seriesnum']} is not in the database.");
    }
    elseif ($status == "race_error")
    {
        u_exitnicely($scriptname, $eventid, $lang['err']['sys004'],
            "race format requested for {$_SESSION["e_$eventid"]['ev_name']} {$_SESSION["e_$eventid"]['ev_seriesnum']} is not the database or is not currently used.");
    }   
}
else
{
    u_exitnicely($scriptname, $eventid, $lang['err']['sys002'], "Cannot initialise the event as the event id is not defined.");
}

//echo "<pre>";print_r($_SESSION); echo "</pre>";
header("Location: race_pg.php?eventid=$eventid");
exit();



?>