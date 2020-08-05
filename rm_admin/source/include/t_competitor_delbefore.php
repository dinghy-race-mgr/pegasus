<?php
$competitor = <<<EOT
{$deleted_values['classname']} {$deleted_values['boatnum']} - {$deleted_values['helm']}
EOT;

$rs_update = db_query("UPDATE t_competitor SET active = 0 WHERE id = {$deleted_values['id']}");
$message = strtoupper($competitor)." has been marked as not active and will now not be available for use in raceManager";

$delete = false;  // we don' want the record removed - just hidden