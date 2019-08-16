<?php

$msg = "";
// individual field checks
// FIXME add field checks
// individual field checks complete

empty($msg) ? $commit = true : $commit = false;

if ($commit)
{
    $values['updby']   = $_SESSION['UserID'];
    $values['upddate'] = NOW();
}
else
{
    $message = "<span style=\"white-space: normal\">WARNINGS: $msg </span>";
}

