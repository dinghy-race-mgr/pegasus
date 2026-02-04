<?php
/*
 * dtm_status_synch_check
 *
 * Interrogates dutyman members and duties view to check on confirmed and swap status.
 * Synchs dutyman future allocated duty information with the latest information in racemanager regarding swaps
 * Records current duty status information in t_eventduty
 *
 *
 */

require_once("../common/lib/util_lib.php");
require_once("../common/classes/db_class.php");

session_start();

//  ---------- setup
date_default_timezone_set('Europe/London');
$today  = date("Y-m-d");
key_exists("dryrun", $_REQUEST) ?  $dryrun = filter_var($_REQUEST['dryrun'], FILTER_VALIDATE_BOOLEAN) : $dryrun = false;
$start_synch = $today;                  // starts looking from today
$end_synch = date("Y-12-31");           // ends looking at end of current year


$cfg = u_set_config("../config/common.ini", array(), false);

$_SESSION['syslog'] = "../logs/sys/cronlog_".date("Y").".log";
$_SESSION['sql_debug'] = false;
$_SESSION['db_name'] = $cfg['db_name'];
$_SESSION['db_user'] = $cfg['db_user'];
$_SESSION['db_pass'] = $cfg['db_pass'];
$_SESSION['db_host'] = $cfg['db_host'];
$_SESSION['db_port'] = $cfg['db_port'];


// logging - start process (appending to cronlog)
u_cronlog("****** DUTYMAN STATUS SYNCH - start");
u_cronlog(" - starting analysis for events from $start_synch to $end_synch");

// open database connection for racemanager
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host'], $cfg['db_port']);

// open database connection for dutyman - doesn't seem to support PDO
$dbt_o = mysqli_connect("dutyman.biz","S0002342","necuCe82mati","dutyman", "3307");
$dbt_o = mysqli_connect($cfg['dtm_name'], $cfg['dtm_user'], $cfg['dtm_pass'], $cfg['dtm_host'], $cfg['dtm_port']);

if(!$dbt_o) {
    die("Connection failed: " . mysqli_connect_error());
}
//$dbt_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);
//$dbt_o = new DB($cfg['dtm_name'], $cfg['dtm_user'], $cfg['dtm_pass'], $cfg['dtm_host'], $cfg['dtm_port']);

$continue = true;

// ---------- get lookup table to convert dutyman rota names to racemanager rota codes

// get rota code lookup map
$rota = get_rota_lookup();
if (empty($rota)) {
    $continue = false;
    u_cronlog(" - rota code lookup not found in raceManager - ** end processing");
} else {
    u_cronlog(" - " . count($rota) . " rota codes defined");
}


// ---------- get dutyman information for each member that has been allocated a duty

if ($continue)
{
    $members = get_member_lookup();
    if (empty($members)) {
        $continue = false;
        u_cronlog(" - member information not found in dutyman - ** end processing");
    } else {
        u_cronlog(" - " . count($members) . " member records found in dutyman ");
    }
}

// ---------- get dutyman duties for each event in the standard structure

if ($continue)
{
    // get dtm duties in standard structure
    $dtm_duties = get_dtm_duty_arr($start_synch, $end_synch);
    if (empty($dtm_duties))                     // no duties -stop processing
    {
        $continue = false;
        u_cronlog(" - duty information not found in dutyman - ** end processing");
    }
    else                                        // extract list of unique events from extracted duties
    {
        $events = array();
        $eventid = 0;
        foreach ($dtm_duties as $duty)
        {
            if ($duty['eid'] != $eventid)
            {
                $eventid  = $duty['eid'];
                $events[] = $duty['eid'];
            }
        }
        u_cronlog(" - ".count($dtm_duties)." duties retrieved by dutyman duties - covering ".count($events)." events");
    }
}

// ---------- get racemanager duties for each event in the standard structure

