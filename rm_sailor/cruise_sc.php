<?php
/**
 * cruise_sc.php - adds entry for dinghy cruising
 * 
 *
 * 
 */
$loc        = "..";       
$page       = "cruise_sc";
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
$_SESSION['events'] = get_cruise_details(true);
$event_status = $_SESSION['events']['details'][$_REQUEST['event']]['event_status'];

//echo "<pre>REQUEST: ".print_r($_REQUEST,true)."</pre>";
//echo "<pre>SESSION - entries: ".print_r($_SESSION['entries'],true)."</pre>";
//echo "<pre>SESSION - events: ".print_r($_SESSION['events'],true)."</pre>";

// arguments
empty($_REQUEST['opt']) ? $opt = "" : $opt = strtolower($_REQUEST['opt']);
empty($_REQUEST['eventid']) ? $eventid = "" : $eventid = strtolower($_REQUEST['eventid']);
empty($_REQUEST['cruise_type']) ? $cruise_type = "" : $cruise_type = $_REQUEST['cruise_type'];

if (in_array($opt, $valid_opt) AND $cruise_type)
{
    $action = $opt;
    $msg = "";
    if ($opt == "register")
    {
        $success = process_signon($cruise_type);
        if ($success)
        {
            $status = "ok";
            $action = "update";
        }
    }
    elseif ($opt == "declare")
    {
        $success = process_declare($cruise_type, $eventid);
        if ($success) { $status = "ok"; }
    }

    // update information on entries
    $_SESSION['entries'] = get_entry_information($_SESSION['sailor']['id'], $_SESSION['events']['details']);
}
else
{
    $action = "";
    $status = "err";
    $msg = "invalid option or event";
}

header("Location: cruise_pg.php?event=$eventid&action=$action&status=$status&msg=$msg");
exit();

function process_signon($cruise_type)
{
    global $db_o;
    global $date;

    // add to entry table
    $cruise_o = new CRUISE($db_o, $date);
    $status = $cruise_o->add_cruiser($cruise_type, $_SESSION['sailor']['id'],
        $_SESSION['sailor']['chg-helm'], $_SESSION['sailor']['chg-crew'], $_SESSION['sailor']['chg-sailnum']);

    if ($status == "update" OR $status == "register")
    {
        empty($_SESSION['sailor']['chg-helm']) ? $chg_helm = "" : $chg_helm = "*";
        empty($_SESSION['sailor']['chg-crew']) ? $chg_crew = "" : $chg_crew = "*";
        empty($_SESSION['sailor']['chg-sailnum']) ? $chg_sailnum = "" : $chg_sailnum = "*";
        u_writelog("cruise: $cruise_type $date| {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} -> $chg_sailnum | {$_SESSION['sailor']['helmname']} -> $chg_helm | {$_SESSION['sailor']['crewname']} -> $chg_crew | $status","");
        $success = $status;
    }
    else
    {
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

    // add record to entry table to record declaration
    $entry_o = new CRUISE($db_o, $date);
    $status = $entry_o->end_cruiser($_SESSION['sailor']['id'], $cruise_type);
    if ($status)
    {
        // create log record
        u_writelog("cruise: $cruise_type $date | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} | return declared","");
        $success = "declare";
    }
    else
    {
        // create log record of failure
        u_writelog("cruise: $cruise_type $date | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} | return declaration FAILED","");
        $success = false;
    }
    return $success;
}


