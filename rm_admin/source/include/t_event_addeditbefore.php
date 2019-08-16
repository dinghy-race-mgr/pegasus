<?php
$msg = "";
isset($oldvalues) ? $mode = "edit" : $mode = "add";
$values['event_date'] = date("Y-m-d", strtotime($values['event_date']));

// individual field checks
if ($values['event_type'] == "racing") {
    // check race format
    if (!f_check_exists("t_cfgrace", " id='{$values['event_format']}' ", $conn)) {
        $msg .= "- race format is not recognised<br>";
    }

    // check that at least one of start time or order on day is set
    if (!f_values_oneset($values['event_start'], $values['event_order'])) {
        $msg .= "- either the event start time or the order (1st, 2nd ..) must be defined<br>";
    }

    // check start_interval is set if start_scheme has been set
    if (!f_values_dependset($values['start_scheme'], $values['start_interval']))
    {
        $msg .= "- default start scheme has changed and the start interval must be set<br>";
    }

    // check series code exists
    if (!empty($values['series_code']))
    {
        $series_root = get_series_root($values['series_code']);
        if (!f_check_exists("t_series", " seriescode='$series_root' ", $conn)) {

            $msg .= "- series code [{$values['series_code']}] is not recognised<br>";
        }
    }

    // check entry type is set
    if (empty($values['event_entry']))
    {
        $msg.= "- race entry method must be set<br>";
    }

}
elseif ($values['event_type'] == "training")
{
    // no additional checks required
}
elseif  ($values['event_type'] == "social")
{
    // no additional checks required
}
elseif  ($values['event_type'] == "cruise")
{
    // no additional checks required
}

empty($msg) ? $commit = true : $commit = false;

// field checks complete
if ($commit)
{
    // check if we need to reset tide (tide data empty or data changed)
    $tide_reset = false;
    if (!empty($values['event_start']) and (empty($values['tide_time']) or empty($values['tide_height'])))
    {
        $tide_reset = true;
    }

    if ($mode == "edit" and !empty($values['event_start']))
    {
        if (($values['event_date'] != $oldvalues['event_date']) or ($values['event_start'] != $oldvalues['event_start']))
        {
            $tide_reset = true;
        }
    }


    if ($tide_reset)
    {
        if (f_check_exists("t_tide", " date = '{$values['event_date']}' ", $conn))
        {
            $tide_rs = f_get_row("t_tide", "hw1_time,hw1_height,hw2_time,hw2_height", " date = '{$values['event_date']}' ", $conn);
            if ($tide_rs and !empty($values['event_start']))
            {
                $best_tide = get_best_tide($values['event_start'], $tide_rs['hw1_time'], $tide_rs['hw2_time']);
                $values['tide_time']   = $tide_rs["hw{$best_tide}_time"];
                $values['tide_height'] = $tide_rs["hw{$best_tide}_height"];
            }
        }
    }

    // set defaults
    if ($mode == "add" and !empty($values['series_code']))
    {
        $values['series_code'] = $values['series_code']."-".date("y", strtotime($values['event_date']));
    }
    $values['updby']      = $_SESSION['UserID'];
    $values['upddate']    = NOW();
    // error_log("start addbefore 103: ".print_r($values,true), 3, "../../logs/dbglogs/rmdebug.log");

}
else
{
    $message = "<span style=\"white-space: normal\">WARNINGS:<br>$msg </span>";
}


