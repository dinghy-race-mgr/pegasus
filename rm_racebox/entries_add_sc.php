<?php
/**
 *
 * 
 */
$debug      = false;
$loc        = "..";                                                // <--- relative path from script to top level folder
$page       = "addentry";     // 
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php"); 
// require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/comp_class.php");
require_once ("{$loc}/common/classes/entry_class.php");

u_initpagestart($_REQUEST['eventid'], $page, $_REQUEST['menu']);   // starts session and sets error reporting
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");    // language file

// process parameters  (eventid, pagestate, entryid)
empty($_REQUEST['eventid']) ? $eventid = "" : $eventid = $_REQUEST['eventid'];
empty($_REQUEST['pagestate']) ?  $page_state = "" : $page_state = $_REQUEST['pagestate'];

if ($eventid AND $page_state)
{
    $db_o = new DB;
    $comp_o = new COMPETITOR($db_o);

    if($page_state == "search")                                          // do search and return results
    {
        unset($_SESSION["e_$eventid"]['enter_opt']);                     // initialise session variables
        $_SESSION["e_$eventid"]['enter_opt'] = $comp_o->comp_searchcompetitor($_REQUEST['searchstr']);
    }

    elseif ($page_state == "enterone")       // add competitor to current event
    {
        $entry_o = new ENTRY($db_o, $eventid);
        // debug:u_writedbg("competitor {$_REQUEST['competitorid']}",__FILE__,__FUNCTION__,__LINE__);  // debug:
        $entry = $entry_o->get_competitor($_REQUEST['competitorid']);
        // debug:u_writedbg(u_check($entry, "ENTRY"),__FILE__,__FUNCTION__,__LINE__);  // debug:

        if ($entry)
        {
            enter_boat($entry, $eventid);
        }
        else
        {
            $_SESSION["e_$eventid"]['enter_err']= "Failed - competitor record not found";
        }
    }

    else
    {
        u_exitnicely($scriptname, $eventid,"event001","pagestate not recognised ".$lang['err']['exit-action']);
    }

    header("Location: entries_add_pg.php?eventid=$eventid&pagestate=pick");
    exit();
}
else
{
    u_exitnicely($scriptname, $eventid,"sys005", "eventid and/or pagestate not defined ($eventid|$page_state)");
}

// ------------- FUNCTIONS ---------------------------------------------------------------------------
function enter_boat($entry, $eventid, $race = "")
{
    global $entry_o;

    $entry_tag = "{$entry['classname']} - {$entry['sailnum']}";
    $alloc = $entry_o->allocate($entry);
    //u_writedbg(u_check($alloc, "ALLOCATE"),__FILE__,__FUNCTION__,__LINE__); //debug:

    if ($alloc['status'])           // ok to load entry
    {
        $i = $entry['fleet'];
        $entry = array_merge($entry, $alloc);
        $entry_alloc = "[S {$entry['start']} / F $i]";
        $result = $entry_o->set_entry($entry);

        if ($result['status'])
        {
            $upd = $entry_o->upd_lastrace( $entry['id'], $eventid);         // update competitor record
            $fleet_name = $_SESSION["e_$eventid"]["fl_$i"]['code'];
            $_SESSION["e_$eventid"]['enter_rst'][] = "$entry_tag [$fleet_name]";

            $_SESSION["e_$eventid"]["fl_$i"]['entries']++;                  // increment no. of entries
            $_SESSION["e_$eventid"]['result_status'] = "invalid";           // set results update flag

            u_writelog("ENTRY: $entry_tag", $eventid);
        }
        else
        {
            $_SESSION["e_$eventid"]['enter_err']= "$entry_tag $entry_alloc - race $race - failed ({$result["problem"]})";
            u_writelog("ENTRY FAILED: $entry_tag [{$result["problem"]}]", $eventid);
        }
    }
    else
    {
        $_SESSION["e_$eventid"]['enter_err']= "$entry_tag - allocation failed ({$alloc['alloc_code']}))";
        u_writelog("ENTRY FAILED: $entry_tag [no fleet allocation - {$alloc['alloc_code']}]", $eventid);
    }
}
?>