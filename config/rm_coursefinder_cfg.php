<?php
/* ---------------------------------------------------------------------------------------
    rm_sailor_cfg.php
    
    SESSION configuration setup for rm_sailor application
    
    --------------------------------------------------------------------------------------
*/

$_SESSION['app_ini']  = "racemanager.ini";               // name of ini file for this app
$_SESSION['app_name'] = "coursefinder";                  // name of application

$_SESSION['syslog'] = "../logs/sys/sys_".date("Y-m-d").".log";                                 // sys log file
$_SESSION['dbglog'] = "../logs/dbg/" . $_SESSION['app_name'] . "_" . date("Y-m-d") . ".log";   // debug log

$_SESSION['background'] = "";            // page background colour
$_SESSION['sql_debug']  = false;         // set to true to turn on debugging of sql commands - otherwise false

$_SESSION['check_event'] = true;         // checks for event
$_SESSION['check_eventformat'] = true;   // takes race format into account when presenting course
$_SESSION['check_tide']  = false;        // checks tide condition when assessing best course
$_SESSION['wind_info']   = "https://www.starcrossyc.org.uk/essentials/weather-at-syc";   // link to wind information for club