if ($continue)
{
    // get racemanager duties in standard structure
    $rm_duties = get_rm_duty_arr($start_synch, $end_synch);
    if (empty($rm_duties))
    {
        $continue = false;
        u_cronlog(" - duty information not found in raceManager - ** end processing");
    }
    else
    {
        u_cronlog(" - ".count($rm_duties)." duty records found in raceManager ");
    }
}

// ---------- compare the duty specification for dutyman and racemanager for each event

if ($continue)
{
    $status = duty_compare($events, $dtm_duties, $rm_duties, $dryrun);
    $num_events = $status['num_events'];
    $logtext = <<<EOT
 - DIFFERENCE COUNTS | no. of duties mismatch: {$status['numduty']} | duplicate duties: {$status['dupduty']}
  | duty changes: {$status['personchg']} | confirms: {$status['confirms']} | unconfirms: {$status['unconfirms']} | swaps requested: {$status['swapreq']} | swaps dropped: {$status['unswapreq']}
EOT;
    u_cronlog($logtext);

}

// ---------- apply differences between dutyman and racemanager duties to the racemanager database (t_eventduty)

if ($continue)
{
    if ($dryrun)
    {
        u_cronlog(" - DRYRUN - changes NOT APPLIED to racemanager event duty records ");
        //$continue = false;
    }
    else
    {
        $status = apply_changes();     // needs to log each change
    }
}

// ---------- republish the website programme

if ($continue)
{

    if ($dryrun)
    {
        u_cronlog(" - DRYRUN - programme not updated on website ");
        //$continue = false;
    }
    else
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $cfg['baseurl']."/rm_utils/website_publish.php?pagestate=submit&report=off");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   //returns result output from script

        $output = curl_exec($ch);
        curl_close($ch);

        parse_str($output, $status_arr);

        $msg = "website programme update: ";
        $status_arr['file'] ? $msg.= "programme created - " : $msg.= "programme not created - ";
        $status_arr['transfer'] ? $msg.= "uploaded to website  - " : $msg.= "not uploaded to website - ";
        $msg.= "dates {$status_arr['start']} to {$status_arr['last']}";

        u_cronlog(" - $msg ");
    }
}

if ($continue)
{
    $rept = notify_rota_managers($dryrun);             // create report for today's run
    echo <<<EOT
<div><hr><p style="margin-left: 30px;">$rept</p><hr></div>
EOT;

    if ($dryrun)
    {
        u_cronlog(" - DRYRUN - email notification of changes to rota managers not sent");
        $continue = false;
    }
    else
    {
        // fixme need to add correct from and to emails  (rota.managers@starcrossyc.org.uk)
        $email_from = array("email" => "noreply@starcrossyc.org.uk", "name"=> "Starcross YC no reply");    // noreply@starcrossyc.org.uk // SYC – No reply
        $emails_to = array(
            "0" => array("email" => "rota.managers@starcrossyc.org.uk", "name"=> "SYC Rota Managers"),   // rota.managers@starcrossyc.org.uk / SYC rota managers
            "1" => array("email" => "markeb14.762@gmail.com", "name"=> "Mark Elkington")
        );
        $subject = "DUTYMAN CHANGES - ".date("l jS F Y H:m");
        $display_date = date("l jS F Y H:m");

        $html = <<<EOT
<div>
<p>Hi</p>
<p>This is the report of dutyman changes as checked at $display_date</p>
<p>The new changes recorded since the last check are shown below - and have been applied to the online SYC programme.  
Other issues that may need reviewing are listed after the changes</p>
<hr>
<p style="margin-left: 30px;">$rept</p>
<hr>
</div>
EOT;

        $status_arr = send_email($email_from, $emails_to, $subject, $html, $cfg['BREVO_API']);      // sends email to all rota managers
        u_cronlog("<pre>email status: ".print_r($status_arr,true)."</pre>");
        if ($status_arr['success'])
        {
            u_cronlog(" - email notification of changes sent to rota managers ");
        }
        else
        {
            u_cronlog(" - email notification of changes to rota managers FAILED (curl: {$status_arr['err']}  return: {$status_arr['return']}");
        }
    }
}

