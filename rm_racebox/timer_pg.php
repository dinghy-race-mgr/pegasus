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
    u_exitnicely($scriptname, 0, "$page page - the requested event has an missing/invalid record identifier [{$_REQUEST['eventid']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// start session
session_id('sess-rmracebox');
session_start();

// page initialisation
u_initpagestart($eventid, $page, true);

// check if display mode has changed - reset session variable if necessary
$display_mode = u_checkarg("mode", "setnotnull", "");
if ($display_mode) { $_SESSION['timer_options']['mode'] = $display_mode; }
if (empty($_SESSION['timer_options']['mode']))  { $_SESSION['timer_options']['mode'] = "tabbed"; }

// check if display view has changed - reset session variable if necessary
$display_view = u_checkarg("view", "setnotnull", "");
if ($display_view) { $_SESSION['timer_options']['view'] = $display_view; }
if (empty($_SESSION['timer_options']['view']))  { $_SESSION['timer_options']['view'] = "sailnum"; }

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/race_class.php");
require_once ("{$loc}/common/classes/bunch_class.php");
require_once ("{$loc}/common/classes/template_class.php");

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

// get fleet data
$fleet_data = array();
for ($fleetnum=1; $fleetnum<=$_SESSION["e_$eventid"]['rc_numfleets']; $fleetnum++)
{
    $fleet_data["$fleetnum"] = $_SESSION["e_$eventid"]["fl_$fleetnum"];
}

// ----- navbar -----------------------------------------------------------------------------
$fields = array("eventid" => $eventid, "brand" => "raceBox: {$_SESSION["e_$eventid"]['ev_label']}", "club" => $_SESSION['clubcode']);
$params = array("page" => $page, "current_view" => $_SESSION['timer_options']['mode'],
                "pursuit" => $_SESSION["e_$eventid"]['pursuit'], "links" => $_SESSION['clublink']);
$nbufr = $tmpl_o->get_template("racebox_navbar", $fields, $params);

// ----- left hand panel --------------------------------------------------------------------
$lbufr = u_growlProcess($eventid, $page);
$lbufr_bot = "";
$bunch_display = true;

$problems = problem_check($eventid);    // check problems preventing timing
if (in_array(true, $problems, true))
{
    $lbufr.= $tmpl_o->get_template("problems", array("eventid" => $eventid), $problems);
    $bunch_display = false;
}
else
{
    // display boats as defined by display mode
    if ($_SESSION['timer_options']['mode'] == "tabbed")
    {
        $rs_race = $race_o->race_gettimings($_SESSION['timer_options']['listorder']);
        $lbufr.= $tmpl_o->get_template("timer_tabs", array(),
              array("eventid" => $eventid, "num-fleets" => $_SESSION["e_$eventid"]['rc_numfleets'], "timings" => $rs_race));

        // add modals
        $lbufr.= $tmpl_o->get_template("modal", $mdl_editlap['fields'], $mdl_editlap);
    }
    elseif ($_SESSION['timer_options']['mode'] == "list")
    {
        //echo "<pre><br><br><br>OPTION: {$_SESSION['timer_options']['view']}</pre>";
        $rs_race = $race_o->race_gettimings($_SESSION['timer_options']['view']."-list");
        if ($_SESSION['timer_options']['view'] == "fleet")
        {
            $out = array();
            foreach ($rs_race as $entry)
            {
                $out[$entry['fleet']][] = $entry;
            }
        }
        elseif ($_SESSION['timer_options']['view'] == "class")
        {
            $out = array();
            foreach ($rs_race as $entry)
            {
                $out[$entry['class']][] = $entry;
            }
        }
        else
        {
            $out = array();
            foreach ($rs_race as $entry)
            {

                if (ctype_digit($entry['sailnum'][0]))    // if first char of sailnumber is number - use it for group index
                {
                    $i = $entry['sailnum'][0];
                }
                else                                      // first char is not a number - use group 10
                {
                    $i = 10;
                }
                $out[$i][] = $entry;
            }
        }

        //echo "<pre>".print_r($rs_race,true)."</pre>";
        $lbufr.= $tmpl_o->get_template("timer_list", array(), 
            array("eventid" => $eventid, "view" => $_SESSION['timer_options']['view'], "timings" => $out));

        // add modals
    }


}

// ----- right hand panel --------------------------------------------------------------------
$rbufr = "";

// undo
$btn_undo['fields']['link'] = "timer_sc.php?eventid=$eventid&pagestate=undo";
$rbufr.= $tmpl_o->get_template("btn_link", $btn_undo['fields'], $btn_undo);

if (!$_SESSION["e_$eventid"]['pursuit'])
{
    // shorten all and reset laps buttons
    $fleet_data = array();
    for ($i = 1; $i <= $_SESSION["e_$eventid"]['rc_numfleets']; $i++)
    {
        $fleet_data["$i"] = $_SESSION["e_$eventid"]["fl_$i"];
    }

    // shorten all fleets button/modal
    $rbufr.= $tmpl_o->get_template("btn_modal", $btn_shorten['fields'], $btn_shorten);
    $mdl_shorten['fields']['body'] = $tmpl_o->get_template("fm_timer_shortenall", array(), array("fleet-data" => $fleet_data));
    //if ($_SESSION['shorten_possible']) { $mdl_shorten['submit'] = false; }
    $rbufr.= $tmpl_o->get_template("modal", $mdl_shorten['fields'], $mdl_shorten);

    // reset laps button/modal
//    $rbufr.= $tmpl_o->get_template("btn_modal", $btn_resetlaps['fields'], $btn_resetlaps);
//    $mdl_resetlaps['fields']['body'] = $tmpl_o->get_template("fm_timer_resetlaps", array(), array("fleet-data" => $fleet_data));
//    $rbufr.= $tmpl_o->get_template("modal", $mdl_resetlaps['fields'], $mdl_resetlaps);
}

$rbufr.= "<hr>";

// bunch display
if ($_SESSION['racebox_timer_bunch'] and $bunch_display)
{
    if (!array_key_exists("bunch", $_SESSION["e_$eventid"])) { $_SESSION["e_$eventid"]['bunch'] = array(); };
    $bunch_o = new BUNCH($eventid, "timer_sc.php", $_SESSION["e_$eventid"]['bunch']);

    $bunch_o->is_empty() ? $bunch_htm = "<div style='text-align: center'><i> --- empty ---</i></div>" : $bunch_htm = $bunch_o->render();

    $rbufr.=<<<EOT
    <div class="panel panel-info">
        <div class="panel-heading"><h4 class="panel-title">Bunch ...</h4></div>
        <div class="panel-body">$bunch_htm</div>
    </div>
EOT;

}

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
    "title"      => $_SESSION["e_$eventid"]['ev_label'],
    "theme"      => $_SESSION['racebox_theme'],
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => $lbufr,
    "l_mid"      => "",
    "l_bot"      => $lbufr_bot,
    "r_top"      => $rbufr, //"<div class=\"margin-top-40\">".$rbufr."</div>",
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


