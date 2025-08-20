<?php
/*
 * rmu_sailwave_entry_export.php
 *
 * script to export event entries from racemanager to the sailwave csv import format
 *
 * usage: sailwave_entry_export.php?eid=<event id from e_event>
 *
 * Arguments (* required)
 *    eid       -   id for event in e_event *
 *    mode      -   |standard|extended| - content mode - default is standard
 *    include   -       none    - only include entered boats
 *                      waiting - include entered boats + boats on waiting list
 *                      exclude - include entered boats and boats that have been excluded
 *                      all     - allows entered boats, excluded boats and waiting list boats
 *                   default is `none`
 */

$loc  = "..";
$page = "sailwave entry export";
define('BASE', dirname(__FILE__) . '/');
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$stylesheet = "./style/rm_utils.css";

error_reporting(E_ERROR);

require_once ("{$loc}/common/classes/db.php");
require_once ("{$loc}/common/lib/util_lib.php");
require_once("{$loc}/common/lib/rm_event_lib.php");
require_once("classes/template.php");

// initialise utility application
$cfg = set_config("../config/common.ini", array(), false);
$cfg['rm_event'] = set_config("../config/rm_event.ini", array("rm_event"), true);
foreach($cfg['rm_event'] as $k => $v) { $cfg[$k] = $v; }
unset($cfg['rm_event']);
$cfg['logfile'] = str_replace("_date", date("_Y"), $cfg['logfile']);
if (array_key_exists("timezone", $cfg)) { date_default_timezone_set($cfg['timezone']); }

// connect to database  (using PDO)
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// get club specific values
foreach ($db_o->getinivalues(true) as $k => $v) { $cfg[$k] = $v; }

// set templates
$tmpl_o = new TEMPLATE(array("./templates/util_layouts_tm.php"));

// FIXME check access key ???
//{
//    exit("Sorry - you are not authorised to use this script ... STOPPING");  // FIXME -  change to exit_nicely
//}

// standard page fields
$pagefields = array(
    "loc"         => $loc,
    "page-theme-utils" => $cfg['theme_utils'],
    "stylesheet"  => $stylesheet,
    "page-title"  => $cfg['sys_name'],
    "page-navbar" => $tmpl_o->get_template("navbar_utils", array("util-name"=>$page, "version"=>$cfg['sys_version'], "year" => date("Y")), array()),
    "page-footer" => "",
    "page-modals" => "",
    "page-js" => "",
);

// get pagestate
$pagestate = u_checkarg("pagestate", "set", "", "init");

if ($pagestate == "init") {
    $eid = u_checkarg("eid", "set", "", "");
    $mode = u_checkarg("mode", "set", "", "standard");
    $include = u_checkarg("include", "set", "", "excluded");

    $formfields = array(
        "function" => "Creates CSV file for import of entries into SAILWAVE",
        "instructions" => "Please select the field set you need and the entry records to include.",
        "script" => "rmu_sailwave_entry_export.php?pagestate=submit&eid=$eid",
    );

    $pagefields['page-main'] = $tmpl_o->get_template("sailwave_export_form", $formfields, array("action" => true, "mode" => $mode, "include" => $include));
}

