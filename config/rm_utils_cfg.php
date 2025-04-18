<?php
/* ---------------------------------------------------------------------------------------
    rm_utils_cfg.php

    SESSION configuration setup for rm_utils scripts

    --------------------------------------------------------------------------------------
*/
$_SESSION['app_name'] = "utils";        // name of application

$_SESSION['syslog'] = "../logs/sys/sys_".date("Y-m-d").".log";                                 // sys log file
$_SESSION['dbglog'] = "../logs/dbg/" . $_SESSION['app_name'] . "_" . date("Y-m-d") . ".log";   // debug log

$_SESSION['app_db'] = false;            // no application specific database stored parameters

$_SESSION['sql_debug'] = false;         // set true to turn on debugging of sql commands - otherwise false

$_SESSION['background'] = "";           // display has white background [FIXME no longer used]

$_SESSION['daylight_saving']= array(
    "start_ref"   => "YYYY-04-01",
    "start_delta" => "last sunday",
    "end_ref"     => "YYYY-11-01",
    "end_delta"   => "last sunday",
    "time_diff"   => "1"
);

$_SESSION['webcollect']= array(
    "firstname_fld"           => "firstname",
    "familyname_fld"          => "lastname",
    "phone_fld"               => "mobile_phone",
    "email_fld"               => "email",
    "rota_fld"                => "Allocated_Duties_Club_use_only",
    "duty_restriction_fld"    => "Duty_Restrictions_Club_use_only",
    "duty_availability_fld"   => "Duty_Non_Availability_Club_use_only",
    "rota_status_fld"         => "Rota_status_Club_Use_Only",
    "rota_ignore_values"      => array("resigning", "exempt", "opted out"),
    "rota_code_map"           => array(
        "ood cruising"       => "ood_c",
        "beachmaster"        => "ood_b",
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
    "ood_c"    => "race_duty",
    "ood_b"    => "race_duty",
    "safety_d" => "safety_duty",
    "safety_c" => "safety_duty",
    "galley"   => "club_duty",
    "bar"      => "club_duty",
);

$_SESSION['event_card_duties'] = "race|safety|house";

$_SESSION['eventcard_fields'] = array(
    "date" => "Date",
    "time" => "Time",
    "event" => "Event",
    "tide" => "Tide",
    "notes" => "Notes",
    "race_duty" => "OOD",
    "safety_duty" => "Safety Duties",
    "club_duty" => "Clubhouse"
);

$_SESSION['dutycheck'] = array(
    "max_duty" => 2
);

$_SESSION['publish'] = array(
     "loc" => "../data/programme",
     "file"=> "programme_date.json",
     "transfer_loc"  => "/syc_programme",
);

$_SESSION['berth'] = array(
    "loc" => "../data/berth",
    "file"=> "berth_report_date.csv",
);

$_SESSION['dutyman'] = array(
    "loc"        => "../data/dutyman",
    "event_file" => "dutyman_event_import_date.csv",     // must start with dutyman_event
    "duty_file"  => "dutyman_duty_import_date.csv",      // must start with dutyman_duty

    "tide"       => false,
    "clean"      => true
);

$_SESSION['pmaker'] = array(
    "loc" => "../data/programme",
    "export_file" => "programme_import_date.csv"
);

