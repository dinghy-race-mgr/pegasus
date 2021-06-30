<?php

/* ------------------------------------------------------------
   rbx_pg_publishresults
   
   Performs all of the tasks associated with publishing the results
   
   arguments:
       eventid     id of event
       pagestate   control state for page
   
   ------------------------------------------------------------
*/

$loc        = "..";       // <--- relative path from script to top level folder
$page       = "publish";     //
$scriptname = basename(__FILE__);
require_once ("$loc/common/lib/util_lib.php");

u_initpagestart($_REQUEST['eventid'], $page, false);   // starts session and sets error reporting

// initialising language   
//include ("{$loc}/config/lang/{$_SESSION['lang']}-racebox-lang.php");

require_once ("$loc/common/classes/db_class.php");
require_once ("$loc/common/classes/template_class.php");
require_once ("$loc/common/classes/race_class.php");
require_once ("$loc/common/classes/rota_class.php");
require_once ("$loc/common/classes/event_class.php");
require_once ("$loc/common/classes/result_class.php");
require_once ("$loc/common/classes/seriesresult_class.php");

// templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php", "./templates/layouts_tm.php",
    "./templates/results_tm.php", "$loc/common/templates/race_results_tm.php", "$loc/common/templates/series_results_tm.php"));

$pagestate = $_REQUEST['pagestate'];
$eventid   = $_REQUEST['eventid'];

if (empty($pagestate) OR empty($eventid))
{
    u_exitnicely("results_publish_pg", $eventid, "errornum", "eventid or pagestate are missing");
}

$db_o    = new DB;                        // database object
$event_o = new EVENT($db_o);              // event object
$result_o  = new RESULT($db_o, $eventid); // result object

$event = $event_o->get_event_byid($eventid);


