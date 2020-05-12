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

$_SESSION['sailor_race_sleep_delay'] = 4;
$_SESSION['sailor_cruise_sleep_delay'] = 0;

//change fields
$_SESSION['change_fm'] = array();
if ($_SESSION['mode'] == "race")
{
    $_SESSION['change_fm'] = array (
        "chg-helm"  => array("status" => false, "label" => "Helm", "width" => "col-xs-6"),
        "chg-crew"  => array("status" => true, "label" => "Crew", "width" => "col-xs-6"),
        "chg-sailnum" => array("status" => true, "label" => "Sail No.", "width" => "col-xs-3"),
    );

    $_SESSION['pagename'] = array(
        "search"  => "Search Boat",
        "pick"    => "Pick Boat",
        "control" => "Race Recording",
        "change"  => "Change Boat Details",
        "add"     => "Add New Boat",
        "edit"    => "Edit Boat Details",
    );
}
else
{
    $_SESSION['change_fm'] = array (
        "chg-helm"    => array("status" => true, "width" => "col-xs-6", "label" => "Helm name"),
        "chg-crew"    => array("status" => true, "width" => "col-xs-6", "label" => "Crew name(s)"),
        "chg-sailnum" => array("status" => true, "width" => "col-xs-3", "label" => "Sail No."),
        "chg-numcrew"=> array("status" => true, "width" => "col-xs-3", "label" => "Total crew",
                           "placeholder" => "number of people in boat...", "evtype"=> "freesail"),
        "chg-contact" => array("status" => true, "width" => "col-xs-6", "label" => "Contact Details",
                           "placeholder" => "contact mobile number", "evtype"=> "freesail"),
    );

    $_SESSION['pagename'] = array(
        "search"  => "Search Boat",
        "pick"    => "Pick Boat",
        "control" => "Record Leisure Sailing",
        "change"  => "Change Boat Details",
        "add"     => "Add New Boat",
        "edit"    => "Edit Boat Details",
    );
}
