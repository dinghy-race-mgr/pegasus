<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 26/06/2019
 * Time: 22:09
 */
global $conn;
include ("../../common/lib/fieldchk_lib.php");

$msg = "";

// individual field checks
// absolute time limit check
if (f_values_lessthan($values['timelimit_abs'], 0)) { $msg.= "- absolute time limit is negative "; }

// relative  time limit check
if (f_values_lessthan($values['timelimit_rel'], 0)) { $msg.= "- relative time limit is negative "; }

// signal check
if (f_values_equal($values['warn_signal'], $values['prep_signal']))
{ $msg.= "- warning and preparatory signals are the same "; }

// py check
if (!f_values_inrange($values['max_py'], 1, 10000)) { $msg.= "- max PY must be between 1 and 10000 "; }
if (!f_values_inrange($values['min_py'], 1, 10000)) { $msg.= "- min PY must be between 1 and 10000 "; }
if (!f_values_insequence($values['min_py'], $values['max_py'])) { $msg.= "- max PY must be greater than min PY "; }


// helm age check
if (!f_values_inrange($values['min_helmage'], 1, 100)) { $msg.= "- min helm age must be between 1 and 100 "; }
if (!f_values_inrange($values['max_helmage'], 1, 100)) { $msg.= "- min helm age must be between 1 and 100 "; }
if (!f_values_insequence($values['min_helmage'], $values['max_helmage']))
{ $msg.= "- max helm age must be greater than min helm age "; }


// default laps
if (f_values_lessthan($values['defaultlaps'], 0)) { $msg.= "- default laps must be greater than 0"; }

// class conflicts
if (!empty($values['classinc']) AND !empty($values['classexc']))
{
    $array1 = explode(",", $values['classinc']);
    $array2 = explode(",", $values['classexc']);
    $intersect = array_intersect($array1, $array2);
    if ($intersect)
    {
        $msg.= "- some classes are both specifically included and excluded";
    }
}

// end of individual field checks

if (empty($msg))
{
    $values['updby']   = $_SESSION['UserID'];
    $values['upddate'] = NOW();
    return true;
}
else
{
    $message = "<span style=\"white-space: normal\">WARNINGS: $msg </span>";
    return false;
}
