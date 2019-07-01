<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 26/06/2019
 * Time: 17:46
 */
global $conn;
include ("../../common/lib/fieldchk_lib.php");

$delete = true;

// must not delete event if results have been recorded
if (!check_exists("t_results", " eventid={$values['id']} ", $conn))
{
    $message = "Results have been recorded for this event - cannot be deleted";
    $delete = false;
}

// must not delete if status anything other than scheduled
if ($deleted_values['event_status'] != "scheduled")
{
    $message = "Event is in progress or has been in progress - cannot be deleted [status must be 'scheduled']";
    $delete = false;
}


if (!$delete)
{
    return false;
}
return true;