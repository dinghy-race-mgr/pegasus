<?php
/**
 * rm_lib.php - racemanager functions
 * 
 * Utility Functions
 * 
 *      EVENT   ----------------------------------
 *          r_seteventinsession      set event details in session
 *      
 *      RACE CONFIGURATION ----------------------------------
 *          r_setraceinsession       set race configuration details in session
 *          r_setfleetinsession      set fleet configuration details in session
 * 
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * 
 * %%copyright%%
 * %%license%%
 *  
 * 
 */
 
/* ---------------------  EVENT functions ----------------------------------------------------------*/

function r_initialiseevent($mode, $eventid, $current_event_status = "")
    /*
     *    mode        reset mode [init|reset|rejoin]
     *    eventid     id for event being initialised
     *    current_event_status     event status before initialisation (only required for rejoin mode)
     *
     *    assumes t_race, t_lap, t_finish, t_racestate have had all event related records
     *    deleted if the mode is 'init' or 'reset'
     */
{
    $status = "ok";

    // establish database and event objects   
    $db_o = new DB();
    $event_o = new EVENT($db_o);
    $rota_o = new ROTA($db_o);

    // set up codes from drop downs     // FIXME is there a more efficient way to do this
    $_SESSION['startcodes']  = $db_o->db_getresultcodes("start");
    $_SESSION['timercodes']  = $db_o->db_getresultcodes("timer");
    $_SESSION['resultcodes'] = $db_o->db_getresultcodes("result");

    // get event details
    $event_rs = $event_o->get_event_byid($eventid);

    if ($status == "ok") {
        if ($event_rs AND $event_rs['event_type'] == "racing")  // we have information on the specified event and it is a race
        {
            // get series information   FIXME - deal with series_code not being valid
            if (empty($event_rs['series_code']))
            {
                $series_rs = array();
            }
            else
            {
                $series_rs = $event_o->event_getseries($event_rs['series_code']);
            }

            // get OOD information
            $ood_rs = $rota_o->get_event_duties($eventid, "ood_p");

            r_seteventinsession($mode, $eventid, $current_event_status, $event_rs, $series_rs, $ood_rs);      // add event and ood information to session
        }
        else
        {
            $status = "event_error";
        }
    }


    // if we have the event - get the individual race configuration details

    if ($status == "ok")
    {
        $racecfg_rs = $event_o->event_getracecfg($_SESSION["e_$eventid"]['ev_format'], $eventid);

        if ($racecfg_rs AND $racecfg_rs['active'] == 1)
        {
            //get fleet configuration for this race format and add to session
            $fleetcfg_rs = $event_o->event_getfleetcfg($_SESSION["e_$eventid"]['ev_format']);
            $fleetnum = count($fleetcfg_rs);

            // set race format info into event session
            r_setraceinsession($eventid, $racecfg_rs, $fleetnum);

            // add fleet format info into event session
            if ($fleetcfg_rs)
            {
                foreach ($fleetcfg_rs as $fleet)
                {
                    $i = $fleet['fleet_num'];

                    // create racestate information for each fleet in init or reset
                    $rs_db = r_initfleetdb($mode, $eventid, $i, $fleet, $racecfg_rs['start_scheme'], $racecfg_rs['start_interval']);


                    // add fleet information to session
                    $rs_sess = r_initfleetsession($eventid, $i, $fleet);

                    if (!$rs_db or !$rs_sess) { $status = "fleetinit_error" ;}

                }
                // now determine if timer has been started.
                if (!empty($event_rs['timerstart']))  // race has already started
                {
                    $_SESSION["e_$eventid"]['timerstart'] = $event_rs['timerstart'];
                }
            }
            else
            {
                $status = "fleetcfg_error";
            }
        }
        else
        {
            $status = "racecfg_error";
        }
    }


    if ($status == "ok") {
        // set status to selected in database and session if mode is init or reset
        if ($mode == "init" or $mode == "reset")
        {
            $eventchange = $event_o->event_updatestatus($eventid, "selected");
            $_SESSION["e_$eventid"]['ev_status'] = "selected";

            // reload demo entries
            if ($_SESSION['mode'] == "demo")
            {
                // delete t_entry records for this event
                $del = $db_o->db_delete("t_entry", array("eventid"=>$eventid));

                // read data from z_entry - change eventid - insert into t_entry
                $entry_rs = $db_o->db_get_rows("SELECT * FROM z_entry");
                foreach($entry_rs as $row)
                {
                    $ins = $db_o->db_insert("t_entry", array("action"=>$row['action'], "eventid"=>$eventid,
                                            "competitorid"=>$row['competitorid'], "updby"=>"demorun"));
                }
            }
        }
    }
    else
    {
        u_exitnicely("rm_lib.php", $eventid,"Failed to initialise rm_racebox application correctly [ fail due to $status ]",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__,
                "calledby" => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2)[1]['function'], "args" => func_get_args()));
    }
       
    // disconnect database
    $db_o->db_disconnect();
    
    return $status;
}


