<?php
/* ----------------------------------------------------------------------------------------------
   db_import.php

   generic import script to handle racemanager import of data from external sources


*/

$loc  = "..";
$page = "import";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";
$validtypes = array("class", "event", "rota", "competitor", "tide");

require_once ("{$loc}/common/lib/util_lib.php");

session_start();

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
        error_log(date('H:i:s')." -- IMPORT --------------------".PHP_EOL, 3, $_SESSION['syslog']);

        // set initialisation flag
        $_SESSION['util_app_init'] = true;
    }
    else
    {
        u_exitnicely($scriptname, 0, "initialisation failure", "one or more problems with import initialisation");
    }
}

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/import_class.php");

// connect to database
$db_o = new DB();

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php", "./templates/import_tm.php"));

$_SESSION['pagefields'] = array(
    "loc" => $loc,
    "theme" => $styletheme,
    "stylesheet" => $stylesheet,
    "title" => "Import",
    "header-left" => "raceManager Import",
    "header-right" => "EVENTS",
    "body" => "",
    "footer-left" => "",
    "footer-center" => "",
    "footer-right" => "",
);

// check type of import
$type_opts = get_typeoptions(strtolower(strtolower($_REQUEST['importtype'])));
if (empty($type_opts))
{
    error_log(date('H:i:s')." FAIL - import type not known ".PHP_EOL, 3, $_SESSION['syslog']);
    $error = array(
        "error" => "Import type not known",
        "detail"=> "Selected type of import was - {$_REQUEST['importtype']} - [$scriptname] ",
        "action"=> "Please check with your system administrator");
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error);
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
    exit();
}
else
{
    require_once  ("{$loc}/common/classes/{$type_opts['class']}.php");
    // establish default page content
    $_SESSION['pagefields']['header-right'] = strtoupper($type_opts['title']);
    $_SESSION['pagefields']['instructions'] = $type_opts['instructions'];
    $_SESSION['pagefields']['import-type']  = $type_opts['type'];
    $_SESSION['pagefields']['import-title'] = ucwords($type_opts['title']);
    $_SESSION['pagefields']['file-types']   = $type_opts['files'];
}

if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

/* ------------ file selection page ---------------------------------------------*/

if ($_REQUEST['pagestate'] == "init")
{
    // present form to select csv file for processing (general template)
    $_SESSION['pagefields']['body'] =  $tmpl_o->get_template("upload_import_file", $_SESSION['pagefields']);

    // render page
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
}


/* ------------ submit page ---------------------------------------------*/

