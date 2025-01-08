<?php
/*
 * Displays entries + waiting list using the same format as the entry page
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
    exit("Sorry - you are not authorised to use this script ... ");  // FIXME -  change to exit_nicely
}


// arguments
if (key_exists("event", $_REQUEST))   // requesting single nicknamed event
{
    // find event matching nick name
    $event = $db_o->run("SELECT * FROM e_event WHERE nickname = ?", array($_REQUEST['event']) )->fetch();
    $eid = $event['id'];

    empty($_REQUEST['sort'])  ? $sort_arg = 1 : $sort_arg = $_REQUEST['sort'];
    empty($_REQUEST['fields'])  ? $fields_arg = 1 : $fields_arg = $_REQUEST['fields'];
    empty($_REQUEST['where'])  ? $where_arg = 1 : $where_arg = $_REQUEST['where'];
    empty($_REQUEST['checks'])  ? $checks = false : $checks = true;

    $options = array("fields" => $fields_arg, "where" => $where_arg, "sort" => $sort_arg, "checks"=>$checks);
}
else
{
    echo "exit nicely error message - event not found";  // FIXME -  change to exit_nicely
    exit("script stopped");
}

// options
$cols = array(
    "1" => array("class"=>"b-class","sail no."=>"b-sailno","helm"=>"h-name","crew"=>"c-name","club"=>"h-club"),
    "2" => array("tally"=>"e-tally","class"=>"b-class","sail no."=>"b-sailno","helm"=>"b-helm","crew"=>"b-crew","club"=>"club"),
);
$whereplus = array(
    "1" => "and `e-waiting` = 0",
    "2" => "and `e-waiting` = 1",
);
$sort = array(
    "1" => "order by `b-class` ASC, `b-sailno` * 1 ASC",
    "2" => "order by `b-pn` DESC, `b-class` ASC, `b-sailno` * 1 ASC",
    "3" => "order by `e-tally` ASC",
    "4" => "order by `b-fleet` ASC, `b-class` ASC, `b-sailno` * 1 ASC",
);

// get all entry data (excluding waiting list - sorted by fleet, class, sailnumber)
$sql = "SELECT a.`id`, `b-class`, `b-sailno`, `b-altno`, `b-pn`, `b-division`, 
               `h-name`, `h-club`, `h-age`, `h-gender`, `h-emergency`,
               `c-name`, `c-age`, `c-gender`, `c-emergency`, 
               `e-tally`, `e-racemanager`, `e-waiting`, b.crew as crewnum 
               FROM e_entry as a LEFT JOIN t_class as b ON a.`b-class`= b.classname 
               WHERE eid = ? and `e-exclude` = 0 {$whereplus["$where_arg"]} {$sort["$sort_arg"]}";
// echo "<pre>$sql</pre>";
$entries = $db_o->run($sql, array($eid) )->fetchall();

// process entries
$entries = validate_entries($entries);

// produce body of report
$fields = array(
    "event-title"   => $event['title'],
    "version"       => $cfg['sys_release']." ".$cfg['sys_version']
);

$params = array(
    "eid"        => $eid,
    "cols"       => $cols[$fields_arg],
    "entries"    => $entries,
    "options"    => $options,
    "checks"     => $checks
);

// assemble page
$fields = array(
    'page-title'  => $cfg['sys_name'],
    'page-main'   => $tmpl_o->get_template("entries_review_body", $fields, $params),
    'page-footer' => "",
    'page-modals' => "&nbsp;",
    'page-js'     => "&nbsp;"
);
$params = array();
echo $tmpl_o->get_template("page", $fields, $params);

function validate_entries($entries)
{
    /*
     *  Runs following checks
     *    1 - juniors on board
     *    2 - missing consent information
     *    3 - missing emergency contact
     *    4 - missing crew name for double hander
     *    5 - missing gender info for helm or crew
     *    6 - missing sail no.
     *    7 - class not known to raceManager
     *    8 - competitor not known to racemanager
     */
    global $db_o;

    foreach ($entries as $k=>$entry)
    {
        $entry['crewnum'] > 1 ?  $doublehander = true : $doublehander = false;

        // check 1
        $entries[$k]['chk1'] = 0;
        $num_juniors = 0;
        if (!empty($entry['h-age']) and $entry['h-age'] < 18) { $num_juniors++;}
        if (!empty($entry['c-age']) and $entry['c-age'] < 18) { $num_juniors++;}
        $entries[$k]['chk1'] = $num_juniors;

        // check 2
        $entries[$k]['chk2'] = false;
        if ($num_juniors > 0)  // check if we have consents
        {

            $num_consents = $db_o->run("SELECT count(*) as consents FROM e_consent WHERE entryid = ? 
                                        GROUP BY entryid", array($entry['id']) )->fetchColumn();
            if ($num_consents < $num_juniors) { $entries[$k]['chk2'] = true; };
        }

        // check 3
        $entries[$k]['chk3'] = false;
        if (empty($entry['h-emergency']) and empty($entry['c-emergency'])) {$entries[$k]['chk3'] = true;}

        // check 4
        $entries[$k]['chk4'] = false;
        if ($doublehander and (empty($entry['c-name'] or strtolower($entry['c-name']) == "tbc"
                or strtolower($entry['c-name']) == "tba" or strtolower($entry['c-name']) == "tbd"))) {$entries[$k]['chk4'] = true;}

        // check 5
        $entries[$k]['chk5'] = false;
        if ($entry['h-gender'] == 'not given' or ($doublehander and $entry['c-gender'] == 'not given')) {$entries[$k]['chk5'] = true;}

        // check 6
        $entries[$k]['chk6'] = false; // FIXME deal with TBA TBC TBD
        if (empty($entry['b-sailno']) and empty($entry['b-altno'])) {$entries[$k]['chk6'] = true;}

        // check 7
        $entries[$k]['chk7'] = false;
        if (empty($entry['b-pn'])) {$entries[$k]['chk7'] = true;}

        // check 8
        $entries[$k]['chk8'] = false;
        if (empty($entry['e-racemanager'])) {$entries[$k]['chk8'] = true;}
    }

    return $entries;
}
function mark_entries_requiring_consent($entries)
{
    /*
     Identifies which boats have juniors on board, and how many consent forms are required
     Returns the information in an updated 'entries' array
     */

    global $db_o;

    foreach ($entries as $k=>$entry)
    {
        $entries[$k]['junior'] = false;
        $num_consents_reqd = 0;
        if (!empty($entry['h-age']) and $entry['h-age'] < 18) { $num_consents_reqd++; $entries[$k]['junior'] = true;}
        if (!empty($entry['c-age']) and $entry['c-age'] < 18) { $num_consents_reqd++; $entries[$k]['junior'] = true;}

        $num_consents = $db_o->run("SELECT count(*) as consents FROM e_consent WHERE entryid = ? GROUP BY entryid", array($entry['id']) )->fetchColumn();
        $num_consents < $num_consents_reqd ? $entries[$k]['consents_reqd'] = $num_consents_reqd - $num_consents: $entries[$k]['consents_reqd'] = 0;
    }

    return $entries;
}