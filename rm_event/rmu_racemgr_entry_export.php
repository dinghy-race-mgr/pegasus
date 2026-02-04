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

require_once ("{$loc}/common/classes/db.php");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_event_lib.php");
require_once ("{$loc}/common/classes/template_class.php");

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
    $races = explode(",", $event['races']);
    $num_races = count($races);
}

// check waiting argument
if (key_exists("waiting", $_REQUEST))
{
    $inc_waiting = filter_var($_REQUEST['waiting'], FILTER_VALIDATE_BOOLEAN);
}

if (!isset($eid) or empty($event))
{
    echo "exit nicely error message - event not found";  // FIXME -  change to exit_nicely
    exit("script stopped");
}

// setup common fields for templates
$navbar = $tmpl_o->get_template("navbar_utils", array("util-name"=> "Transfer Entries to raceManager",
    "release" => $cfg['sys_release'], "version" =>$cfg['sys_version'], "year"=>date("Y") ), array());

$fields = array(
    'tab-title'   => "raceMgr Transfer",
    'styletheme'  => $cfg['theme_utils'],
    'page-navbar' => $navbar,
    'page-title'  => $event['title'],
    'page-footer' => "&nbsp;",
    'page-modals' => "&nbsp;",
    'page-js'     => "&nbsp;"
);

// get and validate entry data
$entries = get_required_entries($eid, $inc_waiting);
$num_entries = count($entries);
$entries = validate_entries();

if($pagestate == "init")
{

// setup top level description
    $fields['page-main'] = $tmpl_o->get_template("rm_export_form", array("event-title"=>$event['title']),
        array("eid" => $eid, "entries" => $entries));

// assemble page
    echo $tmpl_o->get_template("utils_page", $fields, array());
}

elseif ($pagestate == "submit")
{
    $audit = array();
    $totals = array("entries" => $num_entries, "races" => $num_races, "registered" => 0, "entered" => 0, "updated" => 0 );

    if ($num_entries > 0)
    {
        // empty temporary data table z_entry
        $trunc = $db_o->run("TRUNCATE z_entry", array() );

        $num_registered = 0;
        $num_entered = 0;
        $num_club_update = 0;
        $error = false;

        foreach ($entries as $k=>$entry)
        {
            //$icount++;
            //echo "<pre>START".print_r($entry,true)."</pre>";
            $boat = $entry['b-class']." ".$entry['b-sailno'];
            $audit[$k] = array(
                "boat"    => $boat,
                "class"   => $entry['rm_class'],          // unknown class - needs manually correcting
                "comp"    => $entry['rm_comp'],           // unknown competitor - needs new competitor record creating
                "club"    => "",                          // club update needed
                "comp_Y"  => 0,                           // id for new competitor record - 0 if not set/failed
                "entry_Y" => 0,                           // competitor id added to e_entry table (e-racemanager] - 0 not set/failed
                "info"    => "",                          // info confirming entry
            );

            if ($entry['rm_class'])                            // don't know class - can't add entry to RM
            {
                $error = true;
            }
            else                                               // we can add entry to RM - creating new competitor record if required
            {
                if ($entry['rm_comp'])     // we don't have a competitor record - so we need to create one
                {
                    $comp = create_competitor_record($boat, $entry);

                    if ($comp['compid'])      // we have a RM competitor id - update the e_entry record
                    {
                        $num_registered++;
                        $audit[$k]['comp_Y'] = $comp['compid'];

                        // add competitor id to e_entry record
                        $upd = add_rmid_to_entrytable($entry['id'], $comp['compid']);
                        $entry['e-racemanager'] = $comp['compid'];
                        $audit[$k]['entry_Y'] = $comp['compid'];
                    }
                    else
                    {
                        $error = true;
                    }
                }
                else                                             // we have an existing record - so just check if club needs to be updated
                {
                    $status = update_club_name($entry);
                    if ($status) { $num_club_update++; }
                }

                if (!$error)
                {
                    // we have a complete competitor record  - now create the record(s) for entry into the event race(s)
                    $rst = create_entry_record($boat, $entry, $races);
                    if ($rst['success']) { $num_entered++; }
                    $audit[$k]['entry_Y'] = $rst['id'];
                    $audit[$k]['info'] = $rst['info'];
                }
            }
        }
    }

    // Summary reporting
    $totals["registered"] = $num_registered;
    $totals["entered"] = $num_entered;
    $totals["updated"] = $num_club_update;

    // setup top level description
    $fields['page-main'] = $tmpl_o->get_template("rm_export_report", array("event-title"=>$event['title']),
        array("eid" => $eid, "audit" => $audit, "totals" => $totals));

    // assemble page
    echo $tmpl_o->get_template("utils_page", $fields, array());
}
elseif ($pagestate == "commit")
{
    $num_entered = $_REQUEST['entered'];

    copy_entries($races);

    // setup top level description
    $fields['page-main'] = $tmpl_o->get_template("rm_export_confirm", array("event-title"=>$event['title'], "num_entered" =>$num_entered), array());

    // assemble page
    echo $tmpl_o->get_template("utils_page", $fields, array());
}

else
{
    echo "exit nicely error message - pagestate not recognised";  // FIXME -  change to exit_nicely
    exit("script stopped");
}

