<?php
/**
 * results_pg.php
 * 
 * This page allows the user to view, edit and publish the race results.  It also 
 * allows bulk import of results from third party data collection systems.
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 * @param string $eventid
 * 
 */

$loc        = "..";
$page       = "results";
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_racebox_lib.php");

$eventid = u_checkarg("eventid", "checkintnotzero","");
if (!$eventid)
{
    u_exitnicely($scriptname, 0,"$page page - event id record [{$_REQUEST['eventid']}] not defined",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// start session
session_id('sess-rmracebox');
session_start();

// page initialisation
u_initpagestart($eventid, $page, true);
//echo "<pre><br><br><br><br>".print_r($_SESSION["e_$eventid"],true)."</pre>";

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/race_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/event_class.php");

// templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/results_tm.php"));

// page controls
include ("./templates/growls.php");

// database connection
$db_o = new DB;
$race_o = new RACE($db_o, $eventid);

// set event name
$eventname = u_conv_eventname($_SESSION["e_$eventid"]['ev_name']);

// set number of fleets
$numfleets = $_SESSION["e_$eventid"]['rc_numfleets'];

// get results into 2D array - covering all fleets
$rs_data = array();
foreach ($race_o->race_getresults() as $result)
{
    $rs_data[$result['fleet']][] = $result;
}

// check if race is running or has been run
$total_entries = 0;
$event_run = false;
if ($_SESSION["e_$eventid"]['ev_status'] == "running" OR $_SESSION["e_$eventid"]['ev_status'] == "sailed" OR
    $_SESSION["e_$eventid"]['ev_status'] == "completed") { $event_run = true; }

// ---- Recalculate results if required ----------------------------------
$results = array("eventid" => $eventid, "num-fleets" => $numfleets);
if (!$_SESSION["e_$eventid"]['result_valid'])   // check to see if results need recalculating
{
    $warning_count = 0;
    $event_still_running = false;
    for ($i = 1; $i <= $numfleets; $i++)
    {
            $total_entries = $total_entries + $_SESSION["e_$eventid"]["fl_$i"]['entries'];

            $fleet_rs['warning'] = array();
            $fleet_rs['data'] = array();
            if (!empty($rs_data[$i]))
            {
                $fleet_rs = $race_o->race_score($eventid, $i, $_SESSION["e_$eventid"]["fl_$i"]['scoring'], $rs_data[$i] );
            }

            if (!empty($fleet_rs['warning'])) { $warning_count++; }
            $results['warning'][$i] = $fleet_rs['warning'];
            $results['entries'][$i] = $_SESSION["e_$eventid"]["fl_$i"]['entries'];
            $results['data'][$i]    = $fleet_rs['data'];

            $fleet_still_racing = $race_o->fleet_race_stillracing($i);
            if ($fleet_still_racing) { $event_still_running = true; }
    }

    if ($event_run AND $total_entries > 0)
    {
        $event_o = new EVENT($db_o);
        if ($event_still_running)
        {
            // set event status to running
            $upd = $event_o->event_updatestatus($eventid, "running");
        }
        else
        {
            // set event status to sailed
            $upd = $event_o->event_updatestatus($eventid, "sailed");
        }
    }
}

include ("./include/results_ctl.inc");

// ----- navbar -----------------------------------------------------------------------------
$fields = array("eventid" => $eventid, "brand" => "raceBox: {$_SESSION["e_$eventid"]['ev_label']}", "club" => $_SESSION['clubcode']);
$params = array("page" => $page, "pursuit" => $_SESSION["e_$eventid"]['pursuit'], "links" => $_SESSION['clublink'], "num_reminders" => $_SESSION["e_$eventid"]['num_reminders']);
$nbufr = $tmpl_o->get_template("racebox_navbar", $fields, $params);

// ----- left hand panel ---------------------------------------------------------------------
$lbufr = "";
$lbufr = u_growlProcess($eventid, $page);       // check for confirmations to present

// ---- create tabs/panels ------------------
$lbufr.= $tmpl_o->get_template("result_tabs", array(), $results);

// add modals for inline buttons
$lbufr.= $tmpl_o->get_template("modal", $mdl_edit['fields'], $mdl_edit);
$lbufr.= $tmpl_o->get_template("modal", $mdl_remove['fields'], $mdl_remove);

// ----- right hand panel ------------------------------------------------------------
$rbufr = "";

// Save Results button (only if a) we have entries, and b) the race has started)
if ($event_run AND $total_entries > 0)
{
    $rbufr.= $tmpl_o->get_template("btn_modal", $btn_publish['fields'], $btn_publish)."<hr>";
}

// retirements button
if ($_SESSION["e_$eventid"]['ev_entry'] != "ood")
{
    $entry_o = new ENTRY($db_o, $eventid);

    if ($_SESSION["e_$eventid"]['ev_entry'] == "signon-retire")
    {
        $num_retirements = $entry_o->count_signons("retirements");
        if ($num_retirements > 0)
        {
            $text_style = "text-primary";
            $template = "btn_link_blink";
            $btn_loadret['fields']['style'] = "warning";
        }
        else
        {
            $text_style = "text-info";
            $template = "btn_link";
        }
        $btn_loadret['fields']['label'] = <<<EOT
            Load Retirements<br>
            <small>
                <span class='$text_style' style='padding-left: 30px'><b>$num_retirements waiting</b></span>
            </small>
EOT;
        $rbufr.= $tmpl_o->get_template("$template", $btn_loadret['fields'], $btn_loadret)."<hr>";
    }
}


// Change Finish Lap button  (if not a pursuit race)
if (!$_SESSION["e_$eventid"]['pursuit'])
{
    $rbufr.= $tmpl_o->get_template("btn_modal", $btn_changefinish['fields'], $btn_changefinish);
}

// Send Message button
$rbufr.= $tmpl_o->get_template("btn_modal", $btn_message['fields'], $btn_message);


// modal code
if (!$_SESSION["e_$eventid"]['pursuit'])
{
    // change finish lap modal
    $fleet_data = array();
    for ($i = 1; $i <= $numfleets; $i++)
    {
        $fleet_data["$i"] = $_SESSION["e_$eventid"]["fl_$i"];
    }
    $mdl_changefinish['fields']['body'] = $tmpl_o->get_template("fm_change_finish", $mdl_changefinish['fields'], array("fleet-data" => $fleet_data));
    $rbufr.= $tmpl_o->get_template("modal", $mdl_changefinish['fields'], $mdl_changefinish);          // finish lap modal
}

// send message modal
$mdl_message['fields']['body'] = $tmpl_o->get_template("fm_race_message", array());
$rbufr.= $tmpl_o->get_template("modal", $mdl_message['fields'], $mdl_message);                    // send message modal

// results modal
$rbufr.= $tmpl_o->get_template("modal", $mdl_publish['fields'], $mdl_publish);                    // publish results modal


// disconnect database
$db_o->db_disconnect();

// ----- render page -----------------------------------------------------------------------------
$fields = array(
    "title"      => $_SESSION["e_$eventid"]['ev_label'],
    "theme"      => $_SESSION['racebox_theme'],
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => $lbufr,
    "l_mid"      => "",
    "l_bot"      => "",
    "r_top"      => $rbufr,
    "r_mid"      => "",
    "r_bot"      => "",
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


/* ---- functions ------- */

function get_lap_details($laptimes)
{
    // lap timings button
    $laps = explode(",", $laptimes);
    if (!empty($laps))
    {
        $prev_time = 0;
        $prev_delta = 0;
        $status = "";
        $bufr = "";
        $i = 0;
        foreach ($laps as $key=>$lap)
        {
            $i++;
            $delta_secs = $lap-$prev_time;
            $delta_time = u_conv_secstotime($delta_secs);
            $lap_clock  = u_conv_secstotime($lap);

            // assess status
            $status = "default";
            if ($i != 1)
            {
                if (abs($prev_delta - $delta_secs)/$prev_delta > 0.5)
                {
                    $status = "danger";
                }
                elseif (abs($prev_delta - $delta_secs)/$prev_delta > 0.2)
                {
                    $status = "warning";
                }
            }
            $prev_time = $lap;
            $prev_delta = $delta_secs;
            $bufr.= "<tr class='$status text-$status'><td>lap $i</td><td>$delta_time</td><td>$lap_clock</td></tr>";
        }
        $lapbufr = <<<EOT
        <div class='alert alert-warning alert-dismissable' role='alert'>
        These are the individual and cumulative lap times for this competitor.
        <br><br>Lap times that are significantly different from previous lap times are highlighted.  You may want to check these against other competitors in the race.
        <br><br>If you want to edit an individual lap time use the form on the Timer page. <br>
        </div>
        <table class='table table-condensed' width='80%' style='font-size: 1.0em;'>
            <tbody>
                <tr style='font-weight: bold'><td>Lap</td><td>Lap Time</td><td>Cumulative Elapsed Time</td></tr>
                $bufr
            </tbody>
        </table>
EOT;
    }
    else
    {
        $lapbufr = "<p class='text-center text-danger'> No laps recorded for this competitor </p>";
    }
    return $lapbufr;
}