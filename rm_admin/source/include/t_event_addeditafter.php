<?php
$msg = "";
isset($oldvalues) ? $mode = "edit" : $mode = "add";
$values['event_date'] = date("Y-m-d", strtotime($values['event_date']));

// individual field checks
if ($values['event_type'] == "racing")
{
    // if series code specified but series ot mentioned in name - get user to check
    $array = explode(" ", strtolower($values['event_name']));
    if (!in_array('series', $array) AND !empty($values['series_code'])) {
        $msg .= "- it is not clear whether this event is part of a series of race - if not please remove the series 
                code - if it is please check you have the correct series code<br>";
    }
}
$message = "<span style=\"white-space: normal\">WARNINGS:<br>$msg </span>";





