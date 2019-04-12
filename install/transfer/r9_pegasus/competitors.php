<?php
/* --- competitors.php -----------------------------------------------------------------------

converts COMPETITOR data from original racemanager database to new structure


*/
session_start();
$loc = "../../../";
include("$loc/common/lib/db_lib.php");

include("config.php");
$old_conn = db_connect('old');
$new_conn = db_connect('new');

// clear out tables from new database
echo <<<EOT
   <b>Transferring COMPETITOR information:</b><br>
   <table border="0" width="90%" align="center">
	  <tbody>	
		<tr>
			<td width="5%">id</td>
			<td width="20%">class</td>
			<td width="10%">sail</td>
			<td width="10%">helm</td>
			<td width="10%">last entry</td>
			<td width="5%">status</td>
			<td width="5%">updby</td>
		</tr>
EOT;
$clear = db_query($new_conn, "TRUNCATE t_competitor");

//
// -- COMPETITOR information [t_competitor]
// not transferring open meeting entries
//
$result = db_query($old_conn, "SELECT * FROM tblcompetitors WHERE list1=1 OR list2=1 ORDER BY id");
$count = 0;
while ($row = db_fetchrow($result))
{
    // get new classid#
    $result2 = db_query($new_conn, "SELECT id, classname FROM t_class WHERE updby = 'transfer-{$row['classID']}'");
    $row2 = db_fetchrow($result2);

    $count++;
	// transform
    $classid     = $row2['id'];
    $boatnum     = $row['boatNumber'];
    $sailnum     = $row['sailNumber'];
	$helm        = $row['helmName'];
    $crew        = $row['crewName'];
    $club        = $row['club'];
    $last_entry  = $row['lastRaced'];
    if ($row['visibility']=="1")
    {
        $status  = "current";
    }
    else
    {
        $status  = "retired";
    }
    $updby 	     = "transfer-{$row['id']}";
    
    // write to new database
    $insert = db_query($new_conn, "INSERT INTO t_competitor (`classid`,`boatnum`,`sailnum`,`helm`,`crew`,`club`,`last_entry`,`status`,`updby`) VALUES ('$classid','$boatnum','$sailnum','$helm','$crew','$club','$last_entry','$status','$updby')");

	echo <<<EOT
		<tr>
			<td width="5%">$count</td>
			<td width="15%">{$row2['classname']}</td>
			<td width="10%">$sailnum</td>
			<td width="15%">$helm</td>
			<td width="10%">$last_entry</td>
			<td width="10%">$status</td>
			<td width="10%">$updby</td>
		</tr>
EOT;
    
}

echo "</tbody></table><br><br> - added $count competitors to new database (t_competitor table): <br>";

?>