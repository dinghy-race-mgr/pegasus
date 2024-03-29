<?php

$msg = "";


// field checks

if (is_numeric($values['nat_py']))
{
    if (intval($values['nat_py']) < 1 OR intval($values['nat_py']) > 2000)
    {
        $msg .= "- the national PY must be a number between 1 and 2000<br>";
    }
} else {
    $msg .= "- the national PY must be a number<br>";
}

if (!empty($values['local_py']))
{
    if (is_numeric($values['local_py']))
    {
        if (intval($values['local_py']) < 1 OR intval($values['local_py']) > 2000)
        {
            $msg.= "- the local PY must be a number between 1 and 2000";
        }
    }
    else
    {
        $msg.= "- the local PY must be a number";
    }
}

empty($msg) ? $commit = true : $commit = false;

if ($commit)
{
    // set acronym if not set
    if ($values['acronym']=="") { $values['acronym'] = substr($values['classname'],0,4) ;}

    // set audit fields
    $values['updby']   = $_SESSION['UserID'];
    $values['upddate'] = NOW();

    // check local PN is set - if not set to national value
    if ($values['local_py']=="") {$values['local_py'] = $values['nat_py'];}
}
else
{
    $message = "<span style=\"white-space: normal\">WARNINGS: $msg </span>";
}