elseif ($pagestate == "submit")
{
    $eid       = u_checkarg("eid", "set", "", "");
    $mode      = u_checkarg("mode", "set", "", "standard");
    $include   = u_checkarg("include", "set", "", "excluded");

    // get event record
    $event = $db_o->run("SELECT * FROM e_event WHERE `id` = ?", array($eid) )->fetch();
    if (!empty($event))
    {
        // get entries associated with event
        $query = set_query($include);
        $entry = $db_o->run($query, array($eid) )->fetchall();

        if (count($entry) <= 0)           // no entries
        {
            $formfields = array(
                "problem" => "No entries found for specified event",
                "data"    => "event: {$event['title']} ",
                "action"  => "Check that you have selected the correct event, and that the event has entries"
            );
            $pagefields['page-main'] = $tmpl_o->get_template("problem_report", $formfields, array("info"=>true));
            $pagefields['page-footer'] = $tmpl_o->get_template("footer_utils", array("footer-left"=>"", "footer-center"=>"",
                "footer-right"=>"close this browser tab to return"), array("footer"=>true));
        }
        else
        {
            // process entries to get output data
            $num_excluded = 0;
            $num_waiting = 0;
            $num_total = 0;

            foreach ($entry as $row)
            {
                // get entry status and count records of each type
                $status_val = set_status($row['e-exclude'], $row['e-waiting'] );

                if ($mode == "standard")
                {
                    // standard fields
                    $rows[] = array(
                        "fleet"        => $row['b-fleet'],
                        "class"        => $row['b-class'],
                        "sailno"       => $row['b-sailno'],
                        "boat"         => $row['b-name'],             // used for boatname/sponsor
                        "rating"       => $row['b-pn'],
                        "helmname"     => $row['h-name'],
                        "helmagegroup" => $row['h-age'],
                        "crewname"     => $row['c-name'],
                        "crewagegroup" => $row['c-age'],
                        "club"         => $row['h-club'],
                        "helmnotes"    => $row['h-emergency'],        // used for emergency phone no.
                        "altsailno"    => $row['b-altno'],            // used for short sail number or bow number
                        "status"       => $status_val,                // set to "entered" or "waiting" or "excluded"
                    );
                }
                else
                {
                    // extended fields
                    $rows[] = array(
                        "fleet"        => $row['b-fleet'],
                        "class"        => $row['b-class'],
                        "sailno"       => $row['b-sailno'],
                        "boat"         => $row['b-name'],             // used for boatname/sponsor
                        "rating"       => $row['b-pn'],
                        "helmname"     => $row['h-name'],
                        "helmagegroup" => $row['h-age'],
                        "crewname"     => $row['c-name'],
                        "crewagegroup" => $row['c-age'],
                        "club"         => $row['h-club'],
                        "helmnotes"    => $row['h-emergency'],        // used for emergency phone no.
                        "altsailno"    => $row['b-altno'],            // used for short sail number or bow number
                        "status"       => $status_val,                // set to "entered" or "waiting" or "excluded"
                    );
                }
            }

            // create output file
            $filename = "../data/events/".date("Y", strtotime($event['date-start']))."/{$event['nickname']}/entries_export_".date("jMHi").".csv";
            $cols = array_keys($rows[0]);
            u_create_csv_file($filename, $cols, $rows);

            // reporting
            $entry_options = array(
                "none"     => "entered only",
                "excluded" => "entered and excluded",
                "waiting"  => "entered and waiting list",
                "all"      => "entered and waiting list and excluded",
            );

            $formfields = array(
                "event"        => $event['title'],
                "fieldlist"    => $mode,
                "entries"      => $entry_options[$include],
                "num_excluded" => $num_excluded,
                "num_waiting"  => $num_waiting,
                "num_total"    => $num_total,
                "filename"     => $filename,
            );

            $pagefields['page-main'] = $tmpl_o->get_template("sailwave_export_output", $formfields, array());
            $pagefields['page-footer'] = $tmpl_o->get_template("footer_utils", array("footer-left"=>"click link to download export file ...",
                "footer-center"=>"", "footer-right"=>"close this browser tab to return"), array("footer"=>true));
        }

    }
    else   // event not recognised
    {
        $formfields = array(
            "problem" => "Event not recognised",
            "data"    => "event id: $eid ",
            "action"  => "Check that you have specified the correct event"
        );

        $pagefields['page-main'] = $tmpl_o->get_template("problem_report", $formfields, array("info"=>true));
        $pagefields['page-footer'] = $tmpl_o->get_template("footer_utils", array("footer-left"=>"", "footer-center"=>"",
            "footer-right"=>"close this browser tab to return"), array("footer"=>true));
    }
}
else  // pagestate not recogised
{
    $formfields = array(
        "problem"  => "pagestate not recognised",
        "file"     => __FILE__,
        "line"     => __LINE__,
        "evidence" => "pagestate = |$pagestate|",
    );
    $params['action'] = "close browser window and try again";
    $pagefields['page-main'] = $tmpl_o->get_template("error_report", $formfields, array());
    $pagefields['page-footer'] = $tmpl_o->get_template("footer_utils", array("footer-left"=>"", "footer-center"=>"",
        "footer-right"=>"close this browser tab to return"), array("footer"=>true));
}
echo $tmpl_o->get_template("utils_page", $pagefields );



function set_query($include)
{
    if ($include == "none")
    {
        $query = "SELECT * FROM e_entry WHERE `eid` = ? AND `e-waiting` != 1 AND `e-exclude` != 1";
    }
    elseif ($include == "waiting")
    {
        $query = "SELECT * FROM e_entry WHERE `eid` = ?  AND `e-exclude` != 1";
    }
    elseif ($include == "excluded")
    {
        $query = "SELECT * FROM e_entry WHERE `eid` = ? AND `e-waiting` != 1";
    }
    elseif ($include == "all")
    {
        $query = "SELECT * FROM e_entry WHERE `eid` = ?";
    }

    return $query;
}

function set_status($exclude, $waiting)
{
    global $num_total, $num_waiting, $num_excluded;
    $status_val = "";
    $num_total++;
    if ($exclude == 1)
    {
        $status_val = "excluded";
        $num_excluded++;
    }
    elseif ($waiting == 1)
    {
        $status_val = "waiting";
        $num_waiting++;
    }
    if (empty($status_val)) { $status_val = "entered"; }

    return $status_val;
}

