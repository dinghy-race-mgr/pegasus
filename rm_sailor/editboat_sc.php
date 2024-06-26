<?php
/**
 *  Processes change to boat details submitted through editboat_pg
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
u_initpagestart(0,$scriptname,false);   // starts session and sets error reporting

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
$editboatfields = array(
    "class" => $boat_o->boat_getclassname($_REQUEST['classid']),
    "sailnum" => $_REQUEST['sailnum'],
    "team" => u_getteamname($_REQUEST['helm'], $_REQUEST['crew'])
);

// update competitor record in competitor table - checks for duplicate
$status = $comp_o->comp_updatecompetitor($_SESSION['sailor']['id'], $_REQUEST, "rm_sailor");

// create confirmation response
if ($status != "failed")
{
    // update sailor session details
    $competitors = $comp_o->comp_findcompetitor(array("id"=>$_SESSION['sailor']['id']));
    if ($competitors)
    {
        $_SESSION['sailor'] = $competitors[0];
        $_SESSION['sailor']['change'] = true;
        $_SESSION['sailor']['chg-sailnum'] = "";
        $_SESSION['sailor']['chg-helm'] = "";
        $_SESSION['sailor']['chg-crew'] = "";

        $template = "editboat_success";
        u_writelog($_SESSION['app_name']." $scriptname : registered boat details edited ->  ".print_r($editboatfields, true),"");
    }
    else
    {                                           // report failure to find newly registered boat
        $template = "editboat_fail";
        u_writelog($_SESSION['app_name']." $scriptname : FAILED - registered boat details edited but not found ->  ".print_r($editboatfields, true),"");
    }
}
else                                            // report failure to register new boat
{
    $template = "editboat_fail";
    u_writelog($_SESSION['app_name']." $scriptname : FAILED - registered boat details edited but not saved  ".print_r($editboatfields, true),"");
}

// assemble and render page (header set in editboat_pg)
$_SESSION['pagefields']['body'] = $tmpl_o->get_template($template, $editboatfields, array("restart" => true));
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();
