<?php
/*
 * display_trophy_winners.php
 *
 * utility used to create a printed list of trophy winners
 *
 * arguments
 *    pagestate - 'init' for options selection,  'submit' for processing  (required)
 *    period    - period label for the data to be displayed
 *
 *    if pagestate = "submit" - pass values for processing and transfer as variables process_<seriescode>, transfer_<seriescode>
 *
 *
 */
$dbg = true;
$loc  = "..";
$page = "display_trophy_winners";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "";
$stylesheet = "./style/rm_utils.css";

require_once ("{$loc}/common/lib/util_lib.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

$_SESSION['sql_debug'] = true;  // FIXME - turn off after debugging

// classes
require_once ("{$loc}/common/classes/db.php");
require_once ("{$loc}/common/classes/template_class.php");

// initialise utility application
$cfg = u_set_config("../config/common.ini", array(), false);
$cfg['rm_trophy'] = u_set_config("../config/rm_trophy.ini", array("rm_trophy"), true);
foreach($cfg['rm_trophy'] as $k => $v) { $cfg[$k] = $v; }
unset($cfg['rm_trophy']);
//$cfg['logfile'] = str_replace("_date", date("_Y"), $cfg['logfile']); // no logging
if (array_key_exists("timezone", $cfg)) { date_default_timezone_set($cfg['timezone']); }

// connect to database  (using PDO)
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// get club specific values
foreach ($db_o->getinivalues(true) as $k => $v) { $cfg[$k] = $v; }  // FIXME not working on HTZ

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/trophies_tm.php"));

if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "submit"; }   // FIXME temporary fix for testing


/* ------------ file selection page ---------------------------------------------*/
$state = 0;

if ($_REQUEST['pagestate'] != "init" AND $_REQUEST['pagestate'] != "submit")
{
    $pagefields = array(
        "theme" => $styletheme,
        "stylesheet" => $stylesheet,
        "tab-title" => "Trophy Winners",
        "page-theme" => "",
        "page-title" => "Trophy Winners Display",
        "page-main" => $tmpl_o->get_template("trophies_error", array(), array("state"=>2, "pagestate"=>$_REQUEST['pagestate'])),
        "page-footer" => "",
    );

    echo $tmpl_o->get_template("print_page", $pagefields );
}

/* ------------ get user input page ---------------------------------------------*/
elseif ($_REQUEST['pagestate'] == "init")
{
//    // if event exists create options page
//    if ($event)
//    {
//        $formfields = array(
//            "instructions" => "Creates a display of trophy winners for the selected year</br>
//           <span class=' rm-text-xs'>Please set the options below.</br>",
//            "script" => "display_trophy_winners.php?pagestate=submit",
//        );
//
//        $pagefields['header-right'] = "{$event['event_name']} - {$event['event_date']}  (". substr($event['event_start'], 0, 5) . ")";
//        $pagefields['body'] = $tmpl_o->get_template("publishresults_form", $formfields,
//                              array("list" => $process_list, "series"=>$series_arr, "upload" => $_SESSION['result_upload']));
//    }
//    else
//    {
//        $state = 3;  // report missing event
//    }

    if ($state != 0 )  // reset page to deal with error conditions
    {
        $pagefields['body'] = $tmpl_o->get_template("publishresults_error", array(), array("state"=>$state, "eventid"=>$_REQUEST['eventid']));
    }

    // display page
    echo $tmpl_o->get_template("print_page", $pagefields );

}

/* ------------ submit page ------------------------------------------------------*/

