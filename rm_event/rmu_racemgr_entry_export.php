<?php
/*
 * rmu_racemgr_entry_export.php
 *
 * Transfers confirmed entries held in rm_event (e_entry) to t_entry for the races (t_event) associated with the
 * open event.  Passes sailnumber and crewname as part of the entry.  Assumes entry records have been created using rm_admin
 *
 * // fixme - what if the clubname is wrong
 * // fixme - ultimately will want to create missing competitor records
 *
 * Loop through records in e_entry
 *   - if e-racemanager - check class, helm, club match  - report if mismatch
 *   - if !e-racemanager - search for class and helm match
 *   - update $entries with fix required
 * Endloop
 *
 * if errors and user selects update
 *   - make changes to records
 *   - create new records
 *   - rerun initial check
 *
 * if no errors
 *   - write entry records to z_entry
 *   - if successful - copy content to z_entry
 *
 */

$loc  = "..";
$page = "racemanager entry export";
define('BASE', dirname(__FILE__) . '/');
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$stylesheet = "./style/rm_utils.css";

error_reporting(E_ERROR);  //set for live operation to E_ERROR

require_once("../common/classes/db.php");
require_once("../common/lib/util_lib.php");
require_once("./include/rm_event_lib.php");
require_once("classes/template.php");

// initialise utility application
$cfg = u_set_config("../config/common.ini", array("rm_event"), true);
$cfg['rm_event'] = u_set_config("../config/rm_event.ini", array("rm_event"), true);
foreach($cfg['rm_event'] as $k => $v) { $cfg[$k] = $v; }
unset($cfg['rm_event']);
$cfg['logfile'] = str_replace("_date", date("_Y"), $cfg['logfile']);
if (array_key_exists("timezone", $cfg)) { date_default_timezone_set($cfg['timezone']); }

// connect to database (using PDO)
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// get club specific values
foreach ($db_o->getinivalues(true) as $k => $v) { $cfg[$k] = $v; }

// set templates
$tmpl_o = new TEMPLATE(array( "./templates/util_layouts_tm.php"));

// check pagestate
key_exists("pagestate", $_REQUEST) ? $pagestate = $_REQUEST['pagestate'] : $pagestate = "init";

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

// setup common fields for templates
$fields = array(
    'tab-title'   => "Transfer Entries to raceManager",
    'styletheme'  => "sandstone_",
    'page-navbar' => $navbar,
    'page-title'  => $event['title'],
    'page-footer' => "&nbsp;",
    'page-modals' => "&nbsp;",
    'page-js'     => "&nbsp;"
);

// setup navbar for output
$navbar = $tmpl_o->get_template("navbar_utils", array("util-name"=> "Transfer Entries to raceManager",
    "release" => $cfg['sys_release'], "version" =>$cfg['sys_version'], "year"=>date("Y") ), array());

$entries = get_confirmed_entries($eid);
$num_entries = count($entries);

$entries = validate_entries();

//$problems = mark_problem_records();


if($pagestate == "init")
{

// setup top level description
    $fields['page-main'] = $tmpl_o->get_template("rm_export_form", array(), array("eid" => $eid, "entries" => $entries, "problems" => $problems));

// assemble page
    echo $tmpl_o->get_template("utils_page", $fields, array());
}

elseif ($pagestate == "submit")
{
    if ($num_entries > 0)
    {
        // empty temporary data table z_entry
        $trunc = $db_o->run("TRUNCATE z_entry", array() );

        $num_entered = 0;
        $rept = "";
        foreach ($entries as $k=>$entry)
        {
            $boat = $entry['b-class']." ".$entry['b-sailno'];

            if ($entry['rs_class'])
            {
                $rept.= "$boat - NOT ENTERED - class not known to raceManager<br>";
            }
            else
            {
                $chg_club = false;
                if ($entry['rs_club'])  // we have a mismatch on club name for this competitor  - so we should update it
                {
                    $query = "UPDATE t_competitor SET club = ? WHERE `id` = XXXX";
                    $upd = $db_o->run($query, array($xxx));    // fixme - only use club up to a / delimiter
                    $chg_club = true
                }

                if ($entry['rs_comp'])     // we don't have a competitor record - so we only need to create an entry record
                {
                    $query = create_competitor_record($entry);
                    $add_comp = $db_o->run($query, array()); // fixme  - maybe do this in the function

                    if ($add_comp)
                    {
                        $query = create_entry_record($entry);
                        $add = $db_o->run($query, array());    // fixme  - maybe do this in the function

                        if ($add)
                        {
                            $num_entered++;
                            $chg_club ? $rept.= "$boat - ENTERED - club name updated<br>" : $rept.= "$boat - ENTERED<br>";
                        }
                        else
                        {
                            // not entered message
                        }
                    }
                }
                else                         // we only need to create the entry record
                {
                    $query = create_entry_record($entry);
                    $add = $db_o->run($query, array());    // fixme  - maybe do this in the function
                    // error handling

                    if ($add)
                    {
                        $num_entered++;
                        $chg_club ? $rept.= "$boat - ENTERED - club name updated<br>" : $rept.= "$boat - ENTERED<br>";
                    }
                    else
                    {
                        // not entered message
                    }
                }

            }
        }
    }




}

