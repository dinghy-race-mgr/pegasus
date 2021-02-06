<?php
/**
 * timer_pg.php - main race timing page
 * 
 * This page allows the OOD to time laps and finish for each competitor
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 * @param string $eventid
 * 
 */

$loc        = "..";       // <--- relative path from script to top level folder
$page       = "timer";     // 
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");

// set event id
$eventid = u_checkarg("eventid", "checkintnotzero","");
if (!$eventid) {
    u_exitnicely($scriptname, 0, "the requested event has an invalid record identifier [{$_REQUEST['eventid']}]",
        "please contact your raceManager administrator");  }
else {
    u_initpagestart($eventid, $page, true);   // starts session and sets error reporting
}

// check if display mode has changed - reset session variable if necessary
$display_mode = u_checkarg("mode", "setnotnull", "");
if ($display_mode) { $_SESSION['timer_options']['mode'] = $display_mode; }

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/race_class.php");

// app includes
require_once ("./include/rm_racebox_lib.php");

// templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/timer_tm.php"));

// database connection
$db_o   = new DB;
$race_o = new RACE($db_o, $eventid);

// page controls
include ("./include/timer_ctl.inc");
include ("./templates/growls.php");

// FIXME - review if these options are used and where they should be set
$_SESSION['timer_options']['listorder']     = "class";     // options "class|pn|position|ptime""
$_SESSION['timer_options']['laptime']       = "button";    // options "row|button|both"
$_SESSION['timer_options']['notify_length'] = "on ";       // options "on|off"
$_SESSION['timer_options']['notify_undo']   = "on";        // options "on|off"

$fleet_data = array();
for ($fleetnum=1; $fleetnum<=$_SESSION["e_$eventid"]['rc_numfleets']; $fleetnum++)
{
    $fleet_data["$fleetnum"] = $_SESSION["e_$eventid"]["fl_$fleetnum"];
    //u_writedbg("FLEET:<pre>".print_r( $fleet_data["$fleetnum"],true)."</pre>", __FILE__, __FUNCTION__, __LINE__); // debug:)
}

// ----- navbar -----------------------------------------------------------------------------
$fields = array("eventid" => $eventid, "brand" => "raceBox: {$_SESSION["e_$eventid"]['ev_label']}", "club" => $_SESSION['clubcode']);
$params = array("page" => $page, "pursuit" => $_SESSION["e_$eventid"]['pursuit'], "links" => $_SESSION['clublink']);
$nbufr = $tmpl_o->get_template("racebox_navbar", $fields, $params);

// ----- left hand panel --------------------------------------------------------------------
$lbufr = u_growlProcess($eventid, $page);
$lbufr_bot = "";

$problems = problem_check($eventid);    // check problems preventing timing
if (in_array(true, $problems, true))
{
    $lbufr.= $tmpl_o->get_template("problems", array("eventid" => $eventid), $problems);
}
else
{
    // default to tabbed display mode if not set
    if (empty($_SESSION['timer_options']['mode'])) { $_SESSION['timer_options']['mode'] = "tabbed"; }

    // display boats as defined by display mode
    if ($_SESSION['timer_options']['mode'] == "tabbed")
    {
        $rs_race = $race_o->race_gettimings(true, 0, 0);
        $lbufr.= $tmpl_o->get_template("timer_tabs", array(),
              array("eventid" => $eventid, "num-fleets" => $_SESSION["e_$eventid"]['rc_numfleets'], "timings" => $rs_race));

        // add modals
        $lbufr.= $tmpl_o->get_template("modal", $mdl_editlap['fields'], $mdl_editlap);
    }
    elseif ($_SESSION['timer_options']['mode'] == "list")
    {
        $lbufr.= $tmpl_o->get_template("under_construction", array("title" => "Timer: List option", "info" => "We are still working on this"));
    }
    else
    {
        $lbufr.= $tmpl_o->get_template("under_construction", array("title" => "Timer: option", "info" => "Never heard of it"));
    }

//    if (!$_SESSION["e_$eventid"]['pursuit'])
//    {
//        $params = array(
//            "lapstatus" => check_lap_status($eventid),
//            "fleet-data" => $fleet_data,
//        );
//        $mdl_setlaps['body'] = $tmpl_o->get_template("fm_timer_setlaps", array(), $params);
//        $lbufr_bot = $tmpl_o->get_template("modal", $mdl_setlaps['fields'], $mdl_setlaps);
//    }
}

