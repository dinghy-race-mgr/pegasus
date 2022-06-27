<?php
// we do not want to remove the record  - just hide it
$competitor = "{$deleted_values['classname']} {$deleted_values['boatnum']} - {$deleted_values['helm']}";

$rs_update = db_query("UPDATE t_competitor SET active = 0 WHERE id = {$deleted_values['id']}");
$message = strtoupper($competitor)." has been marked as not active and will now not be available for use in raceManager";

$delete = false;