if ($pagestate == "init")    // display information collection form
{
    echo display_options_form($loc, $eventid);

    ob_flush();
    flush();      
}
// FIXME need to provide more information if there is a problem - probably a publishing log that can be accessed
elseif ($pagestate == "process")    // run through process workflow
{
    $continue = true;
    $success = array(1=>true, 2=>true, 3=>true, 4=>true );
    u_writelog("Results processing started: ", $eventid);

    $row_start = array ("1" => 5, "2" => 15, "3" => 30, "4" => 45, "5" => 65 );  // % of page height for process display
    $transfer_files = array(); // contains files to be transferred

    // deal with parameters from form
    empty($_REQUEST['result_status']) ? $result_status = "FINAL" : $result_status = strtoupper($_REQUEST['result_status']);
    isset($_REQUEST['include_club']) ? $include_club = true : $include_club = false;
    empty($_REQUEST['result_notes']) ? $result_notes = "" : $result_notes = $_REQUEST['result_notes'];

    // update event with form details
    $update = $event_o->event_changedetail($eventid, array("result_notes" => $_REQUEST['result_notes'],
        "ws_start" => $_REQUEST['ws_start'], "wd_start" => $_REQUEST['wd_start'],
        "ws_end" => $_REQUEST['ws_end'], "wd_end" => $_REQUEST['wd_end'] ));
    if ($update) {u_writelog("Updated event wind details and notes", $eventid); }
    else { u_writelog("FAILED to update event wind details and notes", $eventid); }


    echo start_page($loc);
    ob_flush();
    flush();
    sleep(1);

    // -----------------------------------------------------------------------------------
    // step 1 archive data
    // -----------------------------------------------------------------------------------
    $step = 1;
    startProcess($step, $row_start[$step], "Archiving results data...", "warning");
    $result_o = new RESULT($db_o, $eventid);

    $status = process_archive();
    sleep(2);

    if ($status['copy'] and $status['archive'])
    {
        endProcess($step, $row_start[$step], "success", "Results archived ");
        u_writelog("results archived", $eventid);
        $continue = true;
    }
    else
    {
        $status['copy'] ?  $msg1 = "results copied " : $msg1 = "results copy failed ";
        $status['archive'] ?  $msg2 = "results archived" : $msg2 = "results archiving failed";

        endProcess($step, $row_start[$step], "fail", "Results archiving FAILED [$msg1 : $msg2]");
        u_writelog("FAILED to archive results [$msg1 : $msg2]", $eventid);
        $continue = false;
        $success[1] = false;
    }

    // -----------------------------------------------------------------------------------
    // step 2 create race results file
    // -----------------------------------------------------------------------------------
    if ($continue)
    {
        $step = 2;
        startProcess($step, $row_start[$step], "Creating results sheet ...", "warning");

        // create race result
        $fleet_msg = array(); // TODO this is a future use feature - displays individual notes for each fleet - currently not collected anywhere
        $status = process_result_file($loc, $include_club, $result_notes, $fleet_msg, $result_status);
        sleep(2);

        if ($status['success'])
        {
            endProcess($step, $row_start[$step], "success", "Results sheet created", $status['url'], "Click to view");
            u_writelog("race results file created", $eventid);
            $transfer_file[] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);
            $continue = true;
        }
        else
        {
            endProcess($step, $row_start[$step], "fail", "Results sheet FAILED ");
            u_writelog("FAILED to create results file", $eventid);
            $continue = false;
            $success[2] = false;
        }
    }

    // -----------------------------------------------------------------------------------
    // step 3 create series results file - if required
    // -----------------------------------------------------------------------------------
    if ($continue)    // go to next step
    {
        $step = 3;

        if (!empty($_SESSION["e_$eventid"]["ev_seriescode"]))
        {
            $series = $event_o->event_in_series($eventid);

            $opts = array(
                "inc-pagebreak" => $series['opt_pagebreak'],                                          // page break after each fleet
                "inc-codes"     => $series['opt_addcode'],                                            // include key of codes used
                "inc-club"      => $series['opt_clubname'],                                           // include club name for each competitor
                "inc-turnout"   => $series['opt_turnout'],                                            // include turnout statistics
                "race-label"    => $series['opt_racelabel'],                                          // use race number or date for labelling races
                "club-logo"     => $_SESSION['baseurl']."/config/images/club_logo.jpg",               // if set include club logo
                "styles" => $_SESSION['baseurl']."/config/style/result_{$series['opt_style']}.css"    // styles to be used
            );


            startProcess($step, $row_start[$step], "Updating series results ", "warning");

            $status = process_series_file($eventid, $opts, $series['seriescode'], $result_status);
            sleep(2);

            if ($status['success'])
            {
                endProcess($step, $row_start[$step], "success", "Series result sheet created", $status['url'], "Click to view");
                u_writelog("series results file updated", $eventid);
                $transfer_file[] = array("path" => $status['path'], "url" => $status['url'], "file" => $status['file']);
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

                endProcess($step, $row_start[$step], "fail", "Series results update FAILED ");
                u_writelog("FAILED to update series results file [$err_detail_txt]", $eventid);
                $continue = true;  // should transfer what we can
                $success[3] = false;
            }
        }
        else
        {
            sleep(1);
            endProcess($step, $row_start[$step], "na", "No series result to update ");
            u_writelog("Not part of a series - no series result file to update", $eventid);
            $continue = true;
        }
    }


    // -----------------------------------------------------------------------------------
    // step 4 post race, series, inventory file to website
    // -----------------------------------------------------------------------------------
    if ($continue)
    {
        $step = 4;

        if ($result_status != "embargoed")
        {
            startProcess($step, $row_start[$step], "Transferring results files to website ", "warning");

            // get inventory file name/path
            $inventory_file = $result_o->get_inventory_filename();
            $inventory_path = $_SESSION['result_path'].DIRECTORY_SEPARATOR.$inventory_file;
            $inventory_url  = $_SESSION['result_url']."/".$inventory_file;

            // create inventory
            $inventory = $result_o->create_result_inventory($inventory_path);

            if ($inventory['success'])                         // if inventory created successfully then proceed
            {
                // add inventory file to file to be transferred
                $transfer_files[] = array("path" => $inventory_path, "url" => $inventory_url,
                                          "file" => $inventory_file);

                // transfer files using relevant protocol
                $status = process_transfer($transfer_files, $_SESSION['ftp_protocol']);
                sleep(2);

                if ($status['success'])
                {
                    endProcess($step, $row_start[$step], "success", "Results files transferred");
                    $continue = true;
                }
                else
                {
                    endProcess($step, $row_start[$step], "fail", "Results file transfer FAILED ");
                    $continue = false;
                    $success[4] = false;
                }
            }
            else                                            // report issue with inventory creation
            {
                endProcess($step, $row_start[$step], "fail", "Results inventory file transfer FAILED");
                $continue = false;
                $success[4] = false;
            }
        }
        else
        {
            sleep(1);
            endProcess($step, $row_start[$step], "warning", "Transfer to website not specified ");
            $continue = true;
        }
    }

    // -----------------------------------------------------------------------------------
    // step 5 report end status
    // -----------------------------------------------------------------------------------
    echo $tmpl_o->get_template("process_footer", array("top" => "{$row_start[5]}"), array("complete" => $success, "step" => $step));
    ob_flush();
    flush();
}
else
{
    u_exitnicely("results_publish_pg", $eventid, "errornum", "pagestate not recognised [pagestate]");
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
        <div class="row" style='background-color= #ECF0F1; width: 90%; position: absolute; top: $pos%;'>
            <div class="col-sm-5 col-sm-offset-1">
               <h4 style="color: steelblue">$text</h4>
            </div> 
            <div class="col-sm-5" style="padding-top: 15px; padding-bottom: 5px">
                $pbufr
            </div>   
        </div>
EOT;
    ob_flush();
    flush();
}

