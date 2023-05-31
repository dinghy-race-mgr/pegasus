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

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

// initialise session if this is first call
//if (!isset($_SESSION['util_app_init']) OR ($_SESSION['util_app_init'] === false))
//{
    $init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

    if ($init_status)
    {
        // set timezone
        if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

        // start log
        error_log(date('H:i:s')." -- rm_util PUBLISH RESULTS ------- [session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

        // set initialisation flag
        $_SESSION['util_app_init'] = true;
    }
    else
    {
        u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
//}

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/rota_class.php");
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



// setup debug
array_key_exists("debug", $_REQUEST) ? $params['debug'] = $_REQUEST['debug'] : $params['debug'] = "off" ;

// get event details
$eventid = u_checkarg("eventid", "checkintnotzero","");
$event_o = new EVENT($db_o);
$event = $event_o->get_event_byid($eventid);
$series = $event_o->event_in_series($eventid);  // check if event is part of series

$pagefields = array(
    "loc" => $loc,
    "theme" => $styletheme,
    "stylesheet" => $stylesheet,
    "title" => "Publish Results",
    "header-left" => $_SESSION['sys_name'],
    "header-right" => "{$event['event_name']} - {$event['event_date']}  (". substr($event['event_start'], 0, 5) . ")",
    "body" => "",
    "footer-left" => "",
    "footer-center" => "",
    "footer-right" => "",
);

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

    // FIXME deal with report parameters - add as suboptions
    empty($_REQUEST['result_status']) ? $result_status = "final" : $result_status = $_REQUEST['result_status'];
    isset($_REQUEST['include_club']) ? $include_club = true : $include_club = false;
    empty($_REQUEST['result_notes']) ? $result_notes = "" : $result_notes = $_REQUEST['result_notes'];

    //echo "<pre>SUBMIT - eventid: $eventid|race: $race_results|series: $series_results|post: $post_results|result_status: $result_status|inc_club: $include_club|notes: $result_notes|</pre>";

    // if event exists do requested processing
    if ($event)
    {
        require_once("{$loc}/common/classes/raceresult_class.php");

        $result_o = new RACE_RESULT($db_o, $eventid);
        $transfer_file = array();
        $report = array();
        $continue = true;

        // display header for report page
        echo $tmpl_o->get_template("process_header", $pagefields, array());
        ob_flush();
        flush();

        // ----------- race result -------------------------------------------------------------------------------------

        $report_arr = array("action"=>"race", "detail"=> "", "file" => array());
        if ($race_results)
        {
            if ($continue)
            {

                $fleet_msg = array();  // FIXME - future use
                $status = process_result_file($loc, strtoupper($result_status), $include_club, $result_notes, $fleet_msg);

                if ($status['success'])
                {
                    $report_arr['result'] = "success";
                    $report_arr['msg'] = $status['err'];
                    $report_arr['file'] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);

                    //$transfer_file[] = $report_arr['file'];
                    $continue = true;
                }
                else
                {
                    // we have an error - process any detail information
                    $err_detail_txt = "";
                    foreach ($status['detail'] as $detail)
                    {
                        $err_detail_txt.= " | {$detail['type']} {$detail['code']}:  {$detail['msg']}";
                    }

                    $report_arr['result'] = "fail";
                    $report_arr['msg'] = $status['err']."|". $err_detail_txt;
                    $report_arr['detail'] = $err_detail_txt;
                    $continue = false;
                }
            }
            else
            {
                $report_arr['result'] = "stopped";
                $report_arr['msg'] = "result files update processing stopped";
                $continue = false;
            }
        }
        else  // not requested
        {
            $report_arr['result'] = "notrequested";
            $report_arr['msg'] = "race result processing not requested";
            $report_arr['detail'] = "";
            $continue = true;
        }
        echo $tmpl_o->get_template("publishresults_item_rpt", array(), $report_arr);
        ob_flush();
        flush();

        // ----------- series result -------------------------------------------------------------------------------------

        $report_arr = array("action"=>"series", "detail"=> "", "file" => array());
        if ($series_results)
        {
            if ($continue)
            {
                // $series = $event_o->event_in_series($eventid);  // check if event is part of series
                if ($series)
                {
                    $opts = array(
                        "inc-pagebreak" => $series['opt_pagebreak'],                                          // page break after each fleet
                        "inc-codes"     => $series['opt_scorecode'],                                          // include key of codes used
                        "inc-club"      => $series['opt_clubnames'],                                          // include club name for each competitor
                        "inc-turnout"   => $series['opt_turnout'],                                            // include turnout statistics
                        "race-label"    => $series['opt_racelabel'],                                          // use race number or date for labelling races
                        "club-logo"     => "../../club_logo.jpg",               // if set include club logo
                        "styles" => $_SESSION['baseurl']."/config/style/result_{$series['opt_style']}.css"    // styles to be used
                    );

                    $series_notes = "";   // fixme - curently not used
                    $status = process_series_file($opts, $event['series_code'], strtoupper($result_status), $series_notes);

                    if ($status['success'])
                    {
                        // file OK
                        $report_arr['result'] = "success";
                        $report_arr['msg'] = $status['err'];
                        $report_arr['file'] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);

                        //$transfer_file[] = $report_arr['file'];
                        $continue = true;
                    }
                    else
                    {
                        // we have an error - process any detail information
                        $err_detail_txt = "";
                        foreach ($status['detail'] as $detail)
                        {
                            $err_detail_txt.= " | {$detail['type']} {$detail['code']}:  {$detail['msg']}";
                        }

                        $report_arr['result'] = "fail";
                        $report_arr['msg'] = $status['err']."|". $err_detail_txt;
                        $report_arr['detail'] = $err_detail_txt;
                        $continue = false;
                    }
                }
                else // not part of a series
                {
                    $report_arr['result'] = "info";
                    $report_arr['msg'] = "not part of a series - no series result file to update";
                    $continue = true;
                }
            }
            else  // processing has stopped
            {
                $report_arr['result'] = "stopped";
                $report_arr['msg'] = "result files update processing stopped";
                $continue = false;
            }

        }
        else  // not requested
        {
            $report_arr['result'] = "notrequested";
            $report_arr['msg'] = "series result processing not requested";
            $continue = true;
        }


        echo $tmpl_o->get_template("publishresults_item_rpt", array(), $report_arr);
        ob_flush();
        flush();


        // ----------- transfer results files -------------------------------------------------------------------------------------

        if ($post_results)
        {
            if ($continue)     // continue if previous processsing hasn't causes a problem
            {
                // check if the race was part of a series
                $series = $event_o->event_in_series($eventid);  // - if not set series upload flag
                if (!$series) { $series['opt_upload'] = true; } // has effect of ignoring series

                // results files will be transferred if a) individual race has not been embargoed, b) the series upload flag is
                // not set in t_series, c) the result_upload parameter is not set to 'none'

                if ($result_status == "embargoed" OR !$series['opt_upload'] OR $_SESSION['result_upload'] == "none")
                {
                    $txt = "";
                    if ($_SESSION['result_upload'] == "none") { $txt.= "System is not configured to allow file transfer [parameter: result_upload]"; }

                    if ($series_results)
                    {
                        if (!$series['opt_upload']) { $txt.= "This series is not configured to allow file transfer. "; }
                    }

                    if ($race_results)
                    {
                        if ($result_status == "embargoed") { $txt.= "These race results are embargoed and cannot be transferred. "; }
                    }

                    $continue = true;
                    $report_arr['result'] = "info";
                    $report_arr['msg'] = "results transfer not requested";
                    $report_arr['detail'] = $txt;
                }
                else  //process transfer
                {
                    // create inventory
                    $result_year = date("Y", strtotime($event['event_date']));
                    $inventory = process_inventory($result_year );

                    if ($inventory)                         // if inventory created successfully then proceed
                    {
                        // get files to be transferred
                        $files = get_files_from_inventory($inventory['path'], $inventory['filename'], $result_year);

                        // transfer them
                        $report_arr = transfer_files($files, $_SESSION['result_transfer_protocol']);
                        $report_arr['action'] = "transfer";
                    }
                }
            }
            else  // processing has stopped
            {
                $report_arr['result'] = "stopped";
                $report_arr['msg'] = "result files update processing stopped ..";
            }

        }
        else  // not requested
        {
            $report_arr['result'] = "notrequested";
            $report_arr['msg'] = "result files transfer not requested ..";
        }

        echo $tmpl_o->get_template("publishresults_item_rpt", array(), $report_arr);
        echo $tmpl_o->get_template("publishfooter", array(), $report_arr);

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

