elseif (strtolower($_REQUEST['pagestate']) == "submit")
{

    $num_before = 0;
    $num_after = 0;
    $params = array("file_status" => false, "read_status" => false, "data_status" => false, "import_status" => false);

    $csv_o = new IMPORT_CSV($db_o, $type_opts['fieldmap']);

    $params['file_status'] = $csv_o->check_importfile($_FILES);

    if ($params['file_status'])                                          // no file errors
    {
        $params['read_status'] = $csv_o->read_importdata();

        if ($params['read_status'])                                      // no read errors
        {
            $import = $csv_o->get_importdata();
            $import_ref = array();
            $data_error = custom_validation($type_opts);
            if (empty($data_error)) { $params['data_status'] = true; }

            if ($params['data_status'])                                   // no errors in data
            {
                $csv_o->put_importdata($import);        // set data to be imported
                $csv_o->put_importref($import_ref);     // set info to determine update/insert/delete

                $_SESSION['pagefields']['records-before'] = count_records($_REQUEST['importtype'], $db_o);

                // create recovery copy table and file
                $bkup_file = $db_o->db_table_to_file("$loc/tmp/db_backup", $type_opts['table']);
                $bkup_table = $db_o->db_table_to_temptable($type_opts['table']);

                // import (and delete any original records marked for deletion)
                $params['delete'] = "";
                $num_deleted = 0;
                foreach ($import_ref as $i=>$record)
                {
                    if (!empty($record['delete']))
                    {
                        foreach ($record['delete'] as $key)
                        {
                            $num = del_records($type_opts, $key);
                            $num_deleted = $num_deleted + $num;
                            if ($num > 0) { $params['delete'].= "$num events on $key, "; }
                        }
                    }
                }
                $params['import_status'] = $csv_o->import_data($type_opts['table'], $type_opts['truncate'], $type_opts['update']);
                $_SESSION['pagefields']['rows-in-file'] = $csv_o->get_numimports();

                // report
                $changes = $csv_o->get_import_info();
                $params = array_merge($params, $changes);            // adds inserts and update details to params

                $fail_line = $csv_o->get_fail_line();
                if ($fail_line == 0)    // no errors in import - log success
                {
                    error_log(date('H:i:s') . " -- Data import: {$type_opts['title']} (table: {$type_opts['table']})
                           \n INSERTS:\n{$params['insert']}\n UPDATES:\n{$params['update']}\n" . PHP_EOL,
                        3, $_SESSION['syslog']);
                    $params['success'] = true;
                }
                else                     // errors - log fail
                {
                    $_SESSION['pagefields']['import-problems'] = "Backup file: $bkup_file";

                    error_log(date('H:i:s') . " -- Data import: {$type_opts['title']} (table: {$type_opts['table']})
                           -- FAILED\n Failed on line $fail_line\nINSERTS:\n{$params['insert']}\n
                           UPDATES:\n{$params['update']}\n" . PHP_EOL, 3, $_SESSION['syslog']);
                    $params['success'] = false;
                }
                $_SESSION['pagefields']['records-after'] = count_records($_REQUEST['importtype'], $db_o);
            }
            else
            {
                $_SESSION['pagefields']['data-problems'] = format_error($data_error, 10);
                $params['success'] = false;
            }
        }
        else
        {
            $read_error = $csv_o->get_data_info();
            $_SESSION['pagefields']['read-problems'] = format_error($read_error, 10);
            $params['success'] = false;
        }
    }
    else
    {
        $_SESSION['pagefields']['file-problems'] = $csv_o->get_file_val();
        $params['success'] = false;
    }

// render page
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("submit_import", $_SESSION['pagefields'], $params);
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);

}

function format_error($errors, $err_limit)
{
    $err_count = 0;
    $error_msg = "";
    foreach ($errors as $line => $error)
    {
        $err_count++;
        if ($err_count > $err_limit)
        {
            $error_msg .= " --- truncated error report ---<br>";
            break;
        }
        $error_msg .= "Row $line: " . rtrim($error, "; ") . "<br>";
    }
    return $error_msg;
}


