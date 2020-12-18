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

// growl responses
include("./templates/growls.php");


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
                        $entryupdate = $entry_o->confirm_entry($entry['t_entry_id'], 0, "L");  // update entry record
                    }
                    else
                    {
                        $entryupdate = $entry_o->confirm_entry($entry['t_entry_id'], "", "F");                 // update entry record
                        $rpt_bufr.= " - {$result['class']} {$result['sailnum']} : retirement failed</br>";
                        $error = true;
                    }
                }
                elseif ($entry['action'] == "declare")
                {
                    $result = $race_o->entry_declare($entry['id'], "declare", $protest);
                    if ($result)
                    {
                        $counts['declares']++;
                        $entryupdate = $entry_o->confirm_entry($entry['t_entry_id'], 0, "L");  // update entry record
                    }
                    else
                    {
                        $entryupdate = $entry_o->confirm_entry($entry['t_entry_id'], "", "F");                 // update entry record
                        $rpt_bufr.= " - {$result['class']} {$result['sailnum']} : declaration failed</br>";
                        $error = true;
                    }
                }
            }
            combinegrowls($eventid, $page, $pagestate, $counts, rtrim($rpt_bufr, "</br>"), $error);    // present summary as growl
        }
        else
        {
            u_growlSet($eventid, $page, $g_results_zero_declare);
        }
        if ($stop_here) { header("Location: results_pg.php?eventid=$eventid");  exit(); }  // back to results page
        
    }

    
    elseif ($pagestate == "editresult")                // change results
    {
        $constraint = array();
        if (!empty($_REQUEST['helm']))    { $constraint['helm']    = $_REQUEST['helm']; }
        if (!empty($_REQUEST['crew']))    { $constraint['crew']    = $_REQUEST['crew']; }
        if (!empty($_REQUEST['sailnum'])) { $constraint['sailnum'] = $_REQUEST['sailnum']; }
        if (!empty($_REQUEST['pn']))      { $constraint['pn']      = $_REQUEST['pn']; }
        if (!empty($_REQUEST['lap']))     { $constraint['lap']     = $_REQUEST['lap']; }
        if (!empty($_REQUEST['etime']))   { $constraint['etime']   = strtotime($_REQUEST['etime']); }
        if (!empty($_REQUEST['code']))    { $constraint['code']    = $_REQUEST['code']; }
        if (!empty($_REQUEST['penalty'])) { $constraint['penalty'] = $_REQUEST['penalty']; }
        if (!empty($_REQUEST['note']))    { $constraint['note']    = $_REQUEST['note']; }
        
        // update race results
        $update = $race_o->entry_update($entryid, $constraint);
        
        // retrieve updated result record
        $result = $race_o->entry_get($entryid, "race");
        $boat = "{$result['class']} {$result['sailnum']}";
        
        // update last lap time
        // FIXME - this is not correct if the elapsed time has changed (need to recalc clicktime, etime, ctime)
        // FIXME - needs to be same as submit section of timer_editlaptimes_pg
        $updatelap = $race_o->entry_lap_update($entryid, $result['fleet'], $result['lap'], $result['pn'],
            array("clicktime"=>$result['clicktime'], "etime"=>$result['etime'], "ctime"=>$result['ctime'], "status"=>1));
        
        // update results status
        $_SESSION["e_$eventid"]['result_valid'] = false;

        u_writelog("$boat: updated result", $eventid);
        u_growlSet($eventid, $page, $g_result_edit_success, array($boat));
    }
    
    
    elseif ($pagestate == "delete")                // remove boat from race
    {
        $result = $race_o->entry_get($entryid, "race");
        $boat = "{$result['class']} {$result['sailnum']}";
        $delete = $race_o->entry_delete($entryid);
        if ($delete)
        {
            u_writelog("$boat: deleted from race ", $eventid);
            u_growlSet($eventid, $page, $g_result_del_success, array($boat));
        }
        else         
        {
            u_writelog("$boat: attempt to delete entry failed ", $eventid);
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
