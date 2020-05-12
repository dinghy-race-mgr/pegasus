<?php
/**
 * Makes permanent changes to a boat details
 * 
 */
$loc        = "..";
$page       = "editboat";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"editboat_pg",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/boat_class.php");
require_once ("{$loc}/common/classes/comp_class.php");

// connect to database to get event information
$db_o = new DB();
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/addeditboat_tm.php"));

// set optional fields     FIXME - this will eventually be part of configuration
$field_set = array(
    "helm_email"  => $_SESSION['sailor_boat_h_email'],
    "crew_email"  => $_SESSION['sailor_boat_c_email'],
    "dob"         => $_SESSION['sailor_boat_dob'],
    "skill_level" => $_SESSION['sailor_boat_skill'],
);

//FIXME - stop them changing the class with an edit - or if they do change it make it a new record

// get all details for current boat
$comp_o = new COMPETITOR($db_o);
$editboatfields = $comp_o->get_competitor($_SESSION['sailor']['id']);

// create class and skill drop downs
$class_o = new BOAT($db_o);
$class_list = $class_o->boat_getclasslist();
$class_lut  = u_selectlist($class_list, $_SESSION['sailor']['classname']);
$skill_lut = u_selectcodelist($db_o->db_getsystemcodes("competitor_skill"), "default");

// assemble and render page
$_SESSION['pagefields']['header-center'] = $_SESSION['pagename']['edit'];
$_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(),
    array("options" => set_page_options("addboat")));
$_SESSION['pagefields']['body'] = $tmpl_o->get_template("boat_fm", $editboatfields,
    array("mode" => "edit", "fields" => $field_set, "class_list" => $class_lut, "skill_list" => $skill_lut));
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();