// logging end of process
u_cronlog("DUTYMAN STATUS SYNCH - end [processed duties for $num_events events]");
exit("stopping - $num_events events");

// -----------------------------------------------------------------------------------------------

function get_rota_lookup()
{
    global $db_o;

    $rotas = $db_o->db_get_rows("select `code`, `label` from t_code_system WHERE groupname='rota_type' ORDER BY code ASC");
    foreach($rotas as $rota)
    {
        $dutycodes["{$rota['label']}"] = $rota['code'];
    }

    return $dutycodes;
}

function get_member_lookup()
{
    global $dbt_o;

    $arr = array();

    // get member info
    $members = mysqli_query($dbt_o,"SELECT * FROM members ORDER BY `Last Name` ASC");
    //$members = $dbt_o->run("SELECT * FROM members ORDER BY `Last Name` ASC", array() )->fetchall();
    $i = 0;
    foreach($members as $member)
    {
        $i++;
        $arr[$i] = array(
            "Last Name"     => $member['Last Name'],
            "First Name"    => $member['First Name'],
            "Email Address" => $member['Email Address'],
            "Phone 2"       => $member['Phone 2'],
            "Phone"         => $member['Phone'],
            "Address 1"     => $member['Address 1'],
            "Member UID"    => $member['Member UID']
        );
    }

    return $arr;
}

