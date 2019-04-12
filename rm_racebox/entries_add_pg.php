<?php

/* ------------------------------------------------------------
   rbx_pg_addentry
   
   Allows OOD to pick competiors from database for adding to 
   either just the current race or all races today.
   
   arguments:
       eventid     id of event
       pagestate   control state for page
   
   ------------------------------------------------------------
*/

$loc        = "..";                                                // <--- relative path from script to top level folder
$page       = "addentry";     // 
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");

u_initpagestart($_REQUEST['eventid'], $page, $_REQUEST['menu']);   // starts session and sets error reporting
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");    // language file

$eventid   = $_REQUEST['eventid'];
$page_state = $_REQUEST['pagestate'];

if (empty($page_state) OR empty($eventid))
{
    u_exitnicely("entries_add_pg", $eventid, "errornum", "eventid or pagestate are missing");
}

// templates
$tmpl_o = new TEMPLATE(array("../templates/general_tm.php",
    "../templates/racebox/layouts_tm.php",
    "../templates/racebox/entries_tm.php"));

include("./include/entries_ctl.inc");

$db_o    = new DB;              // create database object

/* --------------  LEFT HAND COLUMN -------------------------------------------------------------- */
// search box
$lbufr = $tmpl_o->get_template("fm_addentry", array("eventid" => $eventid));

// search results
if ($page_state == "pick")    // display search results
{
    $num_results = count($_SESSION["e_$eventid"]['enter_opt']);
    $data = $_SESSION["e_$eventid"]['enter_opt'];
//    if ($num_results > 0)
//    {
//       $data = array(
//           "found" => $num_results,
//           "competitors" =>$_SESSION["e_$eventid"]['enter_opt'],
//       );
//    }
    $lbufr.= $tmpl_o->get_template("addentry_search_result", array("eventid" =>$eventid), $data);
}

/* --------------  RIGHT HAND COLUMN -------------------------------------------------------------- */

$data = array(
    "pagestate" => $page_state,
    "entries"   => isset($_SESSION["e_$eventid"]['enter_rst']) ? $_SESSION["e_$eventid"]['enter_rst'] : array(),
    "error"     => isset($_SESSION["e_$eventid"]['enter_err']) ? $_SESSION["e_$eventid"]['enter_err'] : null,
);
if (isset($_SESSION["e_$eventid"]['enter_err'])) { unset($_SESSION["e_$eventid"]['enter_err']); }
$rbufr = $tmpl_o->get_template("addentry_boats_entered", array(), $data);

$fields = array(
    "title"      => "racebox",
    "loc"        => $loc,
    "stylesheet" => "$loc/style/rm_racebox.css",
    "navbar"     => "",
    "l_top"      => "",
    "l_mid"      => $lbufr,
    "l_bot"      => "",
    "r_top"      => "",
    "r_mid"      => "<div style='margin-top: -50px;'".$rbufr."</div>",
    "r_bot"      => "",
    "footer"     => "<script>window.location.reload(true);)</script>",
    "page"      => $page,
    "refresh"   => 0,
    "l_width"   => 8,
    "forms"     => true,
    "tables"    => true,
    "body_attr" => ""
);
echo $tmpl_o->get_template("two_col_page", $fields);

?>