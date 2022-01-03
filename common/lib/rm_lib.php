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

function r_initialiseevent($mode, $eventid)
    /*
     *    mode        reset mode [init|reset|rejoin]
     *    eventid     id for event being initialised
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

    // setup where list of codes to continue timing
    // FIXME not sure this is used
    $codelist = $db_o->db_getresultcodes("timing");
    $_SESSION['timercodelist'] = "";
    foreach ($codelist as $row) {
        $_SESSION['timercodelist'] .= "'{$row['code']}',";
    }

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

            r_seteventinsession($mode, $eventid, $event_rs, $series_rs, $ood_rs);      // add event and ood information to session
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
                foreach ($fleetcfg_rs as $fleet) {
                    $i = $fleet['fleet_num'];

                    // create racestate information for each fleet in init or reset
                    if ($mode == "init" or $mode == "reset")
                    {
                        r_initfleetdb($db_o, $eventid, $i, $fleet, $racecfg_rs['start_scheme'], $racecfg_rs['start_interval']);
                    }

                    // add fleet information to session
                    r_initfleetsession($db_o, $eventid, $i, $fleet);
                }
                // now determine if timer has been started.
                //$_SESSION["e_$eventid"]['timerstart'] = 0;
                if (!empty($event_rs['timerstart']))  // race has already started
                {
                    $_SESSION["e_$eventid"]['timerstart'] = $event_rs['timerstart'];
                }
            } else {
                $status = "fleet_error";
            }
        } else {
            $status = "race_error";
        }
    }

    if ($status == "ok") {
        // check event status - if scheduled, update status to selected in database and session
        if ($_SESSION["e_$eventid"]['ev_status'] == "scheduled")
        {
            $eventchange = $event_o->event_updatestatus($eventid, "selected");
            $_SESSION["e_$eventid"]['ev_status'] = "selected";
        }
    }
       
    // disconnect database
    $db_o->db_disconnect(); 
    
    return $status;
}


function r_seteventinsession($mode, $eventid, $event, $series_rs, $ood_rs = array())
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
        $_SESSION["e_$eventid"]['timerstart']     = 0;                        // actual start time as timestamp (not reset if rejoin)
    }

    $_SESSION["e_$eventid"]['ev_prevstatus']  = "";                       // status before current status for this event
    $_SESSION["e_$eventid"]['exit']           = false;                    // flag set if race is closed
    
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
    
    // names  (short and full)
    $_SESSION["e_$eventid"]['ev_sname'] = strtok($event['event_name'], " /-");
    $_SESSION["e_$eventid"]['ev_fname'] = $event['event_name'];                // FIXME - shouldn't really need this anymore'
    
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

function r_initfleetsession($db_o, $eventid, $fleetnum, $fleet)
{
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

    // extend fleet session data with current t_racestate data
    $fleetdata = $db_o->db_get_row("SELECT * FROM t_racestate WHERE eventid=$eventid AND fleet=$fleetnum");

    // set fleet details
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['startdelay'] = $fleetdata['startdelay'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['starttime']  = strtotime($fleetdata['starttime']);
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['maxlap']     = $fleetdata['maxlap'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['currentlap'] = $fleetdata['currentlap'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['entries']    = $fleetdata['entries'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['status']     = $fleetdata['status'];

    // set start details
    $_SESSION["e_$eventid"]["st_{$fleet['start_num']}"]['startdelay'] = $fleetdata['startdelay'];
    $_SESSION["e_$eventid"]["st_{$fleet['start_num']}"]['starttime']  = $fleetdata['starttime'];

    return;
}

function r_initfleetdb($db_o, $eventid, $fleetnum, $fleet, $start_scheme, $start_interval)
{
    // only called if initialising or reset - set values into t_racestate  (doesn't update database if we are rejoining)

    $data = array(
        "fleet"       => $fleetnum,
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

    $_SESSION["e_$eventid"]['pursuit'] ? $data['maxlap'] = 1000 : $data['maxlap'] = $fleet['defaultlaps'];

    $insert_rs = $db_o->db_insert("t_racestate", $data);
    
    // extend fleet session data with current t_racestate data
    //$fleetdata = $db_o->db_get_row("SELECT * FROM t_racestate WHERE eventid=$eventid AND fleet=$fleetnum");
    
    // set fleet details into session
//    $_SESSION["e_$eventid"]["fl_$fleetnum"]['startdelay'] = $fleetdata['startdelay'];
//    $_SESSION["e_$eventid"]["fl_$fleetnum"]['starttime']  = $fleetdata['starttime'];
//    $_SESSION["e_$eventid"]["fl_$fleetnum"]['maxlap']     = $fleetdata['maxlap'];
//    $_SESSION["e_$eventid"]["fl_$fleetnum"]['currentlap'] = $fleetdata['currentlap'];
//    $_SESSION["e_$eventid"]["fl_$fleetnum"]['entries']    = $fleetdata['entries'];
//    $_SESSION["e_$eventid"]["fl_$fleetnum"]['status']     = $fleetdata['status'];

    $_SESSION["e_$eventid"]["fl_$fleetnum"]['startdelay'] = $data['startdelay'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['starttime']  = $data['starttime'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['maxlap']     = $data['maxlap'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['currentlap'] = $data['currentlap'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['entries']    = $data['entries'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['status']     = $data['status'];
    
    // set start details
//    $_SESSION["e_$eventid"]["st_{$fleet['start_num']}"]['startdelay'] = $fleetdata['startdelay'];
//    $_SESSION["e_$eventid"]["st_{$fleet['start_num']}"]['starttime']  = $fleetdata['starttime'];

    $_SESSION["e_$eventid"]["st_{$fleet['start_num']}"]['startdelay'] = $data['startdelay'];
    $_SESSION["e_$eventid"]["st_{$fleet['start_num']}"]['starttime']  = $data['starttime'];
    return $insert_rs;
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
    // FIXME change this an associative array
    if (in_array($currentstatus, array("completed","cancelled","abandoned")))
    {
        return "complete";
    }
    elseif(in_array($currentstatus, array("running","sailed")))
    {
        return "in progress";
    }
    elseif(in_array($currentstatus, array("selected")))
    {
        return "not started";
    }
    elseif(in_array($currentstatus, array("scheduled")))
    {
        return "scheduled";
    }
    else
    {
        return "unknown";
    }
}

function r_styleracestatus($currentstatus)
{
    // FIXME change this an associative array

    if (in_array($currentstatus, array("completed","cancelled","abandoned")))
    {
        return "success";                                          // complete
    }
    elseif(in_array($currentstatus, array("running","sailed")))    // in progress
    {
        return "warning";
    }
    elseif(in_array($currentstatus, array("selected")))            // not started
    {
        return "info";
    }
    elseif(in_array($currentstatus, array("scheduled")))           // scheduled
    {
        return "danger";
    }
    else                                                           // unknown
    {
        return "default";
    }
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
    if ($mode == "cancel")
    {
        $status = array (
            "result"    => true,
            "reason"    => "",
            "action"    => "",
            "notes"     => ""
        );
    }
    else
    {
        $status = array (
            "result"    => true,
            "reason"    => "",
            "action"    => "",
            "notes"     => ""
        );
    }

    return $status;
}


function r_oktoabandon($eventid, $mode)
{
    if ($mode == "abandon")
    {
        $status = array (
            "result"    => true,
            "reason"    => "",
            "action"    => "",
            "notes"     => ""
        );
    }
    else
    {
        $status = array (
            "result"    => true,
            "reason"    => "",
            "action"    => "",
            "notes"     => ""
        );
    }

    return $status;
}


function r_oktoclose($eventid)
{
    // FIXME - still needs to be implemented
    
    // check if there are any unfinished boats - if there are return the number of boats
    
    // check if the results have been published

    $status = array (
        "result"    => true,
        "missing"   => 0,
        "published" => true,
        "reason"    => "The race results have not been published",
        "action"    => "Go to the results page and publish the results before closing the race",
        "notes"     => "If you have a problem with the results that you cannot resolve -
                        publish them anyway and leave a message describing the problem when you close the race"
    );
    return $status;
}

function r_oktoreset($eventid)
{
    // FIXME - still needs to be implemented
    $status = array (
        "result" => true,
        "reason" => "",
        "action" => "",
        "notes"  => "",
    );
    return $status;
}


// FIXME - no longer used
//function r_getcompetitorpn($eventid, $fleetnum, $personal, $national, $local)
//{
//    //u_requestdbg(func_get_args(), __FILE__,__FUNCTION__,__LINE__);
//    $pytype = $_SESSION["e_$eventid"]["fl_$fleetnum"]['pytype'];
//    //u_writedbg("pytype: $pytype",__FILE__,__FUNCTION__,__LINE__);
//    if($pytype == "personal")
//    {
//        $pn = $personal;
//    }
//    elseif ($pytype == "national")
//    {
//        $pn = $national;
//    }
//    elseif ($pytype == "local")
//    {
//        $pn = $local;
//    }
//    else  // default to local value
//    {
//        $pn = $local;
//    }
//    return $pn;
//}

//function r_competitorsearch($db, $searchstr)
//{
//    /* searches for competitors based on search string containing one or more of class name, helms name and sailnum */
//
//    $words = explode (" ",$searchstr);
//    $sailnum = "";
//    $class   = "";
//    $helm    = "";
//    foreach($words as $word)
//    {
//        $word = trim($word);
//        if ($word == "" or $word == " ") { continue; }
//
//        if (ctype_digit($word))   // its an integer - therefore a sailnumber
//            { $sailnum = $word; }
//        else
//        {
//            $result = $db->db_get_rows("SELECT id FROM t_class WHERE classname LIKE '$word%'");
//            if ($result)               // class starts with string
//                { $class = $word; }
//            else
//            {
//                $result = $db->db_get_rows("SELECT id FROM t_competitor WHERE helm LIKE '%$word%'");
//                if ($result)           // helm includes string
//                    { $helm = $word; }
//            }
//        }
//    }
//
//    // now do the search
//    $where = "";
//    if (!empty($class))   { $where.= " and classname LIKE '$class%' "; }
//    if (!empty($sailnum)) { $where.= " and (sailnum LIKE '$sailnum%' or sailnum LIKE '$sailnum%') "; }
//    if (!empty($helm))    { $where.= " and helm LIKE '%$helm%' "; }
//
//    if (empty($where))
//    {
//        $result = array();
//    }
//    else
//    {
//        $where = ltrim($where,"and ");
//        $query = "SELECT a.id, classname, sailnum, helm, a.crew
//                  FROM `t_competitor` as a
//                  JOIN t_class as b ON a.classid=b.id
//                  WHERE 1=0 OR ($where)
//                  ORDER BY  classname, sailnum * 1";
//        $result = $db->db_get_rows($query);
//    }
//
//    return $result;
//}
// FIXME  - no usages
//function r_entercompetitor($eventid, $db_o, $race_o, $competitorid, $change)
//{
//    $comp_o = new COMPETITOR($db_o);
//    $comp = $comp_o->comp_getcompetitor($competitorid);
//    echo "competitor - before ".print_r($comp,true)."<br>";
//    //$check = $comp_o->comp_eligible($comp, $_SESSION["e_$eventid"]['ev_format']); // FIXME
//    $check['eligible'] = true;    // FIXME this is always making eligible
//    $check['start']    = 1;
//    $check['fleet']    = 1;
//    $comp = array_merge($comp, $check);
//
//    echo "competitor - after ".print_r($comp,true)."<br>";
//
//    $result = $race_o->entry_add($comp, $change);
//
//    if ($result['status'] == "entered")
//    {
//       $compupdate = $comp_o->comp_updatelastentry($comp['id'], $eventid);     // update competitor record
//    }
//
//    return $result;
//}

