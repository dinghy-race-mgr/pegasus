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
if (!isset($_SESSION['util_app_init']) OR ($_SESSION['util_app_init'] === false))
{
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
}

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

$ftp_info = array(
    "server" => $_SESSION['ftp_server'],
    "user"   => $_SESSION['ftp_user'],
    "pwd"    => $_SESSION['ftp_pwd'],
);

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
        require_once("{$loc}/common/classes/raceresult_class.php");

        $result_o = new RACE_RESULT($db_o, $eventid);
        $transfer_file = array();
        $report = array();
        $continue = true;

        // display header for report page
        $pagefields['header-right'] = "{$event['event_name']} - {$event['event_date']}  (". substr($event['event_start'], 0, 5) . ")";
        $pagefields['body'] = "<h1>Result File Refresh</h1>";
        echo $tmpl_o->get_template("basic_page", $pagefields, array());
        ob_flush();
        flush();

        // ----------- race result -------------------------------------------------------------------------------------

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

        // ----------- series result -------------------------------------------------------------------------------------

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

                    $status = process_series_file($opts, $event['series_code'], $result_status);

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


        // ----------- transfer results files -------------------------------------------------------------------------------------

        $report_arr = array("action"=>"transfer");
        if ($post_results)
        {
            if ($continue)     // continue if previous processsing hasn't causes a problem
            {
                // if results from this race have been embargoed - we shouldn't transfer anything or update the inventory
                // also check if upload is turned on in config
                if ($result_status != "embargoed" and $_SESSION['result_upload'] != "none")
                {
                    // create inventory
                    $result_year = date("Y", strtotime($event['event_date']));
                    $inventory = process_inventory($result_year );

                    if ($inventory)                         // if inventory created successfully then proceed
                    {
                        // inventory file into array
                        $invdata = json_decode(file_get_contents($inventory['path']."/".$inventory['filename']), true);

                        // create file list for transfer ( upload not done or out of date and not embargoed)
                        $files = array();
                        foreach($invdata['events'] as $id=>$invevent)
                        {
                            foreach($invevent['resultsfiles'] as $k=>$file)
                            {
                                // check if file neeeds uploading
                                $upload_time = strtotime($file['upload']);
                                $update_time = strtotime($file['update']);
                                if ($file['status'] != "embargoed" and (empty($file['upload']) or $upload_time < $update_time))
                                {
                                    $files[] = $file;
                                }
                            }
                        }

                        // add inventory file
                        $files[] = array(
                            "file_id" =>"",
                            "year"   => $result_year,
                            "type"   => "",
                            "format" => "json",
                            "file"   => $result_o->get_inventory_filename($result_year),
                            "label"  => "inventory file",
                            "notes"  => "",
                            "status" => "final",
                            "rank"   => 0,
                            "upload" => "",
                            "update" => date("Y-m-d H:i:s")
                        );

                        // transfer files
                        $num_for_transfer = count($files);
                        if ($num_for_transfer > 0) {

                            // use appropriate protocol for upload
                            if ($_SESSION['result_upload'] == "network")
                            {
                                empty($_SESSION['result_public_path']) ? $target_path = "" : $target_path = $_SESSION['result_public_path'];
                                empty($_SESSION['result_public_url']) ? $target_url = "" : $target_url = $_SESSION['result_public_url'];

                                if (empty($target_path) or empty($target_url))
                                {
                                    $continue = true;
                                    $report_arr['result'] = "stopped";
                                    $report_arr['msg'] = "results location for website not configure [{$_SESSION['result_public_path']}<br>result files update processing stopped ..";
                                }
                                else
                                {
                                    $status = process_transfer_network($files, $target_path, $target_url);
                                    $msg_type = "fail";
                                    $txt = $status['num_files']." of $num_for_transfer uploaded";
                                    if ($status['result'] and $status['complete'] )
                                    {
                                        $msg_type = "success";
                                    }
                                    elseif ($status['result'])
                                    {
                                        $msg_type = "warning";
                                    }

                                    $detail_htm = "";
                                    $detail_txt = "";
                                    if ($msg_type != "success")
                                    {
                                        foreach ($status['log'] as $file)
                                        {
                                            $detail_htm.= "<p>$file</p>";
                                            $detail_txt.= "$file\n";
                                        }
                                    }
                                }

                            }
                            elseif ($_SESSION['result_upload'] == "ftp")
                            {
                                $ftp = array("protocol"=>$_SESSION["ftp_protocol"], "server"=> $_SESSION["ftp_server"],
                                    "user"=> $_SESSION["ftp_user"], "pwd"=> $_SESSION["ftp_pwd"]);

                                if (empty($ftp['protocol']) or empty($ftp['server']) or empty($ftp['user']) or empty($ftp['pwd']))
                                {
                                    $continue = true;
                                    $report_arr['result'] = "stopped";
                                    $report_arr['msg'] = "ftp protocol for website not configured correctly<br>result files update processing stopped ..";
                                }
                                else
                                {
                                    $status = process_transfer_ftp($files, $ftp); // FIXME implement function
                                }
                            }
                            else
                            {
                                $continue = true;
                                $report_arr['result'] = "stopped";
                                $report_arr['msg'] = "transfer protocol option not configured correctly [{$_SESSION['result_upload']}]<br>result files update processing stopped ..";
                            }
                        }


                        // report on transfer process - FIXME
                        $detail_htm = "";
                        foreach ($status['log'] as $file) { $detail_htm.= "<p>$file</p>"; }

                        $txt = $status['num_files']." of $num_for_transfer uploaded";
                        if ($status['result'] and $status['complete'] )
                        {
                            $report_arr['result'] = "success";
                            $report_arr['msg'] = "SUCCESS - $txt";
                        }
                        elseif ($status['result'])
                        {
                            $report_arr['result'] = "success";
                            $report_arr['msg'] = "PARTIAL SUCCESS - $txt";
                            $report_arr['detail'] = $detail_htm;
                        }
                        else
                        {
                            $report_arr['result'] = "fail";
                            $report_arr['msg'] = "FAILED - no files uploaded";
                            $report_arr['detail'] = $detail_htm;
                        }
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
            $report_arr['msg'] = "result files transfer not requested ..";
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

















