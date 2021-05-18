<?php

/**
 * RM9_event_import.php
 *
 * script to export race events from racemanager and create a CSV file for import directly into - it maps
 * the race formats and entry scheme information.
 *
 * It attempts to assign series race numbers to each record where appropriate but this will not work if the
 * the start date is part way through a series.  In this case ou will need to edit the RN column by hand.
 *
 * Arguments (* required)
 *    start    -   start date (yyyy-mm-dd)
 *    end      -   end date (yyyy-mm-dd)
 *
 */

$loc  = "..";
$page = "rm9 event import";
define('BASE', dirname(__FILE__) . '/');

$target_dir = "../data/programme/";
$filename = str_replace("date", date("YmdHi"), "rm9_import_date.csv");
$filepath = $target_dir.$filename;

/******************************************************/
session_start();
$_SESSION = parse_ini_file("../config/common.ini", false);
$_SESSION['sql_debug'] = false;
$_SESSION['syslog'] = "../logs/syslogs/sys_log".date("Y_m_d").".log";
include("../common/classes/db_class.php");

// race format types
$format_map = array(
    "1" => "club series plus",
    "2" => "trophy",
    "3" => "handicap series plus",
    "4" => "pursuit race",
);

$entry_map = array(
    "ood" => "ood",
    "signon" => "on/retire",
    "signon-retire" => "on/retire",
    "signon-declare"=> "on/off",
);

$start_date  = "";
$end_date    = "";

// get input parameters
$where = array();
if (key_exists("start", $_REQUEST))
{
   $start_date = date("Y-m-d", strtotime($_REQUEST['start']));
   $where[] = " event_date >= '$start_date' ";
}
else
{
   echo "<pre>ERROR! - required start date for events not specified [start=YYYY-MM-DD]<br></pre>";
   exit("stopping ..."); 
}

if (key_exists("end", $_REQUEST))
{
   $end_date = date("Y-m-d", strtotime($_REQUEST['end']));
   $where[] = " event_date <= '$end_date' ";
}
else
{
    echo "<pre>ERROR! - required end date for events not specified [start=YYYY-MM-DD]<br></pre>";
    exit("stopping ...");
}


// connect to database
$db_o = new DB();

// retrieve events
$sql = "SELECT a.id, event_date, event_start, event_name, series_code, event_format, tide_time, tide_height, 
               event_entry, event_status, event_notes 
               FROM `t_event` as a 
               WHERE active=1 and `event_type`='racing' and event_date>='$start_date' and event_date<='$end_date' 
               ORDER BY event_date ASC, event_order ASC, event_start ASC";
//echo "<pre>".print_r($sql,true)."</pre>";
$rs = $db_o->db_get_rows($sql);
$num_records = count($rs);
echo "<pre>records retrieved: $num_records</pre>";

$date = "2000-01-01";
$htm_bufr = "";
$series_count = array();
$sequence_count = 0;
$out = array();
foreach ($rs as $k=>$event)
{
    $sql = "SELECT person FROM t_eventduty WHERE eventid = {$event['id']} and dutycode = 'ood_p'";
    $dutyrs = $db_o->db_get_rows($sql);
    count($dutyrs) > 0 ? $event['person'] = $dutyrs[0]['person'] : $event['person'] = "unknown";

    if (empty($event['series_code']))
    {
        $series_code = "";
    }
    else
    {
        $series_code = substr($event['series_code'], 0, strpos($event['series_code'], '-')).date("y", strtotime($event['event_date']));
    }


    if (!empty($event['series_code']))
    {
        $series_count["{$event['series_code']}"]++;
    }

    if (date("Y-m-d", strtotime($date)) == date("Y-m-d", strtotime($event['event_date'])))
    {
        $sequence_count++;
    }
    else
    {
        $sequence_count = 1;
    }

    $cols = array("EventID","EventDate","StartTime", "Event",  "series", "EventType", "HighWater","Height","notes","RN", "RaceSeq", "signontype" , "status", "OOD");

    $cols_bufr= "<tr>";
    foreach ($cols as $label)
    {
        $cols_bufr.= "<th><b>$label</b></th>";
    }
    $cols_bufr.= "</tr>";

    $arr = array(
        "EventID"   => $event['id'],
        "EventDate" => $event['event_date'],
        "StartTime" => $event['event_start'],
        "Event"     => $event['event_name'],
        "series"    => $series_code,
        "EventType" => $format_map[$event['event_format']],
        "HighWater" => $event['tide_time'],
        "Height"    => $event['tide_height'],
        "notes"     => $event['event_notes'],
        "RN"        => $series_count["{$event['series_code']}"],
        "RaceSeq"   => $sequence_count,
        "signontype"=> $entry_map[$event['event_entry']],
        "status"    => $event['event_status'],
        "OOD"       => $event['person']
    );

    $htm_bufr.= "<tr>";
    foreach ($arr as $field)
    {
        $htm_bufr.= "<td>$field</td>";
    }
    $htm_bufr.= "</tr>";

    $out[] = $arr;
    $date = $event['event_date'];
}

// cleanup old files of this type
    foreach (GLOB("rm9_import_*.csv") AS $file) { unlink($file); }

// now create csv file
$fp = fopen($filepath, 'wb');

fputcsv($fp, $cols, ',');
foreach ($out as $k=>$v)
{
    fputcsv($fp, $v, ',');
}
fclose($fp);

// report output
echo <<<EOT
<!DOCTYPE html>
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
     <h1>RM9 event import</h1>
     <p>Generating programme details from clubManager as a csv file<br><br>
	 Using database server [{$_SESSION['db_host']}/{$_SESSION['db_name']}]<br><br>
     Start Date: $start_date  End Date: $end_date<br><br>
	 Races processed $num_records</p>
 
	 <p>Please check the series number and event sequence carefully</p>
	 
	 <p><a href="$filepath">CSV file for import</a></p>

	<h3>Exported records:</h3> 
	<table border=0 width=100%>
	<thead>$cols_bufr</thead>
	<tbody>$htm_bufr</tbody>
	</table>
</body>
</html>			 
EOT;






  
  
 