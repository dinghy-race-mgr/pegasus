<?php

/* ------------------------------------------------------------
   results_publish_pg
   
   Performs all of the tasks associated with publishing the results.  Appears in modal frame on results page
   
   arguments:
       eventid     id of event
       pagestate   control state for page
   
   ------------------------------------------------------------
*/

$loc        = "..";       // <--- relative path from script to top level folder
$page       = "publish";     //
$scriptname = basename(__FILE__);
$dbg        = false;
require_once ("$loc/common/lib/util_lib.php");
require_once ("$loc/common/lib/results_lib.php");

// start session
u_startsession("sess-rmracebox", 10800);

// arguments
$eventid = u_checkarg("eventid", "checkintnotzero","");     // eventid (required)
$pagestate = u_checkarg("pagestate", "set", "", "");        // pagestate (required)
if (empty($pagestate) OR !$eventid)
{
    u_exitnicely($scriptname, 0,"$page page - event id record [{$_REQUEST['eventid']}] or pagestate [{$_REQUEST['pagestate']}] are not defined",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// page initialisation
u_initpagestart($eventid, $page, false);

// classes
require_once ("$loc/common/classes/db_class.php");
require_once ("$loc/common/classes/template_class.php");
require_once ("$loc/common/classes/race_class.php");
require_once ("$loc/common/classes/rota_class.php");
require_once ("$loc/common/classes/event_class.php");
require_once ("$loc/common/classes/raceresult_class.php");
require_once ("$loc/common/classes/seriesresult_class.php");

// templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php", "./templates/layouts_tm.php",
    "./templates/results_tm.php", "$loc/common/templates/race_results_tm.php", "$loc/common/templates/series_results_tm.php"));


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

