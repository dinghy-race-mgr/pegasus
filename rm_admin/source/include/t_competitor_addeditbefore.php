<?php

$msg = "";
// individual field checks
// FIXME add field checks
// individual field checks complete

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

