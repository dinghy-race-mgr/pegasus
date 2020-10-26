<?php
/**
 * Templates for managing the leisure sailing sign in / return process
 *
 */

function cruise_control($params = array())
{
    $bufr = "";
    $instruction_bufr = "<br>";

    $event_list = "";
    $end_message = "";
    foreach ($params['event-list'] as $eventid => $row)    // loop over events
    {
        $cruise_type = $row['cruise-type'];

        if ($row['event-status'] == "cancelled") {

            $event_list .= <<<EOT
        
            <div class="row margin-top-20">
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 col-lg-offset-1">
                    <span class="rm-text-sm rm-text-trunc text-success">{$row['time']}<br>{$row['name']}</span>
                </div>
                <div class="col-xs-9 col-sm-9 col-md-9 col-lg-7">
                    <span class="rm-text-sm rm-text-trunc text-warning">this event is CANCELLED</span>
                </div>
            </div>
EOT;
        } else {

            // signon button
            $signon_btn = signon_btn($eventid, $row['entry-status'], $cruise_type);

            // declare (signoff) button
            $declare_btn = declare_btn($params['declare_opt'], $eventid, $row['entry-status'], $cruise_type);

            // entry status
            $entry_status_txt = entry_status_txt($row['entry-status'], $row['entry-alert'], $row['time_in'], $row['time_out']);

            // end message
            $end_message = end_message($row['entry-status']);

            // add components to row for this event
            $event_list .= <<<EOT
            <div class="row margin-top-20">
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 col-lg-offset-1">
                    <span class="rm-text-sm rm-text-trunc">{$row['time']}<br>{$row['name']}</span>
                </div>
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                    <span class="rm-text-sm text-success">$signon_btn</span>
                </div>
EOT;
            if ($params['declare_opt']) {
                $event_list .= <<<EOT
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-2">
                    <span class="rm-text-sm text-success">$declare_btn</span>
                </div>
EOT;
            }

            $event_list .= <<<EOT
                <div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
                    <span class="rm-text-sm rm-text-trunc text-warning">$entry_status_txt</span>
                </div>
            </div>
EOT;
        }
    }

    // build complete template
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
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-right">
            <a href="search_pg.php" class="btn btn-info btn-sm rm-text-sm" role="button" >
                <span class="glyphicon glyphicon-step-backward" aria-hidden="true"></span>&nbsp;Back to Start ...
            </a>
        </div>
    </div>
EOT;

    return $bufr;

}


function end_message($entry_status)
{
    if ($entry_status == "registered") {
        $txt = "Be careful - have a good sail";
    } elseif ($entry_status == "updated") {
        $txt = "Thanks for the update - have a good sail";
    } elseif ($entry_status == "returned") {
        $txt = "All done thank you";
    } else {
        $txt = "";
    }

    return $txt;
}


function entry_status_txt($entry_status, $entry_alert, $time_in, $time_out)
{
    if (empty($entry_alert)) {
        if ($entry_status == "registered") {
            $txt = "Registered &nbsp; [ $time_in ]";
        } elseif ($entry_status == "updated") {
            $txt = "Details Updated &nbsp; [ $time_in ]";
        } elseif ($entry_status == "returned") {
            $txt = "Back Ashore &nbsp; [ $time_out ]";
        } else {
            $txt = "";
        }
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


