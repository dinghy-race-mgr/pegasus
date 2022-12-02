<?php

function p_getstarts_class($classes, $minpn, $maxpn, $py_field, $race_length, $start_interval, $boat_types)
{
    $i = 0;
    $starts = array();
    foreach ($classes as $key => $row)
    {
        $pn = $row[$py_field];

        if ($pn <= $maxpn AND in_array($row['category'], $boat_types))
        {
            if (empty($minpn))        // no limit on fastest class
            {
                $starts[$i]['class']   = $row['classname'];
                $starts[$i]['popular'] = $row['popular'];
                $starts[$i]['pn']      = $pn;
                $time = $race_length - ($race_length * ($pn/$maxpn));
                $starts[$i]['start']   = u_timeresolution($start_interval, $time);  // apply required time resolution
                $i++;
            }
            else
            {
                if ($pn >= $minpn)    // check limit on fastest class
                {
                    $starts[$i]['class']   = $row['classname'];
                    $starts[$i]['popular'] = $row['popular'];
                    $starts[$i]['pn']      = $pn;
                    $time = $race_length - ($race_length * ($pn/$maxpn));
                    $starts[$i]['start']   = u_timeresolution($start_interval, $time);  // apply required time resolution
                    $i++;
                }
            }

        }
    }

    return $starts;
}


function p_getstarts_competitors($competitors, $maxpn, $race_length, $start_interval)
{
    // receives competitors array in sorted by pn desc, classname asc, sailnum asc
    // assumes race config sets max and min py and boat types that can be included

    $i = 0;
    $starts = array();
    foreach ($competitors as $key => $row)
    {
        $time = $race_length - ($race_length * ($row['pn']/$maxpn));

        $starts[$i] = array(
            "class"   => $row['class'],
            "sailnum" => $row['sailnum'],
            "id"      => $row['id'],                                  // should this be competitorid
            "code"    => $row['code'],
            "status"  => $row['status'],
            "start"   => u_timeresolution($start_interval, $time),    // apply required time resolution
        );

        $i++;
    }

    return $starts;
}

