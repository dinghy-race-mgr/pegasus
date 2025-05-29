<?php

// must not delete event if it has linked records

$record_types = "";

if (f_check_exists("e_document", " eid={$deleted_values['id']} ", $conn))
{
    $delete = false;
    $record_types.= "documents, ";
}

if (f_check_exists("e_notice", " eid={$deleted_values['id']} ", $conn))
{
    $delete = false;
    $record_types.= "notices, ";
}

if (f_check_exists("e_entry", " eid={$deleted_values['id']} ", $conn))
{
    $delete = false;
    $record_types.= "entries, ";
}

if (f_check_exists("e_contact", " eid={$deleted_values['id']} ", $conn))
{
    $delete = false;
    $record_types.= "contacts, ";
}

if (f_check_exists("e_content", " eid={$deleted_values['id']} ", $conn))
{
    $delete = false;
    $record_types.= "content pages, ";
}

if (f_check_exists("e_results", " eid={$deleted_values['id']} ", $conn))
{
    $delete = false;
    $record_types.= "results, ";
}

$record_types = rtrim(", ", $record_types);

$message = "event cannot be deleted - it still has $record_types records linked to it.<br>
[Please contact your system administrator if you need held to remove an event]";





