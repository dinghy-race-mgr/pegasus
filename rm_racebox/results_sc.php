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

// parameters  [eventid (required), pagestate(required), entryid)
$eventid   = u_checkarg("eventid", "checkintnotzero","", false);
$pagestate = u_checkarg("pagestate", "set", "", false);
$entryid   = u_checkarg("entryid", "set", "", "");

u_initpagestart($_REQUEST['eventid'], $page, false);   // starts session and sets error reporting

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/race_class.php");

// app includes
require_once ("./include/rm_racebox_lib.php");

// growl responses
include("./templates/growls.php");

//echo "<pre>input arguments: ".print_r($_REQUEST,true)."</pre>";
//exit();


if ($eventid AND $pagestate)
{
    $db_o    = new DB;
    $race_o  = new RACE($db_o, $eventid);
    
    if ($pagestate == "retirements" /*OR $pagestate == "declarations"*/)  // FIXME - currently not planning to use declarations
    {
        $entry_o = new ENTRY($db_o, $eventid);
        $declares = $entry_o->get_signons($pagestate);    // gets declarations ( signoffs/retirements)

        if ($declares)
        {
            // loop over entries 
            $counts = array("protests" => 0, "retires" => 0, "declares" => 0);
            $rpt_bufr = "";
            $error = false;

            foreach ($declares as $declare)
            {

                $error = false;
                // need to get $entryid - as not provided by $_REQUEST for this function
                $entry = $race_o->entry_get($declare['id'], "competitor");
                $entryid = $entry['id'];
                $entry_txt = "{$entry['class']} {$entry['sailnum']}";

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
                    if ($code_upd != "code_set") { $error = true; }
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
                    echo "<pre>marking entry record as processed</pre>";
                }
                else
                {
                    $entryupdate = $entry_o->confirm_entry($declare['t_entry_id'], "F");
                    $rpt_bufr.= " - $entry_txt : $action_txt failed</br>";
                    echo "<pre>marking entry record as failed</pre>";
                }
            }
            creategrowl($eventid, $page, $pagestate, $counts, rtrim($rpt_bufr, "</br>"));    // present summary as growl
        }
        else
        {
            u_growlSet($eventid, $page, $g_results_zero_declare);
        }
        
    }

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
// code for editing lap times below
/*
    elseif ($pagestate == "editresult")                // change result (handled through iframe
    {
        // get existing record and change lap times to array
        $old = $race_o->entry_get_timings($entryid);
        $laptimes = $race_o->lapstr_toarray($old['laptimes']);

        // convert returned field values
        $edit_str = "";
        $edit = array();
        if (!empty($_REQUEST['helm']))    { $edit['helm'] = $_REQUEST['helm']; }
        $edit['crew']    = $_REQUEST['crew'];
        $edit['club']    = u_getclubname($_REQUEST['club']);
        $edit['sailnum'] = $_REQUEST['sailnum'];
        if (ctype_digit($_REQUEST['pn']) ) { $edit['pn'] = (int)$_REQUEST['pn']; }
        if (ctype_digit($_REQUEST['lap']) ) { $edit['lap'] = (int)$_REQUEST['lap']; }
        $edit['etime']   = u_conv_timetosecs($_REQUEST['etime']);
        $edit['code']    = $_REQUEST['code'];
        if (ctype_digit($_REQUEST['penalty']) ) { $edit['penalty'] = (int)$_REQUEST['penalty']; }
        $edit['note']    = $_REQUEST['note'];

        // check which fields have changed - remove unchanged fields and create audit string for log
        foreach ($edit as $k => $v)
        {
            if ($old[$k] === $edit[$k]) {
                unset($edit[$k]);
            } else {
                $edit_str .= "$k:$v ";
            }
        }

        // update race result in t_race
        $update = $race_o->entry_update($entryid, $edit);

        // delete and add finish lap time to t_lap
        $del = $race_o->entry_lap_delete($entryid, $edit['lap']);
        $ctime = $race_o->entry_calc_ct($edit['etime'], $edit['pn'], $_SESSION["e_$eventid"]["fl_{$old['fleet']}"]['scoring']);
        $clicktime = strtotime("{$edit['etime']} seconds");
        $add_lap = $race_o->entry_lap_add($old['fleet'], $entryid, array("lap" => $edit['lap'], "clicktime" => $clicktime,
            "etime" => $edit['etime'], "ctime" => $ctime));

        // check for missing laps in t_lap - and add placeholders if necessary

        // update t_race with time info to match last lap (use $lap-1 as arg + assumes no force finish and calcs ptime as average)
        $add_time = $race_o->entry_time($entryid, $old['fleet'], $edit['lap']-1, $edit['pn'], $clicktime );
        
        // update results status
        $_SESSION["e_$eventid"]['result_valid'] = false;

        // log change
        u_writelog("Result Update - {$old['class']} {$old['sailnum']} : edit_str", $eventid);
    }
*/

