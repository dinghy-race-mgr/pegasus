<?php
/*
 *
 *
 *
 *
 */

$loc        = "..";                               // relative path from script to top level folder
$page       = "race_format";
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("{$loc}/common/lib/raceformat_lib.php");

u_initpagestart("", $page, false);
if ($_SESSION['debug']==2) { u_sessionstate($scriptname, $page, 0); }

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/event_class.php");

// database connection
$db_o = new DB;
$tmpl_o  = new TEMPLATE(array("../templates/general_tm.php",
    "../templates/racebox/layouts_tm.php",
    "../templates/racebox/navbar_tm.php",
    "../templates/racebox/pickrace_tm.php"));

// initialising language
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");

$eventid = $_REQUEST['eventid'];

// get event information for today
$event_o  = new EVENT($db_o);
$event = $event_o->event_getevent($eventid);

$racecfg = $event_o->event_getracecfg($eventid, $event['event_format']);

$duties = $event_o->event_geteventduties($eventid, "");
$viewbufr = createdutypanel($duties, $eventid, "in");
$fleetcfg = $event_o->event_getfleetcfg($event['event_format']);
$viewbufr.= createfleetpanel ($fleetcfg, $eventid, "in");
$sequence = getsignaldetail($event_o, $event);
$viewbufr.= createsignalpanel($sequence, $eventid, "in");

$title = ucwords($racecfg['race_name'])." Format";

$body = <<<EOT
    <div class="container-fluid" role="main">
        <h1>$title</h1>
        $viewbufr
    </div>
EOT;

$fields = array(
    "title"      => $title,
    "loc"        => $loc,
    "stylesheet" => "",
    "navbar"     => "",
    "body"       => $body,
    "footer"     => "",
);
echo $tmpl_o->get_template("basic_page", $fields);

?>