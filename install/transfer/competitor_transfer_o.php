<?php
/* competitor_transfer
   utility script that does a transfer from the v9race.tblcompetitors to pegasus.t_competitor
   
   database configuration is hardcoded
   
   old competitor id will be stored in t_competitor.memberid following transfer
*/

session_start();
include ("../classes/db_class.php");            // include database interface

echo "<b>COMPETITOR TRANSFER:</b><br>";
 
/* from database */
$_SESSION['db_host'] = "";
$_SESSION['db_user'] = "";
$_SESSION['db_pass'] = ""; 
$_SESSION['db_name'] = "";

$db_o = new DB();
echo " -> connected to racemanager database<br>";

// get data
$rs = $db_o->db_get_rows("SELECT * FROM tblcompetitors WHERE visibility = 1 ORDER BY id ASC");    // check update date
$numrows = count($rs);
echo " -> retrieved $numrows competitors<br>";

// disconnect
$db_o->db_disconnect();


/* to database */
$_SESSION['db_host'] = "localhost";
$_SESSION['db_user'] = "rmuser";
$_SESSION['db_pass'] = "pegasus";
$_SESSION['db_name'] = "pegasus";

$db_o = new DB();
echo " -> connected to PEGASUS database<br>";

// empty t_entry table
$db_o->db_truncate( array("t_competitor") );
echo " -> emptied competitor table<br>";

// loop over signon records
echo " -> inserting competitors . . .<br><br>";
$count = 0;
$skip_count = 0;
foreach ($rs as $k=>$row)
{
    $new = array();
    // check if competitor already exists
    $comp = $db_o->db_get_row("SELECT * FROM t_competitor WHERE memberid = {$row['competitorid']}");
    
    if (!empty($comp))  // delete existing record
    {
       $db_o->db_delete( t_competitor, $where = array("id"=>$comp["id"]), $limit = '' );
    }
    
    // get id for class from Pegasus database
    $class = $db_o->db_get_row("SELECT * FROM t_class WHERE classname = {$row['boatClass']}"); 
    
    if (empty($class))     // don't add if class is not known
    {
      $skip_count++;
      echo  " --- skipped: class not found [{$row["boatClass"]} {$row["sailNumber"]} - {$row["helmName"]}]<br>";
    }
    else
    {
      // map old competitor record to Pegasus structure
      $new['classid']    = $class['id'];
      $new['boatnum']    = $row['boatNumber'];
      $new['sailnum']    = $row['sailNumber'];
      $new['helm']       = $row['helmName'];
      $new['crew']       = $row['crewName'];
      $new['club']       = "Starcross YC";
      $new['regular']    = 0;
      $new['last_entry'] = $row['lastRaced'];   
      $new['active']     = 1;
      $new['memberid']   = $row['id'];   // keeping a record of id in old database
      $new['updby']      = "transfer_".date("Y-m-d");

      // add to database
      $db_o->db_insert( "t_competitor", $new );
      
      // log to screen
      $count++;
      echo " --- inserted: {$row["boatClass"]} {$row["sailNumber"]} - {$row["helmName"]}<br>";
    }
    
}
$db_o->db_disconnect();
echo "<br><br> -> processing complete, $numrows records<br>";
echo " -> $count entries added; $skip_count skipped<br>;
    
    