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
     */
{
    $status = "ok";
    
    // establish database and event objects   
    $db_o     = new DB();
    $event_o  = new EVENT($db_o); 
    $race_o   = new RACE($db_o, $eventid); 
    
    // empty dynamic database tables if a reset
//    if ($mode == "init" or $mode == "reset")
//    {
//        // clear t_race
//        //$race_o->race_clearrace($eventid);  FIXME
//
//        // clear t_racestate
//        $race_o->racestate_delete();
//
//        // clear lap times (t_lap)
//        //$race_o->race_clearracetimes($eventid); FIXME
//
//        if ($mode == "reset")
//        {
//            // reset t_entry
//            //$event_o->comp_resetentries($eventid); FIXME
//            $set  =  0;
//        }
//    }
    
    // set up codes from drop downs     // FIXME is there a more efficient way to do this
    $_SESSION['startcodes']  = $db_o->db_getresultcodes("start");
    $_SESSION['timercodes']  = $db_o->db_getresultcodes("timer");
    $_SESSION['resultcodes'] = $db_o->db_getresultcodes("result");
    
    // setup where list of codes to continue timing
    $codelist = $db_o->db_getresultcodes("timing");
    $_SESSION['timercodelist'] = "";
    foreach ($codelist as $row)
    {
        $_SESSION['timercodelist'].= "'{$row['code']}'}'";
    }   
    
    // get event details
    $event_rs = $event_o->event_getevent($eventid);
    if ($event_rs AND $event_rs['event_type']=="racing")  // we have information on the specified event and it is a race
    {        
        // FIXME - deal with event not being part of series OR series not being valid
        $series_rs = $event_o->event_getseries($event_rs['series_code']);   // get series information
        $ood_rs = $event_o->event_geteventduties($eventid, "ood_p");          // get OOD information
        r_seteventinsession($eventid, $event_rs, $series_rs, $ood_rs);      // add event and ood information to session
        
        // deal with status
        if ($_SESSION["e_$eventid"]['ev_status']=="scheduled" or $mode=="reset")
        {
            $result =  $event_o->event_updatestatus($eventid, "selected");
        }
                                
    }
    else
    {
        $status = "event_error";
    }
    
    // get race configuration details   
    $racecfg_rs = $event_o->event_getracecfg($eventid, $_SESSION["e_$eventid"]['ev_format']);
    if ($racecfg_rs and $racecfg_rs['active']==1)
    {      
        //get fleet configuration for this race format
        $fleetcfg_rs = $event_o->event_getfleetcfg($_SESSION["e_$eventid"]['ev_format']);

        // add race configuration to session
        $fleetnum = count($fleetcfg_rs);
        r_setraceinsession($eventid, $racecfg_rs, $fleetnum);   
        
        // clear any database fleet status records relating to the fleets if this is first initialisation
        if ($_SESSION["e_$eventid"]['ev_status'] == "scheduled")  // FIXME won't work on reset
        {
            r_clearfleetdb($db_o, $eventid);
        }                
               
        if ($fleetcfg_rs)
        {
            // loop over each fleet
            foreach ($fleetcfg_rs as $fleet)
            {
                $i = $fleet['fleet_num'];
                // add fleet information to session
                r_initfleetsession($eventid, $i, $fleet);
                
                // add information to t_racestate if this is first initialisation or a reset
                r_initfleetdb ($db_o, $eventid, $i, $fleet, $racecfg_rs['start_scheme'], $racecfg_rs['start_interval'], $mode);
 
             } 
             // now determine if timer has been started.   FIXME = this won't work if you close the browser - need to get this from racestate'
             if ($_SESSION["e_$eventid"]["fl_$fleetnum"]['starttime']!="00:00:00")
             {
                $_SESSION["e_$eventid"]['timerstart'] = strtotime($_SESSION["e_$eventid"]["fl_$fleetnum"]['starttime']) - r_getstartdelay(1, $_SESSION["e_$eventid"]['ev_startscheme'], $_SESSION["e_$eventid"]['ev_startint']);
                // u_writedbg ("timer reset to".date($_SESSION["e_$eventid"]['timerstart']),__FILE__,__FUNCTION__,__LINE__);
             }                                          
        }
        else
        {
            $status = "fleet_error";
        }
    }
    else
    {
        $status = "race_error";
    }
    
    // check event status - if scheduled, update status to selected in database and session 
    if ($_SESSION["e_$eventid"]['ev_status'] == "scheduled")
    {
       $eventchange = $event_o->event_updatestatus($eventid, "selected");
       $_SESSION["e_$eventid"]['ev_status'] = "selected";
    }
       
    // disconnect database
    $db_o->db_disconnect(); 
    
    return $status;
}



