<?php
/**
 * Processes search request for boat
 * 
 * free text search for boats based on search string containing one or more of class name,
 * helms surname and sail number
 * 
 * @author Mark Elkington %%email%%
 * 
 * %%copyright%%
 * %%license%%
 *
 */
$loc        = "..";       
$page       = "search";
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
require_once ("{$loc}/common/classes/comp_class.php");

// connect to database to get competitor information
$db_o = new DB();
$comp_o = new COMPETITOR($db_o); 

// check for match on query string (sailnumber, surname or class)
$searchstr = trim($_REQUEST['searchstr']);
$_SESSION['competitors'] = $comp_o->comp_searchcompetitor($searchstr);

$num_found = count($_SESSION['competitors']);
u_writelog($_SESSION['app_name']." $scriptname : search made with |$searchstr| ->  $num_found found","");


// go to selection of boat
header("Location: pickboat_pg.php?searchstr=$searchstr");
exit();


