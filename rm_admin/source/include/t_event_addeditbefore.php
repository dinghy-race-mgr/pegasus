<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 26/06/2019
 * Time: 22:26
 */
global $conn;
include ("./rma_common_even.php");
include ("../../common/lib/fieldchk_lib.php");

$msg = "";
isset($oldvalues) ? $edit = true : $edit = false;

// individual field checks
if ($values['event_type'] == "race")
{
    // check race format
    if (!f_check_exists("t_cfgrace", " id={$values['event_format']} ", $conn))
    {
        $msg.= "- race format is not recognised ";
    }

    // check race order or start time provided
    if (empty($values['event_order']))
    {
        $msg.= "- race event must have event order on day defined ";
    }

    // check start_interval is set if start_scheme has been set
    if (f_values_dependset($values['start_scheme'], $values['start_interval']))
    {
        $msg.= "- default start scheme has changed and the start interval must be set ";
    }

    // check series code exists
    if (!f_check_exists("t_series", " seriescode='{$values['seriescode']}' ", $conn))
    {
        $msg.= "- series code is not recognised ";
    }

    // check entry type is set
    if (empty($values['event_entry']))
    {
        $msg.= "- race entry method must be set ";
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

// field checks complete

if (empty($msg))
{
    // check if we need to reset tide (tide data empty or data changed)
    $tide_reset = false;
    if (!empty($values['event_start']) and (empty($values['tide_time']) or empty($values['tide_height'])))
    {
        $tide_reset = true;
    }

    if ($edit)   // its an edit
    {
        if (!empty($values['event_start']) and ($values['event_date'] != $oldvalues))
        {
            $tide_reset = true;
        }
    }

    if ($tide_reset)
    {
        if (f_check_exists("t_tide", " date = '{$values['event-date']}' ", $conn))
        {
            $tide_rs = f_get_row("t_tide", "hw1_time,hw1_height,hw2_time,hw2_height", " date = '{$values['event-date']}' ", $conn);
            if ($tide_rs and !empty($values['event_start']))
            {
                $best_tide = get_best_tide($values['event_start'], $tide_rs['hw1_time'], $tide_rs['hw2_time']);
                $values['tide_time']   = $tide_rs["hw{$best_tide}_time"];
                $values['tide_height'] = $tide_rs["hw{$best_tide}_height"];
            }
        }
    }

    // if even date has changed reset tide

    // set defaults
    if (!$edit and !empty($values['series_code']))
    {
        $values['series_code'] = $values['series_code']."-".date("y", strtotime($values['event_date']));
    }
    $values['updby']      = $_SESSION['UserID'];
    $values['upddate']    = NOW();

    return true;
}
else
{
    $message = "<span style=\"white-space: normal\">WARNINGS: $msg </span>";
    return false;
}


