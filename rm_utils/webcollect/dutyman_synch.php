<?php

/**
 * dutyman_synch.php
 *
 * script to export duty information from club manager in the dutyman csv format
 * 
 */

define('BASE', dirname(__FILE__) . '/');

/******************************************************/
session_start(); 
$_SESSION['sql_debug'] = false;
$_SESSION['syslog'] = "./sys_log".date("Y_m_d").".log";
include("./lib/db_class.php");

include ("./config.php");

$event_total = 0;
$duty_total = 0;
$duty_arr = array();

// get input parameters
if (key_exists("start", $_REQUEST))
{
   $start_date = date("Y-m-d", strtotime($_REQUEST['start']));
}
else
{
   echo "ERROR! - start date for events not specified [start=YYYYMMDD]<br>";
   exit("stopping ..."); 
}

if (key_exists("end", $_REQUEST))
{
   $end_date = date("Y-m-d", strtotime($_REQUEST['end']));
}
else
{
   echo "ERROR! - end date for events not specified [start=YYYYMMDD]<br>";
   exit("stopping ..."); 
}

$inc_tide = false;
if (key_exists("tide", $_REQUEST))
{
   if (strtolower($_REQUEST['tide']) == "y")
   {
      $inc_tide = true;
   }
}


// connect to database
$db_o = new DB();
echo <<<EOT
     Generating duty allocation details from clubManager as a csv file for import to dutyman<br><br>
	 Using database server [{$_SESSION['db_host']}/{$_SESSION['db_name']}]<br><br>
     Processing . . . (this may take a few minutes)<br><hr><br>
	<b>List of exported records:</b> 
	<table border=0 width=60%>			 
EOT;
html_flush();


// get event records
$sql = "SELECT * FROM ctblprogramme WHERE date >= '$start_date' AND date <= '$end_date' ORDER BY date ASC, sequence ASC";
$rs = $db_o->db_get_rows($sql);
$num_events = count($rs);

foreach ($rs as $$k=>$row)
{	
	$event_total++;
	
	$row['tide_time'] = substr($row['tide_time'], 0, 5);
	empty($row['series_num']) ? $event = $row['name'] : $event = $row['name']." - ".$row['series_num'];
	$inc_tide ? $event = "$event [HW {$row['tide_time']} - {$row['tide_height']}m]" : $event = $event;
	$duty_date = date("d/m/Y", strtotime($row['date']));
	$duty_time = substr($row['start'], 0, 5);
	$id = $row['id'];
	$duty_found = false;
	
	foreach ($duty_type as $field=>$duty)
    {		   
	   if (!empty($row[$field]))
	   {	       
		   $duty_found = true;
		   $duty_total++;
		   
		   // remove repeating whitespace in duty name
		   $name = preg_replace('/\s+/', ' ',$row[$field]);
		   $name = trim($name);
		   
		   // check if duty member is in webcollect database
		   //error_log("passing $name\n", 3, $_SESSION['syslog']);
		   $exists = check_member($name);	
           $exists ? $exist_check = "" : $exist_check = "**** duty person missing ****";
		   
		   // trim trailing characters and explode words in name
		   $names = explode(' ', rtrim($name, " [C]"));
		   $num_names = count($names);
		   $first_name = ucwords($names[0]);
		   $last_name = ucwords($names[$num_names - 1]);		   		   
		   
		   $duty_arr[] = array(
	          "duty_date"  => $duty_date,
	          "duty_time"  => $duty_time,
			  "duty_type"  => $duty,
	          "event"      => $event,
	          "first_name" => $first_name,
			  "last_name"  => $last_name,
			  "instruction" => $duty_instruction[$field],
              );

           echo "<tr><td>{$event}</td><td>{$duty_date}</td><td>{$duty_time}</td><td>{$duty}</td><td>{$first_name} {$last_name}</td><td>{$exist_check}</td></tr>";
		   html_flush();		  
	   }
	      
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

// delete any csv files to avoid them being cached
foreach (GLOB("*.csv") AS $filename) {
   unlink($filename);
}
	
// now create csv file
$filename = "dutyman_duty_import_".date("Y_m_d_H_i").".csv";
$fp = fopen($filename, 'wb');

$cols = array("Duty Date","Duty Time","Duty Type","Event","Swappable","Reminders","Duty Notify","Duty Instructions","Duty DBID","Notes","Confirmed","First Name","Last Name","Member Name","Mode","GenPswd","Date Format");

fputcsv($fp, $cols, ',');

foreach ($duty_arr as $k=>$v)
{
    $out_arr = array(
	"Duty Date" => $v['duty_date'],
	"Duty Time" => $v['duty_time'],
	"Duty Type" => $v['duty_type'],
	"Event" => $v['event'],
	"Swappable" => "Yes",
	"Reminders" => "Yes",
	"Duty Notify" => "",
	"Duty Instructions" => $v['instruction'],
	"Duty DBID" => "",
	"Notes" => "",
	"Confirmed" => "No",
	"First Name" => $v['first_name'],
	"Last Name" => $v['last_name'],
	"Member Name" => "",
	"Mode" => "",
	"GenPswd" => "",
	"Date Format" => ""	
	);

	fputcsv($fp, $out_arr, ',');
}


fclose($fp);

// now output download link to file
echo "<b>get the csv DUTY import file from <a href=\"$filename\">HERE</a></br></b>";


function html_flush()
{
   echo str_pad('',4096)."\n";
   ob_flush();
   flush();
}

function check_member($name)
{
   global $db_o;
   //error_log("receiving $name\n", 3, $_SESSION['syslog']);
   
   $exists = false;
     
   // search database (ctblmember should have same as webcollect
   $qname = addslashes($name);
   $sql = "SELECT * FROM ctblmember WHERE concat(firstname,' ', familyname) LIKE '$qname%'";   
   $rs = $db_o->db_get_rows($sql);
   
   if ($rs) { $exists = true; }
   
   // $exists ? $exist_msg = "ok" : $exist_msg = "not ok";
   // $message = "$name\n$qname\n$sql\n$exist_msg\n";	 
   // error_log($message, 3, $_SESSION['syslog']);
   
   return $exists;
}
?>



  
  
 