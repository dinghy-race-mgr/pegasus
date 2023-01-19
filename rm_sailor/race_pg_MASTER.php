<?php
/**
 * race process administration page
 *
 * User can enter a race, and optionally declare or retire from a race.  Also
 * provides links to the results a protest submission page for each race.
 *
 */
$loc        = "..";
$page       = "race";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("./include/rm_sailor_lib.php");

// start session
session_id('sess-rmsailor');
session_start();

// initialise page
u_initpagestart(0,$page,false);

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/event_class.php");

// arguments
$external = u_checkarg("state", "setbool", "init", true);
$action = array(
    "event"  => u_checkarg("event", "set", "", 0),
    "type"   => u_checkarg("action", "set", ""),
    "status" => u_checkarg("status", "set", ""),
    "msg"    => u_checkarg("msg", "set", "")
);

// connect to database to get event information
$db_o = new DB();
$event_o = new EVENT($db_o);
$tmpl_o = new TEMPLATE(array("./templates/layouts_tm.php", "./templates/race_tm.php"));

// update event details
$_SESSION['events'] = get_event_details($_SESSION['sailor_event_window'], $_SESSION['event_passed']);

// update information on entries
$_SESSION['entries'] = get_entry_information($_SESSION['sailor']['id'], $_SESSION['events']['details']);

// set up boat information
$race_fields = set_boat_details();
$race_fields["boat-label"] = $tmpl_o->get_template("boat_label", $race_fields,
             array("change"=>true, "change_set"=>$_SESSION['sailor']['change'], "type" => "race"));

// display race page
if ($_SESSION['events']['numevents'] > 0)
{
    $signon_entry_list = set_event_status_list($_SESSION['events']['details'], $_SESSION['entries'], $action);

    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("race_control", $race_fields,
        array('state'=>"submitentry", 'numdays'=> $_SESSION['events']['numdays'],
              'event-list'=>$signon_entry_list, 'opt_cfg' =>$_SESSION['option_cfg'] ));
}

else
{
    $event_list = $tmpl_o->get_template("no_events", array(), $_SESSION['events']);
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("race_control", $race_fields,
        array('state' => "noevents", 'event-list' => $event_list) );
}

// assemble and render page
$_SESSION['pagefields']['header-center'] = $_SESSION['option_cfg'][$page]['pagename'];
$_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(),
    array("page" => $page, "options" => set_page_options($page)));

// add automated timed return to search page if usage and delay are configured
$_SESSION['pagefields']['body'].= add_auto_continue($_SESSION['usage'], $_SESSION['sailor_race_sleep_delay'],
    $external, "search_pg.php");

//echo "<pre>".print_r($_SESSION,true)."</pre>";
//exit();

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();



