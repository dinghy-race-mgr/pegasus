<?php

function sailor_redirect($mode, $racelink, $cruiselink)
{
    if ($mode == "race") {
        header("Location: $racelink");
    } else {
        header("Location: $cruiselink");
    }
    exit();
}

function set_page_options($page)
{
    $opt_map = get_options_map($page);
    $options = array();
    foreach ($opt_map as $opt) {
        if (array_key_exists($opt, $_SESSION['option_cfg'])) {
            $options[] = $_SESSION['option_cfg'][$opt];
        }
    }
    return $options;
}

function get_cruise_details($eventtypes, $addevent = true)
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
    $rs = $event_o->event_getevents(array("event_date"=>date("Y-m-d")),$_SESSION['demo'], false);

    if ($rs) {
        $i = 0;
        foreach ($rs as $k => $detail) {
            $i++;
            // check if this event type is configured for the cruising mode
            if (strpos($eventtypes, $detail['event_type']) !== false) {
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
            }
        }
    }

    // get number of events
    if ($data['details']) { $data['numevents'] = count($data['details']); }

    return $data;
}


function get_event_details($eventid_list = array())
{
    global $event_o;

//  event information held in array
//       numevents - number of racing events either scheduled for today or specified as a parameter
//       nextevent - details of the next event if there are none today or specified
//       details   - 2D array with 1 array for each event.  The event includes key information for the
//                   the event , and details of the configuration for each fleet in the event.
//
//

    $data = array("numevents" => 0, "nextevent" => array(), "details" => array(), );

    if (!empty($eventid_list)) {                         // if event id has values - get details for those events
        foreach ($eventid_list as $k => $id) {
            $rs = $event_o->event_getevent($id, true);   // get event if it is a racing event
            if ($rs) {
                $data['details'][$rs['id']] = $rs;
            }
        }
    } else {                                              // else get event details for all races today
        $rs = $event_o->event_getevents(array("event_date" => date("Y-m-d")), $_SESSION['demo'], true);
        if ($rs) {
            foreach ($rs as $k => $detail) {
                $data['details'][$detail['id']] = $detail;
            }
        }
    }

    // get fleet info for each event
    $error = false;
    foreach ($data['details'] as $k => $event) {
        // get fleet details for this event
        $fleetcfg = $event_o->event_getfleetcfg($event['event_format']);

        if (!$fleetcfg) {
            $error = true;
        } else {
            foreach ($fleetcfg as $j => $fleet) {
                // add to array
                $data['details'][$k]['fleetcfg'][$fleet['fleet_num']] = array(
                    "fleetnum" => $fleet['fleet_num'],
                    "startnum" => $fleet['start_num'],
                    "code" => $fleet['fleet_code'],
                    "name" => $fleet['fleet_name'],
                    "desc" => $fleet['fleet_desc'],
                    "scoring" => strtolower($fleet['scoring']),
                    "pytype" => strtolower($fleet['py_type']),
                    "warnsignal" => $fleet['warn_signal'],
                    "prepsignal" => $fleet['prep_signal'],
                    "timelimitabs" => $fleet['timelimit_abs'],
                    "timelimitrel" => $fleet['timelimit_rel'],
                    "defaultlaps" => $fleet['defaultlaps'],
                    "defaultfleet" => $fleet['defaultfleet'],
                    "classinc" => $fleet['classinc'],
                    "onlyinc" => $fleet['onlyinc'],
                    "classexc" => $fleet['classexc'],
                    "groupinc" => $fleet['groupinc'],
                    "minpy" => $fleet['min_py'],
                    "maxpy" => $fleet['max_py'],
                    "crew" => $fleet['crew'],
                    "spintype" => $fleet['spintype'],
                    "hulltype" => $fleet['hulltype'],
                    "minhelmage" => $fleet['min_helmage'],
                    "maxhelmage" => $fleet['max_helmage'],
                    "minskill" => $fleet['min_skill'],
                    "maxskill" => $fleet['max_skill']
                );
            }
        }
    }

    if ($error) {
        $data['numevents'] = -1;
    } else {
        // get number of events
        if ($data['details']) {
            $data['numevents'] = count($data['details']);
        }

        // get details of next event
        $rs = $event_o->event_getnextevent(date("Y-m-d"));
        if ($rs) {
            $data['nextevent'] = $rs;
        }
    }

    return $data;
}

function get_entry_information($sailorid, $events)
{
    //Gets entry status by parsing the content of the t_entry table.
    if ($_SESSION['mode'] == "cruise") {
        $data = get_cruise_entry_data($sailorid, $events);
    } else {
        $data = get_race_entry_data($sailorid, $events);
    }

    return $data;
}

function get_cruise_entry_data($sailorid, $events)
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
            "declare" => false
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

//            foreach ($cruises as $k => $r) {
//                if ($r['action'] == "register") {
//                    $data[$eventid]['entered'] = true;
//                } elseif ($r['action'] == "update") {
//                    $data[$eventid]['updated'] = true;
//                } elseif ($r['action'] == "declare") {
//                    $data[$eventid]['declare'] = true;
//                }
//            }
        }
    }

    return $data;
}

