<?php

// ADMINISTRATION FUNCTIONS --------------------------------------------------------------------------------------------

function sailor_redirect($mode, $racelink, $cruiselink)
    // directs application to next page depending on race/cruise mode
{
    if ($mode == "race") {
        header("Location: $racelink");
    } else {
        header("Location: $cruiselink");
    }
    exit();
}

function set_page_options($page)
    // gets options that should be shown on menu for specified page
{
    $options = array();
    foreach ($_SESSION['options_map'][$page] as $opt) {
        if (array_key_exists($opt, $_SESSION['option_cfg'])) {
            $options[] = $_SESSION['option_cfg'][$opt];
        }
    }
    return $options;
}

function get_entry_information($sailorid, $events)
    // gets entry data using different functions for race and cruise mode
{
    if ($_SESSION['mode'] == "cruise") {
        $data = get_cruise_entry_data($sailorid, $events);
    } else {
        $data = get_race_entry_data($sailorid, $events);
    }

    return $data;
}

function add_auto_continue($usage, $delay, $external, $target_url)
    // adds an automatic return to the search page when usage mode is "multi"
    // delay before return is set in the rm_sailor_cfg.php file
{
    if ($usage == "multi") {          // multi user session
        $delay = $delay * 1000;       // convert delay to millisecs
        if ($external) {              // double it if coming from somewhere other than this page
            $delay = $delay * 2;
        }
    }

    // add jump to target page after delay
    $bufr = str_repeat("\n",4096);
    if ($delay > 0) {
        $bufr .= <<<EOT
            <script>
                $(document).ready(function () {
                window.setTimeout(function () {
                    location.replace('$target_url');
                }, $delay);
            });
            </script>
EOT;
    }

    return $bufr;
}

function check_valueinlist ($val, $list)
    // case insensitive check if value in list of values (in array)
{
    $result = false;
    $search_list = array_map('strtolower', $list);
    if (in_array(strtolower($val), $search_list)) {
        $result = true;
    }

    return $result;
}

function set_boat_details()
    // creates and updates sailor details
{
    if (empty($_SESSION['sailor']['change'])) {
        $boat = array(
            "id" => $_SESSION['sailor']['id'],
            "class" => $_SESSION['sailor']['classname'],
            "sailnum" => $_SESSION['sailor']['sailnum'],
            "helm" => $_SESSION['sailor']['helmname'],
            "crew" => $_SESSION['sailor']['crewname']
        );
    } else {
        $boat = array(
            "id" => $_SESSION['sailor']['id'],
            "class" => $_SESSION['sailor']['classname'],
            "sailnum" => u_pick($_SESSION['sailor']['chg-sailnum'], $_SESSION['sailor']['sailnum']),
            "helm" => u_pick($_SESSION['sailor']['chg-helm'], $_SESSION['sailor']['helmname']),
            "crew" => u_pick($_SESSION['sailor']['chg-crew'], $_SESSION['sailor']['crewname'])
        );
    }
    $boat['team'] = u_conv_team($boat['helm'], $boat['crew'], 0);

    return $boat;
}

// RACE FUNCTIONS ------------------------------------------------------------------------------------------------------


function map_fleet_cfg($format)
{
    global $event_o;
    $fleetcfg = $event_o->event_getfleetcfg($format);

    if (!$fleetcfg) {
        return false;
    } else {
        foreach ($fleetcfg as $j => $fleet) {
            // add to array
            $cfg[$fleet['fleet_num']] = array(
                "fleetnum"     => $fleet['fleet_num'],
                "startnum"     => $fleet['start_num'],
                "code"         => $fleet['fleet_code'],
                "name"         => $fleet['fleet_name'],
                "desc"         => $fleet['fleet_desc'],
                "scoring"      => strtolower($fleet['scoring']),
                "pytype"       => strtolower($fleet['py_type']),
                "warnsignal"   => $fleet['warn_signal'],
                "prepsignal"   => $fleet['prep_signal'],
                "timelimitabs" => $fleet['timelimit_abs'],
                "timelimitrel" => $fleet['timelimit_rel'],
                "defaultlaps"  => $fleet['defaultlaps'],
                "defaultfleet" => $fleet['defaultfleet'],
                "classinc"     => $fleet['classinc'],
                "onlyinc"      => $fleet['onlyinc'],
                "classexc"     => $fleet['classexc'],
                "groupinc"     => $fleet['groupinc'],
                "minpy"        => $fleet['min_py'],
                "maxpy"        => $fleet['max_py'],
                "crew"         => $fleet['crew'],
                "spintype"     => $fleet['spintype'],
                "hulltype"     => $fleet['hulltype'],
                "minhelmage"   => $fleet['min_helmage'],
                "maxhelmage"   => $fleet['max_helmage'],
                "minskill"     => $fleet['min_skill'],
                "maxskill"     => $fleet['max_skill']
            );
        }
    }

    return $cfg;
}