else
{
    echo "exit nicely error message - pagestate not recognised";  // FIXME -  change to exit_nicely
    exit("script stopped");
}

function get_confirmed_entries($eid)
{
    global $db_o;
    $sql = "SELECT a.`id`, `b-class`, `b-sailno`, `b-altno`, `b-pn`, `b-fleet`, `b-division`, 
               `h-name`, `h-club`, `h-age`, `h-gender`, `h-emergency`,
               `c-name`, `c-age`, `c-gender`, `c-emergency`, 
               `e-tally`, `e-racemanager`, `e-waiting`, b.crew as crewnum 
               FROM e_entry as a LEFT JOIN t_class as b ON a.`b-class`= b.classname 
               WHERE eid = ? and `e-exclude` = 0 and `e-waiting` = 0 
               ORDER BY `b-fleet` ASC, `b-class` ASC, `b-sailno` * 1 ASC";

    $entries = $db_o->run($sql, array($eid) )->fetchall();

    return $entries;
}

//function mark_problem_records()
//{
//    global $db_o, $entries;
//
//    $problems = array();
//
//    foreach ($entries as $k=>$entry)
//    {
//        $boat = $entry['b-class']." ".$entry['b-sailno'];
//        $team = $entry['h-name']." / ".$entry['c-name']." ".$entry['h-club'];
//
//        // if no matching class in racemanager   (cannot fix - needs manual fix)
//        if ($entry['rm_class'])
//        {
//            $entries[$k]['problem'][] = array("id" => $k, "type"=> "class", "boat"=>$boat, "team"=>$team, "fixable"=>false);
//        }
//
//        // if there is no matching competitor in racemanager (can fix - create record)
//        if ($entry['rm_comp'])
//        {
//            $entries[$k]['problem'][] = array("id" => $k, "type"=> "comp", "boat"=>$boat, "team"=>$team, "fixable"=>true);
//        }
//        else   // if club does not match competitor record (can fix - change club name)
//        {
//            $comp = $db_o->run("SELECT * FROM t_competitor WHERE id = ?", array($entry['e-racemanager']) )->fetch();
//
//            if ($entry['h-club'] != $comp['club'])
//            {
//                $entries[$k]['problem'][] = array("id" => $k, "type"=> "club", "boat"=>$boat, "team"=>$team, "fixable"=>true);
//            }
//        }
//    }
//
//    return $problems;
//}





    // ----------------------------------------------------------------------------------------------------------------------
    // ----------------------------------------------------------------------------------------------------------------------

