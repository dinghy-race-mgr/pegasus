<?php
/**
 * dtm_duty_import
 * Updates duty information in the rm_admin programme table from the latest information in dutyman via a csv file
 *
 * arguments:
 *  pagestate   str     page option [init | submit]  default is init
 *  start date  str     start date in programme yyyy-mm-dd
 *  end date    str     end date in programme yyyy-mm-dd
 *  dryrun      str     [on | off] default is off
 *
 * CURRENT STATUS
 * Check duties in programme database against dutyman csv export, and identifies possible swaps.
 * It reports this in dryrun mode.
 *
 * Current Issues
 *      - not making swaps in database
 *      - not handling situation where dutyman has additional duties added for an event
 *      - no handling situation where programme has additional duties added - not sure it needs to (all changes should be made in dutyman)
 *      - not handling dutyman entered duties with different codes (eg lockup) - probably doesn't need to
 *      - not sure it will work when more than one change to the same duty type needs to be made
 *      - not working after dutyman combines across multiple race events on the same day
 *        (to stop multiple reminders being issued) - see below
 *
 * There are related issues with the dtm_duty_export script
 *      - not handling names with more than one first name (e.g. marie anne beard)
 *      - needs to munge multiple race events on the same day together and aggregate the duties across them so
 *        it doesn't need to be done manually in dutyman
 *
 */

$loc  = "..";
$page = "Rota Synch";
$scriptname = basename(__FILE__);
$today = date("Y-m-d");

session_start();

require_once("$loc/common/lib/util_lib.php");
require_once("$loc/common/classes/db_class.php");
require_once("{$loc}/common/classes/template_class.php");
require_once("{$loc}/common/classes/event_class.php");
require_once("{$loc}/common/classes/rota_class.php");

// set templates
$tmpl_o = new TEMPLATE(array("$loc/templates/general_tm.php","$loc/templates/utils/layouts_tm.php", "$loc/templates/utils/dutyman_tm.php"));

// initialise session if this is first call
if (!isset($_SESSION['app_init']) OR ($_SESSION['app_init'] === false))
{
    $init_status = u_initialisation("$loc/config/racemanager_cfg.php", "$loc/config/rm_utils_cfg.php", $loc, $scriptname);

    if ($init_status)
    {
        // set timezone
        if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

        // start log
        $_SESSION['syslog'] = "$loc/logs/adminlogs/".$_SESSION['syslog'];
        error_log(date('H:i:s')." -- DUTY SYNCH --------------------".PHP_EOL, 3, $_SESSION['syslog']);
    }
    else
    {
        u_exitnicely($scriptname, 0, "initialisation failure", "one or more problems with script initialisation");
    }
}

// arguments

empty($_REQUEST['pagestate']) ?  $pagestate = "init" : $pagestate = $_REQUEST['pagestate'];


$pagefields = array(
    "loc"           => $loc,
    "theme"         => "flatly_",
    "stylesheet"    => "$loc/style/rm_utils.css",
    "title"         => "Duty Synchronisation",
    "header-left"   => "raceManager",
    "header-right"  => "synchronise duty info from dutyman ...",
    "body"          => "",
    "confirm"       => "Synchronise",
    "footer-left"   => "",
    "footer-center" => "",
    "footer-right"  => "",
);

/* ------------ confirm run script page ---------------------------------------------*/

if ($_REQUEST['pagestate'] == "init")
{

    // present form to select json file for processing (general template)
    $formfields = array(
        "instructions"  => "Updates duty allocations in raceManager with information exported from dutyman in csv format.  
                       This is used to reflect duty swaps made in dutyman in the racemanager programme <br><br>
                       <b>You will need to republish the programme after it has been updated</b><br><br>
                       Using server {$_SESSION['db_host']}/{$_SESSION['db_name']}<br>",
    );
    $pagefields['body'] =  $tmpl_o->get_template("dtm_duty_import_form", $formfields, $params);

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields, $params);
}

/* ------------ submit page ---------------------------------------------*/

