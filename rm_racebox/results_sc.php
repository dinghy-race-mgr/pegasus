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
$entryid   = u_checkarg("entryid", "set", "", "");;

u_initpagestart($_REQUEST['eventid'], $page, false);   // starts session and sets error reporting

// classes
require_once ("{$loc}/common/classes/db_class.php");
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
    
    if ($pagestate == "retirements" OR $pagestate == "declarations")
    {
        $entry_o = new ENTRY($db_o, $eventid);
        $entries = $entry_o->get_signons($pagestate);    // gets retirements or declarations/retirements

        if ($entries)
        {
            // loop over entries 
            $counts = array("protests" => 0, "retires" => 0, "declares" => 0);
            $rpt_bufr = "";
            $error = false;

            foreach ($entries as $entry)
            {
                $protest = false;
                if ($entry['protest']==1 AND $_SESSION['sailor_protest'])
                { 
                    $protest = true;
                    $counts['protests']++;
                    $rpt_bufr.= " - {$result['class']} {$result['sailnum']} : protesting</br>";
                }
                
                if ($entry['action'] == "retire")
                {                    
                    $result = $race_o->entry_declare($entry['id'], "retire", $protest);
                    if ($result)
                    {
                        $counts['retires']++;
                        $entryupdate = $entry_o->confirm_entry($entry['t_entry_id'], "L");  // update entry record
                    }
                    else
                    {
                        $entryupdate = $entry_o->confirm_entry($entry['t_entry_id'], "F");                 // update entry record
                        $rpt_bufr.= " - {$result['class']} {$result['sailnum']} : retirement failed</br>";
                        $error = true;
                    }
                }
                /*
                elseif ($entry['action'] == "declare")
                {
                    $result = $race_o->entry_declare($entry['id'], "declare", $protest);
                    if ($result)
                    {
                        $counts['declares']++;
                        $entryupdate = $entry_o->confirm_entry($entry['t_entry_id'], "L");  // update entry record
                    }
                    else
                    {
                        $entryupdate = $entry_o->confirm_entry($entry['t_entry_id'], "F");                 // update entry record
                        $rpt_bufr.= " - {$result['class']} {$result['sailnum']} : declaration failed</br>";
                        $error = true;
                    }
                }
                */
            }
            combinegrowls($eventid, $page, $pagestate, $counts, rtrim($rpt_bufr, "</br>"), $error);    // present summary as growl
        }
        else
        {
            u_growlSet($eventid, $page, $g_results_zero_declare);
        }
        if ($stop_here) { header("Location: results_pg.php?eventid=$eventid");  exit(); }  // back to results page
        
    }

    elseif ($pagestate == "setcode")
    {
        $err = false;
        empty($_REQUEST['entryid'])    ? $err = true : $entryid = $_REQUEST['entryid'];
        empty($_REQUEST['boat'])       ? $err = true : $boat = $_REQUEST['boat'];
        empty($_REQUEST['racestatus']) ? $err = true : $racestatus = $_REQUEST['racestatus'];
        empty($_REQUEST['code'])       ? $code = ""  : $code = $_REQUEST['code'];

        if ($err)
        {
            $reason = "required parameters were invalid
                       (id: {$_REQUEST['entryid']}; boat: {$_REQUEST['boat']}; status: {$_REQUEST['racestatus']};)";
            u_writelog("$boat - set code failed - $reason", $eventid);
            u_growlSet($eventid, $page, $g_timer_setcodefailed, array($boat, $reason));
        }
        else
        {
            $update = set_code($eventid, $entryid, $code, $racestatus, $boat);

            if (!$update)
            {
                $reason = "database update failed";
                u_writelog("$boat - attempt to set code to $code] FAILED" - $reason, $eventid);
                u_growlSet($eventid, $page, $g_timer_setcodefailed, array($boat, $reason));
            }
        }
    }
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

    
    elseif ($pagestate == "changefinish")
    {        
        echo "<pre>entering changefinish</pre>";
        echo "<pre>".print_r($_REQUEST,true)."</pre>";
        // get current racestate
        $racestate = $race_o->racestate_get();
        
        // for each fleet that has changed
        foreach ($racestate as $key=>$fleet)
        {
            $fleetnum = $fleet['race'];
            $finishlap = $_REQUEST['finishlap'][$fleetnum];
            echo "<pre>finish lap: $finishlap  [{$fleet['maxlap']}]</pre>";
            if ( $fleet['maxlap'] != $finishlap )     // laps have been changed for this fleet
            {
                // update t_racestate, t_race tables and session maxlap for this fleet
                $update = $race_o->race_laps_set($fleetnum, $finishlap, $mode="switch");

                echo "<pre>UPDATE: ".print_r($update,true)."</pre>";

                // in t_race if laps is > maxlap - reset and map correct etime from t_lap
                $switch = $race_o->race_switch_lap($fleetnum, $finishlap);

                echo "<pre>SWITCH: $switch</pre>";
            }
        }

        // reset rescore flag
        $_SESSION["e_$eventid"]['result_valid'] = false;
    }


    elseif ($pagestate == "message")   // send message
    {
        $event_o = new EVENT($db_o);

        // set fields to enter
        $fields = array();
        $fields["name"]    = $_REQUEST['msgname'];
        $fields["subject"] = $_SESSION["e_$eventid"]['ev_fname']." - OOD message";
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
        // pagestate value ont recognised
        u_exitnicely($scriptname, $eventid,"program control option not recognised [pagestate: $pagestate]",
            "please contact your raceManager administrator");
    }
    
    if (!$stop_here) { header("Location: results_pg.php?eventid=$eventid"); exit(); }  // back to results page
    exit();
       
}
else
{
    // input parameter missing
    u_exitnicely($scriptname, $eventid,"program parameters eventid and/or pagestate not recognised [eventid: $eventid, pagestate: $pagestate]",
        "please contact your raceManager administrator");
}

// ------------- FUNCTIONS ---------------------------------------------------------------------------
function combinegrowls($eventid, $page, $pagestate, $counts, $rpt_bufr, $error)
{    
    if ($counts['protests'] > 0) { $protests = "&nbsp;[ {$counts['protests']} protest".u_plural($counts['protests'])." ]"; }


    if ($pagestate == "retirements")
    {
        $title = "<p>Processing: {$counts['retires']} retirements $protests</p>";
    }
    elseif ($pagestate == "declarations")
    {
        $title = "<p>Processing: {$counts['declares']} declarations, {$counts['retires']} retirements $protests</p>";
    }

    if ($error)
    {
        $rpt_bufr = "<p>$rpt_bufr</p><p>Please use the edit function to manually apply failed declarations / retirements</p>";
        $gclose = 50000;
        $gstyle = "danger";
    }
    else
    {
        $gclose = 5000;
        $gstyle = "success";
    }

    $g_content = array(
        "type" => $gstyle,
        "delay"=> $gclose,
        "msg"  => "$title $rpt_bufr",
    );
    
    u_growlSet($eventid, $page, $g_content, array());
}
