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

$loc        = "..";                                    // <--- relative path from script to top level folder
$page       = "results_edit";
$stop_here  = false;
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");

// start session
u_startsession("sess-rmracebox", 10800);

// arguments
$eventid   = u_checkarg("eventid", "checkintnotzero","", false);  // eventid (required)
$pagestate = u_checkarg("pagestate", "set", "", false);           // pagestate (required)
$entryid   = u_checkarg("entryid", "set", "", "");                // entryid (required)
if (empty($eventid) OR empty($pagestate) OR empty($entryid))
{
    u_exitnicely($scriptname, $_REQUEST['eventid'],"$page page - input parameters eventid [{$_REQUEST['eventid']}], pagestate [{$_REQUEST['pagestate']}] or entryid [{$_REQUEST['entryid']}] is missing",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// page initialisation
u_initpagestart($eventid, $page, true);   // starts session and sets error reporting

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/race_class.php");

// templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php",
                             "./templates/results_tm.php", "./templates/pursuit_tm.php"));

$pagefields = array(
    "id"         => "resultedit",
    "title"      => "",
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

if ($pagestate == "init")               // display form with lap times for each lap
{

    if ($_SESSION["e_$eventid"]['pursuit'])
    {
        $entry = $race_o->race_getresults_pursuit(array("id"=>$entryid), "");
        $old = $entry[0];
        $old["eventid"] = $eventid;
        $old["fldw"] = "4";
        $old["lblw"] = "3";
        $old["hlpw"] = "4";

        $resultcodes = $_SESSION['resultcodes'];                          // remove codes not relevant to pursuit race
        unset($resultcodes['BFD'], $resultcodes['UFD']);

        $params = array(
            "eventid"           => $eventid,                                                     //
            "entryid"           => $entryid,                                                     //
            "scoring"           => $_SESSION["e_$eventid"]["fl_{$old['fleet']}"]['scoring'],     //
            "resultcodes"       => $resultcodes,                                                 //
            "points_allocation" => $_SESSION['points_allocation'],                               //
            "code"              => $old['code']                                                  //
        );

        $pagefields['body'] = $tmpl_o->get_template("fm_result_edit_pursuit", $old, $params);                    // create edit form
        echo $tmpl_o->get_template("basic_page", $pagefields, array("form_validation"=>true));           // create page with form
    }
    else
    {
        // get data for selected entry
        $old = $race_o->entry_get_timings($entryid);
        $old["eventid"] = $eventid;
        $old["fldw"] = "4";
        $old["lblw"] = "3";
        $old["hlpw"] = "4";

        $params = array(
            "eventid"           => $eventid,
            "entryid"           => $entryid,
            "scoring"           => $_SESSION["e_$eventid"]["fl_{$old['fleet']}"]['scoring'],
            "resultcodes"       => $_SESSION['resultcodes'],
            "points_allocation" => $_SESSION['points_allocation'],
            "laptimes"          => $old["laptimes"],
            "code"              => $old['code'],
            "etime"             => $old['etime']
        );

        $pagefields['body'] = $tmpl_o->get_template("fm_result_edit", $old, $params);                    // create edit form
        echo $tmpl_o->get_template("basic_page", $pagefields, array("form_validation"=>true));           // create page with form
    }

}
elseif ($pagestate == "submit-pursuit")
{
    $old = $race_o->entry_get_timings($entryid);

    // get returned field values
    $edit = get_edit_data_pursuit($_REQUEST);

    $warnings = check_edit($edit, array("maxlap" => 0), true);

    if ($warnings)
    {
        $pagefields['body'] = $tmpl_o->get_template("result_edit_warnings", array(),
            array("warnings" => $warnings, "eventid" => $eventid, "entryid" => $entryid));  // warnings layout
        echo $tmpl_o->get_template("basic_page", $pagefields, array());
    }
    else  // process changes
    {
        $edit_str = "";
        foreach ($edit as $k => $v)
        {
            if ($old[$k] != $edit[$k])
            {
                $edit_str .= "| $k:$v ";
            }
            else
            {
                unset($edit[$k]);
            }
        }

        // update race result in t_race
        $update = $race_o->entry_update($entryid, $edit);

        // update results status - needs recalculating
        $_SESSION["e_$eventid"]['result_valid'] = false;
    }

    // log change
    u_writelog("Result Update - {$old['class']} {$old['sailnum']} : changes [ $edit_str ]", $eventid);

    // return to main results page - closing modal
    if (!$stop_here)
    {
        echo <<<EOT
            <script "text/javascript"> window.top.location.href = 'results_pg.php?eventid=$eventid';</script>
EOT;
        exit();
    }
}
elseif ($pagestate == "submit")       // update t_race and t_lap records
{
    $dbg_on = false;

    // get existing record and change lap times to array
    $old = $race_o->entry_get_timings($entryid);
    $laptimes = $race_o->entry_laptimes_get($entryid);
    if ($dbg_on) { u_writedbg("<pre>RESULT REQUEST ARR:".print_r($_REQUEST,true)."</pre>", __FILE__, __FUNCTION__, __LINE__); }

    // get returned field values
    $edit = get_edit_data($_REQUEST);

    // do checks on edits
    $warnings = check_edit($edit, array("maxlap" => $_SESSION["e_$eventid"]["fl_{$old['fleet']}"]['maxlap']));
    if ($warnings)
    {
        $pagefields['body'] = $tmpl_o->get_template("result_edit_warnings", array(),
            array("warnings" => $warnings, "eventid" => $eventid, "entryid" => $entryid));  // warnings layout
        echo $tmpl_o->get_template("basic_page", $pagefields, array());
    }
    else // process changes
    {
        // check which fields have changed - remove unchanged fields and create audit string for log
        $lap_changed = false;
        $etime_changed = false;
        $edit_str = "";
        $txt = "";
        foreach ($edit as $k => $v)
        {

            if ($k == "lap")   {$lap = $v; }
            if ($k == "etime") {$etime = $v; }
            if ($k == "pn")    {$pn = $v; }

            if ($old[$k] != $edit[$k])
            {
                $edit_str .= "| $k:$v ";
                if ($k == "lap") { $lap_changed = true; }
                if ($k == "etime") { $etime_changed = true; }
            }
            else
            {
                unset($edit[$k]);
            }
        }
        if ($dbg_on) { u_writedbg("<pre>RESULT EDIT string:   $edit_str </pre>", __FILE__, __FUNCTION__, __LINE__); }

        $time = array();
        if ($etime_changed or $lap_changed) // assume just changing the time recorded for the finish lap
        {
            $race_scoring = $_SESSION["e_$eventid"]["fl_{$old['fleet']}"]['scoring'];

            $time = update_times($lap, $etime, $pn, $old, $laptimes, $race_scoring);

            $upd = update_lap($entryid, $old['fleet'], $lap, $time);
        }

        // combine edit and time changes
        if ($dbg_on) { u_writedbg("<pre>arr1 - edit:".print_r($edit,true)."</pre>", __FILE__, __FUNCTION__, __LINE__); }
        if ($dbg_on) { u_writedbg("<pre>arr2 - time:".print_r($time,true)."</pre>\"", __FILE__, __FUNCTION__, __LINE__); }
        $result_upd_arr = array_merge($edit, $time);
        if ($dbg_on) { u_writedbg("<pre>arr3 - combined:".print_r($result_upd_arr,true)."</pre>\"", __FILE__, __FUNCTION__, __LINE__); }

        // update race result in t_race
        $update = $race_o->entry_update($entryid, $result_upd_arr);

        // update results status - needs recalculating
        $_SESSION["e_$eventid"]['result_valid'] = false;

        // log change
        u_writelog("Result Update - {$old['class']} {$old['sailnum']} : changes [ $edit_str ]", $eventid);

        // return to main results page - closing modal
        if (!$stop_here)
        {
            echo <<<EOT
            <script "text/javascript"> window.top.location.href = 'results_pg.php?eventid=$eventid';</script>
EOT;
            exit();
        }
    }

}
else  // pagestate not recognised
{
    u_exitnicely($scriptname, $_REQUEST['eventid'],"$page page - pagestate value [{$_REQUEST['pagestate']}] not recognised",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}


function check_edit($edit, $data, $pursuit = false)
{
    $issues = array(
        1 => array( "type" => "error", "title" => "Finish Lap", "msg" => "Finish lap cannot be greater than the no. of laps set"),
        2 => array( "type" => "error", "title" => "Finish Time", "msg" => "Finish time is zero or not set"),
        3 => array( "type" => "warning", "title" => "Penalty Points", "msg" => "Penalty points should only be used with a code of BPI"),
        4 => array( "type" => "warning", "title" => "Scoring Codes", "msg" => "The DSQ and RDG scoring codes should only be used following a protest/redress meeting ")
    );

    $warnings = array();

    if ($pursuit === false)
    {
        if ($edit['lap'] > $data['maxlap'])
        {
            $warnings[] = $issues[1];
        }

        if ($edit['etime'] <= 0)
        {
            $warnings[] = $issues[2];
        }
    }


    if ((float)$edit['penalty'] > "0" AND $edit['code'] != "DPI")
    {
        $warnings[] = $issues[3];
    }

    if ($edit['code'] == 'DSQ' OR $edit['code'] == "RDG")
    {
        $warnings[] = $issues[4];
    }

    return $warnings;
}

//function check_lap_problems($laptimes_str)     // this function is also on the timer page
///*
//checks for problems with the lap time sequence presented
//*/
//{
//    $rs = array(
//        "times" => array(),
//        "err"   => false,
//        "msg"   => "",
//    );
//
//    $prev = 0;
//    foreach($laptimes_str as $lap=>$time)
//    {
//        $rs['times'][$lap] = strtotime("1970-01-01 $time UTC");;
//        if ($rs['times'][$lap] == 0)
//        {
//            $rs['msg'].= "<p><b>lap $lap</b> has an elapsed time of 0 secs</p>";
//            $rs['err'] = true;
//        }
//        if ($lap > 1 and $prev >= $rs['times'][$lap])
//        {
//            $rs['msg'].= "<p><b>lap $lap</b> must have an elapsed greater than the previous lap</p>";
//            $rs['err'] = true;
//        }
//        $prev = $rs['times'][$lap];
//    }
//    return $rs;
//}

function get_edit_data($args)
{
    $edit = array();


    if (!empty($args['helm']))    { $edit['helm'] = ucwords($args['helm']); }

    $edit['crew']    = ucwords($args['crew']);
    $edit['club']    = u_getclubname($args['club']);
    $edit['sailnum'] = $args['sailnum'];
    
    $edit['etime']   = u_conv_timetosecs($args['etime']);
    $edit['code']    = $args['code'];                                  //*
    $edit['note']    = $args['note'];

    if (!array_key_exists("code", $args) or empty($args['code']))
    {
        $edit['code'] = "";
    }
    else
    {
        $edit['code'] = $args['code'];
    }

    if (ctype_digit($args['pn']) )      { $edit['pn'] = (int)$args['pn']; }
    
    if (ctype_digit($args['lap']) )     { $edit['lap'] = (int)$args['lap']; }

    empty($args['penalty']) ? $edit['penalty'] = 0 : $edit['penalty'] = $args['penalty'];

    return $edit;
}

function get_edit_data_pursuit($args)
{
    $edit = array();


    if (!empty($args['helm']))    { $edit['helm'] = ucwords($args['helm']); }

    $edit['crew']    = ucwords($args['crew']);
    $edit['club']    = u_getclubname($args['club']);
    $edit['sailnum'] = $args['sailnum'];

    $edit['code']    = $args['code'];
    $edit['note']    = $args['note'];

    if (!array_key_exists("code", $args) or empty($args['code']))
    {
        $edit['code'] = "";
    }
    else
    {
        $edit['code'] = $args['code'];
    }

    empty($args['penalty']) ? $edit['penalty'] = 0 : $edit['penalty'] = $args['penalty'];

    return $edit;
}

function update_times($lap, $etime, $pn, $old, $laptimes, $race_scoring)
{
    global $race_o;
    $time = array();
    $time['clicktime'] = $old['clicktime'] - ($old['etime'] - $etime);                             // clicktime
    $time['etime'] = $etime;
    $time['ctime'] = $race_o->entry_calc_ct($etime, $pn, $race_scoring);                           // corrected
    $time['atime'] = $race_o->entry_calc_at($etime, $pn, $race_scoring, $lap, $old['finishlap']);  // aggregate

    if ($lap == 1)                                                                                 // predicted
    {
        $time['ptime'] = 2 * $etime;
    }
    else
    {
        $time['ptime'] = $race_o->entry_calc_pt($etime, $laptimes[$lap - 1], $lap);
    }

    return $time;
}

function update_lap($entryid, $fleetnum, $lap, $times)
{
    global $race_o;

    // update t_lap with new times  (clicktime, etime, ctime)
    $del = $race_o->entry_lap_delete($entryid, $lap);
    $lap_edit = array("lap" => $lap, "clicktime" => $times['clicktime'], "etime" => $times['etime'], "ctime" => $times['ctime']);
    $add_lap = $race_o->entry_lap_add($fleetnum, $entryid, $lap_edit);

    u_writedbg("<pre>LAP EDIT UPDATE: entry: $entryid| fleet: $fleetnum| lap: $lap|<br>".print_r($lap_edit,true)."</pre>", __FILE__, __FUNCTION__, __LINE__);

    return $add_lap;
}

