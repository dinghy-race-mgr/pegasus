<?php

// add ood duty if not provided
if ($mode == "add" and $values['event_type'] == "racing")
{
    $rs = db_query("SELECT * FROM t_eventduty WHERE eventid = '{$values['id']}' AND dutycode='ood_p'", $conn);
    $data = db_fetch_array($rs);
    if (!$data)
    {
        if ($values['event_ood'])
        {
            $name = $values['event_ood'];
        }
        else
        {
            $name = "- not specified -";
        }

        $insert = db_query("INSERT INTO t_eventduty (`eventid`,`dutycode`,`person`, `updby`) VALUES ('{$values['id']}','ood_p', '$name', '{$_SESSION['UserID']}')", $conn);
    }
}


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