function r_getresultcolumns($racetype, $raceopen, $cellattr)
{
    $columns = array (
          "class"      => array( "attr" => "{$cellattr['class']}", "width" => "10%", "label" => "class" ),
          "sailnum"    => array( "attr" => "{$cellattr['sailnum']}", "width" => "7%", "label"  => "no." ),
          "competitor" => array( "attr" => "{$cellattr['competitor']}", "width" => "20%", "label" => "competitor" )
          );
    
    if($raceopen!="local")
    {
        $columns['club'] = array( "attr" => "{$cellattr['club']}", "width" => "", "label" => "club" );
    }
    
    if ($racetype =="level")
    {
        $columns['etime'] =  array( "attr" => "{$cellattr['etime']}", "width" => "", "label"  => "ET" );
    }
    elseif ($racetype == "hcap")
    {
        $columns['pn'] = array( "attr" => "{$cellattr['pn']}", "width" => "", "label"  => "pn" );
        $columns['etime'] = array( "attr" => "{$cellattr['etime']}", "width" => "", "label"  => "ET" );
        $columns['ctime'] = array( "attr" => "{$cellattr['ctime']}", "width" => "", "label"  => "CT" );
        
    }
    elseif ($racetype == "avglap")
    {
        $columns['pn'] = array( "attr" => "{$cellattr['pn']}", "width" => "", "label"  => "pn" );
        $columns['lap']= array( "attr" => "{$cellattr['lap']}", "width" => "", "label"  => "lap" );
        $columns['etime'] = array( "attr" => "{$cellattr['etime']}", "width" => "", "label"  => "ET" );
        $columns['atime'] = array( "attr" => "{$cellattr['atime']}", "width" => "", "label"  => "CT" );
    }
    elseif ($racetype == "pursuit")
    {
        $columns['pn'] = array( "attr" => "{$cellattr['pn']}", "width" => "", "label"  => "pn" );
        $columns['lap']= array( "attr" => "{$cellattr['lap']}", "width" => "", "label"  => "lap" );
    }
    else
    {
        return false;
    }
    $columns['points']  = array( "attr" => "{$cellattr['points']}", "width" => "", "label"  => "points");
    $columns['code']    = array( "attr" => "{$cellattr['code']}", "width" => "", "label"  => "code" );
    $columns['status']  = array( "attr" => "{$cellattr['status']}", "width" => "", "label"  => "status" );
    $columns['buttons'] = array( "attr" => "", "width" => "15%", "label" => "actions" );
        
    return $columns;
}

