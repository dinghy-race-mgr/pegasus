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

$eventid = $_REQUEST['eventid'];

u_initpagestart("", $page, false);

// libraries
require_once ("{$loc}/common/lib/rm_lib.php");
require_once ("{$loc}/common/lib/raceformat_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/rota_class.php");

// database connection
$db_o = new DB;

// initialising templates
$tmpl_o  = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php"));

// get event information for today
$event_o  = new EVENT($db_o);
$rota_o  = new ROTA($db_o);
$event = $event_o->get_event_byid($eventid);

// create html markup
$racecfg  = $event_o->event_getracecfg($event['event_format'], $eventid);
$fleetcfg = $event_o->event_getfleetcfg($event['event_format']);
$duties   = $rota_o->get_event_duties($eventid);
$viewbufr = createdutypanel($duties, $eventid, "in");
$viewbufr.= createfleetpanel ($fleetcfg, $eventid, "in");
$viewbufr.= createsignalpanel(getsignaldetail($racecfg, $fleetcfg, $event), $eventid, "in");

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
    "theme"      => $_SESSION['racebox_theme'],
    "stylesheet" => "",
    "navbar"     => "",
    "body"       => $body,
    "footer"     => "",
);
echo $tmpl_o->get_template("basic_page", $fields);