function get_fixed_event($eventid_list)
{
    global $event_o;
    $err = false;

    $data = array("mode" => "fixed", "numevents" => 0, "numdays"=> "",  "eventday" => "",
                  "nextevent" => array(), "details" => array());

    if (!empty($eventid_list)) // if event list has values - get details for those events
    {
        $num_days = 0;
        $day = "";
        foreach ($eventid_list as $k => $id) {
            $rs = $event_o->get_event_byid($id, "racing");   // get event if it is a racing event

            if ($day != $rs['event_date'] ) { $num_days++; }

            if ($rs) {
                $data['details'][$rs['id']] = $rs;
                $cfg = map_fleet_cfg($rs['event_format']);   // get fleet configurations for this event
                if ($cfg) {
                    $data['details'][$rs['id']]['fleetcfg'] = $cfg;
                } else {
                    $err = true;
                }
            }
            $day = $rs['event_date'];
        }
    }

    $err ? $data['numevents'] = -1 : $data['numevents'] = count($data['details']);

    $data['numdays'] = $num_days;
    if ($num_days == 1) { $data['eventday'] = $day; }

    // get next event details
    $rs = $event_o->get_nextevent(date("Y-m-d"), "racing");
    $rs ? $data['nextevent'] = $rs : $data['nextevent'] = array();
    
    //echo "<pre>".print_r($data,true)."</pre>";

    return $data;
}

function get_dated_event($future_window)
{
    global $event_o;
    $err = false;
    $today = date("Y-m-d");
    $data = array("mode" => "dated", "numevents" => 0, "numdays" => 1, "eventday"=> "", "nextevent" => array(), "details" => array());

    // check if any race events today and the next event
    $_SESSION['demo']=="demo" ? $status = "demo" : $status = "active";
    $rs = $event_o->get_events("racing", $status, array("start"=>$today, "end"=>$today));
    //echo "<pre> RS: ".print_r($rs,true)."</pre>";

    $nrs = $event_o->get_nextevent(date("Y-m-d"), "racing");
    //echo "<pre> NRS: ".print_r($rs,true)."</pre>";
    if ($nrs) { $data['nextevent'] = $nrs; }

    // if events today - set race day date as today
    if ($rs) {
        $data['event_day'] = date("Y-m-d");
    }
    // look for next event
    else {
        $next_event_day = "";
        if ($nrs) {
            $next_event_day = date("Y-m-d", strtotime($nrs['event_date']));
        }

        // if I don't have an $event_day set and future events are permitted - check if next event lies in permitted window
        if (empty($data['event_day']) and !empty($next_event_day) and $future_window > 0) {
            $diffdays = u_daysdiff(date("Y-m-d"), $next_event_day);
            //echo "<pre>|$diffdays|</pre>";
            if ($diffdays <= $future_window) {
                $data['event_day'] = $next_event_day;
                $rs = $event_o->get_events_bydate($data['event_day'], $_SESSION['demo'], "racing");
            }
        }
    }

    if ($rs) {
        // get event configurations
        foreach ($rs as $k => $event) {
            $data['details'][$event['id']] = $event;
            $cfg = map_fleet_cfg($event['event_format']);   // get fleet configurations for this event
            if ($cfg) {
                $data['details'][$event['id']]['fleetcfg'] = $cfg;
            } else {
                $err = true;
            }
        }
    }

    if ($err) {
        $data['numevents'] = -1;
    } else {
        $data['numevents'] = count($data['details']);
    }

    return $data;
}

