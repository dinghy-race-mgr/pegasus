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
$page       = "raceinit";

require_once ("{$loc}/common/lib/util_lib.php");

// check we have a valid numeric eventid and mode
$eventid = u_checkarg("eventid", "checkintnotzero","");
$mode = u_checkarg("mode", "set", "", "init");

if (!$eventid)
{
    u_exitnicely($scriptname, 0, "requested event has an invalid or missing record identifier [{$_REQUEST['eventid']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// start session
session_id('sess-rmracebox');   // creates separate session for this application
session_start();

// starts session and sets error reporting
u_initpagestart($eventid, $page, false);

// classes / libraries
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/rota_class.php");
require_once ("{$loc}/common/classes/race_class.php");

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
        u_exitnicely($scriptname, $eventid,"race initialisation - the requested event details can not be found in the raceManager database",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
    elseif ($status == "fleetcfg_error")
    {
        u_exitnicely($scriptname, $eventid,"race initialisation - fleet format details for the selected event can not be found in the raceManager database",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
    elseif ($status == "fleetinit_error")
    {
        u_exitnicely($scriptname, $eventid,"race initialisation - fleet information for this race was not correctly installed in the raceManager database",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
    elseif ($status == "racecfg_error")
    {
        u_exitnicely($scriptname, $eventid,"race initialisation - the race format requested for the selected event cannot be found in the raceManager database, or is not currently used",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
}
else
{
    u_exitnicely($scriptname, $eventid,"race initialisation - the event has an invalid record identifier [{$_REQUEST['eventid']}] or initialisation mode [{$_REQUEST['mode']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
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


