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

$loc        = "..";
$scriptname = basename(__FILE__);
$page = "raceinit";
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/rota_class.php");
require_once ("{$loc}/common/classes/race_class.php");

// starts session and sets error reporting
u_initpagestart($_REQUEST['eventid'], $page, false);

// check we have a valid numeric eventid - else exit
$eventid = u_checkarg("eventid", "checkintnotzero","");

if ($eventid)
{
    // clear event session
    unset($_SESSION["e_$eventid"]);
        
    // start event log
    u_starteventlogs($scriptname, $eventid);
    
    // initialise event from database
    //u_writedbg("<pre>session".print_r($_SESSION,true)."</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:
    $status = r_initialiseevent($_REQUEST['mode'], $eventid);

    if ($status == "event_error")
    {
        u_exitnicely($scriptname, $eventid, "the requested event details can not be found in the raceManager database",
            "please contact your raceManager administrator");
    }
    elseif ($status == "fleet_error")
    {
        u_exitnicely($scriptname, $eventid, "the fleet format details for the selected event can not be found in the raceManager database.",
            "please contact your raceManager administrator");
    }
    elseif ($status == "race_error")
    {
        u_exitnicely($scriptname, $eventid, "the race format requested for the selected event can not be found in the raceManager database, or is not currently used.",
            "please contact your raceManager administrator");
    }   
}
else
{
    u_exitnicely($scriptname, $eventid, "the requested event has an invalid record identifier [{$_REQUEST['arg']}]",
        "please contact your raceManager administrator");
}


header("Location: race_pg.php?eventid=$eventid");
exit();

