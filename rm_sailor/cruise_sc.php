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
// start session
session_id('sess-rmsailor');
session_start();

// initialise page
u_initpagestart(0,$page,false);   // starts session and sets error reporting

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
$opt = strtolower(u_checkarg("opt", "set", ""));
$eventid = strtolower(u_checkarg("eventid", "set", ""));
$cruise_type = strtolower(u_checkarg("cruise_type", "set", ""));

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
        $success = process_cruise_signon($cruise_type, $_SESSION['sailor']);
        if ($success) {
            $status = "ok";
            $action = "update";
        }
    } elseif ($opt == "declare")  // record return from cruise
    {
        // u_writedbg("DECLARE\n", __FILE__, __FUNCTION__, __LINE__, false);
        $success = process_cruise_declare($cruise_type, $eventid);
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