function get_race_entry_data($sailorid, $events)
{
    global $db_o;

    $data = array();

    // loop over events
    foreach ($events as $eventid=>$event) {
        $entry_o = new ENTRY($db_o, $eventid, $events[$eventid]);
        // get fleet allocation for this sailor
        $alloc = $entry_o->allocate($_SESSION['sailor']);

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
            foreach ($records as $k => $r) {
                if ($r['action'] == "enter") {
                    $data[$eventid]['entered'] = true;
                } elseif ($r['action'] == "update") {
                    $data[$eventid]['updated'] = true;
                } elseif ($r['action'] == "declare" or $r['action'] == "retire") {
                    $data[$eventid]['declare'] = $r['action'];
                    if ($r['protest']) {
                        $data[$eventid]['protest'] = true;
                    }
                }
            }
        }
    }
    return $data;
}

function get_options_map($page)
    // FIXME do I still need this
{
    $opt_map = array(
        "boatsearch" => array("addboat"),
        "pickboat"   => array("boatsearch", "addboat"),
        "race"       => array("boatsearch", "editboat", "rememberme"),
        "cruise"     => array("boatsearch", "editboat", "rememberme"),
        "addboat"    => array("boatsearch"),
        "change"     => array(),
        "editboat"   => array("boatsearch"),
        "results"    => array("boatsearch"),
        "protest"    => array("boatsearch")
    );

    return $opt_map["$page"];
}

function get_options_arr()
{
// setting available options  FIXME is this still needed
    $options = array();

    $options['boatsearch'] = array("label" => "Search Boats", "url" => "boatsearch_pg.php", "tip" => "", "active" => true);
    $options['race']       = array("label" => "Sign On", "url" => "race_pg.php", "tip" => "",  "active" => $_SESSION['sailor_race']);
    $options['cruise']     = array("label" => "Sign Off/Retire", "url" => "signoff_pg.php", "tip" => "",  "active" => $_SESSION['sailor_cruise']);
    $options['addboat']    = array("label" => "Add New Boat", "url" => "addboat_pg.php", "tip" => "",  "active" => $_SESSION['sailor_addboat']);
    $options['editboat']   = array("label" => "Change Boat Details", "url" => "editboat_pg.php", "tip" => "",  "active" => $_SESSION['sailor_editboat']);
    $options['results']    = array("label" => "Get Results", "url" => "results_pg.php", "tip" => "",  "active" => $_SESSION['sailor_results']);
    $options['protest']    = array("label" => "Submit Protest", "url" => "protest_pg.php", "tip" => "",  "active" => $_SESSION['sailor_protest']);
    $options['hideboat']   = array("label" => "Hide Boat", "url" => "hideboat_sc.php", "active" => $_SESSION['sailor_hideboat'],
                                   "tip" => "Click here to remove this boat from future searches - but it will remain in the racemanager archive");
    $options['rememberme'] = array("label" => "Remember Me", "url" => "rememberme_sc.php", "active" => $_SESSION['sailor_rememberme'],
                                   "tip" => "Click here to set this as the boat you usually sail.");

    return $options;
}


function set_boat_details()
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

function set_cruise_status_list($events, $entries, $action = array())
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
            "entry-status" => $entry_status,
            "entry-updated" => $entries[$eventid]['updated'],
            "entry-alert" => $entry_alert
        );
    }
    return $event_arr;
}

function set_event_status_list($events, $entries, $action = array())
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
    }
    return $event_arr;
}

function set_result_data($sailorid, $events)
{
    global $db_o;

    $arr = array();

    foreach ($events as $eventid => $event) {
        $entry_o = new ENTRY($db_o, $eventid, $events[$eventid]);
        $alloc = $entry_o->allocate($_SESSION['sailor']);
        $fleet = $alloc['fleet'];

        $arr['list'][$eventid] = array(
            "event-name" => $event['event_name'],
            "event-date" => $event['event_date'],
            "event-start" => $event['event_start'],
            "fleet-name" => $event['fleetcfg'][$fleet]['name'],
            "race-type" => $event['fleetcfg'][$fleet]['scoring']
        );

        $arr['list'][$eventid]['racestate'] = "unknown";
        $rstate_o = new RACESTATE($db_o, $eventid);
        $rstate_rst = $rstate_o->racestate_get($fleet);
        if ($rstate_rst) {
            $arr['list'][$eventid]['racestate'] = $rstate_rst[$fleet]['status'];
        }

        $result_o = new RESULT($db_o, $eventid);
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


function add_auto_continue($usage, $delay, $external, $target_url)
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


function check_argument($arg, $mode, $check, $default = "")
{
    // tests $_REQUEST argument for existence and sets values or defaults accordingly.
    //
    // e.g
    //
    // $external = check_argument("state", "setbool", "init", true)
    // $action['event'] = check_argument("event", "set", "", 0)

    $val = $default;
    if (key_exists($arg, $_REQUEST)) {
        if ($mode == "set") {
            empty($_REQUEST[$arg]) ? $val = $default : $val = $_REQUEST[$arg];
        }
        elseif ($mode == "setbool") {
            $_REQUEST[$arg] == $check ? $val = true : $val = false;
        }
        elseif ($mode == "checkint") {
            ctype_digit($_REQUEST[$arg]) ? $val = $arg : $val = false;
        }
    }

    return $val;
}

function check_valueinlist ($val, $list)
{
    $result = false;
    $search_list = array_map('strtolower', $list);
    if (in_array(strtolower($val), $search_list)) {
        $result = true;
    }

    return $result;
}