<?php
/*
 * Displays entries + waiting list using the same format as the entry page
 */

/*
 * Check we have eid and accesskey (stored in config.ini)
 * get event details
 * get entry details
 * prep data for layout
 * modified entry page layout (include modified consent info)
 */

// start session
session_id('sess-rmuevent');
session_start();

// error_reporting(E_ERROR);  set for live operation
require_once("include/rm_event_lib.php");
require_once("classes/template.php");
require_once("classes/db.php");

// initialise application
$cfg = set_config("config.ini", array("rm_event"), true);
$cfg['logfile'] = str_replace("_date", date("_Y"), $cfg['logfile']);

$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);
$tmpl_o = new TEMPLATE(array( "./templates/util_layouts_tm.php"));

// arguments
if (key_exists("event", $_REQUEST))   // requesting single nicknamed event
{
    // find event matching nick name
    $event = $db_o->run("SELECT * FROM e_event WHERE nickname = ?", array($_REQUEST['event']) )->fetch();
    $eid = $event['id'];
}
else
{
    echo "exit nicely error message - event not found";
}

// get all entry data (excluding waiting list - sorted by fleet, class, sailnumber)
$entries = $db_o->run("SELECT * FROM e_entry WHERE eid = ? and `e-exclude` = 0 and `e-waiting` = 0 ORDER BY `b-fleet` ASC, `b-class` ASC, `b-sailno` * 1 ASC", array($eid) )->fetchall();
$entries = mark_entries_requiring_consent($entries);

// get all waiting list entries (sorted by order on waiting list)
$waiting = $db_o->run("SELECT * FROM e_entry WHERE eid = ? and `e-exclude` = 0 and `e-waiting` = 1 ORDER BY `e-entryno` ASC", array($eid) )->fetchall();

//construct entries body htm
$fields = array(
    "event-title"   => $event['title'],
);

$params = array(
    "eid"        => $eid,
    "entries"    => $entries,
    "waiting"    => $waiting,
    "layout"     => "wide"
);

// render confirmation and entries table
$body = $tmpl_o->get_template("entries_body", $fields, $params);

// assemble page
$fields = array(
    'page-title'=>$cfg['sys_name'],
    'page-main'=>$body,
    'page-modals'=>"&nbsp;",
    'page-js'=>"&nbsp;");
$params = array();
echo $tmpl_o->get_template("page", $fields, $params);


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