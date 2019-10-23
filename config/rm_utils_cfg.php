<?php
/* ---------------------------------------------------------------------------------------
    rm_utils_cfg.php

    SESSION configuration setup for rm_utils scripts

    --------------------------------------------------------------------------------------
*/
$_SESSION['app_name'] = "utils";                        // name of application

$_SESSION['app_db'] = false;            // no application specific database stored parameters

$_SESSION['sql_debug'] = false;         // set true to turn on debugging of sql commands - otherwise false

$_SESSION['background'] = "";           // display has white background

$_SESSION['syslog'] = "import_".date("Y-m-d").".log";    // log FIXME - not correct for all utils

$_SESSION['daylight_saving']= array(
    "start_ref"   => "YYYY-04-01",
    "start_delta" => "last sunday",
    "end_ref"     => "YYYY-11-01",
    "end_delta"   => "last sunday",
    "time_diff"   => "1"
);

$_SESSION['webcollect']= array(
    "access_token"            => "986T9N5ZDSQFDBWAR4DCKOKEZS35YDFEP49KBBWMKJKXMKEHBPZOWUFC6HPZG6CS",
    "organisation_short_name" => "STARCROSSYC",
    "firstname_fld"           => "firstname",
    "familyname_fld"          => "lastname",
    "phone_fld"               => "mobile_phone",
    "email_fld"               => "email",
    "rota_fld"                => "Allocated_Duties_Club_use_only",
    "duty_restriction_fld"    => "Duty_Restrictions_Club_use_only",
    "duty_availability_fld"   => "Duty_Non_Availability_Club_use_only",
    "rota_code_map"           => array(
        "ood cruising"       => "ood_c",
        "safety boat driver" => "safety_d",
        "safety boat crew"   => "safety_c",
        "ood racing"         => "ood_p",
        "aood"               => "ood_a",
        "galley"             => "galley",
        "bar"                => "bar"
    )
);

$_SESSION['rotamap'] = array(
    "ood_p"    => "race_duty",
    "ood_a"    => "race_duty",
    "ood_c"    => "safety_duty",
    "safety_d" => "safety_duty",
    "safety_c" => "safety_duty",
    "galley"   => "club_duty",
    "bar"      => "club_duty",
);