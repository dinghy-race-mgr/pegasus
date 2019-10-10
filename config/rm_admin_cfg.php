<?php
$_SESSION['app_name'] = "admin";                        // name of application

$_SESSION['db_host'] = "127.0.0.1";
$_SESSION['db_user'] = "root";
$_SESSION['db_pass'] = "";
$_SESSION['db_port'] = "";
$_SESSION['db_name'] = "pegasus";

$_SESSION['sql_debug'] = false;               // set to true to turn on debugging of sql commands - otherwise false

$_SESSION['daylight_saving']= array(
    "start_ref"   => "YYYY-04-01",
    "start_delta" => "last sunday",
    "end_ref"     => "YYYY-11-01",
    "end_delta"   => "last sunday",
    "time_diff"   => "1"
);

//$_SESSION['webcollect']= array(
//    "access_token"            => "986T9N5ZDSQFDBWAR4DCKOKEZS35YDFEP49KBBWMKJKXMKEHBPZOWUFC6HPZG6CS",
//    "organisation_short_name" => "STARCROSSYC",
//    "firstname_fld"           => "firstname",
//    "familyname_fld"          => "lastname",
//    "phone_fld"               => "phone",
//    "email_fld"               => "email",
//    "rota_fld"                => "Allocated_Duties_Club_use_only",
//    "duty_restriction_fld"    => "Duty_Restrictions_Club_use_only",
//    "duty_availability_fld"   => "Duty_Non_Availability_Club_use_only",
//    "rota_code_map"           => array(
//        "ood cruising"       => "ood_c",
//        "safety boat driver" => "safety_d",
//        "safety boat crew"   => "safety_c",
//        "ood racing"         => "ood_p",
//        "aood"               => "ood_a",
//        "galley"             => "galley",
//        "bar"                => "bar"
//    )
//);
