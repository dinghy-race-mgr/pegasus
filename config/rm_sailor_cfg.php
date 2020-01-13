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
