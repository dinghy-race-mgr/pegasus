<?php
/**
 * pickrace_sc.php
 * 
 * @abstract Processes server requests from the pickrace page
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *
*/
$loc        = "..";                                              // relative path from script to top level folder
$page       = "pickrace";
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/event_class.php");

u_initpagestart("", $page, false);                               // starts session and sets error reporting
//include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");  // language file

if (!empty($_REQUEST['pagestate']))
{
    $pagestate = $_REQUEST['pagestate'];
    include("../templates/racebox/growls.php");

    $db_o = new DB;
    $event_o = new EVENT($db_o);

    if ($pagestate == "addrace")
    {
        // set fields to create event record
        $fields = array(
            "event_name"   => $_REQUEST['eventname'],
            "event_date"   => $_REQUEST['eventdate'],
            "event_start"  => $_REQUEST['starttime'],
            "event_format" => $_REQUEST['eventformat'],
            "event_entry"  => $_REQUEST['evententry'],
            "event_type"   => "racing",
        );

        if (!empty($_REQUEST['seriesname']))
        {
            $fields['series_code']  = u_getseriesname($_REQUEST['seriesname'], $_REQUEST['eventdate']);
        }

        $duties[0] = array ("dutycode" => "ood_p", "person" => $_REQUEST['oodname']);

        // add event to t_event
        $add = $event_o->event_addevent($fields, $duties);
        if ($add == "ok")
        {
            u_writelog("created new event - {$_REQUEST['eventname']} on {$_REQUEST['eventdate']}", "");
            u_growlSet(0, $page, $g_add_event_success, array($_REQUEST['eventname'], $_REQUEST['eventdate']));
        }
        
        elseif ( $add == "dutyfailed" )  // report duties not added
        {
            u_writelog("event {$_REQUEST['eventname']} created but duties not added", "");
            u_growlSet(0, $page, $g_add_event_warning, array($_REQUEST['eventname'], $_REQUEST['eventdate']));
        }
    
        else   // report other failures
        {
            u_writelog("attempt to create new event {$_REQUEST['eventname']} failed with error code: $add", "");
            u_growlSet(0, $page, $g_add_event_fail, array($_REQUEST['eventname'], $add));
        }
        // return to race page
        header("Location: pickrace_pg.php?");
        exit();
    }
    
    else
    {
        u_exitnicely($scriptname, $eventid, "event001", $lang['err']['exit-action']);        // FIXME
    }
}
else
{
    u_exitnicely($scriptname, $eventid, "sys005", $lang['err']['exit-action']);              // FIXME
}

