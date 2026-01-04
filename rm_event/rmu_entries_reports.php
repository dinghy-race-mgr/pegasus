<?php
/*
 * Displays entries + waiting list using the same format as the entry page
 *
 * Output:
 *   Reception (confirmed entries, sorted on class/sailno, template: reception_rept
 *   EntryCheck (confirmed and waiting list entries, sorted on class/sailno, template: entrycheck_rept
 *   TallyList (confirmed entries, sorted on class/sailno, template: tallylist_rept
 *   FleetList (confirmed entries, sorted on class/sailno, template fleetlist_rept
 *
 */

$loc = "..";

require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_event_lib.php");
require_once ("{$loc}/common/classes/db.php");
require_once ("{$loc}/common/classes/template_class.php");

// start session
session_id('sess-rmuevent');
session_start();

error_reporting(E_ALL);  //fixme set for live operation to E150

// initialise application
$cfg = u_set_config("../config/common.ini", array(), true);
$cfg['rm_event'] = u_set_config("../config/rm_event.ini", array("rm_event"), true);
foreach($cfg['rm_event'] as $k => $v) { $cfg[$k] = $v; }
unset($cfg['rm_event']);

$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);
$tmpl_o = new TEMPLATE(array( "./templates/util_layouts_tm.php"));

//if (empty($_REQUEST['access']) OR $_REQUEST['access'] != $cfg['access_key'])
//{
//    exit("Sorry - you are not authorised to use this script ... ");  // FIXME -  change to exit_nicely
//}

// find event information
if (key_exists("eid", $_REQUEST))
{
    $eid = $_REQUEST['eid'];
    $event = $db_o->run("SELECT * FROM e_event WHERE id = ?", array($_REQUEST['eid']) )->fetch();
}

if (!isset($eid) or empty($event))
{
    echo "exit nicely error message - event not found";  // FIXME -  change to exit_nicely
    exit("script stopped");
}

// check if fleet information is required
key_exists("fleet", $_REQUEST) ? $fleet = true : $fleet = false;

// get output configuration
$output = get_report_config($_REQUEST['output'], $fleet);

// get all entry data (excluding waiting list - sorted by fleet, class, sailnumber)
$sql = "SELECT a.`id`, `b-class`, `b-sailno`, `b-altno`, `b-pn`, `b-fleet`, `b-division`, 
               `h-name`, `h-club`, `h-age`, `h-gender`, `h-emergency`,
               `c-name`, `c-age`, `c-gender`, `c-emergency`, 
               `e-tally`, `e-racemanager`, `e-waiting`, b.crew as crewnum 
               FROM e_entry as a LEFT JOIN t_class as b ON a.`b-class`= b.classname 
               WHERE eid = ? and `e-exclude` = 0 {$output['where']} {$output['sort']}";
//echo "<pre>$sql</pre>";
$entries = $db_o->run($sql, array($eid) )->fetchall();

$num_counts = get_entry_counts($eid); // gets counts for classes, fleets, and divisions

// process entries if required
if ($output['checks'])
{
    $entries = validate_entries();   // basic checks for missing data and racemanager integration - extends entries array
}

// produce body of report
$fields = array(
    "event-title"   => $event['title'],
    "version"       => $cfg['sys_release']." ".$cfg['sys_version'],
);

$params = array(
    "eid"        => $eid,
    "entries"    => $entries,
    "options"    => $output,
    "checks"     => $output['checks'],
    "counts"     => $num_counts,
    "fleet"      => $fleet
);

$navbar = $tmpl_o->get_template("navbar_utils", array("util-name"=> ucwords($_REQUEST['output'])." Report",
    "release" => $cfg['sys_release'], "version" =>$cfg['sys_version'], "year"=>date("Y") ), array());

$body = $tmpl_o->get_template($output['template'], $fields, $params);

// assemble page
$fields = array(
    'tab-title'   => "raceMgr Event Report",
    'styletheme'  => "sandstone_",
    'page-navbar' => $navbar,
    'page-title'  => $event['title'],
    'page-main'   => $body,
    'page-footer' => "&nbsp;",
    'page-modals' => "&nbsp;",
    'page-js'     => "&nbsp;"
);
$params = array();
echo $tmpl_o->get_template("utils_page", $fields, array());

function get_report_config($output, $fleet)
{
    // options
    $waiting = array(
        "1" => "and `e-waiting` = 0",
        "2" => "and `e-waiting` = 1",
    );
    $sort = array(
        "1" => "order by `b-class` ASC, `b-sailno` * 1 ASC",
        "2" => "order by `b-pn` DESC, `b-class` ASC, `b-sailno` * 1 ASC",
        "3" => "order by `e-tally` ASC",
        "4" => "order by `b-fleet` ASC, `b-class` ASC, `b-sailno` * 1 ASC",
    );

    if ($output == "reception")
    {
//      Reception (sort: class/sailno, waiting: no, checks: false, template: reception_rept

        $out_cfg = array(
            "sort"     => $sort["1"],
            "where"    => $waiting["1"],
            "checks"   => false,
            "template" => "reception_rept"
        );
    }
    elseif ($output == "entrycheck")
    {
//      EntryCheck (sort: 1 where:  checks: true template: entrycheck_rept
        $fleet? $sort_option = 4 : $sort_option = 1;
        $out_cfg = array(
            "sort"     => $sort["1"],
            "where"    => "",                      // checks both confirmed and waiting list boats
            "checks"   => true,
            "template" => "entrycheck_rept"
        );
    }
    elseif ($output == "tallylist")
    {
//      Tally (sort: 1 where: 1 checks: false template: tally_rept
        $out_cfg = array(
            "sort"     => $sort["1"],
            "where"    => $waiting["1"],
            "checks"   => false,
            "template" => "tallylist_rept"
        );
    }
    elseif ($output == "fleetlist")
    {
//      Fleet (sort: 4 where: 1 checks: false template: tally_rept
        $out_cfg = array(
            "sort"     => $sort["4"],
            "where"    => $waiting["1"],
            "checks"   => false,
            "template" => "fleetlist_rept"
        );
    }
    else
    {
        $out_cfg = false;
    }

    return $out_cfg;
}


function get_entry_counts($eid)
{
    global $db_o, $output;

    $num = array();

    $sql_group = "SELECT `id`, `b-class`, `b-fleet`, `b-division`, count(*) as number FROM e_entry  
               WHERE eid = ? and `e-exclude` = 0 {$output['where']} 
               --setgroup-- ORDER BY number DESC";

// get counts
    $sql = str_replace("--setgroup--", "and `b-class` != '' and `b-class` is not null GROUP BY `b-class` ", $sql_group);
    $num['class'] = $db_o->run($sql, array($eid) )->fetchall();

    $sql = str_replace("--setgroup--", "and `b-fleet` != '' and `b-fleet` is not null GROUP BY `b-fleet` ", $sql_group);
    $num['fleet'] = $db_o->run($sql, array($eid) )->fetchall();

    $sql = str_replace("--setgroup--", "and `b-division` != '' and `b-division` is not null GROUP BY `b-division` ", $sql_group);
    $num['division'] = $db_o->run($sql, array($eid) )->fetchall();

    return $num;
}