<?php
/* ------------------------------------------------------------
   start_infringements_pg
   Allows OOD to set codes for start line infringements.

   
   ------------------------------------------------------------
*/

$loc        = "..";       // <--- relative path from script to top level folder
$page       = "start_infringement";     // 
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");

u_initpagestart($_REQUEST['eventid'],$page,"");   // starts session and sets error reporting

require_once ("{$loc}/common/classes/db_class.php"); 
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/race_class.php");

// app includes
require_once ("./include/rm_racebox_lib.php");

$pagestate = $_REQUEST['pagestate'];
$eventid   = $_REQUEST['eventid'];
$startnum  = $_REQUEST['startnum'];

if (empty($pagestate) OR empty($eventid))
{
    u_exitnicely("start_infringements_pg", $eventid, "errornum", "eventid ($eventid), or pagestate ($pagestate) or startnum()$startnum is missing");
}

$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/start_tm.php"));

$db_o    = new DB;              // create database object
$race_o  = new RACE($db_o, $eventid);

// display list of entries for this start
if ($pagestate == "init")
{
    // get entries for this start
    $entries = $race_o->race_getstarters(array("start"=>$startnum));

    $params = array(
        "eventid"    => $eventid,
        "startnum"   => $startnum,
        "entries"    => count($entries),
        "entry-data" => $entries,
    );
    $body = $tmpl_o->get_template("infringe", array(), $params);

    $fields = array(
        "title"      => "start infringements",
        "theme"      => $_SESSION['racebox_theme'],
        "loc"        => $loc,
        "stylesheet" => "./style/rm_racebox.css",
        "navbar"     => "",
        "body"       => $body,
        "footer"     => "",
    );
    echo $tmpl_o->get_template("basic_page", $fields);
}

// change code for specified entry
elseif ($pagestate == "setcode")
{
    $err = false;

    empty($_REQUEST['entryid'])    ? $err = true : $entryid = $_REQUEST['entryid'];
    empty($_REQUEST['boat'])       ? $err = true : $boat = $_REQUEST['boat'];
    empty($_REQUEST['racestatus']) ? $err = true : $racestatus = $_REQUEST['racestatus'];
    empty($_REQUEST['declaration'])? $err = true : $declaration = $_REQUEST['declaration'];
    empty($_REQUEST['lap'])        ? $err = true : $lap = $_REQUEST['lap'];
    empty($_REQUEST['finishlap'])  ? $err = true : $finishlap = $_REQUEST['finishlap'];
    empty($_REQUEST['code'])       ? $code = ""  : $code = $_REQUEST['code'];

    if ($err)
    {
        $reason = "required parameters were invalid
                       (id: {$_REQUEST['entryid']}; boat: {$_REQUEST['boat']}; status: {$_REQUEST['racestatus']};)";
        u_writelog("$boat - set code failed - $reason", $eventid);
        u_growlSet($eventid, $page, $g_timer_setcodefailed, array($boat, $reason));
    }
    else
    {
        $update = set_code($eventid, $entryid, $code, $racestatus, $declaration, $boat, $finishlap, $lap);

        if (!$update)
        {
            $reason = "database update failed";
            u_writelog("$boat - attempt to set code to $code] FAILED" - $reason, $eventid);
            u_growlSet($eventid, $page, $g_timer_setcodefailed, array($boat, $reason));
        }
    }

    header("Location: start_infringements_pg.php?eventid=$eventid&startnum=$startnum&pagestate=init");
}

// deal with invalid pagestate
else
{
    u_exitnicely("start_infringements_pg", $eventid, "errornum", "pagestate ($pagestate) is not recognised");
}

