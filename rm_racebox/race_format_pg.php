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

// start session
u_startsession("sess-rmracebox", 10800);

// arguments
$eventid = $_REQUEST['eventid'];
if (!$eventid)
{
    u_exitnicely($scriptname, $_REQUEST['eventid'],"$page page - input parameters eventid [{$_REQUEST['eventid']}] is missing",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// page initialisation
u_initpagestart("", $page, false);   // <-- not logged

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

$title = ucwords($racecfg['race_name']);

$body = <<<EOT
    <div class="container-fluid" role="main">
    
        <div class="flex-container">
            <div class="flex-child">
                <h2>Race Format: <span style="font-size: 1.1em;"><b>$title</b></span></h2>
            </div>
            <div class="flex-child ">
                <span class="print" >
                    <button type="button" class="btn btn-md btn-success noprint pull-right" style="margin-top: 10px;" onclick="window.print()">Print Format</button>
                </span>
            </div>
        </div>

        $viewbufr
    </div>
EOT;

$fields = array(
    "title"      => $title,
    "loc"        => $loc,
    "theme"      => $_SESSION['racebox_theme'],
    "stylesheet" => "./style/rm_format.css",
    "navbar"     => "",
    "body"       => $body,
    "footer"     => "",
);
echo $tmpl_o->get_template("basic_page", $fields);