function r_seteventinsession($eventid, $event, $series_rs, $ood_rs = array())
{    
    // set database record  into session
    $_SESSION["e_$eventid"]['ev_date']        = $event['event_date'];   
    $_SESSION["e_$eventid"]['ev_starttime']   = $event['event_start'];    // scheduled start time
    
    $_SESSION["e_$eventid"]['ev_order']       = $event['event_order'];
    $_SESSION["e_$eventid"]['ev_name']        = $event['event_name'];
    $_SESSION["e_$eventid"]['ev_seriescode']  = strtolower($event['series_code']);
    $_SESSION["e_$eventid"]['ev_type']        = strtolower($event['event_type']);
    $_SESSION["e_$eventid"]['ev_format']      = $event['event_format'];
    $_SESSION["e_$eventid"]['ev_entry']       = strtolower($event['event_entry']);
    $_SESSION["e_$eventid"]['ev_status']      = strtolower($event['event_status']);
    $_SESSION["e_$eventid"]['ev_open']        = strtolower($event['event_open']);
    $_SESSION["e_$eventid"]['ev_prevstatus']  = "";                       // FIXME - what should this be if I am returning
    $_SESSION["e_$eventid"]['ev_tidetime']    = $event['tide_time'];
    $_SESSION["e_$eventid"]['ev_tideheight']  = $event['tide_height'];
    $_SESSION["e_$eventid"]['ev_startscheme'] = $event['start_scheme'];       
    $_SESSION["e_$eventid"]['ev_startint']    = $event['start_interval'];       
    $_SESSION["e_$eventid"]['ev_wind']        = $event['wind'];
    $_SESSION["e_$eventid"]['ev_notes']       = $event['event_notes'];
    $_SESSION["e_$eventid"]['ev_resultnotes'] = $event['result_notes'];
    
    // initialised variables

    $_SESSION["e_$eventid"]['timerstart']     = 0;                        // actual start time as timestamp
    $_SESSION["e_$eventid"]['ev_prevstatus']  = "";                       // status before current status for this event
//    $_SESSION["e_$eventid"]['result_status']  = false;                    // results publication status
    $_SESSION["e_$eventid"]['exit']           = false;
    
    // derived variables
    empty($event['series_code']) ?  $_SESSION["e_$eventid"]['ev_seriesname'] = "" :
                                    $_SESSION["e_$eventid"]['ev_seriesname'] = $series_rs['seriesname'];
    
    // get OOD name
    $oodname = "";
    if (!empty($ood_rs))
    {
        foreach ($ood_rs as $key=>$data)
        {
            $oodname.= $data['person'].", ";
        }
        $oodname = trim($oodname,", ");
    }
    $_SESSION["e_$eventid"]['ev_ood'] = $oodname;  
    
    // names  (short and full)
    $_SESSION["e_$eventid"]['ev_sname'] = strtok($event['event_name'], " /-");
    $_SESSION["e_$eventid"]['ev_fname'] = $event['event_name'];                // FIXME - shouldn't really need this anymore'
 
    // results valid/published
    $_SESSION["e_$eventid"]['result_valid']   = false;
    $_SESSION["e_$eventid"]['result_publish'] = false;
    
    // last click time
    $_SESSION["e_$eventid"]['lastclick']['entryid'] = 0;
    $_SESSION["e_$eventid"]['lastclick']['clicktime'] = 0;
    
}


/* ----------------------- RACE CONFIGURATION fucntions -----------------------------------------------*/

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

}

function r_initfleetsession($eventid, $fleetnum, $fleet)
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

}

function r_initfleetdb($db_o, $eventid, $fleetnum, $fleet, $start_scheme, $start_interval, $mode)
{
    // if new or reset - set values into t_racestate  (doesn't update database if we are rejoining)
    if ($mode == "init" OR $mode == "reset")
    {
        $data = array(
            "race"       => $fleetnum,
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
    }
    
    // extend fleet session data with current t_racestate data
    $fleetdata = $db_o->db_get_row("SELECT * FROM t_racestate WHERE eventid=$eventid AND race=$fleetnum");
    
    // set fleet details
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['startdelay'] = $fleetdata['startdelay'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['starttime']  = $fleetdata['starttime'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['maxlap']     = $fleetdata['maxlap'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['currentlap'] = $fleetdata['currentlap'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['entries']    = $fleetdata['entries'];
    $_SESSION["e_$eventid"]["fl_$fleetnum"]['status']     = $fleetdata['status'];
    
    // set start details
    $_SESSION["e_$eventid"]["st_{$fleet['start_num']}"]['startdelay'] = $fleetdata['startdelay'];
    $_SESSION["e_$eventid"]["st_{$fleet['start_num']}"]['starttime']  = $fleetdata['starttime'];

    
    return $insert_rs;
}

function r_clearfleetdb($db, $eventid)
{
    $constraint = array("eventid"=>"$eventid");
    $delete_rs = $db->db_delete("t_racestate", $constraint);
    return $delete_rs;
}

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

