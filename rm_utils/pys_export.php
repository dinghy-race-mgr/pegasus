<?php
/*
pys_export.php

Creates an export race data file for input to the RYA's PYS system in either csv or xml format.

The csv format files need to be converted to xlsx and the sheet named Front Sheet.

The processing is controlled by a json command file which sits in the data/pyscheme directory

The processing generates a log file in data/pyscheme/logs directory

 */

$loc        = "..";
$page       = "pys_export";     //
$scriptname = basename(__FILE__);
$today      = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";
$logging    = true;
$reporting  = true;

require_once ("{$loc}/common/lib/util_lib.php");

// arguments
$pagestate    = u_checkarg("pagestate", "set", "", "init");
$control_file = u_checkarg("control-file", "set", "", "");
$file_type    = u_checkarg("file-type", "set", "", "xml");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

// initialise session
$init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

if ($init_status)
{
    // set timezone
    if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

    // start log
    error_log(date('H:i:s')." -- rm_util PUBLISH EVENTS ------- [session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

    // set initialisation flag
    $_SESSION['util_app_init'] = true;
}
else
{
    u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/pys_class.php");

// connect to database
$db_o = new DB();
foreach ($db_o->db_getinivalues(false) as $data) { $_SESSION["{$data['parameter']}"] = $data['value']; }

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php", "./templates/pys_export_tm.php"));

if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

$pagefields = array(
    "loc"           => $loc,
    "theme"         => $styletheme,
    "stylesheet"    => $stylesheet,
    "title"         => "PYS-export",
    "header-left"   => $_SESSION['sys_name'],
    "header-right"  => "PYS Export",
    "body"          => "",
    "footer-left"   => "",
    "footer-center" => "",
    "footer-right"  => "",
);

$basepath = $_SESSION['basepath']."/data/pyscheme";
$baseurl = $_SESSION['baseurl']."/data/pyscheme";
$pys_o = new PYS($db_o, $basepath, $baseurl);


// argument checks
$state = 0;
$error = array();
if ($pagestate != "init" AND $pagestate != "submit")         // error pagestate not recognised
{
    $state = 2;
    $error[] = 2;
}

if ($pagestate == "submit" )                                 // error check for input arguments
{
    // check control file is not empty
    if (empty($control_file))
    {
        $state = 5;
        $error[] = 5;
    }

    // check file type is set and valid
    if ($file_type != "csv" and $file_type != "xml")
    {
        $state = 7;
        $error[] = 7;
    }

    // check target directory exists
    if (!is_dir($basepath)) {
        $mkdir = mkdir($basepath, 0777, true);
        if (!is_dir($basepath))
        {
            $state = 4;
            $error[] = 4;
        }
    }
}

// --- INIT page -------
if ($_REQUEST['pagestate'] == "init" and $state == 0)        // display user parameter selection page
{
    $formfields = array(
        "instructions" => "Processes race results to produce files which can be submitted to the RYA Portsmouth Yardstick System [https://www.pyonline.org.uk/] </br>
           <span class=' rm-text-xs'> - The races to be processed will be defined in a command file selected from the menu below - either create
           a new command file or edit an existing one.  The files should be in the data/pyscheme directory.</br>
            - links are provided to each output file produced <i>(files can also be found in your raceManager installation at data/pyscheme/{year})</i></span>",
        "script" => "pys_export.php?pagestate=submit",
    );

    $pagefields['body'] = $tmpl_o->get_template("publish_form", $formfields, array("control-files" => $pys_o->get_control_files()));
    echo $tmpl_o->get_template("basic_page", $pagefields, array() );
}

// --- SUBMIT processing page --------
elseif ($_REQUEST['pagestate'] == "submit" and $state == 0)  // process data as requested
{
    // read json command file
    $commands = $pys_o->read_control_file(basename($control_file));

    if (empty($commands) or $commands === false)
    {
        $state = 5;
        $error[] = 5;
    }
    else
    {
        $admin = $pys_o->get_admin_info();
        echo $tmpl_o->get_template("publish_results", $pagefields, array("state-error"=>false, "name"=>$admin['name'], "file"=>$control_file));

        foreach ($commands as $k => $command)
        {
            // create logfile (removing existing one)
            $logfile = $pys_o->set_log_filename($command);
            if (file_exists($logfile)) { unlink($logfile); }

            // get data output filename
            $out_filename = $pys_o->set_filename($command, $admin, $_SESSION['pys_id'], $file_type);

            // get events associated with command
            $status = $pys_o->set_events($command);
            if ($logging) { event_logging($logfile, $status); }
            $events = $pys_o->get_events();
            $num_events = count($events);

            // start logging for this command
            if ($logging) { start_logging($logfile, $command, $num_events, false, "", "", $out_filename); }

            // process each event
            $command_report = array(
                "description"      => $command['description'],
                "mode"             => $command['mode'],
                "attribute"        => $command['attribute'],
                "start_date"       => $command['start-date'],
                "end_date"         => $command['end-date'],
                "events_found"     => count($events),
                "events_processed" => 0,
                "races_processed"  => 0,
                "races_excluded"   => 0,
                "races_noentries"  => 0,
                "log_link"         => $pys_o->get_log_filename("url"),
                "datafile_link"    => $pys_o->get_filename("url"),
            );

            if (!empty($events))
            {
                foreach ($events as $event)
                {
                    $command_report['events_processed']++;

                    if ($logging) { error_log(PHP_EOL. date('H:i:s') . " -- PROCESSING EVENT: {$event['event_name']} {$event['event_date']} {$event['event_start']} [id: {$event['id']}] " . PHP_EOL, 3, $logfile); }

                    // get name of each fleet for this event
                    $fleetnames = $pys_o->get_fleet_names($event['id']);
                    $fleet_num = count($fleetnames);

                    for ($i = 1; $i <= $fleet_num; $i++)
                    {
                        if ($logging) { error_log(date('H:i:s') . " --- FLEET $i - {$fleetnames[$i]['fleet_name']}" . PHP_EOL, 3, $logfile); }

                        // get result for this fleet
                        $fleet_count = $pys_o->set_fleet_results($event['id'], $i);

                        $checks = $pys_o->check_valid_results($event['id'], $i);
                        $included = report_fleet_checks($i, $checks, $logging, $logfile, $fleet_count);

                        // process data into required information
                        if ($included)
                        {
                            $num_records = $pys_o->process_result_data($event['id'], $i, $fleetnames[$i]['fleet_name'], $status);
                            if ($logging) { error_log(date('H:i:s') . " ---- results processed: $num_records" . PHP_EOL, 3, $logfile); }
                        }
                        else
                        {
                            if ($logging) { error_log(date('H:i:s') . " ---- results processed: fleet data invalid" . PHP_EOL, 3, $logfile); }
                        }
                    }
                }

                // create final data structure - races/fleets/entries
                $data = $pys_o->get_results();

                // output in required format
                if ($file_type == "xml") {

                    $status = output_xml_file($data, $command, $logging, $out_filename, $logfile);
                }
                else  // defaults to csv
                {
                    $status = output_csv_file($data, $logging, $out_filename, $logfile);
                }

                // output report details for command + link to logging + link to output file
                echo $tmpl_o->get_template("command_report", $pagefields, array("command" => $command_report));

            }
            else
            {
                if ($logging) { error_log(date('H:i:s') . " -- GETTING EVENTS: failed (no events match control parameters" . PHP_EOL, 3, $logfile);}
            }
        }
    }
    echo $tmpl_o->get_template("end_report", $pagefields, array("dir" => $basepath));
}
elseif ($_REQUEST['pagestate'] == "submit" and $state > 0)  // process error state
{
    echo $tmpl_o->get_template("publish_results", $pagefields, array("state-error" => true));
    echo $tmpl_o->get_template("publish_state", array(), array("error"=>$error, "args"=>$_REQUEST));
}


function start_logging($logfile, $command, $num_events, $dateswap, $start_date, $end_date, $out_filename)
{
    error_log(date('H:i:s') . " -- start PYS processing" . PHP_EOL, 3, $logfile);
    error_log(date('H:i:s') . " -- COMMAND:" . print_r($command, true) . PHP_EOL, 3, $logfile);
    error_log(date('H:i:s') . " -- EVENTS: number of events to process - $num_events" . PHP_EOL, 3, $logfile);
    error_log(date('H:i:s') . " -- OUTPUT FILE: $out_filename" . PHP_EOL, 3, $logfile);
}

function event_logging($logfile, $status)
{
    if ($status == 0) {
        error_log(date('H:i:s') . " -- GETTING EVENTS: $status events to process" . PHP_EOL, 3, $logfile);
    } elseif ($status == -1) {
        error_log(date('H:i:s') . " -- GETTING EVENTS: failed (missing/inconsistent attributes in command file)" . PHP_EOL, 3, $logfile);
    } elseif ($status == -2) {
        error_log(date('H:i:s') . " -- GETTING EVENTS: failed (command 'mode' not recognised)" . PHP_EOL, 3, $logfile);
    } elseif ($status == -3) {
        error_log(date('H:i:s') . " -- GETTING EVENTS: failed (no events found matching command attributes)" . PHP_EOL, 3, $logfile);
    }
}

function report_fleet_checks($fleetnum, $checks, $logging, $logfile, $fleet_count)
{
    global $command_report;

    $check_txt = "";
    $included = true;
    foreach ($checks as $k => $check) { if (!$check['result']) { $check_txt .= "- check $k fail [{$check['msg']}] "; }}

    if (empty($check_txt))
    {
        $command_report['races_processed']++;
        if ($logging) { error_log(date('H:i:s') . " ---- results validity: results OK for inclusion ($fleet_count entries)" . PHP_EOL, 3, $logfile); }
    }
    else
    {
        $command_report['races_excluded']++;
        $included = false;
        $check_txt = "Fleet $fleetnum: " . $check_txt . "<br>";
        if ($logging) { error_log(date('H:i:s') . " ---- results validity: $check_txt" . PHP_EOL, 3, $logfile); }
    }

    return $included;
}

function output_csv_file($data, $logging, $file, $logfile)
{
    global $pys_o;

    $status = $pys_o->output_csv($data);
    if ($logging) {
        if ($status >= 0)
        {
            error_log(PHP_EOL.date('H:i:s') . " ---- results output: $file" . PHP_EOL, 3, $logfile);
        }
        else
        {
            if ($status == -1) {
                $problem = " opening output file";
            } elseif ($status == -2) {
                $problem = " writing column labels";
            } elseif ($status == -3) {
                $problem = " writing data rows";
            }
            error_log(date('H:i:s') . " ---- results output: failed (problem $problem)" . PHP_EOL, 3, $logfile);
        }
    }
    return $status;
}

function output_xml_file($data, $command, $logging, $file, $logfile)
{
    global $pys_o;
    global $tmpl_o;

    $params = array(
        "data" => $data,
        "pys_id" => $_SESSION['pys_id'],
        "club" => $_SESSION['clubname'],
        "eventid" => $command['attribute'],
        "eventname" => $command['description']
    );

    $xml = $tmpl_o->get_template("output_xml", array(), $params);
    $status = $pys_o->output_xml($xml);

    if ($logging)
    {
        if ($status >= 0)
        {
            error_log(date('H:i:s') . " ---- results output: $file" . PHP_EOL, 3, $logfile);
        }
        else
        {
            if ($status == -1) {
                $problem = " opening output file";
            } elseif ($status == -2) {
                $problem = " writing xml data to file";
            } elseif ($status == -3) {
                $problem = " zero length xml file";
            }
            error_log(date('H:i:s') . " ---- results output: failed (problem $problem)" . PHP_EOL, 3, $logfile);
        }
    }

    return $status;
}



