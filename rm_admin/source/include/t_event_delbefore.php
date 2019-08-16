<?php

// must not delete event if results have been recorded
if (f_check_exists("t_result", " eventid={$deleted_values['id']} ", $conn))
{
    $message = "Results have been recorded for this event - cannot be deleted";
    $delete = false;
    // error_log("results recorded | ", 3, "../../logs/dbglogs/rmdebug.log");
}

// must not delete if status anything other than scheduled
if ($deleted_values['event_status'] != "scheduled")
{
    $message = "Event is or has been in progress - cannot be deleted [status must be 'scheduled']";
    $delete = false;
    // error_log("event status | ", 3, "../../logs/dbglogs/rmdebug.log");
}