// ----- right hand panel --------------------------------------------------------------------
$rbufr = "";

// undo
$btn_undo['fields']['link'] = "timer_sc.php?eventid=$eventid&pagestate=undo";
$rbufr.= $tmpl_o->get_template("btn_link", $btn_undo['fields'], $btn_undo);

// shorten all fleets
$rbufr.= $tmpl_o->get_template("btn_modal", $btn_shorten['fields'], $btn_shorten);
$fleet_laps = array();
for ($i = 1; $i <= $_SESSION["e_$eventid"]['rc_numfleets']; $i++)
{
    $fleet_laps[$i] = array(
        "name"    => $_SESSION["e_$eventid"]["fl_$i"]["name"],
        "shlaps"  => $_SESSION["e_$eventid"]["fl_$i"]['currentlap'] + 1,
        "maxlaps" => $_SESSION["e_$eventid"]["fl_$i"]['maxlap']
    );
}
$mdl_shorten['fields']['body'] = $tmpl_o->get_template("fm_timer_setlaps", array(), array("mode"=>"shorten", "fleets" => $fleet_laps));
$rbufr.= $tmpl_o->get_template("modal", $mdl_shorten['fields'], $mdl_shorten);

// set laps
$rbufr.= $tmpl_o->get_template("btn_modal", $btn_setlaps['fields'], $btn_setlaps);
$mdl_setlaps['fields']['body'] = $tmpl_o->get_template("fm_timer_setlaps", array(), array("mode"=>"set", "fleets" => $fleet_laps));
$rbufr.= $tmpl_o->get_template("modal", $mdl_setlaps['fields'], $mdl_setlaps);

$rbufr.= "<hr>";

//// quick timer option
//$rbufr .= $tmpl_o->get_template("btn_modal", $btn_quicktime['fields], $btn_quicktime);
//$rbufr .= $tmpl_o->get_template("modal", $mdl_quicktime['fields], $btn_quicktime);
//
//// bunch timer option
//$rbufr .= $tmpl_o->get_template("btn_modal", $btn_bunch['fields], $btn_bunch);
//$rbufr .= $tmpl_o->get_template("modal", $mdl_bunch['fields], $mdl_bunch);

// mode button
//$toggle_fields = array(
//    "size"        => "lg",
//    "off-style"   => "default",
//    "on-style"    => "warning",
//    "left-label"  => "Tabbed",
//    "left-link"   => "timer_pg.php?eventid=$eventid&mode=tabbed",
//    "right-label" => "List",
//    "right-link"  => "timer_pg.php?eventid=$eventid&mode=list"
//);
//$_SESSION['timer_options']['mode'] == "tabbed" ? $toggle_fields['on'] = "left" : $toggle_fields['on'] = "right";

// ----- render page -------------------------------------------------------------------------
$db_o->db_disconnect();

$fields = array(
    "title"      => "racebox",
    "theme"      => $_SESSION['racebox_theme'],
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => $lbufr,
    "l_mid"      => "",
    "l_bot"      => $lbufr_bot,
    "r_top"      => "<div class=\"margin-top-40\">".$rbufr."</div>",
    "r_mid"      => "",
    "r_bot"      => "", //$tmpl_o->get_template("toggle_button", array(), $toggle_fields),
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

// ----- page specific functions ---------------------------------------------------------------
function problem_check($eventid)
{
    global $race_o;

    $problems = array(
       "timer"   => false,
       "laps"    => false,
       "entries" => false
    );
    
    // timer not started
    if ($_SESSION["e_$eventid"]['timerstart'] == 0)   
        { $problems["timer"] = true; }
    
    // laps not set
    if (check_lap_status($eventid) == 0)
        { $problems["laps"] = true; }
    
    // entries loaded
    $entries = $race_o->race_entry_counts();
    if ($entries[0] == 0)                                
        { $problems["entries"] = true; }
        
    return $problems;
}

function check_lap_status ($eventid)
{
    $laps_set = 0;
    for ($i=1; $i<=$_SESSION["e_$eventid"]['rc_numfleets']; $i++)
    {
        if ($_SESSION["e_$eventid"]["fl_$i"]['maxlap'] > 0) { $laps_set++; }            
    }  
    return $laps_set;
}


