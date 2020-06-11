<?php
/**
 * race_sc.php - adds entry for selected races
 * 
 *
 * 
 */
$loc        = "..";       
$page       = "race";
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

// arguments
empty($_REQUEST['opt']) ? $opt = "" : $opt = strtolower($_REQUEST['opt']);
empty($_REQUEST['event']) ? $eventid = 0 : $eventid = $_REQUEST['event'];

if (in_array($opt, $valid_opt) AND $eventid) {
    $action = $opt;
    $msg = "";
    if ($opt == "signon") {
        if ($event_status == "scheduled" or $event_status == "selected") {
            $success = process_signon($eventid);
            if ($success) {
                $status = "ok";
            }
        } else {
            $status = "err";
            $msg = "race has started";
        }
    } elseif ($opt == "declare") {
        $success = process_declare($eventid);
        if ($success) {
            $status = "ok";
        }
    } elseif ($opt == "retire") {
        $success = process_retire($eventid);
        if ($success) {
            $status = "ok";
        }
    }
    // update information on entries
    $_SESSION['entries'] = get_entry_information($_SESSION['sailor']['id'], $_SESSION['events']['details']);
} else {
    $action = "";
    $status = "err";
    $msg = "invalid option or event";
}

header("Location: race_pg.php?event=$eventid&action=$action&status=$status&msg=$msg");
exit();

