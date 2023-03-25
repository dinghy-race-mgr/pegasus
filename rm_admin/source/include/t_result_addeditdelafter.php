<?php

// check if anything has changed
if ($_SESSION['results_update'])
{
    $eventid   = $values['eventid'];
    $fleet     = $values['fleet'];
    $race_type = $values['race_type'];


    // get results to be recalculated
    $sql = "SELECT id, fleet, class, sailnum, helm, crew, club, pn, lap, lap as finishlap, 
                   etime, 0 as ctime, 0 as atime, 0 as points, penalty, code, declaration, 
                   '' as note, '' as protest, 'F' as status FROM t_result  
                   WHERE eventid = $eventid and fleet = $fleet";

    $rs = CustomQuery($sql);

    // put results into data array
    $rs_data = array();
    while( $data = db_fetch_array($rs) )
    {
        $rs_data[] = $data;
        //error_log("DATA: {$data['class']} {$data['sailnum']} {$data['lap']} {$data['etime']} {$data['code']} {$data['penalty']} {$data['points']}\n",3, $_SESSION['dbg_file']);
    }

    if ($rs_data)
    {
        // use RM results class to do recalculation
        require_once ("../../common/lib/util_lib.php");
        require_once ("../../common/classes/dbphpr_class.php");
        require_once ("../../common/classes/race_class.php");

        $db_o = new DBPHPR;
        $_SESSION['resultcodes'] = $db_o->db_getresultcodes("result");
        $race_o = new RACE($db_o, $values['eventid']);

        $fleet_rs['warning'] = array();
        $fleet_rs['data'] = array();
        $fleet_rs = $race_o->race_score($eventid, $fleet, $race_type, $rs_data, "t_result" );
        $results = $fleet_rs['data'];
        $warning = $fleet_rs['warning'];
    }
    else
    {
        $pageObject->setMessageType(MESSAGE_ERROR);
        $pageObject->setMessage("ERROR: data not found - results for fleet {$values['fleet']} not updated");
    }
}

// now reset result_update flag
$_SESSION['results_update'] = false;