function r_seteventinsession($mode, $eventid, $current_event_status, $event, $series_rs, $ood_rs = array())
{    
    // set database record  into session
    $_SESSION["e_$eventid"]['ev_date']        = $event['event_date'];   
    $_SESSION["e_$eventid"]['ev_starttime']   = $event['event_start'];    // scheduled start time e.g 12:30
    $_SESSION["e_$eventid"]['ev_order']       = $event['event_order'];
    $_SESSION["e_$eventid"]['ev_name']        = $event['event_name'];
    $_SESSION["e_$eventid"]['ev_seriescode']  = strtolower($event['series_code']);
    $_SESSION["e_$eventid"]['ev_type']        = strtolower($event['event_type']);
    $_SESSION["e_$eventid"]['ev_format']      = $event['event_format'];
    $_SESSION["e_$eventid"]['ev_entry']       = strtolower($event['event_entry']);
    $_SESSION["e_$eventid"]['ev_status']      = strtolower($event['event_status']);
    $_SESSION["e_$eventid"]['ev_open']        = strtolower($event['event_open']);
    $_SESSION["e_$eventid"]['ev_ood']         = ucfirst($event['event_ood']);
    $_SESSION["e_$eventid"]['ev_tidetime']    = $event['tide_time'];
    $_SESSION["e_$eventid"]['ev_tideheight']  = $event['tide_height'];
    $_SESSION["e_$eventid"]['ev_startscheme'] = $event['start_scheme'];
    $_SESSION["e_$eventid"]['timerstart']     = $event['timerstart'];
    $_SESSION["e_$eventid"]['ev_startint']    = $event['start_interval'];
    $_SESSION["e_$eventid"]['ev_notes']       = $event['event_notes'];
    $_SESSION["e_$eventid"]['ev_resultnotes'] = $event['result_notes'];
    $_SESSION["e_$eventid"]['result_valid']   = $event['result_valid'];
    $_SESSION["e_$eventid"]['result_publish'] = $event['result_publish'];

    $wind = array("ws_start"=>$event['ws_start'], "ws_end"=>$event['ws_end'], "wd_start"=>$event['wd_start'], "wd_end"=>$event['wd_end'],);
    $_SESSION["e_$eventid"]['ev_wind'] = u_getwind_str($wind);

    // initialised variables
    if ($mode == "init" or $mode == "reset")
    {
        $_SESSION["e_$eventid"]['timerstart'] = 0;                      // actual start time as timestamp (not reset if rejoin)
        $_SESSION["e_$eventid"]['ev_prevstatus']  = "";                 // status before current status for this event}
    }
    else
    {
        $_SESSION["e_$eventid"]['ev_prevstatus'] = $current_event_status;
    }

    $_SESSION["e_$eventid"]['exit'] = false;                            // flag set if race is closed


    // derived variables
    empty($event['event_order']) ? $label = $event['event_start'] : $label = $event['event_order'];
    $_SESSION["e_$eventid"]['ev_label'] = "Race $label";

    empty($event['series_code']) ?  $_SESSION["e_$eventid"]['ev_seriesname'] = "" :
                                    $_SESSION["e_$eventid"]['ev_seriesname'] = $series_rs['seriesname'];
    
    // get OOD name(s) - if not already in event record
    if (!empty($ood_rs) and empty($_SESSION["e_$eventid"]['ev_ood']))
    {
        foreach ($ood_rs as $key=>$data)
        {
            $_SESSION["e_$eventid"]['ev_ood'].= $data['person'].", ";
        }
        $_SESSION["e_$eventid"]['ev_ood'] = trim($_SESSION["e_$eventid"]['ev_ood'],", ");
    }
    
    // names  (display version)
    $_SESSION["e_$eventid"]['ev_dname'] = u_conv_eventname($_SESSION["e_$eventid"]['ev_name']);
    
    // last click time
    $_SESSION["e_$eventid"]['lastclick']['entryid'] = 0;
    $_SESSION["e_$eventid"]['lastclick']['clicktime'] = 0;

}


