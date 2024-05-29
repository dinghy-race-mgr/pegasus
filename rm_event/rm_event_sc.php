<?php
/* rm_event_sc.php

    Processing script for rm_event form processing etc.
*/

// start session
session_id('sess-rmevent');
session_start();
// error_reporting(E_ERROR);  // turn off warnings for live operation
require_once("include/rm_event_lib.php");
require_once("classes/db.php");

// initialise application
$cfg = parse_ini_file("config.ini", true);                                                      // FIXME location of ini file
$_SESSION['logfile'] = str_replace("<date>", date("Y"), $cfg['rm_event']['logfile']);

// debugging
echo "<pre>".print_r($_REQUEST,true)."</pre>";

// get required arguments
$eid = $_REQUEST['eid'];
$pagestate = $_REQUEST['pagestate'];
if (empty($eid) or empty($pagestate)) { echo "ERROR: eventid or pagestate not set"; exit(); }   // FIXME exit_nicely

// set database
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass']);

// get event details
$event = $db_o->run("SELECT * FROM e_event WHERE id = ?", array($eid) )->fetch();


if ($pagestate == "newentry")
{

    // set up entry array
    $entry = array("eid" => $_REQUEST['id']);

    $entry['b-class']      = get_class($_REQUEST['class']);
    $entry['b-sailno']     = $_REQUEST['sailnumber'];
    $entry['b-name']       = $_REQUEST['boatname'];
    $entry['b-variant']    = "";
    $entry['b-fleet']      = "";
    $entry['b-division']   = get_category($_REQUEST['category']);
    $entry['b-pn']         = "";  // get this from t_class (but need setting for event to get right value
    $entry['b-personalpn'] = "";
    $entry['h-name']       = get_name($_REQUEST['helm-name']);
    $entry['h-club']       = get_club($_REQUEST['club'], $_SESSION['club_std']);
    $entry['h-age']        = "{$_REQUEST['helm-age']}";
    $entry['h-gender']     = "notreported";
    $entry['h-email']      = $_REQUEST['helm-email'];
    $entry['h-phone']      = get_phone($_REQUEST['ph-mobile']);
    $entry['h-emergency']  = get_phone($_REQUEST['ph-emer']);
    $entry['h-country']    = "";
    $entry['c-name']       = get_name($_REQUEST['crew-name']);
    $entry['c-club']       = "";
    $entry['c-age']        = "{$_REQUEST['crew-age']}";
    $entry['c-gender']     = "notreported";
    $entry['c-emergency']  = "";
    $entry['c-country']    = "";

    // check if it is a competitor known to raceManager (class and sailnumber match
    $competitor_id = check_competitor_exists($entry['b-class'], $entry['b-sailno']);

    // if handicap racing get pn from t_class or t_competitor
    $entry['b-pn'] = 0;
    if ( $event['scoring-type'] == 'handicap' or $event['scoring-type'] == 'pursuit' )
    {
        if ( $event['handicap-type'] == 'national' )
        {
            $entry['b-pn'] = $db_o->run("SELECT nat_py FROM t_class WHERE classname = ? and `active` = 1", array($entry['b-class']) )->fetchColumn();
        }
        elseif ( $event['handicap-type'] == 'local' )
        {
            $entry['b-pn'] = $db_o->run("SELECT local_py FROM t_class WHERE classname = ? and `active` = 1", array($entry['b-class']) )->fetchColumn();
        }
        elseif ( $event['handicap-type'] == 'personal' and $competitor_id !== false)
        {
            $entry['b-pn'] = $db_o->run("SELECT local_py FROM t_competitor WHERE classname = ? and `active` = 1", array($entry['b-class']) )->fetchColumn();y
        }
    }

// FIXME get entry no.

// determine if entry will be on waiting list
    $waiting_chk = false;
    if ($event['entry-limit'] > 0)
    {
        // get no. of current entries in this event
        $numentries = $db_o->run("SELECT COUNT(*) FROM e_entry WHERE eid = ? and `e-exclude` = 0", array($eid) )->fetchColumn();
        if ( $numentries > $event['entry-limit'] ) { $waiting_chk = true; }
    }

// determine if a junior consent form is required
    $junior_chk = false;
    if ( $entry['h-age'] < 18 or $entry['c-age'] < 18 ) { $junior_chk = true; }


//$newentry = "failed";     // can have value noentry|failed|str<id of entry>
//header("Location: rm_event.php?page=entries&year=$year&eid={$_REQUEST['eid']}&newentry=$newentry"); exit();

// process fields (club abbrevs, spaces in phone numbers, capitalisation of names)

// create new fields (guide, entry no., )

// should entry be on waiting list

// is any crew members less than 18 years old - need consent form
}
elseif ($pagestate == "updentry")
{
    $status = "failed";
}
else
{
    $status = "unknown pagestate";
}

// return to



exit();


function get_class($in_class)
{
    $class = ucwords(strtolower($in_class));
    return $class;
}

function get_category($in_category)
{
    $class = strtolower($in_category);
    return $class;
}

function get_name($in_name)
{
    $name = ucwords(strtolower($in_name));
    return $name;
}

function get_phone($in_phone)
{
    $in_phone = trim($in_phone);

    // remove international codes
    if (strpos($in_phone, "+") === 0) { $in_phone = str_replace('+','',$in_phone); }
    if (strpos($in_phone, "44") === 0) { $in_phone = str_replace('+','',$in_phone); }

    $phone = $in_phone;

    // check phone number is 11 digits starting with a 0
    if (ctype_digit($phone))
    {
        if ($phone[0] != "0")  { $phone = "0".$phone; }          // check if first digit is a 0

        if (strlen($phone) != 11 ) { $phone = "invalid"; }       // check if 11 digits
    }
    else
    {
        $phone = "invalid";
    }

    return $phone;
}

function get_club($in_club, $club_std = "")
{
    $club = trim($in_club);
    if ($club == $club_std)                // used to allow home club to always use the same format
    {
        $club = $club_std;
    }
    else
    {
        $club = str_ireplace("sailing club","SC", $club);
        $club = str_ireplace("yacht club","YC", $club);
    }

    return $club;
}

