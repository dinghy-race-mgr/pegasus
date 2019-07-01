<?php

$msg = "";

// start individual field checks
// FIXME add field checks

// end individual field checks

if (empty($msg))
{
    $values['updby']   = $_SESSION['UserID'];
    $values['upddate'] = NOW();
    return true;
}
else
{
    $message = "<span style=\"white-space: normal\">WARNINGS: $msg </span>";
    return false;
}

