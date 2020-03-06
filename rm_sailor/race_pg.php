<?php
/**
 * race_pg.php
 *
 *   
 * 
 */
$loc        = "..";
$page       = "race_pg";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"race_pg",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/event_class.php");

// arguments
$sleep = false;
if (!empty($_REQUEST['state'])) { if ($_REQUEST['state'] == "init") { $sleep = true; } }


$action = array();             // message froom action processing
if (!empty($_REQUEST['event']))  { $action['event'] = $_REQUEST['event']; }
if (!empty($_REQUEST['action'])) { $action['type'] = $_REQUEST['action']; }
if (!empty($_REQUEST['status'])) { $action['status'] = $_REQUEST['status']; }
if (!empty($_REQUEST['msg']))    { $action['msg'] = $_REQUEST['msg']; }

// connect to database to get event information
$db_o = new DB();
$event_o = new EVENT($db_o);
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/race_tm.php"));

// update event details
$_SESSION['events'] = get_event_details($_SESSION['event_passed']);

// update information on entries
$_SESSION['entries'] = get_entry_information($_SESSION['sailor']['id'], $_SESSION['events']['details']);

// set up boat information
$race_fields = set_boat_details();
$race_fields["boat-label"] = $tmpl_o->get_template("boat_label", $race_fields,
             array("change"=>true, "change_set"=>$_SESSION['sailor']['change']));

// display race page
if ($_SESSION['events']['numevents'] > 0)
{
    $signon_entry_list = set_event_status_list($_SESSION['events']['details'], $_SESSION['entries'], $action);

    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("race_control", $race_fields,
        array('state'=>"submitentry", 'event-list'=>$signon_entry_list));
}

else  // no events today - nothing to deal with
{
    $event_list = $tmpl_o->get_template("no_events", array(), $_SESSION['events']);
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("race_control", $race_fields,
                                      array('state' => "noevents", 'event-list' => $event_list));
}

if (empty($_SESSION['pagefields']['body']))  // we have an error
{
    $error_fields = array(
        "error"  => "Fatal Error: invalid state for race page",
        "detail" => "",
        "action" => "Please report error to your raceManager administrator",
    );
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
}

$_SESSION['pagefields']['body'].= str_repeat("\n",4096);
if ($sleep AND $_SESSION['usage'] = "multi")  // not arriving from search function (pickboat)
{
    $delay = $_SESSION['sailor_race_sleep_delay'] * 3000;
}
elseif ($_SESSION['usage'] = "multi")   // arriving after using function on race page
{
    $delay = $_SESSION['sailor_race_sleep_delay'] * 1000;
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

