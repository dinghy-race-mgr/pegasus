<?php
$msg = "";

// field checks - none


// if no errors add/edit record

empty($msg) ? $commit = true : $commit = false;

if ($commit)
{
    // tidy field formats
    if (!empty($values['club'])) { $values['club'] = u_getclubname($values['club']); }

    if (!empty($values['helm']))  { $values['helm'] = ucwords($values['helm']); }

    if (!empty($values['crew']))  { $values['crew'] = ucwords($values['crew']); }

    if (!empty($values['helm_dob'])) { $values['helm_dob'] = date("Y-m-d", strtotime($values['helm_dob'])); }

    if (!empty($values['crew_dob'])) { $values['crew_dob'] = date("Y-m-d", strtotime($values['crew_dob'])); }

    // set sailnum if not already set
    if (empty($values['sailnum'])) { $values['sailnum'] = $values['boatnum']; }

    // make record active
    //$values['active'] = 1;

    // audit fields
    $values['updby']   = $_SESSION['UserID'];
    $values['upddate'] = NOW();
}
else
{
    $message = "<span style=\"white-space: normal\">WARNINGS: $msg </span>";
}