//$pagefields = array(
//    "loc"         => $loc,
//    "page-theme-utils" => $cfg['theme_utils'],
//    "stylesheet"  => $stylesheet,
//    "page-title"  => $cfg['sys_name'],
//    "page-navbar" => $tmpl_o->get_template("navbar_utils", array("util-name"=>$page, "version"=>$cfg['sys_version'], "year" => date("Y")), array()),
//    "page-footer" => "",
//    "page-modals" => "",
//    "page-js" => "",
//);
//
//
//
//
//if ($pagestate == "init")
//{
//    // this state will provide instructions and then list any issues with the confirmed entries
//    // the user can choose to sort the issues manually or try to do them automatically
//
//
//
//    if (!$eid)
//    {
//
//    }
//    else
//    {
//        $formfields = array(
//            "function"     => "Imports event entries into the RACEMANAGER entries table",
//            "instructions" => "Select mode (recommended to use dryrun first), and reporting level",
//            "script"       => "rmu_racemanager_entry_export.php?pagestate=submit&eid=$eid",
//        );
//
//        $pagefields['page-main'] = $tmpl_o->get_template("racemgr_export_form", $formfields, array("action" => true, "mode" => $mode, "include" => $include));
//        $pagefields['page-footer'] = $tmpl_o->get_template("footer_utils", array("footer-left"=>"Select options, and click Transfer Entries button ...", "footer-center"=>"", "footer-right"=>""), array("footer"=>true));
//
//    }
//}
//elseif ($pagestate == "submit")
//{
//
//}
//else // pagestate not recognised
//{
//    $formfields = array(
//        "problem"  => "pagestate not recognised",
//        "file"     => __FILE__,
//        "line"     => __LINE__,
//        "evidence" => "pagestate = |$pagestate|",
//    );
//    $params['action'] = "close browser window and try again";
//    $pagefields['page-main'] = $tmpl_o->get_template("error_report", $formfields, array());
//    $pagefields['page-footer'] = $tmpl_o->get_template("footer_utils", array("footer-left"=>"", "footer-center"=>"",
//        "footer-right"=>"close this browser tab to return"), array("footer"=>true));
//}
//
//
//
//
//
//
//
//// arguments
//$eid = set_eventid($_REQUEST);                         // gets event id in e_event
//$mode = set_mode($_REQUEST);                           // mode - dryrun or process
//$report_level = set_report_level($_REQUEST);           // reporting summary = 1, newcomp = 2, detail = 3
//
//// ---------------------- get event record ----------------------
//$sql = "SELECT * FROM e_event WHERE id = ?";
//$event = $db_o->run($sql, array($eid, ) )->fetch();
//$races = set_target_races ($event['races']);           // returns list of racemanager event ids as an array
//
//if (!$event)
//{
//    exit("Sorry - could not find the specified event in rm_event ... STOPPING");  // FIXME -  change to exit_nicely
//}
//
//// ---------------------- get entries ------------------------------
//
//$sql = "SELECT * FROM e_entry WHERE eid = ? and `e-exclude` = 0 and `e-waiting` = 0 ORDER BY `b-class` ASC, `b-sailno` * 0 ASC";
//$entries = $db_o->run($sql, array($eid, ) )->fetchall();
//
//if (count($entries) <= 0)
//{
//    exit("Sorry - no entries found for specified event ... STOPPING ");  // FIXME -  change to exit_nicely
//}
//
//// clear z_entry_draft table
//if ($mode == "process")
//{
//    foreach ($races as $race)
//    {
//        // empty database table for entries
//        $trunc = $db_o->run("TRUNCATE z_entry_draft", array());
//        if (!$trunc)
//        {
//            exit("Sorry - failed to empty table z_entry_draft ... STOPPING");
//        }
//    }
//}
//
//// ----------------------- process entries-------------------------------
//
//$entry_num = 0;
//$entries_made = 0;
//report(1,"<h2>Entry Transfer Report - {$event['title']}</h2>");
//foreach ($entries as $k => $entry)
//{
//    //entry delimiter
//    $boat = "{$entry['b-class']} {$entry['b-sailno']} {$entry['h-name']} (id: {$entry['id']})";
//    $entry_num++;
//    report(1,"-------------------------------------------------------------------------ENTRY $entry_num <b>[$boat]</b>");
//
//    // check we have minimum info to process entry record
//    $val = validate_entry($entry);
//
//
//        if (!$val['class']) {
//            $missing .= "class | ";
//        }
//        if (!$val['sailno']) {
//            $missing .= "sail no. | ";
//
//
//
//
//
//
//
//
//
//
//        }
//        if (!$val['helm']) {
//            $missing .= "helm name | ";
//        }
//        rtrim($missing, "|");
//        report(1, "$boat - insufficient data to process [missing: $missing] ... moving to next entry");
//        continue;
//    }
//
//    // check class  - query tests match with no spaces;
//    $class = get_class_detail($entry['b-class']);
//    if ($class)
//    {
//        $entries[$k]['b-class'] = $class['classname'];
//    }
//    else
//    {
//        // can't proceed no match for class - stop processing
//        exit("Sorry - can't find matching class - stopping processing ");
//    }
//
//
//    // get competitor record for entry (either existing record or new record
//    $entry['e-racemanager'] ? $compid = $entry['e-racemanager'] : $compid = 0;
//    {
//        $competitor = get_competitor($compid, $entry, $class);
//
//        if ($competitor)
//        {
//            // set entry record for each race
//            $entry_id = set_entry_record($event['id'], $races, $competitor);
//
//            if ($entry_id)
//            {
//                $entries_made++;
//                report(3, "entry created - {$class['classname']} {$entry['b-sailno']} {$entry['h-name']} / {$entry['c-name']}");
//            }
//            else
//            {
//                exit("Sorry - unable to create record - - [ $boat ] ...  STOPPING");
//            }
//        }
//        else
//        {
//            exit("Sorry - finding/creating competitor record failed - [ $boat ] ...  STOPPING");
//        }
//    }
//
//}
//
//report(2,"<br><br>========================== PROCESSING COMPLETE - $entries_made entries added =====================");
//
//
//
//
//
//
//
//function report($level, $txt)
//{
//    global $report_level;
//
//    if ($level <= $report_level) { echo $txt."<br>"; }
//}
//
//function validate_entry($entry)
//{
//    /*
//     *  Runs following checks
//
//     */
//    global $db_o;
//
//    $val = array("data_complete" => false, "class" => true, "sailno" => true, "helm" => true);
//    // check 1 - we have data for class, sailno and helm name
//    if (empty($entry['b-class'])) { $val['class'] = false; }
//    if (empty($entry['b-sailno'])) { $val['sailno'] = false; }
//    if (empty($entry['h-name'])) { $val['helm'] = false; }
//
//    if ($val['class'] and $val['sailno'] and $val['helm']) { $val['data_complete'] = true;}
//
//    return $val;
//}

