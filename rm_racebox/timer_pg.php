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

u_initpagestart($_REQUEST['eventid'], $page, $_REQUEST['menu']);   // starts session and sets error reporting
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");

// check we have request id - if not stop with system error
if (empty($_REQUEST['eventid']) or !is_numeric($_REQUEST['eventid'])) 
{
    u_exitnicely($scriptname, "not defined", $lang['err']['sys002'], "event id is not defined");  
    exit();
}

$eventid = $_REQUEST['eventid'];
if (!empty($_REQUEST['mode'])) {$_SESSION['timer_options']['mode'] = $_REQUEST['mode']; }
if (empty($_SESSION['timer_options']['mode'])) { $_SESSION['timer_options']['mode'] = "tabbed"; }

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
//require_once ("{$loc}/common/classes/event_class.php");      // <-- remove if not required
require_once ("{$loc}/common/classes/race_class.php");

// templates
$tmpl_o = new TEMPLATE(array("../templates/general_tm.php",
    "../templates/racebox/layouts_tm.php",
    "../templates/racebox/navbar_tm.php",
    "../templates/racebox/timer_tm.php"));

// database connection
$db_o   = new DB;
$race_o = new RACE($db_o, $eventid);

// page controls
include ("./include/timer_ctl.inc");

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
$fields = array(
    "eventid"  => $eventid,
    "brand"    => "raceBox: {$_SESSION["e_$eventid"]['ev_sname']}",
    "page"     => $page,
    "pursuit"  => $_SESSION["e_$eventid"]['pursuit'],
);
$nbufr = $tmpl_o->get_template("racebox_navbar", $fields);


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
    if ($_SESSION['timer_options']['mode'] == "tabbed" or empty($_SESSION['timer_options']['mode']))
    {
        $rs_race = $race_o->race_gettimings(true, 0, 0);
        $lbufr.= $tmpl_o->get_template("timer_tabs",
              array("eventid" => $eventid, "num-fleets" => $_SESSION["e_$eventid"]['rc_numfleets']), $rs_race);
        // add modals
        $lbufr.= $tmpl_o->get_template("modal", $mdl_editlap);
    }
    elseif ($_SESSION['timer_options']['mode'] == "list")
    {
        $lbufr.= $tmpl_o->get_template("under_construction", array("title" => "Timer: List option", "info" => "We are still working on this"));
    }
    else
    {
        $lbufr.= $tmpl_o->get_template("under_construction", array("title" => "Timer: option", "info" => "Never heard of it"));
    }

    if (!$_SESSION["e_$eventid"]['pursuit'])
    {
        $data = array(
            "lapstatus" => check_lap_status($eventid),
            "fleet-data" => $fleet_data,
        );
        $mdl_setlaps['body'] = $tmpl_o->get_template("fm_timer_setlaps", array(), $data);
        $lbufr_bot = $tmpl_o->get_template("modal", $mdl_setlaps);
    }
}


// ----- right hand panel --------------------------------------------------------------------
$rbufr = "";

// undo
$rbufr.= "<div class=\"margin-top-40\">";
$btn_undo['link'] = "timer_sc.php?eventid=$eventid&pagestate=undo";
$rbufr.= $tmpl_o->get_template("btn_link", $btn_undo);

// shorten all fleets
$btn_shorten['link'] = "timer_sc.php?eventid=$eventid&pagestate=shorten&fleet=all";
$rbufr.= $tmpl_o->get_template("btn_link", $btn_shorten);
$rbufr.= "<hr>";

//// quick timer option
//$rbufr .= $tmpl_o->get_template("btn_modal", $btn_quicktime);
//$rbufr .= $tmpl_o->get_template("modal", $mdl_quicktime);
//
//// bunch timer option
//$rbufr .= $tmpl_o->get_template("btn_modal", $btn_bunch);
//$rbufr .= $tmpl_o->get_template("modal", $mdl_bunch);

$rbufr.= "</div>";

$rbufr_bot = mode_button($eventid);
// ----- render page -------------------------------------------------------------------------
$db_o->db_disconnect();

$fields = array(
    "title"      => "racebox",
    "loc"        => $loc,
    "stylesheet" => "$loc/style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => $lbufr,
    "l_mid"      => "",
    "l_bot"      => $lbufr_bot,
    "r_top"      => $rbufr,
    "r_mid"      => "",
    "r_bot"      => $rbufr_bot,
    "footer"     => "",
    "page"       => $page,
    "refresh"    => 0,
    "l_width"    => 10,
    "forms"      => true,
    "tables"     => true,
    "body_attr"  => "onload=\"startTime()\""
);
echo $tmpl_o->get_template("two_col_page", $fields);

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

function mode_button($eventid)
{
    $tabbed = "btn-danger";
    $list = "btn-default";
    if ($_SESSION['timer_options']['mode'] == "list")
    {
        $tabbed = "btn-default";
        $list = "btn-danger";
    }

    $bufr = <<<EOT
    <div class="btn-group btn-toggle btn-md pull-left">
        <a class="btn btn-sm $tabbed" style="width: 100px; font-weight: bold" href="timer_pg.php?eventid=$eventid&mode=tabbed">
            tabbed
        </a>
        <a class="btn btn-sm $list" style="width: 100px; font-weight: bold" href="timer_pg.php?eventid=$eventid&mode=list">
            list
        </a>
    </div>
EOT;
    return $bufr;
}