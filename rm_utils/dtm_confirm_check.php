<?php
/*
 * dtm_confirm_check
 *
 * Design
 *  - need a simple generic template useful for other dtm issues
 *  - process runs once per n days (or on certain day of week) - via daily_cron_manager process
 *  - identifies all unconfirmed duties grouped by member
 *  - converts data into content for brevo (still not sure whether to do minimal or maximal)
 *         - if already 2 reminders sent - change the message
 *  - sends emails
 *  - updates count of reminders
 *  - identifies all members who have been sent more than a threshold value (5) reminders to confirm
 *  - prepares report for each rota_manager for over threshold duties
 *  - sends email with report to rota_managers
 *
 *
 */

$dbg = false;
$loc  = "..";
$page = "duty_confirm_check";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");

$today = date("Y-m-d");
if (key_exists("dryrun", $_REQUEST) ;
$dryrun = false;       // fixme get this from $_REQUEST - need to keep this active in form

$cfg = u_set_config("../config/common.ini", array(), false);
//echo "<pre>".print_r($cfg,true)."</pre>";

// logging - start process (appending to cronlog)
u_cronlog("\n--- DUTYMAN CONFIRM CHECK - start");

// access to database
$db_o = new DB($cfg['db_name'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_host']);

// get unconfirmed duties
$query = "SELECT a.eventid, dutycode, person, swapable, email, confirmed, confirmed_reminders, swap_requested, 
          swap_request_date, event_name, event_date, event_start 
          FROM t_eventduty as a JOIN t_event as b ON a.eventid=b.id 
          WHERE event_date >= '2025-11-12' and confirmed = 'X' and person is not null 
                and person not like '%not specified%' and person != '' 
          ORDER BY person ASC, event_date ASC";


