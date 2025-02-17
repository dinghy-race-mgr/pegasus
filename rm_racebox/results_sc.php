<?php
/**
 * results_sc.php
 * 
 * code for results page functions.
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
$loc        = "..";          // <--- relative path from script to top level folder
$page       = "results";     
$debug      = false;
$stop_here  = false;
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_racebox_lib.php");

// start session
u_startsession("sess-rmracebox", 10800);

// arguments
$eventid   = u_checkarg("eventid", "checkintnotzero","", false);      // eventid (required)
$pagestate = u_checkarg("pagestate", "set", "", false);               // pagestate (required)
$entryid   = u_checkarg("entryid", "set", "", "");                    // entryid

if (!$eventid)
{
    u_exitnicely($scriptname, 0,"$page page - event id record [{$_REQUEST['eventid']}] not defined",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// page initialisation
u_initpagestart($eventid, $page, false);

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/race_class.php");

// page controls
include("./templates/growls.php");

if ($eventid AND $pagestate)
{
    $db_o    = new DB;
    $race_o  = new RACE($db_o, $eventid);

// -----------------------------------------------------------------------------------
//   load retirements
// -----------------------------------------------------------------------------------
    if ($pagestate == "retirements")
    {
        $entry_o = new ENTRY($db_o, $eventid);
        $declares = $entry_o->get_signons($pagestate);    // gets declarations ( signoffs/retirements)

        if ($declares)
        {
            // loop over entries 
            $counts = array("protests" => 0, "retires" => 0, "declares" => 0);
            $rpt_bufr = "";

            foreach ($declares as $declare)
            {
                $error = false;
                $entry = $race_o->entry_get($declare['id'], "competitor");
                $entryid = $entry['id'];
                $entry_txt = "{$entry['class']} - {$entry['sailnum']}";

                $update = array();
                // process protests
                $protest = false;
                if ($declare['protest'] AND $_SESSION['sailor_protest'])
                {
                    $counts['protests']++;
                    $update['protest'] = "1";
                }

                if ($declare['action'] == "retire")
                {
                    $action_txt = "retirement";
                    $update["declaration"] = "R";
                    $counts['retires']++;
                    $code_upd = $race_o->entry_code_set($entryid, "RET");   // sets retirement code
                    if ($code_upd < 0) { $error = true; }
                }
                elseif ($declare['action'] == "declare")
                {
                    $action_txt = "declaration";
                    $update["declaration"] = "D";
                    $counts['declares']++;
                }

                // updates declaration and protest fields
                $entry_upd = $race_o->entry_update($entryid, $update);
                if ($entry_upd == -1) { $error = true; }

                // confirm entry record processed
                if (!$error)
                {
                    $entryupdate = $entry_o->confirm_entry($declare['t_entry_id'], "L");
                    u_writelog("RESULTS (load retirements) $entry_txt: marked as retired ", $eventid);
                }
                else
                {
                    $entryupdate = $entry_o->confirm_entry($declare['t_entry_id'], "F");
                    $rpt_bufr.= " - $entry_txt : $action_txt failed</br>";
                    u_writelog("RESULTS (load retirements) $entry_txt: retirement processing failed", $eventid);
                }
            }
            creategrowl($eventid, $page, $pagestate, $counts, rtrim($rpt_bufr, "</br>"));    // present summary as growl
        }
        else
        {
            u_growlSet($eventid, $page, $g_results_zero_declare);
        }
    }

// -----------------------------------------------------------------------------------
//   set scoring code
// -----------------------------------------------------------------------------------
    elseif ($pagestate == "setcode")
    {
        if (!empty($_REQUEST['fleet']))
        {
            $setcode = set_code($eventid, $_REQUEST);

            if ($setcode !== true) { u_growlSet($eventid, $page, $g_timer_setcodefailed, array($_REQUEST['boat'], $setcode)); }
        }
        else
        {
            error_log("results_sc.php/pagestate=setcode : fleet argument not set", 3, $_SESSION['syslog']);
        }

    }

// -----------------------------------------------------------------------------------
//   delete boat
// -----------------------------------------------------------------------------------
    elseif ($pagestate == "delete")                // remove boat from race - mark status as 'D'
    {
        $result = $race_o->entry_get($entryid, "race");
        $boat = "{$result['class']} {$result['sailnum']}";
        $delete = $race_o->entry_update($entryid, array("status" => "D"));

        if ($delete)
        {
            u_writelog("$boat: removed from race ", $eventid);
            u_growlSet($eventid, $page, $g_result_del_success, array($boat));
        }
        else         
        {
            u_writelog("$boat: attempt to remove entry failed ", $eventid);
            u_growlSet($eventid, $page, $g_result_del_fail, array($boat));
        }
    }

// -----------------------------------------------------------------------------------
//   change finish lap
// -----------------------------------------------------------------------------------
    elseif ($pagestate == "changefinish")
    {
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
                    $_SESSION["e_$eventid"]["fl_$i"]['maxlap'] = $new_maxlap;
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

// -----------------------------------------------------------------------------------
//   send message
// -----------------------------------------------------------------------------------
    elseif ($pagestate == "message")   // send message
    {
        $event_o = new EVENT($db_o);

        // set fields to enter
        $fields = array();
        $fields["name"]    = $_REQUEST['msgname'];
        $fields["subject"] = $_SESSION["e_$eventid"]['ev_dname']." - OOD message";
        $fields["message"] = $_REQUEST['message'];
        !empty($_REQUEST['email']) ? $fields["email"] = $_REQUEST['email'] : $fields["email"] = "racebox";
        $fields["status"]  = "received";
        
        // add message to message table
        $add = $event_o->event_addmessage($eventid, $fields);

        if ($add) // report success
        {            
            // setup success growl
            u_writelog("message sent", $eventid);
            u_growlSet($eventid, $page, $g_race_msg_success);
        }
        else      // report fail
        {
            u_writelog("ERROR - attempt to send message failed", $eventid);
            u_growlSet($eventid, $page, $g_race_msg_fail);
        }
    }

// -----------------------------------------------------------------------------------
//   close race
// -----------------------------------------------------------------------------------
//
    elseif ($pagestate == "close")
    {
        if (!empty($_REQUEST['message']))       // send message if necessary
        {
            // set fields to enter
            $fields = array(
                "name"    => "OOD",
                "subject" => $_SESSION["e_$eventid"]['ev_dname']." - OOD closing message",
                "message" => $_REQUEST['message'],
                "email"   => "",
                "status"  => "received",
            );
            $add = $event_o->event_addmessage($eventid, $fields);
        }

        $result = $event_o->event_close($eventid);
        if ($result)
        {
            $_SESSION["e_$eventid"]['exit'] = true;
            u_writelog("race COMPLETED", $eventid);
            u_growlSet(0, $page, $g_race_close_success);

            // return to dashboard
            header("Location: pickrace_pg.php?");
            exit();
        }
        else
        {
            u_writelog("ERROR - attempt to close race failed ", $eventid);
            u_growlSet($eventid, $page, $g_race_close_fail);
        }
    }

// -----------------------------------------------------------------------------------
//   pagestate not recognised
// -----------------------------------------------------------------------------------
    else
    {
        u_exitnicely($scriptname, $eventid,"$page page - pagestate value not recognised [{$_REQUEST['pagestate']}]",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }

// -----------------------------------------------------------------------------------
//   update racestate and return to results page
// -----------------------------------------------------------------------------------
    // check status / update session
    $race_o->racestate_updatestatus_all($_SESSION["e_$eventid"]['rc_numfleets'], $page);
    if (!$stop_here) { header("Location: results_pg.php?eventid=$eventid"); exit(); }
       
}
else
{
    u_exitnicely($scriptname, $eventid,"$page page - event id record [{$_REQUEST['eventid']}] or pagestate value not recognised [{$_REQUEST['pagestate']}] not defined",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// -----------------------------------------------------------------------------------
//   functions
// -----------------------------------------------------------------------------------
function creategrowl($eventid, $page, $pagestate, $counts, $rpt_bufr)
{
    $protest_txt = "";
    if ($counts['protests'] > 0) { $protest_txt = "&nbsp;&nbsp;[ {$counts['protests']} protest(s) ]"; }

    $title = "";
    if ($pagestate == "retirements")
    {
        $title.= "<p>Processed: {$counts['retires']} retirement(s) $protest_txt</p>";
    }
    elseif ($pagestate == "declarations")
    {
        $title.= "<p>Processed: {$counts['declares']} declaration(s), {$counts['retires']} retirement(s) $protest_txt</p>";
    }

    $gclose = 10000;
    $gstyle = "success";
    if (!empty($rpt_bufr))
    {
        $rpt_bufr = "<p>$rpt_bufr</p><p>Please use the inline edit button to manually apply failed declarations / retirements</p>";
        $gclose = 0;
        $gstyle = "danger";
    }
    u_growlSet($eventid, $page, array("type" => $gstyle, "delay"=> $gclose, "msg" => "$title $rpt_bufr"), array());

    return ;
}


function fleet_changefinishlap($fleetnum, $fleet, $new_maxlap, $current_lap)
{
    global  $race_o;
    $status = true;

    $race = $race_o->race_getresults($fleetnum);  // get race data for this fleet
    //u_writedbg("<pre>BEFORE: ".print_r($race,true)."</pre>", __FILE__, __FUNCTION__, __LINE__);

    foreach($race as $boat)
    {
        // set data for change in finish lap
        if ($fleet['racetype'] == "average")    // average lap race: set each boat to an individual finish lap
        {
            if ($new_maxlap < $fleet['maxlap'])  // reducing number of laps
            {
            /*
             *  All boats that have completed a lap get a finish based on the new finish lap specified
             *  or the last lap that they completed.  Boats that haven't finished a lap will not be finished - OOD
             *  needs to do that with a DNF.  If specified finish lap is > than the current leaders lap then it
             *  will still finish everyone BUT will aggregate the ET to the specified finish lap
             */

                if ($boat['lap'] >= 1)              // boat has completed at least one lap - can be scored
                {
                    $boat['status'] == "X" ? $set_status = "X" : $set_status = "F" ;   // set status to finished unless it is excluded

                    $update = get_newlap_data($boat['id'], $new_maxlap);
                    $update['status'] = $set_status;
                    $update['finishlap'] = $new_maxlap;
                }
                else                                // boat hasn't completed a lap - can't be scored - just reset finish lap
                {
                    $update = array("finishlap" => $new_maxlap);
                }
            }
            elseif ($new_maxlap > $fleet['maxlap'])    // increasing number of laps
            {
                /*
                 * reset finish lap and set status on whether the boat has finished the new finish lap
                 * change finish status as required
                 */

                if ($boat['lap'] >= 1)              // boat has completed at least one lap - can be scored
                {
                    $update = get_newlap_data($boat['id'], $new_maxlap);
                    $update['finishlap'] = $new_maxlap;

                    if ($update['lap'] < $new_maxlap) {
                        if ($boat['status'] == "F") {
                            $update['status'] = "R";
                        }
                    }
                }
                else                                // boat hasn't completed a lap - can't be scored - just reset finish lap
                {
                    $update = array("finishlap" => $new_maxlap);
                }
            }
        }
        else                                        // handicap or fleet race: set all boats to same finish lap
        {
            /*
             *  Finishes all boats that have completed the specified finish lap (new_maxlap).  Boats who haven't
             *  completed the specified lap will need to be marked DNF by the OOD if not already done.
             */
            if ($boat['lap'] >= 1)
            {
                if ($new_maxlap > $current_lap)         // boat will not have finished
                {
                    $boat['status'] == "F" ? $set_status = "R" : $set_status = $boat['status'];
                    $update = array("status" => $set_status, "finishlap" => $new_maxlap);
                }
                else
                {
                    $boat['status'] == "X" ? $set_status = "X" : $set_status = "R";
                    $update = get_newlap_data($boat['id'], $new_maxlap);
                    $update['finishlap'] = $new_maxlap;
                    $update['status'] = $set_status;
                }
            }
            else
            {
                $update = array("finishlap" => $new_maxlap);
            }
        }
        // update boat details
        //u_writedbg("<pre>{$boat['class']} - {$boat['sailnum']}: ".print_r($update,true)."</pre>", __FILE__, __FUNCTION__, __LINE__);
        $race_o->entry_update($boat['id'], $update);
    }

    //$race = $race_o->race_getresults($fleetnum);  // get race data for this fleet
    //u_writedbg("<pre>AFTER: ".print_r($race,true)."</pre>", __FILE__, __FUNCTION__, __LINE__);

    return $status;
}

function get_newlap_data($entryid, $new_maxlap)
/*
 * when finish lap is changed this gets the relevant time details from t_lap to update t_race with the correct info.
 * if the boat hasn't completed the finish lap it will return tha last lap recorded
 */
{
    global $race_o;


    $finlapdata = $race_o->entry_lap_get($entryid, "lap", $new_maxlap);   // get lap data for new finish lap

    if ($finlapdata)                                                      // if boat has lap data - use that
    {
        $lapdata = $finlapdata;
        $lastlap = $new_maxlap;
    }
    else                                                                  // else get last lap completed
    {
        $lapdata = $race_o->entry_lap_get($entryid, "last");
        $lastlap = $lapdata['lap'];
    }

    $data = array(
        "lap" => $lapdata['lap'],
        "etime" => $lapdata['etime'],
        "ctime" => $lapdata['ctime'],
        "ptime" => $race_o->entry_calc_pt($lapdata['etime'], 0, $lapdata['lap']),
        "clicktime" => $lapdata['clicktime']
    );

    //u_writedbg("NEWLAP DATA: ".print_r($data, true) ,__FILE__, __FUNCTION__, __LINE__);

    return $data;
}
