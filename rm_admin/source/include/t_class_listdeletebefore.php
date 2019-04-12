<?php

// don't allow deletion if competitors still reference this class
$strSQLExists = "SELECT * from t_competitor WHERE classid='{$deleted_values['id']}'";
$rsExists = db_query($strSQLExists,$conn);
$data=db_fetch_array($rsExists);
if ($data)
{
	$message = "the {$deleted_values['classname']} class has registered competitors with this type of boat - please remove them before deleting this class";
	return false;
}
else
{
    $rs_update = db_query("UPDATE t_class SET active = 0 WHERE id = {$deleted_values['id']}")
	$message = "the {$deleted_values['classname']} class has been set as not active and will not be available for use in raceManager";
	return false;
}

?>