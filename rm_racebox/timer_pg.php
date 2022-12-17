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
//echo "<pre><br><br><br><br>".print_r($_SESSION["e_$eventid"],true)."</pre>";

// check if display mode has changed - reset session variable if necessary
$display_mode = u_checkarg("mode", "setnotnull", "");
if ($display_mode) { $_SESSION['timer_options']['mode'] = $display_mode; }
if (empty($_SESSION['timer_options']['mode']))  { $_SESSION['timer_options']['mode'] = "tabbed"; }

// check if display view has changed - reset session variable if necessary
$display_view = u_checkarg("view", "setnotnull", "");
if ($display_view) { $_SESSION['timer_options']['view'] = $display_view; }
if (empty($_SESSION['timer_options']['view']))
{
    $_SESSION["e_$eventid"]['pursuit'] ? $_SESSION['timer_options']['view'] = "sailnum_p" : $_SESSION['timer_options']['view'] = "sailnum";
}

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/race_class.php");
require_once ("{$loc}/common/classes/bunch_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// app includes
require_once ("./include/rm_racebox_lib.php");

// templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php",
                             "./templates/timer_tm.php", "./templates/pursuit_tm.php"));

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
$numfleets = $_SESSION["e_$eventid"]['rc_numfleets'];
$fleet_data = array();
for ($fleetnum=1; $fleetnum<=$numfleets; $fleetnum++)
{
    $fleet_data["$fleetnum"] = $_SESSION["e_$eventid"]["fl_$fleetnum"];
}

// ----- navbar -----------------------------------------------------------------------------
$fields = array("eventid" => $eventid, "brand" => "raceBox: {$_SESSION["e_$eventid"]['ev_label']}", "club" => $_SESSION['clubcode']);
$params = array("page" => $page, "current_view" => $_SESSION['timer_options']['mode'],
                "pursuit" => $_SESSION["e_$eventid"]['pursuit'], "links" => $_SESSION['clublink'], "num_reminders" => $_SESSION["e_$eventid"]['num_reminders']);
$nbufr = $tmpl_o->get_template("racebox_navbar", $fields, $params);

// ----- left hand panel --------------------------------------------------------------------
$lbufr = u_growlProcess($eventid, $page);
$lbufr_bot = "";
$bunch_display = true;

$problems = problem_check($eventid);    // check problems preventing timing (timer not started, no entries, laps not set)
if (in_array(true, $problems, true))
{
    $lbufr.= "<pre>".print_r($_SESSION["e_$eventid"],true)."</pre>";
    $lbufr.= $tmpl_o->get_template("problems", array("eventid" => $eventid), $problems);
    $bunch_display = false;
}
else
{
    if ($_SESSION["e_$eventid"]['pursuit'])                        // pursuit race
    {
        // display boats for adding finish position
        $lbufr.= display_boats_pursuit($eventid, $_SESSION['timer_options']['view']);
    }
    else                                                           // class, handicap, average lap race
    {
        // display boats for timing in tabbed or list format
        $lbufr.= display_boats($eventid, $_SESSION['timer_options']['mode'], $_SESSION['timer_options']['view'], $mdl_editlap['fields']);
    }


//    // display boats as defined by display mode
//    if ($_SESSION['timer_options']['mode'] == "tabbed")
//    {
//        $rs_race = $race_o->race_gettimings($_SESSION['timer_options']['listorder']);
//        $lbufr.= $tmpl_o->get_template("timer_tabs", array(),
//              array("eventid" => $eventid, "num-fleets" => $_SESSION["e_$eventid"]['rc_numfleets'], "timings" => $rs_race));
//
//        // add modals
//        $lbufr.= $tmpl_o->get_template("modal", $mdl_editlap['fields'], $mdl_editlap);
//    }
//    elseif ($_SESSION['timer_options']['mode'] == "list")
//    {
//        $rs_race = $race_o->race_gettimings($_SESSION['timer_options']['view']."-list");
//        if ($_SESSION['timer_options']['view'] == "fleet")
//        {
//            $out = array();
//            foreach ($rs_race as $entry)
//            {
//                $out[$entry['fleet']][] = $entry;
//            }
//        }
//        elseif ($_SESSION['timer_options']['view'] == "class")
//        {
//            $out = array();
//            foreach ($rs_race as $entry)
//            {
//                $out[$entry['class']][] = $entry;
//            }
//        }
//        else
//        {
//            $out = array();
//            foreach ($rs_race as $entry)
//            {
//
//                if (ctype_digit($entry['sailnum'][0]))    // if first char of sailnumber is number - use it for group index
//                {
//                    $i = $entry['sailnum'][0];
//                }
//                else                                      // first char is not a number - use group 10
//                {
//                    $i = 10;
//                }
//                $out[$i][] = $entry;
//            }
//        }
//
//        //echo "<pre>".print_r($rs_race,true)."</pre>";
//        $lbufr.= $tmpl_o->get_template("timer_list", array(),
//            array("eventid" => $eventid, "view" => $_SESSION['timer_options']['view'], "timings" => $out));
//
//        // no modals to add
//    }

}

