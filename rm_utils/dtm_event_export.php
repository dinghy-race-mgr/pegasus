<?php

/**
 * dtm_event_export.php
 *
 * script to export events from racemanager to the dutyman csv format
 *
 * Arguments (* required)
 *    start    -   start date (yyyymmdd) *
 *    end      -   end date (yyyymmdd)
 *    status   -   |draft|live|both| - default is live (only published events)
 *    type     -   |all|<event type>| - defaults to all types of events
 *    format   -   |short|long| if short sends a shortened form with four fields - if long sends
 *                 longer version with seven fields - default is true (shorter version)
 *    noevent  -   |keep|remove| remove any events with type noevent (e.g not possible to sail due to tide)
 *                 default remove
 *    cleanup  -   |Y|N| if set to Y removes old export files of this type - defaults to Y
 */

$loc  = "..";
$page = "dutyman event export";
define('BASE', dirname(__FILE__) . '/');

require_once ("$loc/config/rm_utils_cfg.php");
$target_dir = $_SESSION['dutyman']['loc']."/";
$filename = str_replace("date", date("YmdHi"), $_SESSION['dutyman']['event_file']);
$filepath = $target_dir.$filename;

/******************************************************/
session_start();
$_SESSION = parse_ini_file("../config/common.ini", false);
$_SESSION['sql_debug'] = false;
include("../common/classes/db_class.php");

$start_date  = "";
$end_date    = "";
$status      = "live";
$type        = "all";
$format      = "short";
$noevent     = "remove";
$cleanup     = true;

// get input parameters
$where = array();
if (key_exists("start", $_REQUEST))
{
   $start_date = date("Y-m-d", strtotime($_REQUEST['start']));
   $where[] = " event_date >= '$start_date' ";
}
else
{
   echo "<pre>ERROR! - required start date for events not specified [start=YYYYMMDD]<br></pre>";
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

if (key_exists("type", $_REQUEST))
{
      $type = strtolower($_REQUEST['type']);
}

if ($type != "all")
{
	$where[] = " event_type LIKE '".strtolower($_REQUEST['type'])."' ";
}

if (key_exists("format", $_REQUEST))
{
      if ($_REQUEST['format'] == "long")
	  {
	      $format = "long";
	  }
}

if (key_exists("noevent", $_REQUEST))
{
      if ($_REQUEST['noevent'] == "keep")
	  {
	      $noevent = "keep";
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
     <h1>raceManager-to-Dutyman event export</h1>
     Generating programme details from clubManager as a csv file<br><br>
	 Using database server [{$_SESSION['db_host']}/{$_SESSION['db_name']}]<br><br>
     Processing . . . (this may take a little while)<br><hr><br>
	<b>List of exported records:</b> 
	<table border=0 width=60%>			 
EOT;
html_flush();

// get race formats
$sql = "SELECT * FROM t_cfgrace ORDER BY id ASC";
$rs = $db_o->db_get_rows($sql);
$race_formats = array();
foreach ($rs as $row)
{
	$race_formats[$row['id']] = $row['race_name'];
}

// get event records
$where_str = implode(" AND ", $where);
$sql = "SELECT * FROM t_event WHERE $where_str ORDER BY event_date ASC, event_order ASC";
$rs = $db_o->db_get_rows($sql);
$num_events = count($rs);

$event_arr = array();
$event_total = 0;
foreach ($rs as $k=>$row)
{	
	if ($noevent == "keep" OR $row['event_type'] != "noevent")
	{		
		$event_total++;
		$row['event_format'] ? $ev_format = $race_formats[$row['event_format']] : $ev_format = "none";
		
		if ($format == "short")
		{
			$event_arr[$event_total] = array(
			   "event"       => $row['event_name'],
			   "date"        => date("d/m/Y", strtotime($row['event_date'])),
			   "start"       => substr($row['event_start'], 0, 8),
			   "description" => "tide: ".substr($row['tide_time'], 0, 5)." - ".trim($row['tide_height'])."m"
			);
			echo <<<EOT
				<tr>
					<td>{$event_arr[$event_total]['event']}</td>
					<td>{$event_arr[$event_total]['date']}</td>
					<td>{$event_arr[$event_total]['start']}</td>
					<td>{$event_arr[$event_total]['description']}</td>
				</tr>
EOT;
		}
		else
		{
			$event_arr[$event_total] = array(
			   "event"       => $row['event_name'],
			   "date"        => date("d/m/Y", strtotime($row['event_date'])),
			   "start"       => substr($row['event_start'], 0, 8),
			   "tide_time"   => substr($row['tide_time'], 0, 8),
			   "tide_height" => $row['tide_height'],
			   "type"        => $row['event_type'],
			   "format"      => $ev_format
			);
			echo <<<EOT
				<tr>
					<td>{$event_arr[$event_total]['event']}</td>
					<td>{$event_arr[$event_total]['date']}</td>
					<td>{$event_arr[$event_total]['start']}</td>
					<td>{$event_arr[$event_total]['tide_time']}</td>
					<td>{$event_arr[$event_total]['tide_height']}</td>
					<td>{$event_arr[$event_total]['type']}</td>
					<td>{$event_arr[$event_total]['format']}</td>
				</tr>
EOT;
		}
	}
}	

echo <<<EOT
     </table>
	 <br><br>
	 Programme events processed: $event_total<br>
EOT;
html_flush();

// cleanup old files of this type
if ($cleanup)
{
	foreach (GLOB("dutyman_event_*.csv") AS $file) { unlink($file); }
}

// now create csv file
$fp = fopen($filepath, 'wb');

if ($format == "short")
{
   $cols = array("event","date","start","description");
}
else
{
   $cols = array("event","date","start","tide_time","tide_height","type","format");
}

fputcsv($fp, $cols, ',');

foreach ($event_arr as $k=>$v)
{
	fputcsv($fp, $v, ',');
}

fclose($fp);

echo "<b>get the csv EVENT import file from <a href=\"$filepath\">HERE</a></br></b>";


function html_flush()
{
   echo str_pad('',4096)."\n";
   ob_flush();
   flush();
}




  
  
 