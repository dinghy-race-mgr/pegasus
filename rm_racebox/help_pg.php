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

$eventid = u_checkarg("eventid", "checkintnotzero","");
$menu = u_checkarg("menu", "setbool", "true", "");
$helppage = u_checkarg("page", "set", "", "help");

u_initpagestart($_REQUEST['eventid'], $page, true);  // starts session and sets error reporting
//include ("{$loc}/config/lang/{$_SESSION['lang']}-racebox-lang.php");   // language file

// classes
require_once("{$loc}/common/classes/db_class.php");
require_once("{$loc}/common/classes/template_class.php");

//templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php",
                             "./templates/help_tm.php", "./templates/pickrace_tm.php"));

// buttons/modals
include("./include/help_ctl.inc");

// ----- navbar -----------------------------------------------------------------------------

if ($eventid != 0)   // request from pickrace page
{
    $fields = array("eventid" => $eventid, "brand" => "raceBox: {$_SESSION["e_$eventid"]['ev_label']}", "club" => $_SESSION['clubcode']);
    $params = array("page" => $helppage, "pursuit" => $_SESSION["e_$eventid"]['pursuit'], "links" => $_SESSION['clublink']);
    $nbufr = $tmpl_o->get_template("racebox_navbar", $fields, $params);
}
else
{
    $nav_fields = array("page" => $page, "eventid" => 0, "brand" => "raceBox PICK RACE", "rm-website" => $_SESSION['sys_website']);
    $nbufr = $tmpl_o->get_template("pickrace_navbar", $nav_fields, array("links"=>$_SESSION['clublink']));
}

// database connection
$db_o = new DB;

// body
$body = $tmpl_o->get_template("under_construction", array("title" => "raceManager Help:", "info" => "We are still working on the help system"));

// disconnect database
$db_o->db_disconnect();

// ----- render page -------------------------------------------------------------------------

$eventid != 0 ? $title = $_SESSION["e_$eventid"]['ev_label'] : $title = "racebox" ;
$fields = array(
    "title"      => $eventid,
    "theme"      => $_SESSION['racebox_theme'],
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => $body,
    "l_mid"      => "",
    "l_bot"      => "",
    "r_top"      => "",
    "r_mid"      => "",
    "r_bot"      => "",
    "footer"     => "",
    "body_attr"  => "onload=\"startTime()\""
);
$params = array(
    "page"      => $page,
    "refresh"   => 0,
    "l_width"   => 9,
    "forms"     => true,
    "tables"    => true,
);
echo $tmpl_o->get_template("two_col_page", $fields, $params);



