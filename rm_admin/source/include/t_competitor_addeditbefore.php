<?php

$values['updby']   = $_SESSION['UserID'];
$values['upddate'] = NOW();

$msg = "";
// individual field checks
// FIXME add field checks
// individual field checks complete

if (empty($values['sailnum'])) { $values['sailnum'] = $values['boatnum']; }

empty($msg) ? $commit = true : $commit = false;

if (!$commit)
{
    $message = "<span style=\"white-space: normal\">WARNINGS: $msg </span>";
}

