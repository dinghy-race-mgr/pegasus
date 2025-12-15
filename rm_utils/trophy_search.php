<?php
/*
* trophy_search.php
*
 * utility application for users to query the trophy awards database by trophy name and winner name
*
 *
 *
 */

// TODO - integrate into results application, send to Simon
// TODO - set up return to results button

$dbg = false;
$loc  = "..";
$page = "trophy_search";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$year = date("Y");
$styletheme = "morph_";

$stylesheet = "./style/rm_utils.css";

// uncomment if you want to make bootstrap theme selectable
//session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
//session_start();
//if (key_exists('theme', $_REQUEST)) { $_SESSION['theme'] = $_REQUEST['theme']."_"; }
//$styletheme = $_SESSION['theme'];

require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/classes/db.php");
require_once ("{$loc}/common/classes/template_class.php");

// initialise utility application
$cfg = u_set_config("../config/common.ini", array(), false);
$cfg['rm_trophy'] = u_set_config("../config/rm_trophy.ini", array("rm_trophy"), true);
foreach($cfg['rm_trophy'] as $k => $v) { $cfg[$k] = $v; }
unset($cfg['rm_trophy']);

if (array_key_exists("timezone", $cfg)) { date_default_timezone_set($cfg['timezone']); }

// connect to database  (using PDO)
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/trophies_tm.php"));

// common template parameters
$footer = <<<EOT
<!--div class='text-start ps-5'>
    <a class="btn btn-sm btn-warning border border-warning ms-3" style="min-width: 150px;" "type="button" name="Quit" id="Quit" onclick="return quitBox('quit');">
    <span class="fs-6"><i class="bi bi-arrow-left-square">&nbsp;</i>&nbsp;Exit</span></a>
</div-->
<div class='text-end pe-5'> &copy; Robert Elkington $year</div>
EOT;


$pagefields = array(
    "stylesheet"  => $stylesheet,
    "tab-title"   => "Trophy Search",
    "page-theme"  => $styletheme,
    "page-title"  => "<h2 class='text-primary ps-5 mt-5'>Trophy Search</h2>",
    "page-footer" => $footer,
);

// get list of all trophies "SELECT id, name, sname FROM `t_trophy`";
$query = "SELECT id, name, sname, picture, donor, notes, date_acquired, location, current_allocation, current_division FROM `t_trophy` ORDER BY name ASC";
$trophies = $db_o->run($query, array() )->fetchall();

// get list of periods covered by data
$query = "SELECT period FROM t_trophyaward GROUP BY period ORDER BY period ASC";
$periods = $db_o->run($query, array() )->fetchall();

$min = 9999;
$max = 0;
foreach($periods as $period)
{
    $years = get_year_from_period(implode($period));
    if (intval($years[0]) < $min){ $min = $years[0];}
    if (intval($years[1]) > $max){ $max = $years[1];}
}

$formfields = array(
    "form-title"     => "Trophy Search",
    "search_label" => "Search",
    "instructions" => "",
    "lower-instructions" => "To search -  enter either the trophy name and/or a person name &hellip;",
    "script"       => "trophy_search.php?pagestate=submit",
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
    $pagefields['page-main'] = $tmpl_o->get_template("trophy_search_form", $formfields,
        array("start_year" => $min, "end_year" => $max, "periods" => $periods, "trophies" => $trophies, "error" => array()));
    echo $tmpl_o->get_template("print_page", $pagefields );
}

/* ------------ submit page ------------------------------------------------------*/

