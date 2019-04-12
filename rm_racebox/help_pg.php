<?php
/**
 * help_pg.php - race administration page
 * 
 * This page provides context sensitive help
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 */

$loc        = "..";                                              // <--- relative path from script to top level folder
$page       = "help";
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");

u_initpagestart($_REQUEST['eventid'], $page, $_REQUEST['menu']);  // starts session and sets error reporting
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");   // language file
if ($_SESSION['debug'] == 2) { u_sessionstate($scriptname, $page, $_REQUEST['eventid']); }

empty($_REQUEST['eventid']) ? $eventid = "" : $eventid = $_REQUEST['eventid'];
empty($_REQUEST['page']) ? $helppage = "" : $helppage = $_REQUEST['page'];
empty($_REQUEST['menu']) ? $menu = "" : $menu = $_REQUEST['menu'];

require_once("{$loc}/common/classes/db_class.php");
require_once("{$loc}/common/classes/template_class.php");

$tmpl_o = new TEMPLATE(array("../templates/general_tm.php",
    "../templates/racebox/layouts_tm.php",
    "../templates/racebox/navbar_tm.php"));

// buttons/modals
include("./include/help_ctl.inc");

// ----- navbar -----------------------------------------------------------------------------
$fields = array(
    "eventid"  => $eventid,
    "brand"    => "raceBox HELP ",
    "page"     => $page,
    "pursuit"  => $_SESSION["e_$eventid"]['pursuit'],
);
$nbufr = $tmpl_o->get_template("racebox_navbar", $fields);

// database connection
$db_o = new DB;

// body
$body = $tmpl_o->get_template("under_construction", array("title" => "raceManager Help:", "info" => "We are still working on the help system"));

// disconnect database
$db_o->db_disconnect();

// ----- render page -------------------------------------------------------------------------
$fields = array(
    "title"      => "racebox",
    "loc"        => $loc,
    "stylesheet" => "$loc/style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => $body,
    "l_mid"      => "",
    "l_bot"      => "",
    "r_top"      => "",
    "r_mid"      => "",
    "r_bot"      => "",
    "footer"     => "",
    "page"       => $page,
    "refresh"    => 0,
    "l_width"    => 10,
    "forms"      => true,
    "tables"     => true,
    "body_attr"  => "onload=\"startTime()\""
);
echo $tmpl_o->get_template("two_col_page", $fields);



