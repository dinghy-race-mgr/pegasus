<?php

/* ------------------------------------------------------------
   entries_add_pg.php
   
   Allows OOD to pick competiors from database for adding to 
   either just the current race or all races today.
   
   arguments:
       eventid     id of event
       pagestate   control state for page
   
   ------------------------------------------------------------
*/

$loc        = "..";
$page       = "addentry";     // 
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");

$eventid = u_checkarg("eventid", "checkintnotzero","");
$page_state = u_checkarg("pagestate", "set","");

u_initpagestart($_REQUEST['eventid'], $page, "");
include ("{$loc}/config/lang/{$_SESSION['lang']}-racebox-lang.php");

if (!$eventid) {
    u_exitnicely($scriptname, $eventid, "the requested event has an invalid record identifier [{$_REQUEST['eventid']}]",
        "please contact your raceManager administrator");
}

if (empty($page_state)) {
    u_exitnicely($scriptname, $eventid, "the page state has not been set",
        "please contact your raceManager administrator");
}

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/entries_tm.php"));
include("./include/entries_ctl.inc");

// create database object
$db_o    = new DB;

/* --------------  LEFT HAND COLUMN -------------------------------------------------------------- */
// search box
$lbufr = $tmpl_o->get_template("fm_addentry", array("eventid" => $eventid));

// search results
if ($page_state == "pick")    // display search results
{
    $num_results = count($_SESSION["e_$eventid"]['enter_opt']);
    $params = $_SESSION["e_$eventid"]['enter_opt'];
    $lbufr.= $tmpl_o->get_template("addentry_search_result", array("eventid" =>$eventid), $params);
}

/* --------------  RIGHT HAND COLUMN -------------------------------------------------------------- */

$params = array(
    "pagestate" => $page_state,
    "entries"   => isset($_SESSION["e_$eventid"]['enter_rst']) ? $_SESSION["e_$eventid"]['enter_rst'] : array(),
    "error"     => isset($_SESSION["e_$eventid"]['enter_err']) ? $_SESSION["e_$eventid"]['enter_err'] : null,
);
if (isset($_SESSION["e_$eventid"]['enter_err'])) { unset($_SESSION["e_$eventid"]['enter_err']); }
$rbufr = $tmpl_o->get_template("addentry_boats_entered", array(), $params);

$fields = array(
    "title"      => "racebox",
    "theme"      => $_SESSION['racebox_theme'],
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "navbar"     => "",
    "l_top"      => "",
    "l_mid"      => $lbufr,
    "l_bot"      => "",
    "r_top"      => "",
    "r_mid"      => "<div style='margin-top: -50px;'".$rbufr."</div>",
    "r_bot"      => "",
    "footer"     => "<script>window.location.reload(true);)</script>",
    "page"      => $page,
    "refresh"   => 0,
    "l_width"   => 8,
    "forms"     => true,
    "tables"    => true,
    "body_attr" => ""
);

$params = array(
    "page"      => $page,
    "refresh"   => 0,
    "l_width"   => 8,
    "forms"     => true,
    "tables"    => true,
);

echo $tmpl_o->get_template("two_col_page", $fields, $params);
