<?php
/*
 * publishresults.php
 *
 * utility used in rm_admin to recreate race and series results for an event and optionally post them to the website.
 *
 * arguments
 *    pagestate - 'init' for options selection,  'submit' for processing  (required)
 *    eventid   - id for event (t_event)   (required)
 *
 *    if pagestate = "submit" - pass values for processing and transfer as variables process_<seriescode>, transfer_<seriescode>
 *
 *
 */
$dbg = true;
$loc  = "..";
$page = "publish_results";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";

require_once ("{$loc}/common/lib/util_lib.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

$_SESSION['sql_debug'] = true;  // FIXME - turn off after debugging

$init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

if ($init_status)
{
    // set timezone
    if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

    // start log
    error_log(date('d-M H:i:s')." -- rm_util PUBLISH RESULTS ------- [session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

    // set initialisation flag
    $_SESSION['util_app_init'] = true;
}
else
{
    u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// log parameters
u_writedbg(date('d-Y H:i:s')." -- rm_util PUBLISH RESULTS ---script parameters<br><pre>".print_r($_REQUEST)."</pre>", __FILE__,__FUNCTION__,__LINE__);

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

if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

// system_info
$system_info = array(
    "sys_name"      => $_SESSION['sys_name'],
    "sys_release"   => $_SESSION['sys_release'],
    "sys_version"   => $_SESSION['sys_version'],
    "clubname"      => $_SESSION['clubname'],
    "sys_copyright" => $_SESSION['sys_copyright'],
    "sys_website"   => $_SESSION['sys_website'],
    "result_path"   => $_SESSION['result_public_path'],
    "result_url"    => $_SESSION['result_public_url']
);

// setup debug
array_key_exists("debug", $_REQUEST) ? $params['debug'] = $_REQUEST['debug'] : $params['debug'] = "off" ;

// FIXME deal with report parameters - add as suboptions
empty($_REQUEST['result_status']) ? $result_status = "final" : $result_status = $_REQUEST['result_status'];
isset($_REQUEST['include_club'])  ? $include_club = true : $include_club = false;
empty($_REQUEST['result_notes'])  ? $result_notes = "" : $result_notes = $_REQUEST['result_notes'];

// get event details
$eventid = u_checkarg("eventid", "checkintnotzero","");
$event_o = new EVENT($db_o);
$process_list = array();
$event = $event_o->get_event_byid($eventid);             // get event details
$process_list[] = array("name" => $event['event_name']." Race [".date("j-M", strtotime($event['event_date']))."]",
                        "code" => "$eventid", "type" => "race", "process" => false, "transfer" => false);
if ($event)
{
    $series_arr = get_all_event_series($event);          // get all series associated with the event
    foreach ($series_arr as $code => $series)
    {
        $process_list[] = array("name" => $series['seriesname'], "code" => $code, "type" => "series",
                                "process" => false, "transfer" => false, "series" => $series);
    }
}

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
    $pagefields['body'] = $tmpl_o->get_template("publishresults_error", array(), array("state"=>2, "eventid"=>$_REQUEST['eventid']));
    echo $tmpl_o->get_template("basic_page", $pagefields );
}

/* ------------ get user input page ---------------------------------------------*/
elseif ($_REQUEST['pagestate'] == "init")
{
    // if event exists create options page
    if ($event)
    {
        $formfields = array(
            "instructions" => "Creates and publishes (transfers) results files associated with this event</br>
           <span class=' rm-text-xs'>Please set the publish options below.</br>",
            "script" => "publish_results.php?eventid=$eventid&pagestate=submit",
        );

        $pagefields['header-right'] = "{$event['event_name']} - {$event['event_date']}  (". substr($event['event_start'], 0, 5) . ")";
        $pagefields['body'] = $tmpl_o->get_template("publishresults_form", $formfields,
                              array("list" => $process_list, "series"=>$series_arr, "upload" => $_SESSION['result_upload']));
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
    // establish processes to be done
    foreach($process_list as $k => $process)
    {
        if ($process['type'] == "race")
        {
            $process_list[$k]['process'] = true;
            $x = "transfer_".$process['code'];
            if (u_checkarg($x, "setbool", "1")) { $process_list[$k]['transfer'] = true;}
        }
        else   // a series
        {
            $x = "process_".$process['code'];
            if (u_checkarg($x, "setbool", "1")) { $process_list[$k]['process'] = true;}
            $x = "transfer_".$process['code'];
            if (u_checkarg($x, "setbool", "1")) { $process_list[$k]['transfer'] = true;}
        }
    }

    require_once("{$loc}/common/classes/raceresult_class.php");
    $result_o = new RACE_RESULT($db_o, $eventid);

    // page header
    echo $tmpl_o->get_template("process_header", $pagefields, array());
    ob_flush();
    flush();

    $tab = 0;
    $files = array();
    $continue = true;

    if (!$event)
    {
        $state = 3;
    }

    // =========  UPDATE REQUESTED RESULTS FILES ================================================

    else
    {
        if (!$continue)
        {
            $state = 4;
        }
        else
        {

            if ($dbg) { u_writedbg("<pre>PROCESSES: ".print_r($process_list,true)."</pre>", __FILE__,__FUNCTION__,__LINE__); }
            
            foreach ($process_list as $k => $process)
            {
                if ($process['process'])
                {
                    $tab++;
                    if ($process['type'] == "race")            // individual race result
                    {
                        $report_arr = array("action" => "race", "tab" => $tab, "detail" => "", "file" => array());
                        $label =

                        $fleet_msg = array();  // FIXME - future use
                        $status = process_result_file($loc, strtoupper($result_status), $include_club, $result_notes, $system_info, $fleet_msg);

                        if ($dbg) { u_writedbg("<pre>process_result_file -> status".print_r($status,true)."</pre>", __FILE__,__FUNCTION__,__LINE__); }

                        if ($status['success'])
                        {
                            $report_arr['result'] = "success";
                            $report_arr['msg'] = $status['err'];
                            $report_arr['file'] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);

                            if ($process['transfer'])
                            {
                                $files[] = $status['attr'];  // add to list of files to be transferred
                            }
                        }
                        else
                        {


                            // we have an error - process any detail information
                            $err_detail_txt = "";
                            foreach ($status['detail'] as $detail)
                            {
                                $err_detail_txt .= " | {$detail['type']} {$detail['code']}:  {$detail['msg']}";
                            }

                            $report_arr['result'] = "fail";
                            $report_arr['msg'] = $status['err']; // . "|" . $err_detail_txt;
                            $report_arr['detail'] = $err_detail_txt;
                            $continue = false;
                        }
                    }

                    else                                          // series result
                    {
                        $opts = array(
                            "folder" => $process['series']['folder'],                                             // results folder to use
                            "inc-pagebreak" => $series['opt_pagebreak'],                                          // page break after each fleet
                            "inc-codes" => $series['opt_scorecode'],                                              // include key of codes used
                            "inc-club" => $series['opt_clubnames'],                                               // include club name for each competitor
                            "inc-turnout" => $series['opt_turnout'],                                              // include turnout statistics
                            "race-label" => $series['opt_racelabel'],                                             // use race number or date for labelling races
                            "club-logo" => "../../club_logo.jpg",                                                 // if set include club logo
                            "styles" => $_SESSION['baseurl'] . "/config/style/result_{$series['opt_style']}.css"  // styles to be used
                        );

                        $report_arr = array("action" => "series", "tab" => $tab, "detail" => "", "file" => array());

                        $series_notes = "";    // FIXME not currently used
                        $status = process_series_file($opts, $process['series']['event_seriescode'], strtoupper($result_status), $system_info, $series_notes);

                        if ($status['success'])
                        {
                            $report_arr['result'] = "success";
                            $report_arr['msg'] = $status['err'];
                            $report_arr['file'] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);

                            if ($process['transfer']) {
                                $files[] = $status['attr'];  // add to list of files to be transferred
                            }
                        }
                        else
                        {
                            // we have an error - process any detail information
                            $err_detail_txt = "";
                            foreach ($status['detail'] as $detail) {
                                $err_detail_txt .= " | {$detail['type']} {$detail['code']}:  {$detail['msg']}";
                            }

                            $report_arr['result'] = "fail";
                            $report_arr['msg'] = $status['err'];// . "|" . $err_detail_txt;
                            $report_arr['detail'] = $err_detail_txt;
                            $continue = false;
                        }
                    }

                    // display result
                    echo $tmpl_o->get_template("publishresults_item_rpt", array("label" => $process['name']), $report_arr);
                    ob_flush();
                    flush();
                }
            }
        }

        // =============  TRANSFER REQUESTED FILES ========================================
        if ($continue)
        {

            if (count($files) > 0)            // files to transfer - first create new inventory file
            {
                $tab++;

                $result_year = date("Y", strtotime($event['event_date']));
                $inventory = process_inventory($result_year, $system_info);

                $files[] = array(
                    "eventid"   => "",
                    "eventyear" => $result_year,
                    "folder"    => "",
                    "format"    => "json",
                    "filename"  => $inventory['filename'],
                    "label"   => "inventory file",
                    "notes"   => "",
                    "status"  => "final",
                    "rank"    => 0,
                );

                if ($inventory)
                {
                        $tab++;
                        $report_arr = transfer_files($files, $_SESSION['result_transfer_protocol']);
                        $report_arr['action'] = "transfer";
                        $report_arr['tab'] = $tab;

                        echo $tmpl_o->get_template("publishresults_item_rpt", array("label"=>"File Transfers"), $report_arr);
                        ob_flush();
                        flush();
                }
                else
                {
                    $state = 5;
                }
            }

        }
    }

    // add end of report html
    echo $tmpl_o->get_template("publishfooter", array(), $report_arr);
    ob_flush();
    flush();

    if ($state != 0 )  // deal with error conditions
    {
        $pagefields['body'] = $tmpl_o->get_template("publishresults_error", array(), array("state"=>$state, "eventid"=>$eventid));
        echo $tmpl_o->get_template("basic_page", $pagefields );
    }

//
//
//
//    $race_results   = u_checkarg("race_results", "setbool","1");
//
//    $series_results = series_to_be_processed($_REQUEST);
//
//    $post_results   = u_checkarg("post_results", "setbool","1");
//    echo "<pre>args - ".print_r($_REQUEST,true)."</pre>";
//    echo "<pre>race=$race_results, series=$series_results, post=$post_results</pre>";
//
//    // FIXME deal with report parameters - add as suboptions
//    empty($_REQUEST['result_status']) ? $result_status = "final" : $result_status = $_REQUEST['result_status'];
//    isset($_REQUEST['include_club'])  ? $include_club = true : $include_club = false;
//    empty($_REQUEST['result_notes'])  ? $result_notes = "" : $result_notes = $_REQUEST['result_notes'];
//
//    // if event exists do requested processing
//    if ($event)
//    {
//        require_once("{$loc}/common/classes/raceresult_class.php");
//
//        $result_o = new RACE_RESULT($db_o, $eventid);
//
//        // display header for report page
//        echo $tmpl_o->get_template("process_header", $pagefields, array());
//        ob_flush();
//        flush();
//
//        // ----------- race result processing --------------------------------------------------------------------------
//
//        // initialise report output
//        $tab = 1;
//        $report_arr = array("action"=>"race", "tab"=> $tab, "detail"=> "", "file" => array());
//
//        $continue = true;
//        if ($race_results)
//        {
//            if ($continue)
//            {
//
//                $fleet_msg = array();  // FIXME - future use
//                $status = process_result_file($loc, strtoupper($result_status), $include_club, $result_notes, $system_info, $fleet_msg);
//
//                if ($status['success'])
//                {
//                    $report_arr['result'] = "success";
//                    $report_arr['msg'] = $status['err'];
//                    $report_arr['file'] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);
//
//                    $continue = true;
//                }
//                else
//                {
//                    // we have an error - process any detail information
//                    $err_detail_txt = "";
//                    foreach ($status['detail'] as $detail)
//                    {
//                        $err_detail_txt.= " | {$detail['type']} {$detail['code']}:  {$detail['msg']}";
//                    }
//
//                    $report_arr['result'] = "fail";
//                    $report_arr['msg'] = $status['err']."|". $err_detail_txt;
//                    $report_arr['detail'] = $err_detail_txt;
//                    $continue = false;
//                }
//            }
//            else
//            {
//                $report_arr['result'] = "stopped";
//                $report_arr['msg'] = "result files update processing stopped";
//                $continue = false;
//            }
//        }
//        else  // not requested
//        {
//            $report_arr['result'] = "notrequested";
//            $report_arr['msg'] = "race result processing not requested";
//            $report_arr['detail'] = "";
//            $continue = true;
//        }
//        echo $tmpl_o->get_template("publishresults_item_rpt", array(), $report_arr);
//        ob_flush();
//        flush();
//
//        // ----------- series result -------------------------------------------------------------------------------------
//
//        if (!$continue)
//        {
//            $state = 4;
//        }
//        else
//        {
//            foreach ($series_arr as $series_code => $series)
//            {
//                if ($series['process'])
//                {
//                    echo "<pre>code= $series_code ".print_r($series,true)."</pre>";
//
//                    $opts = array(
//                        "inc-pagebreak" => $series['opt_pagebreak'],                                          // page break after each fleet
//                        "inc-codes" => $series['opt_scorecode'],                                              // include key of codes used
//                        "inc-club" => $series['opt_clubnames'],                                               // include club name for each competitor
//                        "inc-turnout" => $series['opt_turnout'],                                              // include turnout statistics
//                        "race-label" => $series['opt_racelabel'],                                             // use race number or date for labelling races
//                        "club-logo" => "../../club_logo.jpg",                                                 // if set include club logo
//                        "styles" => $_SESSION['baseurl'] . "/config/style/result_{$series['opt_style']}.css"  // styles to be used
//                    );
//
//                    $tab++;
//                    $report_arr = array("action"=>"series", "tab"=>$tab,  "detail"=> "", "file" => array());
//
//                    $series_notes = "";    // FIXME not currently used
//                    $status = process_series_file($opts, $series['event_seriescode'], strtoupper($result_status), $system_info, $series_notes);
//
//                    echo "<pre>".print_r($status,true)."</pre>";
//
//                    if ($status['success'])
//                    {
//                        // file OK
//                        $report_arr['result'] = "success";
//                        $report_arr['msg'] = $status['err'];
//                        $report_arr['file'] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);
//
//                        $continue = true;
//                    }
//                    else
//                    {
//                        // we have an error - process any detail information
//                        $err_detail_txt = "";
//                        foreach ($status['detail'] as $detail) {
//                            $err_detail_txt .= " | {$detail['type']} {$detail['code']}:  {$detail['msg']}";
//                        }
//
//                        $report_arr['result'] = "fail";
//                        $report_arr['msg'] = $status['err'] . "|" . $err_detail_txt;
//                        $report_arr['detail'] = $err_detail_txt;
//                        $continue = false;
//                    }
//
//                }
//                else      // not requested
//                {
//                    $report_arr['result'] = "notrequested";
//                    $report_arr['msg'] = "{$series['seriesname']}- this series not requested";
//                    $continue = true;
//                }
//
//                echo $tmpl_o->get_template("publishresults_item_rpt", array(), $report_arr);
//                ob_flush();
//                flush();
//            }
//        }
//        if ($series_results)
//        {
//            if ($continue)
//            {
//                if ($part_of_series)     // check if event is part of any series
//                {
//                    // options for series result display
//                    $opts = array(
//                        "inc-pagebreak" => $series['opt_pagebreak'],                                          // page break after each fleet
//                        "inc-codes" => $series['opt_scorecode'],                                              // include key of codes used
//                        "inc-club" => $series['opt_clubnames'],                                               // include club name for each competitor
//                        "inc-turnout" => $series['opt_turnout'],                                              // include turnout statistics
//                        "race-label" => $series['opt_racelabel'],                                             // use race number or date for labelling races
//                        "club-logo" => "../../club_logo.jpg",                                                 // if set include club logo
//                        "styles" => $_SESSION['baseurl'] . "/config/style/result_{$series['opt_style']}.css"  // styles to be used
//                    );
//
//                    foreach ($series_arr as $series_code => $series) // loop through all series (fixme need to be able to select just primary or primary + secondary)
//                    {
//
//
//                        $series_processed = false;
//                        if ($series['process'])    // this series to be processed
//                        {
//                            echo "<pre>code= $series_code ".print_r($series,true)."</pre>";
//
//                            $tab++;
//                            $report_arr = array("action"=>"series", "tab"=>$tab,  "detail"=> "", "file" => array());
//
//                            $series_notes = "";   // fixme - curently not used
//                            $status = process_series_file($opts, $series['event_seriescode'], strtoupper($result_status), $system_info, $series_notes);
//
//                            echo "<pre>".print_r($status,true)."</pre>";
//
//                            if ($status['success'])
//                            {
//                                // file OK
//                                $report_arr['result'] = "success";
//                                $report_arr['msg'] = $status['err'];
//                                $report_arr['file'] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);
//
//                                $continue = true;
//                            }
//                            else
//                            {
//                                // we have an error - process any detail information
//                                $err_detail_txt = "";
//                                foreach ($status['detail'] as $detail) {
//                                    $err_detail_txt .= " | {$detail['type']} {$detail['code']}:  {$detail['msg']}";
//                                }
//
//                                $report_arr['result'] = "fail";
//                                $report_arr['msg'] = $status['err'] . "|" . $err_detail_txt;
//                                $report_arr['detail'] = $err_detail_txt;
//                                $continue = false;
//                            }
//                        }
//                        else
//                        {
//                            $report_arr['result'] = "notrequested";
//                            $report_arr['msg'] = "{$series['seriesname']}- this series not requested";
//                            $continue = true;
//                        }
//
//                        echo $tmpl_o->get_template("publishresults_item_rpt", array(), $report_arr);
//                        ob_flush();
//                        flush();
//                        $series_processed = true;
//                    }
//
//                }
//                else // not part of a series
//                {
//                    $report_arr['result'] = "info";
//                    $report_arr['msg'] = "not part of a series - no series result file to update";
//                    $continue = true;
//                    echo $tmpl_o->get_template("publishresults_item_rpt", array(), $report_arr);
//                    ob_flush();
//                    flush();
//                }
//            }
//            else  // processing has stopped
//            {
//                $report_arr['result'] = "stopped";
//                $report_arr['msg'] = "result files update processing stopped";
//                $continue = false;
//            }
//        }
//        else  // not requested
//        {
//            $report_arr['result'] = "notrequested";
//            $report_arr['msg'] = "series result processing not requested";
//            $continue = true;
//        }
//
//        if (!$series_processed)
//        {
//            echo $tmpl_o->get_template("publishresults_item_rpt", array(), $report_arr);
//            ob_flush();
//            flush();
//        }
//        // ----------- transfer results files -------------------------------------------------------------------------------------
//
//        $tab++;
//
//        if (!$continue)
//        {
//            $state = 5;
//        }
//        else
//        {
//            if ($post_results)
//            {
////                // check if the race was part of a series
////                $series = $event_o->event_in_series($eventid);  // - if not set series upload flag
////                if (!$series) { $series['opt_upload'] = true; } // has effect of ignoring series
//
//                // results files will be transferred if
//                //    a) individual race has not been embargoed, OR
////              //      b) the series upload flag is not set in t_series, OR
//                //    c) the result_upload parameter is not set to 'none'
//
////                if ($result_status == "embargoed" OR !$series['opt_upload'] OR $_SESSION['result_upload'] == "none")
//                if ($result_status == "embargoed" OR $_SESSION['result_upload'] == "none")
//                {
//                    $txt = "";
//                    if ($_SESSION['result_upload'] == "none")
//                    {
//                        $txt.= "System is not configured to allow file transfer [parameter: result_upload]";
//                    }
//
//                    if ($series_results)
//                    {
//                        if (!$series['opt_upload'])
//                        {
//                            $txt.= "This series is not configured to allow file transfer. ";
//                        }
//                    }
//
//                    if ($race_results)
//                    {
//                        if ($result_status == "embargoed") { $txt.= "These race results are embargoed and cannot be transferred. "; }
//                    }
//
//                    $continue = true;
//                    $report_arr = array("action" =>"transfer", "tab" =>$tab,  "detail" => $txt, "result" => "info", "file" => array());
//                    $report_arr['msg'] = "results transfer not requested";
//                }
//                else  //process transfer
//                {
//                    // create inventory
//                    $result_year = date("Y", strtotime($event['event_date']));
//                    $inventory = process_inventory($result_year, $system_info );
//
//                    if ($inventory)
//                    {
//                        // get files to be transferred (by checking files that have been updated by this process)
//                        $files = get_new_files_from_inventory($inventory['path'], $inventory['filename'], $result_year);
//
//                        // transfer them
//                        $report_arr = transfer_files($files, $_SESSION['result_transfer_protocol']);
//                        $report_arr['action'] = "transfer";
//                        $report_arr['tab'] = $tab;
//                    }
//                }
//            }
//            else  // not requested
//            {
//                $report_arr['result'] = "notrequested";
//                $report_arr['msg'] = "result files transfer not requested ..";
//            }
//
//            echo $tmpl_o->get_template("publishresults_item_rpt", array(), $report_arr);
//            ob_flush();
//            flush();
//        }
//
//        // display footer
//        echo $tmpl_o->get_template("publishfooter", array(), $report_arr);
//        ob_flush();
//        flush();
//    }
//    else
//    {
//        $state = 3;  // report missing event
//    }
//
//
//    if ($state != 0 )  // deal with error conditions
//    {
//        $pagefields['body'] = $tmpl_o->get_template("publishresults_error", array(), array("state"=>$state, "eventid"=>$eventid));
//        echo $tmpl_o->get_template("basic_page", $pagefields );
//    }

}













