<?php
$msg = "";
// check if series in event name but no series specified
$array = explode(" ", strtolower($values['event_name']));
if ($values['event_type'] == "racing")
{
    if (in_array('series', $array))
    {
        if (empty($values['series_code']))
        {
            $msg.= "JUST CHECKING...\\nevent name implies a series - but no series is specified.\\n\\nIf this is supposed to be a series event please go back and edit the event.";
        }
    }
    else
    {
        if (!empty($values['series_code']))
        {
            $msg.= "JUST CHECKING...\\na series is specified but a series is not indicated in the event name (e.g Spring Series).\\n\\nIf this is not supposed to be a series event please  go back and edit the event.";
        }
    }
}


if (!empty($msg))
{
    echo "<script type='text/javascript'>alert(\"$msg\");</script>";
}