/* ----------------------- RACE CONFIGURATION functions -----------------------------------------------*/

function r_setraceinsession($eventid, $racecfg, $fleetnum)

{
    $_SESSION["e_$eventid"]['pursuit']        = $racecfg['pursuit'];
    $_SESSION["e_$eventid"]['rc_code']        = strtoupper($racecfg['race_code']);
    $_SESSION["e_$eventid"]['rc_name']        = $racecfg['race_name'];
    $_SESSION["e_$eventid"]['rc_desc']        = $racecfg['race_desc'];
    $_SESSION["e_$eventid"]['rc_numstarts']   = $racecfg['numstarts'];
    $_SESSION["e_$eventid"]['rc_numfleets']   = $fleetnum;
    $_SESSION["e_$eventid"]['rc_startscheme'] = $racecfg['start_scheme'];
    $_SESSION["e_$eventid"]['rc_startint']    = $racecfg['start_interval'];
    $_SESSION["e_$eventid"]['rc_comppick']    = $racecfg['comp_pick'];

    return;

}

function r_initfleetsession($eventid, $fleetnum, $fleet)
{
    // sets fleet information and racestate data into session
    global $db_o;

    $_SESSION["e_$eventid"]["fl_$fleetnum"]['fleetnum']     = $fleetnum;
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['startnum']     = $fleet['start_num'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['code']         = $fleet['fleet_code'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['name']         = $fleet['fleet_name'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['desc']         = $fleet['fleet_desc'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['scoring']      = strtolower($fleet['scoring']);
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['pytype']       = strtolower($fleet['py_type']);
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['warnsignal']   = $fleet['warn_signal'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['prepsignal']   = $fleet['prep_signal'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['timelimitabs'] = $fleet['timelimit_abs'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['timelimitrel'] = $fleet['timelimit_rel'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['defaultlaps']  = $fleet['defaultlaps'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['defaultfleet'] = $fleet['defaultfleet'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['classinc']     = $fleet['classinc'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['onlyinc']      = $fleet['onlyinc'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['classexc']     = $fleet['classexc'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['groupinc']     = $fleet['groupinc'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['minpy']        = $fleet['min_py'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['maxpy']        = $fleet['max_py'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['crew']         = $fleet['crew'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['spintype']     = $fleet['spintype'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['hulltype']     = $fleet['hulltype'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['minhelmage']   = $fleet['min_helmage'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['maxhelmage']   = $fleet['max_helmage'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['minskill']     = $fleet['min_skill'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['maxskill']     = $fleet['max_skill'];

    // retrieve current racestate values
    $sql = "SELECT * FROM t_racestate WHERE eventid = $eventid and fleet = $fleetnum order by fleet ASC";
    $fleetdata = $db_o->db_get_row($sql);

    if (!empty($fleetdata))
    {
        $status = true;

        // set fleet details
        $_SESSION["e_$eventid"]["fl_$fleetnum"]['starttime'] = $_SESSION["e_$eventid"]['timerstart'] + $fleetdata['startdelay'];
        $_SESSION["e_$eventid"]["fl_$fleetnum"]['startdelay'] = $fleetdata['startdelay'];
        $_SESSION["e_$eventid"]["fl_$fleetnum"]['maxlap']     = $fleetdata['maxlap'];
        $_SESSION["e_$eventid"]["fl_$fleetnum"]['currentlap'] = $fleetdata['currentlap'];
        $_SESSION["e_$eventid"]["fl_$fleetnum"]['entries']    = $fleetdata['entries'];
        $_SESSION["e_$eventid"]["fl_$fleetnum"]['status']     = $fleetdata['status'];

        // set start details
        $_SESSION["e_$eventid"]["st_{$fleet['start_num']}"]['startdelay'] = $fleetdata['startdelay'];
        $_SESSION["e_$eventid"]["st_{$fleet['start_num']}"]['starttime']  = $fleetdata['starttime'];
    }
    else
    {
        $status = false;
    }

    return $status;
}

function r_initfleetdb($mode, $eventid, $fleetnum, $fleet, $start_scheme, $start_interval)
{
//    If mode is init or reset intialises the racestate record for this fleet
//    Sets racestate current values into session

    global $db_o;
    $status = true;

    if ($mode == "init" or $mode == "reset")
    {
        $data = array(
            "fleet"      => $fleetnum,
            "racename"   => $fleet['fleet_name'],
            "start"      => $fleet['start_num'],
            "eventid"    => $eventid,
            "racetype"   => $fleet['scoring'],
            "startdelay" => r_getstartdelay($fleet['start_num'], $start_scheme, $start_interval),
            "starttime"  => gmdate("H:i:s", 0),
            "currentlap" => 0,
            "entries"    => 0,
            "status"     => "notstarted",
        );

        // set initial laps according to whether a pursuit race or a fleet configuration
        $_SESSION["e_$eventid"]['pursuit'] ? $data['maxlap'] = 1000 : $data['maxlap'] = $fleet['defaultlaps'];

        u_writelog("laps for fleet $fleetnum [{$fleet['fleet_name']}] initialised to {$fleet['defaultlaps']} laps", $eventid);

        // add initial data settings to t_racestate
        $status = $db_o->db_insert("t_racestate", $data);
    }

    return $status;
}


//function r_clearfleetdb($db, $eventid)
//{
//    $constraint = array("eventid"=>"$eventid");
//    $delete_rs = $db->db_delete("t_racestate", $constraint);
//    return $delete_rs;
//}

function r_getstartdelay($startnum, $start_scheme, $start_interval )
{    
    // get first number from scheme
    $numbers = explode("-", $start_scheme);
    // calculate start time in secs
    $start_time = (intval($numbers[0]) * 60) + ($start_interval * 60 * ($startnum - 1));
    
    // echo "<pre> startdelay: $startnum, $start_scheme, $start_interval, $start_time</pre>";
    return $start_time;
}

/*
function r_getprepdelay($startnum, $start_interval )
{    
    $prep_time = ($start_interval * 60 * ($startnum - 1));

    return $prep_time;
}
*/

function r_getelapsedtime ($mode, $origin, $clock, $startdelay, $delta=0)
{    
    // calculates time (hh:mm:ss) from timer origin time in seconds and measured time in seconds
    // it takes into account OOD time adjustments (delta is -ve for earlier starts, +ve for later start)
    $secs = $clock - $origin - $startdelay - $delta;
    if ($mode == "secs")
    {
        return $secs;
    }
    else
    {
        return date("H:i:s", $secs);
    }
}

function r_decoderacestatus($currentstatus)
{
    $status_arr = array(
        "scheduled" => "scheduled",
        "selected"  => "not started",
        "running"   => "in progress",
        "sailed"    => "in progress",
        "completed" => "complete",
        "cancelled" => "complete",
        "abandoned" => "complete",
    );

    key_exists($currentstatus, $status_arr) ? $status = $status_arr[$currentstatus] : $status = "unknown";

    return $status;
}

function r_styleracestatus($currentstatus)
{
    $style_arr = array(
        "scheduled" => "info",
        "selected"  => "info",
        "running"   => "warning",
        "sailed"    => "warning",
        "completed" => "success",
        "cancelled" => "success",
        "abandoned" => "success",
    );

    key_exists($currentstatus, $style_arr) ? $style = $style_arr[$currentstatus] : $style = "default";

    return $style;
}



function r_tideformat($time, $height)
{
    $tide = $time;
    if (!empty($height))
    {
        $tide.= " - $height m";
    }
    return $tide;
}

function r_pursuitstarttimes($db_o, $eventid, $length, $scratchid, $resolution, $pytype)
/*


*/
{
    global $db_o;
    
    $starts = array();    
    //echo "<pre>pytype: $pytype</pre>";
    
    // get scratch PN
    if ($pytype == "local")
    {
        $query = "SELECT local_py as pn FROM t_class WHERE `id`='$scratchid'";
        //echo "<pre>query: $query</pre>";
        $scratchclass = $db_o->db_get_row ($query);
        //echo "<pre>"; print_r($scratchclass); echo "</pre>";
        if ($scratchclass['pn'])
        {
            // get data
            $query = "SELECT classname as class, local_py as pn FROM t_class ORDER BY local_py DESC";
            $data = $db_o->db_get_rows ($query);
            //echo "<pre>"; print_r($data); echo "</pre>";
        }
    }
    elseif ($pytype == "national")
    {
        $query = "SELECT nat_py as pn FROM t_class WHERE `id`='$scratchid'";
        $scratchclass = $db_o->db_get_row ($query);
        if ($scratchclass['pn'])
        {
            // get data
            $query = "SELECT classname as class, nat_py as pn FROM t_class ORDER BY nat_py DESC";
            $data = $db_o->db_get_rows ($query);
        }        
    }
    elseif ($pytype == "personal")
    {
        // ignore the class PY - just use the entered boats, slowest first
        $query = "SELECT helm, class, sailnum, pn FROM t_race WHERE eventid = $eventid ORDER BY pn DESC";
        $data = $db_o->db_get_rows ($query);
    }
    
    if (!$scratchclass['pn'] OR !$data) 
    {
        //not recognised
        return false;
    }
    else
    {
        // loop through $data array getting start times
        $i = 0;
        foreach ($data as $key => $row)
        {
            $starts[$i]['class'] = $row['class'];
            $starts[$i]['pn']    = $row['pn'];
            if ($pytype == "personal")
            {
                $starts[$i]['helm']    = $row['helm'];
                $starts[$i]['sailnum'] = $row['sailnum'];
            }
            $time = $length - ($length * ($row['pn']/$scratchclass['pn']));
            $starts[$i]['start'] = u_timeresolution($resolution, $time);  // apply required time resolution

            $i++;
        }
    }
    return $starts;
}


function r_oktocancel($eventid, $mode)
{
    $ev_status = $_SESSION["e_$eventid"]['ev_status'];

    // can CANCEL if race has NOT started

    if ($mode == "cancel")
    {
        // can CANCEL if race has NOT started or is already cancelled or abandoned
        if ($ev_status == "cancelled")
        {
            $status = array ("result" => false, "reason" => "The race has already been cancelled", "info" => "" );
        }
        elseif ($ev_status == "abandoned")
        {
            $status = array ("result" => false, "reason" => "The race has already been abandoned", "info" => "" );
        }
        elseif ($ev_status == "running" OR $ev_status == "sailed" OR $ev_status == "completed")
        {
            $status = array ("result" => false, "reason" => "The race has already started", "info" => "use ABANDON instead" );
        }
        else
        {
            $status = array ("result" => true, "reason" => "", "info" => "" );
        }

    }
    else     // else uncancelling
    {
        // can uncancel ONLY if race is cancelled
        if ($ev_status == "cancelled")
        {
            $status = array ("result" => true);
        }
        else
        {
            $status = array ("result" => false, "reason" => "The race has not been cancelled - current status is $ev_status", "info" => "");
        }
    }

    return $status;
}


function r_oktoabandon($eventid, $mode)
{
    // FIXME this will need to be updated to deal with abandoning a single fleet

    global $db_o;

    $ev_status = $_SESSION["e_$eventid"]['ev_status'];

    if ($mode == "abandon")    // can ABANDON if race has started AND we have entries
    {
        // check we have entries for this race
        $query = "SELECT class, sailnum, fleet, lap, helm FROM t_race WHERE eventid = $eventid ORDER BY fleet, class, sailnum ASC";
        $data = $db_o->db_get_rows ($query);
        $entries = count($data);

        // check if race is started
        if ($entries > 0 AND ($ev_status == "running" OR $ev_status == "sailed" OR $ev_status == "completed"))
        {
            $status = array ("result" => true, "reason" => "", "info" => "" );
        }
        else
        {
            $status = array ("result" => false, "reason" => "Either the race has not started yet and/or there are no entries for this race",
                "info" => "" );
        }
    }
    else   // else unabandoning - can UNABANDON if race is in ABANDONED state
    {
        if ($ev_status == "abandoned")
        {
            $status = array ("result" => true);
        }
        else
        {
            $status = array ("result" => false, "reason" => "The race has not been abandoned - current status is $ev_status", "info" => "" );
        }
    }

    return $status;
}


function r_oktoclose($eventid)
{
    global $db_o;

    $reason_txt = "";
    $missing_txt = "";

    // get entries that are still racing
    $query = "SELECT class, sailnum, fleet, lap, helm FROM t_race WHERE eventid = $eventid and status = 'R'  ORDER BY fleet, class, sailnum ASC";
    $data = $db_o->db_get_rows ($query);
    $num_missing = count($data);

    if ($_SESSION['mode'] == "demo" AND $_SESSION["e_$eventid"]['result_publish'])
    {
        $status = array("result" => true);
    }
    else
    {
        // can CLOSE if all boats have been finished AND results published OR the race status is cancelled or abandoned
        if (($_SESSION["e_$eventid"]['result_publish'] AND $num_missing <= 0)
            OR $_SESSION["e_$eventid"]['ev_status'] == "cancelled"
            OR $_SESSION["e_$eventid"]['ev_status'] == "abandoned" )
        {
            $status = array ("result" => true);
        }
        else
        {
            if (!$_SESSION["e_$eventid"]['result_publish'])
            {
                $reason_txt.= " - the results have not been published <br><br> ";
            }

            if ( $num_missing > 0 )
            {
                $reason_txt.= " - $num_missing boats appear not to have finished or have not been given a scoring code (e.g. DNF).<br> ";

                if ( $num_missing < 6 )
                {
                    foreach ($data as $boat)
                    {
                        $missing_txt.= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;fleet {$boat['fleet']}: {$boat['class']} - {$boat['sailnum']}
                                        &nbsp;&nbsp;&nbsp;[lap {$boat['lap']}]<br>";
                    }
                }
            }
            $status = array ( "result" => false, "reason" => $reason_txt, "info" => $missing_txt );
        }
    }
    return $status;
}

function r_oktoreset($eventid)
{
    // can always reset
    $status = array ( "result" => true, "reason" => "", "info" => "" );
    return $status;
}

function r_enter_boat($entry, $eventid, $type)
{
    /*
     * Enters a boat into a race
     *
     * Args:
     * $entry
     * $eventid
     * $type
     *
     */

    global $entry_o, $boat_o, $event_o, $db_o;

    //$boat_o = new BOAT($db_o);
    $classcfg = $boat_o->boat_getdetail($entry['classname']);
    $fleets = $event_o->event_getfleetcfg($_SESSION["e_$eventid"]['ev_format']);
    $alloc = r_allocate_fleet($classcfg, $fleets);

    $success = "failed";
    $entry_tag = "{$entry['classname']} - {$entry['sailnum']}";

    // debug:u_writedbg(u_check($alloc, "ALLOCATE"),__FILE__,__FUNCTION__,__LINE__);  // debug:

    if ($alloc['status'])
    {                                              // ok to load entry
        $entry = array_merge($entry, $alloc);
        $i = $entry['fleet'];
        $result = $entry_o->set_entry($entry, $_SESSION["e_$eventid"]["fl_$i"]['pytype'], $_SESSION["e_$eventid"]["fl_$i"]['maxlap']);
        // debug:u_writedbg(u_check($result, "LOAD"),__FILE__,__FUNCTION__,__LINE__);  // debug:
        if ($result['status'])
        {
            $i = $entry['fleet'];

            if ($result["exists"])
            {
                u_writelog("ENTRY ($type) UPDATED: $entry_tag", $eventid);
                $success = "exists";
            }
            else
            {
                u_writelog("ENTRY ($type): $entry_tag", $eventid);
                $success = "entered";
                $_SESSION["e_$eventid"]["fl_$i"]['entries']++;   // increment no. of entries
            }
            if ($type == "signon") {  $upd = $entry_o->confirm_entry($entry['t_entry_id'], "L", $result['raceid']); }

            $fleet_name = $_SESSION["e_$eventid"]["fl_$i"]['code'];
            $_SESSION["e_$eventid"]['enter_rst'][] = "$entry_tag [$fleet_name]";

            $_SESSION["e_$eventid"]['result_status'] = "invalid";           // set results update flag
        }
        else
        {
            u_writelog("ENTRY ($type) FAILED: $entry_tag [{$result["problem"]}]", $eventid);
            if ($type == "signon") {  $upd = $entry_o->confirm_entry($entry['t_entry_id'], "F"); }
        }
    }
    else
    {
        u_writelog("ENTRY ($type) FAILED: $entry_tag [no fleet allocation - {$alloc['alloc_code']}]", $eventid);
        if ($type == "signon") {  $upd = $entry_o->confirm_entry($entry['t_entry_id'], $alloc['alloc_code']); }
    }

    return $success;
}

function r_allocate_fleet($class, $fleets, $competitor = array())
{
    /*
     * Allocates a class to a fleet in the race based on class characteristics and race format specification
     *
     * Args:
     *  $class - array containing record for a single class from t_class
     *  $fleets - 2D array with all records from t_cfgrace for this event
     *  $competitor - array with record for a single competitor - only if competitor specific characteristics
     *                are being used for allocation (e.g personal PY, skill level)
     */

    // FIXME: Still doesn't handle: - flight restrictions     (limits not in t_cfgrace)



    $alloc = array("status" => false, "alloc_code" => "", "start" => "", "fleet" => ""); //  aray to return allocation

    if ($fleets)
    {
        // FIXME there is a BUG here - if you sort to get the default to the bottom - it doesn't necessarily try them in fleet order
        // FIXME and doesn't work with the current club series format - putting all the assy boats in the first fleet that matches (fleet 2
        // FIXME probably need a system to allow the club to decide what order the fleets should be tested for allocation
        //u_array_sort_by_column($fleets, "defaultfleet"); // reset event array to put default fleet last in array
        // debug:u_writedbg(u_check($fleets, "FLEETS"),__FILE__,__FUNCTION__,__LINE__);  // debug:

        // check which fleet this competitor is allocated too
        // try each fleet - default last
        foreach ($fleets as $fleetcfg)
        {
            // decide whether to map array from session array names to db names - FIXME UGLY work around
            // rm_sailor and rm_racebox pass session array, rm_util use db names
            // easiest fix for future tidyup
            key_exists("fleetnum", $fleetcfg) ? $arr_conv = true : $arr_conv = false;

            if ($arr_conv)
            {
                $fleetcfg['fleet_num']   = $fleetcfg['fleetnum'];
                $fleetcfg['start_num']   = $fleetcfg['startnum'];
                $fleetcfg['py_type']     = $fleetcfg['pytype'];
                $fleetcfg['min_py']      = $fleetcfg['minpy'];
                $fleetcfg['max_py']      = $fleetcfg['maxpy'];
                $fleetcfg['min_helmage'] = $fleetcfg['minhelmage'];
                $fleetcfg['max_helmage'] = $fleetcfg['maxhelmage'];
                $fleetcfg['min_skill']   = $fleetcfg['minskill'];
                $fleetcfg['max_skill']   = $fleetcfg['maxskill'];
            }

            $classexc = array_map("trim", explode(",", strtolower($fleetcfg['classexc'])));
            $classinc = array_map("trim", explode(",", strtolower($fleetcfg['classinc'])));
            // debug:u_writedbg(u_check($fleetcfg, "FLEETCFG"),__FILE__,__FUNCTION__,__LINE__);
            // debug:u_writedbg(u_check($classexc, "EXCLUDES"),__FILE__,__FUNCTION__,__LINE__);
            // debug:u_writedbg(u_check($entry, "ENTRY"),__FILE__,__FUNCTION__,__LINE__);

//echo $fleetcfg['fleet_num']." - ".$class['classname']."<br>";
//echo "<pre>".print_r($class,true)."</pre>";
//echo "<pre>".print_r($fleetcfg,true)."</pre>";



            if (in_array(strtolower($class['classname']), $classexc, true))  // check for exclusions
            {
                // debug:u_writedbg("fleet: {$fleetcfg['fleet_num']} - excluded ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                continue; 	// this class is specifically excluded from this race - continue to next fleet
            }
            else
            {
                if ($fleetcfg['onlyinc'])   // only include fleets in classinc
                {
                    if (in_array(strtolower($class['classname']), $classinc))
                    {
                        // debug:u_writedbg("fleet: {$fleetcfg['fleet_num']} - only included ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                        $alloc = array("status"=>true, "alloc_code"=>"", "start"=>$fleetcfg['start_num'], "fleet"=>$fleetcfg['fleet_num']);
                        break;
                    }
                }
                else
                {
                    if (in_array(strtolower($class['classname']), $classinc)) // check if class is in included list
                    {
                        // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - included ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                        $alloc = array("status"=>true, "alloc_code"=>"", "start"=>$fleetcfg['start_num'], "fleet"=>$fleetcfg['fleet_num']);
                        break;
                    }
                    else  // if not allocated by class name then check if other class based characteristics match
                    {
                        $py_ok    = false;
                        $crew_ok  = false;
                        $spin_ok  = false;
                        $hull_ok  = false;
                        $age_ok   = false;
                        $group_ok = false;
                        $skill_ok = false;

                        // PY check  (passes if lies within range)
                        if ($fleetcfg['py_type']=="local" and isset($class['local_py']))
                        {
                            $py = $class['local_py'];
                        }
                        elseif ($fleetcfg['py_type']=="personal" and isset($competitor['personal_py']))
                        {
                            $py = $competitor['personal_py'];
                        }
                        else
                        {
                            $py = $class['nat_py'];
                        }
                        // debug:u_writedbg("fleet: {$fleetcfg['fleet_num']} - PY comparison $py|{$fleetcfg['min_py']}|{$fleetcfg['max_py']} ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                        empty($fleetcfg['min_py']) ? $min_py = 1 : $min_py = $fleetcfg['min_py'];
                        empty($fleetcfg['max_py']) ? $max_py = 2000 : $max_py = $fleetcfg['max_py'];
                        if( $py >= $min_py AND $py <= $max_py )
                        {
                            $py_ok = true;
                        }

                        // crew check (passes if 'any' or correct number)
                        // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - crew comparison {$entry['crew']}|{$fleetcfg['crew']} ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                        if( empty($fleetcfg['crew'])
                            OR $class['crew'] == $fleetcfg['crew'] )
                        {
                            $crew_ok = true;
                        }

                        // spinnaker type check (passes if 'any' or specified spinnaker type)
                        // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - spin comparison {$entry['spinnaker']}|{$fleetcfg['spintype']} ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                        if( empty($fleetcfg['spintype'])
                            OR strtolower($class['spinnaker']) == strtolower($fleetcfg['spintype']) )
                        {
                            $spin_ok = true;
                        }

                        // hull type check (passes if 'any' or specified hull type)
                        // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - hull comparison {$entry['category']}|{$fleetcfg['hulltype']} ",__FILE__,__FUNCTION__,__LINE__);  // debug:
                        if( empty($fleetcfg['hulltype'])
                            OR strtolower($class['category']) == strtolower($fleetcfg['hulltype']) )
                        {
                            $hull_ok = true;
                        }

                        if (!empty($competitor))    // don't do competitor checks if not supplied
                        {
                            // age check (passes if no age limits set
                            if (empty($competitor['helm_dob']) OR (empty($fleetcfg['min_helmage']) and empty($fleetcfg['max_helmage'])))
                            {
                                $age_ok = true;
                            }
                            else
                            {
                                $age = date_diff(date_create($competitor['helm_dob']), date_create('now'))->y;
                                empty($fleetcfg['min_helmage']) ? $min_age = 1 : $min_age = $fleetcfg['min_helmage'];
                                empty($fleetcfg['max_helmage']) ? $max_age = 100 : $max_age = $fleetcfg['max_helmage'];
                                if ($age >= $min_age AND $age <= $max_age)
                                {
                                    $age_ok = true;
                                }
                            }

                            // group check
                            $groupinc = array_map("trim", explode(",", strtolower($fleetcfg['groupinc'])));
                            $grouplist = array_map("trim", explode(",", strtolower($competitor['grouplist'])));
                            if (empty($groupinc) OR array_intersect($groupinc, $grouplist))
                            {
                                $group_ok = true;
                            }

                            // skill check
                            if (empty($competitor['skill_level']) OR (empty($fleetcfg['min_skill']) and empty($fleetcfg['max_skill'])))
                            {
                                $skill_ok = true;
                            }
                            else
                            {
                                empty($fleetcfg['min_skill']) ? $min_skill = 0 : $min_skill = $fleetcfg['min_skill'];
                                empty($fleetcfg['max_skill']) ? $max_skill = 100 : $max_skill = $fleetcfg['max_skill'];
                                if ($competitor['skill_level'] >= $min_skill AND $competitor['skill_level'] <= $max_skill)
                                {
                                    $skill_ok = true;
                                }
                            }
                        }
                        else
                        {
                            $age_ok = true;
                            $group_ok = true;
                            $skill_ok = true;
                        }

                        // if all checks pass then allocate to this race
                        // debug:u_writedbg("fleet: {$fleetcfg['fleetnum']} - comparison summary $py_ok|$crew_ok|$spin_ok|$hull_ok ",__FILE__,__FUNCTION__,__LINE__); // debug:
                        if ($py_ok AND $crew_ok AND $spin_ok AND $hull_ok AND $age_ok AND $group_ok AND $skill_ok)
                        {
                            // debug:u_writedbg("fleet: {$fleetcfg['fleet_num']} - all match ",__FILE__,__FUNCTION__,__LINE__);
                            $alloc = array( "status" => true, "alloc_code" => "", "start" => $fleetcfg['start_num'], "fleet" => $fleetcfg['fleet_num']);
                            break;
                        }
                    }
                }
            }
        }
        if (!$alloc['status'])   // did not find fleet to allocate it too
        {
            // debug:u_writedbg(" - not allocated to any fleet ",__FILE__,__FUNCTION__,__LINE__);  // debug:
            $alloc = array("status" => false, "alloc_code" => "E", "start" => "", "fleet" => ""); // E - ineligible (not allocated)
        }
    }
    else                         // did not find fleet configuration data to check
    {
        // debug:u_writedbg(" - no configuration found ",__FILE__,__FUNCTION__,__LINE__);  // debug:
        $alloc = array("status" => false, "alloc_code" => "X", "start" => "", "fleet" => ""); //  X - no configuration
    }

//echo "<pre>".print_r($alloc, true)."</pre>";
//exit();

    return $alloc;

}
