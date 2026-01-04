<?php
/*
 * dtm_status_check
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
$today = "2026-01-01"; // FIXME to date("Y-m-d")
$dryrun = true;        // fixme
if (key_exists("dryrun", $_REQUEST)) { $dryrun = filter_var($_REQUEST['dryrun'], FILTER_VALIDATE_BOOLEAN); }

$start_synch = $today;                  // starts looking from today
//$end_synch = date("Y")."-12-31";         // FIXME ends looking at end of current year
$end_synch = "2026-12-31";

$cfg = u_set_config("../config/common.ini", array(), false);
$cfg['dutyman'] = u_set_config("../config/rm_utils.ini", array("dutyman"), true);
foreach($cfg['dutyman'] as $k => $v) {$cfg[$k] = $v;}
unset($cfg['dutyman']);
$_SESSION['syslog'] = "../logs/sys/cronlog_".date("Y").".log";
$_SESSION['sql_debug'] = false;
$_SESSION['db_name'] = $cfg['db_name'];
$_SESSION['db_user'] = $cfg['db_user'];
$_SESSION['db_pass'] = $cfg['db_pass'];
$_SESSION['db_host'] = $cfg['db_host'];
$_SESSION['db_port'] = $cfg['db_port'];

// logging - start process (appending to cronlog)
u_cronlog("DUTYMAN STATUS SYNCH - start");
u_cronlog(" - starting analysis for events from $start_synch to $end_synch");

// open database connection for racemanager
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host'], $cfg['db_port']);

// open database connection for dutyman - doesn't seem to support PDO
$dbt_o = mysqli_connect("dutyman.biz","S0002342","necuCe82mati","dutyman", "3307");
if(!$dbt_o) {
    die("Connection failed: " . mysqli_connect_error());
}
//$dbt_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);
//$dbt_o = new DB($cfg['dtm_name'], $cfg['dtm_user'], $cfg['dtm_pass'], $cfg['dtm_host'], $cfg['dtm_port']);

$continue = true;

// ---------- get lookup table to convert dutyman rota names to racemanager rota codes

// get rota code lookup map
$rota = get_rota_lookup();
if (empty($rota))
{
    $continue = false;
    u_cronlog(" - rota code lookup not found in raceManager - ** end processing");
}
else
{
    u_cronlog(" - ".count($rota)." rota codes defined");
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
    $status = duty_compare($events, $dtm_duties, $rm_duties);
    $logtext = <<<EOT
 - dutyman vss racemanager difference counts | no. of duties mismatch: {$status['numduty']} | duplicate duties: {$status['dupduty']}
  | duty changes: {$status['personchg']} | confirms: {$status['confirms']} | unconfirms: {$status['unconfirms']} | swaps requested: {$status['swapreq']} | swaps dropped: {$status['unswapreq']}
EOT;
    u_cronlog($logtext);
}

// ---------- apply differences between dutyman and racemanager duties to the racemanager database (t_eventduty)

if ($continue)
{
    if ($dryrun)
    {
        u_cronlog(" - DRYRUN - changes (logged in t_dutysync) NOT APPLIED to racemanager event duty records ");
        $continue = false;
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
        $continue = false;
    }
    else
    {
        // FIXME add call to utility to update website programme
        // website_publish.php?pagestate=submit&
        // send from 1/1/<current_year to 31/12/<current_year> - unless today is > 1/9/<year) - add three months of next year
        $status = true; // fixme this is just for testing
        if ($status)
        {
            // website_publish.php?pagestate=submit&
            // not sending date-start or date-end - uses defaults relative to today.
            u_cronlog(" - website programme update completed ");
        }
        else
        {
            u_cronlog(" - website programme update FAILED ");
        }
    }
}

if ($continue)
{
    if ($dryrun)
    {
        u_cronlog(" - DRYRUN - weekly email notification of changes to rota managers not sent");
        $continue = false;
    }
    else
    {
        $weekly_rept = notify_rota_managers();             // create report for today's run

        // fixme need to add correct from and to emails
        $email_from = array("email" => "markelkington640@gmail.com", "name"=> "Buzz Aldrin");
        $emails_to = array(
            "0" => array("email" => "markeb14.762@gmail.com", "name"=> "Mark Elkington"),
            "1" => array("email" => "msmaryelk@gmail.com", "name"=> "Mary Elkington"),
        );
        $subject = "DUTYMAN CHANGES - $today";
        $display_date = date("l jS F Y", strtotime($today));

        $html = <<<EOT
<div>
<p>Hi</p>
<p>This is the weekly report of dutyman changes for the week ending $display_date</p>
<p>The changes in the last week are:</p>
<hr>
<p style="margin-left: 30px;">$weekly_rept</p>
<hr>
</div>
EOT;

        //$status = send_email($email_from, $emails_to, $subject, $html, $cfg['BREVO_API']);      // sends email to all rota managers

        // fixme debug
        echo $html;
        $status = true;
        if ($status) u_cronlog(" - email notification of changes sent to rota managers ");
    }
}

// logging end of process
u_cronlog("DUTYMAN STATUS SYNCH - end");

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
        //echo "<pre>".print_r($record,true)."</pre>";

        // decode notes to get event id
        $n_data = array();
        if (!empty($record['Notes']))
        {
            if (strpos($record['Notes'], "=") !== false) 
            {
                parse_str($record['Notes'], $n_data);
                $n_data['type'] = "dcruise"; // fixme only required for testing
                //if (!key_exists("type", $n_data)) { $n_data['type'] = "test_type";}
            } 
            else 
            {
                u_cronlog(" - ** tech issue [event: {$record['id']} duty: {$record['dutycode']} 
                person: {$record['First Name']} {$record['Last Name']} - notes field not correctly configured");
            }
        }
        else
        {
            u_cronlog(" - * tech issue [event: {$record['id']} duty: {$record['dutycode']} person: {$record['First Name']} {$record['Last Name']} - notes field is empty");
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
            "reminders"  => filter_var($record['Reminders'], FILTER_VALIDATE_BOOLEAN),
            "extra"      => "event_type={$n_data['type']}"
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
              WHERE `event_date` >= '2026-01-01' and `event_date` <= '2026-12-31' 
              ORDER BY `event_date` ASC, a.id ASC, dutycode ASC, ln ASC;";
    //echo "<pre>$query</pre>";
    $records = $db_o->db_get_rows($query);

    foreach ($records as $record) {
        // split person name into first and family name
        $names = u_split_name($record['person']);

        // get matching rotamember record
        $fn = mres($names['fn']);
        $fm = mres($names['fm']);
        $query = "SELECT * FROM t_rotamember WHERE firstname = '$fn' and familyname = '$fm' LIMIT 1";
        //echo "<pre>$query</pre>";
        $matches = $db_o->db_get_rows($query);

        $arr[] = array(
            "eid"        => $record['id'],
            "did"        => $record['dutyid'],
            "date"       => $record['event_date'],
            "ename"      => $record['event_name'],
            "code"       => $record['dutycode'],
            "fn"         => $names['fn'],
            "ln"         => $names['fm'],
            "memberid"   => $matches[0]['memberid'],
            "dtm_login"  => substr($matches[0]['dtm_login'], strrpos("/{$matches[0]['dtm_login']}", '/')),
            "confirmed"  => filter_var($record['confirmed'], FILTER_VALIDATE_BOOLEAN),
            "swapwanted" => filter_var($record['swap_requested'], FILTER_VALIDATE_BOOLEAN),
            "swappable"  => filter_var($record['swapable'], FILTER_VALIDATE_BOOLEAN),
            "reminders"  => $matches[0]['reminders'],
            "extra"      => "event_type={$record['event_type']}"
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


function duty_compare($events, $dtm_duty, $rm_duty)
{
    global $db_o;
    global $today;

    $counts = array("numduty"=>0, "dupduty"=>0, "personchg"=>0, "confirms"=>0, "unconfirms"=>0, "swapreq"=>0, "unswapreq"=>0);

   foreach ($events as $event)
   {
       echo "<pre>EVENT = $event</pre>"; // FIXME debug

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
                "info"       => "** [event $event] - different no. of duties for dutyman and racemanager  ",
                "status"     => "X"
            );
            // add change to t_dutysync and log it
            $ins = $db_o->db_insert("t_dutysync", $chg_arr );
            u_cronlog(" - ".$chg_arr['info']);
        }

        // check 2 - loop through dtm duties for this event and see if same person used twice
        $dtm_person_arr = array();
        $i = 0;
        foreach($dtm_keys as $key)
        {
            if (strtolower(trim($dtm_duty[$key]['ln'])) != "unallocated" )      // ignore unallocated duties
            {
                $person = trim($dtm_duty[$key]['fn'])." ".trim($dtm_duty[$key]['ln']);
                $dtm_person_arr[] = $person;
                $i = $key;
            }
        }
        parse_str($dtm_duty[$i]['extra'], $extra);

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
                "info"       => "** Note  [event $event] - $duplicate_person allocated to more than one duty for this {$extra['event_type']} event ",
                "status"     => "X"
            );
            // add change to t_dutysync and log it
            $ins = $db_o->db_insert("t_dutysync", $chg_arr );
            u_cronlog(" - ".$chg_arr['info']);
        }

        // loop over duties for this event to check changes for each duty
        foreach ($dtm_arr as $k=>$duty)
        {
            $person = "{$dtm_arr[$k]['fn']} {$dtm_arr[$k]['ln']}";

            // review differences

            // check 3 - if person doing duty has changed - check last name
            if (strtolower($dtm_arr[$k]['ln']) != strtolower($rm_arr[$k]['ln']))
            {
                $counts['personchg']++;
                $chg_arr = array(
                    "dutyid"     => $rm_arr[$k]['did'],
                    "eventid"    => $dtm_arr[$k]['eid'],
                    "changetype" => "person",
                    "setclause"  => "person = $person",
                    "info"       => "changed from {$rm_arr[$k]['fn']} {$rm_arr[$k]['ln']} to $person",
                    "status"     => "X"
                );
                // add change details to t_dutysync and log it
                $ins = $db_o->db_insert("t_dutysync", $chg_arr);
                u_cronlog(" - duty change [event $event - {$dtm_arr[$k]['code']} - $person ] : {$chg_arr['info']}");
            }

            // check 4 - if confirmation status has changed ------------------------------------------
            if ($event == "11442")
            {
                echo "<pre>event 11442 DTM: {$dtm_arr[$k]['confirmed']}  RM: {$rm_arr[$k]['confirmed']}</pre>";
                echo "<pre>".print_r($dtm_arr[$k],true)."</pre>";
                echo "<pre>".print_r($rm_arr[$k],true)."</pre>";
            }
            //if (key_exists('confirmed', $diff))
            if ($dtm_arr[$k]['confirmed'] != $rm_arr[$k]['confirmed'])
            {
                $action = false;
                if ($dtm_arr[$k]['confirmed'] == $rm_arr[$k]['confirmed'])     // no change
                {
                    $action = false;
                    $change_txt = "";
                    $confirmed_code = "N";
                }
                elseif ($dtm_arr[$k]['confirmed'] and !$rm_arr[$k]['confirmed'])  // now confirmed
                {
                    $action = true;
                    $change_txt = "duty confirmed";
                    $confirmed_code = "Y";
                    $counts['confirms']++;
                }
                elseif (!$dtm_arr[$k]['confirmed'] and $rm_arr[$k]['confirmed'])  // now unconfirmed
                {
                    $action = true;
                    $change_txt = "duty unconfirmed";
                    $confirmed_code = "N";
                    $counts['unconfirms']++;
                }

                if ($action)
                {
                    $chg_arr = array(
                        "dutyid"     => $rm_arr[$k]['did'],
                        "eventid"    => $dtm_arr[$k]['eid'],
                        "changetype" => "confirm",
                        "setclause"  => "`confirmed` = '$confirmed_code', `confirmed_date` = '$today'",
                        "info"       => "confirmation change [event $event - {$dtm_arr[$k]['code']} - $person ]  : $change_txt",
                        "status"     => "X"
                    );
                    // add change to t_dutysync and log it
                    $ins = $db_o->db_insert("t_dutysync", $chg_arr );
                    u_cronlog(" - ".$chg_arr['info']);
                }
            }

            // check 5 - if swap request status has changed -------------------------------------------
            if ($dtm_arr[$k]['swapwanted'] != $rm_arr[$k]['swapwanted'])
            {
                $action = false;
                if ($dtm_arr[$k]['swapwanted'] == $rm_arr[$k]['swapwanted'])     // no change
                {
                    $action = false;
                    $change_txt = "";
                    $swap_code = "N";
                }
                elseif ($dtm_arr[$k]['swapwanted'] and !$rm_arr[$k]['swapwanted'])  // swap now wanted
                {
                    $action = true;
                    $change_txt = "swap wanted";
                    $swap_code = "Y";
                    $counts['swapreq']++;
                }
                elseif (!$dtm_arr[$k]['swapwanted'] and $rm_arr[$k]['swapwanted'])  // swap no longer wanted
                {
                    $action = true;
                    $change_txt = "swap no longer wanted";
                    $swap_code = "N";
                    $counts['unswapreq']++;
                }

                if ($action)
                {
                    $chg_arr = array(
                        "dutyid"     => $rm_arr[$k]['did'],
                        "eventid"    => $dtm_arr[$k]['eid'],
                        "changetype" => "swap",
                        "setclause"  => "`swap_requested` = '$swap_code', `swap_request_date` = '$today'",
                        "info"       => "swap change [event $event - {$dtm_arr[$k]['code']} - $person] : $change_txt",
                        "status"     => "X"
                    );
                    // add change to t_dutysync and log it
                    $ins = $db_o->db_insert("t_dutysync", $chg_arr );
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
    $changes = $db_o->run("SELECT * FROM t_dutysync WHERE status = 'X' ORDER BY createdate ASC", array())->fetchall();

    $full_query = "";
    foreach ($changes as $change)
    {
        // create query
        $query = "UPDATE t_eventduty SET {$change['set_clause']} WHERE id = {$change['dutyid']} and eventid = {$change['eventid']}";
        $full_query.= $query."\n";
        // $upd = $db_o->run($query, array());  // fixme - just for debugging

        $upd = true;

        // mark changes as applied or failed
        if ($upd)
        {
            $upd = $db_o->run("UPDATE t_dutysync SET status = 'P' WHERE id = {$change['id']}", array());
        }
        else
        {
            $upd = $db_o->run("UPDATE t_dutysync SET status = 'F' WHERE id = {$change['id']}", array());
            u_cronlog(" - event duty change in RM FAILED: {$change['info']}\n");
        }
    }
    u_cronlog("Changes applied:\n".$full_query);

    return $status;
}

function notify_rota_managers()
{
    // create daily message for emailing to rota manager

    global $db_o;

    $rept = "";
    $notes = "";
    $changes = array();

//    if (date("N", $today) == 5)  // day is Friday
//    {
//        $start = date('Y-m-d', strtotime('-1 week'));
//        $end = $today;
//        $query = "SELECT * FROM t_dutysync
//                  WHERE (status IN ('P','F') OR `changetype` = 'note' ) AND (createdate >= '$start' AND createdate <= $end)";
//        $changes   = $db_o->run($query, array())->fetchAll();
//    }

    $query = "SELECT * FROM t_dutysync WHERE (status IN ('P','F') OR `changetype` = 'note' ) AND (createdate >= CURDATE())";
    $changes = $db_o->run($query, array())->fetchAll();


    foreach ($changes as $change)
    {
        if ($change('changetype') == "note")
        {
            $notes.= " - NEEDS REVIEW: {$change['info']} ".date("d-m-Y", $change['createdate']."<br>");
        }
        else
        {
            $rept.= " - CHANGE:  {$change['info']} ".date("d-m-Y", $change['createdate']."<br>");
        }
    }

    $rept.= "<br><br>$notes";

    return $rept;
}

function keys_to_array($keys, $data)
{
    // creates new array by selecting keys from data array
    $arr = array();
    foreach ($keys as $key)  { $arr[] = $data[$key]; }
    return $arr;
}

function send_email($email_from, $emails_to, $subject, $html, $api_key)
{
    $status = true;

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
    curl_close($ch);

    if (curl_errno($ch))
    {
        echo 'Error:' . curl_error($ch);
        $status = false;
        u_cronlog("Summary email for rotamanagers failed ".$result);
    }

    return $status;
}

function mres($value)
    // escapes string for use in sql
{
    $search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

    return str_replace($search, $replace, $value);
}
