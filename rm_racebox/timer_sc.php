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
require_once ("./include/rm_racebox_lib.php");

// start session
u_startsession("sess-rmracebox", 10800);

// arguments
$eventid   = u_checkarg("eventid", "checkintnotzero","");    // eventid (required)
$pagestate = u_checkarg("pagestate", "set", "", "");         // pagestate (required)
$fleet     = u_checkarg("fleet", "set", "", "");             // $fleet

// page initialisation
u_initpagestart($eventid, $page, false);

// classes
require_once ("{$loc}/common/classes/db_class.php"); 
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/race_class.php");
require_once ("{$loc}/common/classes/bunch_class.php");

// page controls
include("./templates/growls.php");


if ($eventid AND $pagestate)
{
    $db_o    = new DB;
    if ($fleet) { $_SESSION["e_$eventid"]['fleet_context'] = "fleet$fleet"; }  // set context
    $event_o = new EVENT($db_o);
    $race_o  = new RACE($db_o, $eventid);
    if ($_SESSION['racebox_timer_bunch'])
    {
        $bunch_o  = new BUNCH($eventid, "timer_sc.php", $_SESSION["e_$eventid"]['bunch']);
    }

/* ------- TIME LAP FOR BOAT ------------------------------------------------------------------------------- */
    if ($pagestate == "timelap")
    {
        //u_writedbg("<pre>passed to timelap: ".print_r($_REQUEST,true)."</pre>", __CLASS__,__FUNCTION__,__LINE__);

        $if_err = false;
        empty($_REQUEST['entryid']) ? $if_err = true : $entryid = $_REQUEST['entryid'];
        empty($_REQUEST['start'])   ? $if_err = true : $start = $_REQUEST['start'];    // need to check this
        empty($_REQUEST['boat'])    ? $if_err = true : $boat = $_REQUEST['boat'];
        empty($_REQUEST['status'])  ? $if_err = true : $boatstatus = $_REQUEST['status'];
        empty($_REQUEST['finishlap']) ? $if_err = true : $finishlap = $_REQUEST['finishlap'];

        if ($if_err)
        {
            $reason = "missing information";
            u_writelog("$boat - lap timing failed - $reason", $eventid);
            u_growlSet($eventid, $page, $g_timer_timingfailed, array($boat, $reason));
        }
        else
        {
            empty($_REQUEST['lap'])   ? $lap = 0     : $lap = $_REQUEST['lap'];
            empty($_REQUEST['pn'])    ? $pn = 0      : $pn = $_REQUEST['pn'];
            empty($_REQUEST['etime']) ? $last_et = 0 : $last_et = $_REQUEST['etime'];

            // do checks before registering new lap time
            check_double_click($eventid, $entryid, $_SERVER['REQUEST_TIME']); # return to timer page if double click of same boat
            check_race_started($eventid, $start, $_SERVER['REQUEST_TIME']);   # return to time page if race not started

            $status = $race_o->entry_time($entryid, $fleet, $lap, $finishlap, $pn, $_SERVER['REQUEST_TIME'], $boatstatus, $last_et, false);  // log lap time

            if ($status == "time" OR $status == "finish" OR $status == "first_finish" OR $status == "force_finish")   // valid status
            {
                $newlap = $lap + 1;

                $_SESSION["e_$eventid"]['result_valid']   = false;    // mark results as requiring update
                $_SESSION["e_$eventid"]['result_publish'] = false;    // mark publishing as requiring refresh
                $_SESSION["e_$eventid"]['lastclick'] = array("entryid" => $entryid, "clicktime" => $_SERVER['REQUEST_TIME'], "boat" => $boat);

                if ($status == "time")
                {
                    $msg = "lap $newlap: $boat ";
                }
                elseif ($status == "finish" or $status == "force_finish")
                {
                    $msg = "lap $newlap: $boat -- finished";
                    if ($_SESSION['timer_options']['growl_finish'] == "on")
                    {
                        u_growlSet($eventid, $page, $g_timer_finish, array($boat));
                    }
                }
                elseif ($status == "first_finish")
                {
                    $msg = "lap $newlap: $boat -- first finished";
                    u_growlSet($eventid, $page, $g_timer_firstfinish, array($boat));
                }
                // FIXME entry_calc_et is already called in entry_time (could call it outside of entry_time and pass it as arg)
                $etime_secs = $race_o->entry_calc_et($_SERVER['REQUEST_TIME'], $_SESSION["e_$eventid"]["fl_$fleet"]['starttime']);
                $msg.= "[ et: ". gmdate("H:i:s", $etime_secs)." ($etime_secs secs) ]";

                u_writelog($msg, $eventid);

                // remove entry from bunch if it exists
                if ($_SESSION['racebox_timer_bunch'])
                {
                    $nodeid = $bunch_o->search_nodes($entryid);   // check if in bunch array
                    if ($nodeid !== false)
                    {
                        unset($_SESSION["e_$eventid"]['bunch'][$nodeid]);
                    }
                }
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

/* ------- FINISH A BOAT ------------------------------------------------------------------------------- */
    elseif ($pagestate == "finish")   // FIXME this code should be combined with timelap above
    {


        //u_writedbg("<pre>passed to finish: ".print_r($_REQUEST,true)."</pre>", __CLASS__,__FUNCTION__,__LINE__);

        $if_err = false;
        empty($_REQUEST['entryid']) ? $if_err = true : $entryid = $_REQUEST['entryid'];
        empty($_REQUEST['start'])   ? $if_err = true : $start = $_REQUEST['start'];
        empty($_REQUEST['boat'])    ? $if_err = true : $boat = $_REQUEST['boat'];
        empty($_REQUEST['status'])  ? $if_err = true : $status = $_REQUEST['status'];
        empty($_REQUEST['finishlap'])    ? $if_err = true : $finishlap = $_REQUEST['finishlap'];

        if ($if_err)
        {
            $reason = "missing information";
            u_writelog("$boat - finish timing failed - $reason", $eventid);
            u_growlSet($eventid, $page, $g_timer_finishfailed, array($boat, $reason));
        }
        else
        {
            empty($_REQUEST['lap'])   ? $lap = 0     : $lap = $_REQUEST['lap'];
            empty($_REQUEST['pn'])    ? $pn = 0      : $pn = $_REQUEST['pn'];
            empty($_REQUEST['etime']) ? $last_et = 0 : $last_et = $_REQUEST['etime'];

            check_double_click($eventid, $entryid, $_SERVER['REQUEST_TIME']); // return to timer page if double click of same boat
            check_race_started($eventid, $start, $_SERVER['REQUEST_TIME']);   // return to timer page if race not started

            $status = $race_o->entry_time($entryid, $fleet, $lap, $finishlap, $pn, $_SERVER['REQUEST_TIME'], $status, $last_et, true);
            if ($status == "force_finish")
            {
                $newlap = $lap + 1;
                $_SESSION["e_$eventid"]['result_valid']   = false;
                $_SESSION["e_$eventid"]['result_publish'] = false;
                $_SESSION["e_$eventid"]['lastclick'] = array(
                    "entryid"   => $entryid,
                    "clicktime" => $_SERVER['REQUEST_TIME'],
                    "boat"      => $boat
                );
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

/* ------- SET FINISH FOR PURSUIT -------------------------------------------------------------------------- */
    elseif ($pagestate == "setfinishpursuit")
    {

        // set session with entryid
        empty($_REQUEST['entryid']) ? $_SESSION['pursuitcfg']['entryid'] = "" : $_SESSION['pursuitcfg']['entryid'] = $_REQUEST['entryid'];
    }

/* ------- PROCESS FINISH FOR PURSUIT -------------------------------------------------------------------------- */
    elseif ($pagestate == "processfinishpursuit")
    {
        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        // process form   [set lap, f_line, f_pos, code, status]
        $entryid = u_checkarg("entryid", "checkintnotzero", "");
        $prev_fl = u_checkarg("prev_fl", "set", "", "");
        $boat    = u_checkarg("boat", "set", "", "");
        $prev_status = u_checkarg("prev_status", "set", "", "");
        $f_line  = u_checkarg("f_line", "checkintnotzero", "");
        $f_pos   = u_checkarg("f_pos", "checkintnotzero", "");
        $lap     = u_checkarg("lap", "checkintnotzero", "");
        $code    = u_checkarg("code", "set", "", "");

        if ($prev_status == "X") {
            if (empty($code) OR ($code == "ZFP" or $code == "SCP" or $code == "DPI")) {
                $status = "F";
            } else {
                $status = "X";
            }
        } elseif ($prev_status == "R") {
            $status = "F";
        } else {
            $status = $prev_status;
        }

        // update t_race
        $update = array(
            "f_line" => $f_line,
            "f_pos"  => $f_pos,
            "lap"    => $lap,
            "code"   => $code,
            "status" => $status
        );

        // check update
        $err_txt = array();

        if (empty($code))
        {
            if (empty($f_line) or $f_line > 5) {$err_txt[] = "line must be between 1 and 5";}
            if (empty($f_pos) or $f_pos < 1) {$err_txt[] = "position must be > 0";}
            if (empty($lap) or $lap < 1) {$err_txt[] = "laps must be > 0";}
        }


        if (empty($err_txt))  // no input errors - process finish data
        {
            // need to check if boat has already been finished at an earlier (higher) finish line
            $report = "";
            if (empty($prev_fl))  // no finish entered for this boat so far
            {
                $set_finish = true;
                $report.= "finished:<br> line $f_line - position $f_pos";
            }
            else                  // we already have a finish
            {
                if ($f_line >= $prev_fl)   // new data seems like the first finish
                {
                    $set_finish = true;
                    $report.= "updated finish: line $f_line - position $f_pos";
                }
                else                       // old data seems like the first finish
                {
                    $set_finish = false;
                    $report.= " already finished at line $prev_fl - NOT updated";
                }
            }

            if ($set_finish)
            {
                $race_o->entry_update($entryid, $update);
                u_writelog("$boat - finished at line $f_line, position $f_pos - $report", $eventid);
            }
            else
            {
                u_writelog("$boat - $report", $eventid);
            }

        }
        else  // report input errors
        {
            $set_finish = false;
            $err_rpt = implode("<br>", $err_txt);
            $report = "finish data problem ...<br>$err_rpt";
        }

        // set last boat details
        $_SESSION['pursuitcfg']['last-boat'] = array("boat"=>$boat, "f_line"=>$f_line, "f_pos"=>$f_pos, "lap"=>$lap, "set_finish"=>$set_finish, "report"=>$report);

        // unset entryid
        $_SESSION['pursuitcfg']['entryid'] = "";


    }

/* ------- CLEAR FINISH FOR PURSUIT ----------------------------------------------------------------------- */
    elseif  ($pagestate == "clearfinishpursuit")
    {
        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        $entryid = u_checkarg("entryid", "checkintnotzero", "");
        $boat    = u_checkarg("boat", "set", "");

        $update = array ("f_line" => 0, "f_pos" => 0, "points" => 0.0, "lap" => 0, "status" => 'R');
        $result = $race_o->entry_update($entryid, $update);

        if ($result > 0)
        {
            $_SESSION['pursuitcfg']['last-boat'] = array("boat"=>$boat, "f_line"=>0, "f_pos"=>0, "lap"=>0,
                                                         "set_finish"=>true, "report"=>"finish cleared");
            u_growlSet($eventid, $page, $g_timer_pursuitclearfinish, array($boat));
            u_writelog("$boat - finishing details cleared", $eventid);
        }
        elseif ($result == -1)
        {
            $reason = "clearing finish position details failed - try making changes manually";
            u_growlSet($eventid, $page, $g_timer_pursuitclearfinish_fail, array($boat, $reason));
        }
        elseif ($result == 0)
        {
            $_SESSION['pursuitcfg']['last-boat'] = array();
            u_growlSet($eventid, $page, $g_timer_pursuitclearfinish_fail, array($boat, "not finished - nothing to clear"));
        }
    }

/* ------- SWAP POSITION FOR PURSUIT ----------------------------------------------------------------------- */

    elseif  ($pagestate == "swappositionpursuit")
    {
        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        $entryid = u_checkarg("entryid", "checkintnotzero", "");
        $boat    = u_checkarg("boat", "set", "");
        $line    = u_checkarg("f_line", "checkintnotzero", "");
        $dir     = u_checkarg("dir", "set", "");

        $result = $race_o->race_line_swap_pursuit($entryid, $line, $dir, $boat);

        if ($result > 0)
        {
            u_growlSet($eventid, $page, $g_timer_pursuitswap, array($boat, u_numordinal($result)));
            u_writelog("$boat - position swapped to ".u_numordinal($result), $eventid);
        }
        elseif ($result == -1)
        {
            $reason = "position updates failed - try making changes manually";
            u_growlSet($eventid, $page, $g_timer_pursuitswap_fail, array($boat, $reason));
        }
        elseif ($result == -2)
        {
            $dir == "up" ? $reason = "boat above has more laps" : $reason = "boat below has more laps";
            u_growlSet($eventid, $page, $g_timer_pursuitswap_fail, array($boat, $reason));
        }
        elseif ($result == -3)
        {
            $dir == "up" ? $reason = "no previous boat on his finish line" : $reason = "no following boat on his finish line";
            u_growlSet($eventid, $page, $g_timer_pursuitswap_fail, array($boat, $reason));
        }
        elseif ($result == -4)
        {
            u_growlSet($eventid, $page, $g_timer_pursuitswap_fail, array($boat, "swap direction not recognised"));
        }
    }

/* ------- RENUMBER FOR PURSUIT ----------------------------------------------------------------------- */
    elseif  ($pagestate == "renumberlinepursuit")
    {
        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        $line  = u_checkarg("line", "checkintnotzero", "");
        $result = $race_o->race_line_renumber_pursuit($line);

        if ($result == 1)
        {
            u_growlSet($eventid, $page, $g_timer_pursuitrenumber, array($line));
        }
        elseif ($result == -1)
        {
            u_growlSet($eventid, $page, $g_timer_pursuitrenumber_fail, array($line, "database update failed"));
        }
        elseif ($result == -2)
        {
            u_growlSet($eventid, $page, $g_timer_pursuitrenumber_fail, array($line, "no boats on line $line"));
        }

    }

/* ------- SET SCORING CODE FOR BOAT ----------------------------------------------------------------------- */
    elseif  ($pagestate == "setcode")
    {
        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        if (!empty($_REQUEST['fleet']))
        {
            $setcode = set_code($eventid, $_REQUEST);

            if ($setcode !== true) { u_growlSet($eventid, $page, $g_timer_setcodefailed, array($_REQUEST['boat'], $setcode)); }
        }
        else
        {
            error_log("timer_sc.php/pagestate=setcode : fleet argument not set", 3, $_SESSION['syslog']);
        }
    }

/* ------- REMOVE LAST LAP TIMING  ------------------------------------------------------------------- */
    elseif  ($pagestate == "undo")
    {
        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        $entry = $race_o->entry_time_undo();
            
        if ($entry == 0)
        {
            u_growlSet($eventid, $page, $g_timer_undo_none, array());
        }
        elseif($entry)
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

/* ------- REMOVE LAST LAP TIMING FOR SPECIFIC BOAT ---------------------------------------------------- */
    elseif  ($pagestate == "undoboat")
    {
        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        $entry = $race_o->entry_time_undo($_REQUEST['entryid']);

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

/* ------- SHORTEN FLEET ------------------------------------------------------------------------------- */
    elseif  ($pagestate == "shorten")
    {
        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        // shortens one fleet to finish
        if ($fleet)
        {
            $rs = shorten_fleet($eventid, $fleet);
            if ($rs['type'] == "unknown") {
                $msgtype = "danger";
            } elseif ($rs['type'] == "invalid") {
                $msgtype = "warning";
            } else {
                $msgtype = "info";
            }
            $msg = $rs['text'];

            $g_timer_shortenfleet_report['type'] = $msgtype;
            u_growlSet($eventid, $page, $g_timer_shortenfleet_report, array($msg));
            u_writelog("shorten course fleet [$fleet]", $eventid);
        }
        else
        {
            u_growlSet($eventid, $page, $g_timer_shorten_fail, array("fleet not recognised"));
            u_writelog("shorten course FAILED - fleet not recognised [$fleet]", $eventid);
        }
    }

/* ------- SHORTEN ALL FLEETS ------------------------------------------------------------------------------- */
    elseif  ($pagestate == "shortenall") {

        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        $msg = "";
        $msgtype = "info";
        for ($i = 1; $i <= $_SESSION["e_$eventid"]['rc_numfleets']; $i++)
        {
            $requested_finish_lap = 0;
            if (array_key_exists("shlaps$i", $_REQUEST))
            {
                $requested_finish_lap = $_REQUEST["sh_laps$i"];
            }

            $rs = shorten_fleet($eventid, $i, $requested_finish_lap);
            $msg.= $rs['text'];
            if ($msgtype != "danger") {
                if ($rs['type'] == "unknown") {
                    $msgtype = "danger";
                } elseif ($rs['type'] == "invalid") {
                    $msgtype = "warning";
                }
            }
        }
        $g_timer_shortenall_report['type'] = $msgtype;
        u_growlSet($eventid, $page, $g_timer_shortenall_report, array($msg));
        u_writelog("shorten course for all fleets", $eventid);
    }

/* ------- CHANGEFINISH ------------------------------------------------------------------------------- */
    elseif ($pagestate == "changefinish")
    {
        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        $growl_txt = "";
        $racestate = $race_o->racestate_get();   // get racestate for all fleets

        // loop over fleets
        foreach ($racestate as $i=>$fleet)
        {
            $fleetnum = $fleet['fleet'];
            $new_maxlap = $_REQUEST['laps'][$i];
            if ($fleet['maxlap'] != $new_maxlap)  // laps have been changed - process change
            {
                $success = fleet_changefinishlap($fleetnum, $fleet, $new_maxlap, $_SESSION["e_$eventid"]["fl_$i"]['currentlap']);
                if ($success)
                {
                    $log_txt = "{$fleet['racename']} finish changed to lap $new_maxlap ";
                }
                else
                {
                    $log_txt = "attempt to change finish lap in {$fleet['racename']} to lap $new_maxlap - FAILED ";
                }
                u_writelog($log_txt, $eventid);
                $growl_txt.= $log_txt."<br>";
            }
        }
        u_growlSet($eventid, $page, $g_results_changefinish, array($growl_txt));

        $_SESSION["e_$eventid"]['result_valid'] = false;     // reset rescore flag

    }

    elseif ($pagestate == "undoshorten")
    {
        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        $lapsetfail = false;
        $growlmsg   = "";

        //u_writedbg("laps change requested: ".print_r($_REQUEST['laps'],true), __CLASS__, __FUNCTION__, __LINE__);

        for ($i=1; $i<=$_SESSION["e_$eventid"]['rc_numfleets']; $i++)
        {
            $fleetname = $_SESSION["e_$eventid"]["fl_$i"]['name'];
            $current_maxlap = $_SESSION["e_$eventid"]["fl_$i"]['maxlap'];
            //$rs = $race_o->race_laps_set("set", $i, $_SESSION["e_$eventid"]["fl_$i"]['scoring'], $_REQUEST['laps'][$i]);
            $rs = $race_o->race_laps_set("shorten", $i, $_SESSION["e_$eventid"]["fl_$i"]['scoring'], $_REQUEST['laps'][$i]);

            $str = array(
                "pursuit_race"      => "&nbsp;&nbsp;$fleetname is a pursuit race - laps cannot be set <br>",
                "less_than_current" => "&nbsp;&nbsp;$fleetname - laps not changed, boats already on lap {$rs['currentlap']} <br>",
                "finishing"         => "&nbsp;&nbsp;$fleetname - laps not changed, boats already finishing <br>",
                "already_set"       => "&nbsp;&nbsp;$fleetname - laps already set to {$rs['finishlap']} <br>",
            );

            //echo "<pre>$fleetname - ".print_r($rs,true)."</pre>";
            if (empty($rs['result']) or $rs['result'] == "failed")          // lap change failed
            {
                u_writelog("setlaps: $fleetname - failed [{$_REQUEST['laps'][$i]} laps]", $eventid);
                $growlmsg.= "&nbsp;&nbsp;$fleetname - laps set FAILED <br>";
                $lapsetfail = true;
            }
            elseif($_REQUEST['laps'][$i] == $current_maxlap)                // no change to lap for this fleet
            {
                $growlmsg.= "";
            }
            else
            {
                if ($rs['result'] == "ok")                                  // laps changed
                {
                    u_writelog("setlaps: $fleetname - {$_REQUEST['laps'][$i]} laps", $eventid);
                    $growlmsg.= "&nbsp;&nbsp;$fleetname - laps changed to {$_REQUEST['laps'][$i]} <br>";
                }
                else
                {
                    $growlmsg.= $str["{$rs['result']}"];
                    $lapsetfail = true;
                }
            }
        }

        if ($lapsetfail)
        {
            $growlmsg  = "Setting laps:<br>".$growlmsg;
            u_growlSet($eventid, $page, $g_race_lapset_fail, array($growlmsg));
        }
        else
        {
            empty($growlmsg) ?  $growlmsg = "Setting laps - no changes made" : $growlmsg = "Setting laps:<br>".$growlmsg;
            u_growlSet($eventid, $page, $g_race_lapset_success, array($growlmsg));
        }
    }


    /* ------- FORGOT SHORTEN PROCESSING ------------------------------------------------------------------------------- */
    elseif ($pagestate == "forgotshorten")   // FIXME - this code is the same as set all laps on race_sc - refactor accordingly
    {
        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        $lapsetfail = false;
        $growlmsg   = "";

        //u_writedbg("laps change requested: ".print_r($_REQUEST['laps'],true), __CLASS__, __FUNCTION__, __LINE__);

        for ($i=1; $i<=$_SESSION["e_$eventid"]['rc_numfleets']; $i++)
        {
            $fleetname = $_SESSION["e_$eventid"]["fl_$i"]['name'];
            $current_maxlap = $_SESSION["e_$eventid"]["fl_$i"]['maxlap'];
            $rs = $race_o->race_laps_set("set", $i, $_SESSION["e_$eventid"]["fl_$i"]['scoring'], $_REQUEST['laps'][$i]);

            $str = array(
                "pursuit_race"      => "&nbsp;&nbsp;$fleetname is a pursuit race - laps cannot be set <br>",
                "less_than_current" => "&nbsp;&nbsp;$fleetname - laps not changed, boats already on lap {$rs['currentlap']} <br>",
                "finishing"         => "&nbsp;&nbsp;$fleetname - laps not changed, boats already finishing <br>",
                "already_set"       => "&nbsp;&nbsp;$fleetname - laps already set to {$rs['finishlap']} <br>",
            );

            //echo "<pre>$fleetname - ".print_r($rs,true)."</pre>";
            if (empty($rs['result']) or $rs['result'] == "failed")          // lap change failed
            {
                u_writelog("setlaps: $fleetname - failed [{$_REQUEST['laps'][$i]} laps]", $eventid);
                $growlmsg.= "&nbsp;&nbsp;$fleetname - laps set FAILED <br>";
                $lapsetfail = true;
            }
            elseif($_REQUEST['laps'][$i] == $current_maxlap)                // no change to lap for this fleet
            {
                $growlmsg.= "";
            }
            else
            {
                if ($rs['result'] == "ok")                                  // laps changed
                {
                    u_writelog("setlaps: $fleetname - {$_REQUEST['laps'][$i]} laps", $eventid);
                    $growlmsg.= "&nbsp;&nbsp;$fleetname - laps changed to {$_REQUEST['laps'][$i]} <br>";
                }
                else
                {
                    $growlmsg.= $str["{$rs['result']}"];
                    $lapsetfail = true;
                }
            }
        }

        if ($lapsetfail)
        {
            $growlmsg  = "Setting laps:<br>".$growlmsg;
            u_growlSet($eventid, $page, $g_race_lapset_fail, array($growlmsg));
        }
        else
        {
            empty($growlmsg) ?  $growlmsg = "Setting laps - no changes made" : $growlmsg = "Setting laps:<br>".$growlmsg;
            u_growlSet($eventid, $page, $g_race_lapset_success, array($growlmsg));
        }
    }


/* ------- BUNCH PROCESSING ------------------------------------------------------------------------------- */
    elseif ($pagestate == "bunch")
    {
        //echo "<pre>".print_r($_REQUEST,true)."</pre>";

        if ($_REQUEST['action'] == "addnode")
        {
            $params = array(
                "entryid" => $_REQUEST['entryid'],
                "boat"    => $_REQUEST['boat'],
                "fleet"   => $_REQUEST['fleet'],
                "start"   => $_REQUEST['start'],
                "lap"     => $_REQUEST['lap'],
                "finishlap" => $_REQUEST['finishlap'],
                "pn"      => $_REQUEST['pn'],
                "etime"   => $_REQUEST['etime'],
                "status"  => $_REQUEST['status']
            );

            $_REQUEST['lastlap'] == "true" ? $lastlap = true : $lastlap = false;
            $node = array(
                "entryid" => $_REQUEST['entryid'],
                "lastlap" => $lastlap,
                "label"   => $_REQUEST['bunchlbl'],
                "link"    => "timer_sc.php?eventid=$eventid&pagestate=timelap&".http_build_query($params)
            );

            $success = $bunch_o->add_node($node);

            if ($success)
            {
                $_SESSION["e_$eventid"]['bunch'] = $bunch_o->get_bunch();
            }
            else
            {
                u_growlSet($eventid, $page, $g_timer_addbunch_fail, array());
            }

        }
        elseif ($_REQUEST['action'] == "delnode")
        {
            $_SESSION["e_$eventid"]['bunch'] = $bunch_o->del_node($_REQUEST['node']);
        }
        elseif ($_REQUEST['action'] == "up")
        {
            $_SESSION["e_$eventid"]['bunch'] = $bunch_o->siftup_node($_REQUEST['node']);
        }
        elseif ($_REQUEST['action'] == "down")
        {
            $_SESSION["e_$eventid"]['bunch'] = $bunch_o->siftdown_node($_REQUEST['node']);
        }

    }

    else
    {
        u_growlSet($eventid, $page, $g_sys_invalid_pagestate, array($pagestate, $page)); //
    }

    // check race state / update session
    // FIXME - for a lot of functions this only needs to look at one fleet
    $race_o->racestate_updatestatus_all($_SESSION["e_$eventid"]['rc_numfleets'], $page);

    // return to timer page
    if (!$stop_here) { header("Location: timer_pg.php?eventid=$eventid"); exit(); }
}
else
{
    u_exitnicely($scriptname, $eventid, "$page page has an invalid or missing event identifier [{$_REQUEST['eventid']}] or page state [{$_REQUEST['pagestate']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// ------------- FUNCTIONS ---------------------------------------------------------------------------

function shorten_fleet($eventid, $fleetnum, $new_finish_lap = 0)
{
    global $race_o;
    $fleetname = $_SESSION["e_$eventid"]["fl_$fleetnum"]['name'];

    $str = array(
        "ok"                => "$fleetname - shortened to lap {}",
        "pursuit_race"      => "$fleetname is a pursuit race - cannot be shortened",
        "less_than_current" => "$fleetname - NOT shortened, boats already on lap {}",
        "finishing"         => "$fleetname - NOT shortened, boats already finishing ",
        "already_set"       => "$fleetname - NOT shortened, boats already on lap {} ",
        "unknown"           => "$fleetname - NOT shortened for reasons unknown ",
        "no_laps_set"       => "$fleetname - NOT shortened, no laps set for this fleet"
    );

    if ($_SESSION["e_$eventid"]["fl_$fleetnum"]['maxlap'] != 0)
    {
        if (empty($new_finish_lap))      // set finish lap if not specified by user
        {
            $new_finish_lap = $_SESSION["e_$eventid"]["fl_$fleetnum"]['currentlap'] + 1;
        }
        $rs = $race_o->race_laps_set("shorten", $fleetnum, $_SESSION["e_$eventid"]["fl_$fleetnum"]['scoring'], $new_finish_lap);

        $msg['text'] = u_format($str["{$rs['result']}"], array($rs['finishlap']));
        $rs['result'] == "ok" ? $msg['type'] = "info" : $msg['type'] = "invalid";
    }
    else
    {
        $rs['result'] = $str["no_laps_set"];
        $msg['text'] = $rs['result'];
        $msg['type'] = "invalid";

    }
    u_writelog($msg['text'], $eventid);

    $msg['text'] = "&nbsp;&nbsp;- ".$msg['text']."<br>";

    return $msg;
}


//function update_racestate_lap($eventid, $fleetnum, $state, $lap)
//{
//    global $race_o;
//
//    $update = array();
//
//    if ($state == "time" OR $state == "finish" OR $state == "first_finish" OR $state == "force_finish")
//    {
//        if ($lap > $_SESSION["e_$eventid"]["fl_$fleetnum"]['currentlap'])   // check if current lap for this fleet has changed
//        {
//            $update['currentlap'] = $lap;
//            $_SESSION["e_$eventid"]["fl_$fleetnum"]['currentlap'] = $lap;
//        }
//
//        if ($state == "finish" OR $state == "first_finish")                 // check if fleet status has changed - not changed if force_finish
//        {
//            if ($_SESSION["e_$eventid"]["fl_$fleetnum"]['status'] == "inprogress")  // set race state to finishing
//            {
//                $update['status'] = "finishing";
//                $_SESSION["e_$eventid"]["fl_$fleetnum"]['status'] = "finishing";
//            }
//        }
//    }
//
//    // check if boats still racing in this fleet
//    if($race_o->fleet_race_stillracing($fleetnum)) { $update['status'] = "allfinished"; }
//
//    if (!empty($update))
//    {
//        $check = $race_o->racestate_update($update, array("eventid"=>"$eventid", "fleet"=>"$fleetnum"));
//    }
//}

function check_double_click($eventid, $entryid, $server_time)       
// return to timer page if double click of same boat within 3 seconds
{
    global $page;
    global $boat;
    global $g_timer_doubleclick;
    
    if ($entryid == $_SESSION["e_$eventid"]['lastclick']['entryid'] AND 
            ABS($server_time - $_SESSION["e_$eventid"]['lastclick']['clicktime']) <= 3 )
    {
        u_growlSet($eventid, $page, $g_timer_doubleclick, array($boat));
        header("Location: timer_pg.php?eventid=$eventid");
        exit();
    }
}

function check_race_started($eventid, $fleetnum, $server_time)
// check that this fleet has started
{
    global $page;
    global $g_timer_racenotstarted;

    if ($server_time < $_SESSION["e_$eventid"]["fl_$fleetnum"]['starttime'])
    {
        u_growlSet($eventid, $page, $g_timer_racenotstarted, array());
        header("Location: timer_pg.php?eventid=$eventid");
        exit(); 
    }
}

// FIXME - this is also used on timer page
//function fleet_changefinishlap($fleetnum, $fleet, $new_maxlap, $current_lap)
//{
//    global  $race_o;
//    $status = true;
//
//    $race = $race_o->race_getresults($fleetnum);  // get race data for this fleet
//    //u_writedbg("<pre>BEFORE: ".print_r($race,true)."</pre>", __FILE__, __FUNCTION__, __LINE__);
//
//    foreach($race as $boat)
//    {
//        $update = array();
//
//        // set data for change in finish lap
//        if ($fleet['racetype'] == "average")    // average lap race: set each boat to an individual finish lap
//        {
//            /*
//             *  All boats that have completed a lap get a finish based on the new finish lap specified
//             *  or the last lap that they completed.  Boats that haven't finished a lap will not be finished - OOD
//             *  needs to do that with a DNF.  If specified finish lap is > than the current leaders lap then it
//             *  will still finish everyone BUT will aggregate the ET to the specified finish lap
//             */
//
//            if ($boat['lap'] >= 1)              // boat has completed at least one lap - can be scored
//            {
//                $boat['status'] == "X" ? $set_status = "X" : $set_status = "F" ;   // set status to finished unless it is excluded
//
//                // find last completed lap for this boat that is <= finishlap
//                //$boat['lap'] > $new_maxlap ? $new_lap = $new_maxlap : $new_lap = $boat['lap'];
//
//                // get lap time data for that new finish (0 if lap not completed)
//                $lapdata = $race_o->race_getlap_etime($boat, $new_maxlap);
//
//                if ($lapdata['etime'] > 0)      // boat did complete new_lap so update all time details for that lap
//                {
//                    $ptime = $race_o->entry_calc_pt($lapdata['etime'], 0, $new_maxlap);
//                    $update = array("status" => $set_status, "finishlap" => $new_maxlap, "lap" => $new_maxlap,
//                        "etime" => $lapdata['etime'], "ctime" => $lapdata['ctime'], "ptime" => $ptime,
//                        "clicktime" => $lapdata['clicktime']);
//                }
//                else                            // just use the last lap they did (time details will already be ok
//                {
//                    $update = array("status" => $set_status, "finishlap" => $new_maxlap);
//                }
//            }
//            else                                // boat hasn't completed a lap - can't be scored - just reset finish lap
//            {
//                $update = array("finishlap" => $new_maxlap);
//            }
//        }
//        else                                        // handicap or fleet race: set all boats to same finish lap
//        {
//            /*
//             *  Finishes all boats that have completed the specified finish lap (new_maxlap).  Boats who haven't
//             *  completed the specified lap will need to be marked DNF by the OOD if not already done.
//             */
//
//            if ($new_maxlap > $current_lap)         // boat will not have finished
//            {
//                $boat['status'] == "F" ?  $set_status = "R" : $set_status = $boat['status'];
//                $update = array("status" => $set_status, "finishlap" => $new_maxlap);
//            }
//
//            else
//            {
//                // get lap time data for that new finish (0 if lap not completed)
//                $lapdata = $race_o->race_getlap_etime($boat, $new_maxlap);
//                if ($lapdata['etime'] > 0)      // boat did complete new_lap so change all time details to that lap
//                {
//                    $ptime = $race_o->entry_calc_pt($lapdata['etime'], 0, $new_maxlap);
//                    $update = array("status" => "F", "finishlap" => $new_maxlap, "lap" => $new_maxlap,
//                        "etime" => $lapdata['etime'], "ctime" => $lapdata['ctime'], "ptime" => $ptime,
//                        "clicktime" => $lapdata['clicktime']);
//                }
//                else                            // just set new finish lap
//                {
//                    $boat['status'] == "X" ? $set_status = "X" : $set_status = "R" ;   // set status to racing unless it is excluded
//                    $update = array("status" => $set_status, "finishlap" => $new_maxlap);
//                }
//            }
//        }
//
//        // update boat details
//        //u_writedbg("<pre>{$boat['class']} - {$boat['sailnum']}: ".print_r($update,true)."</pre>", __FILE__, __FUNCTION__, __LINE__);
//        $race_o->entry_update($boat['id'], $update);
//    }
//
//    $race = $race_o->race_getresults($fleetnum);  // get race data for this fleet
//    //u_writedbg("<pre>AFTER: ".print_r($race,true)."</pre>", __FILE__, __FUNCTION__, __LINE__);
//
//    return $status;
//}

