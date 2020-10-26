<?php
/**
 *  entries_add_sc.php
 * 
 */
$debug      = false;
$loc        = "..";
$page       = "addentry";     // 
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");

$eventid = u_checkarg("eventid", "checkintnotzero","");
$page_state = u_checkarg("pagestate", "set","");

u_initpagestart($_REQUEST['eventid'], $page, "");
include ("{$loc}/config/lang/{$_SESSION['lang']}-racebox-lang.php");

if (!$eventid) {
    u_exitnicely($scriptname, $eventid, "the requested event has an invalid record identifier [{$_REQUEST['eventid']}]",
        "please contact your raceManager administrator");
}

if (empty($page_state)) {
    u_exitnicely($scriptname, $eventid, "the page state has not been set",
        "please contact your raceManager administrator");
}

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/boat_class.php");
require_once ("{$loc}/common/classes/comp_class.php");
require_once ("{$loc}/common/classes/entry_class.php");

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
    //echo "<pre>".print_r($entry,true)."</pre>";
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
    u_exitnicely($scriptname, $eventid,"ENTER failed - the value of page state not recognised [$pagestate]","please contact your raceManager administrator");
}

header("Location: entries_add_pg.php?eventid=$eventid&pagestate=pick");
exit();


// ------------- FUNCTIONS ---------------------------------------------------------------------------
function enter_boat($entry, $eventid, $race = "")
{
    global $entry_o, $db_o;

    $entry_tag = "{$entry['classname']} - {$entry['sailnum']}";

    $event_o = new EVENT($db_o);
    $boat_o = new BOAT($db_o);
    $classcfg = $boat_o->boat_getdetail($entry['classname']);
    $fleets = $event_o->event_getfleetcfg($_SESSION["e_$eventid"]['ev_format']);
    $alloc = r_allocate_fleet($classcfg, $fleets);
//    $alloc = $entry_o->allocate($entry);
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
            $_SESSION["e_$eventid"]['enter_rst'][] = $entry_tag;

            $_SESSION["e_$eventid"]["fl_$i"]['entries']++;                  // increment no. of entries
            $_SESSION["e_$eventid"]['result_status'] = "invalid";           // set results update flag

            u_writelog("ENTRY: $entry_tag", $eventid);
        }
        else
        {
            $_SESSION["e_$eventid"]['enter_err']= "$entry_tag entry failed ({$result["problem"]})";
            u_writelog("ENTRY FAILED: $entry_tag - failed [{$result["problem"]}]", $eventid);
        }
    }
    else
    {
        $_SESSION["e_$eventid"]['enter_err']= "$entry_tag - allocation failed ({$alloc['alloc_code']}))";
        u_writelog("ENTRY FAILED: $entry_tag [no fleet allocation - {$alloc['alloc_code']}]", $eventid);
    }
}