function get_event_details($event_window, $eventid_list = array())
    // gets racing event details from database (t_event)
{
//    global $event_o;

//  event information held in array
//       numevents - number of racing events either scheduled for today or specified as a parameter
//       nextevent - details of the next event if there are none today or specified
//       details   - 2D array with 1 array for each event.  The event includes key information for the
//                   the event , and details of the configuration for each fleet in the event.
//
//

    if (!empty($eventid_list)) { // get events from contents of event list
        $events = get_fixed_event($eventid_list);
    } else { // get today's events - if none today look for next days events in event window
        $events = get_dated_event($event_window);
    }

    return $events;
}

function get_race_entry_data($sailorid, $events)
    // gets details on sailor with regard to today's races using the t_entry table
{
    global $db_o;

    $data = array();

    // loop over events
    foreach ($events as $eventid=>$event) {

        $entry_o = new ENTRY($db_o, $eventid);
        // get fleet allocation for this sailor
        //$alloc = $entry_o->allocate($_SESSION['sailor']);
        $alloc = r_allocate_fleet($_SESSION['sailor'], $events[$eventid]['fleetcfg']);

        $data[$eventid] = array(
            "sailorid" => $sailorid,
            "event-name" => $event['event_name'],
            "start-time" => $event['event_start'],
            "allocate" => $alloc,
            "entered" => false,
            "updated" => false,
            "declare" => "",
            "protest" => false,
            "event-status" => $event['event_status']
        );

        // check position or code in race
        $race = $entry_o->get_by_compid($sailorid);
        $data[$eventid]['position'] = "unknown";
        if (empty($race['code'])) {
            if ($race['points'] > 0 AND !empty($race['points'])) {
                $data[$eventid]['position'] = u_numordinal($race['points']);
            }
        } else {
            $data[$eventid]['position'] = $race['code'];
        }

        // get all records from t_entry for this competitor and this event - in ascending time order
        $records = $entry_o->get_signon($eventid, $sailorid);

        if ($records) {
            // loop through t_entry records
            $count = 0;

            foreach ($records as $k => $r) {
                if ($r['action'] == "enter") {
                    $data[$eventid]['entered'] = true;

                } elseif ($r['action'] == "update") {
                    $data[$eventid]['updated'] = true;
                    $count++;

                } elseif ($r['action'] == "declare" or $r['action'] == "retire") {
                    $data[$eventid]['declare'] = $r['action'];
                    if ($r['protest']) {
                        $data[$eventid]['protest'] = true;
                    }
                }
            }
            $data[$eventid]['update_num'] = $count;
        }
    }

    return $data;
}

function set_event_status_list($events, $entries, $action = array())
    // set race status for each event (for current sailor)
{
    $evstatuscode = array("scheduled" => 1, "selected" => 2,"running" => 3,
        "sailed" => 4, "complete" => 5,"abandoned" => 6, "cancelled" => 7,);

    $event_arr = array();

    foreach ($events as $eventid => $event) {
        $entry_status = "";
        if ($entries[$eventid]['declare'] == "retire") {
            $entry_status = "retired";
        } elseif ($entries[$eventid]['declare'] == "declare") {
            $entry_status = "signed off";
        } elseif ($entries[$eventid]['entered']) {
            $entries[$eventid]['updated'] ? $entry_status = "updated" : $entry_status = "entered";
        } else // not entered
        {
            if (!$entries[$eventid]['allocate']['status']) // problem with allocating
            {
                if ($entries[$eventid]['allocate']['alloc_code'] == "E") {
                    $entry_status = "not eligible";
                } elseif ($entries[$eventid]['allocate']['alloc_code'] == "X") {
                    $entry_status = "class not recognised";
                }
            }
        }

        $entry_alert = "";
        if (!empty($action)) {
            if ($action['event'] == $eventid) {
                if ($action['status'] == "err") {
                    $entry_alert = "FAILED - {$action['msg']}";
                }
            }
        }

        $status_map = array(
            "scheduled" => "not started",
            "selected" => "not started",
            "running" => "in progress",
            "sailed" => "finishing",
            "complete" => "complete",
            "abandoned" => "abandoned",
            "cancelled" => "cancelled",
        );

        if (key_exists($event['event_status'], $status_map)) {
            $txt = $status_map[$event['event_status']];
        } else {
            $txt = "unknown";
        }

        $event_arr[$eventid] = array(
            "name" => $event['event_name'],
            "date" => $event['event_date'],
            "time" => $event['event_start'],
            "start" => $entries[$eventid]['allocate']['start'],
            "signon" => $event['event_entry'],
            "entry-status" => $entry_status,
            "entry-updated" => $entries[$eventid]['updated'],
            "entry-alert" => $entry_alert,
            "event-status" => $event['event_status'],
            "event-status-txt" => $txt,
            "event-status-code" => $evstatuscode[$event['event_status']]
        );

        key_exists("update_num", $entries[$eventid]) ? $event_arr[$eventid]['update-num'] = $entries[$eventid]['update_num']
                    : $event_arr[$eventid]['update-num'] = 0;
    }
    return $event_arr;
}


