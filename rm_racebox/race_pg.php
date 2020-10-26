<?php
/**
 * race_pg.php
 *
 * @abstract race administration page
 * 
 * This page allows the user to change some details of the race they are running and run
 * some administration functions (e.g. cancel, reset, close, etc.).  It is the racebox
 * application landing page once the event has been chosen.
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *
 * FIXME - exit nicely
 * FIXME - r_oktoreset needs to be implemented
 * FIXME - r_oktoclose
 *
 */

$loc        = "..";
$page       = "race";
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php"); 

$eventid = u_checkarg("eventid", "checkintnotzero","");

u_initpagestart($_REQUEST['eventid'], $page, true);
include ("{$loc}/config/lang/{$_SESSION['lang']}-racebox-lang.php"); // language file
//u_writedbg("<pre>session".print_r($_SESSION,true)."</pre>", __FILE__, __FUNCTION__, __LINE__); //debug:
//echo "<pre>".print_r($_SESSION,true)."</pre>";

if (!$eventid) { u_exitnicely($scriptname, 0, "the requested event has an invalid record identifier [{$_REQUEST['eventid']}]",
                              "please contact your raceManager administrator");  }

require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("{$loc}/common/lib/raceformat_lib.php");
include ("{$loc}/common/classes/db_class.php");
include ("{$loc}/common/classes/template_class.php");
include ("{$loc}/common/classes/event_class.php");
include ("{$loc}/common/classes/rota_class.php");

$eventname = $_SESSION["e_$eventid"]['ev_name'];

$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/race_tm.php"));

$db_o = new DB;                                                  // database connection
$event_o = new EVENT($db_o);
$rota_o = new ROTA($db_o);
$event = $event_o->get_event_byid($eventid);

include ("./include/race_ctl.inc");
include("./templates/growls.php");                      // FIXME why is this here - this is not a template as such

//echo "<pre>".print_r($_SESSION,true)."</pre>";

$fleet_data = array();
$flag_data = array();
for ($fleetnum=1; $fleetnum<=$_SESSION["e_$eventid"]['rc_numfleets']; $fleetnum++)
{
    $fleet_data["$fleetnum"] = $_SESSION["e_$eventid"]["fl_$fleetnum"];
    $flag_data["$fleetnum"] = $_SESSION["e_$eventid"]["fl_$fleetnum"]['warnsignal'];
}
//echo "<pre>".print_r($flag_data,true)."</pre>";

//  check laps status
$laps_set = checklapstatus($eventid);
if (in_array(false, $laps_set, true)) { u_growlSet($eventid, $page, $g_race_laps_not_set); }

// ----- navbar -----------------------------------------------------------------------------
$fields = array("eventid" => $eventid, "brand" => "raceBox: {$_SESSION["e_$eventid"]['ev_label']}", "club" => $_SESSION['clubcode']);
$params = array("page" => $page, "pursuit" => $_SESSION["e_$eventid"]['pursuit'], "links" => $_SESSION['clublink']);
$nbufr = $tmpl_o->get_template("racebox_navbar", $fields, $params);

// ----- left hand panel --------------------------------------------------------------------
$lbufr_top = u_growlProcess($eventid, $page);                      // check for confirmations to present
$lbufr_mid = "";
$lbufr_bot = "";

if ($_SESSION["e_$eventid"]['exit'])
{
    $lbufr_top.= $tmpl_o->get_template("race_exit", array());
    unset($_SESSION["e_$eventid"]['exit']);
}
else
{
    empty($_SESSION["e_$eventid"]['ev_tidetime']) ? $tidestr = "" :
        $tidestr = r_tideformat($_SESSION["e_$eventid"]['ev_tidetime'], $_SESSION["e_$eventid"]['ev_tideheight']);

    $fields = array(
        "start-time"   => $_SESSION["e_$eventid"]['ev_starttime'],
        "tide-detail"  => $tidestr,
        "ood-name"     => $_SESSION["e_$eventid"]['ev_ood'],
        "event-name"   => $_SESSION["e_$eventid"]['ev_name'],
        "event-status" => $_SESSION["e_$eventid"]['ev_status'],
        "race-format"  => $_SESSION["e_$eventid"]['rc_name'],
        "race-starts"  => $_SESSION["e_$eventid"]['rc_numstarts'],
        "series-name"  => $_SESSION["e_$eventid"]['ev_seriesname'],
    );
//echo "<pre>session".print_r($_SESSION,true)."</pre>";
//echo "<pre>session".print_r($fields,true)."</pre>";
    $lbufr_top .= $tmpl_o->get_template("race_detail_display", $fields, $fields);

    // get current fleet status
    $params = array(
        "eventid"        => $eventid,
        "timer-start"    => $_SESSION["e_$eventid"]['timerstart'],       // time watch started
        "fleet-data"     => $event_o->get_fleetstatus($eventid),    // current fleet status
        "flag-data"      => $flag_data,
        "pursuit"        => $_SESSION["e_$eventid"]['pursuit'],
        "start-scheme"   => $_SESSION["e_$eventid"]['rc_startscheme'],
        "start-interval" => $_SESSION["e_$eventid"]['rc_startint']
    );
    $lbufr_mid = $tmpl_o->get_template("race_status_display", array(), $params);

    if (!$_SESSION["e_$eventid"]['pursuit'])
    {
        $params = array(
            "lapstatus" => $laps_set[0],
            "fleet-data" => $fleet_data,
        );
        $mdl_setlaps['fields']['body'] = $tmpl_o->get_template("fm_race_setlaps", array(), $params);
        $lbufr_bot = $tmpl_o->get_template("modal", $mdl_setlaps['fields'], $mdl_setlaps);
    }
}