function get_required_entries($eid, $inc_waiting)
{
    global $db_o;

    $inc_waiting ? $waiting = "" : $waiting = "and `e-waiting` = 0 " ;

    $sql = "SELECT a.id, `b-class`, `b-sailno`, `b-altno`, `b-pn`, `b-fleet`, `b-division`, `h-name`, `h-club`, 
               `h-age`, `h-gender`, `h-emergency`, `c-name`, `c-age`, `c-gender`, `c-emergency`, `e-tally`, 
               `e-racemanager`, `e-waiting`, b.crew as crewnum
               FROM e_entry as a LEFT JOIN t_class as b ON a.`b-class`= b.classname
               WHERE a.eid = ? AND `e-exclude` = 0 $waiting
               ORDER BY `b-fleet` ASC, `b-class` ASC, `b-sailno` * 1 ASC";
    $entries = $db_o->run($sql, array($eid) )->fetchall();
    return $entries;
}

function create_competitor_record($boat, $entry)
{
    global $db_o;

    $status = array("success"=>false, "err"=> 0, "compid" => 0, "info" => "");

    // get class id
    $classname = trim($entry['b-class']);
    $query = "SELECT id FROM t_class WHERE classname = ? ";
    $classid = $db_o->run($query, array($classname))->fetchColumn();

    if ($classid)
    {
        $arr = array(
            "classid"  => $classid,
            "boatnum"  => $entry['b-sailno'],
            "sailnum"  => $entry['b-sailno'],
            "boatname" => $entry['b-name'],
            "helm"     => $entry['h-name'],
            "crew"     => $entry['c-name'],
            "club"     => $entry['h-club'],
            "personal_py" => 0,
            "active"   => 1,
            "updby"    => "rm_event"
        );
        $compid = $db_o->insert("t_competitor", $arr);
        $compid ? $status['compid'] = $compid : $status['err'] = -1;      // could not get id of last record inserted
    }

    else
    {
        $status['err'] = -2;                                                   // could not find class id to match classname
    }

    if ($status['err'] >= 0) { $status['success'] = true; }

    return $status;
}

function create_entry_record($boat, $entry, $races)
{
    global $db_o;

    $num_races = count($races);
    $status = array("success"=>false, "id"=> 0, "err"=> 0, "races" => 0, "info" => "");

    // check if we have an event list and convert to an array
    if (empty($races)) {
        $status['err'] = -1 ;                              // event list is empty
    }

    // check if we have a competitor record
    if (empty($entry['e-racemanager'])) {                  // competitor id is empty
        $status['err'] = -2 ;
    }

    if ($status['err'] >= 0)
    {
        foreach ($races as $race)
        {
            $arr = array(
                "action"       => "enter",
                "protest"      => 0,
                "status"       => "N",
                "eventid"      => $race,
                "competitorid" => $entry['e-racemanager'],
                "chg-crew"     => $entry['c-name'],
                "chg-sailnum"  => $entry['b-sailno'],
                "updby"        => "rm_event"
            );

            $ins = $db_o->insert("z_entry", $arr);
            if ($ins) {
                $status['races']++;
            } else {
                $status['err'] = -3;                         // at least one race not entered
            }
        }

        if ($status['races'] == 0) { $status['err'] = -4; }  // no races entered

        if ($status['races'] == $num_races) { $status['success'] = true; }

        if ($status['success'])
        {
            $status['info'] = "ENTERED {$status['races']} race(s)";
            $status['id'] = $ins;
        }
        else
        {
            if ($status['err'] = -4) { $status['info'] = "ENTRY FAILED (for all race(s))"; }
            elseif ($status['err'] = -3) { $status['info'] = "ENTRY FAILED (for at least one race)"; }
            elseif ($status['err'] = -2) { $status['info'] = "ENTRY FAILED (no competitor record exists)"; }
            elseif ($status['err'] = -1) { $status['info'] = "ENTRY FAILED (no event defined)"; }
            else { $status['info'] = "ENTRY FAILED (unknown reason)"; }
        }
    }

    return $status;
}

function update_club_name($entry)
{
    global $db_o;

    if (!empty($entry['e-racemanager']))
    {
        // remove club names concatenated with a / - just takes the first club
        $club = strstr($entry['h-club'].'/', '/', true);
        $upd = $db_o->run("UPDATE t_competitor SET `club` = ? WHERE id = ?", array($club, $entry['e-racemanager']));
        $status = true;
    }
    else
    {
        $status = false;
    }
    
    return $status;
}

function add_rmid_to_entrytable($id, $competitorid)
{
    global $db_o;
    $upd = $db_o->run("UPDATE e_entry SET `e-racemanager` = ? WHERE id = ?", array($competitorid, $id))->fetchAll();

    return $upd;
}

function copy_entries($races)
{
    // copies the entries from the temporary table z_entry to t_entry
    global $db_o;

    // clear t_entry of existing entries for the this event - then records from z_entry for each race in event
    foreach ($races as $race)
    {
        $del = $db_o->run("DELETE FROM t_entry WHERE `eventid` = ?", array($race));

        $ins = $db_o->run("INSERT INTO t_entry SELECT * FROM z_entry WHERE `eventid` = ?", array($race));
    }

    return;

}


