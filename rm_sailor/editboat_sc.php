<?php
/**
 * editboat_sc.php - adds entry for selected races
 * 
 *
 * 
 */
$loc        = "..";       
$page       = "editboat";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"editboat_sc",false);   // starts session and sets error reporting

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
$editboatfields = array(
    "class" => $boat_o->boat_getclassname($_REQUEST['classid']),
    "sailnum" => $_REQUEST['sailnum'],
    "team" => u_getteamname($_REQUEST['helm'], $_REQUEST['crew'])
);

// do post race validation - none identified at the moment

// update competitor record in competitor table - checks for duplicate
$status = $comp_o->comp_updatecompetitor($_SESSION['sailor']['id'], $_REQUEST);

// create confirmation response
if ($status != "failed")         // report success
{
    // update sailor session details
    $competitors = $comp_o->comp_findcompetitor(array("id"=>$_SESSION['sailor']['id']));
    if ($competitors)
    {
        $_SESSION['sailor'] = $competitors[0];
        $_SESSION['sailor']['change'] = false;
        $_SESSION['sailor']['chg-sailnum'] = "";
        $_SESSION['sailor']['chg-helm'] = "";
        $_SESSION['sailor']['chg-crew'] = "";

        $_SESSION['pagefields']['body'] = $tmpl_o->get_template("editboat_success", $editboatfields, array());
    }
    else  // report failure
    {
        $_SESSION['pagefields']['body'] = $tmpl_o->get_template("editboat_fail", $editboatfields, array());
    }
}
else                 // report failure
{
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("editboat_fail", $editboatfields, array());
}

// generate confirmation page
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();
?>