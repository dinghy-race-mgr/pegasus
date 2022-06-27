<?php
$ini = parse_ini_file("../../config/common.ini");
foreach ($ini as $key => $value)
{
    $_SESSION["$key"] = $value;
}

$_SESSION['app_name'] = "admin";                                                                  // name of application
$_SESSION['syslog'] = "../../logs/sys/sys_".date("Y-m-d").".log";                                 // sys log file
$_SESSION['dbglog'] = "../../logs/dbg/" . $_SESSION['app_name'] . "_" . date("Y-m-d") . ".log";   // debug log
$_SESSION['sql_debug'] = false;                                                                   // true to debug sql commands

$_SESSION['daylight_saving']= array(
    "start_ref"   => "YYYY-04-01",
    "start_delta" => "last sunday",
    "end_ref"     => "YYYY-11-01",
    "end_delta"   => "last sunday",
    "time_diff"   => "1"
);

