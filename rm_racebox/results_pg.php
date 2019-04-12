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

u_initpagestart($_REQUEST['eventid'], $page, $_REQUEST['menu']);   // starts session and sets error reporting

// initialising language   
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");

// check we have request id - if not stop with system error
if (empty($_REQUEST['eventid']) or !is_numeric($_REQUEST['eventid'])) 
{
    u_exitnicely($scriptname, "not defined", $lang['err']['sys002'], "event id is not defined");
    exit();
}

// classes  (remove classes not required)
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/race_class.php");
require_once ("{$loc}/common/classes/entry_class.php");

// templates
$tmpl_o = new TEMPLATE(array("../templates/general_tm.php",
    "../templates/racebox/layouts_tm.php",
    "../templates/racebox/navbar_tm.php",
    "../templates/racebox/results_tm.php"));

// ---- set script data
$eventid   = $_REQUEST['eventid'];
$numfleets = $_SESSION["e_$eventid"]['rc_numfleets'];

include ("./include/results_ctl.inc");
include ("$loc/templates/racebox/growls.php");

// database connection
$db_o = new DB;
$race_o = new RACE($db_o, $eventid);

// get results into 2D array - covering all fleets
$rs_data = array();
foreach ($race_o->race_getresults() as $result)
{
    $rs_data[$result['fleet']][] = $result;                    // raw results data
}

// ---- Recalculate results if required ----------------------------------
$results = array();
if (!$_SESSION["e_$eventid"]['result_valid'])   // check to see if results need recalculating
{
    for ($i = 1; $i <= $numfleets; $i++)
    {
        $fleet_rs['warning'] = array();
        $fleet_rs['data'] = array();
        if (!empty($rs_data[$i]))
        {
            $fleet_rs = $race_o->race_score($_SESSION["e_$eventid"]["fl_$i"]['scoring'], $rs_data[$i] );
        }
        $results['warning'][$i] = $fleet_rs['warning'];
        $results['data'][$i]    = $fleet_rs['data'];
    }
    count($results['warning'])>0 ? $growl = $g_results_recalc_fail : $growl = $g_results_recalc_success ;
    u_growlset($eventid, $page, $growl);
}

// ----- navbar -----------------------------------------------------------------------------
$fields = array(
    "eventid"  => $eventid,
    "brand"    => "raceBox RESULTS - {$_SESSION["e_$eventid"]['ev_sname']}",
    "page"     => $page,
    "pursuit"  => $_SESSION["e_$eventid"]['pursuit'],
);
$nbufr = $tmpl_o->get_template("racebox_navbar", $fields);

// ----- left hand panel ---------------------------------------------------------------------
$lbufr = "";
$lbufr = u_growlProcess($eventid, $page);       // check for confirmations to present

// ---- create tabs/panels ------------------
//echo <<<EOT
//   <div style="min-height: 40px">&nbsp;</div>
//EOT;
//echo "<pre>".print_r($results,true)."</pre><br>";
$lbufr.= $tmpl_o->get_template("result_tabs", array("eventid" => $eventid, "num-fleets" => $numfleets), $results);

// add modals for inline buttons
$fields = array( "entryid" => "", "allocation" => $_SESSION['points_allocation']);
$data = array("resultcodes" => $_SESSION['resultcodes']);
$mdl_edit['body'] = $tmpl_o->get_template("fm_edit_result", $fields, $data);
$lbufr.= $tmpl_o->get_template("modal", $mdl_edit);
$lbufr.= $tmpl_o->get_template("modal", $mdl_detail);
$lbufr.= $tmpl_o->get_template("modal", $mdl_remove);


// ----- right hand panel ------------------------------------------------------------
$rbufr = "";

// retirements button
if ($_SESSION["e_$eventid"]['ev_entry'] != "ood")
{
    $entry_o = new ENTRY($db_o, $eventid);

    if ($_SESSION["e_$eventid"]['ev_entry'] == "signon-retire")
    {
        $num_retirements = $entry_o->count_signons("retirements");
        if ($num_retirements > 0)
        {
            $btn_loadret['style'] = "warning";
            $btn_loadret['label'] = "+ Retirements - ";
        }
        $rbufr.= $tmpl_o->get_template("btn_link", $btn_loadret);
        $rbufr.= "<hr>";
    }
    elseif ($_SESSION["e_$eventid"]['ev_entry'] == "signon-declare")
    {
        $num_declarations = $entry_o->count_signons("declarations");
        if ($num_declarations > 0)
        {
            $btn_loaddec['style'] = "warning";
            $btn_loaddec['label'] = "+ Declarations - ";
        }
        $rbufr.= $tmpl_o->get_template("btn_link", $btn_loaddec);
        $rbufr.= "<hr>";
    }
}