function get_typeoptions($importtype)
{
    $opts = array();
    if ($importtype == "class")
    {
        $opts = array(
            "type" => $importtype,
            "title" => "classes",
            "files" => ".csv",
            "table" => "t_class",
            "class" => "boat_class",
            "update" => true,
            "truncate" => false,
            "fieldmap" => array(
                "classname" => 'classname',
                "nat_py" => 'nat_py',
                "local_py" => 'local_py',
                "category" => 'category',
                "rig" => 'rig',
                "crew" => 'crew',
                "spinnaker" => 'spinnaker',
                "engine" => 'engine',
                "keel" => 'keel',
                "popular" => 'popular',
                "info" => 'info'
            ),
            "instructions" => "Use the <code>import_class.csv</code> template file in the <code>install/import_templates</code> 
                               directory or a classes export from your current raceManager database as a starting point.  Add 
                               new classes, or change details of existing classes in the csv file and then import it here.",
        );
    }
    elseif ($importtype == "tide")
    {
        $opts = array(
            "type" => $importtype,
            "title" => "tides",
            "files" => ".csv",
            "table" => "t_tide",
            "class" => "tide_class",
            "update" => true,
            "truncate" => false,
            "fieldmap" => array(
                "date" => 'date',
                "hw1_time" => 'hw1_time',
                "hw1_height" => 'hw1_height',
                "hw2_time" => 'hw2_time',
                "hw2_height" => 'hw2_height',
                "time_reference" => 'time_reference',
                "height_units" => 'height_units'
            ),
            "instructions" => "Use the <code>import_tide.csv</code> template file in the <code>install/import_templates</code> 
                               directory to detail high water times and heights for each day.  The data imported from the file will create
                               new tide records if no record is in the database for hat day or if the data already exists will update it"
            );
    }
    elseif ($importtype == "rota")
    {
        $opts = array(
            "type" => $importtype,
            "title" => "rotas",
            "files" => ".csv",
            "table" => "t_rotamember",
            "class" => "rota_class",
            "update" => false,
            "truncate" => true,
            "fieldmap" => array(
                "firstname"  => 'firstname',
                "familyname" => 'surname',
                "rota"       => 'rota',
                "phone"      => 'phone',
                "email"      => 'email',
                "note"       => 'note',
                "partner"    => 'partner',
                "memberid"   => 'memberid'
            ),
            "instructions" => "Use the <code>import_rota.csv</code> template file in the <code>install/import_templates</code> 
                               directory or a rota export from your current raceManager database as a starting point.  Add 
                               new members, or change details for existing members in the csv file and then import it here.
                               This script will <b>remove all of the current rota information</b> and replace it with the information you import",
        );
    }
    elseif ($importtype == "event")
    {
        $opts = array(
            "type" => $importtype,
            "title" => "events",
            "files" => ".csv",
            "table" => "t_event",
            "class" => "event_class",
            "update" => true,
            "truncate" => false,

            "fieldmap" => array(
                "id"           => 'id',
                "event_date"   => 'event_date',
                "event_start"  => 'event_start',
                "event_name"   => 'event_name',
                "series_code"  => 'series_code',
                "event_type"   => 'event_type',
                "event_format" => 'event_format',
                "event_entry"  => 'event_entry',
                "event_open"   => 'event_open',
                "tide_time"    => 'tide_time',
                "tide_height"  => 'tide_height',
                "event_notes"  => 'event_notes',
                "weblink"      => "weblink"
            ),
            "instructions" => "Use the <code>import_event.csv</code> template file in the <code>install/import_templates</code> 
                               directory.  Create events list from scratch or use an events export from programme 
                               generator script as a starting point.  Add new events, or change details of existing events 
                               in the csv file and then import it here.",
        );
    }
    return $opts;
}

function del_records($opts, $key)
{
    global $db_o;
    $num_deleted = 0;
    if ($opts['type'] == "event")
    {
        $event_o = new EVENT($db_o);
        $events = $event_o->get_events_bydate($key, "live", "");
        foreach ($events as $event)
        {
            $del = $event_o->event_delete($event['id']);
            if ($del) { $num_deleted++; }
        }
    }
    return $num_deleted;
}

function custom_validation($opts)
{
    global $import;

    $i = 1;
    $error = array();

    $expected_fields = count($opts['fieldmap']);
    foreach($import as $key=>$row)
    {
        $i++;
        $func = "val_{$opts['type']}";
        $error = $func($i, $key, $row, $opts['table'], $expected_fields);
    }

    return $error;
}

