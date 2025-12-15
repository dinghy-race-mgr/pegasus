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
//TODO - sort out dryrun (collects differences - does not apply to Rm database - reports them as a batch in logging)
//DONE - add logging in duty_compare - just changes
//TODO - fix database connections to use both dutyman and racemanager databases
//TODO - switch sql in get_member_lookup
//TODO - switch sql in get_dtm_duty_arr
//TODO - add status return to duty_compare
//TODO - add logging in apply_changes
//TODO - add status return to apply_changes
//TODO - repost the programme (need to turn of reporting - replace with cronlog output)
//TODO - consolidate cronlog output to exception report for rota managers
//TODO - get switching of database to work when updating racemanager
//TODO - get $today value sorted out

require_once("../common/lib/util_lib.php");
require_once("../common/classes/db.php");

// setup
$today = "2026-01-23"; // FIXME  date("Y-m-d")
$dryrun = false;
if (key_exists("dryrun", $_REQUEST))
{
    $dryrun = filter_var($_REQUEST['dryrun'], FILTER_VALIDATE_BOOLEAN);
}

$cfg = u_set_config("../config/common.ini", array(), false);
//echo "<pre>".print_r($cfg,true)."</pre>";


// logging - start process (appending to cronlog)
u_cronlog("\n--- DUTYMAN CONFIRM CHECK - start");
u_cronlog("\n- starting analysis for events from $today onwards");

// open database connection for racemanager
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// open database connection for dutyman
// fixme - swap these back
$dbt_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);
//$dbt_o = new DB($cfg['dtm_name'], $cfg['dtm_user'], $cfg['dtm_pass'], $cfg['dtm_host']);

$continue = true;

// ---------- get lookup table to convert dutyman rota names to racemanager rota codes

// get rota code lookup map
$rota = get_rota_lookup();
if (empty($rota))
{
    $continue = false;
    u_cronlog("\n- rota code lookup not found in raceManager - ** end processing");
}

// ---------- get dutyman information for each member that has been allocated a duty

if ($continue)
{
    $members = get_member_lookup();
    if (empty($members))
    {
        $continue = false;
        u_cronlog("\n- members information not found in dutyman - ** end processing");
    }
    else
    {
        u_cronlog("\n- ".count($members)." members records found in dutyman ");
    }
}

// ---------- get dutyman duties for each event in the standard structure

if ($continue)
{
    // get dtm duties in standard structure
    $dtm_duties = get_dtm_duty_arr($today);
    if (empty($dtm_duties))                     // no duties -stop processing
    {
        $continue = false;
        u_cronlog("\n- duty information not found in dutyman - ** end processing");
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
        u_cronlog("\n- ".count($dtm_duties)." duties retrieved by dutyman duties ");
        u_cronlog("\n- ".count($events)." events covered by dutyman duties ");
    }
}

// ---------- get racemanager duties for each event in the standard structure

if ($continue)
{
    // get racemanager duties in standard structure
    $rm_duties = get_rm_duty_arr($today);
    if (empty($rm_duties))
    {
        $continue = false;
        u_cronlog("\n- duty information not found in raceManager - ** end processing");
    }
    else
    {
        u_cronlog("\n- ".count($rm_duties)." duty records found in raceManager ");
    }
}

// ---------- compare the duty specification for dutyman and racemanager for each event

if ($continue)
{
    $status = duty_compare($events, $dtm_duties, $rm_duties);   // needs to log each identified change
}

// ---------- apply differences between dutyman and racemanager duties to the racemanager database (t_eventduty)

if ($continue)
{
    if ($dryrun)
    {
        u_cronlog("\n- dryrun mode - changes not applied to racemanager event duty records ");
    }
    else
    {
        $status = apply_changes();     // needs to log each change
    }
}

// ---------- republish the website programme

if ($continue)
{
// website_publish.php?pagestate=submit&
// not sending date-start or date-end - uses defaults relative to today.
}





// logging end of process
u_cronlog("\n--- DUTYMAN STATUS SYNCH - end");