// function buttons
$rbufr.= $tmpl_o->get_template("btn_modal", $btn_changefinish);      // change finish lap button
$rbufr.= $tmpl_o->get_template("btn_modal", $btn_message);           // send message button
$rbufr.= $tmpl_o->get_template("btn_modal", $btn_publish);           // publish results button

// modal markup
$mdl_changefinish['body'] = $tmpl_o->get_template("fm_change_finish", array("num-fleets" => $numfleets), $_SESSION["e_$eventid"]);
$rbufr.= $tmpl_o->get_template("modal", $mdl_changefinish);          // finish lap modal
$mdl_message['body'] = $tmpl_o->get_template("fm_race_message", array());
$rbufr.= $tmpl_o->get_template("modal", $mdl_message);               // send message modal
$rbufr.= $tmpl_o->get_template("modal", $mdl_publish);               // publish results modal

// disconnect database
$db_o->db_disconnect();

// ----- render page -----------------------------------------------------------------------------
$fields = array(
    "title"      => "racebox",
    "loc"        => $loc,
    "stylesheet" => "$loc/style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => $lbufr,
    "l_mid"      => "",
    "l_bot"      => "",
    "r_top"      => $rbufr,
    "r_mid"      => "",
    "r_bot"      => "",
    "footer"     => "",
    "page"       => $page,
    "refresh"    => 0,
    "l_width"    => 10,
    "forms"      => true,
    "tables"     => false,
    "body_attr"  => "onload=\"startTime()\""
);
echo $tmpl_o->get_template("two_col_page", $fields);


/* ---- functions ------- */
function get_result_row($result)
{
    global $race_o, $tmpl_o;
    global $btn_edit, $btn_detail, $btn_remove;

    $boat = $result['class']." ".$result['sailnum'];
    $competitor = u_truncatestring(rtrim($result['helm'] . "/" . $result['crew'], "/"), 25);
    $status_bufr = $race_o->entry_resultstatus($result['status'], $result['declaration']);

    $button_bufr = "";
    // edit button
    $formatted_time = gmdate("H:i:s", $result["etime"]);
    $btn_edit['data'] = " data-boat=\"$boat\"
                          data-entryid=\"{$result['id']}\"
                          data-helm=\"{$result["helm"]}\"
                          data-crew=\"{$result["crew"]}\"
                          data-sailnum=\"{$result["sailnum"]}\"
                          data-pn=\"{$result["pn"]}\"
                          data-lap=\"{$result["lap"]}\"
                          data-etime=\"$formatted_time\"
                          data-code=\"{$result["code"]}\"
                          data-penalty=\"{$result["penalty"]}\"
                          data-note=\"{$result["note"]}\"
                        ";
    $button_bufr.= $tmpl_o->get_template("badge_modal", $btn_edit);

    // lap details button
    $lapbufr = get_lap_details($result['laptimes']);
    $btn_detail['data'] = " data-entryname=\"$competitor\" data-table=\"$lapbufr\" ";
    $button_bufr.= $tmpl_o->get_template("badge_modal", $btn_detail);

    // remove button
    $btn_remove['data'] = " data-entryid=\"{$result['id']}\" data-entryname=\"$competitor\" ";
    $button_bufr.= $tmpl_o->get_template("badge_modal", $btn_remove);

    $fields = array(
        "class" => $result['class'],
        "sailnum" => $result['sailnum'],
        "competitor" => $competitor,
        "pn" => $result['pn'],
        "lap" => $result['lap'],
        "et" => u_conv_secstotime($result['etime']),
        "ct" => u_conv_secstotime($result['atime']),
        "code" => $result['code'],
        "points" => $result['points'],
        "status" => $status_bufr,
        "button" => $button_bufr,
    );
    return $fields;
}


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