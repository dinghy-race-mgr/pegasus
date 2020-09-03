<?php
/**
 * timer_sc.php
 * 
 * Manages the timer page processing
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
$page       = "timer";
$debug      = true;
$stop_here  = false;       // <--- if true will stop without returning to calling page
$scriptname = basename(__FILE__);

require_once ("{$loc}/common/lib/util_lib.php"); 
require_once ("{$loc}/common/lib/rm_lib.php");

u_initpagestart($_REQUEST['eventid'], $page, "");   // starts session and sets error reporting

// initialising language
include ("{$loc}/config/lang/{$_SESSION['lang']}-racebox-lang.php");

require_once ("{$loc}/common/classes/db_class.php"); 
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/race_class.php");

include("./templates/growls.php");

// process parameters  (eventid, pagestate, entryid)
//u_writedbg(u_check($_REQUEST, "REQUEST"), __FILE__, __FUNCTION__, __LINE__);
empty($_REQUEST['eventid'])   ? $eventid = ""   : $eventid = $_REQUEST['eventid'];
empty($_REQUEST['pagestate']) ? $pagestate = "" : $pagestate = $_REQUEST['pagestate'];
empty($_REQUEST['fleet'])     ? $fleet = ""     : $fleet = $_REQUEST['fleet'];

if ($eventid AND $pagestate)
{
    $db_o    = new DB;
    if ($fleet) { $_SESSION["e_$eventid"]['fleet_context'] = "fleet$fleet"; }
    $event_o = new EVENT($db_o);
    $race_o  = new RACE($db_o, $eventid);

    if ($pagestate == "timelap")
    {
        $if_err = false;
        empty($_REQUEST['entryid']) ? $if_err = true : $entryid = $_REQUEST['entryid'];
        empty($_REQUEST['start'])   ? $if_err = true : $start = $_REQUEST['start'];
        empty($_REQUEST['boat'])    ? $if_err = true : $boat = $_REQUEST['boat'];
        if ($if_err)
        {
            $reason = "required parameters were invalid (timelap)";
            u_writelog("$boat - lap timing failed - $reason", $eventid);
            u_growlSet($eventid, $page, $g_timer_timingfailed, array($boat, $reason));
        }
        else
        {
            empty($_REQUEST['lap'])   ? $lap = 0     : $lap = $_REQUEST['lap'];
            empty($_REQUEST['pn'])    ? $pn = 0      : $pn = $_REQUEST['pn'];
            empty($_REQUEST['etime']) ? $last_et = 0 : $last_et = $_REQUEST['etime'];

            check_double_click($eventid, $entryid, $_SERVER['REQUEST_TIME']); # return to timer page if double click of same boat
            check_race_started($eventid, $start, $_SERVER['REQUEST_TIME']);   # return to time page if race not started

            $status = $race_o->entry_time($entryid, $fleet, $lap, $pn, $_SERVER['REQUEST_TIME'], $last_et, false);
            if ($status == "time" OR $status == "finish" OR $status == "first_finish")
            {
                $newlap = $lap + 1;
                $_SESSION["e_$eventid"]['result_valid']   = false;
                $_SESSION["e_$eventid"]['result_publish'] = false;
                $_SESSION["e_$eventid"]['lastclick']['entryid']   = $entryid;
                $_SESSION["e_$eventid"]['lastclick']['clicktime'] = $_SERVER['REQUEST_TIME'];
                update_racestate($eventid, $fleet, $status, $newlap);  # update racestate and session

                if ($status == "time")
                {
                    $msg = "lap $newlap: $boat ";
                }
                elseif ($status == "finish")
                {
                    $msg = "lap $newlap: $boat finished";
                    if ($_SESSION['timer_options']['growl_finish'] == "on")
                    {
                        u_growlSet($eventid, $page, $g_timer_finish, array($boat));
                    }
                }
                elseif ($status == "first_finish")
                {
                    $msg = "lap $newlap: $boat finished";
                    u_growlSet($eventid, $page, $g_timer_firstfinish, array($boat));
                }
                u_writelog($msg, $eventid);
            }
            else
            {
                $reason = "unexpected status returned [$status]";
                $msg = "$boat - lap timing failed - $reason";
                u_growlSet($eventid, $page, $g_timer_timingfailed, array($boat, $reason));
                u_writelog($msg, $eventid);
            }

        }
    }
    
    elseif ($pagestate == "finish")
    {
        $if_err = false;
        empty($_REQUEST['entryid']) ? $if_err = true : $entryid = $_REQUEST['entryid'];
        empty($_REQUEST['start'])   ? $if_err = true : $start = $_REQUEST['start'];
        empty($_REQUEST['boat'])    ? $if_err = true : $boat = $_REQUEST['boat'];
        if ($if_err)
        {
            $reason = "required parameters were invalid (finish)";
            u_writelog("$boat - lap timing failed - $reason", $eventid);
            u_growlSet($eventid, $page, $g_timer_finishfailed, array($boat, $reason));
        }
        else
        {
            empty($_REQUEST['lap'])   ? $lap = 0     : $lap = $_REQUEST['lap'];
            empty($_REQUEST['pn'])    ? $pn = 0      : $pn = $_REQUEST['pn'];
            empty($_REQUEST['etime']) ? $last_et = 0 : $last_et = $_REQUEST['etime'];

            check_double_click($eventid, $entryid, $_SERVER['REQUEST_TIME']); # return to timer page if double click of same boat
            check_race_started($eventid, $start, $_SERVER['REQUEST_TIME']);  # return to time page if race not started

            $status = $race_o->entry_time($entryid, $fleet, $lap, $pn, $_SERVER['REQUEST_TIME'], $last_et, true);
            if ($status == "force_finish")
            {
                $newlap = $lap + 1;
                $_SESSION["e_$eventid"]['result_valid']   = false;
                $_SESSION["e_$eventid"]['result_publish'] = false;
                $_SESSION["e_$eventid"]['lastclick']['entryid']   = $entryid;
                $_SESSION["e_$eventid"]['lastclick']['clicktime'] = $_SERVER['REQUEST_TIME'];
                update_racestate($eventid, $fleet, $status, $newlap);  # update racestate and session
                u_writelog("lap $newlap: $boat finished ", $eventid);
                if ($_SESSION['timer_options']['growl_finish'] == "on")
                {
                    u_growlSet($eventid, $page, $g_timer_finish, array($boat));
                }
            }
            else
            {
                $reason = "unexpected status returned [$status]";
                u_growlSet($eventid, $page, $g_timer_finishfailed, array($boat, $reason));
                u_writelog("$boat - finish option failed - $reason", $eventid);
            }
        }
    }
    
    elseif  ($pagestate == "setcode")
    {
        $if_err = false;
        empty($_REQUEST['entryid'])    ? $if_err = true : $entryid = $_REQUEST['entryid'];
        empty($_REQUEST['boat'])       ? $if_err = true : $boat = $_REQUEST['boat'];
        empty($_REQUEST['racestatus']) ? $if_err = true : $racestatus = $_REQUEST['racestatus'];

        if ($if_err)
        {
            $reason = "required parameters were invalid
                       (id: {$_REQUEST['entryid']}; boat: {$_REQUEST['boat']}; status: {$_REQUEST['racestatus']};)";
            u_writelog("$boat - set code failed - $reason", $eventid);
            u_growlSet($eventid, $page, $g_timer_setcodefailed, array($boat, $reason));
        }
        else
        {
            empty($_REQUEST['code']) ? $code = "" : $code = $_REQUEST['code'];
            if ($code)
            {
                $update = $race_o->entry_code_set($entryid, $code);
                if ($update) { u_writelog("$boat - code set to $code", $eventid); }
            }
            else
            {
                $update = $race_o->entry_code_unset($entryid, $racestatus);
                if ($update) { u_writelog("$boat - code unset", $eventid); }
            }
            if (!$update)
            {
                $reason = "database update failed";
                u_writelog("$boat - attempt to set code to $code] FAILED" - $reason, $eventid);
                u_growlSet($eventid, $page, $g_timer_setcodefailed, array($boat, $reason));
            }
        }
    }
    
    elseif  ($pagestate == "undo")
    {
        $entry = $race_o->entry_time_undo();
            
        if ($entry)
        {
            $boat = "{$entry['class']} {$entry['sailnum']}";
            $msg = "$boat: last timing removed via UNDO";
            u_writelog($msg, $eventid);
            if ($_SESSION['timer_options']['growl_undo'] == "on")
            {
                u_growlSet($eventid, $page, $g_timer_undo_success, array($boat));
            }
        } 
        else
        {
            u_writelog("attempt to UNDO last timing FAILED", $eventid);
            u_growlSet($eventid, $page, $g_timer_undo_fail, array());
        }
    }
       
    elseif  ($pagestate == "shorten") {
        $err_reason = "";

        // work out which fleets to shorten
        $s_fleets = array();
        if ($fleet == "all")
        {
            for ($i = 1; $i <= $_SESSION["e_$eventid"]['rc_numfleets']; $i++)
            {
                $s_fleets[] = $i;
            }
        }
        elseif (is_numeric($fleet))
        {
            $s_fleets[] = $fleet;
        }
        else
        {
            $err_reason = "fleet not recognised";
        }
        // shorten selected fleets
        if (empty($err_reason))
        {
            $msg = "";
            $msgtype = "success";
            foreach ($s_fleets as $fleetnum) {
                $rs = shorten_fleet($eventid, $fleetnum);
                $msg.= $rs['text'];
                if ($msgtype != "danger") {
                    if ($rs['type'] == "unknown") {
                        $msgtype = "danger";
                    } elseif ($rs['type'] == "invalid") {
                        $msgtype = "warning";
                    }
                }
            }
            if ($fleet=="all")
            {
                $g_timer_shortenall_report['type'] = $msgtype;
                u_growlSet($eventid, $page, $g_timer_shortenall_report, array($msg));
            }
            else
            {
                $g_timer_shortenone_report['type'] = $msgtype;
                u_growlSet($eventid, $page, $g_timer_shortenone_report, array($msg));
            }

        }
        else
        {
            u_writelog("shorten course FAILED - fleet not recognised [$fleet]", $eventid);
            u_growlSet($eventid, $page, $g_timer_shorten_fail, array($err_reason));
        }
    }

    elseif ($pagestate == "setalllaps")        // sets laps for all fleets
    {
        $lapsetfail = false;
        for ($i=1; $i<=$_SESSION["e_$eventid"]['rc_numfleets']; $i++)
        {
            $fleetname = $_SESSION["e_$eventid"]["fl_$i"]['name'];
            $status = $race_o->race_laps_set($i, $_REQUEST['laps'][$i]);
            if ($status)
            {
                if ($status == "less_than_current")
                {
                    $growlmsg.="<b>Setting laps</b><br>$fleetname - not set, at least one boat is on this lap already<br>";
                    $lapsetfail = true;
                }
                else
                {
                    u_writelog("setlaps: $fleetname - {$_REQUEST['laps'][$i]} laps", $eventid);
                }
            }
            else
            {
                $lapsetfail = true;
                u_writelog("setlaps: $fleetname - failed [{$_REQUEST['laps'][$i]}] laps", $eventid);
                $growlmsg.= "<b>Setting laps</b><br>$fleetname - laps set FAILED <br>";
            }
        }
        if ($lapsetfail)  { u_growlSet($eventid, $page, $g_race_lapset_fail, array($growlmsg)); }
    }


    else
    {
        u_growlSet($eventid, $page, $g_sys_invalid_pagestate, array($pagestate, $page)); //
    }

    if (!$stop_here) { header("Location: timer_pg.php?eventid=$eventid"); exit(); }
}
else
{
    //FIXME - needs to be a way to get back
    u_exitnicely($scriptname, $eventid,"sys005",$lang['err']['exit-action']);
}

// ------------- FUNCTIONS ---------------------------------------------------------------------------

function shorten_fleet($eventid, $fleetnum)
{
    global $race_o;

    if ($_SESSION["e_$eventid"]["fl_$fleetnum"]['maxlap'] != 0)
    {
        $new_finish_lap = $_SESSION["e_$eventid"]["fl_$fleetnum"]['currentlap'] + 1;
        $rs = $race_o->race_laps_set($fleetnum, $new_finish_lap);

        if ($rs['result'] == "less_than_current")
        {
            $msg['type'] = "invalid";
            $msg['text'] = "[{$_SESSION["e_$eventid"]["fl_$fleetnum"]['name']} fleet] <b>shorten course not applied - leader already on last lap</b><br>";
        }
        elseif ($rs['result'] == "ok")
        {
            $msg['type'] = "success";
            $msg['text'] = "[{$_SESSION["e_$eventid"]["fl_$fleetnum"]['name']} fleet] <b>shortened to lap {$rs['finishlap']}</b><br>";
        }
        else
        {
            $msg['type'] = "unknown";
            $msg['text'] = "[{$_SESSION["e_$eventid"]["fl_$fleetnum"]['name']} fleet] <b>unknown problem attempting to shorten course</b></b>";
        }
    }
    else
    {
        $msg['type'] = "invalid";
        $msg['text'] = "[{$_SESSION["e_$eventid"]["fl_$fleetnum"]['name']} fleet] <b>shorten course not applied - no laps set</b><br>";
    }
    u_writelog($msg['text'], $eventid);

    return $msg;
}


function update_racestate($eventid, $fleetnum, $state, $lap)
{
    global $race_o;

    $update = array();
      
    if ($state == "time" OR $state == "finish" OR $state == "first_finish" OR $state == "force_finish")
    {
        if ($lap > $_SESSION["e_$eventid"]["fl_$fleetnum"]['currentlap'])   # check if current lap for this fleet has changed
        {
            $update['currentlap'] = $lap;
            $_SESSION["e_$eventid"]["fl_$fleetnum"]['currentlap'] = $lap;
        }
        if ($state == "finish" OR $state == "first_finish")                 # check if fleet status has changed
        {
            if ($_SESSION["e_$eventid"]["fl_$fleetnum"]['status'] == "inprogress")
            {
                $update['status'] = "finishing";
                $_SESSION["e_$eventid"]["fl_$fleetnum"]['status'] = "finishing";
            }
        }
        elseif($state == "force_finish")                                    # don't change status if a force finisher'
        {
            
        }
    }
    if (!empty($update))
    {        
        $check = $race_o->racestate_update($update, array("eventid"=>"$eventid", "race"=>"$fleetnum"));
    }    
}

function check_double_click($eventid, $entryid, $server_time)       
// return to timer page if double click of same boat within 5 seconds
{
    global $page;
    global $boat;
    global $g_timer_doubleclick;
    
    if ($entryid == $_SESSION["e_$eventid"]['lastclick']['entryid'] AND 
            ABS($server_time - $_SESSION["e_$eventid"]['lastclick']['clicktime']) <= 5 )
    {
        u_growlSet($eventid, $page, $g_timer_doubleclick, array($boat));
        header("Location: timer_pg.php?eventid=$eventid");
        exit();
    }
}

function check_race_started($eventid, $start, $server_time)
// check that this fleet has started
{
    global $page;
    global $g_timer_racenotstarted;
    
    if ($server_time < $_SESSION["e_$eventid"]["st_$start"]['starttime'])
    {
        u_growlSet($eventid, $page, $g_timer_racenotstarted, array());
        header("Location: timer_pg.php?eventid=$eventid");
        exit(); 
    }
}

