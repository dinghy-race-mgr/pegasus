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

// start session
session_id('sess-rmsailor');
session_start();

// initialise page
u_initpagestart(0,$scriptname,false);

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/boat_class.php");
require_once ("{$loc}/common/classes/comp_class.php");

// connect to database to get event information
$db_o = new DB();
$tmpl_o = new TEMPLATE(array("./templates/layouts_tm.php", "./templates/addeditboat_tm.php"));

// set optional fields     FIXME - this will eventually be part of configuration
$field_set = array(
    "helm_email"  => $_SESSION['sailor_boat_h_email'],
    "crew_email"  => $_SESSION['sailor_boat_c_email'],
    "dob"         => $_SESSION['sailor_boat_dob'],
    "skill_level" => $_SESSION['sailor_boat_skill'],
);

// get all details for current boat
$comp_o = new COMPETITOR($db_o);
$editboatfields = $comp_o->get_competitor($_SESSION['sailor']['id']);

// create skill drop downs
//$class_o = new BOAT($db_o);
//$class_list = $class_o->boat_getclasslist();
//$class_lut  = u_selectlist($class_list, $_SESSION['sailor']['classname']);
$skill_lut = u_selectcodelist($db_o->db_getsystemcodes("competitor_skill"), "default");

// assemble and render page
$_SESSION['pagefields']['header-center'] = $_SESSION['option_cfg'][$page]['pagename'];
$_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(),
    array("page" => $page, "options" => set_page_options($page)));
$_SESSION['pagefields']['body'] = $tmpl_o->get_template("boat_fm", $editboatfields,
    array("action" => "edit", "sailor" => $_SESSION['sailor'], "fields" => $field_set, "skill_list" => $skill_lut));
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();
