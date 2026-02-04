<?php
/*
 * Sets tally numbers for confirmed entries in an event
 * Tallies are assigned numerically in class/sail number order
 *
 */

$loc = "..";
$today = date("Y-m-d");

require_once ("{$loc}/common/lib/util_lib.php");
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

// check pagestate
key_exists("pagestate", $_REQUEST) ? $pagestate = $_REQUEST['pagestate'] : $pagestate = "init";

// find event information
if (key_exists("eid", $_REQUEST))
{
    $eid = $_REQUEST['eid'];
    $event = $db_o->run("SELECT * FROM e_event WHERE id = ?", array($eid) )->fetch();
}

// set page navbar
$navbar = $tmpl_o->get_template("navbar_utils", array("util-name"=> "Set Tally Numbers",
    "release" => $cfg['sys_release'], "version" =>$cfg['sys_version'], "year"=>date("Y") ), array());



//if (!isset($eid) or empty($event))
//{
//    echo "exit nicely error message - event not found";  // FIXME -  change to exit_nicely
//    exit("script stopped");
//}

//// check if fleet information is required
//key_exists("fleet", $_REQUEST) ? $fleet = true : $fleet = false;

//// get output configuration
//$output = get_report_config($_REQUEST['output'], $fleet);

if (($pagestate != "init" and $pagestate != "submit") or (!isset($eid)))
{
    // deal with bad arguments
}
elseif ($pagestate == "init")
{
    // output instructions
    $fields = array(
        "event-title" => $event['title'],
        "version"     => $cfg['sys_release']." ".$cfg['sys_version'],
    );
    $body = $tmpl_o->get_template("set_tally_info", $fields, array("eid"=>$eid, "state" => "instructions"));

}
elseif ($pagestate == "submit")
{
    // add tally numbers + link to tally report

    // get all confirmed entry data (excluding waiting list - class, sailnumber)
    $sql = "SELECT `id`, `b-class`, `b-sailno`, `b-fleet`, `b-division`, `h-name`, `h-club`, `h-age`, `h-emergency`, 
               `c-name`, `c-age`, `c-emergency`, `e-tally` FROM e_entry  WHERE eid = ? and `e-exclude` = 0 and `e-waiting` = 0 
                ORDER BY `b-class` ASC, `b-sailno` * 1 ASC";
    $entries = $db_o->run($sql, array($eid) )->fetchall();

    $count = count($entries);

    $i = 0;
    foreach ($entries as $row)
    {
        $i++;
        $sql = "UPDATE e_entry SET `e-tally` = $i WHERE id = {$row['id']}";
        $upd = $db_o->run($sql, array());
    }

    $fields = array(
        "event-title" => $event['title'],
        "version"     => $cfg['sys_release']." ".$cfg['sys_version'],
    );
    $body = $tmpl_o->get_template("set_tally_info", $fields, array("eid"=>$eid, "state"=>"results", "count"=>$count));
}
else
{
    echo "exit nicely error message - event not found";  // FIXME -  change to exit_nicely
    exit("script stopped");
}

