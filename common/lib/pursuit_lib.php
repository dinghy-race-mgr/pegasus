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

    if (empty($start_interval)) { $start_interval = 60; }

    $i = 0;
    $starts = array();
    foreach ($competitors as $key => $row)
    {
        $time = $race_length - ($race_length * ($row['pn']/$maxpn));

        $starts[$i] = array(
            "class"   => $row['class'],
            "sailnum" => $row['sailnum'],
            "lap"     => $row['lap'],
            "declaration" => $row['declaration'],
            "id"      => $row['id'],
            "code"    => $row['code'],
            "status"  => $row['status'],
            "start"   => u_timeresolution($start_interval, $time),    // apply required time resolution
        );

        $i++;
    }

    return $starts;
}

function p_class_match($pn, $pn_type)
{
    global $db_o;

    $pn_type == "national" ? $pn_field = "`nat_py`" : $pn_field = "`local_py`" ;

    $row = $db_o->db_get_row("SELECT `classname` FROM t_class WHERE ".$pn_field." = ".$pn." and `active` = 1 ORDER BY $pn_field ASC LIMIT 1");
    if ($row)
    {
        $classname = $row['classname'];
    }
    else
    {
        $classname = false;
    }

    return $classname;
}

function check_pursuit_cfg($eventid)
{
    $cfg_filename = $_SESSION['basepath']."/tmp/pursuitcfg_$eventid.json";
    if (file_exists($cfg_filename))
    {
        $json = file_get_contents($cfg_filename,0,null,null);
        if ($json !== false and strlen($json) > 0)
        {
            $cfg = json_decode($json,true);
            if (isset($cfg['length']) and !empty($cfg['length'])) { $_SESSION['pursuitcfg']['length'] = $cfg['length']; }
            if (isset($cfg['interval']) and !empty($cfg['interval'])) { $_SESSION['pursuitcfg']['interval'] = $cfg['interval']; }
            if (isset($cfg['pntype']) and !empty($cfg['pntype'])) { $_SESSION['pursuitcfg']['pntype'] = $cfg['pntype']; }
            if (isset($cfg['slowpn']) and !empty($cfg['slowpn'])) { $_SESSION['pursuitcfg']['slowpn'] = $cfg['slowpn']; }
            if (isset($cfg['slowclass']) and !empty($cfg['slowclass'])) { $_SESSION['pursuitcfg']['slowclass'] = $cfg['slowclass']; }
            if (isset($cfg['fastpn']) and !empty($cfg['fastpn'])) { $_SESSION['pursuitcfg']['fastpn'] = $cfg['fastpn']; }
            if (isset($cfg['fastclass']) and !empty($cfg['fastclass'])) { $_SESSION['pursuitcfg']['fastclass'] = $cfg['fastclass']; }
        }
    }
    else
    {
        $cfg = false;
    }

    return $cfg;
}
