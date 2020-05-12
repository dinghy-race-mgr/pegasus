<?php
/**
 * cruise_sc.php - adds entry for dinghy cruising
 * 
 *
 * 
 */
$loc        = "..";       
$page       = "cruise";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"cruise_sc",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/cruise_class.php");
require_once ("{$loc}/common/classes/event_class.php");

// connect to database to get event information
$db_o = new DB();
$event_o = new EVENT($db_o);

// set option details
$valid_opt = array("register", "declare");

// update event details
$_SESSION['events'] = get_cruise_details($_SESSION['sailor_cruiser_eventtypes'], true);
$event_status = $_SESSION['events']['details'][$_REQUEST['event']]['event_status'];

// arguments
$opt = strtolower(check_argument("opt", "set", ""));
$eventid = strtolower(check_argument("eventid", "set", ""));
$cruise_type = strtolower(check_argument("cruise_type", "set", ""));

//empty($_REQUEST['opt'])         ? $opt = "" : $opt = strtolower($_REQUEST['opt']);
//empty($_REQUEST['eventid'])     ? $eventid = "" : $eventid = strtolower($_REQUEST['eventid']);
//empty($_REQUEST['cruise_type']) ? $cruise_type = "" : $cruise_type = $_REQUEST['cruise_type'];


if (in_array($opt, $valid_opt) AND $cruise_type) {
    // u_writedbg("entering in_array\n", __FILE__, __FUNCTION__, __LINE__, false);

    $action = $opt;
    $msg = "";
    $status = "";
    if ($opt == "register")   // register start of cruise
    {
        // u_writedbg("REGISTER\n", __FILE__, __FUNCTION__, __LINE__, false);
        $success = process_signon($cruise_type, $_SESSION['sailor']);
        if ($success) {
            $status = "ok";
            $action = "update";
        }
    } elseif ($opt == "declare")  // record return from cruise
    {
        // u_writedbg("DECLARE\n", __FILE__, __FUNCTION__, __LINE__, false);
        $success = process_declare($cruise_type, $eventid);
        if ($success) {
            $status = "ok";
        }
    }

} else {    // record invalid option selected
    $action = "";
    $status = "err";
    $msg = "invalid option or event";
}

header("Location: cruise_pg.php?event=$eventid&action=$action&status=$status&msg=$msg");
exit();


function process_signon($cruise_type, $sailor)
{
    global $db_o;
    global $date;

    // add to entry table
    $cruise_o = new CRUISE($db_o, $date);
    $status = $cruise_o->add_cruise($cruise_type, $sailor);

    if ($status == "update" OR $status == "register") {
        u_writelog("cruise: $cruise_type $date| {$_SESSION['sailor']['classname']} 
        | {$_SESSION['sailor']['sailnum']} | {$_SESSION['sailor']['helmname']}  
        | {$_SESSION['sailor']['crewname']} | $status", "");
        $success = $status;
    } else {
        u_writelog("cruise: $cruise_type $date | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} | registration failed [reason: $status]", "");
        $success = false;
    }

    return $success;
}


function process_declare($cruise_type, $eventid)
{
    global $db_o;
    global $date;

    // update entry array
    $_SESSION['entries'][$eventid]['declare'] =  "declare";

    // add record to entry table to record declaration - replacing original record
    $entry_o = new CRUISE($db_o, $date);
    $status = $entry_o->end_cruise($_SESSION['sailor']['id'], $cruise_type);
    if ($status) {
        // create log record
        u_writelog("cruise: $cruise_type $date | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} | return declared", "");
        $success = "declare";
    } else {
        // create log record of failure
        u_writelog("cruise: $cruise_type $date | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} | return declaration FAILED", "");
        $success = false;
    }

    return $success;
}
