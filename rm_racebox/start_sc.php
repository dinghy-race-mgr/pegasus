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

u_initpagestart($eventid, $page, false);   // starts session and sets error reporting

require_once ("{$loc}/common/classes/db_class.php"); 
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/race_class.php"); 
require_once ("{$loc}/common/classes/timer_class.php");

include("./templates/growls.php");

// process parameters  (eventid, pagestate, entryid)
$eventid   = (!empty($_REQUEST['eventid']))? $_REQUEST['eventid']: "";
$pagestate = (!empty($_REQUEST['pagestate']))? $_REQUEST['pagestate']: "";

if ($eventid AND $pagestate)
{
    $db_o = new DB;
    $event_o = new EVENT($db_o);
    $race_o = new RACE($db_o, $eventid);
    $timer_o = new TIMER($db_o, $eventid);    // new

    if ($pagestate == "starttimer")
    {        
        $status = $event_o->event_updatestatus($eventid, "running");       // update event status
        $race_o->race_times_init();                                        // reset timings in t_race, t_lap and t_finish
        $timer_o->start($_SERVER['REQUEST_TIME']);                         // start timer
    }
    
    elseif ($pagestate == "stoptimer")
    {
        if (strtolower(trim($_REQUEST['confirm'])) == "stop")
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
        $status = $event_o->event_updatestatus($eventid, "running");        // update event status
        $race_o->race_times_init();                                         // reset timings in t_race and t_lap
        $timer_o->start(strtotime($_REQUEST['adjusttime']));                // set timer to adjusted time
        u_growlSet($eventid, $page, $g_start_timer_adjusted, array($_REQUEST['adjusttime']));
    }
    
    elseif ($pagestate == "generalrecall")
    {
        u_writedbg("general recall - passed arguments: |{$_REQUEST['restarttime']}|{$_REQUEST['startnum']}|", __FILE__, __FUNCTION__,__LINE__);
        
        // check if specified start time is later that originally scheduled start time
        if (strtotime($_REQUEST['restarttime']) <= $_SESSION["e_$eventid"]["st_{$_REQUEST['startnum']}"]['starttime']) 
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
        
    if (!$stop_here){ header("Location: start_pg.php?eventid=$eventid"); exit(); }    // back to entries page
       
}
else
{
    u_exitnicely($scriptname, $eventid,"event id [$eventid] or pagestate [$pagestate] not recognised",
        "Close this window and try to restart the application.  If the problems continue please report the error to your system administrator");
}

// ------------- FUNCTIONS ---------------------------------------------------------------------------

