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
$appname    = "rm_trophy";
$opmode     = "live";
$syslog     = "$loc/logs/sys/$appname"."_".date("Y-m-d").".log";
$page = "Trophy Winners";
$scriptname = basename(__FILE__);
$styletheme = "cerulean_";
$stylesheet = "./style/rm_trophy.css";
$today = date("Y-m-d");

// includes
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/classes/db.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("./include/rm_trophy_lib.php");

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
    "page-title"  => "<h2>$page</h2>",
    "page-footer" => "<div class='text-end mt-4 pe-5'> &copy; {$cfg['sys_copyright']}</div>",
);

/* ------------ check pagestate ---------------------------------------------*/
if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }   // FIXME temporary fix for testing

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
        "function"     => "Trophy Display Report",
        "instructions" => "Creates a display of trophy winners for the selected year/period</br>Please set the options below.",
        "script"       => "trophy_winners_display.php?pagestate=submit",
    );

    $pagefields['page-main'] = $tmpl_o->get_template("trophies_display_form", $formfields, array("periods" => $periods));
    echo $tmpl_o->get_template("print_page", $pagefields );

}

/* ------------ submit page ------------------------------------------------------*/

elseif ($_REQUEST['pagestate'] == "submit")
{
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
              FROM t_trophy as a JOIN t_trophyaward as b ON a.id=b.trophyid  WHERE b.period = '{$_REQUEST['period']}'
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
     "data"    => $winners,
    );

    $pagefields = array(
        "stylesheet" => "",
        "tab-title" => "Trophy Winners",
        "page-theme" => "litera_",
        "page-title" => "<div class='text-center text-info'><h1>{$cfg['clubname']} - Trophies {$_REQUEST['period']}</h1></div>",
        "page-main" => $tmpl_o->get_template("trophy_display_content", array(), $params ),
        "page-footer" => "<p><small>printed on $today</small></p>",
    );

    echo $tmpl_o->get_template("print_page", $pagefields, array() );

}














