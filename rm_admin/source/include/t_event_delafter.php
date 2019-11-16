<?php

// deletes associated duty allocation records
$rs = db_query("SELECT id FROM t_eventduty WHERE eventid = '{$deleted_values['id']}'", $conn);
$i = 0;
while ($data = db_fetch_array($rs))
{
    $i++;
    $del = db_query("DELETE FROM t_eventduty WHERE id = {$data['id']}", $conn);
}
$message = "deleted $i duty allocations associated wih the event";