function get_dtm_duty_arr($start, $end)
{
    global $dbt_o;
    global $rota, $members;

    // get duty data
    $arr = array();
    $err = array();

    $query = "SELECT * from duties WHERE `Duty Date` >= '$start' AND `Duty Date` <= '$end' ORDER BY `Duty Date` ASC";
    $records = mysqli_query($dbt_o, $query);

    foreach ($records as $record)
    {
        // decode notes to get event id
        $n_data = array();
        if (!empty($record['Notes']))
        {
            if (strpos($record['Notes'], "=") !== false) 
            {
                parse_str($record['Notes'], $n_data);
            } 
            else 
            {
                u_cronlog(" - ** technical issue [event: {$record['Event']} - {$record['Duty Date']} duty: {$record['Duty Type']} 
                person: {$record['First Name']} {$record['Last Name']} - notes field not correctly configured");
            }
        }
        else
        {
            u_cronlog(" - ** technical issue [event: {$record['Event']} - {$record['Duty Date']}  duty: {$record['Duty Type']} 
            person: {$record['First Name']} {$record['Last Name']} - notes field is empty");
        }

        // get event name (removing tide stuff)
        $tide_pos = strpos($record['Event'], "[");
        $tide_pos === false ? $event_name = $record['Event'] : $event_name = strstr($record['Event'], '[', true);


        // get webcollect member id - stored in first line of address and other member related info
        if (empty($record['Last Name']) and empty($record['First Name']))   // duty is definitely unallocated
        {
            $ln = "UNALLOCATED";
            $fn = "";
            $m_id = "";
            $dlogin = "";
        }
        else                                                                // check through members to find the right person
        {
            $found = false;
            foreach ($members as $k=>$member)
            {
                //$member_index = $k;
                if ($member['Last Name'] == $record['Last Name'] AND $member['First Name'] == $record['First Name'])
                {
                    $found = true;
                    break;
                }
            }

            if (!$found)                                                    // member found
            {
                $ln = "UNALLOCATED";
                $fn = "";
                $m_id = "";
                $dlogin = "";
            }
            else                                                             // member not found
            {
                $ln = $members[$k]['Last Name'];
                $fn = $members[$k]['First Name'];
                $m_id = $members[$k]['Address 1'];
                $dlogin = $members[$k]['Member UID'];
            }
        }


        $rota_code = $rota["{$record['Duty Type']}"];

//        debug code below  for missing notes information (e.g. duty record has been deleted)

//        if ($record['Duty Type'] == "Safety Boat 1" or $record['Duty Type'] == "Safety Boat 2")
////      if (empty($rota["{$record['Duty Type']}"]) or empty($n_data['eid']))
//        {
//            $debug_arr =  array(
//                "eid"        => $n_data['eid'],
//                "date"       => $record['Duty Date'],
//                "ename"      => $event_name,
//                "code"       => $rota_code,
//                "fn"         => $fn,
//                "ln"         => $ln,
//                "memberid"   => $m_id,
//                "dtm_login"  => $dlogin,
//                "confirmed"  => filter_var($record['Confirmed'], FILTER_VALIDATE_BOOLEAN),
//                "swapwanted" => filter_var($record['Swap Wanted'], FILTER_VALIDATE_BOOLEAN),
//                "swappable"  => filter_var($record['Swappable'], FILTER_VALIDATE_BOOLEAN),
//                "reminders"  => filter_var($record['Reminders'], FILTER_VALIDATE_BOOLEAN)
//                //"extra"      => "event_type={$n_data['type']}"
//            );
//
//            echo "<pre>".print_r($debug_arr,true)."</pre>";
//        }

        $arr[] = array(
            "eid"        => $n_data['eid'],
            "date"       => $record['Duty Date'],
            "ename"      => $event_name,
            "code"       => $rota_code,
            "fn"         => $fn,
            "ln"         => $ln,
            "memberid"   => $m_id,
            "dtm_login"  => $dlogin,
            "confirmed"  => filter_var($record['Confirmed'], FILTER_VALIDATE_BOOLEAN),
            "swapwanted" => filter_var($record['Swap Wanted'], FILTER_VALIDATE_BOOLEAN),
            "swappable"  => filter_var($record['Swappable'], FILTER_VALIDATE_BOOLEAN),
            "reminders"  => filter_var($record['Reminders'], FILTER_VALIDATE_BOOLEAN)
        );

        // sort array by date, eid, rota code and member last name
        $date_sort = array_column($arr, 'date');
        $eid_sort = array_column($arr, 'eid');
        $code_sort = array_column($arr, 'code');
        $ln_sort = array_column($arr, 'ln');
        array_multisort($date_sort, SORT_ASC, $eid_sort, SORT_ASC, $code_sort, SORT_ASC, $ln_sort, SORT_ASC, $arr);
    }

    return $arr;
}

function get_rm_duty_arr($start, $end)
{
    global $db_o;

    $arr = array();

    $query = "select a.id, b.id as dutyid, event_date, event_name, event_type, person, SUBSTR(person FROM (INSTR(person, \" \") + 1)) as ln, 
              dutycode, confirmed, swap_requested, swapable 
              FROM t_event as a JOIN t_eventduty as b ON a.id=b.eventid 
              WHERE `event_date` >= '$start' and `event_date` <= '$end' 
              ORDER BY `event_date` ASC, a.id ASC, dutycode ASC, ln ASC;";
    $records = $db_o->db_get_rows($query);

    foreach ($records as $record) {
        // split person name into first and family name
        $names = u_split_name($record['person']);

        if (strtolower($names['fn']) == "unallocated" or strtolower($names['fm']) == "unallocated")    // if unallocated no matching member
        {
            $memberid  = "";
            $dtm_login = "";
            $reminder  = "";
        }
        else                                                                                           // get matching rotamember record
        {
            $fn = mres($names['fn']);
            $fm = mres($names['fm']);
            $query = "SELECT * FROM t_rotamember WHERE firstname = '$fn' and familyname = '$fm' LIMIT 1";
            $matches = $db_o->db_get_rows($query);
            
            if (empty($matches))
            {
                $memberid  = "";
                $dtm_login = "";
                $reminder  = "";
            }
            else
            {
                $memberid  = $matches[0]['memberid'];
                $dtm_login = $matches[0]['dtm_login'];
                $reminder  = $matches[0]['reminders'];
            }
        }

        $arr[] = array(
            "eid"        => $record['id'],
            "did"        => $record['dutyid'],
            "date"       => $record['event_date'],
            "ename"      => $record['event_name'],
            "code"       => $record['dutycode'],
            "fn"         => $names['fn'],
            "ln"         => $names['fm'],
            "memberid"   => $memberid,
            "dtm_login"  => substr($dtm_login, strrpos("/$dtm_login", '/')),
            "confirmed"  => filter_var($record['confirmed'], FILTER_VALIDATE_BOOLEAN),
            "swapwanted" => filter_var($record['swap_requested'], FILTER_VALIDATE_BOOLEAN),
            "swappable"  => filter_var($record['swapable'], FILTER_VALIDATE_BOOLEAN),
            "reminders"  => $reminder
            //"extra"      => "event_type={$record['event_type']}"
        );
    }

    // sort array by date, eid and rota code
    $date_sort = array_column($arr, 'date');
    $eid_sort = array_column($arr, 'eid');
    $code_sort = array_column($arr, 'code');
    $ln_sort = array_column($arr, 'ln');
    array_multisort($date_sort, SORT_ASC, $eid_sort, SORT_ASC, $code_sort, SORT_ASC, $ln_sort, SORT_ASC, $arr);

    return $arr;
}


function duty_compare($events, $dtm_duty, $rm_duty, $dryrun)
{
    global $db_o;
    global $today;
    global $rota;

    $counts = array("numduty"=>0, "dupduty"=>0, "personchg"=>0, "confirms"=>0, "unconfirms"=>0, "swapreq"=>0,
                    "unswapreq"=>0, "num_events"=>0, "unallocated"=>0);
    $num_events = 0;

   foreach ($events as $event)
   {

        // get contextual data for this event
        $query = "SELECT `event_name` as `name`, `event_type` as `type`, `event_date` as `date`, `event_start` as `start`
                  FROM t_event WHERE `id` = $event LIMIT 1";
        $evdata = $db_o->db_get_row($query);
        $evlbl = u_truncatestring(stripslashes($evdata['name']), 25, "..")." ".date("j-M", strtotime($evdata['date']));

        $counts['num_events']++;

        $dtm_keys = u_2darray_search($dtm_duty, "eid", "$event");
        $dtm_arr  = keys_to_array($dtm_keys, $dtm_duty);

        $rm_keys  = u_2darray_search($rm_duty, "eid", "$event");
        $rm_arr   = keys_to_array($rm_keys, $rm_duty);

        // check 1 - check if different number of duties in DTM and RM for this event
        $dtm_keys_num = count($dtm_keys);
        $rm_keys_num  = count($rm_keys);
        if ($dtm_keys_num != $rm_keys_num)
        {
            $counts['numduty']++;
            $chg_arr = array(
                "dutyid"     => "",
                "eventid"    => $event,   // event just holds the id
                "changetype" => "note",
                "setclause"  => "",
                "info"       => "** [$evlbl] - different no. of duties for dutyman ($dtm_keys_num) and racemanager ($rm_keys_num)  ",
                "status"     => "X"
            );
            // add change to t_dutysync and log it
            if (!$dryrun) { $ins = $db_o->db_insert("t_dutysync", $chg_arr ); }
            u_cronlog(" - ".$chg_arr['info']);
        }

        // check 2 - loop through dtm duties for this event and see if same person used twice
        $dtm_person_arr = array();
        $i = 0;
        foreach($dtm_keys as $key)
        {
            if (strtolower(trim($dtm_duty[$key]['ln'])) != "unallocated" )      // ignore unallocated duties
            {
                $dtm_person_arr[] = trim($dtm_duty[$key]['fn'])." ".trim($dtm_duty[$key]['ln']);;
                $i = $key;
            }
        }

        $temp = array_count_values($dtm_person_arr);
        $duplicate = false;
        $duplicate_person = "";
        foreach ($temp as $k => $item)
        {
            if ($item > 1)
            {
                $duplicate = true;
                $duplicate_person = $k;
                break;
            }
        }

        if ($duplicate)
        {
            $counts['dupduty']++;
            $chg_arr = array(
                "dutyid"     => "",
                "eventid"    => $event,
                "changetype" => "note",
                "setclause"  => "",
                "info"       => "** Note  [$evlbl] - $duplicate_person allocated to more than one duty for this {$evdata['type']} event ",
                "status"     => "X"
            );
            // add change to t_dutysync and log it
            if (!$dryrun) { $ins = $db_o->db_insert("t_dutysync", $chg_arr ); }
            u_cronlog(" - ".$chg_arr['info']);
        }


        // loop over duties for this event to check changes for each duty
        foreach ($dtm_arr as $k=>$duty)
        {

            // setup common variables
            $fn = trim($dtm_arr[$k]['fn']);                        // first name
            $ln = trim($dtm_arr[$k]['ln']);                        // last name
            $person = "{$dtm_arr[$k]['fn']} {$dtm_arr[$k]['ln']}"; // full name
            $rotalbl = array_search($dtm_arr[$k]['code'], $rota);  // rota text

            // review differences

            // check 3 - if duty is unallocated
            if (strtolower($dtm_arr[$k]['ln']) == "unallocated" OR empty($dtm_arr[$k]['ln']))
            {
                $counts['unallocated']++;
                $chg_arr = array(
                    "dutyid"     => "",
                    "eventid"    => $event,
                    "changetype" => "note",
                    "setclause"  => "",
                    "info"       => "** Note  [$evlbl - $rotalbl] - nobody is allocated to this duty for this {$evdata['type']} event ",
                    "status"     => "X"
                );
                // add change to t_dutysync and log it
                if (!$dryrun) { $ins = $db_o->db_insert("t_dutysync", $chg_arr ); }
                u_cronlog(" - ".$chg_arr['info']);
            }


//        debug code below for missing records in raceManager

//            if (($k == 5 or $k == 6) and empty($rm_arr[$k]))
//            {
//                echo "<pre>K: $k</pre>";
//                echo "<pre>DTM: {$dtm_arr[$k]['ln']} RM: {$rm_arr[$k]['ln']}</pre>";
//                echo "<pre>DTM arr: ".print_r($dtm_arr[$k],true)."</pre>";
//                echo "<pre>RM arr: ".print_r($rm_arr[$k],true)."</pre>";
//            }

            // check 4 - if person doing duty has changed - check last name
            if (strtolower($dtm_arr[$k]['ln']) != strtolower($rm_arr[$k]['ln']))
            {
                if (strtolower($dtm_arr[$k]['ln']) == "unallocated")
                {
                    $chgperson = "UNALLOCATED";
                    $set_clause = "`person`='$chgperson',`email`='',`phone`='',`memberid`='',`confirmed`='0',`confirmed_date`='',`swap_requested`='0',`swap_request_date`='',`notes`='' ";
                }
                else
                {
                    $chgperson = $person;

                    // find details of person we are swapping to
                    $query = "SELECT * FROM t_rotamember WHERE firstname = '$fn' and familyname = '$ln' LIMIT 1";
                    $m = $db_o->db_get_row($query);
                    if (empty($m))
                    {
                        // details not found - just add name and no other details
                        $set_clause = "`person`='$chgperson',`email`='',`phone`='', `memberid`='',`confirmed`= '0',`confirmed_date`='',`swap_requested`='0',`swap_request_date`='',`notes`='' ";
                    }
                    else
                    {
                        // details found - add name and other details from t_rotamember
                        $set_clause = "`person`='$chgperson',`email`='{$m['email']}',`phone`='{$m['phone']}',`memberid`='{$m['memberid']}',`confirmed`='0',`confirmed_date`='',`swap_requested`='0',`swap_request_date`='',`notes`='' ";
                    }

                }

                $counts['personchg']++;
                $chg_arr = array(
                    "dutyid"     => $rm_arr[$k]['did'],
                    "eventid"    => $dtm_arr[$k]['eid'],
                    "changetype" => "person",
                    "setclause"  => $set_clause,
                    "info"       => "person change [$evlbl- $rotalbl] - from {$rm_arr[$k]['fn']} {$rm_arr[$k]['ln']} to $person",
                    "status"     => "X"
                );
                // add change details to t_dutysync and log it
                if (!$dryrun) { $ins = $db_o->db_insert("t_dutysync", $chg_arr); }
                u_cronlog(" - duty change [$evlbl - $rotalbl] - {$chg_arr['info']}");
            }

            // check 5 - if confirmation status has changed ------------------------------------------

            if ($dtm_arr[$k]['confirmed'] != $rm_arr[$k]['confirmed'])
            {
                if ($dtm_arr[$k]['confirmed'] and !$rm_arr[$k]['confirmed'])  // now confirmed
                {
                    $action = true;
                    $change_txt = "confirmed";
                    $set_clause = "`confirmed` = '1', `confirmed_date` = '$today'";
                    $counts['confirms']++;
                }
                elseif (!$dtm_arr[$k]['confirmed'] and $rm_arr[$k]['confirmed'])  // now unconfirmed
                {
                    $action = true;
                    $change_txt = "unconfirmed";
                    $set_clause = "`confirmed` = '0', `confirmed_date` = '$today'";
                    $counts['unconfirms']++;
                }
                else      // no change
                {
                    $action = false;
                    $change_txt = "";
                    $set_clause = "";
                }

                if ($action)
                {
                    $chg_arr = array(
                        "dutyid"     => $rm_arr[$k]['did'],
                        "eventid"    => $dtm_arr[$k]['eid'],
                        "changetype" => "confirm",
                        "setclause"  => $set_clause,
                        "info"       => "$change_txt [$evlbl - $rotalbl - $person ]",
                        "status"     => "X"
                    );
                    // add change to t_dutysync and log it
                    if (!$dryrun) { $ins = $db_o->db_insert("t_dutysync", $chg_arr ); }
                    u_cronlog(" - ".$chg_arr['info']);
                }
            }

            // check 6 - if swap request status has changed -------------------------------------------
            if ($dtm_arr[$k]['swapwanted'] != $rm_arr[$k]['swapwanted'])
            {
                if ($dtm_arr[$k]['swapwanted'] and !$rm_arr[$k]['swapwanted'])     // swap now wanted
                {
                    $action = true;
                    $change_txt = "swap wanted";
                    $set_clause = "`swap_requested` = '1', `swap_request_date` = '$today'";
                    $counts['swapreq']++;
                }
                elseif (!$dtm_arr[$k]['swapwanted'] and $rm_arr[$k]['swapwanted'])  // swap no longer wanted
                {
                    $action = true;
                    $change_txt = "swap request dropped";
                    $set_clause = "`swap_requested` = '0', `swap_request_date` = '$today'";
                    $counts['unswapreq']++;
                }
                else                                                                // no change
                {
                    $action = false;
                    $change_txt = "";
                    $set_clause = "";
                }

                if ($action)
                {
                    $chg_arr = array(
                        "dutyid"     => $rm_arr[$k]['did'],
                        "eventid"    => $dtm_arr[$k]['eid'],
                        "changetype" => "swap",
                        "setclause"  => $set_clause,
                        "info"       => "$change_txt [$evlbl - $rotalbl - $person] ",
                        "status"     => "X"
                    );
                    // add change to t_dutysync and log it
                    if (!$dryrun) { $ins = $db_o->db_insert("t_dutysync", $chg_arr ); }
                    u_cronlog(" - ".$chg_arr['info']);
                }
            }
        }
   }
   return $counts;
}


function apply_changes()
{
    global $db_o;

    $status = array();

    // get changes from database
    $query = "SELECT * FROM t_dutysync WHERE `status` = 'X' AND changetype != 'note' ORDER BY createdate ASC";
    $changes = $db_o->db_get_rows($query);

    $full_query = "";
    foreach ($changes as $change)
    {
        $query = "UPDATE t_eventduty SET {$change['setclause']} WHERE `id` = {$change['dutyid']} and `eventid` = {$change['eventid']}";
        $set = $db_o->db_query($query);
        $full_query.= $query."\n";

        // mark changes as applied or failed
        if ($set)
        {
            $upd = $db_o->db_query("UPDATE t_dutysync SET `status` = 'P' WHERE `id` = {$change['id']}");
        }
        else
        {
            $upd = $db_o->db_query("UPDATE t_dutysync SET `status` = 'F' WHERE `id` = {$change['id']}");
            u_cronlog(" - event duty change in RM FAILED: {$change['info']}\n");
        }

    }
    u_cronlog("Changes applied:\n".$full_query);

    return $status;
}

function notify_rota_managers($dryrun)
{
    // create daily message for emailing to rota manager

    global $db_o;

    $diff = "";
    $notes = "";

    if ($dryrun)
    {
        $query_notes = "SELECT * FROM `t_dutysync` WHERE `status` = 'X' AND `changetype` = 'note' AND `createdate` >= CURDATE()";
        $query_changes = "SELECT * FROM `t_dutysync` WHERE `status` = 'X' AND `changetype` != 'note' AND `createdate` >= CURDATE()";
    }
    else
    {
        $query_notes = "SELECT * FROM `t_dutysync` WHERE `status` = 'X' AND `changetype` = 'note' AND `createdate` >= CURDATE()";
        $query_changes = "SELECT * FROM `t_dutysync` WHERE `status` IN ('P','F') AND `changetype` != 'note' AND `createdate` >= CURDATE()";
        //$query = "SELECT * FROM `t_dutysync` WHERE (`status` IN ('P','F') AND `changetype` != 'note') OR (`status` = 'X' AND `changetype` = 'note') AND (`createdate` >= CURDATE())";
    }
    $notes_rs = $db_o->db_get_rows($query_notes);
    $changes_rs = $db_o->db_get_rows($query_changes);

    $notes = "";
    $num_notes = 0;
    foreach ($notes_rs as $row)
    {
        $num_notes++;
        $notes.= " - CHECK (".date("d-m-Y", strtotime($row['createdate']))."): {$row['info']} <br>";
        // reset status so it isn't included in future reports as a duplicate
        $upd = $db_o->db_query("UPDATE t_dutysync SET `status` = 'P' WHERE `id` = {$row['id']}");
    }

    $diff = "";
    $num_diff = 0;
    foreach ($changes_rs as $row)
    {
        $num_diff++;
        $diff.= " - CHANGE (".date("d-m-Y", strtotime($row['createdate']))."):  {$row['info']} <br>";
    }

    $rept = <<<EOT
        <p><b>Changes identified [$num_diff]</b></p>
        $diff
        <br>
        <p><b>For Review [$num_notes]</b></p>
        $notes
        <br>
EOT;

    return $rept;
}

function keys_to_array($keys, $data)
{
    // creates new array by selecting keys from data array
    $arr = array();
    foreach ($keys as $key)  { $arr[] = $data[$key]; }
    return $arr;
}

function send_email($email_from, $emails_to, $subject, $html, $api_key)   /// FIXME move to lib
{
    $status_arr = array("success"=>true, "err"=>"", "return"=>"");

    $data = array(
        "sender"      => $email_from,
        "to"          => $emails_to,
        "subject"     => $subject,
        "htmlContent" => "<html><head></head><body>$html</body></html>"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.sendinblue.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = "Api-Key: $api_key";
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    $status_arr['return'] = $result;

    if (curl_errno($ch))
    {
        $status_arr['err'] = 'Error:' . curl_error($ch);
        $status_arr['success'] = false;
    }

    curl_close($ch);

    return $status_arr;
}

function mres($value)
    // escapes string for use in sql
{
    $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

    return str_replace($search, $replace, $value);
}