/* ------- DELETE ------------------------------------------------------------------------------- */
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


/* ------- CHANGEFINISH ------------------------------------------------------------------------------- */
    elseif ($pagestate == "changefinish")
    {
        $growl_txt = "";
        $racestate = $race_o->racestate_get();   // get racestate for all fleets

        // loop over fleets
        foreach ($racestate as $i=>$fleet) {
            $log_text = "";
            $fleetnum = $fleet['fleet'];
            $new_maxlap = $_REQUEST["finlap$i"];

            if ($fleet['maxlap'] != $new_maxlap)  // laps have been changed
            {
                $log_txt = "{$_SESSION["e_$eventid"]["fl_$i"]['fleetname']} finish changed to lap $new_maxlap ";

                $race = $race_o->race_getresults($i);  // get race data for this fleet

                // loop over boats in fleet
                $leaderlap = 0;
                foreach ($race as $j => $entry)
                {
                    if ($fleet['racetype'] == "average")     // will always finish if at least one lap is completed
                    {
                        if ($entry['lap'] >= 1)
                        {
                            // find last completed lap that is <= finishlap
                            $entry['lap'] > $new_maxlap ? $new_lap = $new_maxlap : $new_lap = $entry['lap'];

                            $etime = $race_o->race_getlap_etime($entry, $new_lap); // get etime for finish lap
                            $entry['status'] == "X" ? $set_status = "X" : $set_status = "F" ;
                            $update = array("status" => "F", "finishlap" => $new_maxlap, "lap" => $new_lap, "etime" => $etime);
                        }
                        else
                        {
                            $update = array("finishlap" => $new_maxlap);
                        }
                        $race_o->entry_update($entry['id'], $update);      // update t_race
                    }
                    else                                     // will only finish if new finish lap <= current lap
                    {
                        if ($entry['lap'] >= 1)
                        {
                            if ($new_maxlap <= $entry['lap'])    // finished
                            {
                                $etime = $race_o->race_getlap_etime($entry, $new_maxlap); // get etime for finish lap
                                $entry['status'] == "X" ? $set_status = "X" : $set_status = "F" ;
                                $update = array("status" => $set_status, "finishlap" => $new_maxlap, "lap" => $new_maxlap, "etime" => $etime);
                            }
                            else // still racing
                            {
                                $entry['status'] == "X" ? $set_status = "X" : $set_status = "R" ;
                                $update = array("status" => $set_status, "finishlap" => $new_maxlap);
                            }
                        }
                        else
                        {
                            $update = array("finishlap" => $new_maxlap);
                        }
                    }
                    // update t_race
                    $race_o->entry_update($entry['id'], $update);

                    // find leader lap
                    if ($entry['lap'] > $leaderlap) { $leaderlap = $entry['lap']; }
                }

                $status = $race_o->racestate_analyse($fleetnum, $racestate['starttime'], $racestate['racetype']);
// FIXME shouldn't be required now
//
//                // if status changed update
//                if ($status != $racestate['status']) {
//                    $update = array(
//                        "status" => $status,
//                        "prevstatus" => $racestate['status'],
//                        "maxlap" => $new_maxlap,
//                        "currentlap" => $leaderlap
//                    );
//                    $upd = $race_o->racestate_update($update, array("fleet" => $i));
//                    $_SESSION["e_$eventid"]["fl_$i"]['status'] = $status;
//                }
//
//                $status == "allfinished" ? $log_txt.= " - all finished": $log_txt.= " - not all finished";
//
//                u_writelog($log_txt, $eventid);  // log change
//                $growl_txt.= $log_txt."<br>";
            }
        }

        // set growl
        u_growlSet($eventid, $page, $g_results_changefinish, array($growl_txt));

        // reset rescore flag
        $_SESSION["e_$eventid"]['result_valid'] = false;
    }


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


    else
    {
        u_exitnicely($scriptname, $eventid,"$page page - pagestate value not recognised [{$_REQUEST['pagestate']}]",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }

    // check status / update session
    $race_o->racestate_updatestatus_all($_SESSION["e_$eventid"]['rc_numfleets'], $page);
    
    if (!$stop_here) { header("Location: results_pg.php?eventid=$eventid"); exit(); }  // back to results page
       
}
else
{
    u_exitnicely($scriptname, $eventid,"$page page - event id record [{$_REQUEST['eventid']}] or pagestate value not recognised [{$_REQUEST['pagestate']}] not defined",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// ------------- FUNCTIONS ---------------------------------------------------------------------------
function creategrowl($eventid, $page, $pagestate, $counts, $rpt_bufr)
{    
    echo "<pre>counts: ".print_r($counts,true)."</pre>";

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
        $gclose = 30000;
        $gstyle = "warning";
    }
    u_growlSet($eventid, $page, array("type" => $gstyle, "delay"=> $gclose, "msg" => "$title $rpt_bufr"), array());

    return ;
}
