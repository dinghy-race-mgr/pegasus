<?php
/* ------------------------------------------------------------
   start_infringements_pg
   Allows OOD to set codes for start line infringements.
   
   arguments:
       eventid     id of event
       startnum    start number
       pagestate   control state for page
       entryid     id for entry to be changed
       code        code value to be set
   
   ------------------------------------------------------------
*/

$loc        = "..";       // <--- relative path from script to top level folder
$page       = "start_infringement";     // 
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");

u_initpagestart($_REQUEST['eventid'],$page,"");   // starts session and sets error reporting

// initialising language   
include ("{$loc}/config/lang/{$_SESSION['lang']}-racebox-lang.php");

require_once ("{$loc}/common/classes/db_class.php"); 
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/race_class.php");

$pagestate = $_REQUEST['pagestate'];
$eventid   = $_REQUEST['eventid'];
$startnum  = $_REQUEST['startnum'];

if (empty($pagestate) OR empty($eventid) OR empty($startnum))
{
    u_exitnicely("start_infringements_pg", $eventid, "errornum", "eventid ($eventid), startnum ($startnum), or pagestate ($pagestate) is missing");
}

$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/start_tm.php"));

$db_o    = new DB;              // create database object
$race_o  = new RACE($db_o, $eventid);

// display list of entries for this start
if ($pagestate == "init")
{
    // create codes drop down
    $codebufr = u_dropdown_resultcodes($_SESSION['startcodes'], "short",
        "start_infringements_pg.php?eventid=$eventid&startnum=$startnum&pagestate=submit&entryid=ENTRY&boat=BOAT");
    
    // table with entries
    $entries = $race_o->race_getstarters(array("start"=>$startnum));

    $params = array(
        "entries" => count($entries),
        "code-bufr"  => $codebufr,
        "entry-data" => $entries,
    );
    $body = $tmpl_o->get_template("infringe", array(), $params);

    $fields = array(
        "title"      => "start infringements",
        "loc"        => $loc,
        "stylesheet" => "./style/rm_racebox.css",
        "navbar"     => "",
        "body"       => $body,
        "footer"     => "",
    );
    echo $tmpl_o->get_template("basic_page", $fields);
}

// change code for specified entry
elseif ($pagestate == "submit") 
{
    $entry  = $race_o->entry_get($_REQUEST['entryid'], "race");
    //u_writedbg ("<pre>".print_r($entry,true)."</pre>",__FILE__,__FUNCTION__,__LINE__);
    $update = $race_o->entry_code_set($_REQUEST['entryid'], $_REQUEST['code']);
    if ($update)
    {
        u_writelog("set code for {$_REQUEST['boat']} to [{$_REQUEST['code']}]", $eventid);
    }   
    header("Location: start_infringements_pg.php?eventid=$eventid&startnum=$startnum&pagestate=init");
}

// deal with invalid pagestate
else
{
    u_exitnicely("start_infringements_pg", $eventid, "errornum", "pagestate ($pagestate) is not recognised");
}

