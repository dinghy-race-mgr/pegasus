<?php
/* ---------------------------------------------------------------------------------------
    rm_utils_cfg.php

    SESSION configuration setup for rm_utils scripts

    --------------------------------------------------------------------------------------
*/

$_SESSION['app_ini'] = "";              // no application specific ini settings file
$_SESSION['app_db'] = false;            // no application specific database sored parameters

$_SESSION['sql_debug'] = false;         // set true to turn on debugging of sql commands - otherwise false

$_SESSION['background'] = "";           // display has white background
$_SESSION['syslog'] = "import_".date("Y-m-d").".log";    // log

