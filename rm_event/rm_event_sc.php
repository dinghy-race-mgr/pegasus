<?php
/* rm_event_sc.php

    Processing script for rm_event form processing etc.
*/

// start session
session_id('sess-rmevent');
session_start();

// error_reporting(E_ERROR);  // FIXME turn off warnings for live operation

require_once("include/rm_event_lib.php");
require_once("classes/db.php");

// initialise application
$cfg = set_config("config.ini", array("rm_event"), true);   // FIXME location of ini file
$cfg['logfile'] = str_replace("<date>", date("Y"), $cfg['logfile']);


//echo "<pre>".print_r($_REQUEST,true)."</pre>";
//exit();

// get required arguments
$eid       = $_REQUEST['eid'];
$pagestate = $_REQUEST['pagestate'];
$mode      = $_REQUEST['mode'];
if (empty($eid) or empty($pagestate))
{
    echo "ERROR: eventid [{$_REQUEST['eid']}], pagestate [{$_REQUEST['pagestate']}] or mode [{$_REQUEST['mode']}] not set";
    exit();
    // FIXME exit_nicely
}

// set database
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// get event details
$event = $db_o->run("SELECT * FROM e_event WHERE id = ?", array($eid) )->fetch();

if ($pagestate == "newentry")
{
    $action = "newentry";

    $entry = array("eid" => $eid, "b-class" => get_class_name($_REQUEST['class']), "updby" => "online entry");

    empty($_REQUEST['sailnumber']) ? $entry['b-sailno'] = "MISSING" : $entry['b-sailno'] = $_REQUEST['sailnumber'];
    if(!empty($_REQUEST['bownumber']))  { $entry['b-altno'] = $_REQUEST['bownumber']; }
    if(!empty($_REQUEST['boatname']))   { $entry['b-name'] = $_REQUEST['boatname']; }
    if(!empty($_REQUEST['category']))   { $entry['b-division'] = get_category($_REQUEST['category']); }

    empty($_REQUEST['helm-name'])  ? $entry['h-name'] = "MISSING" : $entry['h-name'] = get_name($_REQUEST['helm-name']);
    if(!empty($_REQUEST['club']))       { $entry['h-club'] = get_club($_REQUEST['club'], $cfg['club_std']); }
    if(!empty($_REQUEST['helm-age']))   { $entry['h-age'] = $_REQUEST['helm-age']; }
    if(!empty($_REQUEST['h-gender']))   { $entry['h-gender'] = $_REQUEST['h-gender']; }

    if(!empty($_REQUEST['helm-email'])) { $entry['h-email'] = $_REQUEST['helm-email']; }
    if(!empty($_REQUEST['ph-mobile']))  { $entry['h-phone'] = get_phone($_REQUEST['ph-mobile']); }
    if(!empty($_REQUEST['ph-emer']))    { $entry['h-emergency'] = get_phone($_REQUEST['ph-emer']); }

    if(!empty($_REQUEST['crew-name']))  { $entry['c-name'] = get_name($_REQUEST['crew-name']); }
    if(!empty($_REQUEST['crew-age']))   { $entry['c-age'] = $_REQUEST['crew-age']; }
    if(!empty($_REQUEST['c-gender']))   { $entry['c-gender'] = $_REQUEST['c-gender']; }


//    empty($_REQUEST['sailnumber']) ? $entry['b-sailno'] = "MISSING" : $entry['b-sailno'] = $_REQUEST['sailnumber'];
//    empty($_REQUEST['bownumber'])  ? $entry['b-altno'] = "" : $entry['b-altno'] = $_REQUEST['bownumber'];
//    empty($_REQUEST['boatname'])   ? $entry['b-name'] = "" : $entry['b-name'] = $_REQUEST['boatname'];
//    empty($_REQUEST['category'])   ? $entry['b-division'] = "" : $entry['b-division'] = get_category($_REQUEST['category']);
//
//    empty($_REQUEST['helm-name'])  ? $entry['h-name'] = "MISSING" : $entry['h-name'] = $_REQUEST['helm-name'];
//    empty($_REQUEST['club'])       ? $entry['h-club'] = "" : $entry['h-club'] = $_REQUEST['club'];
//    empty($_REQUEST['helm-age'])   ? $entry['h-age'] = "" : $entry['h-age'] = $_REQUEST['helm-age'];
//    empty($_REQUEST['h-gender'])   ? $entry['h-gender'] = "" : $entry['h-gender'] = $_REQUEST['h-gender'];
//
//    empty($_REQUEST['helm-email']) ? $entry['h-email'] = "" : $entry['h-email'] = $_REQUEST['helm-email'];
//    empty($_REQUEST['ph-mobile'])  ? $entry['h-phone'] = "" : $entry['h-phone'] = $_REQUEST['ph-mobile'];
//    empty($_REQUEST['ph-emer'])    ? $entry['h-emergency'] = "" : $entry['h-emergency'] = $_REQUEST['ph-emer'];
//
//    empty($_REQUEST['crew-name'])  ? $entry['c-name'] = "" : $entry['c-name'] = $_REQUEST['crew-name'];
//    empty($_REQUEST['crew-age'])   ? $entry['c-age'] = "" : $entry['c-age'] = $_REQUEST['crew-age'];
//    empty($_REQUEST['c-gender'])   ? $entry['c-gender'] = "" : $entry['c-gender'] = $_REQUEST['c-gender'];

    // set up entry array
//    $entry = array(
//        "eid"          => $eid,
//        "b-class"      => get_class_name($_REQUEST['class']),
//        "b-sailno"     => $_REQUEST['sailnumber'],
//        "b-altno"      => $_REQUEST['bownumber'],
//        "b-name"       => $_REQUEST['boatname'],
//        "b-division"   => get_category($_REQUEST['category']),
//        "b-pn"         => get_pn ($event['scoring-type'],$event['handicap-type'], $_REQUEST['class']),
//       "h-name"       => get_name($_REQUEST['helm-name']),
//        "h-club"       => get_club($_REQUEST['club'], $cfg['club_std']),
//        "h-age"        => $_REQUEST['helm-age'],
//        "h-gender"     => $_REQUEST['h-gender'],
//        "h-email"      => $_REQUEST['helm-email'],
//        "h-phone"      => get_phone($_REQUEST['ph-mobile']),
//        "h-emergency"  => get_phone($_REQUEST['ph-emer']),
//        "c-name"       => get_name($_REQUEST['crew-name']),
//        "c-age"        => $_REQUEST['crew-age'],
//        "c-gender"     => "",
//        "e-racemanager"=> check_competitor_exists($_REQUEST['class'], $_REQUEST['sailnumber'], $_REQUEST['helm-name']),
//        "updby"        => "online entry"
//    );

    // check if boat is known to raceManager
    $entry['e-racemanager'] = check_competitor_exists($_REQUEST['class'], $_REQUEST['sailnumber'], $_REQUEST['helm-name']);

    // get boat handicap from t_class
    $entry['b-pn'] = get_pn ($event['scoring-type'],$event['handicap-type'], $_REQUEST['class']);

    // if personal handicap racing get pn from t_competitor [0 means not required or not found]
    $entry['b-personalpn'] = get_personal_pn ($entry['e-racemanager'], $event['handicap-type']);

    // set guid for future updates
    $mode == "add" ? $entry['e-guid'] = get_guid() : $entry['e-guid'] = "";

    // get entry sequence no.
    $max_id = $db_o->run("SELECT MAX(`e-entryno`) FROM e_entry WHERE `eid` = ?", array($eid) )->fetchColumn();
    $entry['e-entryno'] = $max_id + 1;

    // determine if entry will be on waiting list
    $waiting_chk = check_waiting_list ( $event['entry-limit'], $eid);
    $waiting_chk ? $entry['e-waiting'] = 1 : $entry['e-waiting'] = 0;

    // determine if a junior consent form is required
    $junior_chk = check_junior_consent( $entry['h-age'], $entry['c-age']);

    // FIXME check for duplicates

    // insert record
    $insertid = $db_o->insert("e_entry", $entry );
    // $insertid = $db_o->insert2("e_entry", $entry );
    $insertid ? $status = "success": $status = "fail";

    // go directly to parental consent form if a junior event
    if ($junior_chk and $_REQUEST['formname'] == "junior_class_open_fm.php")    // go directly to parental consent form
    {
        header("Location: rm_event.php?page=juniorconsentform&eid=$eid&recordid=$insertid");
    }
    else                // return to display page
    {
        header("Location: rm_event.php?page=entries&eid=$eid&action=$action&status=$status&recordid=$insertid&junior=$junior_chk&waiting=$waiting_chk");
    }

    // return to display
    // header("Location: rm_event.php?page=entries&eid=$eid&action=$action&status=$status&recordid=$insertid&junior=$junior_chk&waiting=$waiting_chk");
    exit();
}
elseif ($pagestate == "updentry")   // FIXME need to do the update variations
{
    // FIXME - dummy settings
    $action      = "updentry";
    $status      = "success";
    $insertid    = "9";
    $waiting_chk = 1;
    $junior_chk  = 0;
    // return to display
    header("Location: rm_event.php?page=entries&eid=$eid&action=$action&status=$status&recordid=$insertid&junior=$junior_chk&waiting=$waiting_chk");
    exit();
}
elseif ($pagestate == "juniorconsent")
{
//    echo "<pre>".print_r($_REQUEST,true)."</pre>";
//    exit("stopping");


    $_REQUEST['c-treatment'] == "on" ? $treatment = "YES" : $treatment = "NO";
    $_REQUEST['c-confident'] == "on" ? $confident = "YES" : $confident = "NO";

    $consent = array
    (
        "eid" => $eid,
        "entryid"            => $_REQUEST['entryid'],
        "parent_name"        => $_REQUEST['p-name'],
        "parent_phone"       => $_REQUEST['p-mobile'],
        "parent_email"       => $_REQUEST['p-email'],
        "parent_address"     => $_REQUEST['p-address'],
        "alt_contact_detail" => $_REQUEST['p-altcontact'],
        "child_name"         => $_REQUEST['c-name'],
        //"child_dob"          => $_REQUEST['c-dob'],
        "child_dob"          => date ("Y-m-d", strtotime($_REQUEST['c-dob'])),
        "medical"            => $_REQUEST['c-medical'],
        "dietary"            => $_REQUEST['c-dietary'],
        "confirm-treatment"  => $treatment,
        "confirm-media"      => $_REQUEST['c-media'],
        "confirm-confident"  => $confident,
        "updby"               => "rm_event_form"
    );

    // insert record
    $insertid = $db_o->insert("e_consent", $consent );
    $insertid ? $status = "success": $status = "fail";

    // return to display
    header("Location: rm_event.php?page=entries&eid=$eid&action=newconsent&status=$status&recordid=$insertid");
    exit();
}
else
{
    echo "ERROR: pagestate in rm_event_sc.php not recognised [$pagestate]"; exit(); // FIXME exit nicely
}