function set_result_data($sailorid, $events)
    // get results for current sailor in all races today
{
    global $db_o;

    $arr = array();

    foreach ($events as $eventid => $event) {
        $entry_o = new ENTRY($db_o, $eventid);
        // $alloc = $entry_o->allocate($_SESSION['sailor']);
        $alloc = r_allocate_fleet($_SESSION['sailor'], $events[$eventid]['fleetcfg']);
        $fleet = $alloc['fleet'];

        $arr['list'][$eventid] = array(
            "event-name"  => $event['event_name'],
            "event-date"  => $event['event_date'],
            "event-start" => $event['event_start'],
            "fleet-name"  => $event['fleetcfg'][$fleet]['name'],
            "race-type"   => $event['fleetcfg'][$fleet]['scoring']
        );

        $arr['list'][$eventid]['racestate'] = "unknown";
        $race_o = new RACE($db_o, $eventid);
        $rstate_rst = $race_o->racestate_get($fleet);
        if ($rstate_rst) {
            $arr['list'][$eventid]['racestate'] = $rstate_rst[$fleet]['status'];
        }

        $result_o = new RACE_RESULT($db_o, $eventid);
        $fleet_rst = $result_o->get_race_results($fleet);

        $arr['list'][$eventid]['position'] = "?";
        foreach ($fleet_rst as $rst) {
            $arr['data'][$eventid][] = array(
                "compid" => $rst['competitorid'],
                "position" => $rst['result'],
                "class" => $rst['class'],
                "sailnum" => $rst['sailnum'],
                "team" => $rst['team'],
                "laps" => $rst['lap'],
                "etime" => $rst['etime'],
                "atime" => $rst['atime']
            );

            if ($sailorid == $rst['competitorid']) {
                $arr['list'][$eventid]['position'] = $rst['result'];
            }
        }
    }

    return $arr;
}


function get_race_entries($sailorid, $today)
    // get last entry for current sailor in t_entry
{
    global $db_o;

    $detail = array();
    $query = "SELECT * FROM t_entry WHERE DATE(`upddate`) = '$today' AND competitorid = $sailorid ORDER BY upddate DESC LIMIT 1";
    $detail = $db_o->db_get_row($query);

    if (empty($detail)) {
        return false;
    } else {
        return $detail;
    }
}

function process_signon($eventid)
    // processes action of sign on for an event on the race control page
{
    global $db_o, $loc;

    $entry = $_SESSION["entries"][$eventid];

    // add to entry table
    $entry_o = new ENTRY($db_o, $eventid);
    $status = $entry_o->add_signon($_SESSION['sailor']['id'], $entry['allocate']['status'],
        $_SESSION['sailor']['chg-helm'], $_SESSION['sailor']['chg-crew'], $_SESSION['sailor']['chg-sailnum'], "rm_sailor");

    if ($status == "update" OR $status == "enter") {
        empty($_SESSION['sailor']['chg-helm']) ? $chg_helm = "" : $chg_helm = "*";
        empty($_SESSION['sailor']['chg-crew']) ? $chg_crew = "" : $chg_crew = "*";
        empty($_SESSION['sailor']['chg-sailnum']) ? $chg_sailnum = "" : $chg_sailnum = "*";
        u_writelog("event $eventid | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} -> $chg_sailnum | {$_SESSION['sailor']['helmname']} -> $chg_helm | {$_SESSION['sailor']['crewname']} -> $chg_crew | $status", "");
        $success = true;
    } else {
        u_writelog("event $eventid | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} | entry failed [reason: $status]", "");
        $success = false;
    }

    // update competitor record
    if ($success)
    {
        require_once ("{$loc}/common/classes/comp_class.php");
        $comp_o = new COMPETITOR($db_o);
        $upd = $comp_o->comp_updatecompetitor($_SESSION['sailor']['id'], array("last_entry"=> date("Y-m-d")), "rm_sailor" );
    }

    return $success;
}

