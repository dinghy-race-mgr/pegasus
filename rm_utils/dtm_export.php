<?php

/*
 * dtm_export.php
 *
 * script to export duties from racemanager to the dutyman csv format
 *
 * usage: dtm_duty_export.php?pagestate=init
 *
 * Arguments (* required)
 *    event_form - event form required
 *    duty_form -  duty form required
 *    start    -   start date (yyyy-mm-dd) *
 *    end      -   end date (yyyy-mm-dd)
 *    status   -   |draft|live|both| - default is live (only published events)
 *
 * Config Settings
 *    tide     -   |true|false| - default is true (include tide in event description)
 *    clean    -   |true|false| - default is true (removes old export files of this type)
 */

$loc  = "..";
$page = "dutyman duty export";
define('BASE', dirname(__FILE__) . '/');
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";
$documentation = "./documentation/dutyman_utils.pdf";

require_once ("{$loc}/common/lib/util_lib.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

// initialise session
$init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

if ($init_status)
{
    // set timezone
    if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

    // start log
    error_log(date('d-M H:i:s')." -- rm_util EXPORT TO DUTYMAN ------- [session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

    // set initialisation flag
    $_SESSION['util_app_init'] = true;
}
else
{
    u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

require_once ("$loc/common/classes/db_class.php");
require_once ("$loc/common/classes/template_class.php");

// connect to database
$db_o = new DB();

foreach ($db_o->db_getinivalues(false) as $data)
{
    $_SESSION["{$data['parameter']}"] = $data['value'];
}

// get rota types
$rota_types = $db_o->db_getsystemcodes("rota_type");
$all_rotas = array();
foreach($rota_types as $rota)
{
    $dutycode_map["{$rota['code']}"] = $rota['label'];
    $all_rotas[] = $rota['code'];
}
//echo "<pre>".print_r($dutycode_map,true)."</pre>";

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php", "./templates/dutyman_tm.php"));

// set filenames and paths
$target_dir = $_SESSION['dutyman']['loc']."/";
$duty_fn   = str_replace("date", date("YmdHi"), $_SESSION['dutyman']['duty_file']);
$duty_path   = $target_dir.$duty_fn;
$event_fn   = str_replace("date", date("YmdHi"), $_SESSION['dutyman']['event_file']);
$event_path   = $target_dir.$event_fn;


// get arguments
$pagestate = u_checkarg("pagestate", "set", "", "init");

$server_txt = "{$_SESSION['db_host']}/{$_SESSION['db_name']}";
$pagefields = array(
    "loc"           => $loc,
    "theme"         => $styletheme,
    "stylesheet"    => $stylesheet,
    "title"         => "Dutyman DUTY Export",
    "header-left"   => $_SESSION['sys_name']." <span style='font-size: 0.4em;'>[$server_txt]</span>",
    "header-right"  => "Export Duties to CSV ...",
    "body"          => "",
    "confirm"       => "Create Export",
    "footer-left"   => "",
    "footer-center" => "",
    "footer-right"  => "",
);

/* ------------ confirm run script page ---------------------------------------------*/

if (trim(strtolower($pagestate)) == "init")
{

    $fields = array(
        "instructions"  => "Creates DutyMan import CVS format files for EVENTS and/or DUTIES between specified start and end dates for selected rotas. 
                            See <a href='$documentation' target='_BLANK'>Detailed Instructions</a> for more details<br><br> 
                            These can be used to update DutyMan with new events and duty allocations when the programme is first published <br>",
        "script"        => "dtm_export.php?pagestate=submit"
    );

    $pagefields['body'] =  $tmpl_o->get_template("dtm_export_form", $fields, array("rotas" => $dutycode_map));

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields, array());
}

/* ------------ submit page ---------------------------------------------*/

elseif (trim(strtolower($pagestate)) == "submit")
{

    // get arguments for processing
    $event_file = u_checkarg("event_file", "set", "");

    $duty_file = u_checkarg("duty_file", "set", "");

    $start_date = u_checkarg("start", "set", "", "");      // start_date
    $start_date ? $start_date =  date("Y-m-d", strtotime($start_date)) : $start_date = "";

    $end_date = u_checkarg("end", "set", "", "");          // end_date
    $end_date ? $end_date = date("Y-m-d", strtotime($end_date)) : $end_date = "";

    empty($_REQUEST['rotas']) ? $rotas = array(): $rotas = $_REQUEST['rotas'];     // multiple select received as array

    $rota_list = "";
    $rota_list_quote = "";
    if ($duty_file)   // duty file requested
    {
        // handle $rota information - if "all" selected
        if (in_array("all", $rotas))
        {
            $rotas = $all_rotas;
        }

        // create lists from array
        foreach($rotas as $rota)
        {
            //$rota_list.= "$rota,";
            $rota_list.= "{$dutycode_map[$rota]}, ";
            $rota_list_quote.= "'$rota',";
        }
        $rota_list = rtrim($rota_list, ",");
        $rota_list_quote = rtrim($rota_list_quote, ",");
    }

    // check args status
    $arg_status = check_args($event_file, $duty_file, $start_date, $end_date, $rotas);

    if (!empty($arg_status))
    {
        // output report to screen
        $fields = array(
            "start"     => $start_date,
            "end"       => $end_date,
            "rotas"     => $rota_list,
            "host"      => $_SESSION['db_host'],
            "database"  => $_SESSION['db_name'],
        );


        $pagefields['body'] =  $tmpl_o->get_template("dtm_export_err", $fields,
            array("errors" => $arg_status, "duty_file" => $duty_file, "event_file" => $event_file));
        echo $tmpl_o->get_template("basic_page", $pagefields, array());
    }
    else
    {
        // get duty instructions
        $duty_instruction = get_duty_instructions();

        // get event formats information
        $ev_format_types = get_event_formats();

        // get events
        $events = get_events($start_date, $end_date);
        $num_events = count($events);

        // process events
        $event_total = 0;
        $duty_total = 0;
        $duty_arr = array();
        $event_arr = array();
        foreach ($events as $k=>$event)
        {
            $event_total++;

            $event['event_format'] ? $ev_format = $ev_format_types[$event['event_format']] : $ev_format = "none";

            $_SESSION['dutyman']['tide'] ? $event_name = "{$event['event_name']} [HW {$event['tide_time']} - {$event['tide_height']}m]" : $event_name = $event['event_name'];
            $duty_date = date("d/m/Y", strtotime($event['event_date']));
            $duty_time = substr($event['event_start'], 0, 5);

            $event_arr[] = array(
                "event"       => $event['event_name'],
                "date"        => $duty_date,
                "start"       => substr($event['event_start'], 0, 8),     // FIXME should this be dutytime
                "description" => "tide: ".substr($event['tide_time'], 0, 5)." - ".trim($event['tide_height'])."m", // FIXME is this the format I want
            );

            // get duties
            if ($duty_file)
            {
                $duty_rs = get_duties($event['id'], $rota_list_quote);

                // process duties
                foreach ($duty_rs as $j=>$duty)
                {
                    $duty_total++;

                    $name_out = u_split_name($duty['person']);

                    $duty_type = $dutycode_map["{$duty['dutycode']}"];

                    $duty['swapable'] == "1" ? $swapable = "YES" : $swapable = "NO";

                    $duty_arr[] = array(
                        "duty_date"   => $duty_date,
                        "duty_time"   => $duty_time,
                        "duty_type"   => $duty_type,
                        "event"       => $event_name,
                        "swappable"   => $swapable,
                        "reminders"   => "Yes",
                        "duty_notify" => "",
                        "duty_instructions" => $duty_instruction["{$duty['dutycode']}"],
                        "duty_dbid"   => "",
                        "notes"       => "eid={$event['id']}&start={$event['event_start']}&order={$event['event_order']}",
                        "confirmed"   => "No",
                        "first_name"  => $name_out["fn"],
                        "last_name"   => $name_out["fm"],
                        "member_name" => "",
                        "mode"        => "",
                        "genpswd"     => "",
                        "date_format" => "",
                        "exists"      => check_member(trim(preg_replace('/\s+/', ' ', $duty['person'])))
                    );
                }
            }
        }

        // delete any csv files to avoid them being cached
        $duty_file_status = 0;
        if ($duty_file)
        {
            if ($_SESSION['dutyman']['clean'])
            {
                foreach (GLOB($target_dir . "dutyman_duty*.csv") AS $file) { unlink($file); }
            }

            $duty_cols = array("Duty Date","Duty Time","Duty Type","Event","Swappable",
                "Reminders","Duty Notify","Duty Instructions","Duty DBID","Notes",
                "Confirmed","First Name","Last Name","Member Name","Mode","GenPswd","Date Format");

            $duty_file_status = create_csv_file($duty_path, $duty_cols, $duty_arr, array("exists"));
        }

        $event_file_status = 0;
        if ($event_file)
        {
            if ($_SESSION['dutyman']['clean']) {
                foreach (GLOB($target_dir . "dutyman_event*.csv") AS $file) { unlink($file); }
            }

            $event_cols = array("event", "date", "start", "description");

            $event_file_status = create_csv_file($event_path, $event_cols, $event_arr, array());
        }

        // output report to screen
        $fields = array(
            "start"     => $start_date,
            "end"       => $end_date,
            "rotas"     => $rota_list,
            "dutypath"  => $duty_path,
            "eventpath" => $event_path,
            "host"      => $_SESSION['db_host'],
            "database"  => $_SESSION['db_name'],
        );
        $params = array(
            "duty_file_status"  => $duty_file_status,
            "event_file_status" => $event_file_status,
            "duty_file"  => $duty_file,
            "event_file" => $event_file,
            "duty_data"  => $duty_arr,
            "event_data" => $event_arr,
        );

        $pagefields['body'] =  $tmpl_o->get_template("dtm_export_report", $fields, $params);  // report body
        echo $tmpl_o->get_template("basic_page", $pagefields, array());                                                 // full rendered page
    }

}

function check_args($event_file, $duty_file, $start_date, $end_date, $rotas)
{
    $arg_status = array();

    if (!$event_file and !$duty_file)
    {
        $arg_status[] = array("err" => 1, "msg" => "no output files have been selected");
    }

    if (empty($start_date) or empty($end_date))
    {
        // missing date information
        $arg_status[] = array("err" => 2, "msg" => "either the start date or end date is missing");
    }
    elseif (strtotime($start_date) >= strtotime($end_date))
    {
        // start is after end
        $arg_status[] = array("err" => 3, "msg" => "start date is after end date");
    }

    if ($duty_file and empty($rotas))
    {
        // no rotas requested
        $arg_status[] = array("err" => 4, "msg" => "no rotas have been selected");
    }

    return $arg_status;
}

function get_events($start_date, $end_date)
{
    global $db_o;

    $sql = "SELECT * FROM t_event WHERE `event_date` >= '$start_date' AND `event_date` <= '$end_date' AND `active` = 1 
                    ORDER BY `event_date` ASC, `event_order` ASC";
    //echo "<pre>$sql</pre>";
    $rs = $db_o->db_get_rows($sql);

    return $rs;
}

function get_event_formats()
{
    global $db_o;

    $sql = "SELECT * FROM t_cfgrace ORDER BY id ASC";
    $rs = $db_o->db_get_rows($sql);
    $race_formats = array();
    foreach ($rs as $row)
    {
        $race_formats[$row['id']] = $row['race_name'];
    }

    return $race_formats;
}

function get_duties($eventid, $rota_list)
{
    global $db_o;

    $sql = "SELECT `id`, `eventid`, `dutycode`, `person`, `swapable`  FROM t_eventduty WHERE `eventid` = $eventid AND `dutycode` IN ($rota_list) ORDER BY FIELD(`dutycode`, $rota_list)";
    //echo "<pre>$sql</pre>";
    $duty_rs = $db_o->db_get_rows($sql);

    return $duty_rs;
}

function html_flush()
{
    echo str_pad('',4096)."\n";
    ob_flush();
    flush();
}

//function get_name($name)   -> moved to util_lib as split_name
//{
//    // extract name into first and last name
//    // works for John Allen MBE, Fred van Tam, Sir Paul McCartney OBE, Marie Anne Beard etc.
//    $name_arr = explode(' ', $name);
//    $count = count($name_arr);
//    $last = end($name_arr);
//    if (strtolower($last) == "mbe" or strtolower($last) == "cbe" or strtolower($last) == "obe")
//    {
//        $lastname = $name_arr[$count-2]." ".$last;
//        $pointer = $count - 2;
//    }
//    else
//    {
//        $lastname = $name_arr[$count-1];
//        $pointer = $count - 1;
//    }
//    $firstname = implode(" ", array_slice($name_arr, 0, $pointer));
//
//    $name_out = array("fn"=>$firstname, "fm"=>$lastname);
//
//    return $name_out;
//}

function check_member($name)
{
    global $db_o;

    $exists = false;

    // search database (t_rotamember should have same as webcollect
    $qname = addslashes($name);
    $sql = "SELECT * FROM t_rotamember WHERE concat(firstname,' ', familyname) LIKE '$qname%'";
    $rs = $db_o->db_get_rows($sql);

    if ($rs) { $exists = true; }

    return $exists;
}

function get_duty_instructions()
{
    $duty_instruction = array(
        "ood_p"    => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED EVENT START",
        "ood_a"    => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED EVENT START",
        "ood_c"    => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED EVENT START",
        "safety_d" => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED EVENT START",
        "safety_c" => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED EVENT START",
        "galley"   => "",
        "bar"      => "",
    );

    return $duty_instruction;
}

function create_csv_file($file, $cols, $rows, $excludes = array())
{
    // FIXME - this function is used elsewhere in rm_utils and is also in util_lib

    // remove any fields not required in CSV file
    foreach ($rows as $k => $row) {
        foreach ($excludes as $exclude) {

            if (key_exists($exclude, $row)) {
                unset($rows[$k][$exclude]);
            }
        }
    }

    $status = "0";
    $fp = fopen($file, 'w');
    if (!$fp) { $status = "1"; }

    if ($fp)
    {
        $r = fputcsv($fp, $cols, ',');
        if (!$r) { $status = "2"; }

        foreach ($rows as $row)
        {
            if ($status != "0") { break; }
            $r = fputcsv($fp, $row, ',');
            if (!$r) {$status = "3"; }
        }
        fclose($fp);
    }

    return $status;
}



