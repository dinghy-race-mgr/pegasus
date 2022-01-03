<?php
/*
 * publishresults.php
 *
 * utility used in rm_admin to recreate race and series results for an event and optionally post them to the website.
 *
 * arguments
 *    pagestate - 'init' for options selection,  'submit' for processing
 *
 *    init
 *          eventid        - id for event (t_event)
 *
 *    submit
 *          eventid        - id for event (t_event)
 *          race_results   - 1|0 - 1 to produce race results
 *          series_results - 1|0 - 1 to produce series results
 *          post_results   - 1|0 - 1 to post results files to website
 *
 */
$loc  = "..";
$page = "publish_results";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";


require_once ("{$loc}/common/lib/util_lib.php");

session_start();
$_SESSION['dbglog'] = "../logs/dbglogs/rm_utils_dbg.log";

// initialise session if this is first call
if (!isset($_SESSION['util_app_init']) OR ($_SESSION['util_app_init'] === false))
{
    $init_status = u_initialisation("$loc/config/racemanager_cfg.php", "$loc/config/rm_utils_cfg.php", $loc, $scriptname);

    if ($init_status)
    {
        // set timezone
        if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

        // start log
        $_SESSION['syslog'] = "$loc/logs/adminlogs/".$_SESSION['syslog'];
        error_log(date('H:i:s')." -- PUBLISH ALL --------------------".PHP_EOL, 3, $_SESSION['syslog']);

        // set initialisation flag
        $_SESSION['util_app_init'] = true;
    }
    else
    {
        u_exitnicely($scriptname, 0, "initialisation failure", "one or more problems with script initialisation");
    }
}

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/seriesresult_class.php");
require_once ("{$loc}/common/lib/results_lib.php");


// connect to database
$db_o = new DB();
foreach ($db_o->db_getinivalues(false) as $data)
{
    $_SESSION["{$data['parameter']}"] = $data['value'];
}

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php", "$loc/common/templates/race_results_tm.php",
                             "./templates/layouts_tm.php", "./templates/publishresults_tm.php", "$loc/common/templates/series_results_tm.php"));

// get event details

if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

$pagefields = array(
    "loc" => $loc,
    "theme" => $styletheme,
    "stylesheet" => $stylesheet,
    "title" => "Publish Results",
    "header-left" => $_SESSION['sys_name'],
    "header-right" => "",
    "body" => "",
    "footer-left" => "",
    "footer-center" => "",
    "footer-right" => "",
);

// setup debug
array_key_exists("debug", $_REQUEST) ? $params['debug'] = $_REQUEST['debug'] : $params['debug'] = "off" ;

// get event details
$eventid = u_checkarg("eventid", "checkintnotzero","");
$event_o = new EVENT($db_o);
$event = $event_o->get_event_byid($eventid);

/* ------------ file selection page ---------------------------------------------*/
$state = 0;

if ($_REQUEST['pagestate'] != "init" AND $_REQUEST['pagestate'] != "submit")
{
    $state = 2;  // invalid pagestate

    if ($state != 0 )  // reset page to deal with error conditions
    {
        $pagefields['body'] = $tmpl_o->get_template("publishresults_error", array(), array("state"=>$state, "eventid"=>$_REQUEST['eventid']));
    }

    // display page
    echo $tmpl_o->get_template("basic_page", $pagefields );
}

/* ------------ get user input page ---------------------------------------------*/
elseif ($_REQUEST['pagestate'] == "init")
{
    // if event exists create options page
    if ($event)
    {
        // check if event is part of a series
        empty($event['series_code']) ? $series = false : $series = true;


        $formfields = array(
            "instructions" => "Creates and publishes results files for this event</br>
           <span class=' rm-text-xs'>Please check the publish options below.</br>",
            "script" => "publish_results.php?eventid=$eventid&pagestate=submit",
        );

        $pagefields['header-right'] = "{$event['event_name']} - {$event['event_date']}  (". substr($event['event_start'], 0, 5) . ")";
        $pagefields['body'] = $tmpl_o->get_template("publishresults_form", $formfields, array("series"=>$series, "series_name"=>$event['series_code']));
    }
    else
    {
        $state = 3;  // report missing event
    }

    if ($state != 0 )  // reset page to deal with error conditions
    {
        $pagefields['body'] = $tmpl_o->get_template("publishresults_error", array(), array("state"=>$state, "eventid"=>$_REQUEST['eventid']));
    }

    // display page
    echo $tmpl_o->get_template("basic_page", $pagefields );

}

