<?php



/*
After a results change we need to:
 - update race results
 - update series results - if race is part of series

For update of the race results I can use similar code to start of results_pg.php

For update of series results I can use similar code to test header on seriesresult_class.php

ISSUES - do I need to do this for pursuit races





The post to website function will be a separate grid function on t_event_results.  That function will create
a new results inventory file, race results file, and series results files and transfer the inventory, race and series files to the website using either
- direct copy (rm on same server as website
- ftp/sftp
- sftp



*/

// check if anything has changed
if ($_SESSION['results_update'])
{
    // initialise dbg file
    //file_put_contents($_SESSION['dbg_file'], '--- starting run '.date("Y-m-d H:i")." --------\n");

    // get results to be recalculated
    $sql = "SELECT id, fleet, class, sailnum, helm, crew, club, pn, lap, lap as finishlap, 
                         etime, 0 as ctime, 0 as atime, 0 as points, penalty, code, declaration, 
                         '' as note, '' as protest, 'F' as status FROM t_result  
                         WHERE eventid = {$values['eventid']} and fleet = {$values['fleet']}";

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
        $fleet_rs = $race_o->race_score($values['eventid'], $values['fleet'], $values['race_type'], $rs_data, "t_result" );
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

