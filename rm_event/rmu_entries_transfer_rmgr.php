<?php
/*
 * Transfers entries held in rm_event (e_entry) to t_entry for the races (t_event) associated with the
 * open event.
 *
 * If necessary creates new competitor records.
 *
 * It assumes that entry records in e_entry are complete and correct and that all boat classes
 * occurring in the t_entry records exist in t_class with identical names
 */

// start session
session_id('sess-rmuevent');
session_start();

error_reporting(E_ALL);  //set for live operation to E_ERROR

require_once("../common/classes/db.php");
require_once("../common/lib/rm_event_lib.php");
require_once("classes/template.php");


// initialise application
$cfg = set_config("../config/rm_event.ini", array("rm_event"), true);
$cfg['logfile'] = str_replace("_date", date("_Y"), $cfg['logfile']);

$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);
$tmpl_o = new TEMPLATE(array( "./templates/util_layouts_tm.php"));

if (empty($_REQUEST['access']) OR $_REQUEST['access'] != $cfg['access_key'])
{
    exit("Sorry - you are not authorised to use this script ... STOPPING");  // FIXME -  change to exit_nicely
}

// arguments
$eid = set_eventid($_REQUEST);                         // gets event id in e_event
$mode = set_mode($_REQUEST);                           // mode - dryrun or process
$report_level = set_report_level($_REQUEST);           // reporting summary = 1, newcomp = 2, detail = 3

// ---------------------- get event record ----------------------
$sql = "SELECT * FROM e_event WHERE id = ?";
$event = $db_o->run($sql, array($eid, ) )->fetch();
$races = set_target_races ($event['races']);           // returns list of racemanager event ids as an array

if (!$event)
{
    exit("Sorry - could not find the specified event in rm_event ... STOPPING");  // FIXME -  change to exit_nicely
}

// ---------------------- get entries ------------------------------

$sql = "SELECT * FROM e_entry WHERE eid = ? and `e-exclude` = 0 and `e-waiting` = 0 ORDER BY `b-class` ASC, `b-sailno` * 0 ASC";
$entries = $db_o->run($sql, array($eid, ) )->fetchall();

if (count($entries) <= 0)
{
    exit("Sorry - no entries found for specified event ... STOPPING ");  // FIXME -  change to exit_nicely
}

// clear t_entry_draft table
if ($mode == "process")
{
    foreach ($races as $race)
    {
        // empty database table for entries
        $trunc = $db_o->run("TRUNCATE t_entry_draft", array());
        if (!$trunc)
        {
            exit("Sorry - failed to empty table t_entry_draft ... STOPPING");
        }
    }
}

// ----------------------- process entries-------------------------------

$entry_num = 0;
$entries_made = 0;
report(1,"<h2>Entry Transfer Report - {$event['title']}</h2>");
foreach ($entries as $k => $entry)
{
    //entry delimiter
    $boat = "{$entry['b-class']} {$entry['b-sailno']} {$entry['h-name']} (id: {$entry['id']})";
    $entry_num++;
    report(1,"-------------------------------------------------------------------------ENTRY $entry_num <b>[$boat]</b>");

    // check we have minimum info to process entry record
    $val = validate_entry($entry);

    if (!$val['data_complete']) {
        $missing = "";
        if (!$val['class']) {
            $missing .= "class | ";
        }
        if (!$val['sailno']) {
            $missing .= "sail no. | ";
        }
        if (!$val['helm']) {
            $missing .= "helm name | ";
        }
        rtrim($missing, "|");
        report(1, "$boat - insufficient data to process [missing: $missing] ... moving to next entry");
        continue;
    }

    // check class  - query tests match with no spaces;
    $class = get_class_detail($entry['b-class']);
    if ($class)
    {
        $entries[$k]['b-class'] = $class['classname'];
    }
    else
    {
        // can't proceed no match for class - stop processing
        exit("Sorry - can't find matching class - stopping processing ");
    }


    // get competitor record for entry (either existing record or new record
    $entry['e-racemanager'] ? $compid = $entry['e-racemanager'] : $compid = 0;
    {
        $competitor = get_competitor($compid, $entry, $class);

        if ($competitor)
        {
            // set entry record for each race
            $entry_id = set_entry_record($event['id'], $races, $competitor);

            if ($entry_id)
            {
                $entries_made++;
                report(3, "entry created - {$class['classname']} {$entry['b-sailno']} {$entry['h-name']} / {$entry['c-name']}");
            }
            else
            {
                exit("Sorry - unable to create record - - [ $boat ] ...  STOPPING");
            }
        }
        else
        {
            exit("Sorry - finding/creating competitor record failed - [ $boat ] ...  STOPPING");
        }
    }

}

