<?php
$msg = "";

// check if race configuration being copied has associated fleet configurations
$rs = db_query("SELECT * FROM t_cfgfleet WHERE eventcfgid = {$_SESSION['copy_record']}", $conn);
$count = 0;

// copy fleet configurations to new race configuration
while( $data = db_fetch_array($rs))
{
    $count++;
    error_log("fleet $count:\n",3,$_SESSION['dbglog']);

    unset($data['id']);
    $insert = "INSERT INTO t_cfgfleet ";
    $fields = array();
    $values = array();
    foreach( $data as $field => $value )
    {
        if ($field == "eventcfgid")
        {
            $value = $keys['id'];
        }
        elseif ($field == "min_py" AND empty($value))
        {
            $value = 0;
        }
        elseif ($field == "max_py" AND empty($value))
        {
            $value = 2000;
        }

        $fields[] = "`".$field."`";
        $values[] = "'".addslashes($value)."'";
    }
    $field_str = ' (' . implode(', ', $fields) . ')';
    $value_str = '('. implode(', ', $values) .')';

    $insert .= $field_str .' VALUES '. $value_str;
    error_log("$insert\n",3,$_SESSION['dbglog']);
    $insert_rs  = db_query( $insert );
}

if ($count > 0)
{
    echo "<script type='text/javascript'>alert(\"$count fleet configurations copied from {$_SESSION['copy_name']}\");</script>";
}
else
{
    echo "<script type='text/javascript'>alert(\"no fleet configurations associated with copied record\");</script>";
}

