<?php
/* ---------------------------------------------------------------------------------------
    rm_sailor_cfg.php
    
    SESSION configuration setup for rm_sailor application
    
    --------------------------------------------------------------------------------------
*/

$_SESSION['app_ini'] = "racemanager.ini";                // name of ini file for this app
$_SESSION['app_name'] = "sailor";                        // name of application

$_SESSION['syslog'] = "../logs/sys/sys_".date("Y-m-d").".log";                                 // sys log file
$_SESSION['dbglog'] = "../logs/dbg/" . $_SESSION['app_name'] . "_" . date("Y-m-d") . ".log";   // debug log

$_SESSION['background']    = "bg-primary";               // page background colour
$_SESSION['sql_debug']     = false;                      // set to true to turn on debugging of sql commands - otherwise false

$_SESSION['sailor_race_sleep_delay'] = 10;               // in multi use mode will return to search page after specified seconds
$_SESSION['sailor_cruise_sleep_delay'] = 20;             // 0 does not auto-return

// options configuration
$_SESSION['option_cfg'] = array(
        "search" => array("label" => "Search Boats", "pagename" => "SEARCH FOR BOAT", "url" => "search_pg.php",
            "tip" => "", "active" => true),
        "pick" => array("label" => "Pick Boat", "pagename" => "PICK BOAT", "url" => "pickboat_pg.php",
        "tip" => "", "active" => true),
        "race" => array("label" => "Racing", "pagename" => "REGISTER FOR RACE", "url" => "race_pg.php",
            "tip" => "", "active" => true),
        "cruise" => array("label" => "Cruising", "pagename" => "REGISTER FOR CRUISE", "url" => "cruise_pg.php",
            "tip" => "", "active" => true),
        "change" => array("label" => "Change Details", "pagename" => "CHANGE DETAILS FOR TODAY", "url" => "change_pg.php",
        "tip" => "", "active" => true),
        "addboat" => array("label" => "Register New Boat", "pagename" => "REGISTER NEW BOAT", "url" => "addboat_pg.php",
            "tip" => "", "active" => true),
        "editboat" => array("label" => "Edit Boat Details", "pagename" => "CHANGE BOAT DETAILS", "url" => "editboat_pg.php",
            "tip" => "", "active" => true),
        "results" => array("label" => "Get Results", "pagename" => "RACE RESULTS", "url" => "results_pg.php",
            "tip" => "Results for this race", "active" => false),
        "protest" => array("label" => "Submit Protest", "pagename" => "SUBMIT PROTEST", "url" => "protest_pg.php",
            "tip" => "Submit a protest for this race", "active" => false),
        "hideboat" => array("label" => "Hide Boat", "pagename" => "HIDE THIS BOAT", "url" => "hideboat_sc.php", "active" => false,
            "tip" => "Click to remove this boat from future searches - it will remain in the racemanager archive"),
        "rememberme" => array("label" => "Remember Me", "pagename" => "REMEMBER THIS BOAT", "url" => "rememberme_pg.php", "active" => false,
            "tip" => "Click to set this as the boat you usually sail."),
    );

// options configuration per page
$_SESSION['options_map'] = array(
        "search"     => array("addboat"),
        "pick"       => array("search", "addboat"),
        "race"       => array("search", "addboat", "editboat", "results", "rememberme"),
        "cruise"     => array("search", "addboat", "editboat", "rememberme"),
        "addboat"    => array("search"),
        "change"     => array("search"),
        "editboat"   => array("search"),
        "results"    => array("search", "addboat", "editboat", "protest"),
        "protest"    => array("search", "addboat", "editboat", "results"),
        "rememberme" => array("search"),
);

// boat change fields
$_SESSION['change_fm'] = array();

if ($_SESSION['mode'] == "cruise")
{
    $_SESSION['change_fm'] = array (
        "chg-helm"    => array("status" => true, "width" => "col-xs-6", "label" => "Helm name"),
        "chg-crew"    => array("status" => true, "width" => "col-xs-6", "label" => "Crew name(s)"),
        "chg-sailnum" => array("status" => true, "width" => "col-xs-3", "label" => "Sail No."),
        "chg-numcrew"=> array("status" => true, "width" => "col-xs-3", "label" => "Total crew",
            "placeholder" => "number of people in boat...", "evtype"=> "individual|freesail|dcruise"),
        "chg-contact" => array("status" => true, "width" => "col-xs-6", "label" => "Contact Details",
            "placeholder" => "contact mobile number", "evtype"=> "individual|freesail|dcruise"),
    );
}
else
{
    $_SESSION['change_fm'] = array (
        "chg-helm"  => array("status" => false, "label" => "Helm", "width" => "col-xs-6"),
        "chg-crew"  => array("status" => true, "label" => "Crew", "width" => "col-xs-6"),
        "chg-sailnum" => array("status" => true, "label" => "Sail No.", "width" => "col-xs-3"),
    );

}

// plugin options
$_SESSION['plugins'] = array(
    "1" => array("configured" => true, "name" => "qfo"),    // ordering hot food
    "2" => array("configured" => false, "name" => ""),
    "3" => array("configured" => false, "name" => ""),
);