function val_class($i, $key, $row, $table, $fields)
{
    global $import;
    global $import_ref;
    global $error;
    $db_o = new DB;
    $boat_o = new BOAT($db_o);

    // check for existence
    $import_ref[$i]['ref']    = $row['classname'];
    $rs_class = $boat_o->boat_classexists($row['classname'], false);
    if ($rs_class)
    {
        $import_ref[$i]['exists'] = true;
        $import_ref[$i]['id'] = $rs_class['id'];
    }

    // class name - check not empty
    if (empty($row['classname']))   // required
    {
      $error[$i].= "class name must be supplied; ";
    }
    else   // and unique
    {
        $j = 1;
        foreach ($import as $row2)
        {
            $j++;
            if ($row['classname']==$row2['classname'] AND $i != $j)
            {
                $error[$i].= "class name must be unique; ";
                break;
            }
        }
    }

    // nat_py - must be provided and must be a number
    $py_range = array('options' => array( 'min_range' => 400, 'max_range' => 2000 ));
    if (empty($row['nat_py']) OR filter_var( $row['nat_py'], FILTER_VALIDATE_INT, $py_range ) == FALSE)
        { $error[$i].= "national py must be provided and must be a positive integer number; "; }

    // local_py  - if not provided set to national py
    if (empty($row['local_py']))
        { $row['local_py'] = $row['nat_py']; }
    elseif (filter_var( $row['local_py'], FILTER_VALIDATE_INT, $py_range ) == FALSE)
        { $error[$i].= "local py must be a positive integer number; ";}

    // category - must be one of set codes - change to upper case
    if (!$db_o->db_checksystemcode("class_category", $row['category']))
        { $error[$i].= "class category code is not valid; "; }

    // rig - must be one of set codes - change to upper case
    if (!$db_o->db_checksystemcode("class_rig", $row['rig']))
        { $error[$i].= "class rig code is not valid; "; }

    // crew - must be one of set codes - change to upper case
    if (!$db_o->db_checksystemcode("class_crew", $row['crew']))
        { $error[$i].= "class crew code is not valid; "; }

    // spinnaker - must be one of set codes - change to upper case
    if (!$db_o->db_checksystemcode("class_spinnaker", $row['spinnaker']))
        { $error[$i].= "class spinnaker code is not valid; "; }

    // engine - must be one of set codes - change to upper case
    if (!$db_o->db_checksystemcode("class_engine", $row['engine']))
        { $error[$i].= "class engine code is not valid; "; }

    // keel - must be one of set codes - change to upper case
    if (!$db_o->db_checksystemcode("class_keel", $row['keel']))
        { $error[$i].= "class keel code is not valid; "; }

    // popular - set to 0 if not set
    if ($row['popular']!="1") { $row['popular'] = "0"; }

    // rya_id - set to none if not set - deal with special characters
    if (empty($row['rya_id'])) { $row['rya_id'] = "none"; }

    $import[$key]['classname'] = addslashes(ucfirst($row['classname']));
    $import[$key]['nat_py']    = addslashes($row['nat_py']);
    $import[$key]['local_py']  = addslashes($row['local_py']);
    $import[$key]['category']  = addslashes(strtoupper($row['category']));
    $import[$key]['crew']      = addslashes(strtoupper($row['crew']));
    $import[$key]['rig']       = addslashes(strtoupper($row['rig']));
    $import[$key]['spinnaker'] = addslashes(strtoupper($row['spinnaker']));
    $import[$key]['engine']    = addslashes(strtoupper($row['engine']));
    $import[$key]['keel']      = addslashes(strtoupper($row['keel']));
    $import[$key]['popular']   = addslashes($row['popular']);
    $import[$key]['info']      = addslashes($row['info']);

    return $error;
}

