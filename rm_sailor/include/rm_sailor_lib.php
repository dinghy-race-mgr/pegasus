<?php

function set_page_options($page)
{
    $opt_map = get_options_map($page);
    $options = array();
    foreach ($opt_map as $opt)
    {
        if (array_key_exists($opt, $_SESSION['option_cfg']))
        {
            $options[] = $_SESSION['option_cfg'][$opt];
        }
    }
    return $options;
}


function get_event_details($eventid_list = array())
{
    global $event_o;

    /* event information held in array
       numevents - number of racing events either scheduled for today or specified as a parameter
       nextevent - details of the next event if there are none today or specified
       details   - 2D array with 1 array for each event.  The event includes key information for the
                   the event , and details of the configuration for each fleet in the event.

    */
    $data = array("numevents" => 0, "nextevent" => array(), "details" => array(), );

    if (!empty($eventid_list))                      // if event id has values - get details for those events
    {
        foreach ($eventid_list as $k=>$id)
        {
            $rs = $event_o->event_getevent($id, true);   // get event if it is a racing event
            if ($rs) { $data['details'][$rs['id']] = $rs; }
        }
    }
    else                                               // else get event details for all races today
    {
        $rs = $event_o->event_getevents(array("event_date"=>date("Y-m-d")),$_SESSION['mode'], true);
        if ($rs)
        {
            foreach ($rs as $k=>$detail) { $data['details'][$detail['id']] = $detail; }
        }
    }

    // get fleet info for each event
    $error = false;
    foreach ($data['details'] as $k => $event)
    {
        // get fleet details for this event
        $fleetcfg = $event_o->event_getfleetcfg($event['event_format']);

        if (!$fleetcfg)
        {
            $error = true;
        }
        else
        {
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

    if ($error)
    {
        $data['numevents'] = -1;
    }
    else
    {
        // get number of events
        if ($data['details']) { $data['numevents'] = count($data['details']); }

        // get details of next event
        $rs = $event_o->event_getnextevent(date("Y-m-d"));
        if ($rs) { $data['nextevent'] = $rs; }
    }

    return $data;
}

//function num_entries_requested($params)
//{
//    $i = 0;
//    foreach ($_SESSION['events']['details'] as $eventid=>$race)
//    {
//        if (isset($params["race$eventid"]))
//        {
//            if ($params["race$eventid"]=="on") { $i++; }
//        }
//    }
//    return $i;
//}

//function count_entries($entries)
//{
//    $i = 0;
//    foreach($entries as $entry)
//    {
//        if ($entry['entered'] == true)
//        {
//            $i++;
//        }
//    }
//    return $i;
//}

function get_entry_information($sailorid, $events)
{
    //Gets entry status by parsing the content of the t_entry table.
    global $db_o;

    $data = array();

    // loop over events
    foreach ($events as $eventid=>$event)
    {
        $entry_o = new ENTRY($db_o, $eventid, $events[$eventid]);
        // get fleet allocation for this sailor
        $alloc = $entry_o->allocate($_SESSION['sailor']);

        $data[$eventid] = array(
            "sailorid"     => $sailorid,
            "event-name"   => $event['event_name'],
            "start-time"   => $event['event_start'],
            "allocate"     => $alloc,
            "entered"      => false,
            "updated"      => false,
            "declare"      => "",
            "protest"      => false,
            "event-status" => $event['event_status']
        );

        // check position or code in race
        $race = $entry_o->get_by_compid($sailorid);
        $data[$eventid]['position'] = "unknown";
        if (empty($race['code']))
        {
            if ($race['points'] > 0 AND !empty($race['points']))
            {
                $data[$eventid]['position'] = u_numordinal($race['points']);
            }
        }
        else
        {
            $data[$eventid]['position'] = $race['code'];
        }

        // get all records from t_entry for this competitor and this event - in ascending time order
        $records = $entry_o->get_signon($eventid, $sailorid);

        if ($records)
        {
            // loop through t_entry records
            foreach ($records as $k => $r)
            {
                if ($r['action'] == "enter" )
                {
                    $data[$eventid]['entered'] = true;
                }
                elseif ($r['action'] == "update")
                {
                    $data[$eventid]['updated'] = true;
                }
                elseif ($r['action'] == "declare" or $r['action'] == "retire")
                {
                    $data[$eventid]['declare'] = $r['action'];
                    if($r['protest']) { $data[$eventid]['protest'] = true; }
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
// setting available options
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

//function get_boat_changes()
//{
//    $changes = array();
//    $changes['helm']    = u_change($_SESSION['sailor']['chg-helm'], $_SESSION['sailor']['helmname']);
//    $changes['crew']    = u_change($_SESSION['sailor']['chg-crew'], $_SESSION['sailor']['crewname']);
//    $changes['sailnum'] = u_change($_SESSION['sailor']['chg-sailnum'], $_SESSION['sailor']['sailnum']);
//
//    return $changes;
//}


function set_boat_details()
{
    if (empty($_SESSION['sailor']['change']))
    {
        $boat = array(
            "id"      => $_SESSION['sailor']['id'],
            "class"   => $_SESSION['sailor']['classname'],
            "sailnum" => $_SESSION['sailor']['sailnum'],
            "helm"    => $_SESSION['sailor']['helmname'],
            "crew"    => $_SESSION['sailor']['crewname']
        );
    }
    else
    {
        $boat = array(
            "id"      => $_SESSION['sailor']['id'],
            "class"   => $_SESSION['sailor']['classname'],
            "sailnum" => u_pick($_SESSION['sailor']['chg-sailnum'], $_SESSION['sailor']['sailnum']),
            "helm"    => u_pick($_SESSION['sailor']['chg-helm'], $_SESSION['sailor']['helmname']),
            "crew"    => u_pick($_SESSION['sailor']['chg-crew'], $_SESSION['sailor']['crewname'])
        );
    }
    $boat['team'] = u_conv_team($boat['helm'], $boat['crew'], 0);

    return $boat;
}

function set_event_status_list($events, $entries, $action = array())
{
    $evstatuscode = array("scheduled" => 1, "selected" => 2,"running" => 3,
        "sailed" => 4, "complete" => 5,"abandoned" => 6, "cancelled" => 7,);

    $event_arr = array();

    foreach ($events as $eventid=>$event)
    {
        $entry_status = "";
        if ($entries[$eventid]['declare'] == "retire")
        {
            $entry_status = "retired";
        }
        elseif ($entries[$eventid]['declare'] == "declare")
        {
            $entry_status = "signed off";
        }
        elseif ($entries[$eventid]['entered'])
        {
            $entries[$eventid]['updated'] ? $entry_status = "updated": $entry_status = "entered";
        }
        else // not entered
        {
            if (!$entries[$eventid]['allocate']['status']) // problem with allocating
            {
                if ($entries[$eventid]['allocate']['alloc_code'] == "E")
                {
                    $entry_status = "not eligible";
                }
                elseif ($entries[$eventid]['allocate']['alloc_code'] == "X")
                {
                    $entry_status = "class not recognised";
                }
            }
        }

        $entry_alert = "";
        if (!empty($action))
        {
            if ($action['event'] == $eventid)
            {
                if ($action['status'] == "err") { $entry_alert = "FAILED - {$action['msg']}"; }
            }
        }

        $status_map = array(
            "scheduled" => "not started",
            "selected"  => "not started",
            "running"   => "in progress",
            "sailed"    => "finishing",
            "complete"  => "complete",
            "abandoned" => "abandoned",
            "cancelled" => "cancelled",
        );

        if (key_exists($event['event_status'], $status_map))
        {
            $txt = $status_map[$event['event_status']];
        }
        else
        {
            $txt = "unknown";
        }

        $event_arr[$eventid] = array(
            "name"         => $event['event_name'],
            "time"         => $event['event_start'],
            "start"        => $entries[$eventid]['allocate']['start'],
            "signon"       => $event['event_entry'],
            "entry-status" => $entry_status,
            "entry-updated"=> $entries[$eventid]['updated'],
            "entry-alert"  => $entry_alert,
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

    foreach ($events as $eventid => $event)
    {
        $entry_o = new ENTRY($db_o, $eventid, $events[$eventid]);
        $alloc = $entry_o->allocate($_SESSION['sailor']);
        $fleet = $alloc['fleet'];

        $arr['list'][$eventid] = array(
            "event-name"  => $event['event_name'],
            "event-date"  => $event['event_date'],
            "event-start" => $event['event_start'],
            "fleet-name"  => $event['fleetcfg'][$fleet]['name'],
            "race-type"   => $event['fleetcfg'][$fleet]['scoring']
        );

        $arr['list'][$eventid]['racestate'] = "unknown";
        $rstate_o = new RACESTATE($db_o, $eventid);
        $rstate_rst = $rstate_o->racestate_get($fleet);
        if ($rstate_rst)
        {
            $arr['list'][$eventid]['racestate'] = $rstate_rst[$fleet]['status'];
        }

        $result_o = new RESULT($db_o, $eventid);
        $fleet_rst = $result_o->get_race_results($fleet);

        $arr['list'][$eventid]['position'] = "?";
        foreach ($fleet_rst as $rst)
        {
            $arr['data'][$eventid][]= array(
                "compid"   => $rst['competitorid'],
                "position" => $rst['result'],
                "class"    => $rst['class'],
                "sailnum"  => $rst['sailnum'],
                "team"     => $rst['team'],
                "laps"     => $rst['lap'],
                "etime"    => $rst['etime'],
                "atime"    => $rst['atime']
            );

            if ($sailorid == $rst['competitorid'])
            {
                $arr['list'][$eventid]['position'] = $rst['result'];
            }
        }
    }
    return $arr;
}
