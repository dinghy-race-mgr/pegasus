<?php
//error_log("<pre>".print_r($values,true)."</pre>\n", 3, $_SESSION['dbglog']);


// set mode
//isset($oldvalues) ? $mode = "edit" : $mode = "add";


// modify series fields to include year modifier
if (!empty($values['series_root']))
{
    $values['series_code'] = $values['series_root']."-".date("y", strtotime($values['event_date']));
}
else
{
    $values['series_code'] = "";
}
unset($values['series_root']);

if (!empty($values['series_root_extra']))
{
    $series_arr = explode(",", $values['series_root_extra']);
    $field_txt = "";
    foreach ($series_arr as $v)
    {
        $field_txt.= $v."-".date("y", strtotime($values['event_date'])).",";
    }
    $values['series_code_extra'] = rtrim($field_txt, ",");
}
unset($values['series_root_extra']);


// individual field validation checks
$msg = "";

if ($values['event_type'] == "racing")
{
    // check race format
    if (!f_check_exists("t_cfgrace", " id='{$values['event_format']}' ", $conn))
    {
        $msg .= "- race format is not recognised<br>";
    }

    // check that at least one of start time or order on day is set
    if (!f_values_oneset($values['event_start'], $values['event_order']))
    {
        $msg .= "- either the event start time or the order (1st, 2nd ..) must be defined<br>";
    }

    // check start_interval is set if start_scheme has been set
    if (!f_values_dependset($values['start_scheme'], $values['start_interval']))
    {
        $msg .= "- default start scheme has changed and the start interval must be set<br>";
    }

    // check provided series code exists
    if (!empty($values['series_code']) and ($values['series_code']))
    {
        $series_root = get_series_root($values['series_code']);
        if (!f_check_exists("t_series", " seriescode ='$series_root' ", $conn))
        {
            $msg .= "- series [ $series_root ] is not recognised<br>";
        }
    }

    // check all secondary series codes exist
    if (!empty($values['series_code_extra']) and ($values['series_code_extra']))
    {
        $series_arr = explode(",", $values['series_code_extra']);
        foreach ($series_arr as $v)
        {
            $series_root = get_series_root(trim($v));
            if (!f_check_exists("t_series", " seriescode='$series_root' ", $conn))
            {
                $msg .= "- secondary series [ $series_root ] is not recognised<br>";
            }
        }
    }

    // check entry type is set
    if (empty($values['event_entry'])){ $msg.= "- race entry method must be set<br>"; }

}
elseif ($values['event_type'] == "training" or $values['event_type'] == "social" or $values['event_type'] == "cruise")
{
    if (empty($values['event_start'])) { $msg .= "- the start time must be specified<br>"; }
}

// check tide information
$tide_reset = false;

if ($mode == "add")
{
    if ($_SESSION['tidal_mode'] == "data")
    {
        $tide_reset = true;
    }
}
elseif ($mode == "edit")
{
    if ($_SESSION['tidal_mode'] == "data")
    {
        $event_date = date("Y-m-d", strtotime($values['event_date']));

        //error_log("<pre>{$event_date}|{$oldvalues['event_date']}|{$values['event_start']}|{$oldvalues['event_start']}
        //          |{$values['tide_time']}|{$oldvalues['tide_time']}|</pre>\n", 3, $_SESSION['dbglog']);

        if ($event_date != $oldvalues['event_date'])
        {
            $tide_reset = true;
        }
        elseif ($values['event_start'] != $oldvalues['event_start'])
        {
            $tide_reset = true;
        }
        elseif ($values['tide_time'] != $oldvalues['tide_time'])
        {
            // check tide provided is valid for even date
            $rs = db_query("SELECT * FROM t_tide WHERE date = '{$values['event_date']}'", $conn);
            $tide_rs = db_fetch_array($rs);

            if ($tide_rs)
            {
                //error_log("<pre>{$values['tide_time']}|{$tide_rs['hw1_time']}|{$values['tide_time']}|{$tide_rs['hw2_time']}|</pre>\n", 3, $_SESSION['dbglog']);
                if ($values['tide_time'] == $tide_rs['hw1_time'])      // its valid
                {
                    $values['tide_height'] = $tide_rs['hw1_height'];
                }
                elseif ($values['tide_time'] == $tide_rs['hw2_time'])  // its valid
                {
                    $values['tide_height'] = $tide_rs['hw2_height'];
                }
                else                                                   // not valid
                {
                    $msg.= "- manually entered tide details are not correct for the event date - please check tables<br>";
                }
            }
            else
            {
                $values['tide_time'] = "missing data";
                $values['tide_height'] = "";
            }
        }
    }
}

if ($tide_reset)  // get data from t_tide table
{
    $rs = db_query("SELECT * FROM t_tide WHERE date = '{$values['event_date']}'", $conn);
    $tide_rs = db_fetch_array($rs);

    if ($tide_rs)
    {
        $best_tide = get_best_tide($values['event_start'], $tide_rs['hw1_time'], $tide_rs['hw2_time']);
        $values['tide_time']   = $tide_rs["hw{$best_tide}_time"];
        $values['tide_height'] = $tide_rs["hw{$best_tide}_height"];
    }
    else
    {
        $values['tide_time'] = "missing data";
        $values['tide_height'] = "";
    }
}

// field checks complete
if (empty($msg))
{
    $commit = true;
    if ($values['event_type'] != "racing")
    {
        $values['series_code'] = "";
        $values['series_code_extra'] = "";
        $values['start_scheme'] = "";
        $values['start_interval'] = "";
    }

    $values['updby']      = $_SESSION['UserID'];
    $values['upddate']    = NOW();
    $message = "";
}
else
{
    $commit = false;
    $message = "<span style=\"white-space: normal\">EVENT PROBLEMS:<br>$msg </span>";
}





