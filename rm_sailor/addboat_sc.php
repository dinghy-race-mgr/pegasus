<?php
/**
 * addboat_sc.php - adds entry for selected races
 * 
 *
 * 
 */
$loc        = "..";       
$page       = "addboat";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"addboat_sc",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/boat_class.php");
require_once ("{$loc}/common/classes/comp_class.php");

// connect to database to get event information
$db_o   = new DB();
$comp_o = new COMPETITOR($db_o);
$boat_o = new BOAT($db_o);
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/addeditboat_tm.php"));

// set up confirmation fields
$addboatfields = array(
    "class"   => $boat_o->boat_getclassname($_REQUEST['classid']),
    "sailnum" => $_REQUEST['sailnum'],
    "team"    => u_getteamname(ucwords($_REQUEST['helm']), ucwords($_REQUEST['crew'])),
    "helm"    => ucwords($_REQUEST['helm'])
);

// do field validation - none identified at the moment

// add boat to competitor table - checks for duplicate
$status = $comp_o->comp_addcompetitor($_REQUEST);

// create confirmation response
if ($status['code'] == 0)         // report success
{
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("addboat_success", $addboatfields, array());

    // switch to this boat as active sailor
    $competitors = $comp_o->comp_findcompetitor(array("id"=>$status['id']));
    $_SESSION['sailor'] = $competitors[0];

    $_SESSION['sailor']['change'] = false;
    $_SESSION['sailor']['chg-sailnum'] = "";
    $_SESSION['sailor']['chg-helm'] = "";
    $_SESSION['sailor']['chg-crew'] = "";
}
elseif ($status['code'] == 2)    // report duplicate
{
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("addboat_duplicate", $addboatfields, array());
}
else                             // report other failure
{
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("addboat_fail", $addboatfields, array());
}

// generate confirmation page
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();
?>