// -----------------------------------------------------------------------------------------------

function get_rota_lookup()
{
    global $db_o;

    $rotas = $db_o->run("select `code`, `label` from t_code_system WHERE groupname='rota_type' ORDER BY code ASC", array() )->fetchall();
    foreach($rotas as $rota)
    {
        $dutycodes["{$rota['label']}"] = $rota['code'];
    }

    //echo "<pre>ROTA CODES\n".print_r($dutycodes,true)."</pre>";
    return $dutycodes;
}

function get_member_lookup()
{
    global $dbt_o;

    $arr = array();

    // get member info - fixme change SQL to look in t_rotamember
    $members = $dbt_o->run("select * from z_members ORDER BY `Last Name` ASC", array() )->fetchall();
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

function get_dtm_duty_arr($today)
{
    global $dbt_o;
    global $rota, $members;

    // get duty data - fixme change SQL to use t_eventduty
    $arr = array();
    $err = array();
    
    $records = $dbt_o->run("select * from z_duties WHERE `Duty Date` >= '$today' ORDER BY `Duty Date` ASC", array())->fetchall();

    foreach ($records as $record)
    {
        echo "<pre>".print_r($record,true)."</pre>";
        
        // decode notes to get event id
        if (!empty($record['Notes'])) {
            if (strpos($record['Notes'], "=") !== false) 
            {
                parse_str($record['Notes'], $n_data);
                echo "<pre>".print_r($n_data,true)."</pre>";
            } 
            else 
            {
                //fixme what happens if no id
                $err[] = array("date" => $record['Duty Date'], "event" => $record['Event'],
                    "rota" => $rota["{$record['Duty Type']}"], "person" => "{$record['First Name']} {$record['Last Name']}");
            }
        }

        // get event name (removing tide stuff)
        $tide_pos = strpos($record['Event'], "[");
        $tide_pos === false ? $event_name = $record['Event'] : $event_name = strstr($record['Event'], '[', true);

        // get webcollect member id - stored in first line of address
        // fixme - could this be more efficient (query on last name and then search on first name in results
        foreach ($members as $k=>$member)
        {
            $member_index = $k;
            if ($member['Last Name'] == $record['Last Name'] AND $member['First Name'] == $record['First Name'])
            {
                break;
            }
        }

        //echo "<pre>INDEX = $member_index\n".print_r($members[$member_index],true)."</pre>";

        $rota_code = $rota["{$record['Duty Type']}"];

        $arr[] = array(
            "eid"        => $n_data['eid'],
            "date"       => $record['Duty Date'],
            "ename"      => $event_name,
            "code"       => $rota_code,
            "fn"         => $members[$member_index]['First Name'],
            "ln"         => $members[$member_index]['Last Name'],
            "memberid"   => $members[$member_index]['Address 1'],
            "dtm_login"  => $members[$member_index]['Member UID'],
            "confirmed"  => filter_var($record['Confirmed'], FILTER_VALIDATE_BOOLEAN),
            "swapwanted" => filter_var($record['Swap Wanted'], FILTER_VALIDATE_BOOLEAN),
            "swappable"  => filter_var($record['Swappable'], FILTER_VALIDATE_BOOLEAN),
            "reminders"  => filter_var($record['Reminders'], FILTER_VALIDATE_BOOLEAN)
        );

        // sort array by date, eid and rota code
        $date_sort = array_column($arr, 'date');
        $eid_sort = array_column($arr, 'eid');
        $code_sort = array_column($arr, 'code');
        $ln_sort = array_column($arr, 'ln');
        array_multisort($date_sort, SORT_ASC, $eid_sort, SORT_ASC, $code_sort, SORT_ASC, $ln_sort, SORT_ASC, $arr);
    }

    return $arr;
}

function get_rm_duty_arr($today)
{
    global $db_o;

    $arr = array();
    $err = array();

    // t_rotamember has firstname familyname memberid  dtm_login might need to use NOTES field to store link (can't use id)

    $query = "select a.id, b.id as dutyid, event_date, event_name, person, dutycode, confirmed, swap_requested, swapable
              FROM t_event as a JOIN t_eventduty as b ON a.id=b.eventid 
              WHERE `event_date` >= '$today' 
              LIMIT 10";   // fixme for limit
    $records = $db_o->run($query, array())->fetchall();

    //echo "<pre>RM DUTIES RAW\n".print_r($records,true)."</pre>";

    foreach ($records as $record)
    {
        // split person name into first and family name
        $names = u_split_name($record['person']);

        // get matching rotamember record
        $matches = $db_o->run("SELECT * FROM t_rotamember WHERE firstname = ? and familyname = ?", array($names['fn'],$names['fm']))->fetchall();

        $num_matches = count($matches);
        //echo "-- $num_matches match(es) found for {$record['person']}<br>";
        //echo "<pre>".print_r($matches,true)."</pre>";

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
            "reminders"  => ""
        );
    }

    // sort array by date, eid and rota code
    $date_sort = array_column($arr, 'date');
    $eid_sort = array_column($arr, 'eid');
    $code_sort = array_column($arr, 'code');
    $ln_sort = array_column($arr, 'ln');
    array_multisort($date_sort, SORT_ASC, $eid_sort, SORT_ASC, $code_sort, SORT_ASC, $ln_sort, SORT_ASC, $arr);

    //echo "<pre>RM DUTIES SORTED\n".print_r($arr,true)."</pre>";
    //echo "<pre>ERRORS\n".print_r($err,true)."</pre>";
    return $arr;
}


