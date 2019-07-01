<?php

/**
 * cm_programme_export.php
 *
 * script to export programme in csv format
 *
 * 
 */

define('BASE', dirname(__FILE__) . '/');

/******************************************************/
session_start(); 
$_SESSION['sql_debug'] = false;
$_SESSION['syslog'] = "./sys_log".date("Y_m_d").".log";
include ("./lib/db_class.php");

include ("./config.php");



$start_date = "";
$end_date = "";
$status = "live";
$type = "all";
$description = true;
$noevent = "keep";

// get input parameters
$where = array();
if (key_exists("start", $_REQUEST))
{
   $start_date = date("Y-m-d", strtotime($_REQUEST['start']));
   $where[] = " date >= '$start_date' ";
}
else
{
   echo "ERROR! - start date for events not specified [start=YYYYMMDD]<br>";
   exit("stopping ..."); 
}

if (key_exists("end", $_REQUEST))
{
   $end_date = date("Y-m-d", strtotime($_REQUEST['end']));
   $where[] = " date <= '$end_date' ";
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
	$where[] = " visible = 1 ";
}
elseif ($status == "draft")
{
    $where[] = " visible = 0 ";
}

if (key_exists("type", $_REQUEST))
{
      $type = strtolower($_REQUEST['type']);
}
if ($type != "all")
{
	$where[] = " type LIKE '".strtolower($_REQUEST['type'])."' ";
}

if (key_exists("description", $_REQUEST))
{
      if ($_REQUEST['description'] == "false") 
	  {
	      $description = false;
	  }
}

if (key_exists("noevent", $_REQUEST))
{
      if ($_REQUEST['noevent'] == "remove") 
	  {
	      $noevent = "remove";
	  }
}

// connect to database
$db_o = new DB();
echo <<<EOT
     Generating programme details from clubManager as a csv file<br><br>
	 Using database server [{$_SESSION['db_host']}/{$_SESSION['db_name']}]<br><br>
     Processing . . . (this may take a little while)<br><hr><br>
	<b>List of exported records:</b> 
	<table border=0 width=60%>			 
EOT;
html_flush();


// get event records
$where_str = implode(" AND ", $where);

$sql = "SELECT * FROM ctblprogramme WHERE $where_str ORDER BY date ASC, sequence ASC";
//echo $sql."<br>";

$rs = $db_o->db_get_rows($sql);
$num_events = count($rs);

$event_arr = array();
$event_total = 0;
foreach ($rs as $k=>$row)
{	
	if ($noevent == "keep" OR (strpos($row['name'], 'no event possible') === false AND $noevent == "remove"))
	{		
		$event_total++;
		
		$row['format'] ? $format = $row['format'] : $format = "none"; 
		
		$row['series_num'] ? $name = trim($row['name']." - ".$row['series_num']) : $name = trim($row['name']);
		
		if ($description)
		{
			$event_arr[$event_total] = array(
			   "event"       => $name,
			   "date"        => date("d/m/Y", strtotime($row['date'])),
			   "start"       => substr($row['start'], 0, 8),
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
			   "event"       => $name,
			   "date"        => date("d/m/Y", strtotime($row['date'])),
			   "start"       => substr($row['start'], 0, 8),
			   "tide_time"   => substr($row['tide_time'], 0, 8),
			   "tide_height" => $row['tide_height'],
			   "type"        => $row['type'],
			   "format"      => $format
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

// delete any csv files to avoid them being cached
foreach (GLOB("*.csv") AS $filename) {
   unlink($filename);
}
	
// now create csv file
$filename = "programme_export_".date("Y_m_d_H_i").".csv";
$fp = fopen($filename, 'wb');

if ($description)
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

// now output download link to file
//$last_slash_pos = strrpos ( $_SERVER['REQUEST_URI'] , "/" );
//$uri = substr($_SERVER['REQUEST_URI'], 0, $last_slash_pos);
//$url = 	$_SESSION['protocol'].$_SERVER['SERVER_NAME']."$uri/$filename";

echo "<b>get the csv DUTY import file from <a href=\"$filename\">HERE</a></br></b>";


function html_flush()
{
   echo str_pad('',4096)."\n";
   ob_flush();
   flush();
}

?>



  
  
 