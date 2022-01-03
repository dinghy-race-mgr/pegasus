<?php

// get competitor details
$sql = "SELECT b.classname, sailnum, helm, a.crew, club, nat_py, local_py FROM t_competitor as a 
                    JOIN t_class as b ON a.classid=b.id WHERE a.id = {$values['competitorid']}";
$rs_boat = db_query($sql, $conn);
$boat = db_fetch_array($rs_boat);

if (empty($boat))
{
    $commit = false;
    $message = "<span style=\"white-space: normal\">OOPS: competitor record for this result is invalid/not found<br><br>
        Suggest cancel this edit - then copy this result record and add correct competitor details and then delete the original record </span>";
    $_SESSION['results_update'] = false;
}
else
{
    // set class and helm from competitor record
    $values['class'] = $boat['classname'];
    $values['helm']  = $boat['helm'];

    if ($mode == "add")          // copy fields across if copying a record
    {
        $values['eventid']   = $_SESSION['copy']['eventid'];
        $values['fleet']     = $_SESSION['copy']['fleet'];
        $values['race_type'] = $_SESSION['copy']['race_type'] ;
    }
    else
    {
        $values['eventid']   = $oldvalues['eventid'];
        $values['fleet']     = $oldvalues['fleet'];
        $values['race_type'] = $oldvalues['race_type'] ;
    }

    // get fleet cfg details
    $sql = "SELECT b.py_type FROM t_event as a JOIN t_cfgfleet as b ON a.event_format=b.eventcfgid 
                        WHERE a.id={$values['eventid']} and b.fleet_num={$values['fleet']}";
    $rs_fleet = db_query($sql, $conn);
    $fleet = db_fetch_array($rs_fleet);

    // set pn
    $fleet['py_type'] == "local" ? $boat['pn'] = $boat['local_py'] : $boat['pn'] = $boat['nat_py'];

    if ($mode == "add")
    {
        $values['sailnum'] = $boat['sailnum'];
        $values['crew'] = $boat['crew'];
        $values['club'] = $boat['club'];
    }
    else
    {
        // only overwrite from competitor record if not set
        if (empty($values['sailnum'])) {$values['sailnum'] = $boat['sailnum'];}
        if (empty($values['crew'])) {$values['crew'] = $boat['crew'];}
        if (empty($values['club'])) {$values['club'] = $boat['club'];}
        if (empty($values['pn'])) {$values['pn'] = $boat['pn'];}
    }

    // set elapsed time to seconds
    $values['etime'] = strtotime($values['etval']) - strtotime("00:00:00");
    unset($values['etval']);

    // check notes are html compatible
    $values['note'] = htmlspecialchars($values['note']);

    // check penalty codes
    if (($values['code'] != "ZFP" or $values['code'] != "SCP" or $values['code'] != "DPI") and $values['penalty'] != "0.0")
    {
        $values['penalty'] = "0.0";
    }


    // add audit field
    $values['updby']   = $_SESSION['UserID'];
    $values['upddate'] = NOW();

    $_SESSION['results_update'] = true;
    $commit = true;
}


