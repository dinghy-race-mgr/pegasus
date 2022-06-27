<?php
$code_type = strtoupper($deleted_values['groupname']);

// don't allow deletion if existing code type has associated values
if (f_check_exists("t_code_system", " groupname ='{$deleted_values['groupname']}' ", $conn ))
{
    $message = "the code, $code_type has existing code values - please remove them before deleting this code";
    $delete = false;
}
