<?php
/**
 * pursuit_pg.php - race administration page
 *
 * This page allows the user to resolve finishes on a multi-finish line purauit race.
 * It highlights boats without a finish, and boats with a finish at more than
 * one finish line.
 *
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 *
 * %%copyright%%
 * %%license%%
 *
 * @param string $eventid
 *
 */

$loc        = "..";       // <--- relative path from script to top level folder
$page       = "pursuit";     //
$scriptname = basename(__FILE__);

// required libraries
require_once ("{$loc}/common/lib/util_lib.php");

// get script parameters
$eventid = u_checkarg("eventid", "checkintnotzero","");
if (!$eventid) {
    u_exitnicely($scriptname, 0, "$page page - requested event has an invalid or missing record identifier [{$_REQUEST['eventid']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));  }

// start session
session_id('sess-rmracebox');
session_start();

// page initialisation
u_initpagestart($eventid, $page, true);

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/event_class.php");

// templates
$tmpl_o = new TEMPLATE(array("../templates/general_tm.php",
    "../templates/racebox/layouts_tm.php",
    "../templates/racebox/navbar_tm.php",
    "../templates/racebox/pursuit_tm.php"));

// database connection
$db_o = new DB;
$event_o = new EVENT($db_o);
$event = $event_o->get_event_byid($eventid);

// page controls
include ("./include/pursuit_ctl.inc");

// ----- navbar -----------------------------------------------------------------------------
$fields = array(
    "eventid"  => $eventid,
    "brand"    => "raceBox: {$_SESSION["e_$eventid"]['ev_label']}",
    "page"     => $page,
    "pursuit"  => $_SESSION["e_$eventid"]['pursuit'],
);
$nbufr = $tmpl_o->get_template("racebox_navbar", $fields);


// ----- left hand panel --------------------------------------------------------------------
$lbufr_top= "";
$lbufr_mid = "";

// check for growls that have not been displayed and display them
$lbufr_top.= u_growlProcess($eventid, $page);

$lbufr_mid = <<<EOT
    <h1>Not implemented yet</h1>
EOT;

// ----- right hand panel --------------------------------------------------------------------
$rbufr_top = "";

$rbufr_mid = <<<EOT
    <p>buttons here</p>
EOT;


// disconnect database
$db_o->db_disconnect();

// ----- render page -------------------------------------------------------------------------
$fields = array(
    "title"      => "pursuit",
    "loc"        => $loc,
    "stylesheet" => "$loc/style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => $lbufr_top,
    "l_mid"      => $lbufr_mid,
    "l_bot"      => "",
    "r_top"      => $rbufr_top,
    "r_mid"      => $rbufr_mid,
    "r_bot"      => "",
    "footer"     => "",
    "page"       => $page,
    "refresh"    => 0,
    "l_width"    => 10,
    "forms"      => true,
    "tables"     => false,
    "body_attr"  => "onload=\"startTime()\""
);
echo $tmpl_o->get_template("two_col_page", $fields);