// ----- right hand panel --------------------------------------------------------------------
$rbufr_top = "";

// change race details - modal
$rbufr_top.= $tmpl_o->get_template("btn_modal", $btn_change['fields'], $btn_change);
$fields = array(
    "event-ood"      => $_SESSION["e_$eventid"]['ev_ood'],
    "start-time"     => $_SESSION["e_$eventid"]['ev_starttime'],
    "entry-option"   => $_SESSION["e_$eventid"]['ev_entry'],
    "start-option"   => $_SESSION["e_$eventid"]['rc_startscheme'],
    "start-interval" => $_SESSION["e_$eventid"]['rc_startint'],
    "event-notes"    => $_SESSION["e_$eventid"]['ev_notes'],
);
$mdl_change['fields']['body'] = $tmpl_o->get_template("fm_changerace", $fields, $fields);
$rbufr_top.= $tmpl_o->get_template("modal", $mdl_change['fields'], $mdl_change);

// race format  - modal
$rbufr_top.= $tmpl_o->get_template("btn_modal", $btn_format['fields'], $btn_format);

$viewbufr = createdutypanel($rota_o->get_event_duties($eventid), $eventid, "");
$viewbufr.= createfleetpanel ($event_o->event_getfleetcfg($event['event_format']), $eventid, "");
$viewbufr.= createsignalpanel(getsignaldetail($event_o, $event), $eventid, "");

$mdl_format['fields']['body'] = $viewbufr;
$mdl_format['fields']['title'] = "Race Format: <b>$eventname</b>";
$mdl_format['fields']['footer'] = createprintbutton($eventid, true);
$rbufr_top.= $tmpl_o->get_template("modal", $mdl_format['fields'], $mdl_format);

// send message - modal
$rbufr_top.= $tmpl_o->get_template("btn_modal", $btn_message['fields'], $btn_message);
$mdl_message['fields']['body'] = $tmpl_o->get_template("fm_race_message", array());
$rbufr_top.= $tmpl_o->get_template("modal", $mdl_message['fields'], $mdl_message);

// pursuit start times - modal
if ($_SESSION["e_$eventid"]['fl_1']['scoring']=="pursuit")
{
    include ("{$loc}/common/classes/boat_class.php");
    $class_o = new BOAT($db_o);
    $class_list = $class_o->boat_getclasslist();
    $rbufr_top.= $tmpl_o->get_template("btn_modal", $btn_pursuit['fields'], $btn_pursuit);
    $mdl_pursuit['pytype'] = $_SESSION["e_$eventid"]["fl_1"]['pytype'];
    $mdl_pursuit['body'] = $tmpl_o->get_template("fm_race_pursuitstart", array());
    $rbufr_top.= $tmpl_o->get_template("modal", $mdl_pursuit['fields'], $class_list);  // FIXME - nnot sure about this
}
$rbufr_mid ="<hr>";

// cancel  - modal
if ($_SESSION["e_$eventid"]['ev_status']!="cancelled")
{
    $cancel_ok = r_oktocancel($eventid, "cancel");
    if ($cancel_ok['result'])   // results published
    {
        $mdl_cancel['fields']['body'] = $tmpl_o->get_template("fm_cancel_ok", $cancel_ok);
    }
    else
    {
        $mdl_cancel['fields']['body'] = $tmpl_o->get_template("fm_cancel_notok", $cancel_ok);
        $mdl_cancel['fields']['submit-lbl'] = "";
    }
    $rbufr_mid.= $tmpl_o->get_template("btn_modal", $btn_cancel['fields'], $btn_cancel);
    $rbufr_mid.= $tmpl_o->get_template("modal", $mdl_cancel['fields'], $mdl_cancel);

}
else
{
    $cancel_ok = r_oktocancel($eventid, "uncancel");
    if ($cancel_ok['result'])
    {
        $mdl_uncancel['fields']['body'] = $tmpl_o->get_template("fm_uncancel_ok", $cancel_ok);
    }
    else
    {
        $mdl_uncancel['fields']['body'] = $tmpl_o->get_template("fm_uncancel_notok", $cancel_ok);
        $mdl_uncancel['fields']['submit-lbl'] = "";
        $mdl_uncancel['fields']['close-lbl'] = " close";
    }
    $rbufr_mid.= $tmpl_o->get_template("btn_modal", $btn_uncancel['fields'], $btn_uncancel);
    $rbufr_mid.= $tmpl_o->get_template("modal", $mdl_uncancel['fields'], $mdl_uncancel);
}

