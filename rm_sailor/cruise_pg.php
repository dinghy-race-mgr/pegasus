<?php
/**
 * cruise_pg.php
 *
 *   
 * 
 */
$loc        = "..";
$page       = "cruise_pg";
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

// arguments
$sleep = false;
if (!empty($_REQUEST['state'])) { if ($_REQUEST['state'] == "init") { $sleep = true; } }


$action = array();             // message from action processing
empty($_REQUEST['event'])  ? $action['event'] = 0   : $action['event'] = $_REQUEST['event'];
empty($_REQUEST['action']) ? $action['type'] = ""   : $action['type'] = $_REQUEST['action'];
empty($_REQUEST['status']) ? $action['status'] = "" : $action['status'] = $_REQUEST['status'];
empty($_REQUEST['msg'])    ? $action['msg'] = ""    : $action['msg'] = $_REQUEST['msg'];

// connect to database to get event information
$db_o = new DB();
$event_o = new EVENT($db_o);
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/cruise_tm.php"));

// update event details
$_SESSION['events'] = get_cruise_details();

// update information on entries
$_SESSION['entries'] = get_entry_information($_SESSION['sailor']['id'], $_SESSION['events']['details']);

// set up boat information
$boat_fields = set_boat_details();
$boat_fields["boat-label"] = $tmpl_o->get_template("boat_label", $boat_fields,
             array("change"=>true, "change_set"=>$_SESSION['sailor']['change']));

// display events list
if ($_SESSION['events']['numevents'] > 0)
{
    $signon_entry_list = set_cruise_status_list($_SESSION['events']['details'], $_SESSION['entries'], $action);

    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("cruise_control", $boat_fields,
        array('state'=>"submitentry", 'event-list'=>$signon_entry_list, "declare_opt" => $_SESSION['sailor_cruiser_declare']));
}

else
{
    $error_fields = array(
        "error"  => "Fatal Error: invalid state for cruise page",
        "detail" => "",
        "action" => "Please report error to your raceManager administrator",
    );
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
}

// go back to search page after time interval
$_SESSION['pagefields']['body'].= str_repeat("\n",4096);
if ($sleep AND $_SESSION['usage'] = "multi")  // not arriving from search function (pickboat)
{
    $delay = $_SESSION['sailor_race_sleep_delay'] * 3000;
}
elseif ($_SESSION['usage'] = "multi")   // arriving after using function on race page
{
    $delay = $_SESSION['sailor_cruise_sleep_delay'] * 1000;
}

$_SESSION['pagefields']['body'].= <<<EOT
    <script>
        $(document).ready(function () {
        // Handler for .ready() called.
        window.setTimeout(function () {
            location.replace('boatsearch_pg.php');
        }, $delay);
    });
    </script>
EOT;

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);

