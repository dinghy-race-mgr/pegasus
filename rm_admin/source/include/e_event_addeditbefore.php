<?php
//error_log("<pre>".print_r($values,true)."</pre>\n", 3, $_SESSION['dbglog']);

// initialise
$msg = "";

// make nickname lowercase
$values['nickname'] = strtolower($values['nickname']);

// check event end date is after or = to event start date
$ev_start = strtotime($values['date-start']);
$ev_end = strtotime($values['date-end']);
if ($ev_end < $ev_start)
{
    $msg.= "- event end date is before start date<br>";
}

// check entry end date is before or = to event start date AND after entry start date
$en_start = strtotime($values['entry-start']);
$en_end = strtotime($values['entry-end']);
if ($en_end < $en_start)
{
    $msg.= "- entry end date/time is before entry start date<br>";;
}

if ($en_end > $ev_start)
{
    $msg.= "- entry end date is after the start of the event<br>";
}

// check we only have one form defined
if (!empty($values['entry-form']) and !empty($values['entry-form-link']))
{
    $msg.= "- an external AND internal entry form have been defined can only be one<br>";
}

// set entry required to boolean
$values['entry-reqd'] == strtolower("yes") ? $values['entry-reqd'] = 1 : $values['entry-reqd'] = 0;

// field checks complete
if (empty($msg))
{
    $commit = true;

    $values['updby']      = $_SESSION['UserID'];
    $values['upddate']    = NOW();
    $message = "";
}
else
{
    $commit = false;
    $message = "<span style=\"white-space: normal\">EVENT ISSUES:<br>$msg </span>";
}