$fields = array(
    'tab-title'   => "Set Tally",
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




//
//// produce body of report
//$fields = array(
//    "event-title"   => $event['title'],
//    "version"       => $cfg['sys_release']." ".$cfg['sys_version'],
//);
//
//$params = array(
//    "eid"        => $eid,
//    "entries"    => $entries,
//    "options"    => $output,
//    "checks"     => $output['checks'],
//    "counts"     => $num_counts,
//    "fleet"      => $fleet
//);
//
//$navbar = $tmpl_o->get_template("navbar_utils", array("util-name"=> ucwords($_REQUEST['output'])." Report",
//    "release" => $cfg['sys_release'], "version" =>$cfg['sys_version'], "year"=>date("Y") ), array());
//
//$body = $tmpl_o->get_template($output['template'], $fields, $params);
//
//// assemble page
//$fields = array(
//    'tab-title'   => "raceMgr Event Report",
//    'styletheme'  => "sandstone_",
//    'page-navbar' => $navbar,
//    'page-title'  => $event['title'],
//    'page-main'   => $body,
//    'page-footer' => "&nbsp;",
//    'page-modals' => "&nbsp;",
//    'page-js'     => "&nbsp;"
//);
//$params = array();
//echo $tmpl_o->get_template("utils_page", $fields, array());
//
//function validate_entries()
//{
//    /*
//     *  Runs following checks
//     *    1 - juniors on board
//     *    2 - missing consent information
//     *    3 - missing emergency contact
//     *    4 - missing crew name for double hander
//     *    5 - missing gender info for helm or crew
//     *    6 - missing sail no.
//     *    7 - how many junior consents still required
//     *    rm_class - class not known to raceManager
//     *    rm_comp - competitor not known to racemanager
//     *
//     */
//    global $db_o, $entries;
//
//    foreach ($entries as $k=>$entry)
//    {
//        //echo "<pre>".print_r($entry,true)."</pre>";
//
//        $entry['crewnum'] > 1 ?  $doublehander = true : $doublehander = false;
//
//        // check 1
//        $entries[$k]['chk1'] = false;
//        $num_juniors = 0;
//        if (!empty($entry['h-age']) and $entry['h-age'] < 18) { $num_juniors++;}
//        if (!empty($entry['c-age']) and $entry['c-age'] < 18) { $num_juniors++;}
//        if ($num_juniors > 0)
//        {
//            $entries[$k]['chk1'] = $num_juniors." juniors";
//        }
//
//        // check 2
//        $entries[$k]['chk2'] = false;
//        if ($num_juniors > 0)  // check if we have consents
//        {
//            $num_consents = $db_o->run("SELECT count(*) as consents FROM e_consent WHERE entryid = ?
//                                        GROUP BY entryid", array($entry['id']) )->fetchColumn();
//            if ($num_consents < $num_juniors) { $entries[$k]['chk2'] = "missing consents"; };
//        }
//
//        // check 3
//        $entries[$k]['chk3'] = false;
//        if (empty($entry['h-emergency']) and empty($entry['c-emergency'])) {$entries[$k]['chk3'] = "missing emergency contact";}
//
//        // check 4
//        $entries[$k]['chk4'] = false;
//        if ($doublehander and (empty($entry['c-name']) or strtolower($entry['c-name'] == "tbc"
//                or strtolower($entry['c-name']) == "tba" or strtolower($entry['c-name']) == "tbd"))) {$entries[$k]['chk4'] = "missing crew name";}
//
//        // check 5
//        $entries[$k]['chk5'] = false;
//        if ($entry['h-gender'] == 'not given' or ($doublehander and $entry['c-gender'] == 'not given')) {$entries[$k]['chk5'] = "missing gender";}
//
//        // check 6
//        $entries[$k]['chk6'] = false;
//        if (empty($entry['b-sailno']) or strtolower($entry['b-sailno']) == "tbc"
//            or strtolower($entry['b-sailno']) == "tba" or strtolower($entry['b-sailno']) == "tbd") {$entries[$k]['chk6'] = "missing sail number";}
//
//        // check 7
//        $entries[$k]['chk7'] = false;
//        $num_consents_reqd = 0;
//        if (!empty($entry['h-age']) and $entry['h-age'] < 18) { $num_consents_reqd++; }
//        if (!empty($entry['c-age']) and $entry['c-age'] < 18) { $num_consents_reqd++; }
//        $num_consents = $db_o->run("SELECT count(*) as consents FROM e_consent WHERE entryid = ? GROUP BY entryid", array($entry['id']) )->fetchColumn();
//        $num_consents < $num_consents_reqd ? $entries[$k]['chk7'] = $num_consents_reqd - $num_consents." consents still reqd" : $entries[$k]['chk7'] = "";
//
//        // check rm_class
//        $entries[$k]['rm_class'] = false;
//        if (empty($entry['b-pn'])) {$entries[$k]['rm_class'] = true;}
//
//        // check rm_comp
//        $entries[$k]['rm_comp'] = false;
//        if (empty($entry['e-racemanager'])) {$entries[$k]['rm_comp'] = true;}
//    }
//
//    return $entries;
//}
//
//function get_report_config($output, $fleet)
//{
//    // options
//    $waiting = array(
//        "1" => "and `e-waiting` = 0",
//        "2" => "and `e-waiting` = 1",
//    );
//    $sort = array(
//        "1" => "order by `b-class` ASC, `b-sailno` * 1 ASC",
//        "2" => "order by `b-pn` DESC, `b-class` ASC, `b-sailno` * 1 ASC",
//        "3" => "order by `e-tally` ASC",
//        "4" => "order by `b-fleet` ASC, `b-class` ASC, `b-sailno` * 1 ASC",
//    );
//
//    if ($output == "reception")
//    {
////      Reception (sort: class/sailno, waiting: no, checks: false, template: reception_rept
//
//        $out_cfg = array(
//            "sort"     => $sort["1"],
//            "where"    => $waiting["1"],
//            "checks"   => false,
//            "template" => "reception_rept"
//        );
//    }
//    elseif ($output == "entrycheck")
//    {
////      EntryCheck (sort: 1 where:  checks: true template: entrycheck_rept
//        $fleet? $sort_option = 4 : $sort_option = 1;
//        $out_cfg = array(
//            "sort"     => $sort["1"],
//            "where"    => "",                      // checks both confirmed and waiting list boats
//            "checks"   => true,
//            "template" => "entrycheck_rept"
//        );
//    }
//    elseif ($output == "tallylist")
//    {
////      Tally (sort: 1 where: 1 checks: false template: tally_rept
//        $out_cfg = array(
//            "sort"     => $sort["1"],
//            "where"    => $waiting["1"],
//            "checks"   => false,
//            "template" => "tallylist_rept"
//        );
//    }
//    elseif ($output == "fleetlist")
//    {
////      Fleet (sort: 4 where: 1 checks: false template: tally_rept
//        $out_cfg = array(
//            "sort"     => $sort["4"],
//            "where"    => $waiting["1"],
//            "checks"   => false,
//            "template" => "fleetlist_rept"
//        );
//    }
//    else
//    {
//        $out_cfg = false;
//    }
//
//    return $out_cfg;
//}
//
//
//function get_entry_counts($eid)
//{
//    global $db_o, $output;
//
//    $num = array();
//
//    $sql_group = "SELECT `id`, `b-class`, `b-fleet`, `b-division`, count(*) as number FROM e_entry
//               WHERE eid = ? and `e-exclude` = 0 {$output['where']}
//               --setgroup-- ORDER BY number DESC";
//
//// get counts
//    $sql = str_replace("--setgroup--", "and `b-class` != '' and `b-class` is not null GROUP BY `b-class` ", $sql_group);
//    $num['class'] = $db_o->run($sql, array($eid) )->fetchall();
//
//    $sql = str_replace("--setgroup--", "and `b-fleet` != '' and `b-fleet` is not null GROUP BY `b-fleet` ", $sql_group);
//    $num['fleet'] = $db_o->run($sql, array($eid) )->fetchall();
//
//    $sql = str_replace("--setgroup--", "and `b-division` != '' and `b-division` is not null GROUP BY `b-division` ", $sql_group);
//    $num['division'] = $db_o->run($sql, array($eid) )->fetchall();
//
//    return $num;
//}