function val_event($i, $key, $row, $table, $fields)
{
    global $import;
    global $import_ref;
    global $error;
    $db_o = new DB;
    $event_o = new EVENT($db_o);
    $racecfg = false;

    $err = "";

    // check for existence
    $import_ref[$i]['exists'] = false;
    $import_ref[$i]['ref'] = ucwords($row['event_name'])." ".$row['event_date']." ".$row['event_start'];
    if (strtolower($row['id']) == "r")  // replace all events on that day
    {
        // find all events on that day and record them for deletion
        $import_ref[$i]['delete'][] = $row['event_date'];
    }
    elseif (!empty($row['id']) AND is_numeric($row['id']) AND $row['id'] > 0)     // can only check if id is given
    {
        $detail = $event_o->get_event_byid($row['id']);
        if ($detail)
        {
            $import_ref[$i]['exists'] = true;
            $import_ref[$i]['id']     = $detail['id'];
        }
    }



    // --------------- check fields -------------------------------------------------------------------------

    // event date
    if (empty($row['event_date']) OR date('Y-m-d', strtotime($row['event_date'])) !== $row['event_date'])
    {
        $err .= "date [{$row['event_date']}] is missing, not valid or wrong format -  format yyyy-mm-dd; ";
    }

    // tide time
    if (!empty($row['tide_time']) and (date("H:i", strtotime($row['tide_time'])) !== $row['tide_time']))
    { $err .= "tide time is is not valid time and/or format hh:mm; "; }

    // do checks if event is not a "noevent"
    if ($row['event_type'] == "noevent")
    {
        $row['event_open'] = "club";
    }
    else
    {
        // event start time
        if (date("H:i", strtotime($row['event_start'])) !== $row['event_start']) {
            if (!empty($row['event_start'])) {
                $err .= "start time [{$row['event_start']}] is not a valid time and/or format hh:mm; ";
            }
        }

        // event name
        if (empty($row['event_name'])) {
            $err .= "event name missing; ";
        }

        // event type
        if (!$db_o->db_checksystemcode("event_type", strtolower($row['event_type']))) {
            $err .= "event type [{$row['event_type']}] not recognised; ";
        }

        // event access
        if (!$db_o->db_checksystemcode("event_access", strtolower($row['event_open']))) {
            $err .= "event open setting [{$row['event_open']}] not recognised; ";
        }


        if (strtolower($row['event_type']) == "racing") {
            // event format
            if (empty($row['event_format']))
            {
                $err .= "event format code missing; ";
            }
            else
            {
                $racecfg = $event_o->racecfg_findbyname($row['event_format']);
                if (!$racecfg)
                {
                    $err .= "event format [{$row['event_format']}] not recognised; ";
                }
                else
                {
                    $row['event_format'] = $racecfg['id'];  // switch event format to id
                }
            }

            // event entry
            if (!$db_o->db_checksystemcode("entry_type", strtolower($row['event_entry']))) {
                $err .= "entry type [{$row['event_entry']}] missing or not recognised; ";
            }

            // series code
            if (!empty($row['series_code'])) {
                if (!$event_o->event_getseries($row['series_code'])) {
                    $err .= "series [{$row['series_code']}] not recognised; ";
                }
            }
        }
    }

    if (!empty($row['web_link']) AND filter_var($row['web_link'], FILTER_VALIDATE_URL) === false)
        { $err .= "web link is not a valid URL; "; }

    // set output array
    unset($import[$key]['id']);
    $import[$key]['event_date']   = $row['event_date'];
    $import[$key]['event_start']  = $row['event_start'];
    $import[$key]['event_name']   = addslashes($row['event_name']);
    $import[$key]['series_code']  = addslashes(strtoupper($row['series_code']));
    $import[$key]['event_type']   = addslashes($row['event_type']);
    !$racecfg ? $import[$key]['event_format'] = 0 : $import[$key]['event_format'] = $racecfg['id'];
    $import[$key]['event_entry']  = addslashes($row['event_entry']);
    $import[$key]['event_open']   = addslashes($row['event_open']);
    $import[$key]['tide_time']    = $row['tide_time'];
    $import[$key]['tide_height']  = addslashes($row['tide_height']);
    $import[$key]['event_notes']  = addslashes($row['event_notes']);
    $import[$key]['weblink']      = addslashes($row['weblink']);

    $import[$key]['event_status'] = "scheduled";
    $import[$key]['display_code'] = "W,R,S";
    $import[$key]['active']       = 1;
    $import[$key]['updby']        = "import";

    if (!empty($err)) { $error[$i] = $err;}
    return $error;
}

