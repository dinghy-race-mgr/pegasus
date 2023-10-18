<?php
/**
 * Takes boat out of active list for searches
 *
 */
$loc        = "..";
$page       = "hideboat";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/comp_class.php");

// start session
session_id('sess-rmsailor');
session_start();

// initialise page
u_initpagestart(0,$scriptname,false);

$db_o = new DB();
$comp_o = new COMPETITOR($db_o);

$status = $comp_o->hide_competitor($_REQUEST['sailor']);

// return to search page with initial search string - repeat search
//header("Location: search_sc.php?searchstr=".$_REQUEST['searchstr']);
header("Location: search_pg.php?");
exit();