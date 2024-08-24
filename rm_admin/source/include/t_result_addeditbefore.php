<?php

// get competitor details
$sql = "SELECT b.classname, sailnum, helm, a.crew, club, nat_py, local_py FROM t_competitor as a 
                    JOIN t_class as b ON a.classid=b.id WHERE a.id = {$values['competitorid']}";
$rs_boat = db_query($sql, $conn);
$boat = db_fetch_array($rs_boat);

if (empty($boat))
{
    $commit = false;
    $message = "<span style=\"white-space: normal\">OOPs: competitor for this result is invalid/not found<br>
        Probably because record deleted as part of administrative tidy-up - please check with system admin </span>";
    $_SESSION['results_update'] = false;
}
else
{
  $mode == "add" ? $values['eventid']   = $_SESSION['copy']['eventid'] : $values['eventid'] = $oldvalues['eventid'];;

    // get fleet cfg details (to get PY value used)
    $sql = "SELECT b.py_type FROM t_event as a JOIN t_cfgfleet as b ON a.event_format=b.eventcfgid WHERE a.id={$values['eventid']} and b.fleet_num={$values['fleet']}";
    $rs_fleet = db_query($sql, $conn);
    $fleet = db_fetch_array($rs_fleet);
    // set pn  [fixme - what about personal py]
    $fleet['py_type'] == "local" ? $boat['pn'] = $boat['local_py'] : $boat['pn'] = $boat['nat_py'];

    if ($mode == "add")          // copy fields across if copying a record
    {
        $values['race_type'] = $_SESSION['copy']['race_type'] ;
        $values['class']     = $boat['classname'];
        $values['helm']      = $boat['helm'];
        $values['sailnum']   = $boat['sailnum'];
        $values['crew']      = $boat['crew'];
        $values['club']      = $boat['club'];
        $values['pn']        = $boat['pn'];
    }
    else
    {
        $values['race_type'] = $oldvalues['race_type'] ;
        $values['class']     = $boat['classname'];
        $values['helm']      = $boat['helm'];
        // only overwrite from competitor record if not set
        if (empty($values['sailnum'])) {$values['sailnum'] = $boat['sailnum'];}
        if (empty($values['crew']))    {$values['crew'] = $boat['crew'];}
        if (empty($values['club']))    {$values['club'] = $boat['club'];}
        if (empty($values['pn']))      {$values['pn'] = $boat['pn'];}
    }
    
    // set elapsed time to seconds
    $values['etime'] = strtotime($values['etval']) - strtotime("00:00:00");
    unset($values['etval']);

    require_once ("../../common/classes/race_class.php");
    $race_o = new RACE($db_o, $values['eventid']);

    // get max laps completed in this fleet
    $sql = "SELECT id, lap FROM t_result  WHERE eventid = {$values['eventid']} and fleet = {$values['fleet']}";
    $rs = CustomQuery($sql);
    $rs_data = array();
    while( $data = db_fetch_array($rs) )
    {
        $rs_data[] = $data;
    }
    $maxlap = max(array_column($rs_data, 'lap'));

    // calculate ctime and atime
    $values['ctime'] = $race_o->entry_calc_ct($values['etime'], $values['pn'], $values['race_type']);
    $values['atime'] = $race_o->entry_calc_at($values['etime'], $values['pn'], $values['race_type'], $values['lap'], $maxlap);

    // set points (trick to allow it to be inserted in corect position following 'after' processing
    if ($values['race_type'] == "pursuit")
    {
        if ($mode == "add")
        {
            $values['points'] = $values['points'] - 0.1 ;
        }
        else // edit
        {
            if ($values['points'] > 0 and $values['points'] != $oldvalues['points'])
            {
                if ($values['points'] < $oldvalues['points']) {
                    $values['points'] = $values['points'] - 0.1;
                } else {
                    $values['points'] = $values['points'] + 0.1;
                }
            }
        }
    }
    else
    {
        $values['points'] = 0;
    }

    // make notes html compatible
    $values['note'] = htmlspecialchars($values['note']);

    // check penalty codes
    if ($values['code'] != "DPI") { $values['penalty'] = "0.0"; }

    $_SESSION['results_update'] = true;
    $commit = true;
}


