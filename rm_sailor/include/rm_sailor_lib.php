<?php

function get_event_details($eventid_list)
{
    global $event_o;

    // FIXME - seems a bit wasteful to get this every time I return to options - if this is slow try another approach

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
    foreach ($data['details'] as $k => $event)
    {
        // get fleet details for this event
        $fleetcfg = $event_o->event_getfleetcfg($event['event_format']);

        foreach ($fleetcfg as $j => $fleet)
        {
            // add to array
            $data['details'][$k]['fleetcfg'][$fleet['fleet_num']] = array(
                "fleetnum"    => $fleet['fleet_num'],
                "startnum"    => $fleet['start_num'],
                "code"        => $fleet['fleet_code'],
                "name"        => $fleet['fleet_name'],
                "desc"        => $fleet['fleet_desc'],
                "scoring"     => strtolower($fleet['scoring']),
                "pytype"      => strtolower($fleet['py_type']),
                "warnsignal"  => $fleet['warn_signal'],
                "prepsignal"  => $fleet['prep_signal'],
                "timelimitabs"=> $fleet['timelimit_abs'],
                "timelimitrel"=> $fleet['timelimit_rel'],
                "defaultlaps" => $fleet['defaultlaps'],
                "defaultfleet"=> $fleet['defaultfleet'],
                "classinc"    => $fleet['classinc'],
                "onlyinc"     => $fleet['onlyinc'],
                "classexc"    => $fleet['classexc'],
                "groupinc"    => $fleet['groupinc'],
                "minpy"       => $fleet['min_py'],
                "maxpy"       => $fleet['max_py'],
                "crew"        => $fleet['crew'],
                "spintype"    => $fleet['spintype'],
                "hulltype"    => $fleet['hulltype'],
                "minhelmage"  => $fleet['min_helmage'],
                "maxhelmage"  => $fleet['max_helmage'],
                "minskill"    => $fleet['min_skill'],
                "maxskill"    => $fleet['max_skill']
            );
        }
    }

    // get number of events
    if ($data['details']) { $data['numevents'] = count($data['details']); }

    // get details of next event
    $rs = $event_o->event_getnextevent(date("Y-m-d"));
    if ($rs) { $data['nextevent'] = $rs; }
    return $data;
}

function set_event_status()
{

}

function num_entries_requested($params)
{
    $i = 0;
    foreach ($_SESSION['events']['details'] as $eventid=>$race)
    {
        if (isset($params["race$eventid"]))
        {
            if ($params["race$eventid"]=="on") { $i++; }
        }
    }
    return $i;
}

function count_entries($entries)
{
    $i = 0;
    foreach($entries as $entry)
    {
        if ($entry['entered'] == true)
        {
            $i++;
        }
    }
    return $i;
}

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

        $event_o = new EVENT($db_o);

        // check if race is closed
        $e = $event_o->event_getevent($eventid);
        $event_status = "";
        if ($e)
        {
            $event_status = $e['event_status'];
        }

        $data[$eventid] = array(
            "sailorid"     => $sailorid,
            "event-name"   => $event['event_name'],
            "start-time"   => $event['event_start'],
            "allocate"     => $alloc,
            "entered"      => false,
            "declare"      => "",
            "protest"      => false,
            "event-status" => $event_status
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
                if ($r['action'] == "enter" or $r['action'] == "update")
                {
                    $data[$eventid]['entered'] = true;
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

function get_options_arr()
{
// initialising options in the order they should appear in menus
    $options = array();
    if ($_SESSION['sailor_signon'])
    {
        $options['signon'] = array("label" => "Sign On", "url" => "signon_pg.php", "active" => true);
    }
    if ($_SESSION['sailor_signoff'])
    {
        $options['signoff'] = array("label" => "Sign Off/Retire", "url" => "signoff_pg.php", "active" => true);
    }
    if ($_SESSION['sailor_addboat'])
    {
        $options['addboat'] = array("label" => "Add New Boat", "url" => "addboat_pg.php", "active" => true);
    }
    if ($_SESSION['sailor_editboat'])
    {
        $options['editboat'] = array("label" => "Change Boat Details", "url" => "editboat_pg.php", "active" => true);
    }
    if ($_SESSION['sailor_results'])
    {
        $options['results'] = array("label" => "Get Results", "url" => "results_pg.php", "active" => true);
    }
    if ($_SESSION['sailor_protest'])
    {
        $options['protest'] = array("label" => "register Protest", "url" => "protest_pg.php", "active" => true);
    }

    return $options;
}

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

function set_event_status_list($events, $entries)
{
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
            $entry_status = "entered";
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
        $event_arr[$eventid] = array(
            "name"         => $event['event_name'],
            "time"         => $event['event_start'],
            "start"        => $entries[$eventid]['allocate']['start'],
            "entry-status" => $entry_status,
            "event-status" => $event['event_status']
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
?>