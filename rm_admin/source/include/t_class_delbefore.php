<?php
$class = strtoupper($deleted_values['classname']);

// don't allow deletion if existing active competitors still reference this class
if (f_check_exists("t_competitor", " classid='{$deleted_values['id']}' and active != 0 ", $conn ))
{
	$message = "the $class class has registered competitors - please remove them before deleting this class";
	$delete = false;
}
else
{
    $rs_update = db_query("UPDATE t_class SET active = 0 WHERE id = {$deleted_values['id']}", $conn);
	$message = "the $class class has been marked as not active and will now not be available for use in raceManager";
	$delete = true;
}