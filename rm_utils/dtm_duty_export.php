<?php

/*
*
 * dtm_duty_export.php
 *
 * script to export duties from racemanager to the dutyman csv format
 *
 * Arguments (* required)
 *    start    -   start date (yyyy-mm-dd) *
 *    end      -   end date (yyyy-mm-dd)
 *    status   -   |draft|live|both| - default is live (only published events)
 *    tide     -   |Y|N| - default is N (do not include tide in event description)
 *    cleanup  -   |Y|N| if set to Y removes old export files of this type - defaults to Y
 */

$loc  = "..";
$page = "dutyman duty export";
define('BASE', dirname(__FILE__) . '/');

require_once ("$loc/config/rm_utils_cfg.php");
$target_dir = $_SESSION['dutyman']['loc']."/";
$filename   = str_replace("date", date("YmdHi"), $_SESSION['dutyman']['duty_file']);
$filepath = $target_dir.$filename;

/******************************************************/
session_start();
$_SESSION = parse_ini_file("../config/common.ini", false);
$_SESSION['sql_debug'] = false;
$_SESSION['syslog'] = "../logs/syslogs/sys_log".date("Y_m_d").".log";
include("../common/classes/db_class.php");

$start_date  = "";
$end_date    = "";
$status      = "live";
$tide        = false;
$cleanup     = true;

$duty_instruction = array(
    "ood_p"    => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED EVENT START",
    "ood_a"    => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED EVENT START",
    "ood_c"    => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED EVENT START",
    "safety_d" => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED EVENT START",
    "safety_c" => "PLEASE ARRIVE AT LEAST AN HOUR BEFORE THE PUBLISHED EVENT START",
    "galley"   => "",
    "bar"      => "",
);

// get input parameters
$where = array();
if (key_exists("start", $_REQUEST))
{
    $start_date = date("Y-m-d", strtotime($_REQUEST['start']));
    $where[] = " event_date >= '$start_date' ";
}
else
{
    echo "ERROR! - start date for events not specified [start=YYYYMMDD]<br>";
    exit("stopping ...");
}

if (key_exists("end", $_REQUEST))
{
    $end_date = date("Y-m-d", strtotime($_REQUEST['end']));
    $where[] = " event_date <= '$end_date' ";
}

if (key_exists("status", $_REQUEST))
{
    if (strtolower($_REQUEST['status']) == "draft" OR strtolower($_REQUEST['status']) == "both")
    {
        $status = strtolower($_REQUEST['status']);
    }
}

if ($status == "live")
{
    $where[] = " active = 1 ";
}
elseif ($status == "draft")
{
    $where[] = " active = 0 ";
}

if (key_exists("tide", $_REQUEST))
{
    if (strtolower($_REQUEST['tide']) == "y")
    {
        $tide = true;
    }
}

if (key_exists("cleanup", $_REQUEST))
{
    if (strtolower($_REQUEST['cleanup']) == "n")
    {
        $cleanup = false;
    }
}


// connect to database
$db_o = new DB();
echo <<<EOT
<html>
<head>
<style>
body {
    margin-top: 20px;                           /* margin for navbar and footer */
    margin-bottom: 20px;
    font-family: Kalinga, Arial, sans-serif;    /* default font */
    background-color: #FFFFFF;
}
</style>
</head>
<body>
     Generating duty allocation details from raceManager as a csv file for import to dutyman<br><br>
	 Using database server [{$_SESSION['db_host']}/{$_SESSION['db_name']}]<br><br>
     Processing . . . (this may take a few minutes)<br><hr><br>
     <b>get the csv DUTY import file from <a href="$filepath">HERE</a></br></b>
	 <b>List of exported records:</b> 
	 <table border=0 width=80%>			 
EOT;
html_flush();


// get event records
$where_str = implode(" AND ", $where);
$sql = "SELECT * FROM t_event WHERE $where_str ORDER BY event_date ASC, event_order ASC";
$rs = $db_o->db_get_rows($sql);
$num_events = count($rs);

// get duty code map
$dutycode_map = array();
$codes = $db_o->db_getsystemcodes("rota_type");
foreach($codes as $code) { $dutycode_map["{$code['code']}"] = $code['label']; }

