<?php
/**
 * hideboat - takes boat out of acive list for searches
 *
 *
 *
 *
 */
$loc        = "..";
$page       = "hideboat_sc";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/comp_class.php");

u_initpagestart(0,"hideboat",false);   // starts session and sets error reporting

$db_o = new DB();
$comp_o = new COMPETITOR($db_o);

$status = $comp_o->hide_competitor($_REQUEST['compid']);

header("Location: boatsearch_sc.php?sailnum=".$_REQUEST['sailnum']);
exit();