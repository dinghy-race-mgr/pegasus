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
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/lib/rm_lib.php");

$eventid = $_REQUEST['eventid'];

u_initpagestart($eventid, $page, $_REQUEST['menu']);   // starts session and sets error reporting
if ($_SESSION['debug']!=0) { u_sessionstate($scriptname, $page, $eventid); }

// initialising language
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");

// check we have request id - if not stop with system error
if (empty($eventid) or !is_numeric($eventid))
{
    u_exitnicely($scriptname, 0, $lang['err']['sys002'], "event id is not defined");
}

include ("{$loc}/common/classes/db_class.php");
include ("{$loc}/common/classes/template_class.php");
include ("{$loc}/common/classes/event_class.php");

// templates
$tmpl_o = new TEMPLATE(array("../templates/general_tm.php",
    "../templates/racebox/layouts_tm.php",
    "../templates/racebox/navbar_tm.php",
    "../templates/racebox/pursuit_tm.php"));

// database connection
$db_o = new DB;
$event_o = new EVENT($db_o);
$event = $event_o->event_getevent($eventid);

// page controls
include ("./include/pursuit_ctl.inc");

// ----- navbar -----------------------------------------------------------------------------
$fields = array(
    "eventid"  => $eventid,
    "brand"    => "raceBox PURSUIT FINISH - {$_SESSION["e_$eventid"]['ev_sname']}",
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


?>