function r_getresultdata($results, $racetype, $raceopen)
{
    //u_writedbg ("<pre>".print_r($results,true)."</pre>", __FILE__, __FUNCTION__, __LINE__);
    //u_writedbg ("<pre>$racetype|$raceopen</pre>", __FILE__, __FUNCTION__, __LINE__);
    $out_rs = array();
    $i = 0;
    foreach ($results as $key=>$row)
    {
        $i++;
        $out_rs[$i]['id']         = $row['id'];
        $out_rs[$i]['class']      = $row['class'];
        $out_rs[$i]['sailnum']    = $row['sailnum'];
        $out_rs[$i]["competitor"] = u_truncatestring(rtrim($row['helm']."/".$row['crew'], "/"), 25);
        $out_rs[$i]['helm']       = $row['helm'];
        $out_rs[$i]['crew']       = $row['crew'];
        
        if($raceopen!="local")
        {
            $out_rs[$i]['club'] = $row['club'];
        }

        if ($racetype =="level")
        {           
            $out_rs[$i]['pn']    = $row['pn'];
            $out_rs[$i]['lap']   = $row['lap'];                                 // needed
            $out_rs[$i]['etime'] = u_conv_secstotime($row['etime']);            // needed
            $out_rs[$i]['ctime'] = 0;
            $out_rs[$i]['atime'] = 0;
        }
        elseif ($racetype == "handicap")
        {
            $out_rs[$i]['pn']    = $row['pn'];                                  // needed
            $out_rs[$i]['lap']   = $row['lap'];                                 // needed
            $out_rs[$i]['etime'] = u_conv_secstotime($row['etime']);            // needed
            $out_rs[$i]['ctime'] = u_conv_secstotime($row['ctime']);            // needed
            $out_rs[$i]['atime'] = u_conv_secstotime($row['atime']);
            
        }
        elseif ($racetype == "average")
        {
            $out_rs[$i]['pn']    = $row['pn'];                                  // needed
            $out_rs[$i]['lap']   = $row['lap'];                                 // needed
            $out_rs[$i]['etime'] = u_conv_secstotime($row['etime']);            // needed
            $out_rs[$i]['ctime'] = u_conv_secstotime($row['ctime']);            
            $out_rs[$i]['atime'] = u_conv_secstotime($row['atime']);            // needed
        }
        elseif ($racetype == "pursuit")
        {
            $out_rs[$i]['pn']    = $row['pn'];                                  // needed
            $out_rs[$i]['lap']   = $row['lap'];                                 // needed
            $out_rs[$i]['etime'] = u_conv_secstotime($row['etime']);            
            $out_rs[$i]['ctime'] = u_conv_secstotime($row['ctime']);            
            $out_rs[$i]['atime'] = u_conv_secstotime($row['atime']);            
        }
        else
        {
            return false;
        }
        $out_rs[$i]['points']      = $row['points'];
        $out_rs[$i]['code']        = $row['code'];
        $out_rs[$i]['declaration'] = $row['declaration'];
        $out_rs[$i]['status']      = $row['status'];
        $out_rs[$i]['penalty']     = $row['penalty'];
        $out_rs[$i]['note']        = $row['note'];
        
    }
    
    return $out_rs;
}