if (empty($pagestate) OR !$eventid)
{
    u_exitnicely($scriptname, 0,"$page page - event id record or pagestate are not defined",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

$db_o    = new DB;                              // database object
$event_o = new EVENT($db_o);                    // event object
$result_o  = new RACE_RESULT($db_o, $eventid);  // result object

$event = $event_o->get_event_byid($eventid);


if ($pagestate == "init")    // display information collection form
{
    if ($_SESSION['mode'] == "demo")
    {
        echo display_demo_page($loc, $eventid);
        $_SESSION["e_$eventid"]['result_publish'] = true;
    }
    else
    {
        $warning_count = u_checkarg("warnings", "checkint", "");
        $overide = u_checkarg("overide", "setbool", "1");

        if ($warning_count == 0 or $overide)
        {
            echo display_options_form($loc, $eventid);
        }
        else
        {
            echo display_warnings_page($loc, $eventid);
        }
    }

    ob_flush();
    flush();      
}

elseif ($pagestate == "process")    // run through process workflow
{
    $continue = true;
    $success = array(1=>true, 2=>true, 3=>true, 4=>true );
    u_writelog("Results processing started: ", $eventid);

    $row_start = array ("1" => 10, "2" => 100, "3" => 200, "4" => 300, "5" => 400, "6" => 500 );  // px of page height for process display
    $transfer_files = array(); // contains files to be transferred

    // deal with parameters from form
    $args = array(
        "ws_start"      => u_checkarg("ws_start", "set", "", ""),
        "wd_start"      => u_checkarg("wd_start", "set", "", ""),
        "ws_end"        => u_checkarg("ws_end", "set", "", ""),
        "wd_end"        => u_checkarg("wd_end", "set", "", ""),
        "result_status" => u_checkarg("result_status", "set", "", "final"),
        "include_club"  => u_checkarg("include_club", "setbool", "on"),
        "result_notes"  => u_checkarg("result_notes", "set", "", ""),
    );

    $detail_set = false;
    foreach ($args as $arg)
    {
        if (!empty($arg) or $arg == "final")
        {
            $detail_set = true;
            break;
        }
    }

    // update event with form details
    $update = $event_o->event_changedetail($eventid, $args);
    if ($update and $detail_set)
    {
        $results_msg = " - updated event wind details and notes";
    }
    else
    {
        $results_msg = " - event wind details and notes not supplied (or failed to update)";
    }
    u_writelog($results_msg, $eventid);

    echo start_page($loc);
    echo str_pad('',8192)."\n";
    ob_flush();
    flush();
    sleep(1);

    // -----------------------------------------------------------------------------------
    // step 1 archive data
    // -----------------------------------------------------------------------------------
    $step = 1;
    startProcess($step, $row_start[$step], "Archiving race data...", "warning");
    $result_o = new RACE_RESULT($db_o, $eventid);

    $status = process_archive();
    sleep(2);

    if ($status['copy'] and $status['archive'])
    {
        endProcess($step, $row_start[$step], "success", "Archived race data " );
        u_writelog("results archived", $eventid);
        $continue = true;
    }
    else
    {
        $status['copy'] ?  $msg1 = "results copied " : $msg1 = "results copy failed ";
        $status['archive'] ?  $msg2 = "results archived" : $msg2 = "archiving failed";

        endProcess($step, $row_start[$step], "fail", "Archive race data", "", "FAILED<i>$msg1 : $msg2]</i>");
        u_writelog("FAILED to archive results [$msg1 : $msg2]", $eventid);
        $continue = false;
        $success[1] = false;
    }

    // -----------------------------------------------------------------------------------
    // step 2 create race results file
    // -----------------------------------------------------------------------------------
    if ($continue)
    {
        $step++;
        startProcess($step, $row_start[$step], "Creating race results ...", "warning");

        // create race result
        $fleet_msg = array(); // TODO this is a future use feature - displays individual notes for each fleet - currently not collected anywhere
        $status = process_result_file($loc, strtoupper($args['result_status']), $args['include_club'], $args['result_notes'], $system_info, $fleet_msg);
        sleep(2);

        if ($status['success'])
        {
            endProcess($step, $row_start[$step], "success", "Saved race results", $status['url'], "Click to view");
            u_writelog("race results file created", $eventid);
            $transfer_file[] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);
            $continue = true;
        }
        else
        {
            endProcess($step, $row_start[$step], "fail", "Creating race results", "", "FAILED");
            u_writelog("FAILED to create results file", $eventid);
            $continue = false;
            $success[2] = false;
        }
    }

    // -----------------------------------------------------------------------------------
    // step 3 create series results file - if required
    // -----------------------------------------------------------------------------------
    if ($continue)    // continue with next step
    {
        $step++;

        if (!empty($_SESSION["e_$eventid"]["ev_seriescode"]))
        {
            $series = $event_o->event_in_series($eventid);

            // options for series result display
            $opts = array(
                "folder"        => "series",
                "inc-pagebreak" => $series['opt_pagebreak'],                                          // page break after each fleet
                "inc-codes"     => $series['opt_scorecode'],                                          // include key of codes used
                "inc-club"      => $series['opt_clubnames'],                                          // include club name for each competitor
                "inc-turnout"   => $series['opt_turnout'],                                            // include turnout statistics
                "race-label"    => $series['opt_racelabel'],                                          // use race number or date for labelling races
                "club-logo"     => "../../club_logo.jpg",                                             // if set include club logo
                "styles" => $_SESSION['baseurl']."/config/style/result_{$series['opt_style']}.css"    // styles to be used
            );

            startProcess($step, $row_start[$step], "Creating ".$series['seriesname']." results ... ", "warning");

            $series_notes = "";   // fixme - curently not used
            $status = process_series_file($opts, $event['series_code'], strtoupper($args['result_status']), $system_info, $series_notes);
            sleep(2);

            if ($dbg) { u_writedbg("<pre>STATUS STEP 3: ".print_r($status,true)."</pre>", __FILE__, __FUNCTION__, __LINE__); }

            if ($status['success'])
            {
                endProcess($step, $row_start[$step], "success", "Saved ".$series['seriesname']." results", $status['url'], "Click to view");
                u_writelog("series results file updated", $eventid);
                $transfer_file[] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);
                $continue = true;
            }
            else
            {
                // we have an error - process any detail information
                $err_detail_txt = "";

                if(array_key_exists("detail", $status))
                {
                    foreach ($status['detail'] as $detail)
                    {
                        $err_detail_txt.= " | {$detail['type']} {$detail['code']}:  {$detail['msg']}";
                    }
                }

                endProcess($step, $row_start[$step], "fail", "Creating ".$series['seriesname']." results", "", "FAILED");
                u_writelog("FAILED to update series results file [$err_detail_txt]", $eventid);
                $continue = true;  // should transfer what we can
                $success[3] = false;
            }
        }
        else
        {
            sleep(1);
            endProcess($step, $row_start[$step], "na", "Saving series result", "", "No series result to update ");
            u_writelog("Not part of a series - no series result file to update", $eventid);
            $continue = true;
        }
    }

    // -----------------------------------------------------------------------------------
    // step 3 extra - deal with any secondary series connections (defined in t_event.series_code_extra)
    //              - note: currently only does first secondary series
    // -----------------------------------------------------------------------------------
    if ($continue)
    {
        $step++;

        // check for secondary series codes
        if (!empty($_SESSION["e_$eventid"]['ev_seriescodeextra']))
        {
            $extra_series = explode(",", $_SESSION["e_$eventid"]['ev_seriescodeextra']);

            $series = $event_o->event_getseries($extra_series[0]);    // FIXME - initial implementation only allows for one extra series

            // options for series result display
            $opts = array(
                "folder"        => "special",
                "inc-pagebreak" => $series['opt_pagebreak'],                                          // page break after each fleet
                "inc-codes"     => $series['opt_scorecode'],                                          // include key of codes used
                "inc-club"      => $series['opt_clubnames'],                                          // include club name for each competitor
                "inc-turnout"   => $series['opt_turnout'],                                            // include turnout statistics
                "race-label"    => $series['opt_racelabel'],                                          // use race number or date for labelling races
                "club-logo"     => "../../club_logo.jpg",                                             // if set include club logo
                "styles" => $_SESSION['baseurl']."/config/style/result_{$series['opt_style']}.css"    // styles to be used
            );

            startProcess($step, $row_start[$step], "Creating ".$series['seriesname']." results ... ", "warning");

            $series_notes = "";   // FIXME - curently not used
            $status = process_series_file($opts, $extra_series[0], strtoupper($args['result_status']), $system_info, $series_notes);
            sleep(2);

            if ($dbg) { u_writedbg("<pre>STATUS STEP 3 EXTRA: ".print_r($status,true)."</pre>", __FILE__, __FUNCTION__, __LINE__); }

            if ($status['success'])
            {
                endProcess($step, $row_start[$step], "success", "Saved ".$series['seriesname']." results", $status['url'], "Click to view");
                u_writelog("secondary series results file updated", $eventid);
                $transfer_file[] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);
                $continue = true;
            }
            else
            {
                // we have an error - process any detail information
                $err_detail_txt = "";

                u_writedbg("STATUS: ".print_r($status,true), __FILE__, __FUNCTION__, __LINE__);
                if(array_key_exists("detail", $status))
                {
                    foreach ($status['detail'] as $detail)
                    {
                        $err_detail_txt.= " | {$detail['type']} {$detail['code']}:  {$detail['msg']}";
                    }
                }

                endProcess($step, $row_start[$step], "fail", "Publish ".$series['seriesname']." result", "", "FAILED");
                u_writelog("FAILED to update ".$series['seriesname']." results file [$err_detail_txt]", $eventid);
                $continue = true;  // should transfer what we can
                $success[3] = false;
            }

        }
    }

    // -----------------------------------------------------------------------------------
    // step 4 post race, series, inventory file to website
    // -----------------------------------------------------------------------------------
    if ($continue) // continue if previous processing hasn't causes a problem
    {
        if ($dbg) { u_writedbg("TRANSFER DATA: result_upload: {$_SESSION['result_upload']} | opt_upload: {$series['opt_upload']}
                   | result_status: {$args['result_status']} | result_transfer_protocol: {$_SESSION['result_transfer_protocol']}",
                  __FILE__,__FUNCTION__,__LINE__); }

        $step++;
        $result_year = date("Y", strtotime($event['event_date']));

        // first check if the race is NOT part of a (primary) series - if so then set series upload option to a default setting of true
        $series = $event_o->event_in_series($eventid);
        if (!$series) { $series['opt_upload'] = true; }

        // NO results files will be transferred if any of the following is true
        //    a) OOD has embargoed the results upload,
        //    b) the series upload flag is not set in t_series,
        //    c) the system wide result_upload parameter is set to 'none',
        //    d) the results are not complete
        if ($args['result_status'] == "embargoed"
            OR !$series['opt_upload']
            OR $_SESSION['result_upload'] == "none"
            OR $event['event_status'] == "running" OR $event['event_status'] == "selected")
        {

            // get relevant error message
            $txt = "Transfer not requested ";
            do {
                if ($args['result_status'] == "embargoed") {
                    $txt = "Not transferred - race result is embargoed";
                    break;
                }
                if (!$series['opt_upload']) {
                    $txt = "Not transferred - series is configured not to upload results";
                    break;
                }
                if ($_SESSION['result_upload'] == "none") {
                    $txt = "Not transferred - system is configured not to upload results";
                    break;
                }
                if ($event['event_status'] == "running" OR $event['event_status'] == "selected") {
                    $txt = "Not transferred - race is not complete";
                    break;
                }
            } while (0);

            sleep(1);
            endProcess($step, $row_start[$step], "warning", "Transfer files to website", "", $txt);
            u_writelog("FILE TRANSFER - $txt", $eventid);
        }
        else
        {
            startProcess($step, $row_start[$step], "Transferring files to website ... ", "warning");

            // create inventory
            $inventory = process_inventory($result_year, $system_info);

            if ($inventory)    // if inventory created successfully then proceed
            {
                u_writelog("FILE TRANSFER - results inventory created", $eventid);

                // inventory file into array
                $invdata = json_decode(file_get_contents($inventory['path']."/".$inventory['filename']), true);

                // create file list for transfer ( upload not done or out of date and not embargoed)
                $files = array();
                foreach($invdata['events'] as $id=>$invevent)
                {
                    foreach($invevent['resultsfiles'] as $k=>$file)
                    {
                        // check if file needs uploading
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
                    if ($_SESSION['result_transfer_protocol'] == "network")
                    {
                        empty($_SESSION["result_public_path"]) ? $target_path = "" : $target_path = $_SESSION["result_public_path"];
                        empty($_SESSION["result_public_url"]) ? $target_url = "" : $target_url = $_SESSION["result_public_url"];

                        if (empty($target_path) or empty($target_url))
                        {
                            sleep(1);
                            endProcess($step, $row_start[$step], "fail", "Transfer files to website", "", "results location for website not configured ");
                            u_writelog("FILE TRANSFER - results location for website not configure [{$_SESSION['result_public_path']}]", $eventid);
                        }
                        else
                        {
                            $status = process_transfer_network($files);
                        }

                    }
                    elseif ($_SESSION['result_transfer_protocol'] == "ftp")
                    {
                        // FIXME
                        sleep(1);
                        endProcess($step, $row_start[$step], "fail", "Transfer files to website", "", "ftp transfer not implemented yet");
                        u_writelog("FILE TRANSFER - transfer protocol option not implemented [{$_SESSION['result_transfer_protocol']}]", $eventid);
                    }
                    elseif ($_SESSION['result_transfer_protocol'] == "sftp")
                    {
                        $ftp = array("protocol"=>$_SESSION["ftp_protocol"], "server"=> $_SESSION["ftp_server"],
                                     "user"=> $_SESSION["ftp_user"], "pwd"=> $_SESSION["ftp_pwd"]);

                        if (empty($ftp['server']) or empty($ftp['user']) or empty($ftp['pwd']))
                        {
                            sleep(1);
                            endProcess($step, $row_start[$step], "fail", "Transfer files to website", "", "Transfer software not configured correctly");
                            u_writelog("FILE TRANSFER - ftp connection details for website not configured correctly [{$ftp['protocol']}|{$ftp['server']}|{$ftp['user']}|{$ftp['pwd']}]", $eventid);
                        }
                        else
                        {
                            $status = process_transfer_sftp($files, $ftp);
                        }
                    }
                    else
                    {
                        sleep(1);
                        endProcess($step, $row_start[$step], "fail", "Transfer files to website", "", "Transfer software not configured correctly");
                        u_writelog("FILE TRANSFER - transfer protocol option not recognised [{$_SESSION['result_transfer_protocol']}]", $eventid);
                    }
                }

                // report on transfer process
                $msg_type = "fail";
                $txt = $status['num_files']." of $num_for_transfer results files uploaded";
                if ($status['result'] and $status['complete'] )
                {
                    $msg_type = "success";
                }
                elseif ($status['result'])
                {
                    $msg_type = "warning";
                }

                $detail_txt = "";
                //u_writedbg("file transfer result: ".print_r($status['log'],true), __CLASS__, __FUNCTION__, __LINE__);
                foreach ($status['log'] as $file)
                {
                    $detail_txt.= "$file\n";
                }

                sleep(1);
                endProcess($step, $row_start[$step], $msg_type, "Transferred files to website", "", $txt);
                u_writelog("FILE TRANSFER - $txt\n$detail_txt", $eventid);
            }
            else
            {
                sleep(1);
                endProcess($step, $row_start[$step], "fail", "Transfer files to website", "", "Unable to create list of files to transfer ");
                u_writelog("FILE TRANSFER - results inventory NOT created", $eventid);
            }
        }
    }


    // -----------------------------------------------------------------------------------
    // step 5 report end status
    // -----------------------------------------------------------------------------------
    $continue ? $_SESSION["e_$eventid"]['result_publish'] = true : $_SESSION["e_$eventid"]['result_publish'] = false;
    $step++;
    echo $tmpl_o->get_template("process_footer", array("top" => "{$row_start[$step]}"), array("complete" => $success, "step" => $step));
    ob_flush();
    flush();
}
else
{
    u_exitnicely($scriptname, $eventid,"$page page - pagestate value not recognised [{$_REQUEST['pagestate']}",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

echo close_page();

/* ----------- FUNCTIONS ---------------------------------------------------------------- */


function startProcess($step, $pos, $text, $progress)
{
    $pbufr = "";
    if ($progress)
    {
        $pbufr = <<<EOT
        <div class="progress" >
             <div class="progress-bar progress-bar-$progress progress-bar-striped active" role="progressbar" style="width: 100%"></div>
        </div>
EOT;
    }

    echo <<<EOT
        <div class="row" style='background-color= #ECF0F1; width: 90%; position: absolute; top: {$pos}px;'>
            <div class="col-sm-5 col-sm-offset-1" style="padding-top: 15px; padding-bottom: 5px">
               <h4 style="color: steelblue"><i>$text</i></h4>
            </div> 
            <div class="col-sm-5" style="padding-top: 15px; padding-bottom: 5px">
                $pbufr
            </div>   
        </div>
EOT;
    ob_flush();
    flush();
}

function endProcess($step, $pos, $status, $text, $link="", $end_text="")
{
    if ($status == "success")
    {
        $glyph   = "glyphicon glyphicon-ok";
        $g_style = "color: green";
    }
    elseif ($status == "fail")
    {
        $glyph   = "glyphicon glyphicon-remove";
        $g_style = "color: red";
    }
    elseif ($status == "na")
    {
        $glyph   = "glyphicon glyphicon-minus";
        $g_style = "color: green";
    }
    else // warning
    {
        $glyph   = "glyphicon glyphicon-alert";
        $g_style = "color: orange";
    }


    if ($link)
    {
        $result_bufr = <<<EOT
        <a href="$link" id="printrace" type="button" class="btn btn-info btn-md" target="_BLANK" >
           <span class="glyphicon glyphicon-search"></span>
            $end_text
        </a>
EOT;
    }
    else
    {
        $result_bufr = "<h4>$end_text</h4>";
    }

    echo <<<EOT
        <div class="row" style='background-color: white; width: 90%; position: absolute; top: {$pos}px; padding-bottom: 10px; border-bottom: solid 1pt slategrey;'>
            <div class="col-sm-5 col-sm-offset-1">
               <h4 style="color: steelblue; vertical-align: top;"><b>$text</b></h4>
            </div> 
            <div class="col-sm-2">
               <h4><span class="$glyph" style="$g_style; vertical-align: top;" aria-hidden="true"></span></h4>
            </div>
            <div class="col-sm-4 pull-right" style="vertical-align: top;">
               $result_bufr
            </div>
        </div>
EOT;
    ob_flush();
    flush();
}

function display_demo_page($loc, $eventid)
{
    global $tmpl_o;
    // display demo message - results not published

    // generate page
    $fields = array(
        "title"      => "save [demo] results",
        "loc"        => $loc,
        "theme"      => $_SESSION['racebox_theme'],
        "stylesheet" => "./style/rm_racebox.css",
        "navbar"     => "",
        "footer"     => ""
    );

    $fields['body'] = $tmpl_o->get_template("fm_publish_demo", array("eventid"=>"$eventid"), array());

    return $tmpl_o->get_template("basic_page", $fields);
}

function display_warnings_page($loc, $eventid)
{
    global $event_o, $tmpl_o;

    // get current wind/notes settings in case this is not the first publish
    $event = $event_o->get_event_byid($eventid);

    // generate page
    $fields = array(
        "title"      => "publish results",
        "loc"        => $loc,
        "theme"      => $_SESSION['racebox_theme'],
        "stylesheet" => "./style/rm_racebox.css",
        "navbar"     => "",
        "footer"     => "",
    );

    $fields['body'] = $tmpl_o->get_template("fm_publish_warning", array("eventid" => $eventid), array());

    return $tmpl_o->get_template("basic_page", $fields);
}

function display_options_form($loc, $eventid)
{
    global $event_o, $db_o, $tmpl_o;
    // get current wind/notes settings in case this is not the first publish
    $event = $event_o->get_event_byid($eventid);

    // generate form
    $speed_codes = $db_o->db_getsystemcodes("wind_speed");
    $dirn_codes  = $db_o->db_getsystemcodes("wind_dir");

    $form_params = array(
        "eventid"  => $eventid,
        "notes"    => $event['result_notes'],
        "wd_start" => u_selectcodelist($dirn_codes, $event['wd_start'], false),
        "wd_end"   => u_selectcodelist($dirn_codes, $event['wd_end'], false),
        "ws_start" => u_selectcodelist($speed_codes, $event['ws_start'], false),
        "ws_end"   => u_selectcodelist($speed_codes, $event['ws_end'], false)
    );
    $fields = array(
        "title"      => "publish results",
        "loc"        => $loc,
        "theme"      => $_SESSION['racebox_theme'],
        "stylesheet" => "./style/rm_racebox.css",
        "navbar"     => "",
        "body"       => $tmpl_o->get_template("fm_publish", $form_params, $event),
        "footer"     => "",
    );

    return $tmpl_o->get_template("basic_page", $fields);
}

function start_page($loc)
{
    global $tmpl_o;

    // start html page
    $fields = array(
        "title"      => "publish results",
        "loc"        => $loc,
        "theme"      => $_SESSION['racebox_theme'],
        "stylesheet" => "./style/rm_racebox.css",
        "navbar"     => "",
        "body"       => "",
        "footer"     => "",
    );

    return $tmpl_o->get_template("process_header", $fields);
}


function close_page()
{
    return "</body></html>";
}