function val_rota($i, $key, $row, $table, $fields)
{
    global $import;
    global $import_ref;
    global $error;
    $db_o = new DB;

    $err = "";

    // check for existence
    $import_ref[$i]['exists'] = false;
    $import_ref[$i]['ref'] = ucwords($row['firstname']." ".$row['familyname']).
                             " [".$db_o->db_getsystemlabel("rota_type", $row['rota'])."]";
    $query  = "SELECT * FROM $table WHERE firstname = '{$row['firstname']}'"
              ." AND familyname = '{$row['familyname']}' AND rota = '{$row['rota']}'";
    $detail = $db_o->db_get_row( $query );
    if ($detail)
    {
        $import_ref[$i]['exists'] = true;
        $import_ref[$i]['id']     = $detail['id'];
    }

    if (empty($row['firstname'])) { $err .= "first name must be supplied; "; }

    if (empty($row['familyname'])) { $err .= "surname must be supplied; "; }

    if (!$db_o->db_checksystemcode("rota_type", $row['rota']))
    {
        $err .= "rota code [{$row['rota']}] is not known; ";
    }

    $import[$key]['firstname']  = addslashes(ucwords(strtolower($row['firstname'])));
    $import[$key]['familyname'] = addslashes(ucwords(strtolower($row['familyname'])));
    $import[$key]['rota']       = addslashes(strtolower($row['rota']));
    $import[$key]['phone']      = addslashes($row['phone']);
    $import[$key]['email']      = addslashes($row['email']);
    $import[$key]['note']       = addslashes($row['note']);
    $import[$key]['partner']    = addslashes(ucwords(strtolower($row['partner'])));
    $import[$key]['memberid']   = addslashes($row['memberid']);

    $import[$key]['active']     = 1;
    $import[$key]['updby']      = "import";

    if (!empty($err)) { $error[$i] = $err;}
    return $error;
}

function val_tide($i, $key, $row, $table, $fields)
{
    global $import;
    global $import_ref;
    global $error;

    $db_o = new DB;

    $err = "";

    // check number of fields
    if ($fields != count($row))
    {
        $err .= "incorrect number of fields in row";
    }

    // check for valid date and convert to required format
    if (date('Y-m-d', strtotime($row['date'])) === $row['date'])
    {
        $import_ref[$i]['exists'] = false;
        $import_ref[$i]['ref'] = $row['date'];
        $query  = "SELECT * FROM $table WHERE date = '{$row['date']}'";
        $detail = $db_o->db_get_row( $query );
        if ($detail)
        {
            $import_ref[$i]['exists'] = true;
            $import_ref[$i]['id'] = $detail['id'];
        }
    }
    else  // date in wrong format
    {
        $err .= "date [{$row['date']}] is not valid date and/or format yyyy-mm-dd; ";
    }

    if (empty($row['hw1_time']))  // check we have at least one valid hw time
    {
        $err .= "time of first HW must be supplied; ";
    }
    elseif (date("H:i", strtotime($row['hw1_time'])) !== $row['hw1_time'])
    {
        $err .= "HW 1 time [{$row['hw1_time']}] is not valid time and/or format hh:mm; ";
    }

    if (!empty($row['hw2_time']))
    {
        if (date("H:i", strtotime($row['hw2_time'])) !== $row['hw2_time'])
        {
            $err .= "HW 2 [{$row['hw2_time']}] is not valid time and/or format hh:mm; ";
        }
    }

    $import[$key]['date']           = $row['date'];
    $import[$key]['hw1_time']       = $row['hw1_time'];
    $import[$key]['hw1_height']     = addslashes($row['hw1_height']);
    empty($row['hw2_time']) ? $import[$key]['hw2_time'] = "" : $import[$key]['hw2_time'] = $row['hw2_time'];
    $import[$key]['hw2_height']     = addslashes($row['hw2_height']);
    $import[$key]['time_reference'] = addslashes(strtolower($row['time_reference']));
    $import[$key]['height_units']   = addslashes(strtolower($row['height_units']));

    if (!empty($err)) { $error[$i] = $err;}
    return $error;
}

