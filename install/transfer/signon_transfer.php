<?php
/* signon_transfer
   utility script that does a transfer from the signon table for racemanager 9 to the new schema
   
   database configuration is hardcoded
   
   eventid is passed with URL
   
   old competitor id will be stored in t_competitor.memberid following transfer
*/

session_start();
include ("../classes/db_class.php")            // include database interface

echo "<b>ENTRY TRANSFER:</b><br>";

if (empty($_REQUEST['eventid'])
{
   echo "*** Failed: event id not specified";
   exit();
}
 
/* from database */
$_SESSION['db_host'] = "";
$_SESSION['db_user'] = "";
$_SESSION['db_pass'] = ""; 
$_SESSION['db_name'] = "";

$db_o = new DB();
echo " -> connected to racemanager database<br>";

// get data
$rs = $db_o->db_get_rows("SELECT * FROM tblsignon WHERE status in ('new','loaded') AND change in ('none','temp') AND eventid = {$_REQUEST['eventid']} AND competitorid > 0 ORDER BY updatedate ASC");    // check update date
$numrows = count($rs);
echo " -> retrieved $numrows entries<br>";

// disconnect
$db_o->db_disconnect();


/* to database */
$_SESSION['db_host'] = "localhost";
$_SESSION['db_user'] = "root";
$_SESSION['db_pass'] = ""; 
$_SESSION['db_name'] = "pegasus";

$db_o = new DB();
echo " -> connected to PEGASUS database<br>";

// empty t_entry table
$db_o->db_truncate( array("t_entry") )
echo " -> emptied signon table<br>";

// loop over signon records
echo " -> inserting entries . . .<br><br>";
$count = 0;
$skip_count = 0;
foreach ($rs as $k=>$row)
{
    $new = array();
    // check if competitor is known
    $comp = $db_o->db_get_row("SELECT * FROM t_competitor WHERE memberid = {$row['competitorid']}")
    
    if (!empty($comp))
    {
       // map to array
       
       $new['action'] = 'enter'; 
       if ($comp['change'] == "repeat") { $new['action'] = "replace"; )
       $new['protest'] = 0;
       $new['status'] = "N";
       $new['eventid'] = $_REQUEST['eventid'];
       $new['competitorid'] = $comp['id'];
       $new['memberid'] = $row['competitorid'];
       $new['change_crew'] = "";
       $new['change_sailnum'] = "";
       $new['entryid'] = 0;
       $new['updby'] = "transfer";
       
       // construct info message
       $info = "{$comp['helm']} [sailnum: {$comp['sailnum']}, action: {$new['action']}]"
    
       // add to database
       $db_o->db_insert( "t_entry", $new )
       $count++;
       echo " --- $info inserted <br>";
    
    }
    else
    {
       $skip_count++;
       echo " --- entry not recognised - skipped  [competitorid - {$row['competitorid']}] <br>"
    
    }
}
$db_o->db_disconnect();
echo "<br><br> -> processing complete, $numrows records<br>";
echo " -> $count entries added; $skip_count skipped<br>;
    
    