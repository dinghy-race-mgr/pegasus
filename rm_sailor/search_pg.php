<?php
/**
 * boatsearch_pg
 * 
 * @abstract  Form to allow user to enter search string for competitor search.
 *            Passes control to boatsearch_sc.  Will try to interpret search
 *            string as sailnumber, class name, or surname of helm.
 * 
 * @author Mark Elkington <racemanager@gmail.com>
 * 
 * %%copyright%%
 * %%license%%
 *   
 */
$loc        = "..";       
$page       = "search";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"search_pg",false);   // starts session and sets error reporting

$tmpl_o = new TEMPLATE(array( "../templates/sailor/layouts_tm.php", "../templates/sailor/search_tm.php",
                              "../templates/sailor/cruise_tm.php"));

// check we are still on the same day as the application started - if not restart
if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }
if (date('Ymd', $_SESSION['app_start']) !== date('Ymd')) {
    $i++;
    header("Location: rm_sailor.php?mode={$_SESSION['mode']}&demo={$_SESSION['demo']}&usage={$_SESSION['usage']}&debug={$_SESSION['debug']}");
}

// clear stored details
unset($_SESSION['entry']);
unset($_SESSION['races']);
unset($_SESSION['competitors']);

// get events for today - or from list passed as arguments
$db_o = new DB();
$event_o = new EVENT($db_o);

if ($_SESSION['mode'] == 'race') {
    $_SESSION['events'] = get_event_details($_SESSION['sailor_event_window'], $_SESSION['event_passed']);
} else {
    $_SESSION['events'] = get_cruise_details($_SESSION['sailor_cruiser_eventtypes'], true);
}

// get tide details
require_once("{$loc}/common/classes/tide_class.php");
$tide_o = new TIDE($db_o);
$_SESSION['tide'] = $tide_o->get_tide_by_date(date("Y-m-d"));

// pick relevant event display
if ($_SESSION['events']['numevents'] == 0) {
    $events_bufr = $tmpl_o->get_template("no_events", array(), $_SESSION['events']);

} elseif ($_SESSION['events']['numevents'] > 0) {
    if ($_SESSION['mode'] == 'race') {
        $events_bufr = $tmpl_o->get_template("list_events", array(), $_SESSION['events']);
    } else {
        $events_bufr = $tmpl_o->get_template("list_tide", array(), $_SESSION['tide']);
    }

} else {
    $events_bufr = $tmpl_o->get_template("error_msg",
        array("error" => "Race Configuration Error",
            "detail" => "The system configuration for today's race(s) is invalid",
            "action" => "Please contact the race Officer to enter the race",
            "url" => "index.php"), array("restart"=>true)
    );
}

//prepare and render page
$_SESSION['pagefields']['header-center'] = $_SESSION['option_cfg'][$page]['pagename'];
$_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(),
    array("page" => $page, "options" => set_page_options($page)));
$_SESSION['pagefields']['body'] = $tmpl_o->get_template("boatsearch_fm",
    array("events_bufr"=>$events_bufr), array("add_btn"=>$_SESSION['option_cfg']['addboat']['active']));

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'] );
exit();