function val_competitor($i, $key, $row, $table, $fields)
{
//    global $import;
//    global $import_ref;
//    global $error;
//    $db_o = new DB;
//    $boat_o = new BOAT($db_o);
//
//    // class - convert from name to id and check it exists
//    $classname = $row['classid'];
//    if (!empty($classname))
//    {
//        $boat = $boat_o->boat_getdetail($classname);   // convert to id from name
//        if (!empty($boat))
//        {
//            $row['classid'] = $boat['id'];
//        }
//        else
//        {
//            $error[$i] .= "class is not recognised; ";
//        }
//    }
//    else
//    {
//        $error[$i] .= "class must be specified; ";
//    }
//
//    $import_ref[$i]['exists'] = false;
//    $import_ref[$i]['ref'] = $classname." ".$row['sailnum'];
//
//    if (empty($row['id']))     // check if really a new competitor
//    {
//        $query  = "SELECT a.id, classname, helm, sailnum FROM $table as a "
//            ."JOIN t_class as b ON a.classid=b.id WHERE a.classid = {$row['classid']} "
//            ."AND a.helm = '{$row['helm']}' AND a.sailnum = '{$row['sailnum']}'";
//        $detail = $db_o->db_get_row( $query );
//        if ($detail)   // looks like it exists
//        {
//            $import_ref[$i]['exists'] = true;
//            $import_ref[$i]['id'] = $detail['id'];
//        }
//    }
//    else                       // if id is specified - check competitor exists
//    {
//        if (filter_var($row['id'],FILTER_VALIDATE_INT ) === false)   // not a valid record id
//        {
//            $error[$i] .= "if specified id must be an integer value; ";
//        }
//        else
//        {
//            $query  = "SELECT classname, helm, sailnum FROM $table as a "
//                ."JOIN t_class as b ON a.classid=b.id WHERE a.id = {$row['id']}";
//            $detail = $this->db->db_get_row( $query );
//            if ($detail)
//            {
//                $import_ref[$i]['exists'] = true;
//                $import_ref[$i]['ref'] = $detail['classname']." ".$detail['sailnum'];
//                $import_ref[$i]['id'] = $row['id'];
//            }
//            else
//            {
//                $error[$i] .= "specified id does not exist in database; ";
//            }
//        }
//    }
//
//    if (empty($row['sailnum']))
//        { $error[$i] .= "sail number must be specified; "; }
//
//    if (empty($row['helm']))
//        { $error[$i] .= "helm name must be specified; "; }
//
//    $row['club'] = ucwords($row['club']);
//    $row['club'] = str_replace("Sailing Club", "SC", $row['club']);
//    $row['club'] = str_replace("Yacht Club", "YC", $row['club']);
//
//    if (!empty($row['helm_dob']) and strtotime($row['helm_dob']) == false)
//        { $error[$i] .= "invalid date format for helm birth date; "; }
//
//    if (!empty($row['crew_dob']) and strtotime($row['crew_dob']) == false )
//        { $error[$i] .= "invalid date format for crew birth date; "; }
//
//    if (!empty($row['skill_level']))
//    {
//        if (filter_var($row['skill_level'], FILTER_VALIDATE_INT, array("options" => array("min_range"=>1, "max_range"=>5))) === false)
//            { $error[$i] .= "skill level must be an integer between 1 and 5; "; }
//    }
//
//    if (!empty($row['personal_py']))
//    {
//        if (filter_var($row['personal_py'], FILTER_VALIDATE_INT) === false)
//            { $error[$i] .= "personal PN must be an integer; "; }
//    }
//
//    if (!empty($row['flight']))
//    {
//        $row['flight'] = trim($row['flight']);
//        if (!$db_o->db_checksystemcode("flight_list", $row['flight']))
//        { $error[$i].= "flight specified is not recognised; "; }
//
//        if (filter_var($row['regular'], FILTER_VALIDATE_INT) === false)
//        { $error[$i].= "regular flag must be 1 or 0; "; }
//    }
//
//    if (!empty($row['regular']))
//    {
//        if (filter_var($row['regular'], FILTER_VALIDATE_INT, array("options" => array("min_range"=>0, "max_range"=>1))) === false)
//        { $error[$i] .= "regular flag must be either 0 or 1; "; }
//    }
//
//    if (!empty($row['grouplist']))
//    {
//        $row['grouplist'] = preg_replace("/\s*,\s*/", ",", $row['grouplist']);   // remove spaces before and after commas
//        $groups = explode(",",$row['grouplist']);
//        foreach ($groups as $group)
//        {
//            if (!$db_o->db_checksystemcode("competitor_list", $group))
//            { $error[$i].= "group [$group] not recognised; "; }
//        }
//    }
//
//    if (!empty($row['prizelist']))
//    {
//        $row['prizelist'] = preg_replace('/\s*,\s*/', ',', $row['prizelist']);   // remove spaces before and after commas
//        $prizes = explode(",",$row['prizelist']);
//        foreach ($prizes as $prize)
//        {
//            if (!$db_o->db_checksystemcode("prize_list", $prize))
//            { $error[$i].= "prize group [$prize] not recognised; "; }
//        }
//    }
//
//    unset($import[$key]['id']);
//    $import[$key]['classid']     = addslashes($row['classid']);
//    $import[$key]['sailnum']     = addslashes($row['sailnum']);
//    $import[$key]['boatnum']     = addslashes($row['sailnum']);
//    $import[$key]['boatname']    = addslashes($row['boatname']);
//    $import[$key]['club']        = addslashes($row['club']);
//    $import[$key]['helm']        = addslashes(ucwords($row['helm']));
//    if (!empty($row['helm_dob']))
//        { $import[$key]['helm_dob']    = date("Y-m-d", strtotime($row['helm_dob'])); }
//    else
//        { unset($import[$key]['helm_dob']); }
//    $import[$key]['helm_email']  = addslashes($row['helm_email']);
//    $import[$key]['crew']        = addslashes(ucwords($row['crew']));
//    if (!empty($row['crew_dob']))
//        { $import[$key]['crew_dob']    = date("Y-m-d", strtotime($row['crew_dob'])); }
//    else
//        { unset($import[$key]['crew_dob']); }
//    $import[$key]['crew_email']  = addslashes($row['crew_email']);
//    $import[$key]['skill_level'] = $row['skill_level'];
//    $import[$key]['personal_py'] = $row['personal_py'];
//    $import[$key]['flight']      = strtolower($row['flight']);
//    $import[$key]['regular']     = $row['regular'];
//    $import[$key]['prizelist']   = addslashes($row['prizelist']);
//    $import[$key]['grouplist']   = addslashes($row['grouplist']);
//    $import[$key]['memberid']    = addslashes($row['memberid']);
//
//    return $error;
}


function count_records($importtype, $db_o)
{
    $count = 0;
    if ($importtype == "class")
    {
        $rs_o = new BOAT($db_o);
        return $rs_o->boat_count(array());
    }
    elseif ($importtype == "event")
    {
        $rs_o = new EVENT($db_o);
        return $rs_o->count_events(array());
    }
    elseif ($importtype == "rota")
    {
        $rs_o = new ROTA($db_o);
        return $rs_o->rota_countmembers(array());
    }
    elseif ($importtype == "competitor")
    {
        $rs_o = new COMPETITOR($db_o);
        return $rs_o->comp_count(array());
    }
    elseif ($importtype == "tide")
    {
        $rs_o = new TIDE($db_o);
        return $rs_o->tide_count(array());
    }
    return $count;
}