// abandon - modal
if ($_SESSION["e_$eventid"]['ev_status']!="abandoned")
{
    $abandon_ok = r_oktoabandon($eventid, "abandon");
    if ($abandon_ok['result'])
    {
        $abandon_ok['eventid'] = $eventid;
        $mdl_abandon['fields']['body'] = $tmpl_o->get_template("fm_abandon_ok", $abandon_ok);
    }
    else
    {
        $mdl_abandon['fields']['body'] = $tmpl_o->get_template("fm_abandon_notok", $abandon_ok);
        $mdl_abandon['fields']['submit-lbl'] = "";
        $mdl_abandon['fields']['close-lbl'] = " close";
    }
    $rbufr_mid.= $tmpl_o->get_template("btn_modal", $btn_abandon['fields'], $btn_abandon);
    $rbufr_mid.= $tmpl_o->get_template("modal", $mdl_abandon['fields'], $mdl_abandon);

}
else
{
    $abandon_ok = r_oktoabandon($eventid, "unabandon");
    if ($abandon_ok['result'])
    {
        $mdl_unabandon['body'] = $tmpl_o->get_template("fm_unabandon_ok", $abandon_ok);
    }
    else
    {
        $mdl_unabandon['fields']['body'] = $tmpl_o->get_template("fm_unabandon_notok", $abandon_ok);
        $mdl_unabandon['fields']['submit-lbl'] = "";
        $mdl_unabandon['fields']['close-lbl'] = " close";
    }
    $rbufr_mid.= $tmpl_o->get_template("btn_modal", $btn_unabandon['fields'], $btn_unabandon);
    $rbufr_mid.= $tmpl_o->get_template("modal", $mdl_unabandon['fields'], $mdl_unabandon);

}

$rbufr_bot ="<hr>";

// close  - modal
$close_ok = r_oktoclose($eventid);
if ($close_ok['result'])
{
    $mdl_close['fields']['body'] = $tmpl_o->get_template("fm_close_ok", $close_ok);
}
else
{
    $mdl_close['fields']['body'] = $tmpl_o->get_template("fm_close_notok", $close_ok);
    $mdl_close['fields']['submit-lbl'] = "";
    $mdl_close['fields']['close-lbl'] = " close";
}
$rbufr_bot.= $tmpl_o->get_template("btn_modal", $btn_close['fields'], $btn_close);
$rbufr_bot.= $tmpl_o->get_template("modal", $mdl_close['fields'], $mdl_close);


// reset  - modal
$reset_ok = r_oktoreset($eventid);
if ($reset_ok['result'])
{
    $mdl_reset['fields']['body'] = $tmpl_o->get_template("fm_reset_ok", array());
}
else
{
    $mdl_reset['fields']['body'] = $tmpl_o->get_template("fm_reset_notok", $reset_ok);
    $mdl_reset['fields']['submit-lbl'] = "";
    $mdl_reset['fields']['close-lbl'] = " close";
}
$rbufr_bot.= $tmpl_o->get_template("btn_modal", $btn_reset['fields'], $btn_reset);
$rbufr_bot.= $tmpl_o->get_template("modal", $mdl_reset['fields'], $mdl_reset);

// disconnect database
$db_o->db_disconnect();

if ($_SESSION["e_$eventid"]['exit'])
{
    $nbufr = "";
    $rbufr = "";
}

// ----- render page -------------------------------------------------------------------------
$fields = array(
    "title"      => $_SESSION["e_$eventid"]['ev_label'],
    "theme"      => $_SESSION['racebox_theme'],
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => $lbufr_top,
    "l_mid"      => $lbufr_mid,
    "l_bot"      => $lbufr_bot,
    "r_top"      => $rbufr_top,
    "r_mid"      => $rbufr_mid,
    "r_bot"      => $rbufr_bot,
    "footer"     => "",
    "body_attr"  => "onload=\"startTime()\""
);

$params = array(
    "page"      => $page,
    "refresh"   => 0,
    "l_width"   => 10,
    "forms"     => true,
    "tables"    => true,
);
echo $tmpl_o->get_template("two_col_page", $fields, $params);


// ---- local functions ----------------------------------------------------
function checklapstatus ($eventid)
{
    $laps_set = array();
    $laps_set[0] = true;
    for ($i=1; $i<=$_SESSION["e_$eventid"]['rc_numfleets']; $i++)
    {
        $laps_set[$i] = true;
        if ($_SESSION["e_$eventid"]["fl_$i"]['entries'] > 0 AND $_SESSION["e_$eventid"]["fl_$i"]['maxlap'] <= 0 )
        {
            $laps_set[0] = false;
            $laps_set[$i]= false;
        }
    }
    return $laps_set;
}