report(2,"<br><br>========================== PROCESSING COMPLETE - $entries_made entries added =====================");


function report($level, $txt)
{
    global $report_level;

    if ($level <= $report_level) { echo $txt."<br>"; }
}

function validate_entry($entry)
{
    /*
     *  Runs following checks

     */
    global $db_o;

    $val = array("data_complete" => false, "class" => true, "sailno" => true, "helm" => true);
    // check 1 - we have data for class, sailno and helm name
    if (empty($entry['b-class'])) { $val['class'] = false; }
    if (empty($entry['b-sailno'])) { $val['sailno'] = false; }
    if (empty($entry['h-name'])) { $val['helm'] = false; }

    if ($val['class'] and $val['sailno'] and $val['helm']) { $val['data_complete'] = true;}

    return $val;
}

function set_report_level()
{
    if (key_exists('report', $_REQUEST))          // summary|newcomp|detail
    {
        if ($_REQUEST['report'] != "summary" and $_REQUEST['mode'] != "newcomp" and $_REQUEST['mode'] != "detail" )
        {
            $report = "3";
        }
        else
        {
            if ($_REQUEST['report'] == "summary")
            {
                $report = 1;
            }
            elseif ($_REQUEST['report'] == "newcomp")
            {
                $report = 2;
            }
            elseif ($_REQUEST['report'] == "detail")
            {
                $report = 3;
            }
        }
    }
    else
    {
        $report = "3";
    }

    return $report;
}

function set_mode()
{
    if (key_exists('mode', $_REQUEST))
    {
        if ($_REQUEST['mode'] != "dryrun" and $_REQUEST['mode'] != "process")
        {
            $mode = "dryrun";
        }
        else
        {
            $mode = $_REQUEST['mode'];
        }
    }
    else
    {
        $mode = "dryrun";
    }
    return $mode;
}

function set_eventid()
{
    global $db_o;

    $eid = 0;
    if (key_exists("eid", $_REQUEST))
    {
        $eid = $_REQUEST['eid'];
        $event = $db_o->run("SELECT * FROM e_event WHERE id = ?", array($_REQUEST['eid']) )->fetch();
        if ($event) { $eid = $event['id']; }
    }
    elseif (key_exists("event", $_REQUEST))
    {
        $event = $db_o->run("SELECT * FROM e_event WHERE nickname = ?", array($_REQUEST['event']) )->fetch();
        if ($event) { $eid = $event['id']; }
    }

    if (!$eid)
    {
        exit("Sorry - we can't find the event you requested [{$_REQUEST['eid']} : {$_REQUEST['event']}] ... STOPPING");
    }
    return $eid;
}

function set_target_races($races)
{
    if ($races)
    {
        $target_list = explode(",",str_replace(' ', '', $races));
    }
    else
    {
        exit("Sorry - no raceManager races have been associated with this event ... STOPPING");
    }

    return $target_list;
}

