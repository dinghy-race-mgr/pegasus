<?php
/**
 * rbx_sc_raceprocess.php
 * 
 * Changes the event details.
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 * @param int $eventid
 * @param string $pagestate 
      
 * 
 * 
 */
$loc        = "..";       // <--- relative path from script to top level folder
$page       = "start";     // 
$scriptname = basename(__FILE__);
$debug      = false;
$stop_here  = false;
require_once ("{$loc}/common/lib/util_lib.php"); 
require_once ("{$loc}/common/lib/rm_lib.php");

// process parameters  (eventid, pagestate)
$eventid   = u_checkarg("eventid", "checkintnotzero","");
$pagestate = u_checkarg("pagestate", "set", "", "");

// start session
session_id('sess-rmracebox');
session_start();

// page initialisation
u_initpagestart($eventid, $page, false);

// classes
require_once ("{$loc}/common/classes/db_class.php"); 
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/race_class.php"); 
require_once ("{$loc}/common/classes/timer_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/boat_class.php");

// page controls
include("./templates/growls.php");

if ($eventid AND $pagestate)
{
    $db_o = new DB;
    $event_o = new EVENT($db_o);
    $race_o = new RACE($db_o, $eventid);
    $timer_o = new TIMER($db_o, $eventid);    // new

    if ($pagestate == "starttimer")
    {        

        // if option for collect late entries is set - add them now
        if ($_SESSION['racebox_entry_sweep'])   // check for last minute entries that may not be loaded
        {
            $entries = get_entries($eventid);

            $delta = $entries['found'] - ($entries['entered'] + $entries['replaced'] + $entries['deleted']);

            if ($entries['found'] > 0)
            {
                u_growlSet($eventid, $page, $g_entries_report,
                    array($entries['found'], $entries['entered'], $entries['replaced'], $entries['deleted']));

                if ($delta != 0) { u_growlSet($eventid, $page, $g_entries_failed, array($delta)); }
            }

//            $entry_o = new ENTRY($db_o, $eventid);
//
//            // FIXME this code is duplicated of code in entries_sc
//            $signons = $entry_o->get_signons("entries");
//            $entries_found = count($signons);
//
//            if ($entries_found > 0)             // deal with entries
//            {
//                $entries_deleted = 0;
//                $entries_replaced = 0;
//                $entered = 0;
//                foreach ($signons as $signon)
//                {
//                    if ($signon['action'] == "delete" OR $signon['action'] == "update" OR $signon['action'] == "replace")
//                    {
//                        // delete entry if it exists
//                        $del = $entry_o->delete_by_compid($signon['id']);
//                        if ($signon['action'] == "delete" and $del)
//                        {
//                            $entries_deleted++;
//                            $upd = $entry_o->confirm_entry($signon['t_entry_id'], "L");
//                        }
//                    }
//
//                    if ($signon['action'] == "enter" OR $signon['action'] == "update" OR $signon['action'] == "replace")
//                    {
//                        $status = enter_boat($signon, $eventid, "signon");  // add new or replacement record
//                        if ($status == "entered")
//                        {
//                            $entered++;
//                        }
//                        elseif ($status == "exists")
//                        {
//                            $entries_replaced++;
//                        }
//                    }
//                }
//                u_growlSet($eventid, $page, $g_entries_report, array($entries_found, $entered, $entries_replaced, $entries_deleted));
//                $delta = $entries_found - ($entered + $entries_deleted + $entries_replaced);
//                if ($delta != 0) {
//                    u_growlSet($eventid, $page, $g_entries_failed, array($delta));
//                }
//            }
        }

        // process start time data
        $status = $event_o->event_updatestatus($eventid, "running");       // update event status

        $race_o->race_times_init();                                        // reset timings in t_race, delete relevant t_lap and t_finish

        // set finished boats back to racing
        $db_o->db_query("UPDATE t_race SET status = 'R' WHERE eventid = $eventid AND status != 'X'");

        $timer_o->start($_SERVER['REQUEST_TIME']);                         // start timer - updating t_event, t_racestate and session

    }
    
    elseif ($pagestate == "stoptimer")
    {
        if (strtolower(trim(str_replace('"', "", $_REQUEST['confirm'])) == "stop"))
        {
            $status = $event_o->event_updatestatus($eventid, "selected");       // update event status
            $timer_o->stop($_SERVER['REQUEST_TIME']);                           // stop timer
            u_growlSet($eventid, $page, $g_start_timer_stop);
        }
        else
        {
            u_growlSet($eventid, $page, $g_start_timer_continue);
        }
    }
    
    elseif ($pagestate == "adjusttimer")
    {
        if ($_SESSION['racebox_entry_sweep'])   // check for last minute entries that may not be loaded
        {
            $entries = get_entries($eventid);

            $delta = $entries['found'] - ($entries['entered'] + $entries['replaced'] + $entries['deleted']);

            if ($entries['found'] > 0)
            {
                u_growlSet($eventid, $page, $g_entries_report,
                    array($entries['found'], $entries['entered'], $entries['replaced'], $entries['deleted']));

                if ($delta != 0) { u_growlSet($eventid, $page, $g_entries_failed, array($delta)); }
            }
        }

        $status = $event_o->event_updatestatus($eventid, "running");        // update event status

        $race_o->race_times_init();                                         // reset timings in t_race and t_lap

        u_writelog("adjusting start time to {$_REQUEST['adjusttime']}", $eventid);

        $timer_o->start(strtotime($_REQUEST['adjusttime']));                // set timer to adjusted time
        u_growlSet($eventid, $page, $g_start_timer_adjusted, array($_REQUEST['adjusttime']));
    }
    
    elseif ($pagestate == "generalrecall")
    {
        //u_writedbg("general recall - passed arguments: |{$_REQUEST['restarttime']}|{$_REQUEST['startnum']}|", __FILE__, __FUNCTION__,__LINE__);
        
        // check if specified start time is later that originally scheduled start time
        //u_writedbg("GR: {$_REQUEST['restarttime']} | {$_SESSION["e_$eventid"]["st_{$_REQUEST['startnum']}"]['starttime']} | ".strtotime($_REQUEST['restarttime'])." | ".strtotime($_SESSION["e_$eventid"]["st_{$_REQUEST['startnum']}"]['starttime']),__FILE__,__FUNCTION__,__LINE__);
        if (strtotime($_REQUEST['restarttime']) <= strtotime($_SESSION["e_$eventid"]["st_{$_REQUEST['startnum']}"]['starttime']))
        {
            u_growlSet($eventid, $page, $g_start_recall_fail);
        }
        else
        {
            $timer_o->setrecall($_REQUEST['startnum'], $_REQUEST['restarttime']);        //  set recall time for start affected
            u_growlSet($eventid, $page, $g_start_recall_success, array($_REQUEST['startnum'], $_REQUEST['restarttime']));
        }        
    }
    
//    elseif ($pagestate == "setalllaps")        // sets laps for all fleets
//    {
//        $lapsetfail = false;
//        $growlmsg   = "Setting laps:<br>";
//        for ($i=1; $i<=$_SESSION["e_$eventid"]['rc_numfleets']; $i++)
//        {
//            $fleetname = $_SESSION["e_$eventid"]["fl_$i"]['name'];
//            $status = $race_o->race_laps_set($i, $_REQUEST['laps'][$i]);
//            if ($status)
//            {
//                if ($status == "less_than_current")
//                {
//                    $growlmsg.="$fleetname - not set, at least one boat is on this lap already<br>";
//                    $lapsetfail = true;
//                }
//                else
//                {
//                    u_writelog("setlaps: $fleetname - {$_REQUEST['laps'][$i]} laps", $eventid);
//                }
//            }
//            else
//            {
//                u_writelog("setlaps: $fleetname - failed [{$_REQUEST['laps'][$i]}] laps", $eventid);
//                $growlmsg.= "$fleetname - laps set FAILED <br>";
//                $lapsetfail = true;
//            }
//        }
//        if ($lapsetfail)  { u_growlSet($eventid, $page, $g_start_lapset_fail, array($growlmsg)); }
//    }
//
//
//    elseif ($pagestate == "setlap")   // sets lap for one fleet
//    {
//        $fleetname = $_SESSION["e_$eventid"]["fl_{$_REQUEST['fleet']}"]['name'];
//        $rs = $race_o->race_laps_set($_REQUEST['fleet'], $_REQUEST['laps']);
//        //-- u_writedbg("status = $status", __FILE__,__FUNCTION__,__LINE__);
//        if ($rs)
//        {
//            if ($rs['result'] === "less_than_current")
//            {
//                u_growlSet($eventid, $page, $g_start_fleetset_notok, array($fleetname));
//            }
//            else
//            {
//                u_writelog("setlaps: $fleetname - {$_REQUEST['laps']} laps", $eventid);
//            }
//        }
//        else
//        {
//            u_writelog("setlaps: $fleetname - failed [{$_REQUEST['laps'][$i]} laps]", $eventid);
//            u_growlSet($eventid, $page, $g_start_fleetset_fail, array($fleetname));
//        }
//    }

    // check race state / update session
    $race_o->racestate_updatestatus_all($_SESSION["e_$eventid"]['rc_numfleets'], $page);

    // return to start page
    if (!$stop_here){ header("Location: start_pg.php?eventid=$eventid"); exit(); }    // back to entries page
       
}
else
{
    u_exitnicely($scriptname, 0, "$page page - the requested event has an missing/invalid record identifier [{$_REQUEST['eventid']}] or pagestate [{$_REQUEST['pagestate']}",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// ------------- FUNCTIONS ---------------------------------------------------------------------------

// FIXME these functions are copies function in entries_sc - its untested in this context and needs to be in a lib or class
// FIXME the one in entries_sc needs to be in class or library and this one removed
// FIXME only used here to catch late entries

function get_entries($eventid)
{
    global $db_o;

    $entry_o = new ENTRY($db_o, $eventid);

    // FIXME this code is duplicated of code in entries_sc
    $signons = $entry_o->get_signons("entries");
    $entries_found = count($signons);

    if ($entries_found > 0)             // deal with entries
    {
        $entries_deleted = 0;
        $entries_replaced = 0;
        $entered = 0;
        foreach ($signons as $signon)
        {
            if ($signon['action'] == "delete" OR $signon['action'] == "update" OR $signon['action'] == "replace")
            {
                // delete entry if it exists
                $del = $entry_o->delete_by_compid($signon['id']);
                if ($signon['action'] == "delete" and $del)
                {
                    $entries_deleted++;
                    $upd = $entry_o->confirm_entry($signon['t_entry_id'], "L");
                }
            }

            if ($signon['action'] == "enter" OR $signon['action'] == "update" OR $signon['action'] == "replace")
            {
                $status = enter_boat($signon, $eventid, "signon");  // add new or replacement record
                if ($status == "entered")
                {
                    $entered++;
                }
                elseif ($status == "exists")
                {
                    $entries_replaced++;
                }
            }
        }

        $rst = array("found"=>$entries_found, "entered"=>$entered, "replaced"=>$entries_replaced, "deleted"=>$entries_deleted);
    }

    return $rst;
}




function enter_boat($entry, $eventid, $type)
{
    global $entry_o, $db_o;

    $event_o = new EVENT($db_o);
    $boat_o = new BOAT($db_o);
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