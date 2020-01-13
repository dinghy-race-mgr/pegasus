<?php
/**
 * rememberme - creates a cookie for remembering a boat
 *
 *
 *
 *
 */
$loc        = "..";
$page       = "rememberme_sc";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"rm_sailor",false);   // starts session and sets error reporting

