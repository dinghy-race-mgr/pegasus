<?php
/**
 * Allows user to register new boat
 * 
 * Boat registered must be one of the recognised classes.
 *
 */
$loc        = "..";
$page       = "addboat";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"addboat_pg",false);   // starts session and sets error reporting

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/boat_class.php");

// connect to database to get event information
$db_o = new DB();
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/addeditboat_tm.php"));

// FIXME needs to be configurable
// set optional fields using flags from racemanager.ini
if ($_SESSION['mode'] == "cruise") {
    $field_set = array(
        "helm_email" => $_SESSION['sailor_boat_h_email'],
        "crew_email" => $_SESSION['sailor_boat_c_email'],
        "dob" => $_SESSION['sailor_boat_dob'],
        "skill_level" => $_SESSION['sailor_boat_skill'],
    );
} else {
    $field_set = array(
        "helm_email" => $_SESSION['sailor_boat_h_email'],
        "crew_email" => $_SESSION['sailor_boat_c_email'],
        "dob" => $_SESSION['sailor_boat_dob'],
        "skill_level" => $_SESSION['sailor_boat_skill'],
    );
}

// set initial values for all fields
$addboatfields = array(
    "classid"     => "", "boatnum"     => "", "sailnum"     => "",  "boatname"    => "",
    "helm"        => "", "helm_dob"    => "", "helm_email"  => "",
    "crew"        => "", "crew_dob"    => "", "crew_email"  => "",
    "club"        => $_SESSION['clubname'],
    "personal_py" => "", "skill_level" => "1",
    "flight"      => "",
    "regular"     => "",
//    "last_entry"  => "", "last_event"  => "",  // to avoid setting invalid dates
    "active"      => "1",
    "prizelist"   => "", "grouplist"   => "",
    "memberid"    => "",
    "updby"       => "rm_sailor",
);

// create class and skill drop downs
$class_o = new BOAT($db_o);
$class_list = $class_o->boat_getclasslist(true);
$class_lut  = u_selectlist($class_list, "");
$skill_lut = u_selectcodelist($db_o->db_getsystemcodes("competitor_skill"), "");

// assemble and render page
$_SESSION['pagefields']['header-center'] = $_SESSION['option_cfg'][$page]['pagename'];
$_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(),
    array("page" => $page, "options" => set_page_options($page)));
$_SESSION['pagefields']['body'] = $tmpl_o->get_template("boat_fm", $addboatfields,
    array("mode" => $_SESSION['mode'], "action" => "add", "fields" => $field_set, "class_list" => $class_lut, "skill_list" => $skill_lut));

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();
