<?php
$competitor = strtoupper($deleted_values['helm']." / ".$deleted_values['crew']);

$rs_update = db_query("UPDATE t_competitor SET active = 0 WHERE id = {$deleted_values['id']}");
$message = "$competitor has been marked as not active and will now not be available for use in raceManager";

