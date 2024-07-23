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
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass']);

// get event details
$event = $db_o->run("SELECT * FROM e_event WHERE id = ?", array($eid) )->fetch();

if ($pagestate == "newentry")
{
    $action = "newentry";

    // set up entry array
    $entry = array(
        "eid"          => $eid,
        "b-class"      => get_class_name($_REQUEST['class']),
        "b-sailno"     => $_REQUEST['sailnumber'],
        "b-name"       => $_REQUEST['boatname'],
        "b-division"   => get_category($_REQUEST['category']),
        "b-pn"         => get_pn ($event['scoring-type'],$event['handicap-type'], $_REQUEST['class']),
        "h-name"       => get_name($_REQUEST['helm-name']),
        "h-club"       => get_club($_REQUEST['club'], $cfg['club_std']),
        "h-age"        => $_REQUEST['helm-age'],
        "h-gender"     => "notreported",
        "h-email"      => $_REQUEST['helm-email'],
        "h-phone"      => get_phone($_REQUEST['ph-mobile']),
        "h-emergency"  => get_phone($_REQUEST['ph-emer']),
        "c-name"       => get_name($_REQUEST['crew-name']),
        "c-age"        => $_REQUEST['crew-age'],
        "c-gender"     => "notreported",
        "e-racemanager"=> check_competitor_exists($_REQUEST['class'], $_REQUEST['sailnumber'], $_REQUEST['helm-name']),
        "updby"        => "online entry"
    );

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
    $junior_chk = check_junior_consent ( $entry['h-age'], $entry['c-age']);

    // FIXME check for duplicates

    // insert record
    $insertid = $db_o->insert("e_entry", $entry );
    $insertid ? $status = "success": $status = "fail";

    // return to display
    header("Location: rm_event.php?page=entries&eid=$eid&action=$action&status=$status&recordid=$insertid&junior=$junior_chk&waiting=$waiting_chk");
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
    $_REQUEST['consent'] == "on" ? $consent = 1 : $consent = 0;
    $dob = date ("Y-m-d", strtotime($_REQUEST['c-dob']));
    $consent = array
    (
        "eid" => $eid,
        "entryid"        => $_REQUEST['entryid'],
        "parent_name"    => $_REQUEST['p-name'],
        "parent_phone"   => $_REQUEST['p-mobile'],
        "parent_email"   => $_REQUEST['p-email'],
        "parent_address" => $_REQUEST['p-address'],
        "child_name"     => $_REQUEST['c-name'],
        "child_dob"      => $dob,
        "medical"        => $_REQUEST['c-details'],
        "imagerights"    => 0,
        "consent"        => $consent,
        "updby"          => "rm_event_form",
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



