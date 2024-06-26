<?php
/**
 * entries_sc.php
 *
 * @abstract Processes server requests from the entries page
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 * 
 */
$loc        = "..";
$page       = "entries";
$scriptname = basename(__FILE__);
$stop_here  = false;

require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("./include/rm_racebox_lib.php");

// start session
u_startsession("sess-rmracebox", 10800);

// arguments
$eventid   = u_checkarg("eventid", "checkintnotzero","");
$pagestate = u_checkarg("pagestate", "set", "", "");
$entryid   = u_checkarg("entryid", "checkint", "", "");

// initialise page
u_initpagestart($eventid, $page, false);

// classes / libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/boat_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/comp_class.php");
require_once ("{$loc}/common/classes/race_class.php");

// page controls
include ("./templates/growls.php");

if ($eventid and !empty($pagestate))
{
    $db_o = new DB;
    $event_o = new EVENT($db_o);
    $entry_o = new ENTRY($db_o, $eventid);
    $race_o = new RACE($db_o, $eventid);

    // ------- per entry functions --------------------------------------------------------------------------
    if ($entryid)   // deal with in table button functions
    {
        $entry = $entry_o->get_by_raceid($entryid);
        $entryname = $entry['class']." ".$entry['sailnum'];

        // ------- CHANGE --------------------------------------------------------------------------
        if ($pagestate == "change")
        {
            if ($_REQUEST['helm']==$entry['helm'] AND $_REQUEST['crew']==$entry['crew'] AND
                $_REQUEST['sailnum']==$entry['sailnum'] AND $_REQUEST['pn']==$entry['pn'])
            {
                u_growlSet($eventid, $page, $g_entries_change_none);
            }
            else
            {
                $change = array();
                if (!empty($_REQUEST['helm']))    { $change['helm']    = $_REQUEST['helm']; }
                if (!empty($_REQUEST['crew']))    { $change['crew']    = $_REQUEST['crew']; }
                if (!empty($_REQUEST['sailnum'])) { $change['sailnum'] = $_REQUEST['sailnum']; }
                if (!empty($_REQUEST['pn']))      { $change['pn']      = $_REQUEST['pn']; }

                if ($entry_o->update($entryid, $change)) {
                    u_writelog("CHANGE ENTRY: changed details for $entryname ".print_r($change, true), $eventid);
                } else {
                    u_writelog("CHANGE ENTRY failed: attempt to change details for $entryname failed ", $eventid);
                    u_growlSet($eventid, $page, $g_entries_change_entry_failed);
                }
            }

        }
        
        elseif ($pagestate == "dutypoints")
        {
            if ($entry_o->duty_set($entryid, $entry['status'])) {
                u_writelog("ADD DUTY: $entryname allocated duty points ", $eventid);
            } else {
                u_writelog("ADD DUTY failed: attempt to allocate duty points for $entryname failed ", $eventid);
                u_growlSet($eventid, $page, $g_entries_add_duty_failed);
            }

        }
        
        elseif ($pagestate == "unduty")
        {
            if ($entry_o->duty_unset($entryid, $entry['status'])) {
                u_writelog("REMOVE DUTY: removed duty points for $entryname ", $eventid);
            } else {
                u_writelog("REMOVE DUTY failed: attempt to remove duty points for $entryname failed ", $eventid);
                u_growlSet($eventid, $page, $g_entries_remove_duty_failed);
            }
        }
        
        elseif ($pagestate == "delete")
        {
            if ($entry_o->delete($entryid)) {
                u_writelog("ENTRY DELETED: $entryname deleted from race ", $eventid);
                $_SESSION["e_{$eventid}"]['result_status'] = "invalid";
                $_SESSION["e_{$eventid}"]["fl_{$entry['fleet']}"]['entries']--;
            }
            else
            {
                u_writelog("ENTRY DELETED failed: attempt to delete entry for $entryname failed ", $eventid);
                u_growlSet($eventid, $page, $g_entries_delete_failed);
            }
        }
    }

    // ------- page button functions --------------------------------------------------------------------------

    elseif ($pagestate == "loadentries")
    {
        $problems = $entry_o->chk_signon_errors("entries");        // includes entries, updates and retirements
        $signons = $entry_o->get_signons("entries");               // includes entries, and updates
        $entries_found = count($signons);

        if ($entries_found > 0)                                // deal with entries
        {
            $entries_deleted = 0;
            $entries_replaced = 0;
            $entered = 0;
            foreach ($signons as $signon)
            {
                if ($signon['action'] == "update" OR $signon['action'] == "replace" OR $signon['action'] == "delete")
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
                    if ($status['state'] == "entered")
                    {
                        $entered++;
                    }
                    elseif ($status['state'] == "exists")
                    {
                        $entries_replaced++;
                    }
                    elseif ($status['state'] == "failed") // save entry details for display
                    {
                        $problems[] = array("id"=>$signon['t_entry_id'], "boat"=>$status['entry'], "reason"=>$status['reason']);
                    }
                }
            }

            // report summary of entries made
            $entry_txt = "<br>- $entered entries made";
            if ($entries_replaced > 0) { $entry_txt.= "<br>$entries_replaced existing entries updated"; }
            if ($entries_deleted > 0) { $entry_txt.= "<br>$entries_deleted existing entries removed"; }
            u_growlSet($eventid, $page, $g_entries_report, array($entry_txt));
            u_writelog("ENTRY load: $entered entered - $entries_replaced updated", $eventid);
        }
        else
        {
            // report no entries
            u_growlSet($eventid, $page, $g_entries_none);
            u_writelog("ENTRY load: no boats", $eventid);
        }

        // report failed entries
        if (!empty($problems))
        {
            $problem_txt = "";
            foreach ($problems as $problem)
            {
                $problem_txt .= "&nbsp;&nbsp;&nbsp;{$problem['boat']} &nbsp;:&nbsp;&nbsp; {$problem['reason']}  [id = {$problem['id']}]<br>";
            }
            u_growlSet($eventid, $page, $g_entries_fail_detail, array($problem_txt));
            u_writelog("ENTRY load problems: ".str_replace("&nbsp;", "", $problem_txt), $eventid);
        }
    }
    
    // loads competitors marked as regular
    elseif ($pagestate == "loadregular")
    {
        $entries = $entry_o->get_regulars();

        if ($entries)
        {
            $entries_found = count($entries);
            $entries_replaced = 0;
            $entries_deleted = 0;
            $entered = 0;
            $problems = array();
            foreach ($entries as $entry)
            {
                $status = enter_boat($entry, $eventid, "regular");
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
                    $problems[] = array("id"=>$entry['id'], "boat"=>$status['entry'], "reason"=>$status['reason']);
                }
            }

            u_growlSet($eventid, $page, $g_entries_report, array(" $entered entered - $entries_replaced updated"));
            u_writelog("ENTRY regulars load: $entered entered - $entries_replaced updated", $eventid);
            // report failed entries
            if (!empty($problems))
            {
                $problem_txt = "";
                foreach ($problems as $problem) {
                    $problem_txt .= "{$problem['boat']} - {$problem['reason']}  [id = {$problem['id']}]<br>";
                }
                u_growlSet($eventid, $page, $g_entries_fail_detail, array($problem_txt));
                u_writelog("ENTRY regulars problems: $problem_txt", $eventid);
            }
        }
        else
        {
            u_growlSet($eventid, $page, $g_entries_none);
            u_writelog("ENTRY regulars: no regular competitors found", $eventid);
        }
    }

    // loads competitors who have raced earlier today
    elseif ($pagestate == "loadprevious")
    {
        $entries = $entry_o->get_previous(date("Y-m-d"));

        if ($entries)
        {
            $entries_found = count($entries);
            $entries_replaced = 0;
            $entries_deleted = 0;
            $entered = 0;
            foreach ($entries as $entry)
            {
                $status = enter_boat($entry, $eventid, "previous");
                if ($status['state'] == "entered")
                {
                    $entered++;
                }
                elseif ($status['state'] == "exists")
                {
                    $entries_replaced++;
                }
                elseif ($status['state'] == "failed")        // save entry details for display
                {
                    $problems[] = array("id"=>$entry['id'], "boat"=>$status['entry'], "reason"=>$status['reason']);
                }
            }

            u_growlSet($eventid, $page, $g_entries_report, array(" $entered entered - $entries_replaced updated"));
            u_writelog("ENTRY previous load: $entered entered - $entries_replaced updated", $eventid);
            // report failed entries
            if (!empty($problems))
            {
                $problem_txt = "";
                foreach ($problems as $problem) {
                    $problem_txt .= "{$problem['boat']} - {$problem['reason']}  [id = {$problem['id']}]<br>";
                }
                u_growlSet($eventid, $page, $g_entries_fail_detail, array($problem_txt));
                u_writelog("ENTRY previous problems: $problem_txt", $eventid);
            }
        }
        else
        {
            u_growlSet($eventid, $page, $g_entries_none);
            u_writelog("ENTRY previous today: no previous entries for today found", $eventid);
        }
    }

    // OOD add new boat
    elseif ($pagestate == "addcompetitor")
    {
        $comp_o = new COMPETITOR($db_o);
        $boat_o = new BOAT($db_o);

        $new_boat = array( "updby"=>"OOD" );
        if (!empty($_REQUEST['classid'])) { $new_boat['classid'] = $_REQUEST['classid']; }
        if (!empty($_REQUEST['sailnum'])) { $new_boat['sailnum'] = $_REQUEST['sailnum']; }
        if (!empty($_REQUEST['helm'])   ) { $new_boat['helm']    = $_REQUEST['helm']   ; }
        if (!empty($_REQUEST['crew'])   ) { $new_boat['crew']    = $_REQUEST['crew']   ; }
        if (!empty($_REQUEST['club'])   ) { $new_boat['club']    = $_REQUEST['club']   ; }
        
        $classname = $boat_o->boat_getclassname($new_boat['classid']);
        $comp_tag = "$classname {$new_boat['sailnum']}: {$new_boat['helm']} ";
        
        $result = $comp_o->comp_addcompetitor($new_boat);
        //u_writedbg(u_check($result, "new competitor"),__FILE__,__FUNCTION__,__LINE__);
        if ($result['msg'] == "ok")
        {
            u_writelog("ADD COMPETITOR: $comp_tag", $eventid);
            u_growlSet($eventid, $page, $g_entry_add_comp_success, array($comp_tag));
            // send message
            include("./templates/messages.php");
            $message = array(
                "name"    => $_SESSION["e_$eventid"]['ev_ood'],
                "subject" => "NEW COMPETITOR - new competitor has been added",
                "message" => vsprintf($m_entry_add_competitor, array($result['id'], $classname, $new_boat['sailnum'], $new_boat['helm']) ),
            );
            $msg = $db_o->db_createmessage($eventid, $message, $application="racebox");
        }
        elseif ($result['msg'] == "competitor already exists")
        {
            u_growlSet($eventid, $page, $g_entry_add_comp_exists, array($comp_tag));
        }
        else
        {
            u_writelog("ADD COMPETITOR failed: $comp_tag ({$result['msg']})", $eventid);
            u_growlSet($eventid, $page, $g_entry_add_comp_fail, array($comp_tag));
        }
    }
    
    // add new class option
    elseif ($pagestate == "addclass")
    {
        $boat_o = new BOAT($db_o);
        $newclass = array("updby"=>"OOD");
        if (!empty($_REQUEST['classname'])) { $newclass['classname'] = $_REQUEST['classname']; }
        if (!empty($_REQUEST['py']))        { $newclass['nat_py']    = $_REQUEST['py']; }
        if (!empty($_REQUEST['category']))  { $newclass['category']  = $_REQUEST['category']; }
        if (!empty($_REQUEST['crew']))      { $newclass['crew']      = $_REQUEST['crew']; }
        if (!empty($_REQUEST['rig']))       { $newclass['rig']       = $_REQUEST['rig']; }
        if (!empty($_REQUEST['spinnaker'])) { $newclass['spinnaker'] = $_REQUEST['spinnaker']; }
        if (!empty($_REQUEST['engine']))    { $newclass['engine']    = $_REQUEST['engine']; }
        if (!empty($_REQUEST['keel']))      { $newclass['keel']      = $_REQUEST['keel']; }
        
        // add class to t_class table
        $result = $boat_o->boat_addclass($newclass);
        if ($result['msg'] =="ok")
        {
            u_writelog("ADD CLASS: {$newclass['classname']}", $eventid);
            u_growlSet($eventid, $page, $g_entry_add_class_success, array($newclass['classname']));
            // send message
            include("./templates/messages.php");
            $message = array(
                "name"    => $_SESSION["e_$eventid"]['ev_ood'],
                "subject" => "NEW CLASS - new class has been added",
                "message" => vsprintf($m_entry_add_class, array($newclass['classname'],$result['id']) ),
            );
            $msg = $db_o->db_createmessage($eventid, $message, $application="racebox");
        }
        else
        {
            u_writelog("ADD CLASS failed:  attempt to create new class failed - {$newclass['classname']}  [error: $result]", $eventid);
            u_growlSet($eventid, $page, $g_entry_add_class_fail, array($newclass['classname'], $result));
        }
    }
    else
    {
        u_growlSet($eventid, $page, $g_invalid_pagestate, array($pagestate, $page));
    }

    // get classes (used on list pages)
    $_SESSION["e_$eventid"]['classes'] = $race_o->count_groups("class", "count", 11);

    // check race state / update session
    $race_o->racestate_updatestatus_all($_SESSION["e_$eventid"]['rc_numfleets'], $page);

    // return to page
    if (!$stop_here) { header("Location: entries_pg.php?eventid=$eventid"); exit(); }   // back to entries page
}
else
{
    u_exitnicely($scriptname, 0, "$page page has an invalid or missing event identifier [{$_REQUEST['eventid']}] or page state [{$_REQUEST['pagestate']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// ------------- FUNCTIONS ---------------------------------------------------------------------------
//function enter_boat($entry, $eventid, $type)
//{
//    global $entry_o, $event_o, $db_o;
//
//    $boat_o = new BOAT($db_o);
//    $classcfg = $boat_o->boat_getdetail($entry['classname']);
//    $fleets = $event_o->event_getfleetcfg($_SESSION["e_$eventid"]['ev_format']);
//    $alloc = r_allocate_fleet($classcfg, $fleets);
//
//    $success = "failed";
//    $entry_tag = "{$entry['classname']} - {$entry['sailnum']}";
//
//    // debug:u_writedbg(u_check($alloc, "ALLOCATE"),__FILE__,__FUNCTION__,__LINE__);  // debug:
//
//    if ($alloc['status'])
//    {                                              // ok to load entry
//        $entry = array_merge($entry, $alloc);
//        $i = $entry['fleet'];
//        $result = $entry_o->set_entry($entry, $_SESSION["e_$eventid"]["fl_$i"]['pytype'], $_SESSION["e_$eventid"]["fl_$i"]['maxlap']);
//        // debug:u_writedbg(u_check($result, "LOAD"),__FILE__,__FUNCTION__,__LINE__);  // debug:
//        if ($result['status'])
//        {
//            $i = $entry['fleet'];
//
//            if ($result["exists"])
//            {
//                u_writelog("ENTRY ($type) UPDATED: $entry_tag", $eventid);
//                $success = "exists";
//            }
//            else
//            {
//                u_writelog("ENTRY ($type): $entry_tag", $eventid);
//                $success = "entered";
//                $_SESSION["e_$eventid"]["fl_$i"]['entries']++;   // increment no. of entries
//            }
//            if ($type == "signon") {  $upd = $entry_o->confirm_entry($entry['t_entry_id'], "L", $result['raceid']); }
//
//            $fleet_name = $_SESSION["e_$eventid"]["fl_$i"]['code'];
//            $_SESSION["e_$eventid"]['enter_rst'][] = "$entry_tag [$fleet_name]";
//
//            $_SESSION["e_$eventid"]['result_status'] = "invalid";           // set results update flag
//        }
//        else
//        {
//            u_writelog("ENTRY ($type) FAILED: $entry_tag [{$result["problem"]}]", $eventid);
//            if ($type == "signon") {  $upd = $entry_o->confirm_entry($entry['t_entry_id'], "F"); }
//        }
//    }
//    else
//    {
//        u_writelog("ENTRY ($type) FAILED: $entry_tag [no fleet allocation - {$alloc['alloc_code']}]", $eventid);
//        if ($type == "signon") {  $upd = $entry_o->confirm_entry($entry['t_entry_id'], $alloc['alloc_code']); }
//    }
//
//    return $success;
//}


