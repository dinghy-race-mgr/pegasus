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

u_initpagestart($_REQUEST['eventid'], $page, $_REQUEST['menu']);   // starts session and sets error reporting

// initialising language   
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");

require_once ("{$loc}/common/classes/db_class.php"); 
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/race_class.php");

// process parameters  (eventid, pagestate, entryid)
$eventid   = (!empty($_REQUEST['eventid']))? $_REQUEST['eventid']: "";
$pagestate = (!empty($_REQUEST['pagestate']))? $_REQUEST['pagestate']: "";
$entryid   = (!empty($_REQUEST['entryid']))? $_REQUEST['entryid']: "";

if ($eventid AND $pagestate)
{
    $db_o    = new DB;
    $race_o  = new RACE($db_o, $eventid);
    
    if ($pagestate == "retirements" OR $pagestate == "declarations")
    {
        // FIXME needs to handle protests

        $entry_o = new ENTRY($db_o, $eventid);
        $entries = $entry_o->get_signons($pagestate);    // gets retirements or declarations/retirements
        
        if ($entries)
        {
            // loop over entries 
            $fbufr = ""; 
            $retnum = 0;
            $decnum = 0;
            $failnum = 0;
            foreach ($entries as $entry)
            {
                $protest = false;   // FIXME this needs to be handled at some point
                if ($entry['protest']==1) 
                { 
                    $protest = true; 
                    $fbufr.= "<p>{$result['class']} {$result['sailnum']} : protesting</p>";
                }
                
                if ($entry['action'] == "retire")
                {                    
                    $result = $race_o->entry_declare($entry['competitorid'], "retire", $protest);
                    if ($result == "retired")
                    {                                               
                        $retnum++;
                        $entryupdate = $entry_o->confirm($entry['id'], 0, "L");  // update entry record
                    }
                    else
                    {                         
                        $failnum++;
                        $entryupdate = $entry_o->confirm($entry['id'], "", "F");                 // update entry record
                        $fbufr.= "<p>{$result['class']} {$result['sailnum']} : {$result['status']}</p>";                        
                    }
                }
                elseif ($entry['action'] == "declare")
                {
                    $result = $race_o->entry_declare($entry['competitorid'], "declare", $protest);
                    if ($result == "declared")
                    {                                               
                        $decnum++;
                        $entryupdate = $entry_o->confirm($entry['id'], 0, "L");  // update entry record
                    }
                    else
                    {                         
                        $failnum++;
                        $entryupdate = $entry_o->confirm($entry['id'], "", "F");                 // update entry record
                        $fbufr.= "<p>{$result['class']} {$result['sailnum']} : {$result['status']}</p>";                        
                    }
                }
                
            }
            combinegrowls($eventid, $page, $pagestate, $retnum, $decnum, $failnum, $fbufr);    // present summary as growl
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
        // get current racestate
        $racestate = $race_o->racestate_get();
        
        // for each fleet that has changed
        foreach ($racestate as $key=>$fleet)
        {
            $fleetnum = $fleet['race'];
            $finishlap[$fleetnum] = $_REQUEST['finishlap'][$fleetnum];
            if ( $fleet['maxlap'] != $finishlap[$fleetnum] )     // laps have been changed for this fleet
            {
                // update racestate table
                $update = $race_o->race_laps_set($fleet['race'], array("maxlap"=>$finishlap[$fleetnum]));
            }
        }
        // FIXME  - add growls confirmation
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
        u_exitnicely($scriptname, $eventid,"sys005",$lang['err']['exit-action']);
    }
    
    if (!$stop_here) { header("Location: rbx_pg_results.php?eventid=$eventid"); exit(); }  // back to results page
       
}
else
{
    //FIXME
    u_exitnicely($scriptname, $eventid,"sys005",$lang['err']['exit-action']);
}

// ------------- FUNCTIONS ---------------------------------------------------------------------------
/*function combinegrowls($eventid, $page, $pagestate, $retnum, $decnum, $failnum, $fbufr)
{    
    $gclose = 4000;
    $gstyle = "success";
    if ($pagestate == "retirements")
    {
        $gbufr = "<p> </p><p><b>RETIRED: $retnum</b></p><hr>";
        if ($failnum>0)
        {
            $gclose = 50000;
            $gstyle = "danger";
            $gbufr.= "<p> </p><p><b>FAILED: $failnum</b></p>$fbufr";
        }
    }
    elseif ($pagestate == "declarations")
    {
        $gbufr = "<p> </p><p><b>DECLARED: $decnum</b></p><p><b>RETIRED: $retnum</b></p><hr>";
        if ($failnum>0)
        {
            $gclose = 50000;
            $gstyle = "danger";
            $gbufr.= "<p> </p><p><b>FAILED: $failnum</b></p>$fbufr ";
        }
    }
    u_setgrowls($eventid, $page, $gstyle, $gclose, $gbufr, array());
}*/
?>