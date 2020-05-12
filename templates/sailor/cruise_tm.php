<?php
/**
 * Templates for managing the leisure sailing sign in / return process
 *
 */

function cruise_control($params = array())
{
    $bufr = "";
    $instruction_bufr = "<br>";

    if ($params['state'] == "submitentry") {
        $event_list = "";
        foreach ($params['event-list'] as $eventid => $row)    // loop over events
        {
            $cruise_type = $row['cruise-type'];

            // event identity
            //$event_time = $row['time'] . " - " . $row['name'];   FIXME - is this required

            // signon button
            $signon_btn = signon_btn($eventid, $row['entry-status'], $cruise_type);

            // declare (signoff) button
            $declare_btn = declare_btn($params['declare_opt'], $eventid, $row['entry-status'], $cruise_type);

            // entry status
            $entry_status_txt = entry_status_txt($row['entry-status'], $row['entry-alert']);

            // end message
            $end_message = end_message($row['entry-status']);

            // add components to row for this event
            $event_list .= <<<EOT
            <div class="row margin-top-20">
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 col-lg-offset-1">
                  <span class="rm-text-sm rm-text-trunc text-success">{$row['time']}<br>{$row['name']}</span>
                </div>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                  <span class="rm-text-sm rm-text-trunc text-success">$signon_btn</span>
                </div>
EOT;
            if ($params['declare_opt']) {
                $event_list .= <<<EOT
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                  <span class="rm-text-sm rm-text-trunc text-success">$declare_btn</span>
                </div>
EOT;
            }

            $event_list .= <<<EOT
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                      <span class="rm-text-sm rm-text-trunc text-warning">$entry_status_txt</span>
                </div>
            </div>
EOT;

            // composite template
            $bufr.= <<<EOT
            <div class="row margin-top-10">
                <div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">   
                {boat-label}
                </div>
            </div>
            
            <div class="row margin-top-10">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 col-lg-offset-1">   
                    $instruction_bufr  
                    <form id="confirmform" action="course_sc.php" method="post" role="submit" autocomplete="off">      
                        $event_list
                    </form>
                    <hr>
                    <p class="rm-text-sm text-warning ">$end_message</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
                    <a href="boatsearch_pg.php" class="btn btn-info btn-md rm-text-bg pull-right" role="button" >
                        <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span> &nbsp;Done ...
                    </a>
                </div>
            </div>
EOT;

            return $bufr;
        }
    } else {
        // deal with unknown state - error   FIXME go to standard error page with a restart button
        $bufr .= "error";
        return $bufr;
    }
}


function end_message($entry_status)
{

    if ($entry_status == "registered") {
        $end_message = "Be careful - have a good sail";
    } elseif ($entry_status == "updated") {
        $end_message = "Thanks for the update - have a good sail";
    } elseif ($entry_status == "returned") {
        $end_message = "All done thank you";
    } else {
        $end_message = "";
    }

    return $end_message;
}


function entry_status_txt($entry_status, $entry_alert)
{
    if (empty($entry_alert)) {
        $txt = $entry_status;
    } else {
        $txt = $entry_alert;
    }

    return $txt;
}


function signon_btn($eventid, $entry_status, $cruise_type)
{
    $bufr = "";

    if ($entry_status == "registered" or $entry_status == "updated")    // registered/updated allow update
    {
        $mode = "btn-info";
        $label = "Update Details";
    } elseif ($entry_status == "returned")    // returned allow registration
    {
        $mode = "btn-default disabled";
        $label = "Register Cruise";
    } else // not entered yet - green button
    {
        $mode = "btn-success";
        $label = "Register Cruise";
    }

    $bufr.= <<<EOT
        <a href="cruise_sc.php?opt=register&event=$eventid&cruise_type=$cruise_type" type='button' 
           class='margin-bottom-10 rm-text-xs btn btn-sm $mode' >$label</a>
EOT;

    return $bufr;
}


function declare_btn($option, $eventid, $entry_status, $cruise_type)
{
    $bufr = "";

    if ($option)
    {
        $mode = "btn-default disabled";
        if ($entry_status == "registered" or $entry_status == "updated") {
            $mode = "btn-warning";
        }

        $bufr.= <<<EOT
            <a href="cruise_sc.php?opt=declare&event=$eventid&cruise_type=$cruise_type" type='button' 
            class='margin-bottom-10 rm-text-xs btn btn-sm $mode' >Returned</a>
EOT;
    }

    return $bufr;
}