function endProcess($step, $pos, $status, $text, $link="", $link_text="")
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
        $glyph   = "glyphicon glyphicon-ok";
        $g_style = "color: green";
    }
    else // warning
    {
        $glyph   = "glyphicon glyphicon-warning-sign";
        $g_style = "color: orange";
    }

    $link_bufr = "<p>&nbsp;</p>";
    if ($link)
    {
        $link_bufr = <<<EOT
        <a href="$link" id="printrace" type="button" class="btn btn-info btn-md margin-top-10" target="_BLANK" >
           <span class="glyphicon glyphicon-search"></span>
            $link_text
        </a>
EOT;
    }

    echo <<<EOT
        <div class="row" style='background-color: white; width: 90%; position: absolute; top: $pos%; padding-bottom: 10px; border-bottom: solid 1pt slategrey;'>
            <div class="col-sm-5 col-sm-offset-1">
               <h4 style="color: steelblue; vertical-align: top;">$text</h4>
            </div> 
            <div class="col-sm-2">
               <h4><span class="$glyph" style="$g_style; vertical-align: top;" aria-hidden="true"></span></h4>
            </div>
            <div class="col-sm-4 pull-right" style="vertical-align: top;">
               $link_bufr
            </div>
        </div>
EOT;
    ob_flush();
    flush();
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
        "wd_start" => u_selectcodelist($dirn_codes, $event['wd_start']),
        "wd_end"   => u_selectcodelist($dirn_codes, $event['wd_end']),
        "ws_start" => u_selectcodelist($speed_codes, $event['ws_start']),
        "ws_end"   => u_selectcodelist($speed_codes, $event['ws_end'])
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


function process_archive()
{
    global $result_o;

    $status['copy']    = $result_o->race_copy_results();      // copy data from t_race to t_results
    $status['archive'] = $result_o->race_copy_archive();      // copy data from t_race/t_lap/t_finish to a_<tables>>

    return $status;
}


