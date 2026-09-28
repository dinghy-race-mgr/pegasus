<?php
/*
 * trophy_winners_list.php
 *
 * utility used to create a printed list of trophy winners organised by helm name
 *
 * arguments
 *
 *
 *
 */



$dbg = true;
$loc  = "..";
$appname    = "rm_trophy";
$opmode     = "live";
$syslog     = "$loc/logs/sys/$appname"."_".date("Y-m-d").".log";
$page = "trophy presentation list";
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "cerulean_";
$stylesheet = "./style/rm_utils.css";

$today = date("Y-m-d");

// classes
require_once ("{$loc}/common/classes/db.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_trophy_lib.php");

//session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
//session_start();

// initialise application
$cfg = u_config("../config/racemanager_new.ini", array("rm_trophy"), $opmode, true);

// connect to database  (using PDO)
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// add club specific values to config
$club_val = $db_o->getinivalues(true);
foreach ($club_val as $k => $v)  { $cfg[$k] = $v;}

// find out in trophy_award what period values are currently used
$query = "SELECT period FROM t_trophyaward GROUP BY period ORDER BY period DESC";
$periods = $db_o->run($query, array() )->fetchall();

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php", "./templates/trophies_tm.php"));

// common template parameters
$pagefields = array(
    "stylesheet"  => $stylesheet,
    "tab-title"   => "Trophies",
    "page-theme"  => $styletheme,
    "page-title"  => "<h2 class='ps-5'>Trophy Presentation List</h2>",
    "page-footer" => "<div class='text-end mt-4 pe-5'> &copy; Elmswood Software ".date("Y")."</div>",
);

/* ------------ check pagestate ---------------------------------------------*/
if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

//echo "<pre>".print_r($_REQUEST,true)."</pre>";
if ($_REQUEST['pagestate'] != "init" AND $_REQUEST['pagestate'] != "submit")
{
    $pagefields['page-main'] = $tmpl_o->get_template("trophies_error", array(), array("state"=>2, "pagestate"=>$_REQUEST['pagestate']));
    echo $tmpl_o->get_template("print_page", $pagefields, array() );
}

/* ------------ get user input page ---------------------------------------------*/
elseif ($_REQUEST['pagestate'] == "init")
{

    $formfields = array(
        "function"     => "Trophy Presentation List",
        "instructions" => "Creates a list of trophy winners for a selected year/period. The list groups all trophies won by each member, 
                            sorted by the no. of trophies won be each member (least first).</br></br>Use the print to PDF option on your browser to get the list as 
                            a PDF file</br><hr>Please set the option(s) below.",
        "script"       => "trophy_presentation_list.php?pagestate=submit",
    );

    $pagefields['page-main'] = $tmpl_o->get_template("trophies_presentation_list_form", $formfields, array("periods" => $periods));
    echo $tmpl_o->get_template("print_page", $pagefields );

}

/* ------------ submit page ------------------------------------------------------*/

elseif ($_REQUEST['pagestate'] == "submit") {
    $today = date("jS F Y H:i");

    $query = "SELECT a.id, a.name, a.sname, a.notes, a.allocation_notes, a.picture, a.group_sort, a.individual_sort, 
              b.period, b.award_category, b.award_division, b.winner_1, b.winner_2, b.winner_3, b.notes 
              FROM t_trophy as a JOIN t_trophyaward as b ON a.id=b.trophyid  WHERE b.period = '{$_REQUEST['period']}' 
              ORDER BY FIELD(group_sort, 'trophy_series', 'open_event', 'club_series', 'trophy_race', 'cruiser_race', 'achievement', 'junior_regatta', 'junior_training', ''), individual_sort ASC";

    $winners = $db_o->run($query, array())->fetchall();

    // loop through winners to get a search string on helm name (surname firstname)
    $i = 0;
    $a = array();
    $data = array();
    $names_sort = array();
    foreach ($winners as $k => $winner) {
        empty($winner['sname']) ? $trophy = $winner['name'] : $trophy = $winner['sname'];
        $section = $winner['group_sort'];

        // decode winners - up to 3
        for ($i = 1; $i <= 3; $i++) {
            // decode winner information in database record
            $b = decode_winner($i, $winner["winner_" . $i], $section, "class");
            // add search string for helm's name with surname follwed by firstname
            if ($b['exists'] == "1") {
                $names = explode(" ", $b['helm']);
                $last_name = $names[count($names) - 1];
                unset($names[count($names) - 1]);
                $b['sortstr'] = trim($last_name . " " . implode(" ", $names));
            } else {
                $b['sortstr'] = "";
            }

            // get award information into array
            $a = array(
                "event" => $winner['award_category'] . " - " . $winner['award_division'],
                "trophy" => $trophy,
                "notes" => $winner['notes']
            );

            // merge two arrays into one output array
            $data[] = array_merge($a, $b);
        }
    }
    //    echo "<pre>".print_r($data,true)."</pre>";

    // remove not sailed records
    foreach ($data as $k => $row) {
        if ($row['exists'] != "1" or $row['boat'] == "not sailed") {
            unset($data[$k]);
        }
    }

    // sort data array by helms search str name and position in series
    $helm = array_column($data, 'sortstr');
    $posn = array_column($data, 'posn');
    array_multisort($helm, SORT_ASC, $posn, SORT_ASC, $data);
    //echo "<pre>".print_r($data,true)."</pre>";

    // pass data to template for display
    $params = array("data" => $data);

    $pagefields = array(
        "stylesheet" => "",
        "tab-title" => "Trophies",
        "page-theme" => $styletheme,
        "page-title" => "<h2>Starcross YC Trophy Winners {$_REQUEST['period']}<br>PRESENTATION LIST</h2><hr>",
        "page-main" => $tmpl_o->get_template("trophy_presentation_list", array(), $params),
        "page-footer" => "<div class='text-end mt-4 pe-5'> &copy; Elmswood Software ".date("Y")."</div>",
    );

    echo $tmpl_o->get_template("print_page", $pagefields, array());
}















