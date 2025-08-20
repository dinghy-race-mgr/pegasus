<?php
/* ---------------------------------------------------------------------------------------
    racebox_cfg.php
    
    Standard system configuration file for racebox application
    
    --------------------------------------------------------------------------------------
*/

$_SESSION['app_name'] = "racebox";
$_SESSION['app_ini']  = "racemanager.ini";

$_SESSION['syslog'] = "../logs/sys/sys_".date("Y-m-d").".log";                                 // sys log file
$_SESSION['dbglog'] = "../logs/dbg/" . $_SESSION['app_name'] . "_" . date("Y-m-d") . ".log";   // debug log

$_SESSION['race_states']    = array("scheduled", "selected", "running", "sailed", "completed", "cancelled", "abandoned");


// FIXME - some of these values are also set in timer_pg   [also mode, view not initialisde]
$_SESSION['timer_options'] = array( 
      "listorder"         => "class",                                     // other options "pn|position|ptime""     THIS IS NOT USED THIS WAY
      "laptime"           => "button",                                    // other options "row"                    NOT CURRENTLY USED
      "quicktime"         => "class",                                     // other options "sailnum|ajax|tree"      NOT CURRENTLY USED
      "bunch"             => "class",                                     // other options "sailnum|ajax|tree"      NOT CURRENTLY USED
      "growl_racelength"  => "on",                                        // other option "off"                     NOT CURRENTLY USED
      "growl_undo"        => "on",                                        // other option "off"                     NOT CURRENTLY USED
      "growl_finish"      => "off",                                        // other option "off"                    NOT CURRENTLY USED
   );


$_SESSION['sql_debug']    =
     false;                                        // set to true to turn on debugging of sql commands - otherwise false