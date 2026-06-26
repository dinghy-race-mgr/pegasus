<?php
//error_log("<pre>VALUES".print_r($values,true)."</pre>\n", 3, $_SESSION['dbglog']);
//error_log("<pre>OLDVALUES".print_r($oldvalues,true)."</pre>\n", 3, $_SESSION['dbglog']);

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
else
{
    $values['series_code_extra'] = "";
}
unset($values['series_root_extra']);

// reformat start time to hh:mm
if (!empty($values['event_start'])) { $values['event_start'] = date("H:i", strtotime($values['event_start'])); }

// update audit info
$values['updby']      = $_SESSION['UserID'];
$values['upddate']    = NOW();
$message = "";

//error_log("<pre>- {$values['series_code']}</pre>\n", 3, $_SESSION['dbglog']);
//error_log("<pre>-- {$values['series_code_extra']}</pre>\n", 3, $_SESSION['dbglog']);






