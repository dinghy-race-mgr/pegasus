<?php
/**
 *  entries_add_sc.php
 * 
 */
$debug      = false;
$loc        = "..";
$page       = "addentry";     // 
$scriptname = basename(__FILE__);
$stop_here  = false;

require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");

// script parameters
$eventid = u_checkarg("eventid", "checkintnotzero","");
$pagestate = u_checkarg("pagestate", "set","");

if (!$eventid or empty($pagestate)) {
    u_exitnicely($scriptname, 0, "$page page has an invalid or missing event identifier [{$_REQUEST['eventid']}] or page state [{$_REQUEST['pagestate']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array())); }

// start session
session_id('sess-rmracebox');
session_start();

// page initialisation
u_initpagestart($eventid, $page, false);

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/boat_class.php");
require_once ("{$loc}/common/classes/comp_class.php");
require_once("{$loc}/common/classes/race_class.php");
require_once ("{$loc}/common/classes/entry_class.php");

$db_o = new DB;
$comp_o = new COMPETITOR($db_o);

if($pagestate == "search")                                          // do search and return results
{
    unset($_SESSION["e_$eventid"]['enter_opt']);                     // initialise session variables
    $_SESSION["e_$eventid"]['enter_opt'] = $comp_o->comp_searchcompetitor($_REQUEST['searchstr'], "racebox");
}

elseif ($pagestate == "enterone")       // add competitor to current event
{
    $entry_o = new ENTRY($db_o, $eventid);
    // debug:u_writedbg("competitor {$_REQUEST['competitorid']}",__FILE__,__FUNCTION__,__LINE__);  // debug:
    $entry = $entry_o->get_competitor($_REQUEST['competitorid']);

    //echo "<pre>".print_r($entry,true)."</pre>";
    // debug:

    if ($entry)
    {
        $status = enter_boat($entry, $eventid);

        // get classes (used on list pages)
        $race_o = new RACE($db_o, $eventid);
        $_SESSION["e_$eventid"]['classes'] = $race_o->count_groups("class", "count", 11);
    }
    else
    {
        $_SESSION["e_$eventid"]['enter_err']= "Failed - competitor record not found";
    }
}

else
{
    u_exitnicely($scriptname, $eventid, "ENTER failed - the value of page state not recognised [$pagestate]\"",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

if (!$stop_here) { header("Location: entries_add_pg.php?eventid=$eventid&pagestate=pick"); exit();}  // back to entries add page


// ------------- FUNCTIONS ---------------------------------------------------------------------------
function enter_boat($entry, $eventid)
    // FIXME this is very similar to the same function in entries_sc.php
{
    global $entry_o, $db_o;

    $entry_tag = "{$entry['classname']} - {$entry['sailnum']}";

    $event_o = new EVENT($db_o);
    $boat_o = new BOAT($db_o);
    $race_o = new RACE($db_o, $eventid);

    $classcfg = $boat_o->boat_getdetail($entry['classname']);
    $fleets = $event_o->event_getfleetcfg($_SESSION["e_$eventid"]['ev_format']);
    $alloc = r_allocate_fleet($classcfg, $fleets);

    if ($alloc['status'])           // ok to load entry
    {
        $entry = array_merge($entry, $alloc);
        $i = $entry['fleet'];
        $entry_alloc = "[S {$entry['start']} / F {$entry['fleet']}]";
        $result = $entry_o->set_entry($entry, $_SESSION["e_$eventid"]["fl_$i"]['pytype'], $_SESSION["e_$eventid"]["fl_$i"]['maxlap']);

        if ($result['status'])
        {

            $upd = $entry_o->upd_lastrace( $entry['id'], $eventid);         // update competitor record
            $_SESSION["e_$eventid"]['enter_rst'][] = $entry_tag;

            $_SESSION["e_$eventid"]["fl_$i"]['entries']++;                  // increment no. of entries

            // FIXME removed becuase this should be handled by "sc" racestate catchup
            //$update = $race_o->racestate_update(array("entries"=>$_SESSION["e_$eventid"]["fl_$i"]['entries']), array("fleet"=>$i));  // updae racestate

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

    return $result['status'];
}