function r_correct_time($racetype, $pn, $etime)
{
    if ($pn > 0 AND $etime > 0)
    {
        if ($racetype == "level")
        {
            $ctime = $etime;
        }
        elseif ($racetype == "handicap" OR $racetype == "average")
        {
            $ctime = round(($etime * 1000)/$pn);
        }
        elseif ($racetype == "pursuit")
        {
            $ctime = 0;
        }
        else
        {
            $ctime = 0;
        }
    }
    else
    {
        $ctime = 0;
    }
    return $ctime;

}


function r_aggregate_time($racetype, $pn, $etime, $lap, $maxlap)
{
    if ($pn > 0 AND $etime > 0 AND $lap > 0 and $maxlap > 0)
    {
        if ($racetype == "level")
        {
            $atime = $etime;
        }
        elseif ($racetype == "handicap" OR $racetype == "average")
        {
            $atime = round((($etime * 1000)/$pn) * $maxlap/$lap);
        }
        elseif ($racetype == "pursuit")
        {
            $atime = 0;
        }
        else
        {
            $atime = 0;
        }
    }
    else
    {
        $atime = 0;
    }
    return $atime;
}

function r_evaluate_code_race($code, $race_entries)
{
    $value = 0;

    if ($code['scoringtype'] == "penalty")
    {


        if ($code['scoring'] == "20%")
        {
            $value = round($race_entries * 0.2);
        }
    }
    elseif ($code['scoringtype'] == "race")
    {
        if ($code['scoring'] == "N+1")
        {
            $value = $race_entries + 1;
        }
    }
    elseif ($code['scoringtype'] == "series")
    {
        $value = 9999;
    }

    return $value;
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