function process_declare($eventid)
    // processes action of declaration for an event on the race control page
{
    global $db_o;

    // update entry array
    $_SESSION['entries'][$eventid]['declare'] =  "declare";

    // add record to entry table to record declaration
    $entry_o = new ENTRY($db_o, $eventid);
    $status = $entry_o->add_declare($_SESSION['sailor']['id']);

    if ($status == "declare") {
        // create log record
        u_writelog("event $eventid | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} | declared", "");
        $success = true;
    } else {
        // create log record of failure
        u_writelog("event $eventid | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} | declare FAILED", "");
        $success = false;
    }

    return $success;
}

function process_retire($eventid)
    // processes action of retirement for an event on the race control page
{
    global $db_o;

    // update entry array
    $_SESSION['entries'][$eventid]['declare'] =  "retire";

    // add record to entry table to record declaration
    $entry_o = new ENTRY($db_o, $eventid);
    $status = $entry_o->add_retire($_SESSION['sailor']['id']);

    if ($status == "retire") {
        // create log record
        u_writelog("event $eventid | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} | retired", "");
        $success = true;
    } else {
        // create log record of failure
        u_writelog("event $eventid | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} | retirement FAILED", "");
        $success = false;
    }

    return $success;
}



// CRUISE FUNCTIONS ----------------------------------------------------------------------------------------------------

function get_cruise_details($eventtypes, $addevent = true)
    // gets cruising event details from database (t_event)
{
    global $event_o;

//  event information held in array
//       numevents - number of cruising events either scheduled for today or specified as a parameter
//       nextevent - <not used> - empty array
//       details   - 2D array with 1 array for each event.  The event includes key information for the
//                   the event , and details of the configuration for each fleet in the event.  If $addevent
//                   is true then add a dummy event for free cruising.

    $data = array("numevents" => 0, "nextevent" => array(), "details" => array(), );

    if ($addevent) {
        // create dummy event for free sailing
        $data['details'][0] = array(
            "event_date" => date("Y-m-d"),
            "event_start" => date("H:i"),
            "event_name" => "personal cruising",
            "event_type" => "individual",
            "event_format" => "",
            "event_status" => "scheduled",
            "event_entry" => "signon",
            "event_open" => "club",
            "tide_time" => "",
            "tide_height" => "",
            "event_notes" => "",
        );
    }

    // add any cruising events for today in the programme
    $rs = $event_o->get_events_bydate(date("Y-m-d"),$_SESSION['demo'], $eventtypes);

    if ($rs) {
        $i = 0;
        foreach ($rs as $k => $detail) {
            $i++;
            // check if this event type is configured for the cruising mode
            //if (strpos($eventtypes, $detail['event_type']) !== false) {
            $data['details'][$i] = array(
                "event_date" => $detail['event_date'],
                "event_start" => $detail['event_start'],
                "event_name" => $detail['event_name'],
                "event_type" => $detail['event_type'],
                "event_format" => $detail['event_type'],
                "event_status" => $detail['event_status'],
                "event_entry" => $detail['event_entry'],
                "event_open" => $detail['event_open'],
                "tide_time" => $detail['tide_time'],
                "tide_height" => $detail['tide_height'],
                "event_notes" => $detail['event_notes'],
            );
            //}
        }
    }

    // get number of events
    if ($data['details']) { $data['numevents'] = count($data['details']); }

    return $data;
}