elseif ($_REQUEST['pagestate'] == "submit")
{
    // set up arguments
    $_REQUEST = array(
        "year-label" => '2023-2024',
        "mode"       => 'standard',
    );
    $today = date("jS F Y H:i");

    $section_cfg = array(
        'trophy_series' => array(
            'heading'   => "overall trophy series",
            'description' => "premier season long series involving trophy races",
            'num_winners' => 3,
            'decode_type' => "class"
        ),
        'open_event' => array(
            'heading'   => "exe open regattas",
            'description' => "races and events that are open to non-SYC members",
            'num_winners' => 1,
            'decode_type' => "class"
        ),
        'club_series' => array(
            'heading'   => "club series",
            'description' => "sunday and evening series throughout the season",
            'num_winners' => 3,
            'decode_type' => "class"
        ),
        'trophy_race' => array(
            'heading'   => "trophy races",
            'description' => "individual races for which a trophy is awarded",
            'num_winners' => 1,
            'decode_type' => "class"
        ),
        'cruiser_race' => array(
            'heading'   => "yacht racing",
            'description' => "passage racing for yachts",
            'num_winners' => 1,
            'decode_type' => "name"
        ),
        'achievement' => array(
            'heading'   => "achievement awards",
            'description' => "awards for specific achievements",
            'num_winners' => 1,
            'decode_type' => "class"
        ),
        'junior_regatta' => array(
            'heading'   => "junior regatta",
            'description' => "prizes awarded during the junior regatta",
            'num_winners' => 1,
            'decode_type' => "class"
        ),
        'junior_training' => array(
            'heading'   => "junior training ",
            'description' => "training achievement awards",
            'num_winners' => 1,
            'decode_type' => "class"
        ),
    );

    // get events in defined order (group, then internal order)
    $query = "SELECT a.id, a.name, a.sname, a.notes, a.allocation_notes, a.picture, a.group_sort, a.individual_sort, 
              b.period, b.award_category, b.award_division, b.winner_1, b.winner_2, b.winner_3, b.notes 
              FROM t_trophy as a JOIN t_trophyaward as b ON a.id=b.trophyid  WHERE b.period = '{$_REQUEST['year-label']}'
              ORDER BY FIELD(group_sort, 'trophy_series', 'open_event', 'club_series', 'trophy_race', 'cruiser_race', 'achievement', 'junior_regatta', 'junior_training', ''), individual_sort ASC";
    $winners = $db_o->run($query, array() )->fetchall();

    // loop through events and add content, annotations, format flags
    $i = 0;
    foreach ($winners as $k => $winner)
    {
        $i++;

        $section = $winner['group_sort'];

        // decode winners - up to 3
        for ($i = 1; $i<= 3; $i++)
        {
            $winners[$k]["winner_".$i."_arr"] = decode_winner($i, $winner["winner_".$i], $section, $section_cfg[$section]['decode_type']);
        }
    }

    // pass data to template for display
    $params = array(
     "section" => $section_cfg,
     "data"    => $winners
    );

    $pagefields = array(
        "theme" => $styletheme,
        "stylesheet" => "",
        "tab-title" => "Trophy Winners",
        "page-theme" => "",
        "page-title" => "Starcross YC Trophy Winners {$_REQUEST['year-label']}",
        "page-main" => $tmpl_o->get_template("trophy_display_content", array(), $params ),
        "page-footer" => "<p><small>printed on $today</small></p>",
    );

    echo $tmpl_o->get_template("print_page", $pagefields, array() );

}


function decode_winner($i, $winner_str, $section, $decode_type = 'class')
{
    // Boatname|SailNumber|Helm Name|Crew1 name, Crew2 Name|Other Info|
    // Class|Sailnumber|Helm Name|Crew Name|Other Info|

    $position = array("1"=>"1st","2"=>"2nd","3"=>"3rd");

    $winner_arr = array();
    $arr = array_filter(explode("|", $winner_str));  /// array_filter ensures null array

    empty($arr) ? $winner_arr['exists'] = false : $winner_arr['exists'] = true;

    if ($winner_arr['exists'] === true)
    {
        $winner_arr['type']    = $decode_type;
        $winner_arr['section'] = $section;
        $winner_arr['posn']    = $position[$i];
        !empty($arr[0]) ? $winner_arr['boat'] = $arr[0] : $winner_arr['boat'] = '';
        !empty($arr[1]) ? $winner_arr['number'] = $arr[1] : $winner_arr['number'] = '';
        !empty($arr[2]) ? $winner_arr['helm'] = $arr[2] : $winner_arr['helm'] = '';
        !empty($arr[3]) ? $winner_arr['crew'] = $arr[3] : $winner_arr['crew'] = '';
        !empty($arr[4]) ? $winner_arr['info'] = $arr[4] : $winner_arr['info'] = '';
    }

    return $winner_arr;
}












