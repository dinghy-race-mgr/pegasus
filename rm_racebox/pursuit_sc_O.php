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
$stop_here  = false;

// required libraries
require_once ("{$loc}/common/lib/util_lib.php");

// script parameters
$eventid   = u_checkarg("eventid", "checkintnotzero","");
$pagestate = u_checkarg("pagestate", "set", "", "");

if (!$eventid or !$pagestate) {
    u_exitnicely($scriptname, 0, "$page page - requested event has an invalid or missing record identifier [{$_REQUEST['eventid']}] or pagestate [{$_REQUEST['pagestate']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));  }


// start session
session_id('sess-rmracebox');
session_start();

// page initialisation
u_initpagestart($eventid, $page, false);


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
    u_exitnicely($scriptname, $eventid, "$page page has an invalid or missing event identifier [{$_REQUEST['eventid']}] or page state [{$_REQUEST['pagestate']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}