function duty_compare($events, $dtm_duty, $rm_duty)
{
    global $db_o;

    // get events from dutyman duties
    $ev_check = array();

   foreach ($events as $event)
   {
        global $today;

        $dtm_keys = u_2darray_search($dtm_duty, "eid", "$event");
        $dtm_arr  = keys_to_array($dtm_keys, $dtm_duty);

        $rm_keys  = u_2darray_search($rm_duty, "eid", "$event");
        $rm_arr   = keys_to_array($rm_keys, $rm_duty);

        // check 1 - RM and DTM have same no. of duties
        $dtm_keys_num = count($dtm_keys);
        $rm_keys_num = count($rm_keys);

        $dtm_keys_num == $rm_keys_num ? $status = 1 : $status = 0;
        $check_1 = array( "yearday"=>date("z"), "event"=> $event, "test"=>"1", "status"=>$status, "report"=>"");
        if (!$status)
        {
            u_cronlog("\n- PROBLEM [event $event] - dutyman [{$dtm_keys_num}] and raceManager [{$rm_keys_num}] have a different number of duties");
        }

        // check 2 - loop through dtm duties for this event and see if same person used twice
        $dtm_person_arr = array();
        foreach($dtm_keys as $key)
        {
           $person = trim($dtm_duty[$key]['fn'])." ".trim($dtm_duty[$key]['ln']);
           $dtm_person_arr[] = $person;
        }
        $temp_array = array_unique($dtm_person_arr);
        count($temp_array) != count($dtm_keys) ?  $status = 1 : $status = 0;
        $check_2 = array( "yearday"=>date("z"), "event"=> $event, "test"=>"2", "status"=>$status, "report"=>"");
        if (!$status)
        {
           u_cronlog("\n- PROBLEM [event $event] - dutyman has the same person [$dtm_duty[$key]['fn']] listed for more than one duty");
        }


        foreach ($dtm_arr as $k=>$duty)
        {
//            echo "<pre>".print_r($dtm_arr,true)."</pre>";
//            echo "<pre>".print_r($rm_arr,true)."</pre>";

            $diff = array_diff_uassoc($dtm_arr[$k], $rm_arr[$k], "key_compare_func");

            // review differences

            // check if name of person doing duty has changed
            if (key_exists('ln', $diff))
            {
                $txt = "changed from {$rm_arr[$k]['fn']} {$rm_arr[$k]['ln']} to {$dtm_arr[$k]['fn']} {$dtm_arr[$k]['ln']}";
                $ins = $db_o->insert("z_dutyupdate",
                    array("dutyid"=>$rm_arr[$k]['did'], "eventid"=>$dtm_arr[$k]['eid'], "changetype"=>"person",
                        "set_clause"=>"person = {$dtm_arr[$k]['fn']} {$dtm_arr[$k]['ln']}, `swap_request_date` = '$today'",
                        "info" => $txt) );
                u_cronlog("\n- person change [event $event] - {$dtm_arr[$k]['code']} duty: ]} $txt");

            }

            // check if confirmation status has changed
            if (key_exists('confirmed', $diff))
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
                }
                elseif (!$dtm_arr[$k]['confirmed'] and $rm_arr[$k]['confirmed'])  // now unconfirmed
                {
                    $action = true;
                    $change_txt = "duty not confirmed";
                    $confirmed_code = "N";
                }

                if ($action)
                {
                    $txt = "confirmed status change [event $event] - {$dtm_arr[$k]['code']} duty: $change_txt";
                    u_cronlog("\n- $txt");

                    $ins = $db_o->insert("z_dutyupdate",
                        array("dutyid"=>$rm_arr[$k]['did'], "eventid"=>$dtm_arr[$k]['eid'], "changetype"=>"confirm",
                            "set_clause"=>"`confirmed` = '$confirmed_code', `confirmed_date` = '$today'",
                            "info" => $txt) );
                }
            }

            // check if swap request status has changed
            if (array_key_exists('swapwanted', $diff))
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
                }
                elseif (!$dtm_arr[$k]['swapwanted'] and $rm_arr[$k]['swapwanted'])  // swap no longer wanted
                {
                    $action = true;
                    $change_txt = "swap no longer wanted";
                    $swap_code = "N";
                }

                if ($action)
                {
                    $txt = "swap status change [event $event] - {$dtm_arr[$k]['code']} duty: $change_txt";
                    u_cronlog("\n- $txt");
                    echo "<pre>$txt</pre>";

                $ins = $db_o->insert("z_dutyupdate",
                    array("dutyid"=>$rm_arr[$k]['did'], "eventid"=>$dtm_arr[$k]['eid'], "changetype"=>"swap",
                        "set_clause"=>"`swap_requested` = '$swap_code', `swap_request_date` = '$today'",
                         "info" => $txt) );
                }
            }
        }
   }
   return true;
}

