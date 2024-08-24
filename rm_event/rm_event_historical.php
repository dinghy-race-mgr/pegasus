<?php
/*
 * utility to process data from old event system in new format

// initialise

// configure - hard coded

// set up directory structure for output data (in tmp)

/*
  - loop over old_events_table on linode in event id order
    - create array with data for e_event
    - check if we have a link results php or htm file in t_openmtg
    - move data file into relevant directory structure
    - create record for e_document with results file
    - if we have NOR or SI move these documents into directory structure and add to e_document


    // set up report array

    // read current event record and generate array for insert

    // read related topic records and generate array for insert

    // read linked document records and generate array for insert

    // read linked contact records and generate array for insert


// how to do undo

*/

require_once("include/rm_event_lib.php");
require_once("classes/pages.php");
require_once("classes/template.php");
require_once("classes/db.php");

$cfg = array(
    "db_host" => "localhost",
    "db_name" => "syc_clubm",
    "db_user" => "rmuser",
    "db_pass" => "pegasus",
);

$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

$events = $db_o->run("SELECT * FROM tblopenmtg WHERE id <= 106 AND id >= 6 ORDER BY `id` ASC", array() )->fetchall();
//echo "<pre>".print_r($events,true)."</pre>";

$event_out = array();
$doc_out = array();
foreach ($events as $k=>$event)
{
    // get no. of online entries
    $num_entries = $db_o->run("SELECT count(*) FROM tblopenentry WHERE openid=?", array($event['id']) )->fetchColumn();

    echo "Event ".$event['id'].": ".strip_tags($event['name'])." - ".$event['startdate']." - $num_entries<br>";
    $event_out[] = set_event_record($event, $num_entries);

    // check for results file
    $results_file = check_file($event['results_page']);    // html link, file in folder
}






function set_event_record($data, $num_entries)
{
    $out = array(
        "id"              => $data['id'],
        "title"           => strip_tags($data['name']),
        "nickname"        => $data['nickname'],
        "list-status-txt" => "online-entries: $num_entries",
        "date-start"      => $data['startdate'],
        "date-end"        => $data['enddate'],
        "entry-classes"   =>,
        "results-mgr"     => "sailwave",
        "updby"           => "transfer"
    );

    return $out;

}

function set_document_record($id, $results_page, $format)
{
    $out = array(
        "eid"      => $id,
        "category" => "results",
        "name"     => "final-results",
        "title"    => "Final Results",
        "file-loc" => "local-relative",
        "filename" => $results_page,
        "format"   => $format,
        "updby"    => "transfer"
    );

    return $out;
}

function check_file($file)
{
    http://81.174.169.180:8080/racemanager7/documents/open_meeting/SYC_LaserFinn_Open_2011_results.htm
}