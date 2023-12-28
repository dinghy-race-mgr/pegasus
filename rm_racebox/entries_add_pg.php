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

// start session
u_startsession("sess-rmracebox", 10800);

// arguments
$eventid = u_checkarg("eventid", "checkintnotzero","");
$pagestate = u_checkarg("pagestate", "set","");

if (!$eventid or empty($pagestate)) {
    u_exitnicely($scriptname, 0, "$page page has an invalid or missing event identifier [{$_REQUEST['eventid']}] or page state [{$_REQUEST['pagestate']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// page initialisation
u_initpagestart($eventid, $page, false);

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/entries_tm.php"));

// page controls
include("./include/entries_ctl.inc");

// create database object
$db_o    = new DB;

$pagestate == "pick" ? $search = $_SESSION["e_$eventid"]['enter_opt'] : $search = array();

$params = array(
    "pagestate" => $pagestate,
    "error"     => isset($_SESSION["e_$eventid"]['enter_err']) ? $_SESSION["e_$eventid"]['enter_err'] : null,
    "search"    => $search,
    "entries"   => isset($_SESSION["e_$eventid"]['enter_rst']) ? $_SESSION["e_$eventid"]['enter_rst'] : array(),
);

//if (isset($_SESSION["e_$eventid"]['enter_err'])) { unset($_SESSION["e_$eventid"]['enter_err']); }

$body = $tmpl_o->get_template("fm_addentry", array("eventid" =>$eventid), $params);

$fields = array(
    "title"      => "racebox",
    "theme"      => $_SESSION['racebox_theme'],
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "navbar"     => "",
    "body"       => $body,
    "footer"     => "<script>window.location.reload();)</script>",
    "body_attr" => ""
);

echo $tmpl_o->get_template("basic_page", $fields, array());

