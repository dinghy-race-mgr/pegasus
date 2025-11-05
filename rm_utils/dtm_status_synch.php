<?php
/*
 * dtm_status_check
 *
 * Interrogates dutyman members and duties view to check on confirmed and swap status.
 * Synchs racemanager allocated duty information with the latest information in dutyman regarding swaps
 * Records current duty status information in t_eventduty
 *
 * DESIGN
 *  - cronlog start of process
 *  - retrieve dutyman DUTIES view for future events
 *  - compare dutyman DUTIES info with raceManager duty allocations - if there is a change due to a swap - update racemanager.
 *     send details on swaps recorded to cronlog
 *     if there is a difference that cannot be resolved - collect this information - organised by rota
 *  - add latest confirm status and swap status to t_eventduty
 *     send details on total no. of changes made to cronlog
 *  - create new program.json file + transfer file to website (rework code in website_publish.php to be basis of the code used there
 *    with no outputs and also in this function
 *      send details on programme processing to cronlog
 *  - cronlog end of process
 */
// setup

// logging - start process (appending to cronlog)

// open database connection for dutyman
$db_dtm_o = new DB($cfg_dtm_db); // fixme to get config
$db_rmm_o = new DB($cfg_rm_db(; // fixme

// get control information - control name passed as argument
$control_name = check_arg("control", "isset", "", "");

$controls = json_decode("../config/cron_controls.json", true);

// switch this around - fail first
if (key_exists($control_name, $controls{'processes'}}
	{
        $control = $controls['processes'][$contol_name];

        // start prg_synch class
        $ps_o = new PRGSYNCH($control,$cfg);  // fixme - got to sort out where db classes are instatntiated

        // get dutyman member information
        $dtm_member = $ps_o->get_dtm_member_info();
        if (count($dtm_member) > 0)
        {
            $status = $ps_o->synch_dtm_member_info ($dtm_member);
            // logging of member details changed (nuuumber and list of who)
        }
        else
        {
            // logging - no changes made
        }

        // get dutyman duty status information
        $dtm_duty = $ps_o->get_dtm_duty_info();
        if (count($dtm_member) > 0)
        {
            $status = $ps_o->synch_dtm_duty_info($dtm_duty);
            // logging of no. unconfirmed, no. swaps requested, events affected
        }
        else
        {
            // logging no changes made
        }
    }
	else
	{
        // logging - control information not found - stopping
        // send an email
        // exit script
    }


