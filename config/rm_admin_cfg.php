<?php
//$ini = parse_ini_file("../../config/common.ini");
//foreach ($ini as $key => $value)
//{
//    $_SESSION["$key"] = $value;
//}
$_SESSION['app_name'] = "admin";                                                               // name of application
$_SESSION['syslog'] = "../logs/sys/sys_".date("Y-m-d").".log";                                 // sys log file
$_SESSION['dbglog'] = "../logs/dbg/" . $_SESSION['app_name'] . "_" . date("Y-m-d") . ".log";   // debug log
$_SESSION['sql_debug'] = false;                                                                // true to debug sql commands

$_SESSION['daylight_saving']= array(
    "start_ref"   => "YYYY-04-01",
    "start_delta" => "last sunday",
    "end_ref"     => "YYYY-11-01",
    "end_delta"   => "last sunday",
    "time_diff"   => "1"
);

$_SESSION['db_host'] = "127.0.0.1";                     // database settings necessary to support phprunner
$_SESSION['db_user'] = "rmuser";                        // these settings MUST be the same as in common.ini
$_SESSION['db_pass'] = "pegasus";
$_SESSION['db_port'] = "";
$_SESSION['db_name'] = "pegasus";

$_SESSION['result_url'] = "http://localhost/pegasus/results";         // url to results folder on website   FIXME - this needs to be handled better as already in ini file
$_SESSION['result_path'] = "C:/xampp/htdocs/pegasus/results";         // url to results folder on website   FIXME - this needs to be handled better as already in ini file
$_SESSION['result_target_url'] = "http://localhost/results_archive";  // url to results folder on website   FIXME - this needs to be handled better as already in ini file
$_SESSION['result_target_path'] = "C:/xampp/htdocs/results_archive";  // url to results folder on website   FIXME - this needs to be handled better as already in ini file