function apply_changes()
{
    global $db_o;

    $status = true;

    // get changes from database  FIXME - needs rethink on how to pull specific changes
    $changes = $db_o->run("SELECT * FROM z_dutyupdate WHERE status = 'X'", array())->fetchall();

    $full_query = "";
    $notes = "";
    foreach ($changes as $change)
    {
        // create query
        $query = "UPDATE t_eventduty SET {$change['set_clause']} WHERE id = {$change['dutyid']} and eventid = {$change['eventid']}";
        $full_query.= $query."\n";
        //$upd = $db_o->run($query, array());
        $upd = true;
        if ($upd)
        {
            $upd = $db_o->run("UPDATE z_dutyupdate SET status = 'P' WHERE id = {$change['id']}", array());
            $notes.= "change completed: {$change['info']}\n"; // fixme this should be logging
        }
        else
        {
            $upd = $db_o->run("UPDATE z_dutyupdate SET status = 'F' WHERE id = {$change['id']}", array());
            $notes.= "change FAILED: {$change['info']}\n"; // fixme this should be logging
        }
    }
    echo "<pre>$full_query</pre>";
    echo "<pre>$notes</pre>";


    return $status;
}

function notify_rota_managers()
{
    // sends update on changes on daily basis // fixme should be on weekly basis

}

function keys_to_array($keys, $data)
{
    // creates new array by selecting keys from data array
    $arr = array();
    foreach ($keys as $key)  { $arr[] = $data[$key]; }
    return $arr;
}

function key_compare_func($a, $b)
{
    //call back function to compare array elements using array_diff_uassoc
    return $a <=> $b;
}