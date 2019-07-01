<?php

/**
 * dutyman_members.php
 *
 * creates member import csv file for dutyman from webcollect database
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

$member_total = 0;
$rota_total = 0;
$member_arr = array();

echo <<<EOT
     Generating member details information csv file for import to dutyman<br><br>
     Processing . . . (this may take a few minutes)<br><hr><br>
	<b>List of exported records:</b> 
	<table border=0 width=60%>			 
EOT;
html_flush();

// get the webcollect member records
$client = new WebcollectRestapiClient();
$member = $client->setOrganisationShortName(ORGANISATION_SHORT_NAME)           // this is the short name selected when the org was created on webcollect
  ->setAccessToken(ACCESS_TOKEN)                                               // from the admin UI
  ->setEndPoint('member')                                                     
  ->setQuery()                                                                 // query all members
  ->find('process_member');                                                    // if we pass a callback the client will call it with each object

echo <<<EOT
     </table>
	 <br><br>
	 Member records processed: $member_total<br>
	 Member records transferred: $rota_total<br>
EOT;

html_flush();

// delete any csv files to avoid them being cached
foreach (GLOB("*.csv") AS $filename) 
{
   unlink($filename);
}

// output csv
$filename = "dutyman_member_import_".date("Y_m_d_H_i").".csv";
$fp = fopen($filename, 'wb');

$cols = array("First Name","Last Name","Member Name","Password","Email Address","Phone","Phone 2","Number of Duties");
fputcsv($fp, $cols, ',');

foreach ($member_arr as $k=>$v)
{
	fputcsv($fp, $v, ',');
}

fclose($fp);

// now output download link to file
echo "<b>get the csv DUTY import file from <a href=\"$filename\">HERE</a></br></b>";

exit();
 
function process_member(WebCollectResource $resource) 
// this is called once per member object returned from the api
{ 
  global $member_total;
  global $rota_total;
  global $member_arr; 

  $member_total++; 
  
  $array = json_decode(json_encode($resource), true);  // convert into array

  if (!empty($array['form_data']['Allocated_Duties_Club_use_only']))    // we have a member on a rota
  {
     $rota_total++;
	 $member_arr[] = array(
	   "First Name"       => trim($array['firstname']),
	   "Last Name"        => trim($array['lastname']),
	   "Member Name"      => "",
	   "Password"         => "",
	   "Email Address"    => trim($array['email']),
	   "Phone"            => trim($array['home_phone']),
	   "Phone 2"          => trim($array['mobile_phone']),
	   "Number of Duties" => "no limit",
	 );
	
	echo "<tr><td>{$array['firstname']}</td><td>{$array['lastname']}</td><td>{$array['email']}</td><td>{$array['home_phone']}</td><td>{$array['mobile_phone']}</td></tr>";
    html_flush();
  }  

} 

function html_flush()
{
   echo str_pad('',4096)."\n";
   ob_flush();
   flush();
}