// ----- right hand panel --------------------------------------------------------------------
$rbufr = "";



if (!$_SESSION["e_$eventid"]['pursuit'])
{
    // undo
    $btn_undo['fields']['link'] = "timer_sc.php?eventid=$eventid&pagestate=undo";
    $rbufr.= $tmpl_o->get_template("btn_link", $btn_undo['fields'], $btn_undo);

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

    // Undo Shorten button/modal
    $rbufr .= $tmpl_o->get_template("btn_modal", $btn_undoshorten['fields'], $btn_undoshorten);

    $fleet_data = array();
    for ($i = 1; $i <= $numfleets; $i++)
    {
        $fleet_data["$i"] = $_SESSION["e_$eventid"]["fl_$i"];
    }
    $mdl_undoshorten['fields']['body'] = $tmpl_o->get_template("fm_timer_undoshorten", $mdl_undoshorten['fields'], array("fleet-data" => $fleet_data));
    $rbufr.= $tmpl_o->get_template("modal", $mdl_undoshorten['fields'], $mdl_undoshorten);

}
else    // display finish edit box for pursuit
{
    $rbufr.=<<<EOT
    <div class="panel panel-success margin-top-40">
        <div class="panel-heading"><h4 class="panel-title">Record Boat Finish ...</h4></div>
        <div class="panel-body">pursuit finish form</div>
    </div>
EOT;
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
    "r_top"      => $rbufr,
    "r_mid"      => "",
    "r_bot"      => "",
    "footer"     => "",
    "body_attr"  => ""
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

function display_boats_pursuit($eventid, $display_view)
{
    global $race_o, $tmpl_o;

    $htm = "";

    $rs_race = $race_o->race_gettimings($display_view."-list");
    if ($display_view == "result_p")
    {
        //get no. to be included in each row
        $num_entry = count($rs_race);
        $num_rows = $num_entry + (5 - fmod($num_entry,5));

        // organised by result order
        $out = array();
        $i = 0;
        $group = 1;
        foreach ($rs_race as $entry)
        {
            $i++;
            $out[$group][] = $entry;
            if ($i >= $num_rows)
            {
                $i = 0;
                $group++;
            }
        }
    }
    elseif ($display_view == "finish_p")                              // organised by finish line + separate column for non-finishers
    {
        $out = array();
        foreach ($rs_race as $entry) {
            (empty($entry['f_line']) or $entry['f_line'] > 6) ? $line = 6 : $line = $entry['f_line'];
            $out["$line"][] = $entry;
        }
    }
    elseif ($display_view == "class_p")                               // organised by classname
    {
        $rs_class_counts = $race_o->count_groups("class", "count", 9);
        echo "<pre>".print_r($rs_class_counts,true)."</pre>";

        $out = array();
        foreach ($rs_race as $entry)
        {
            if (array_key_exists($entry['class'], $rs_class_counts))
            {
                
            }

            $out[$entry['class']][] = $entry;
        }
    }
    else                                                            // default view is sailnum_p
    {
        // organised by sailnumber 1,2,3 etc + one non-numeric
        $out = array();
        foreach ($rs_race as $entry) {
            // if first char of sailnumber is number - use it for group index (first char is not a number - use group 10)
            ctype_digit($entry['sailnum'][0]) ? $i = $entry['sailnum'][0] : $i = 10 ;
            $out[$i][] = $entry;
        }
    }

    //echo "<pre>".print_r($rs_race,true)."</pre>";
    $htm.= $tmpl_o->get_template("timer_list_pursuit", array(), array("eventid" => $eventid, "view" => $display_view, "timings" => $out));

    // no modals to add

    return $htm;
}

function display_boats($eventid, $display_mode, $display_view, $mdl_editlap)
{
    global $race_o, $tmpl_o;

    $htm = "";

    // display boats as defined by display mode
    if ($display_mode == "tabbed")
    {
        $rs_race = $race_o->race_gettimings($_SESSION['timer_options']['listorder']);
        $htm.= $tmpl_o->get_template("timer_tabs", array(),
            array("eventid" => $eventid, "num-fleets" => $_SESSION["e_$eventid"]['rc_numfleets'], "timings" => $rs_race));

        // add modals
        $htm.= $tmpl_o->get_template("modal", $mdl_editlap['fields'], $mdl_editlap);
    }
    elseif ($display_mode == "list")
    {
        $rs_race = $race_o->race_gettimings($display_view."-list");
        if ($display_view == "fleet") {
            $out = array();
            foreach ($rs_race as $entry) {
                $out[$entry['fleet']][] = $entry;
            }
        } elseif ($display_view == "class") {
            $out = array();
            foreach ($rs_race as $entry) {
                $out[$entry['class']][] = $entry;
            }
        } else {
            $out = array();
            foreach ($rs_race as $entry) {

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
        $htm.= $tmpl_o->get_template("timer_list", array(),  array("eventid" => $eventid, "view" => $display_view, "timings" => $out));

        // no modals to add
    }

    return $htm;
}


