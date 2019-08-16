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

function get_series_root($series)
{
    $pos = strripos($series, "-");
    if ($pos !== false)
    {
        $series = substr($series, 0, $pos+1);
    }

    return $series;
}