//function set_report_level()
//{
//    if (key_exists('report', $_REQUEST))          // summary|newcomp|detail
//    {
//        if ($_REQUEST['report'] != "summary" and $_REQUEST['mode'] != "newcomp" and $_REQUEST['mode'] != "detail" )
//        {
//            $report = "3";
//        }
//        else
//        {
//            if ($_REQUEST['report'] == "summary")
//            {
//                $report = 1;
//            }
//            elseif ($_REQUEST['report'] == "newcomp")
//            {
//                $report = 2;
//            }
//            elseif ($_REQUEST['report'] == "detail")
//            {
//                $report = 3;
//            }
//        }
//    }
//    else
//    {
//        $report = "3";
//    }
//
//    return $report;
//}

//function set_mode()
//{
//    if (key_exists('mode', $_REQUEST))
//    {
//        if ($_REQUEST['mode'] != "dryrun" and $_REQUEST['mode'] != "process")
//        {
//            $mode = "dryrun";
//        }
//        else
//        {
//            $mode = $_REQUEST['mode'];
//        }
//    }
//    else
//    {
//        $mode = "dryrun";
//    }
//    return $mode;
//}

//function set_eventid()
//{
//    global $db_o;
//
//    $eid = 0;
//    if (key_exists("eid", $_REQUEST))
//    {
//        $eid = $_REQUEST['eid'];
//        $event = $db_o->run("SELECT * FROM e_event WHERE id = ?", array($_REQUEST['eid']) )->fetch();
//        if ($event) { $eid = $event['id']; }
//    }
//    elseif (key_exists("event", $_REQUEST))
//    {
//        $event = $db_o->run("SELECT * FROM e_event WHERE nickname = ?", array($_REQUEST['event']) )->fetch();
//        if ($event) { $eid = $event['id']; }
//    }
//
//
//    return $eid;
//}
//
//function set_target_races($races)
//{
//    if ($races)
//    {
//        $target_list = explode(",",str_replace(' ', '', $races));
//    }
//    else
//    {
//        exit("Sorry - no raceManager races have been associated with this event ... STOPPING");
//    }
//
//    return $target_list;
//}
//
//function get_competitor($compid, $entry, $class)
//{
//    global $db_o;
//    global $entry_num;
//
//    $comp_out = array();
//    if ($compid > 0)          // we have a racemanager competitor id
//    {
//        $sql = "SELECT * FROM `t_competitor` WHERE `id` = ? and `active` = 1";
//        $comp = $db_o->run($sql, array($compid))->fetch();
//        if ($comp)
//        {
//            report(2,"Competitor found via e-racemanager value - {$comp['id']}");
//            $match = check_comp_match($comp, $entry, $class);
//
//            if ($match['class'] and $match['helm'])
//            {
//                $comp_out = $comp;
//                $comp_out['sailnum'] = $entry['b-sailno'];
//                $comp_out['crew'] = $entry['c-name'];
//            }
//        }
//    }
//
//    if (empty($comp_out))            // check classname, sail no, helm name and
//    {
//        $name = preg_replace('/\s+/', ' ', $entry['h-name']);   // remove multiple spaces
//        $names = explode(" ", $name);
//        $surname = $names[1];
//        $comp = $db_o->run("SELECT * FROM t_competitor WHERE `classid` = ? AND (`helm` LIKE ? or `helm` LIKE ?)
//                     ORDER BY `createdate` DESC LIMIT 1", array($class['id'],"%{$entry['h-name']}%", "%$surname%") )->fetch();
//
//        if ($comp)
//        {
//            report(2,"Competitor found via search on transfer - {$comp['id']}");
//            $match = check_comp_match($comp, $entry, $class);
//
//            if ($match['class'] and $match['helm'])
//            {
//                $comp_out = $comp;
//                $comp_out['sailnum'] = $entry['b-sailno'];
//                $comp_out['crew'] = $entry['c-name'];
//            }
//
//        }
//        else // need to create a new competitor record from entry
//        {
//            $args = array("classid" => $class['id'], "boatnum" => $entry['b-sailno'], "sailnum" => $entry['b-sailno'],
//                "boatname" => $entry['b-name'], "helm" => $entry['h-name'], "crew" => $entry['c-name'],
//                "club" => $entry['h-club'], "regular" => 0, "active" => 1, "updby" => "event-".$entry['eid']);
//
//            $new_comp_id = $db_o->insert("t_competitor", $args);
//            report(2,"New competitor record created - $new_comp_id");
//
//            if ($new_comp_id)
//            {
//                $comp_out = $db_o->run("SELECT * FROM t_competitor WHERE `id` = ?", array($new_comp_id))->fetch();
//            }
//            else
//            {
//                report(2,"New competitor record  - insert FAILED");
//                exit("Sorry - unable to create competitor record [{$class['classname']} {$entry['b-sailno']} {$entry['h-name']}] - stopping");
//            }
//        }
//    }
//
//    return $comp_out;
//}
//
//function set_entry_record($eventid, $races, $competitor)
//{
//    global $db_o;
//
//    $status = true;
//    foreach ($races as $race)
//    {
//        $args = array("action"=>'enter',"protest"=>0,"status"=>'N',"eventid"=>$race,"competitorid"=>$competitor['id'],
//            "chg-crew"=>$competitor['crew'],"chg-sailnum"=>$competitor['sailnum'],"updby"=>"rm_event_$eventid");
//
//        $entryid = $db_o->insert("z_entry_draft", $args);
//
//        if(!$entryid)
//        {
//            report(2,"New event entry  - insert FAILED");
//            exit("Sorry - failed to create entry for race $race: competitor {$competitor['id']} - stopping processing");
//        }
//        else
//        {
//            report(2,"New event entry created - $entryid");
//        }
//    }
//
//    return $status;
//}
//
//function check_comp_match($comp, $entry, $class)
//{
//    $match = array("class" => true, "sailno" => true, "helm" => true, "crew" => true );
//    if (strtolower(str_replace(' ', '', $class['classname'])) != strtolower(str_replace(' ', '', $entry['b-class'])))
//    {
//        $match['class'] = false;
//    }
//
//    if (strtolower(str_replace(' ', '', $comp['sailnum'])) != strtolower(str_replace(' ', '', $entry['b-sailno'])))
//    {
//        $match['b-sailno'] = false;
//    }
//
//    $lastword_comp = strtolower(array_slice(explode(' ', rtrim($comp['helm'])), -1)[0]);
//    $lastword_entry = strtolower(array_slice(explode(' ', rtrim($entry['h-name'])), -1)[0]);
//
//    if ($lastword_comp != $lastword_entry)
//    {
//        $match['helm'] = false;
//    }
//
//    if ($class['crew'] > 1)
//    {
//        $lastword_comp = strtolower(array_slice(explode(' ', rtrim($comp['crew'])), -1)[0]);
//        $lastword_entry = strtolower(array_slice(explode(' ', rtrim($entry['c-name'])), -1)[0]);
//
//        if ($lastword_comp != $lastword_entry)
//        {
//            $match['crew'] = false;
//        }
//    }
//    return $match;
//}
