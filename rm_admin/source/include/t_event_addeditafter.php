<?php
$msg = "";

// check if series in event name but no series specified
$array = explode(" ", strtolower($values['event_name']));

$display_msg = false;
if ($values['event_type'] == "racing")
{
    if (in_array('series', $array))
    {
        if (empty($values['series_code']))
        {
            $msg.= "event name implies a series - but no series is specified - 
            if this is supposed to be a series event please go back and edit the event.";
            $display_msg = true;
        }
    }
    else
    {
        if (!empty($values['series_code']))
        {
            $msg.= "a series is specified but a series is not indicated in the event name (e.g Spring Series) - 
            if this is not supposed to be a series event please go back and edit the event.";
            $display_msg = true;
        }

    }
}

error_log("|$display_msg|$msg|", 3, $_SESSION['dbglog']);

if ($display_msg)
{
    echo "<script type='text/javascript'>alert(\"JUST CHECKING... $msg\");</script>";
}

