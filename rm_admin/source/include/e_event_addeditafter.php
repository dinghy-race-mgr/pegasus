<?php

// initialise
$msg = "";
$display_msg = false;

if ($mode == "add")
{
    // create directory for event files
    if (!mkdir("../../data/events/$year/{$values['nickname']}", 0777, true))
    {
        $msg.= "FAILED to create open meeting file directory<br>";
    }

    if ($_SESSION['event_copy'])  // id of copied event
    {
        // copy contacts to this event

        // copy content records to this event

        // create msg
        $msg.= "This record was created from a previous event - please check the event record, the associated contacts and the web page content carefully<br>";

    }
}

error_log("|$display_msg|$msg|", 3, $_SESSION['dbglog']);

if (!empty($msg))
{
    echo "<script type='text/javascript'>alert(\"JUST CHECKING... $msg\");</script>";
}


