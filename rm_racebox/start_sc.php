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
$loc        = "..";
$page       = "start";
$scriptname = basename(__FILE__);
$debug      = false;
$stop_here  = false;
require_once ("{$loc}/common/lib/util_lib.php"); 
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("./include/rm_racebox_lib.php");

// start session
u_startsession("sess-rmracebox", 10800);

// arguments
$eventid   = u_checkarg("eventid", "checkintnotzero","");   // eventid (required)
$pagestate = u_checkarg("pagestate", "set", "", "");        // pagestate (required)

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
    $db_o    = new DB;
    $event_o = new EVENT($db_o);
    $race_o  = new RACE($db_o, $eventid);
    $entry_o = new ENTRY($db_o, $eventid);
    $timer_o = new TIMER($db_o, $eventid);

    if ($pagestate == "starttimer")
    {
        if ($_SESSION['racebox_entry_sweep'])
        {
            sweep_late_entries($eventid, $page, $g_entries_report, $g_entries_failed);
        }

        // process start time data
        $status = $event_o->event_updatestatus($eventid, "running");                          // update event status
        $race_o->race_times_init();                                                           // reset timings in t_race and t_lap
        $timer_o->start($_SERVER['REQUEST_TIME']);                                            // start timer - updating t_event, t_racestate and session

        // set finished boats back to racing
        $db_o->db_query("UPDATE t_race SET status = 'R' WHERE eventid = $eventid AND status != 'X'");
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
        if ($_SESSION['racebox_entry_sweep'])
        {
            sweep_late_entries($eventid, $page, $g_entries_report, $g_entries_failed);
        }

        $status = $event_o->event_updatestatus($eventid, "running");                           // update event status
        $race_o->race_times_init();                                                            // reset timings in t_race and t_lap
        $timer_o->start(strtotime($_REQUEST['adjusttime']), true);                             // set timer to adjusted time
        u_growlSet($eventid, $page, $g_start_timer_adjusted, array($_REQUEST['adjusttime']));
    }
    
    elseif ($pagestate == "generalrecall")
    {
        // check if specified start time is later that originally scheduled start time
//        if (strtotime($_REQUEST['restarttime']) <= strtotime($_SESSION["e_$eventid"]["st_{$_REQUEST['startnum']}"]['starttime']))
//        {
//            u_growlSet($eventid, $page, $g_start_recall_fail);
//        }
//        else
//        {
//            $timer_o->setrecall($_REQUEST['startnum'], $_REQUEST['restarttime']);        //  set recall time for start affected
//            u_growlSet($eventid, $page, $g_start_recall_success, array($_REQUEST['startnum'], $_REQUEST['restarttime']));
//        }

        // check if specified start time is later that originally scheduled start time - give warning
        if (strtotime($_REQUEST['restarttime']) <= strtotime($_SESSION["e_$eventid"]["st_{$_REQUEST['startnum']}"]['starttime']))
        {
            u_growlSet($eventid, $page, $g_start_recall_fail);
        }
        $timer_o->setrecall($_REQUEST['startnum'], $_REQUEST['restarttime']);        //  set recall time for start affected
        u_growlSet($eventid, $page, $g_start_recall_success, array($_REQUEST['startnum'], $_REQUEST['restarttime']));
    }

    elseif ($pagestate == "setcode")
    {
        $setcode = set_code($eventid, $_REQUEST);
        if ($setcode !== true)
        {
            u_growlSet($eventid, $page, $g_timer_setcodefailed, array($_REQUEST['boat'], $setcode));
        }
    }

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

function get_entries($eventid)
{
    global $db_o, $entry_o;

    $problems = $entry_o->chk_signon_errors("entries");        // includes entries, updates and retirements
    $signons = $entry_o->get_signons("entries");
    $entries_found = count($signons);

    $entries_deleted = 0;
    $entries_replaced = 0;
    $entered = 0;
    if ($entries_found > 0)             // deal with entries
    {
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
                elseif ($status['state'] == "failed") // save entry details for display
                {
                    $problems[] = array("id"=>$signon['t_entry_id'], "boat"=>$status['entry'], "reason"=>$status['reason']);
                }
            }
        }


    }

    return array("found"=>$entries_found, "entered"=>$entered, "replaced"=>$entries_replaced,
        "deleted"=>$entries_deleted, "problems" => $problems);
}



function sweep_late_entries($eventid, $page, $g_entries_report, $g_entries_fail_detail)
{
    global $race_o;

    $entries = get_entries($eventid);

    // report summary of entries made
    $entry_txt = "";
    if ($entries['entered'] > 0) $entry_txt.= "<br>- {$entries['found']} entries made";
    if ($entries['replaced'] > 0) { $entry_txt.= "<br>{$entries['replaced']} existing entries updated"; }
    if ($entries['deleted'] > 0) { $entry_txt.= "<br>{$entries['deleted']} existing entries removed"; }
    u_growlSet($eventid, $page, $g_entries_report, array($entry_txt));
    u_writelog("ENTRY load on start: {$entries['found']} entered - {$entries['replaced']} updated", $eventid);

    // report failed entries
    if (!empty($problems))
    {
        $problem_txt = "";
        foreach ($problems as $problem) {
            $problem_txt .= "{$problem['boat']} - {$problem['reason']}  [id = {$problem['id']}]<br>";
        }
        u_growlSet($eventid, $page, $g_entries_fail_detail, array($problem_txt));
        u_writelog("ENTRY load on start problems: $problem_txt", $eventid);
    }

    // reset class groups if necessary (used on timer page)
    if ($entries['found'] > 0)
    {
        $_SESSION["e_$eventid"]['classes'] = $race_o->count_groups("class", "count", 11);
    }
}