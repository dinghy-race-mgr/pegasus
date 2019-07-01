<?php

function get_best_tide($start, $tide1, $tide2)
{
    $diff1 = abs(strtotime($start) - strtotime($tide1));
    $diff2 = abs(strtotime($start) - strtotime($tide2));
    if ($diff1 < $diff2)
    {
        return 1;
    }
    else
    {
        return 2;
    }
}