function get_cruise_entry_data($sailorid, $events)
    // gets details on sailor with regard to today's cruises using the t_cruise table
{
    global $db_o;
    $data = array();

    // loop over events - including free sailing (eventid = 0)
    foreach ($events as $eventid => $event) {
        $cruise_o = new CRUISE($db_o, date("Y-m-d"));

        $data[$eventid] = array(
            "sailorid" => $sailorid,
            "event-name" => $event['event_name'],
            "start-time" => $event['event_start'],
            "entered" => false,
            "updated" => false,
            "declare" => false,
            "start-cruise" => "",
            "end-cruise" => "",
        );

        $cruise = $cruise_o->get_cruise($event['event_type'], $sailorid);
        // loop through t_cruise records
        if ($cruise) {
            if ($cruise['action'] == "register") {
                $data[$eventid]['entered'] = true;
            } elseif ($cruise['action'] == "update") {
                $data[$eventid]['updated'] = true;
            } elseif ($cruise['action'] == "declare") {
                $data[$eventid]['declare'] = true;
            }
            $data[$eventid]['start-cruise'] = date("H:i", strtotime($cruise['time_in']));
            $data[$eventid]['end-cruise'] = date("H:i", strtotime($cruise['time_out']));
        }
    }

    return $data;
}

function set_cruise_status_list($events, $entries, $action = array())
    // set cruise status for each event (for current sailor)
{
    $event_arr = array();

    foreach ($events as $eventid => $event) {
        $entry_status = "";

        if ($entries[$eventid]['declare'] == "declare") {
            $entry_status = "returned";
        } elseif ($entries[$eventid]['updated']) {
            $entry_status = "updated";
        } elseif ($entries[$eventid]['entered']) {
            $entry_status = "registered";
        }

        $entry_alert = "";
        if (!empty($action)) {
            if ($action['event'] == $eventid AND $action['status'] == "err") {
                $entry_alert = "FAILED - {$action['msg']}";
            }
        }

        $event_arr[$eventid] = array(
            "name" => $event['event_name'],
            "time" => $event['event_start'],
            "signon" => $event['event_entry'],
            "cruise-type" => $event['event_type'],
            "event-status" => $event['event_status'],
            "entry-status" => $entry_status,
            "entry-updated" => $entries[$eventid]['updated'],
            "entry-alert" => $entry_alert,
            "time_in" => $entries[$eventid]['start-cruise'],
            "time_out" => $entries[$eventid]['end-cruise']
        );

    }
    return $event_arr;
}


function process_cruise_signon($cruise_type, $sailor)
    // processes action of register for sailor on a cruise
{
    global $db_o;
    global $date;

    // add to entry table
    $cruise_o = new CRUISE($db_o, $date);
    $status = $cruise_o->add_cruise($cruise_type, $sailor);

    if ($status == "update" OR $status == "register") {
        u_writelog("cruise: $cruise_type $date| {$_SESSION['sailor']['classname']} 
        | {$_SESSION['sailor']['sailnum']} | {$_SESSION['sailor']['helmname']}  
        | {$_SESSION['sailor']['crewname']} | $status", "");
        $success = $status;
    } else {
        u_writelog("cruise: $cruise_type $date | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} | registration failed [reason: $status]", "");
        $success = false;
    }

    return $success;
}


function process_cruise_declare($cruise_type, $eventid)
    // processes action of return for sailor on a cruise
{
    global $db_o;
    global $date;

    // update entry array
    $_SESSION['entries'][$eventid]['declare'] =  "declare";

    // add record to entry table to record declaration - replacing original record
    $entry_o = new CRUISE($db_o, $date);
    $status = $entry_o->end_cruise($_SESSION['sailor']['id'], $cruise_type);
    if ($status) {
        // create log record
        u_writelog("cruise: $cruise_type $date | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} | return declared", "");
        $success = "declare";
    } else {
        // create log record of failure
        u_writelog("cruise: $cruise_type $date | {$_SESSION['sailor']['classname']} | {$_SESSION['sailor']['sailnum']} -> {$_SESSION['sailor']['chg-sailnum']} | return declaration FAILED", "");
        $success = false;
    }

    return $success;
}