/* ------------ submit page ------------------------------------------------------*/
elseif ($_REQUEST['pagestate'] == "submit")
{
    $race_results   = u_checkarg("race_results", "setbool","1");
    $series_results = u_checkarg("series_results", "setbool","1");
    $post_results   = u_checkarg("post_results", "setbool","1");

    //echo "<pre>eventid: $eventid|race: $race_results|series: $series_results|post: $post_results|</pre>";

    // if event exists do requested processing
    if ($event)
    {
        require_once ("{$loc}/common/classes/result_class.php");

        $result_o = new RESULT($db_o, $eventid);
        $transfer_file = array();
        $report = array();
        $continue = true;

        // display header for report page
        $pagefields['header-right'] = "{$event['event_name']} - {$event['event_date']}  (". substr($event['event_start'], 0, 5) . ")";
        $pagefields['body'] = "<h1>Result File Refresh</h1>";
        echo $tmpl_o->get_template("basic_page", $pagefields, array());
        ob_flush();
        flush();

        $report_arr = array("action"=>"race");
        if ($race_results)
        {
            if ($continue)
            {
                // FIXME deal with report parameters - add as suboptions
                empty($_REQUEST['result_status']) ? $result_status = "FINAL" : $result_status = strtoupper($_REQUEST['result_status']);
                isset($_REQUEST['include_club']) ? $include_club = true : $include_club = false;
                empty($_REQUEST['result_notes']) ? $result_notes = "" : $result_notes = $_REQUEST['result_notes'];

                $fleet_msg = array();  // FIXME - future use
                $status = process_result_file($loc, $result_status, $include_club, $result_notes, $fleet_msg);

                if ($status['success'])
                {
                    $continue = true;
                    $transfer_file[] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);
                    $report_arr['result'] = "success";
                    $report_arr['msg'] = $status['err'];
                }
                else
                {
                    // deal with failure
                    $continue = false;
                    $report_arr['result'] = "fail";
                    $report_arr['msg'] = $status['err'];
                }
            }
            else
            {
                $report_arr['result'] = "stopped";
                $report_arr['msg'] = "result files update processing stopped ..";
            }
        }
        else  // not requested
        {
            $report_arr['result'] = "notrequested";
        }
        echo $tmpl_o->get_template("publishresults_item_rpt", array(), $report_arr);
        ob_flush();
        flush();

        $report_arr = array("action"=>"series");
        if ($series_results)
        {
            if ($continue)
            {
                $series = $event_o->event_in_series($eventid);  // check if event is part of series
                if ($series)
                {
                    // FIXME some of these should be set as form options
                    $opts = array(
                        "inc-pagebreak" => $series['opt_pagebreak'],                                          // page break after each fleet
                        "inc-codes"     => $series['opt_scorecode'],                                            // include key of codes used
                        "inc-club"      => $series['opt_clubnames'],                                           // include club name for each competitor
                        "inc-turnout"   => $series['opt_turnout'],                                            // include turnout statistics
                        "race-label"    => $series['opt_racelabel'],                                          // use race number or date for labelling races
                        "club-logo"     => $_SESSION['baseurl']."/config/images/club_logo.jpg",               // if set include club logo
                        "styles" => $_SESSION['baseurl']."/config/style/result_{$series['opt_style']}.css"    // styles to be used
                    );

                    $status = process_series_file($eventid, $opts, $event['series_code'], $result_status);

                    if ($status['success'])
                    {
                        $transfer_file[] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);
                        $continue = true;
                        $report_arr['result'] = "success";
                        $report_arr['msg'] = $status['err'];
                    }
                    else
                    {
                        // we have an error - process any detail information
                        $err_detail_txt = "";
                        foreach ($status['detail'] as $detail)
                        {
                            $err_detail_txt.= " | {$detail['type']} {$detail['code']}:  {$detail['msg']}";
                        }

                        $continue = false;
                        $report_arr['result'] = "fail";
                        $report_arr['msg'] = $status['err']."|". $err_detail_txt;
                    }
                }
                else // not part of a series
                {
                    $continue = true;
                    $report_arr['result'] = "info";
                    $report_arr['msg'] = "not part of a series - no series result file to update ..";
                }
            }
            else  // processing has stopped
            {
                $continue = true;
                $report_arr['result'] = "stopped";
                $report_arr['msg'] = "result files update processing stopped ..";
            }

        }
        else  // not requested
        {
            $report_arr['result'] = "notrequested";
        }
        echo $tmpl_o->get_template("publishresults_item_rpt", array(), $report_arr);
        ob_flush();
        flush();


        $report_arr = array("action"=>"transfer");
        if ($post_results)
        {
            if ($continue)
            {
                if ($result_status != "embargoed")  // FIXME - where is embargoed applied
                {

                    // get inventory file name/path
                    $inventory_file = $result_o->get_inventory_filename();
                    $inventory_path = $_SESSION['result_path'] . DIRECTORY_SEPARATOR . $inventory_file;
                    $inventory_url = $_SESSION['result_url'] . "/" . $inventory_file;

                    // create inventory
                    $inventory = $result_o->create_result_inventory($inventory_path);

                    if ($inventory['success'])                         // if inventory created successfully then proceed
                    {
                        // add inventory file to file to be transferred
                        $transfer_files[] = array("path" => $inventory_path, "url" => $inventory_url,
                            "file" => $inventory_file);

                        // transfer files using relevant protocol
                        $status = process_transfer($transfer_files, $_SESSION['ftp_protocol']);

                        $continue = false;
                        $status['success'] == "all" or $status['success'] == "some" ? $report_arr['result'] = "success" : $report_arr['result'] = "fail";;
                        $report_arr['msg'] = "{$status['success']} files successfully transferred";
                    }
                }
            }
            else  // processing has stopped
            {
                $continue = true;
                $report_arr['result'] = "stopped";
                $report_arr['msg'] = "result files update processing stopped ..";
            }

        }
        else  // not requested
        {
            $report_arr['result'] = "notrequested";
        }
        echo $tmpl_o->get_template("publishresults_item_rpt", array(), $report_arr);
        ob_flush();
        flush();
    }
    else
    {
        $state = 3;  // report missing event
    }

    if ($state != 0 )  // deal with error conditions
    {
        $pagefields['body'] = $tmpl_o->get_template("publishresults_error", array(), array("state"=>$state, "eventid"=>$eventid));
        echo $tmpl_o->get_template("basic_page", $pagefields );
    }

}

















