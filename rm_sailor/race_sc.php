<?php
/**
 * race_sc.php - adds entry for selected races
 * 
 *
 * 
 */
$loc        = "..";       
$page       = "race_sc";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"race_sc",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/entry_class.php");

// set option details
$valid_opt = array("signon", "declare", "retire");

// connect to database to get event information
$db_o = new DB();
$event_o = new EVENT($db_o);

// update event details
$_SESSION['events'] = get_event_details($_SESSION['event_passed']);
$event_status = $_SESSION['events']['details'][$_REQUEST['event']]['event_status'];

empty($_REQUEST['opt']) ? $opt = "" : $opt = strtolower($_REQUEST['opt']);
if (in_array($opt, $valid_opt))
{
    $eventid = $_REQUEST['event'];
    $action = $opt;
    $msg = "";
    if ($opt == "signon")
    {
        if ( $event_status == "scheduled" or $event_status == "selected")
        {
            $success = process_signon($_REQUEST['event']);
            if ($success) { $status = "ok"; }
        }
        else
        {
            $status = "err";
            $msg = "race has started";
        }
    }
    elseif ($opt == "declare")
    {
        $status = process_declare($_REQUEST['event']);
    }
    elseif ($opt == "retire")
    {
        $success = process_retire($_REQUEST['event']);
        if ($success) { $status = "ok"; }
    }
    // update information on entries
    $_SESSION['entries'] = get_entry_information($_SESSION['sailor']['id'], $_SESSION['events']['details']);
}
else
{
    // report error
}

header("Location: race_pg.php?event=$eventid&action=$action&status=$status&msg=$msg");
exit();

function process_signon($eventid)
{
    global $db_o;

    $entry = $_SESSION["entries"][$eventid];

    // add to entry table
    $entry_o = new ENTRY($db_o, $eventid, $_SESSION['events']['details'][$eventid]);
    $status = $entry_o->add_signon($_SESSION['sailor']['id'], $entry['allocate']['status'],
        $_SESSION['sailor']['chg-helm'], $_SESSION['sailor']['chg-crew'], $_SESSION['sailor']['chg-sailnum']);

    if ($status == "update" OR $status == "enter")
    {
        empty($_SESSION['sailor']['chg-helm']) ? $chg_helm = "" : $chg_helm = "*";
        empty($_SESSION['sailor']['chg-crew']) ? $chg_crew = "" : $chg_crew = "*";
        empty($_SESSION['sailor']['chg-sailnum']) ? $chg_sailnum = "" : $chg_sailnum = "*";
        u_writelog("event $eventid | {$_SESSION['sailor']['classname']} 
                 | {$_SESSION['sailor']['sailnum']}  -> $chg_sailnum 
                 | {$_SESSION['sailor']['helmname']} -> $chg_helm 
                 | {$_SESSION['sailor']['crewname']} -> $chg_crew | $status","");

        $success = true;
    }
    else
    {
        u_writelog("event $eventid | {$_SESSION['sailor']['classname']} 
                 | {$_SESSION['sailor']['sailnum']} | entry failed [reason: $status]", "");
        $success = false;
    }

    return $success;
}

function process_declare($eventid)
{
    global $db_o;
    $status = "";

    // update entry array
    $_SESSION['entries'][$eventid]['declare'] =  "declare";

    // add record to entry table to record declaration
    $entry_o = new ENTRY($db_o, $eventid, $_SESSION['events']['details'][$eventid]);
    $status = $entry_o->add_declare($_SESSION['sailor']['id']);
    if ($status == "declare")
    {
        // create log record
        u_writelog("event $eventid | {$_SESSION['sailor']['classname']} 
                    | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} 
                    | declared","");
    }
    else
    {
        // create log record of failure
        u_writelog("event $eventid | {$_SESSION['sailor']['classname']} 
                    | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} 
                    | declare FAILED","");
    }
    return $status;
}

function process_retire($eventid)
{
    global $db_o;

    // update entry array
    $_SESSION['entries'][$eventid]['declare'] =  "retire";

    // add record to entry table to record declaration
    $entry_o = new ENTRY($db_o, $eventid, $_SESSION['events']['details'][$eventid]);
    $status = $entry_o->add_retire($_SESSION['sailor']['id']);
    if ($status == "retire")
    {
        // create log record
        u_writelog("event $eventid | {$_SESSION['sailor']['classname']} 
                    | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} 
                    | retired","");
        $success = true;
    }
    else
    {
        // create log record of failure
        u_writelog("event $eventid | {$_SESSION['sailor']['classname']} 
                    | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} 
                    | retirement FAILED","");
        $success = false;
    }
    return $success;
}
