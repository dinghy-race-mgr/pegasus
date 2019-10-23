<?php
$_SESSION['app_name'] = "admin";                        // name of application

$_SESSION['sql_debug'] = false;               // set to true to turn on debugging of sql commands - otherwise false

$_SESSION['daylight_saving']= array(
    "start_ref"   => "YYYY-04-01",
    "start_delta" => "last sunday",
    "end_ref"     => "YYYY-11-01",
    "end_delta"   => "last sunday",
    "time_diff"   => "1"
);