// get events
$event_total = 0;
$duty_total = 0;
$duty_arr = array();
foreach ($rs as $k=>$row)
{
    $event_total++;

    $id = $row['id'];
    $event_name = $row['event_name'];
    $tide ? $event_name = "$event_name [HW {$row['tide_time']} - {$row['tide_height']}m]" : $event_name = $event_name;
    $duty_date = date("d/m/Y", strtotime($row['event_date']));
    $duty_time = substr($row['event_start'], 0, 5);

    // get duties for event
    $sql = "SELECT eventid, dutycode, person, swapable  FROM t_eventduty WHERE eventid = $id 
            ORDER BY FIELD(dutycode, 'ood_p', 'ood_c', 'ood_a', 'safety_d', 'safety_c', 'galley', 'bar')";
    $duty_rs = $db_o->db_get_rows($sql);

    $duty_found = false;

    foreach ($duty_rs as $j=>$duty)
    {

        $duty_found = true;
        $duty_total++;

        // remove repeating whitespace in duty name    FIXME NOT WORKING FOR MULTIPLE FIRST NAMES
        $name = trim(preg_replace('/\s+/', ' ',$duty['person']));
        $names = explode(' ', $name);
        $num_names = count($names);
        $first_name = ucwords($names[0]);
        $last_name = ucwords($names[$num_names - 1]);

        $duty_type = $dutycode_map["{$duty['dutycode']}"];

        // check if duty member is in t_rotamember table
        $exists = check_member($name);
        $exists ? $exist_check = "" : $exist_check = "**** duty person missing ****";

        $duty['swapable'] == "1" ? $swapable = "YES" : $swapable = "NO";

        $duty_arr[] = array(
            "duty_date"  => $duty_date,
            "duty_time"  => $duty_time,
            "duty_type"  => $duty_type,
            "event"      => $event_name,
            "first_name" => $first_name,
            "last_name"  => $last_name,
            "instruction" => $duty_instruction["{$duty['dutycode']}"],
            "swappable"   => $swapable
        );

        echo <<<EOT
        <tr>
            <td>{$event_name}</td>
            <td>{$duty_date}</td>
            <td>{$duty_time}</td>
            <td>{$duty_type}</td>
            <td>{$first_name} {$last_name}</td>
            <td>swap: $swapable</td>
            <td>{$exist_check}</td>
        </tr>
EOT;
        html_flush();

    }
    if ($duty_found)
    {
        echo "<tr><td cols=6> ----------------- </td></tr>";
    }
}

echo <<<EOT
     </table>
	 <br><br>
	 Programme events processed: $event_total<br>
	 Duty allocations transferred: $duty_total<br>
EOT;
html_flush();

if ($cleanup)
{
    // delete any csv files to avoid them being cached
    foreach (GLOB($target_dir . "dutyman_duty*.csv") AS $file) { unlink($file); }
}

// now create csv file
$fp = fopen($filepath, 'wb');

$cols = array("Duty Date","Duty Time","Duty Type","Event","Swappable",
              "Reminders","Duty Notify","Duty Instructions","Duty DBID","Notes",
              "Confirmed","First Name","Last Name","Member Name","Mode","GenPswd","Date Format");

fputcsv($fp, $cols, ',');
foreach ($duty_arr as $k=>$v)
{
    $out_arr = array(
        "Duty Date"   => $v['duty_date'],
        "Duty Time"   => $v['duty_time'],
        "Duty Type"   => $v['duty_type'],
        "Event"       => $v['event'],
        "Swappable"   => $v['swappable'],
        "Reminders"   => "Yes",
        "Duty Notify" => "",
        "Duty Instructions" => $v['instruction'],
        "Duty DBID"   => "",
        "Notes"       => "",
        "Confirmed"   => "No",
        "First Name"  => $v['first_name'],
        "Last Name"   => $v['last_name'],
        "Member Name" => "",
        "Mode"        => "",
        "GenPswd"     => "",
        "Date Format" => ""
    );
    fputcsv($fp, $out_arr, ',');
}
fclose($fp);

// now output download link to file
echo "<b>get the csv DUTY import file from <a href=\"$filepath\">HERE</a></br></b>";


function html_flush()
{
    echo str_pad('',4096)."\n";
    ob_flush();
    flush();
}

function check_member($name)
{
    global $db_o;

    $exists = false;

    // search database (ctblmember should have same as webcollect
    $qname = addslashes($name);
    $sql = "SELECT * FROM t_rotamember WHERE concat(firstname,' ', familyname) LIKE '$qname%'";
    $rs = $db_o->db_get_rows($sql);

    if ($rs) { $exists = true; }

    return $exists;
}







