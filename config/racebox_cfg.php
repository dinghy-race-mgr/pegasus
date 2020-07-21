<?php
/* ---------------------------------------------------------------------------------------
    racebox_cfg.php
    
    Standard system configuration file for racebox application
    
    --------------------------------------------------------------------------------------
*/

$_SESSION['app_name']      = "racebox";

$_SESSION['syslog'] = "racebox_".date("Y-m-d").".log";                   // sys log file

$_SESSION['race_states']    = array("scheduled", "selected", "running", "sailed", "completed", "cancelled", "abandoned");

$_SESSION['timer_options'] = array( 
      "listorder"         => "class",                                     // other options "pn|position|ptime""
      "laptime"           => "button",                                    // other options "row"
      "quicktime"         => "class",                                     // other options "sailnum|ajax|tree"
      "bunch"             => "class",                                     // other options "sailnum|ajax|tree" 
      "growl_racelength"  => "on",                                        // other option "off"   
      "growl_undo"        => "on",                                        // other option "off"
      "growl_finish"      => "on",                                        // other option "off"
   );

$_SESSION['sql_debug']    = false;                                        // set to true to turn on debugging of sql commands - otherwise false