elseif ($_REQUEST['pagestate'] == "submit")
{

    $err = array("1" => false, "2" => false, "3" => false);

    //check inputs - dates
    $sy = $_REQUEST['start_year'];
    $ey = $_REQUEST['end_year'];

    $err_set = false;
    if ($sy >= $ey)
    {
        $err["1"] = true;
        $err_set = true;
    }

    //check inputs - must be either person and/or trophy supplied
    if (check_search_criteria($_REQUEST['trophy'], $_REQUEST['person']) == "none")
    {
        $err["2"] = true;
        $err_set = true;
    }

    if ($err_set)
    {
        $pagefields['page-main'] = $tmpl_o->get_template("trophy_search_form", $formfields,
            array("start_year" => $min, "end_year" => $max, "periods" => $periods, "trophies" => $trophies, "error" => $err));

        echo $tmpl_o->get_template("print_page", $pagefields );
    }
    else
    {
        //get results data

        if (check_search_criteria($_REQUEST['trophy'], $_REQUEST['person']) == "trophy") // user only supplied trophy criteria
        {
            $query = "SELECT b.id, b.name, b.sname, b.picture,
              a.period, a.award_category, a.award_division, a.winner_1, a.winner_2, a.winner_3, a.notes
              FROM t_trophyaward as a JOIN t_trophy as b ON a.trophyid=b.id 
              WHERE (b.name = '{$_REQUEST['trophy']}' or b.sname = '{$_REQUEST['trophy']}') 
              ORDER BY name ASC, a.period DESC";
        }
        elseif (check_search_criteria($_REQUEST['trophy'], $_REQUEST['person']) == "person") // user only supplied person criteria
        {
            $query = "SELECT b.id, b.name, b.sname, b.picture,
              a.period, a.award_category, a.award_division, a.winner_1, a.winner_2, a.winner_3, a.notes
              FROM t_trophyaward as a JOIN t_trophy as b ON a.trophyid=b.id 
              WHERE (a.winner_1 LIKE '%{$_REQUEST['person']}%' or a.winner_2 LIKE '%{$_REQUEST['person']}%' 
              or a.winner_3 LIKE '%{$_REQUEST['person']}%')
              ORDER BY name ASC, a.period DESC";
        }
        elseif (check_search_criteria($_REQUEST['trophy'], $_REQUEST['person']) == "both") // user supplied both criteria
        {
            $query = "SELECT b.id, b.name, b.sname, b.picture,
              a.period, a.award_category, a.award_division, a.winner_1, a.winner_2, a.winner_3, a.notes
              FROM t_trophyaward as a JOIN t_trophy as b ON a.trophyid=b.id 
              WHERE (b.name = '{$_REQUEST['trophy']}' or b.sname = '{$_REQUEST['trophy']}') 
              ORDER BY name ASC, a.period DESC";
        }

        $records = $db_o->run($query, array())->fetchall();

        // extract required data for results
        $data = array();
        foreach ($records as $k => $record)
        {
            for ($i = 1; $i <= 3; $i++)
            {
                $arr = decode_winner($i, $record["winner_$i"], "", $decode_type = 'class');
                if ($arr['exists'])
                {
                    // check if we need this data
                    $include_data = true;
                    if (check_search_criteria($_REQUEST['trophy'], $_REQUEST['person']) == "both" or
                        check_search_criteria($_REQUEST['trophy'], $_REQUEST['person']) == "person")
                    {
                        if (stripos($arr['helm'], $_REQUEST['person']) === false and stripos($arr['crew'], $_REQUEST['person']) === false)
                        {
                            $include_data = false;
                        }
                    }

                    if ($include_data)
                    {
                        empty($arr['crew']) ? $team = $arr['helm'] : $team = $arr['helm']." / ".$arr['crew'];
                        $data[] = array(
                            "place"    => $arr['posn'],
                            "period"   => $record['period'],
                            "team"     => $team,
                            "trophy"   => $record['name'],
                            "category" => $record['award_category'],
                            "division" => $record['award_division'],
                            "boat"     => $arr['boat']." ".$arr['number']);
                    }
                }
            }
        }

        //count data records
        $data_num = count($data);

        // form
        $formfields["search_label"] = "Search Again";

        $pagefields['page-main'] = $tmpl_o->get_template("trophy_search_form", $formfields,
            array("start_year" => $min, "end_year" => $max, "periods" => $periods, "trophies" => $trophies, "error" => $err));
        //

        $display_trophy = false;
        if (check_search_criteria($_REQUEST['trophy'], $_REQUEST['person']) == "trophy")
        {
            $display_trophy = true;
        }

        $trophy_key = array_search($_REQUEST["trophy"], array_column($trophies, 'name'));
        $trophy = $trophies[$trophy_key];

        $pagefields['page-main'].= $tmpl_o->get_template("trophy_search_results", $formfields,
            array("data" => $data, "data_num" => $data_num, "trophy" => $trophy, "display_trophy" => $display_trophy));

        echo $tmpl_o->get_template("print_page", $pagefields );
    }
}


function decode_winner($i, $winner_str, $section, $decode_type = 'class')
{
    // Boatname|SailNumber|Helm Name|Crew1 name, Crew2 Name|Other Info|
    // Class|Sailnumber|Helm Name|Crew Name|Other Info|

    $position = array("1"=>"1st","2"=>"2nd","3"=>"3rd");

    $winner_arr = array();
    $arr = array_filter(explode("|", $winner_str));  /// array_filter ensures null array

    empty($arr) ? $winner_arr['exists'] = false : $winner_arr['exists'] = true;

    $winner_arr['type']    = $decode_type;
    $winner_arr['section'] = $section;
    $winner_arr['posn']    = $position[$i];
    !empty($arr[0]) ? $winner_arr['boat'] = $arr[0] : $winner_arr['boat'] = '';
    !empty($arr[1]) ? $winner_arr['number'] = $arr[1] : $winner_arr['number'] = '';
    !empty($arr[2]) ? $winner_arr['helm'] = $arr[2] : $winner_arr['helm'] = '';
    !empty($arr[3]) ? $winner_arr['crew'] = $arr[3] : $winner_arr['crew'] = '';
    !empty($arr[4]) ? $winner_arr['info'] = $arr[4] : $winner_arr['info'] = '';

    return $winner_arr;
}

function get_year_from_period($period)
{
    $years = explode("-", $period);
    return $years;
}

function check_search_criteria($trophy, $person)
{
    if (empty($person) and empty($trophy)) {
        $ans = "none";
    } elseif ($person and $trophy) {
        $ans = "both";
    } elseif ($person and empty($trophy)) {
        $ans = "person";
    } elseif ($trophy and empty($person)) {
        $ans = "trophy";
    }
    return $ans;
}

function find_person_in_winners($person)
{
    global $winners;

    if (!empty($_REQUEST['person'])) {
        $found = 0;
        foreach ($winners as $k => $winner) {
            if (stripos($winner['winner_1'], $_REQUEST['person']) === false) {
                if (stripos($winner['winner_2'], $_REQUEST['person']) === false) {
                    if (stripos($winner['winner_3'], $_REQUEST['person']) === false) {

                    } else {
                        $found = 3;
                    }
                } else {
                    $found = 2;
                }
            } else {
                $found = 1;
            }
        }
    } else {
        $found = 0;
    }

    return $found;
}








