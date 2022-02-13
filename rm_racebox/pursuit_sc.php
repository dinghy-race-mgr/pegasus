<?php
/**
 * pursuit_sc.php
 *
 * Server processing for pursuit page controls
 *
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 *
 * %%copyright%%
 * %%license%%
 *
 * @param int $eventid
 * @param string $pagestate

 *
 *
 */
$loc        = "..";       // <--- relative path from script to top level folder
$page       = "pursuit";     //
$scriptname = basename(__FILE__);

// required libraries
require_once ("{$loc}/common/lib/util_lib.php");

// script parameters
$eventid   = u_checkarg("eventid", "checkintnotzero","");
$pagestate = u_checkarg("pagestate", "set", "", "");
$stop_here = false;

u_initpagestart($eventid, $page, false);   // starts session and sets error reporting


if ($eventid AND $pagestate)
{
    require_once("{$loc}/common/classes/db_class.php");
    require_once("{$loc}/common/classes/event_class.php");
    include("./include/pursuit_growls.inc");

    $db_o = new DB;
    $event_o = new EVENT($db_o);

    $event = $event_o->get_event_byid($eventid);

// ---- handle specific controls -------------------------------------------------------------
// ---- xxx ---------------------------------
    if ($pagestate == "xxx") {
        u_writelog("xxxx", $eventid);
        u_growlSet($eventid, $page, $g_pursuit_something);

// ---- yyy ---------------------------------
    } elseif ($pagestate == "yyy") {
        u_writelog("xxxx", $eventid);
        u_growlSet($eventid, $page, $g_pursuit_something);
    }

//  check status / update session
    $race_o->racestate_updatestatus_all(1, $page);

//  return to pursuit page
    if (!$stop_here) { header("Location: pursuit_pg.php?eventid=$eventid&xxx"); exit(); }

}
else
{
    u_exitnicely($scriptname, $eventid, "0", "Either the event id [{$_REQUEST['id']} or the requested operation [{$_REQUEST['pagestate']}]
    was not recognised.  Close this browser and restart raceManager to return to your race.  If the problems continue please report 
    the error to your system administrator");
}
