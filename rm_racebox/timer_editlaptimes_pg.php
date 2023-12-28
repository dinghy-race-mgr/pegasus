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

// start session
u_startsession("sess-rmracebox", 10800);

// arguments
$eventid   = u_checkarg("eventid", "checkintnotzero","");   // eventid (required)
$pagestate = u_checkarg("pagestate", "set", "", "");        // pagestate (required)
$entryid   = $_REQUEST['entryid'];                          // entryid (required)
$clear     = u_checkarg("clear", "setbool", 1, 0);          // clear

if (empty($_REQUEST['pagestate']) OR empty($_REQUEST['eventid']) OR empty($_REQUEST['entryid']))
{
    u_exitnicely($scriptname, 0, "$page page - the requested event has an missing/invalid record identifier [{$_REQUEST['eventid']}] or pagestate [{$_REQUEST['pagestate']}",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

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

// if call from timer page - initialise lap edit session array
if ($clear)
{
    $_SESSION["e_$eventid"]['lapeditmsg'] = array("type"=>"", "msg"=>"");
}

if ($pagestate == "init")             // display form with lap times for each lap and msg from previous action is set
{
    $params = array("eventid"=>$eventid, "laps"=>$laptimes);

    $pagefields['body'] = $tmpl_o->get_template("fm_editlaptimes", $boat_detail, $params);  // create edit form
    echo $tmpl_o->get_template("basic_page", $pagefields, array("form_validation"=>true));  // create page with form

    // initialise response variable
    $_SESSION["e_$eventid"]['lapeditmsg'] = array("type"=>"", "msg"=>"");

}
elseif ($pagestate == "addlap")
{
    $action = "remove";
    $lap = u_checkarg("lap", "checkintnotzero", "");
    if ($eventid and $entryid and $lap)
    {
        // get all lap data for this entry
        $lapdata = $race_o->entry_lap_get($entryid, "all");
        $lapdata_reverse = array_reverse($lapdata,true);           // reorder so laps can be processed in reverse order

        // find lap to use as donor data for the new lap
        if ($lap >= count($lapdata))       // a new lap at the end - use last lap
        {
            $key = array_search(count($lapdata), array_column($lapdata, 'lap'));
        }
        else                               // use next lap
        {
            $key = array_search($lap, array_column($lapdata, 'lap'));
        }
        $detail = $lapdata[$key];
        unset($detail['id']);
        $detail['lap'] = $lap;

        // loop through laps in reverse order resetting the lap numbers
        foreach ($lapdata_reverse as $row)
        {
            if ($row['lap'] >= $lap)
            {
                $newlap = $row['lap'] + 1;
                $upd = $race_o->entry_lap_update($entryid, $boat_detail['fleet'], $row['lap'], $boat_detail['pn'], array("lap"=>$newlap) );
            }
        }

        // get all lap data for this entry
        $lapdata = $race_o->entry_lap_get($entryid, "all");

        // insert lap
        $ins = $race_o->entry_lap_add($boat_detail['fleet'], $entryid, $detail);

        // get all lap data for this entry after insert
        $lapdata = $race_o->entry_lap_get($entryid, "all");
        $num_laps = count($lapdata);

        // update t_race record (lap + status)
        $record = $race_o->entry_get($entryid);
        $change = array("lap"=>$num_laps);
        if ($record['status'] == "R" and $num_laps >= $record['finishlap'])  // now nfinished
        {
            $change['status'] = "F";
        }
        $upd = $race_o->entry_update($entryid, $change);

        if ($ins)
        {
            $_SESSION["e_$eventid"]['lapeditmsg']['type'] = "success";
            $_SESSION["e_$eventid"]['lapeditmsg']['msg'] = "A new lap $lap has been created for {$boat_detail['boat']} and the following laps renumbered
             - now you can add the correct elapsed time for that lap.";
        }
        else
        {
            $_SESSION["e_$eventid"]['lapeditmsg']['type'] = "danger";
            $_SESSION["e_$eventid"]['lapeditmsg']['msg'] = "The attempt to add a new lap $lap for {$boat_detail['boat']} has failed.";
        }
    }
    else
    {
        $_SESSION["e_$eventid"]['lapeditmsg']['type'] = "danger";
        $_SESSION["e_$eventid"]['lapeditmsg']['msg'] = "The attempt to add a new lap for {$boat_detail['boat']} failed due to invalid data.";
    }
    header("Location: timer_editlaptimes_pg.php?eventid=$eventid&entryid=$entryid&pagestate=init");
    exit();
}
elseif ($pagestate == "removelap")
{
    $action = "remove";
    $lap = u_checkarg("lap", "checkintnotzero", "");
    if ($eventid and $entryid and $lap)
    {
        // get lap data
        $lapdata = $race_o->entry_lap_get($entryid, "all");

        // delete requested lap
        $del  = $race_o->entry_lap_delete($entryid, $lap);

        if ($del)
        {
            // get all lap data after delete
            $lapdata = $race_o->entry_lap_get($entryid, "all");
            $num_laps = count($lapdata);

            // renumber laps as required
            foreach ($lapdata as $row)
            {
                if ($row['lap'] > $lap)
                {
                    $newlap = $row['lap'] - 1;
                    $upd = $race_o->entry_lap_update($entryid, $boat_detail['fleet'], $row['lap'], $boat_detail['pn'], array("lap"=>$newlap) );
                }
            }

            // update t_race record (lap + status)
            $record = $race_o->entry_get($entryid);
            $change = array("lap"=>$num_laps);
            if ($record['status'] == "F" and $num_laps < $record['finishlap'])  // now unfinished
            {
                $change['status'] = "R";
            }
            $upd = $race_o->entry_update($entryid, $change);

            $_SESSION["e_$eventid"]['lapeditmsg']['type'] = "success";
            $_SESSION["e_$eventid"]['lapeditmsg']['msg'] = "Lap $lap has been removed for {$boat_detail['boat']} and the remaining laps renumbered.";
        }
        else
        {
            $_SESSION["e_$eventid"]['lapeditmsg']['type'] = "danger";
            $_SESSION["e_$eventid"]['lapeditmsg']['msg'] = "The attempt to remove lap $lap for {$boat_detail['boat']} has failed.";
        }
    }
    else
    {
        echo "lap not set"; exit();

    }
    header("Location: timer_editlaptimes_pg.php?eventid=$eventid&entryid=$entryid&pagestate=init");
    exit();

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

        empty($rs_msg) ? $changes = false : $changes = true;

        if (!empty($rs_msg))
        {
            $_SESSION["e_$eventid"]['lapeditmsg']['type'] = "success";
            $_SESSION["e_$eventid"]['lapeditmsg']['msg'] = "changes were made to the lap times for {$boat_detail['boat']}:- <br> $rs_msg";
        }
        else
        {
            $_SESSION["e_$eventid"]['lapeditmsg']['type'] = "info";
            $_SESSION["e_$eventid"]['lapeditmsg']['msg'] = "no changes were made to the lap times for {$boat_detail['boat']}";
        }

    }
    else   // produce error page
    {
        $_SESSION["e_$eventid"]['lapeditmsg']['type'] = "danger";
        $_SESSION["e_$eventid"]['lapeditmsg']['msg'] = "No changes were made to the lap times for {$boat_detail['boat']} - the following problems were identified:- <br> {$rs['msg']}";

    }
    header("Location: timer_editlaptimes_pg.php?eventid=$eventid&entryid=$entryid&pagestate=init");
    exit();
}
else  // pagestate not recognised
{
    u_exitnicely($scriptname, 0, "$page page - the pagestate value [{$_REQUEST['pagestate']} is not recognised",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