function list_tide($params = array())
{
    $bufr = "";

    if (!empty($params)) {
        $hw1 = "";
        if (!empty($params['hw1_time'])) {
            $hw1 = "<span class = ''>{$params['hw1_time']} [{$params['hw1_height']}m]</span>";
        }

        $hw2 = "";
        if (!empty($params['hw2_time'])) {
            $hw2 = " - <span class = ''>{$params['hw2_time']} [{$params['hw2_height']}m]</span>";
        }

        $bufr.= <<<EOT
        <div>
            <p>
                <span class = "text-success rm-text-bg ">Tide today (HW):</span>
                <span class = " rm-text-bg">$hw1 $hw2</span>
            </p>
        </div>
EOT;
    }

    return $bufr;
}

//function change_fm($params = array())          FIXME - can this be removed
//{
//    $lbl_width  = "col-xs-3";
//    $fld_width  = "col-xs-7";
//    $fld_narrow = "col-xs-3";
//
//    // deal with helm if points accumulated by boat
//    $helm_bufr = "";
//    if ($params['points_allocation'] == "boat")
//    {
//        $helm_bufr.= <<<EOT
//    <div class="form-group form-condensed">
//        <label for="helm" class="rm-form-label control-label $lbl_width">Helm</label>
//        <div class="$fld_width">
//            <input name="helm" autocomplete="off" type="text" class="form-control input-lg rm-form-field" id="idhelm" value="{helm}">
//        </div>
//    </div>
//EOT;
//    }
//
//    // deal with singlehanders
//    $crew_bufr = "";
//    if (!$params['singlehander'])
//    {
//        $crew_bufr.= <<<EOT
//    <div class="form-group form-condensed">
//        <label for="crew" class="rm-form-label control-label $lbl_width">Crew</label>
//        <div class="$fld_width">
//            <input name="crew" autocomplete="off" type="text" class="form-control input-lg rm-form-field" id="idcrew" value="{crew}" >
//        </div>
//    </div>
//EOT;
//    }
//
//    $bufr = <<<EOT
//    <div class="rm-form-style">
//
//        <div class="row">
//            <div class="col-xs-10 col-sm-10 col-md-8 col-lg-8 alert alert-info"  role="alert">Change details as necessary...</div>
//        </div>
//
//        <form id="editboatForm" class="form-horizontal" action="change_sc.php" method="post">
//            <div class=""><input name="compid" type="hidden" id="idcomp" value="{compid}"></div>
//
//            $helm_bufr
//
//            $crew_bufr
//
//            <div class="form-group form-condensed">
//                <label for="sailnum" class="rm-form-label control-label $lbl_width">Sail No.</label>
//                <div class="$fld_narrow">
//                    <input name="sailnum" autocomplete="off" type="text" class="form-control input-lg rm-form-field" id="idsailnum" value="{sailnum}" >
//                </div>
//            </div>
//
//
//            <div class="row margin-top-20">
//                <div class = "col-xs-10 col-xs-offset-3 col-sm-10  col-sm-offset-3 col-md-8  col-md-offset-2 col-lg-8 col-lg-offset-3">
//                    <label class="radio-inline">
//                        <input type="radio" name="scope" class="rm-form-label" value="temp" checked>
//                        &nbsp;Just for today &nbsp;&nbsp;&nbsp;
//                    </label>
//                    <label class="radio-inline">
//                        <input type="radio" name="scope" class="rm-form-label" value="perm">
//                        &nbsp;Today and all future races
//                    </label>
//                </div>
//            </div>
//
//            <div class="pull-right margin-top-20">
//                <button type="button" class="btn btn-default btn-lg" onclick="history.go(-1);">
//                    <span class="glyphicon glyphicon-remove"></span>&nbsp;Cancel
//                </button>
//                &nbsp;&nbsp;&nbsp;&nbsp;
//                <button type="submit" class="btn btn-warning btn-lg" >
//                    <span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;<b>Change Details</b>
//                </button>
//            </div>
//
//        </form>
//    </div>
//EOT;
//    return $bufr;
//}

