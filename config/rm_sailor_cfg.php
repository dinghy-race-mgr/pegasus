<?php
/* ---------------------------------------------------------------------------------------
    rm_sailor_cfg.php
    
    SESSION configuration setup for rm_sailor application
    
    --------------------------------------------------------------------------------------
*/

$_SESSION['app_ini']       = "racemanager.ini";                                      // name of ini file for this app
$_SESSION['app_name']      = "sailor";                        // name of application

$_SESSION['background']    = "bg-primary";                    // page background colour
$_SESSION['sql_debug']     = false;                           // set to true to turn on debugging of sql commands - otherwise false

$_SESSION['syslog'] = "sailor_".date("Y-m-d").".log";         // sys log  - only used when in club mode

$_SESSION['sailor_race_sleep_delay'] = 0;                     // in multi use mode will return to search page after specified seconds
$_SESSION['sailor_cruise_sleep_delay'] = 0;                   // 0 does not auto-return

// options configuration
$_SESSION['option_cfg'] = array(
        "search" => array("label" => "Search Boats", "pagename" => "Search Boats", "url" => "search_pg.php",
            "tip" => "", "active" => true),
        "pick" => array("label" => "Pick Boat", "pagename" => "Pick Boat", "url" => "pickboat_pg.php",
        "tip" => "", "active" => true),
        "race" => array("label" => "Racing", "pagename" => "Race Registration", "url" => "race_pg.php",
            "tip" => "", "active" => true),
        "cruise" => array("label" => "Cruising", "pagename" => "Registration", "url" => "cruise_pg.php",
            "tip" => "", "active" => true),
        "change" => array("label" => "Change Details", "pagename" => "Temporary Change of Details", "url" => "change_pg.php",
        "tip" => "", "active" => true),
        "addboat" => array("label" => "Add New Boat", "pagename" => "Add New Boat", "url" => "addboat_pg.php",
            "tip" => "", "active" => false),
        "editboat" => array("label" => "Edit Boat Details", "pagename" => "Edit Boat Details", "url" => "editboat_pg.php",
            "tip" => "", "active" => false),
        "results" => array("label" => "Get Results", "pagename" => "Race Results", "url" => "results_pg.php",
            "tip" => "Results for this race", "active" => false),
        "protest" => array("label" => "Submit Protest", "pagename" => "Submit Protest", "url" => "protest_pg.php",
            "tip" => "Submit a protest for this race", "active" => false),
        "hideboat" => array("label" => "Hide Boat", "pagename" => "Hide This Boat", "url" => "hideboat_sc.php", "active" => false,
            "tip" => "Click to remove this boat from future searches - it will remain in the racemanager archive"),
        "rememberme" => array("label" => "Remember Me", "pagename" => "Remember This Boat", "url" => "rememberme_pg.php", "active" => false,
            "tip" => "Click to set this as the boat you usually sail."),
    );

// options configuration per page
$_SESSION['options_map'] = array(
        "search"     => array("addboat"),
        "pick"       => array("search", "addboat"),
        "race"       => array("search", "addboat", "editboat", "rememberme"),
        "cruise"     => array("search", "addboat", "editboat", "rememberme"),
        "addboat"    => array("search"),
        "change"     => array("search"),
        "editboat"   => array("search"),
        "results"    => array("search"),
        "protest"    => array("search"),
        "rememberme" => array("search"),
);

// boat change fields
$_SESSION['change_fm'] = array();

if ($_SESSION['mode'] == "race")
{
    $_SESSION['change_fm'] = array (
        "chg-helm"  => array("status" => false, "label" => "Helm", "width" => "col-xs-6"),
        "chg-crew"  => array("status" => true, "label" => "Crew", "width" => "col-xs-6"),
        "chg-sailnum" => array("status" => true, "label" => "Sail No.", "width" => "col-xs-3"),
    );
}
else
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
