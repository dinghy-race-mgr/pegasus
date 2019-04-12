<?php
/* --- classes.php -----------------------------------------------------------------------

converts CLASS data from original racemanager database to new structure


*/
session_start();
$loc = "../../../";
include("$loc/common/lib/db_lib.php");

include("config.php");
$old_conn = db_connect('old');
$new_conn = db_connect('new'); 

// clear out tables from new database
echo <<<EOT
   <b>Transferring CLASS information:</b><br>
   <table border="0" width="90%" align="center">
	  <tbody>	
		<tr>
			<td width="5%">id</td>
			<td width="20%">name</td>
			<td width="10%">code</td>
			<td width="10%">py</td>
			<td width="10%">rya</td>
			<td width="5%">type</td>
			<td width="5%">crew</td>
			<td width="5%">rig</td>
			<td width="5%">spin</td>
			<td width="5%">engine</td>
			<td width="5%">keel</td>
			<td>&nbsp;</td>
		</tr>
EOT;
$clear = db_query($new_conn, "TRUNCATE t_class");

//
// -- CLASS information [t_class]
//
$result = db_query($old_conn, "SELECT * FROM tblboattypes ORDER BY BoatType");
$count = 0;
while ($row = db_fetchrow($result))
{
    $count++;
	
	// transform
    $acronym   = strtolower(substr($row['BoatType'],0,3));
    $classname = get_classname($row['BoatType']);

    $info 	   = $row['description'];
	$popular   = 0;
    $nat_py    = $row['ryapy'];
    $local_py  = $row['sycpy'];
    	
    $category  = $row['ryacategory'];
    $crew      = $row['numcrew'];	
    $rig 	   = $row['rigtype'];	
    $spinnaker = $row['spintype'];
    $engine    = $row['engine'];
	if ($engine == "O") { $engine = "OB";}
    $keel 	   = $row['keeltype'];	
    $updby 	   = "transfer-{$row['idtblBoatTypes']}";	

    $rya_id    = $acronym.$crew.$rig.$spinnaker;    
    
    // write to new database
    $insert = db_query($new_conn, "INSERT INTO t_class (`acronym`,`classname`,`info`,`popular`,`nat_py`,`local_py`,`rya_id`,`category`,`crew`,`rig`,`spinnaker`,`engine`,`keel`,`updby`) VALUES ('$acronym','$classname','$info','popular','$nat_py','$local_py','$rya_id','$category','$crew','$rig','$spinnaker','$engine','$keel','$updby')");
	
	echo <<<EOT
		<tr>
			<td width="5%">$count</td>
			<td width="20%">$classname</td>
			<td width="10%">$acronym</td>
			<td width="10%">$nat_py / $local_py</td>
			<td width="5%">$rya_id</td>
			<td width="5%">$category</td>
			<td width="5%">$crew</td>
			<td width="5%">$rig</td>
			<td width="5%">$spin</td>
			<td width="5%">$engine</td>
			<td width="5%">$keel</td>
			<td>$updby</td>
		</tr>
EOT;
    
}

echo "</tbody></table><br><br> - added $count classes to new database (t_class table): <br>";

function get_classname($class)
{
    $lclass = strtolower($class);
    if ($lclass == "merlin") { $class = "Merlin Rocket"; }

    return $class;
}
?>