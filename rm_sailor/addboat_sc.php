<?php
/**
 * Processes input from add boat form
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

// start session
session_id('sess-rmsailor');
session_start();

// initialise page
u_initpagestart(0,$page,false);

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/boat_class.php");
require_once ("{$loc}/common/classes/comp_class.php");

// connect to database to get event information
$db_o   = new DB();
$comp_o = new COMPETITOR($db_o);
$boat_o = new BOAT($db_o);
$tmpl_o = new TEMPLATE(array("./templates/layouts_tm.php", "./templates/addeditboat_tm.php"));

// set up confirmation fields
$addboatfields = array(
    "class"   => $boat_o->boat_getclassname($_REQUEST['classid']),
    "sailnum" => $_REQUEST['sailnum'],
    "team"    => u_getteamname(ucwords($_REQUEST['helm']), ucwords($_REQUEST['crew'])),
    "helm"    => ucwords($_REQUEST['helm'])
);

// add boat to competitor table - checks for duplicate
$status = $comp_o->comp_addcompetitor($_REQUEST);

// create confirmation response
if ($status['code'] == 0) {      // report success
    $template = "addboat_success";

    // switch to this boat as active sailor
    $competitors = $comp_o->comp_findcompetitor(array("id" => $status['id']));
    $_SESSION['sailor'] = $competitors[0];

    $_SESSION['sailor']['change'] = false;
    $_SESSION['sailor']['chg-sailnum'] = "";
    $_SESSION['sailor']['chg-helm'] = "";
    $_SESSION['sailor']['chg-crew'] = "";
//    FIXME - need to add other fields with flags to turn them off and on)

} elseif ($status['code'] == 2) {   // report duplicate
    $template = "addboat_duplicate";

} else  {                           // report other failure
    $template = "addboat_fail";
}

// assemble and render page (header assigned in addboat_pg.php
$_SESSION['pagefields']['body'] = $tmpl_o->get_template($template, $addboatfields,
                                           array("mode" => $_SESSION['mode'], "restart" => true));

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();
