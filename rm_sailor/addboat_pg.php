<?php
/**
 * addboat_pg - allows sailor to register new boat
 * 
 * Boat registered must be one of the recognised classes.
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *   
 * 
 */
$loc        = "..";
$page       = "addboat";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"addboat_pg",false);   // starts session and sets error reporting
// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/boat_class.php");

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
// set initial values
$addboatfields = array(
    "classid"     => "", "boatnum"     => "", "sailnum"     => "",  "boatname"    => "",
    "helm"        => "", "helm_dob"    => "", "helm_email"  => "",
    "crew"        => "", "crew_dob"    => "", "crew_email"  => "",
    "club"        => $_SESSION['clubname'],
    "personal_py" => "", "skill_level" => "1",
    "flight"      => "",
    "regular"     => "",
    "last_entry"  => "", "last_event"  => "", "active"      => "1",
    "prizelist"   => "", "grouplist"   => "",
    "memberid"    => "",
    "updby"       => "rm_sailor",
);

// create class and skill drop downs
$class_o = new BOAT($db_o);
$class_list = $class_o->boat_getclasslist();
$class_lut  = u_selectlist($class_list);
$skill_lut = u_selectcodelist($db_o->db_getsystemcodes("competitor_skill"), "");

$_SESSION['pagefields']['body'] = $tmpl_o->get_template("boat_fm", $addboatfields,
    array("mode" => "add", "fields" => $field_set, "class_list" => $class_lut, "skill_list" => $skill_lut));

// render page
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();
?>