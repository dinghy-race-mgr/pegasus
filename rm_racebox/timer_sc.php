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

u_initpagestart($_REQUEST['eventid'], $page, false);   // starts session and sets error reporting


// app classes
require_once ("{$loc}/common/classes/db_class.php"); 
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/race_class.php");
require_once ("{$loc}/common/classes/bunch_class.php");

// app includes
require_once ("./include/rm_racebox_lib.php");

include("./templates/growls.php");

// process standard parameters  (eventid, pagestate, fleet)
$eventid   = u_checkarg("eventid", "checkintnotzero","");
$pagestate = u_checkarg("pagestate", "set", "", "");
$fleet     = u_checkarg("fleet", "set", "", "");

//echo "<pre>".print_r($_REQUEST,true)."</pre>";
//exit();

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


    if ($pagestate == "timelap")
    {

        $if_err = false;
        empty($_REQUEST['entryid']) ? $if_err = true : $entryid = $_REQUEST['entryid'];
        empty($_REQUEST['start'])   ? $if_err = true : $start = $_REQUEST['start'];
        empty($_REQUEST['boat'])    ? $if_err = true : $boat = $_REQUEST['boat'];
        if ($if_err)
        {
            $reason = "missing information\"";
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

            $status = $race_o->entry_time($entryid, $fleet, $lap, $pn, $_SERVER['REQUEST_TIME'], $last_et, false);  // log lap time
            // return status can be time|finish|first_finish|force_finish

            if ($status == "time" OR $status == "finish" OR $status == "first_finish")
            {
                $newlap = $lap + 1;

                $_SESSION["e_$eventid"]['result_valid']   = false;    // mark results as requiring update
                $_SESSION["e_$eventid"]['result_publish'] = false;    // mark publishing as requiring refresh

                $_SESSION["e_$eventid"]['lastclick'] = array("entryid" => $entryid, "clicktime" => $_SERVER['REQUEST_TIME'], "boat" => $boat);

                // FIXME  - shouldn't be necesary as racestate catchall will get this
                //update_racestate_lap($eventid, $fleet, $status, $newlap);  # update racestate and session

                if ($status == "time")
                {
                    $msg = "lap $newlap: $boat ";
                }
                elseif ($status == "finish")
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
    
    elseif ($pagestate == "finish")
    {
        $if_err = false;
        empty($_REQUEST['entryid']) ? $if_err = true : $entryid = $_REQUEST['entryid'];
        empty($_REQUEST['start'])   ? $if_err = true : $start = $_REQUEST['start'];
        empty($_REQUEST['boat'])    ? $if_err = true : $boat = $_REQUEST['boat'];
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

            $status = $race_o->entry_time($entryid, $fleet, $lap, $pn, $_SERVER['REQUEST_TIME'], $last_et, true);
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

                // FIXME - this shouldn't be necessary as it will be handled by catchall
                //update_racestate_lap($eventid, $fleet, $status, $newlap);           // update racestate and session

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
    
    elseif  ($pagestate == "undo")
    {
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

    elseif  ($pagestate == "undoboat")
    {
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

    elseif  ($pagestate == "shorten")
    {
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
       
    elseif  ($pagestate == "shortenall") {

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

    elseif ($pagestate == "setlaps")        // sets laps for all fleets
        // FIXME this code should be the same as race_sc.php/setlaps
    {
//        $msg = array(
//            "ok" => "&nbsp;&nbsp;- {} - set to {} lap(s) <br>",
//            "pursuit_race" => "&nbsp;&nbsp;- {} is a pursuit race - laps cannot be set <br>",
//            "less_than_current" => "&nbsp;&nbsp;- {} - laps not set, boats already on lap {} <br>",
//            "finishing" => "&nbsp;&nbsp;- {} - laps not set, boats already finishing <br>",
//            "already_set" => "&nbsp;&nbsp;- {} - no laps change requested <br>",
//            "unknown" => "&nbsp;&nbsp;- {} - laps set failed for reasons unknown <br>"
//        );
//        $growlmsg = "Setting Laps &hellip;<br>";
//
//        for ($i=1; $i<=$_SESSION["e_$eventid"]['rc_numfleets']; $i++)
//        {
//            $fleetname = $_SESSION["e_$eventid"]["fl_$i"]['name'];
//            if ($_SESSION["e_$eventid"]["fl_$i"]['maxlap'] != $_REQUEST["laps$i"])    // here has been a change
//            {
//                $rs = $race_o->race_laps_set($i, $_REQUEST["laps$i"]);
//                if ($rs)
//                {
//                    $growlmsg.= u_format($msg["{$rs['result']}"], array($fleetname, $_REQUEST["laps$i"]));
//
//                    if ($rs['result'] == "ok")
//                    {
//                        u_writelog("setlaps: $fleetname - {$_REQUEST["laps$i"]} laps", $eventid);
//                    }
//                }
//                else
//                {
//                    $growlmsg.= u_format($msg["unknown"], array($fleetname, $_REQUEST["laps$i"]));
//                    u_writelog("setlaps: $fleetname - failed [{$_REQUEST["laps$i"]}] laps", $eventid);
//                }
//            }
//        }
//        u_growlSet($eventid, $page, $g_timer_setlaps_report, array($growlmsg));

        $lapsetfail = false;
        $growlmsg   = "Setting laps:<br>";

        for ($i=1; $i<=$_SESSION["e_$eventid"]['rc_numfleets']; $i++)
        {
            $fleetname = $_SESSION["e_$eventid"]["fl_$i"]['name'];
            $rs = $race_o->race_laps_set($i, $_REQUEST["maxlaps$i"]);

            $str = array(
                "pursuit_race" => "$fleetname is a pursuit race - laps cannot be set",
                "less_than_current" => "$fleetname - laps not changed, boats already on lap {$rs['currentlap']}",
                "finishing" => "$fleetname - laps not changed, boats already finishing",
                "already_set" => "$fleetname - laps already set to {$rs['finishlap']}",
            );

            echo "<pre>$fleetname - ".print_r($rs,true)."</pre>";
            if (empty($rs['result']) or $rs['result'] == "failed")
            {
                u_writelog("setlaps: $fleetname - failed [{$_REQUEST["maxlaps$i"]} laps]", $eventid);
                $growlmsg.= "&nbsp;&nbsp;$fleetname - laps set FAILED <br>";
                $lapsetfail = true;
            }
            elseif($_REQUEST["maxlaps$i"] == $_SESSION["e_$eventid"]["fl_$i"]['maxlap'])  // no change requested
            {
                $growlmsg.= "&nbsp;&nbsp;$fleetname - no change <br>";
            }
            else
            {
                if ($rs['result'] == "ok")
                {
                    u_writelog("setlaps: $fleetname - {$_REQUEST["maxlaps$i"]} laps", $eventid);
                    $growlmsg.= "&nbsp;&nbsp;$fleetname - laps changed to {$rs['finishlap']} <br>";
                }
                else
                {
                    $growlmsg.= "&nbsp;&nbsp; - ".$str["{$rs['result']}"]."<br>";
                    $lapsetfail = true;
                }
            }
        }

        if ($lapsetfail)
        {
            u_growlSet($eventid, $page, $g_race_lapset_fail, array($growlmsg));
        }
        else
        {
            u_growlSet($eventid, $page, $g_race_lapset_success, array($growlmsg));
        }
    }

    elseif ($pagestate == "bunch")
    {
//        echo "<pre>".print_r($_SESSION["e_$eventid"]['bunch'],true)."</pre>";

        if ($_REQUEST['action'] == "addnode")
        {
            $params = array(
                "entryid" => $_REQUEST['entryid'],
                "boat"    => $_REQUEST['boat'],
                "fleet"   => $_REQUEST['fleet'],
                "start"   => $_REQUEST['start'],
                "lap"     => $_REQUEST['lap'],
                "pn"      => $_REQUEST['pn'],
                "etime"   => $_REQUEST['etime'],
            );
            //$link = urlencode("&".http_build_query($params));

            $_REQUEST['lastlap'] == "true" ? $lastlap = true : $lastlap = false;
            $node = array(
                "entryid" => $_REQUEST['entryid'],
                "lastlap" => $lastlap,
                "label"   => $_REQUEST['boat'],
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
    $race_o->racestate_updatestatus_all($_SESSION["e_{$this->eventid}"]['rc_numfleets'], $page);

    // return to timer page
    if (!$stop_here) { header("Location: timer_pg.php?eventid=$eventid"); exit(); }
}
else
{
    //FIXME - needs to be a way to get back
    u_exitnicely($scriptname, $eventid,"0","Close this window and try to restart the application.  
    If the problems continue please report the error to your system administrator");
}

// ------------- FUNCTIONS ---------------------------------------------------------------------------

function shorten_fleet($eventid, $fleetnum, $new_finish_lap = 0)
{
    global $race_o;
    $fleetname = $_SESSION["e_$eventid"]["fl_$fleetnum"]['name'];

    $str = array(
        "ok" => "$fleetname - shortened to lap {}",
        "pursuit_race" => "$fleetname is a pursuit race - cannot be shortened",
        "less_than_current" => "$fleetname - NOT shortened, boats already on lap {}",
        "finishing" => "$fleetname - NOT shortened, boats already finishing ",
        "already_set" => "$fleetname - NOT shortened, boats already on lap {} ",
        "unknown" => "$fleetname - NOT shortened for reasons unknown ",
        "no_laps_set" => "$fleetname - NOT shortened, no laps set for this fleet"
    );

    if ($_SESSION["e_$eventid"]["fl_$fleetnum"]['maxlap'] != 0)
    {
        if (empty($new_finish_lap))      // set finish lap if not specified by user
        {
            $new_finish_lap = $_SESSION["e_$eventid"]["fl_$fleetnum"]['currentlap'] + 1;
        }
        $rs = $race_o->race_laps_set($fleetnum, $new_finish_lap);

        $msg['text'] = u_format("&nbsp;&nbsp;- ".$str["{$rs['result']}"]."<br>", array($rs['finishlap']));
        $rs['result'] == "ok" ? $msg['type'] = "info" : $msg['type'] = "invalid";
    }
    else
    {
        $rs['result'] = $str["no_laps_set"];
        $msg['text'] = "&nbsp;&nbsp;{$rs['result']}<br>";
        $msg['type'] = "invalid";

    }
    u_writelog($str["{$rs['result']}"], $eventid);

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

function check_race_started($eventid, $fleetnum, $server_time)
// check that this fleet has started
{
    global $page;
    global $g_timer_racenotstarted;

    if ($server_time < $_SESSION["e_$eventid"]["fl_$fleetnum"]['starttime'])
    {
        u_writedbg("setting growl", __FILE__, __FUNCTION__, __LINE__);
        u_growlSet($eventid, $page, $g_timer_racenotstarted, array());
        header("Location: timer_pg.php?eventid=$eventid");
        exit(); 
    }
}