function get_competitor($compid, $entry, $class)
{
    global $db_o;
    global $entry_num;

    $comp_out = array();
    if ($compid > 0)          // we have a racemanager competitor id
    {
        $sql = "SELECT * FROM `t_competitor` WHERE id = ? and active = 1";
        //echo "<pre>$sql</pre>";
        $comp = $db_o->run($sql, array($compid))->fetch();
        if ($comp)
        {
            report(2,"Competitor found via e-racemanager value - {$comp['id']}");
            $match = check_comp_match($comp, $entry, $class);
            //echo "<pre>".print_r($match,true)."</pre>";
            if ($match['class'] and $match['helm'])
            {
                $comp_out = $comp;
                $comp_out['sailnum'] = $entry['b-sailno'];
                $comp_out['crew'] = $entry['c-name'];
            }
        }
    }

    if (empty($comp_out))            // check classname, sail no, helm name and
    {
        $name = preg_replace('/\s+/', ' ', $entry['h-name']);   // remove multiple spaces
        $names = explode(" ", $name);
        $surname = $names[1];
        $comp = $db_o->run("SELECT * FROM t_competitor WHERE classid = ? AND (helm LIKE ? or helm LIKE ?) 
                     ORDER BY createdate DESC LIMIT 1", array($class['id'],"%{$entry['h-name']}%", "%$surname%") )->fetch();
//        if ($entry_num == 41 )
//        {
//            echo "<pre>$surname|{$class['id']}|{$entry['h-name']}</pre>";
//            echo "<pre>NAMES".print_r($names,true)."</pre>";
//            echo "<pre>COMP".print_r($comp,true)."</pre>";
//            echo "<pre>ENTRY".print_r($entry,true)."</pre>";
//            echo "<pre>CLASS".print_r($class,true)."</pre>";
//        }

        if ($comp)
        {
            report(2,"Competitor found via search on transfer - {$comp['id']}");
            $match = check_comp_match($comp, $entry, $class);
            //echo "<pre>".print_r($match,true)."</pre>";
            if ($match['class'] and $match['helm'])
            {
                $comp_out = $comp;
                $comp_out['sailnum'] = $entry['b-sailno'];
                $comp_out['crew'] = $entry['c-name'];
            }

        }
        else // need to create a new competitor record from entry
        {
            $args = array("classid" => $class['id'], "boatnum" => $entry['b-sailno'], "sailnum" => $entry['b-sailno'],
                "boatname" => $entry['b-name'], "helm" => $entry['h-name'], "crew" => $entry['c-name'],
                "club" => $entry['h-club'], "regular" => 0, "active" => 1, "updby" => "event-".$entry['eid']);

            $new_comp_id = $db_o->insert("t_competitor", $args);
            report(2,"New competitor record created - $new_comp_id");

            if ($new_comp_id)
            {
                $comp_out = $db_o->run("SELECT * FROM t_competitor WHERE id = ?", array($new_comp_id))->fetch();
            }
            else
            {
                report(2,"New competitor record  - insert FAILED");
                exit("Sorry - unable to create competitor record [{$class['classname']} {$entry['b-sailno']} {$entry['h-name']}] - stopping");
            }
        }
    }

    return $comp_out;
}

function set_entry_record($eventid, $races, $competitor)
{
    global $db_o;

    $status = true;
    foreach ($races as $race)
    {
        $args = array("action"=>'enter',"protest"=>0,"status"=>'N',"eventid"=>$race,"competitorid"=>$competitor['id'],
            "chg-crew"=>$competitor['crew'],"chg-sailnum"=>$competitor['sailnum'],"updby"=>"rm_event_$eventid");

        $entryid = $db_o->insert("t_entry_draft", $args);

        if(!$entryid)
        {
            report(2,"New event entry  - insert FAILED");
            exit("Sorry - failed to create entry for race $race: competitor {$competitor['id']} - stopping processing");
        }
        else
        {
            report(2,"New event entry created - $entryid");
        }
    }

    return $status;
}

function check_comp_match($comp, $entry, $class)
{
    $match = array("class" => true, "sailno" => true, "helm" => true, "crew" => true );
    if (strtolower(str_replace(' ', '', $class['classname'])) != strtolower(str_replace(' ', '', $entry['b-class'])))
    {
        $match['class'] = false;
    }

    if (strtolower(str_replace(' ', '', $comp['sailnum'])) != strtolower(str_replace(' ', '', $entry['b-sailno'])))
    {
        $match['b-sailno'] = false;
    }

    $lastword_comp = strtolower(array_slice(explode(' ', rtrim($comp['helm'])), -1)[0]);
    $lastword_entry = strtolower(array_slice(explode(' ', rtrim($entry['h-name'])), -1)[0]);

    if ($lastword_comp != $lastword_entry)
    {
        $match['helm'] = false;
    }

    if ($class['crew'] > 1)
    {
        $lastword_comp = strtolower(array_slice(explode(' ', rtrim($comp['crew'])), -1)[0]);
        $lastword_entry = strtolower(array_slice(explode(' ', rtrim($entry['c-name'])), -1)[0]);

        if ($lastword_comp != $lastword_entry)
        {
            $match['crew'] = false;
        }
    }
    return $match;
}