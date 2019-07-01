<?php
/*
 * Field validation library for use in PHPrunner applications
 */

function f_check_delete($table, $where, $conn)
{
    $rs = db_query("SELECT * from $table WHERE $where", $conn);
    $data = db_fetch_array($rs);
    if ($data)
    {
        return false;
    }
    return true;
}

function f_check_exists($table, $where, $conn)
{
    $rs = db_query("SELECT * from $table WHERE $where", $conn);
    $data = db_fetch_array($rs);
    if ($data)
    {
        return true;
    }
    return false;
}

function f_values_oneset($value1, $value2)
{
    if (empty($value1) AND empty($value2))
    {
        return false;
    }
    return true;
}

function f_values_bothset($value1, $value2)
{
    if (!empty($value1) AND !empty($value2))
    {
        return true;
    }
    return false;
}

function f_values_dependset($value1, $value2)
// second value must be set if first is set
{
    if (!empty($value1))
    {
        if (empty($value2))
        {
            return false;
        }
    }
    return true;
}

function f_values_equal ($value1, $value2)    // returns true if values are equal or either are not set
{
    if (empty($value1) OR empty($value2))
    {
        return false;
    }
    else
    {
        if ($value1 == $value2)
        {
            return true;
        }
    }
    return false;
}

function f_values_lessthan ($value, $limit)   // returns true if value is less than limit or not set
{
    if (!empty($value) AND $value < $limit)
    {
        return true;
    }
    return false;
}

function f_values_inrange($value, $low, $high)    // returns true if value is in specified range or not set
{
    if (empty($value) OR ($value >= $low AND $value <= $high))
    {
        return true;
    }
    return false;
}

function f_values_insequence($value1, $value2)    // returns true if values are in numerical sequence or either are not set
{
    if (empty($value1) OR empty($value2))
    {
        return true;
    }
    else
    {
        if ($value2 >= $value1)
        {
            return true;
        }
    }
    return false;
}

function f_get_row($table, $fields, $where, $conn)
{
    $rs = db_query("SELECT $fields from $table WHERE $where", $conn);
    $data = db_fetch_array($rs);
    if ($data)
    {
        return true;
    }
    return false;
}


?>