elseif (trim(strtolower($_REQUEST['pagestate'])) == "submit")
{


    // arguments
    if (empty($_REQUEST['start']) or empty($_REQUEST['end']))
    {
        u_exitnicely($scriptname, 0, "Argument Error", "Start and/or End date are missing");
    }
    elseif (strtotime($_REQUEST['start']) > strtotime($_REQUEST['end']))
    {
        u_exitnicely($scriptname, 0, "Argument Error", "Start date is after End date");
    }
    elseif (!strtotime($_REQUEST['start']))
    {
        u_exitnicely($scriptname, 0, "Argument Error", "Start date is not a valid date");
    }
    elseif (!strtotime($_REQUEST['end']))
    {
        u_exitnicely($scriptname, 0, "Argument Error", "End date is not a valid date");
    }
    else
    {
        $start = date("Y-m-d", strtotime($_REQUEST['start']));
        $end = date("Y-m-d", strtotime($_REQUEST['end']));
    }

    $dryrun = false;
    if (!empty($_REQUEST['dryrun']))
    {
        if (strtolower($_REQUEST['dryrun']) == "on")
        {
            $dryrun = true;
        }
    }

    $dutytype = "";
    if (!empty($_REQUEST['duty']))
    {
        $dutytype = strtolower($_REQUEST['duty']);
    }

    // read data from csv file into array
    $_FILES['dutymanfile']['tmp_name'] = "../data/dutyman/fromdutyman.csv";
    $arr = read_csv_file($_FILES['dutymanfile']['tmp_name']);
    if (!$arr)
    {
        u_exitnicely($scriptname, 0, "File Error", "Cannot read csv file [{$_FILES['dutymanfile']['tmp_name']}]");
    }
    else
    {
        // connect to database
        $db_o = new DB();
        $event_o = new EVENT($db_o);
        $rota_o = new ROTA($db_o);

        // get programme data
        $prg = $event_o->get_events_inperiod(array("active"=>"1"), $start, $end, "live", $race = false);

        $diff_report = "<pre>";
        foreach ($prg as $k=>$event)
        {
            $diff_report .= "<b>{$event['event_date']} {$event['event_name']}: </b>";

            // get dutyman duties for this event from csv array
            $keys = array();
            foreach ($arr as $i => $dtm_duty) {
                if ($dtm_duty["Duty Date"] == date("d/m/Y", strtotime($event['event_date']))
                    AND strtolower($dtm_duty["Event"]) == strtolower($event['event_name'])) {
                    $keys[] = $i;
                }
            }

            if (empty($keys)) {
                $diff_report .= "no duties in dutyman<br>";
                continue;
            }
            else {
                $dtm_duty = array();
                foreach ($keys as $key) {
                    $dtm_duty[] = $arr[$key];
                }
                if (!empty($dtm_duty)) { $dtm_duty_s = serialise_duty($dtm_duty, "Member Name", "Duty Type"); }
                if ($dryrun) { $diff_report.= "<br>DUTYMAN : " . print_r($dtm_duty_s, true); }
            }


            // get duties for this event from programme
            $prg_duty = $rota_o->get_event_duties($event['id']);
            if (!empty($prg_duty)) { $prg_duty_s = serialise_duty($prg_duty, "person", "dutyname"); }
            if ($dryrun) { $diff_report.= "<br>PROGRAMME: " . print_r($prg_duty_s, true); }


            $arr1 = array_diff($dtm_duty_s, $prg_duty_s);  // get differences from dutyman to programme duties
            $arr2 = array_diff($prg_duty_s, $dtm_duty_s);  // get differences from programme to dutyman duties

            foreach ($arr2 as $j => $a) {
                $type_a = substr($a, strpos($a, ":") + 1);   // get type and name from dutyman entry
                $name_a = substr($a, 0, strpos($a, ':'));

                // see if matching type in $arr1
                $match = false;
                foreach ($arr1 as $k => $b)
                {
                    $type_b = substr($b, strpos($b, ":") + 1); // get type and name from programme entry
                    $name_b = substr($b, 0, strpos($b, ':'));

                    if ($type_a = $type_b)    // compare types for dutyman and programme
                    {
                        $match = true;
                        $key = $k;
                        break;
                    }
                }

                if ($match) {
                    // do swap
                    if ($dryrun) { $diff_report.= "<br>SWAPPING : [{$arr1[$k]}] replaces [{$arr2[$j]}]"; }
                    // FIXME swap_duty call goes here
                    unset($arr1[$k]);
                }
                // echo "<pre>ARR1 MOD: " . print_r($arr1, true) . "</pre>";
            }
                if ($dryrun) { $diff_report.= "<br>---------------------------------------------------------<br>"; }
        }
        if ($dryrun) { $diff_report.= "<br>** report end **"; }




        // get report body
        $pagefields['body'] = $tmpl_o->get_template("dtm_duty_import_report",
            array("report" => $diff_report, "start" => $start, "end" => $end), array("dryrun" => $dryrun) );
        // render page
        echo $tmpl_o->get_template("basic_page", $pagefields, array() );
    }

    $db_o->db_disconnect();
    exit();
}
else
{
    // error pagestate not recognised
    $_SESSION['pagefields']['body'] = "<p>INTERNAL ERROR page status not recognised - please contact System Manager</p>";

    // render page
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'], array() );
}


function read_csv_file($file)
{
    ini_set('auto_detect_line_endings', true);
    $arr = array();
    if (($handle = fopen($file, "r")) !== FALSE)
    {
        // get keys
        $keys = fgetcsv($handle, 1000, ",");

        $i = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE)
        {
            $i++;
            $arr[$i] = array_combine($keys, $data);
        }
        fclose($handle);
    }
    return $arr;
}

function serialise_duty($duties, $member_field, $duty_field)
{
    $sduty = array();
    foreach ($duties as $i=>$duty)
    {
        $sduty[$i] = strtolower($duty["$member_field"].":".$duty["$duty_field"]);
    }
    return $sduty;
}

function html_flush()
{
    echo str_pad('',4096)."\n";
    ob_flush();
    flush();
}


