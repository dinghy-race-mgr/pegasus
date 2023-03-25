<?php

// get common system settings
$ini = parse_ini_file("../../config/common.ini");
foreach ($ini as $key => $value) { $_SESSION["$key"] = $value; }

// get app settings
$adminini = parse_ini_file("../../config/rm_admin.ini");
foreach ($adminini as $key => $value) { $_SESSION["$key"] = $value; }

$_SESSION['syslog']    = "../../logs/sys/sys_".date("Y-m-d").".log";                                 // sys log file
$_SESSION['dbglog']    = "../../logs/dbg/" . $_SESSION['app_name'] . "_" . date("Y-m-d") . ".log";   // debug log
$_SESSION['sql_debug'] = false;                                                                      // true to debug sql commands

$_SESSION['daylight_saving']= array(                                                                 // is this actually used anywhere?
    "start_ref"   => "YYYY-04-01",
    "start_delta" => "last sunday",
    "end_ref"     => "YYYY-11-01",
    "end_delta"   => "last sunday",
    "time_diff"   => "1"
);


