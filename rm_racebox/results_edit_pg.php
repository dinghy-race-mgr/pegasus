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

u_initpagestart($_REQUEST['eventid'], $page, true);   // starts session and sets error reporting

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/race_class.php");

// FIXME - sort out arg processing + just display message
if (empty($_REQUEST['pagestate']) OR empty($_REQUEST['eventid']) OR empty($_REQUEST['entryid']))
{
    u_exitnicely($scriptname, $_REQUEST['eventid'],"$page page - input parameters eventid [{$_REQUEST['eventid']}], pagestate [{$_REQUEST['pagestate']}] or entryid [{$_REQUEST['entryid']}] is missing",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}
else
{
    $pagestate = $_REQUEST['pagestate'];
    $eventid   = $_REQUEST['eventid'];
    $entryid   = $_REQUEST['entryid'];
}

$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/results_tm.php"));

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
    $resultedit_fields = $race_o->entry_get_timings($entryid);
    $resultedit_fields["eventid"] = $eventid;

    $resultedit_params = array("eventid" => $eventid,
                               "entryid" => $entryid,
                               "resultcodes" => $_SESSION['resultcodes'],
                               "points_allocation" => $_SESSION['points_allocation'],
                               "laptimes" => $resultedit_fields["laptimes"],
                               "code" => $resultedit_fields['code'],
                               "etime" => $resultedit_fields['etime'],
        );

    $pagefields['body'] = $tmpl_o->get_template("fm_result_edit", $resultedit_fields, $resultedit_params);  // create edit form
    echo $tmpl_o->get_template("basic_page", $pagefields, array("form_validation"=>true));                  // create page with form

}
elseif ($pagestate == "submit")       // update t_race and t_lap records
{
    // get existing record and change lap times to array
    $old = $race_o->entry_get_timings($entryid);
    $laptimes = $race_o->entry_laptimes_get($entryid);

    // convert returned field values
    $edit_str = "";
    $edit = array();
    if (!empty($_REQUEST['helm']))    { ucwords($edit['helm'] = $_REQUEST['helm']); }
    $edit['crew']    = ucwords($_REQUEST['crew']);
    $edit['club']    = u_getclubname($_REQUEST['club']);
    $edit['sailnum'] = $_REQUEST['sailnum'];
    $edit['etime']   = u_conv_timetosecs($_REQUEST['etime']);
    $edit['code']    = $_REQUEST['code'];
    $edit['note']    = $_REQUEST['note'];
    if (ctype_digit($_REQUEST['pn']) )      { $edit['pn']      = (int)$_REQUEST['pn']; }
    if (ctype_digit($_REQUEST['lap']) )     { $edit['lap']     = (int)$_REQUEST['lap']; }
    $edit['penalty'] = (int)$_REQUEST['penalty'];

    // check which fields have changed - remove unchanged fields and create audit string for log
    foreach ($edit as $k => $v) {
        if ($old[$k] === $edit[$k]) {
            unset($edit[$k]);
        } else {
            $edit_str .= "$k:$v ";
        }
    }

    // calculate new clicktime
    $edit['clicktime'] = $old['clicktime'] + ($old['etime'] - $edit['etime']);

    // calculate new corrected time
    $edit['ctime'] = $race_o->entry_calc_ct($edit['etime'], $edit['pn'], $_SESSION["e_$eventid"]["fl_{$old['fleet']}"]['scoring']);

    // update race result in t_race
    $update = $race_o->entry_update($entryid, $edit);

    // delete and add finish lap time to t_lap
    $del = $race_o->entry_lap_delete($entryid, $edit['lap']);
    $lap_arr = array("lap" => $edit['lap'], "clicktime" => $edit['clicktime'], "etime" => $edit['etime'], "ctime" => $edit['ctime']);
    $add_lap = $race_o->entry_lap_add($old['fleet'], $entryid, $lap_arr);

    // check for missing laps in t_lap - and add placeholders if necessary
    for ($x = 1; $x < $edit['lap']; $x++)  // loop through laps
    {
        // check if lap is missing - if so create null lap (times set to 0)
        if (!key_exists($x, $laptimes))
        {
            $add_new_lap = $race_o->entry_lap_add($old['fleet'], $entryid, array("lap" => $x, "clicktime" => 0, "etime" => 0, "ctime" => 0));
        }
    }

    // update results status - needs recalculating
    $_SESSION["e_$eventid"]['result_valid'] = false;

    // log change
    u_writelog("Result Update - {$old['class']} {$old['sailnum']} : edit_str", $eventid);

    $data = array(
        "maxlap" => $_SESSION["e_$eventid"]["fl_{$old['fleet']}"]['maxlap'],
    );
    $warnings = check_edit($edit, $data);

    // debug stuff
    /*
    echo "<pre> REQUEST ARR : ".print_r($_REQUEST,true)."</pre>";
    echo "<pre> EXISTING RECORD : ".print_r($old,true)."</pre>";
    echo "<pre> EXISTING LAP TIMES : ".print_r($laptimes,true)."</pre>";
    echo "<pre> EDIT INITIAL FIELDS: ".print_r($edit,true)."</pre>";
    echo "<pre> EDIT FINAL FIELDS: ".print_r($edit,true)."</pre>";
    echo "<pre> EDIT STRING: $edit_str</pre>";
    echo "<pre> UPDATE T_RACE : $update</pre>";
    echo "<pre> DEL LAP : $del</pre>";
    echo "<pre> NEW LAP ARR : ".print_r($lap_arr,true)."</pre>";
    echo "<pre> ADD LAP : $add_lap</pre>";
    */

    if ($warnings)
    {
        $pagefields['body'] = $tmpl_o->get_template("result_edit_warnings", array(),
            array("warnings" => $warnings, "eventid" => $eventid, "entryid" => $entryid));  // warnings layout
        echo $tmpl_o->get_template("basic_page", $pagefields, array());
    }
    else
    {
        // return to main results page - closing modal
        $stop_here = false;
        if (!$stop_here)
        {
            echo <<<EOT
            <script "text/javascript">
            window.top.location.href = 'results_pg.php?eventid=$eventid';
            </script>
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


function check_edit($edit, $data)
{
    $issues = array(
        1 => array( "type" => "error", "title" => "Finish Lap", "msg" => "Finish lap cannot be greater than the no. of laps set"),
        2 => array( "type" => "warning", "title" => "Penalty Points", "msg" => "Penalty points should only be used with a code of BPI"),
        3 => array( "type" => "warning", "title" => "Scoring Codes", "msg" => "The DSQ and RDG scoring codes should only be used following a protest/redress meeting ")
    );

    $warnings = array();
    if ($edit['lap'] > $data['maxlap'])
    {
        $warnings[] = $issues[1];
    }

    if ($edit['penalty'] > 0 AND $edit['code'] != "BPI")
    {
        $warnings[] = $issues[2];
    }

    if ($edit['code'] == 'DSQ' OR $edit['code'] == "RDG")
    {
        $warnings[] = $issues[3];
    }

    return $warnings;
}

function check_lap_problems($laptimes_str)     // this function is also on the timer page
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



