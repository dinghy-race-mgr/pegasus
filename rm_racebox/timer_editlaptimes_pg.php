<?php
/* ------------------------------------------------------------
   timer_editlaptimes_pg
   Allows OOD to edit recorded lap times for a competitor.
   
   arguments:
       eventid     id of event in t_event
       pagestate   control state for page
       entryid     id for entry in t_race
   ------------------------------------------------------------
*/

$loc        = "..";       // <--- relative path from script to top level folder
$page       = "timer_editlaptimes";     //
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");

// process standard parameters  (eventid, pagestate, entryid)
$eventid   = u_checkarg("eventid", "checkintnotzero","");
$pagestate = u_checkarg("pagestate", "set", "", "");
$entryid   = $_REQUEST['entryid'];

if (empty($_REQUEST['pagestate']) OR empty($_REQUEST['eventid']) OR empty($_REQUEST['entryid']))
{
    u_exitnicely($scriptname, 0, "$page page - the requested event has an missing/invalid record identifier [{$_REQUEST['eventid']}] or pagestate [{$_REQUEST['pagestate']}",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// start session
session_id('sess-rmracebox');
session_start();

// page initialisation
u_initpagestart($eventid, $page, true);

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/race_class.php");

$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/timer_tm.php"));

$pagefields = array(
    "id"         => "editlap",
    "title"      => "edit lap times",
    "theme"      => $_SESSION['racebox_theme'],
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "body_attr"  => "margin-top-0",
    "navbar"     => "",
    "footer"     => "",
    "form_validation" => true,
);

$db_o   = new DB;                       // create database object
$race_o = new RACE($db_o, $eventid);    // create race object

// get entry details - including existing lap times
$laps_rs = $race_o->entry_get_timings($entryid);
$laptimes = $race_o->entry_laptimes_get($entryid);

// set key parameters
$boat_detail = array(
    "eventid" => $eventid,
    "entryid" => $entryid,
    "fleet"   => $laps_rs['fleet'],
    "boat"    => $laps_rs['class']." - ".$laps_rs['sailnum'],
    "pn"      => $laps_rs['pn'],
);

if ($pagestate == "init")             // display form with lap times for each lap
{
    $pagefields['body'] = $tmpl_o->get_template("fm_editlaptimes", $boat_detail, $laptimes);  // create edit form
    echo $tmpl_o->get_template("basic_page", $pagefields, array("form_validation"=>true));                                   // create page with form

}
elseif ($pagestate == "submit")       // correct modified lap times and return to display lap times
{
    //echo "<pre>".print_r($laps_rs,true)."</pre>";

    // check for issues with user input
    $newlaptimes = array();
    foreach($_REQUEST['etime'] as $lap=>$etime)
    {
        $newlaptimes[$lap] = u_conv_timetosecs($etime);
    }
    $rs = $race_o->entry_laptime_check($newlaptimes);               // checks if modified laptimes are OK

    if (!$rs['err'])                                                // no errors - process the modified lap times
    {
        $rs_msg = "";
        foreach($newlaptimes as $lap=>$etime)
        {
            if ($etime!= $laptimes[$lap])
            {
                //echo "processing lap $lap ($etime) - ({$laps_rs['lap']})<br>";
                $upd  = $race_o->entry_lap_update($entryid, $boat_detail['fleet'], $lap, $boat_detail['pn'], array("etime"=>$etime));
                $rs_msg.= $upd['msg'];

                if ($upd['status'])       // check if data in t_race also needs to be updated
                {
                    if ($lap == $laps_rs['lap'])
                    {
                        // get etime for previous lap
                        if ($lap == 1) {
                            $prev_et = 0;
                        } else {
                            $lap_prev = $race_o->entry_lap_get($entryid, "lap", $lap - 1);
                            $prev_et = $lap_prev['etime'];
                        }

                        // prepare update array
                        $update = array(
                            "etime"     => $etime,
                            "ctime"     => $upd['ctime'],
                            "ptime"     => $race_o->entry_calc_pt($etime, $prev_et, $lap) ,
                            "clicktime" => $upd['clicktime'],
                        );

                        // update t_race record
                        $race_upd = $race_o->entry_update($entryid, $update);
                    }
                }
            }
        }

        $pagefields['body'] = $tmpl_o->get_template("edit_laps_success",
            array("boat"=>$boat_detail['boat'], "msg"=>$rs_msg, "eventid"=>$eventid, "entryid"=>$entryid));
        echo $tmpl_o->get_template("basic_page", $pagefields);
    }
    else   // produce error page
    {
        $pagefields['body'] = $tmpl_o->get_template("edit_laps_error",
            array("boat"=>$boat_detail['boat'], "msg"=>$rs['msg'], "eventid"=>$eventid, "entryid"=>$entryid));
        echo $tmpl_o->get_template("basic_page", $pagefields);
    }
}
else  // pagestate not recognised
{
    u_exitnicely($scriptname, 0, "$page page - the pagestate value [{$_REQUEST['pagestate']} is not recognised",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}


function check_lap_problems($laptimes_str)
/*
checks for problems with the lap time sequence presented
*/
{
    $rs = array(
        "times" => array(),
        "err"   => false,
        "msg"   => "",
    );

    $prev = 0;
    foreach($laptimes_str as $lap=>$time)
    {
        $rs['times'][$lap] = strtotime("1970-01-01 $time UTC");;
        if ($rs['times'][$lap] == 0)
        {
            $rs['msg'].= "<p><b>lap $lap</b> has an elapsed time of 0 secs</p>";
            $rs['err'] = true;
        }
        if ($lap > 1 and $prev >= $rs['times'][$lap])
        {
            $rs['msg'].= "<p><b>lap $lap</b> must have an elapsed greater than the previous lap</p>";
            $rs['err'] = true;
        }
        $prev = $rs['times'][$lap];
    }
    return $rs;
}


