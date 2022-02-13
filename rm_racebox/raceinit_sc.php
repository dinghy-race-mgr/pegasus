<?php
/*
 * raceinit_sc - event initialisation functionality
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

// check we have a valid numeric eventid and mode
$eventid = u_checkarg("eventid", "checkintnotzero","");
$mode = u_checkarg("mode", "set", "", "init");

$db_o = new DB();
$event_o = new EVENT($db_o);

//echo "<pre>event:$eventid mode:$mode</pre>";
if ($eventid and ($mode == "init" or $mode == "reset" or $mode == "rejoin"))
{
    // clear event session
    unset($_SESSION["e_$eventid"]);

    // start event log
    u_starteventlogs($scriptname, $eventid, $mode);

    // reset event
    $status = $event_o->event_reset($eventid, $mode);

    if ($status == "event_error")
    {
        u_exitnicely($scriptname, $eventid, "the requested event details can not be found in the raceManager database",
            "please contact your raceManager administrator");
    }
    elseif ($status == "fleetcfg_error")
    {
        u_exitnicely($scriptname, $eventid, "the fleet format details for the selected event can not be found in the raceManager database.",
            "please contact your raceManager administrator");
    }
    elseif ($status == "fleetinit_error")
    {
        u_exitnicely($scriptname, $eventid, "the fleet information for this race was not correctly installed in the database.",
            "please contact your raceManager administrator");
    }
    elseif ($status == "racecfg_error")
    {
        u_exitnicely($scriptname, $eventid, "the race format requested for the selected event can not be found in the raceManager database, or is not currently used.",
            "please contact your raceManager administrator");
    }
}
else
{
    u_exitnicely($scriptname, 0, "the requested event has an invalid record identifier [{$_REQUEST['eventid']}] or mode [{$_REQUEST['mode']}]",
        "please contact your raceManager administrator");
}

if ($mode == "init")
{
    // first time accessing this event - show relevant reminders
    header("Location: reminder_pg.php?eventid=$eventid&afterlink=race_pg.php?eventid=$eventid");
}
else
{
    // not first time accessing this event - go straight to race page
    header("Location: race_pg.php?eventid=$eventid");
}