function process_result_file($loc, $result_status, $include_club, $result_notes, $fleet_msg)
{
    global $result_o;

    $race_bufr = $result_o->render_race_result($loc, $include_club, $result_notes, $fleet_msg, $result_status);

    // get file path and url
    $race_file = $result_o->get_race_filename();
    $race_path = $_SESSION['result_path'].DIRECTORY_SEPARATOR."races".DIRECTORY_SEPARATOR.$race_file;
    $race_url  = $_SESSION['result_url']."/races/".$race_file;

    // write html to file
    $num_bytes = file_put_contents($race_path, $race_bufr);

    // set up return to main process
    if ($num_bytes === FALSE)
    {
        $status = array('success' => false, 'err' => "error creating file [$race_path]");
    }
    elseif ($num_bytes == 0)
    {
        $status = array('success' => false, 'err' => "file empty [$race_path]");
    }
    else
    {
        $status = array('success' => true, 'err' => "file created [$race_path]", 'url' => $race_url,
                        'path' => $race_path, 'file' => $race_file);

        // add result file entry to t_resultfile
        if ($result_status != "embargoed")
        {
            $listed = $result_o->add_result_file(array(
                "result_status" => $result_status,
                "result_type"   => "race",
                "result_format" => "htm",
                "result_path"   => $race_url,
                "result_notes"  => $result_notes ));
            if (!$listed) { $status= array('success' => false, 'err' => "file created but not added to results list [$race_path]"); }
        }

    }
    return $status;
}


function process_series_file($eventid, $opts, $series_code, $series_status)
{
    global $result_o;
    global $db_o;
    global $tmpl_o;

    $series_o = new SERIES_RESULT($db_o, $series_code, $opts, false);

    // set data for series result
    $err = $series_o->set_series_data();
    if (!$err)
    {
        // calculate series result
        $err = $series_o->calc_series_result();

        if (!$err)
        {
            // render series result into html
            $sys_detail = array(
                "sys_name"      => $_SESSION['sys_name'],
                "sys_release"   => $_SESSION['sys_release'],
                "sys_version"   => $_SESSION['sys_version'],
                "sys_copyright" => $_SESSION['sys_copyright'],
                "sys_website"   => $_SESSION['sys_website'],
            );

            $htm = $series_o->series_render_styled($sys_detail,  $series_status, file_get_contents($opts['styles']));
        }
        else
        {
            $err_detail = $series_o->get_err();
        }
    }
    else
    {
        $err_detail = $series_o->get_err();
    }

    // FIXME output error summary

    if (!$err and !empty($htm))
    {
        // get file name, path and url for series file
        $series_file = $series_o->get_series_filename();
        $series_path = $_SESSION['result_path'].DIRECTORY_SEPARATOR."series".DIRECTORY_SEPARATOR.$series_file;
        $series_url  = $_SESSION['result_url']."/series/".$series_file;

        // output htm to file
        $num_bytes = file_put_contents($series_path, $htm);

        if ($num_bytes === FALSE)
        {
            $status = array('success' => false, 'err' => "error creating series file [$series_path]");
        }
        elseif ($num_bytes == 0)
        {
            $status = array('success' => false, 'err' => "series file empty [$series_path]");
        }
        else
        {
            $status = array('success' => true, 'err' => "series file created [$series_path]", 'url' => $series_url,
                'path' => $series_path, 'file' => $series_file);

            if ($series_status != "embargoed")
            {
                // add series result file entry to t_resultfile
                $resultfile_arr = array(
                    "result_status" => $series_status,
                    "result_type"   => "series",
                    "result_format" => "htm",
                    "result_path"   => $series_url,
                    "result_notes"  => "results file created by raceManager"
                );
                $listed = $result_o->add_result_file($resultfile_arr);
                if (!$listed) { $status = array('success' => false, 'err' => "file created but not added to results list [$series_path]"); }
            }
        }
    }
    else
    {
        // return calculation error with
        $status = array('success' => false, 'err' => "series calculation failed", "detail" => $err_detail);
    }

    return $status;
}


function process_transfer($files, $protocol)
{
    global $loc;
    $ftp_env = array(
        "server" => $_SESSION['ftp_server'],
        "user"   => $_SESSION['ftp_userr'],
        "pwd"    => $_SESSION['ftp_pwd'],
    );

    $status = ftpFiles($loc, $protocol, $ftp_env, $files);

    $num_files = count($files);
    $file_count = 0;
    foreach ($files as $key => $file)
        if ($status['file'][$key])
        {
            $file_count++;
        }

    $status['success'] = "none";
    if ($file_count == $num_files)
    {
        $status['success'] = "all";
    }
    elseif($file_count > 0)
    {
        $status['success'] = "some";
    }

    return $status;
}

