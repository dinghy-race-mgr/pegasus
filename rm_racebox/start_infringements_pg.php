<?php
/* ------------------------------------------------------------
   start_infringements_pg
   Allows OOD to set codes for start line infringements.

   
   ------------------------------------------------------------
*/

$loc        = "..";       // <--- relative path from script to top level folder
$page       = "start_infringement";     // 
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");

u_initpagestart($_REQUEST['eventid'],$page,"");   // starts session and sets error reporting

require_once ("{$loc}/common/classes/db_class.php"); 
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/race_class.php");

// app includes
require_once ("./include/rm_racebox_lib.php");

$pagestate = $_REQUEST['pagestate'];
$eventid   = $_REQUEST['eventid'];
$startnum  = $_REQUEST['startnum'];

if (empty($pagestate) OR empty($eventid))
{
    u_exitnicely($scriptname, $eventid,"$page page - event id record [{$_REQUEST['eventid']}], start number [{$_REQUEST['startnum']}] or pagestate value [{$_REQUEST['pagestate']}] not set",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/start_tm.php"));

// page controls
include ("./templates/growls.php");

$db_o    = new DB;              // create database object
$race_o  = new RACE($db_o, $eventid);

// ---- pagestate INIT ---------------------- display list of entries for this start
if ($pagestate == "init")
{
    // get entries for this start
    $entries = $race_o->race_getstarters(array("start"=>$startnum));

    $params = array(
        "eventid"    => $eventid,
        "startnum"   => $startnum,
        "entries"    => count($entries),
        "entry-data" => $entries,
    );

    $fields = array(
        "title"      => "start infringements",
        "theme"      => $_SESSION['racebox_theme'],
        "loc"        => $loc,
        "stylesheet" => "./style/rm_racebox.css",
        "navbar"     => "",
        "body"       => $tmpl_o->get_template("infringe", array(), $params),
        "footer"     => "",
    );
    echo $tmpl_o->get_template("basic_page", $fields);
}

// ---- pagestate SETCODE ---------------------- change code for specified entry
elseif ($pagestate == "setcode")
{
    if (!empty($_REQUEST['fleet']))
    {
        $setcode = set_code($eventid, $_REQUEST);

        if ($setcode !== true) { u_growlSet($eventid, $page, $g_timer_setcodefailed, array($_REQUEST['boat'], $setcode)); }
    }
    else
    {
        error_log("start_infrigements_pg.php/pagestate=setcode : fleet argument not set", 3, $_SESSION['syslog']);
    }

    empty($_REQUEST['fleet']) ? $err = true : $fleet = $_REQUEST['fleet'];

    if (!$err)
    {
        $setcode = set_code($eventid, $_REQUEST);

        if ($setcode !== true)
        {
            u_growlSet($eventid, $page, $g_timer_setcodefailed, array($_REQUEST['boat'], $setcode));
        }

//        if ($setcode === true)
//        {
//            if ($race_o->fleet_race_stillracing($fleet)) { $update['status'] = "allfinished"; }
//            if (!empty($update)) { $check = $race_o->racestate_update($update, array("eventid"=>"$eventid", "fleet"=>"$fleet")); }
//        }
//        else
//        {
//            u_growlSet($eventid, $page, $g_timer_setcodefailed, array($_REQUEST['boat'], $setcode));
//        }
    }
    else
    {
        error_log("start_infringements_pg.php/pagestate=setcode : fleet argument not set", 3, $_SESSION['syslog']);
    }


//
//
//    $setcode = set_code($eventid, $_REQUEST);
//
//    if($setcode !== true)
//    {
//        u_growlSet($eventid, $page, $g_timer_setcodefailed, array($_REQUEST['boat'], $setcode));
//    }

    header("Location: start_infringements_pg.php?eventid=$eventid&startnum=$startnum&pagestate=init");
}

// ---- pagestate 'unknown'' ---------------------- tdeal with invalid pagestate
else
{
    u_exitnicely($scriptname, $eventid,"$page page - pagestate value not recognised [{$_REQUEST['pagestate']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

