<?php
/* ------------------------------------------------------------
   timer_editlaps_pg

NOT SURE THIS IS USED ANYMORE

   
   arguments:
       eventid     id of event
       pagestate   control state for page
   
   ------------------------------------------------------------
*/

$loc        = "..";       // <--- relative path from script to top level folder
$page       = "editlaptimes";     //
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php"); 
require_once ("{$loc}/common/lib/html_lib.php"); 


u_initpagestart($_REQUEST['eventid'], $page, $_REQUEST['menu']);   // starts session and sets error reporting

// initialising language   
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");

require_once ("{$loc}/common/classes/db_class.php"); 
require_once ("{$loc}/common/classes/html_class.php");
require_once ("{$loc}/common/classes/race_class.php");

$pagestate = $_REQUEST['pagestate'];
$eventid   = $_REQUEST['eventid'];
$entryid = $_REQUEST['entryid'];

if (empty($pagestate) OR empty($eventid))
{
    u_exitnicely("timer_editlaps_pg", $eventid, "errornum", "eventid or pagestate are missing");
}

$db_o    = new DB;              // create database object
$race_o = new RACE($db_o, $eventid);    // create event object


/* --------------  form -------------------------------------------------------------- */
$lbufr = "<div class=\"container\">";

// get entry and lap times
$entry = $race_o->entry_get_timings($_REQUEST['entryid']);
if (empty($entry))
{
    $laptimes = array();
}
else
{
    $laptimes = explode(",", $entry['laptimes']);
}

// include form
include ("./include/rbx_timer_fm_editlaps_{$_SESSION['lang']}.inc");
$lbufr.= $form_bufr;
$lbufr.= "</div>";


$html = new HTMLPAGE($_SESSION['lang']);
$html->html_header($loc, "", true, false, 0, "");  
$html->html_body("");
$html->html_addhtml($lbufr);
$bufr = $html->html_render();
echo $bufr; 

?>