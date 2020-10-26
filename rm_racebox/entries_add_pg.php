<?php

/* ------------------------------------------------------------
   entries_add_pg.php
   
   Allows OOD to pick competitors from database for adding to
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

$page_state == "pick" ? $search = $_SESSION["e_$eventid"]['enter_opt'] : $search = array();

$params = array(
    "pagestate" => $page_state,
    "error"     => isset($_SESSION["e_$eventid"]['enter_err']) ? $_SESSION["e_$eventid"]['enter_err'] : null,
    "search"    => $search,
    "entries"   => isset($_SESSION["e_$eventid"]['enter_rst']) ? $_SESSION["e_$eventid"]['enter_rst'] : array(),
);

if (isset($_SESSION["e_$eventid"]['enter_err'])) { unset($_SESSION["e_$eventid"]['enter_err']); }

$body = $tmpl_o->get_template("fm_addentry", array("eventid" =>$eventid), $params);

$fields = array(
    "title"      => "racebox",
    "theme"      => $_SESSION['racebox_theme'],
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "navbar"     => "",
    "body"       => $body,
    "footer"     => "<script>window.location.reload(true);)</script>",
    "body_attr" => ""
);

echo $tmpl_o->get_template("basic_page", $fields, array());

