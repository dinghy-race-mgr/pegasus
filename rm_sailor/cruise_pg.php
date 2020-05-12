<?php
/**
 * cruise_pg.php
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

u_initpagestart(0,"cruise_pg",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/cruise_class.php");
require_once ("{$loc}/common/classes/event_class.php");

// check arguments
$external = check_argument("state", "setbool", "init", true);
$action = array(
    "event" => check_argument("event", "set", "", 0),
    "type" => check_argument("action", "set", ""),
    "status" => check_argument("status", "set", ""),
    "msg" => check_argument("msg", "set", "")
);

//$sleep = false;
//if (!empty($_REQUEST['state'])) { if ($_REQUEST['state'] == "init") { $sleep = true; } }
//
//$action = array();             // message from action processing
//empty($_REQUEST['event'])  ? $action['event'] = 0   : $action['event'] = $_REQUEST['event'];
//empty($_REQUEST['action']) ? $action['type'] = ""   : $action['type'] = $_REQUEST['action'];
//empty($_REQUEST['status']) ? $action['status'] = "" : $action['status'] = $_REQUEST['status'];
//empty($_REQUEST['msg'])    ? $action['msg'] = ""    : $action['msg'] = $_REQUEST['msg'];

// connect to database to get event information
$db_o = new DB();
$event_o = new EVENT($db_o);
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/cruise_tm.php"));

// update event details
$_SESSION['events'] = get_cruise_details($_SESSION['sailor_cruiser_eventtypes'], true);

// update information on entries
$_SESSION['entries'] = get_entry_information($_SESSION['sailor']['id'], $_SESSION['events']['details']);

// set up boat information
$boat_fields = set_boat_details();
$boat_fields["boat-label"] = $tmpl_o->get_template("boat_label", $boat_fields,
             array("change"=>true, "change_set"=>$_SESSION['sailor']['change']));

// create content events list (or error message if no events)
if ($_SESSION['events']['numevents'] > 0)
{
//    u_writedbg("ACTION: ".print_r($action, true), __FILE__, __FUNCTION__, __LINE__, false);
//    u_writedbg("EVENTS".print_r($_SESSION['events']['details'], true), __FILE__, __FUNCTION__, __LINE__, false);
//    u_writedbg("ENTRIES".print_r($_SESSION['entries'], true), __FILE__, __FUNCTION__, __LINE__, false);

    $signon_entry_list = set_cruise_status_list($_SESSION['events']['details'], $_SESSION['entries'], $action);

//    u_writedbg("ENTRY_LIST".print_r($signon_entry_list, true), __FILE__, __FUNCTION__, __LINE__, false);

    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("cruise_control", $boat_fields,
        array('state'=>"submitentry", 'event-list'=>$signon_entry_list, "declare_opt" => $_SESSION['sailor_cruiser_declare']));

    // add automated timed return to search page if usage and delay are configured
    $_SESSION['pagefields']['body'].= add_auto_continue($_SESSION['usage'], $_SESSION['sailor_cruise_sleep_delay'],
        $external, "boatsearch_pg.php");
}
else
{
    $error_fields = array(
        "error"  => "Fatal Error: invalid state for cruise page",
        "detail" => "No cruising events defined for today",
        "action" => "Please report error to your raceManager administrator",
    );
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
}

// assemble and render page
$_SESSION['pagefields']['header-center'] = $_SESSION['pagename']['cruise'];
$_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(), array("options" => set_page_options("cruise")));

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();

// go back to search page after time interval
//$_SESSION['pagefields']['body'].= str_repeat("\n",4096);
//$delay = 0;
//if ($sleep AND $_SESSION['usage'] = "multi")  // not arriving from cruise function (pickboat)
//{
//    $delay = $_SESSION['sailor_cruise_sleep_delay'] * 2000;
//}
//elseif ($_SESSION['usage'] = "multi")   // arriving after using function on cruise page
//{
//    $delay = $_SESSION['sailor_cruise_sleep_delay'] * 1000;
//}
//if ($delay > 0)
//{
//    $_SESSION['pagefields']['body'].= <<<EOT
//    <script>
//        $(document).ready(function () {
//        // Handler for .ready() called.
//        window.setTimeout(function () {
//            location.replace('boatsearch_pg.php');
//        }, $delay);
//    });
//    </script>
//EOT;
//}



