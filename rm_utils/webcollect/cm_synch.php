<?php

/**
 * cm_synch.php
 *
 * script to upload minimal membership data from webcollect to club manager
 * to allow the rota information tio be used in club manager
 * 
 */

define('BASE', dirname(__FILE__) . '/');

require BASE . 'lib/WebCollectRestapiClient.php';

// you need to customise these values for your organisation
define('ORGANISATION_SHORT_NAME' , 'STARCROSSYC');

// you can generate the access token from the administrator panel if you have role "creator"
define('ACCESS_TOKEN', '986T9N5ZDSQFDBWAR4DCKOKEZS35YDFEP49KBBWMKJKXMKEHBPZOWUFC6HPZG6CS');


/******************************************************/
session_start(); 
$_SESSION['sql_debug'] = false;
include ("./lib/db_class.php");

$sql_insert_data = "";
$member_total = 0;
$rota_total = 0;

include ("./config.php");

// connect to database
$db_o = new DB();

echo "Processing . . . (this may take a few minutes)<br>Using server {$_SESSION['db_host']}/{$_SESSION['db_name']}<br>";

$empty = $db_o->db_truncate(array("ctblmember"));

if ($empty)
{
	echo <<<EOT
	-- member table emptied <br>
	<b>List of transferred records:</b> 
    <hr>
	<table border=0 width=40%>			 
EOT;
	html_flush();
	
	// get the webcollect member records
	$client = new WebcollectRestapiClient();
	$member = $client->setOrganisationShortName(ORGANISATION_SHORT_NAME)           // this is the short name selected when the org was created on webcollect
	  ->setAccessToken(ACCESS_TOKEN)                                               // from the admin UI
	  ->setEndPoint('member')                                                     
	  ->setQuery()                                                                 // query all members
	  ->find('process_member');                                                    // if we pass a callback the client will call it with each object
	
	echo "</table>";
	html_flush();
	
	// run insert query
	$sql_insert_data = rtrim($sql_insert_data,", ");
	$sql =  "INSERT INTO ctblmember (type,status,memberyear,firstname,familyname,rotatags) VALUES $sql_insert_data";

	if ($_REQUEST['mode'] == "sql") {echo $sql."<br><br>"; }
	
    $insert = $db_o->db_query($sql);	

	if ($insert)
	{
		// report counts and data
		echo <<<EOT
			 Member records processed: $member_total<br>
			 Rota member records transferred: $rota_total<br>
EOT;
	}
	else
	{
	    echo "ERROR: rota data insert did not work - STOPPING.....";	
	}	
}
else
{
   echo "ERROR: member data table is not empty - STOPPING.....";
}
$db_o->db_disconnect();
exit();
 
function process_member(WebCollectResource $resource) 
// this is called once per member object returned from the api
{ 
  global $sql_insert_data;
  global $member_total;
  global $rota_total;

  $member_total++;   
  
  $switch = array(
     "ood cruising"       => "ood_rota",
	 "safety boat driver" => "safety_rota",
	 "safety boat crew"   => "safety_crew",
	 "ood racing"         => "ood_rota",
	 "aood"               => "aood_rota",
	 "galley"             => "galley_rota",
	 "bar"                => "bar_rota"
  );   
  
  $array = json_decode(json_encode($resource), true);  // convert into array
  
  $firstname = trim($array['firstname']);
  $surname   = trim($array['lastname']);
  $rota_str  = strtolower($array['form_data']['Allocated_Duties_Club_use_only']);
  
  if (!empty($rota_str))
  {
     $rota_total++;
	 
	 // translate rota codes
	 foreach ($switch as $key=>$code)
	 {
	    $rota_str = str_replace($key, $code, $rota_str, $count);
		if ($key == "ood cruising" AND $count >= 1)
		{
		    $surname = $surname." [C] ";
		}	 
	 }
	 
	 $sql_insert_data.= <<<EOT
	   ("family", "current", "2017", "$firstname", "$surname", "$rota_str" ),
EOT;
  }
  
  echo "<tr><td>$firstname</td><td>$surname</td><td>$rota_str</td></tr>";
  html_flush();
} 

function html_flush()
{
   echo str_pad('',4096)."\n";
   ob_flush();
   flush();
}



  
  
 