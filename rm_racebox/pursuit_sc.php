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
require_once ("{$loc}/common/lib/util_lib.php");

$eventid   = (!empty($_REQUEST['eventid']))? $_REQUEST['eventid']: "";
$pagestate = (!empty($_REQUEST['pagestate']))? $_REQUEST['pagestate']: "";

u_initpagestart($eventid, $page, false);   // starts session and sets error reporting

// initialising language
//include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");

if ($eventid AND $pagestate) {
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

// ---- pagestate unknown -------------------
    } else {
        u_exitnicely($scriptname, $eventid, "sys005", $lang['err']['exit-action']);

    }
// ---- return to calling page --------------
    header("Location: pursuit_pg.php?eventid=$eventid&xxx");
    exit();
}
else
{
    u_exitnicely($scriptname, $eventid, "sys005", $lang['err']['exit-action']);
}
?>