<?php
/* --- transfer_data.php -----------------------------------------------------------------------

converts data from original racemanager database to new structure


TO DO:
fix race configuration to support new structure
fix tblfixture to use t_event and t_eventduty

*/
session_start();
include("../common/lib/db_lib.php");

$programme_date = "2012-03-31";
$result_event = "900";

$_SESSION['old']['DBserver'] = "localhost";
$_SESSION['old']['DBuser']   = "root";
$_SESSION['old']['DBpwd']    = "";
$_SESSION['old']['DBase']    = "rm_old";
$old_conn = db_connect('old');

$_SESSION['new']['DBserver'] = "localhost";
$_SESSION['new']['DBuser']   = "root";
$_SESSION['new']['DBpwd']    = "";
$_SESSION['new']['DBase']    = "rm_v10";
$new_conn = db_connect('new');

/* ----------------------------------------------------------------------------------------------------- 
   BOAT related information   
   -----------------------------------------------------------------------------------------------------
*/

// clear out tables from new database
echo "<b>Transferring BOAT information:</b> <br>";
$clear = db_query($new_conn, "TRUNCATE t_class");

//
// -- CLASS information [t_class]
//
$result = db_query($old_conn, "SELECT * FROM tblboattypes ORDER BY BoatType");
$count = 0;
while ($row = db_fetchrow($result))
{
    // transform
    $id       = $row['idtblBoatTypes'];
    $acronym  = strtolower(substr($row['BoatType'],0,3));
    $class    = $row['BoatType'];
    $desc 	  = $row['description'];
    $nat_py   = $row['ryapy'];
    $local_py = $row['sycpy'];
    $rya_id   = "";	
    $category = $row['ryacategory'];
    $numcrew  = $row['numcrew'];	
    $rig 	  = $row['rigtype'];	
    $spin 	  = $row['spintype'];
    $engine   = $row['engine'];
    $keel 	  = $row['keeltype'];	
    $updby 	  = "transfer";	    
    
    // write to new database
    $insert = db_query($new_conn, "INSERT INTO t_class (`id`,`acronym`,`class`,`desc`,`nat_py`,`local_py`,`rya_id`,`category`,`numcrew`,`rig`,`spin`,`engine`,`keel`,`updby`) VALUES ('$id','$acronym','$class','$desc','$nat_py','$local_py','$rya_id','$category','$numcrew','$rig','$spin','$engine','$keel','$updby')");
    $count++;
}
echo " - added $count classes to t_class: <br>";

//
// --- COMPETITOR information [t_competitor, t_complist]
//
echo " - truncating t_class: <br>";
$clear = db_query($new_conn, "TRUNCATE t_competitor");
echo " - truncating t_competitor: <br>";

$result = db_query($old_conn, "SELECT * FROM tblcompetitors ORDER BY id");
$count = 0;
while ($row = db_fetchrow($result))

{
    // transfor
    $class       = $row['boatClass'];
    $classid     = $row['classID'];
    $boatno      = $row['boatNumber'];
    $sailno      = $row['sailNumber'];
    $helm        = $row['helmName'];
    $crew        = $row['crewName'];
    if ($row['list3']!=1) { $club = "Starcross YC"; }
    $personal_py = $row['PY'];
    $last_entry  = $row['lastRaced'];
    
    if ($row['visibility']==0)
        { $status = "retired"; }
    else 
        { $status = "current";  }
    
    $group = "";
    if ($row['list3']==1)
    {
        $group = "visitor";
    }
    else
    {
        if ($row['list1']==1) { $group.= "member,"; }
        if ($row['list2']==1) { $group.= "junior,"; }
        $group = rtrim($group,",");
    }      
    $updby       = "transfer";
    
    $insert = db_query($new_conn, "INSERT INTO t_competitor (`class`,`classid`,`boatno`,`sailno`,`helm`,`crew`,`club`,`personal_py`,`last_entry`,`status`,`group`,`updby`) VALUES ('$class','$classid','$boatno','$sailno','$helm','$crew','$club','$personal_py','$last_entry','$status','$group','$updby')");
    $compid = db_lastinsert($new_conn);
    $count++;
    
	/*
    // add list information - t_complist
    if ($row['list1']==1)
        { $insert = db_query($new_conn, "INSERT INTO t_complist (`list`,`competitorid`,`updby`) VALUES ('general','$compid','$updby')"); }
    if ($row['list2']==1)
        { $insert = db_query($new_conn, "INSERT INTO t_complist (`list`,`competitorid`,`updby`) VALUES ('junior','$compid','$updby')"); }
    if ($row['list3']==1)
        { $insert = db_query($new_conn, "INSERT INTO t_complist (`list`,`competitorid`,`updby`) VALUES ('open','$compid','$updby')"); }
	*/
}
echo " - added $count competitors to t_competitor: <br>";

/* -----------------------------------------------------------------------------------------------------
      PROGRAMME related information
   -----------------------------------------------------------------------------------------------------
*/
echo "<b>Transferring PROGRAMME information:</b> <br>";

//
// -- EVENT information [t_event, t_eventresult]
//

$clear = db_query($new_conn, "TRUNCATE t_event");
echo " - truncating t_event: <br>";
$clear = db_query($new_conn, "TRUNCATE t_eventresult");
echo " - truncating t_eventresult: <br>";
$result = db_query($old_conn, "SELECT *, tblfixtures.EventId as eventid, tbleventcfg.eventid as eventcfgid FROM tblfixtures JOIN tbleventcfg ON tblfixtures.EventType=tbleventcfg.eventname WHERE tblfixtures.EventId>='$result_event' ORDER BY tblfixtures.EventId");
$count = 0;
while ($row = db_fetchrow($result))
{
    // transfer
    $id             = $row['eventid'];
    $event_date     = $row['EventDate'];
    $event_start    = $row['StartTime'];
    $event_name     = str_replace(array('\'', '"'), '', $row['Event']);
    $series_code    = $row['series'];
    $series_num     = $row['RN'];
    //  $event_sequence = $row['RaceSeq'];     no longer required
    $event_format   = $row['eventcfgid'];
    $event_entry    = $row['signontype'];
    $event_timing   = "standard";
    $event_status   = $row['status'];
    $raceofficer    = $row['OOD'];
    $timekeeper     = $row['AOOD'];
    $tide_time      = $row['HighWater'];
    $tide_height    = $row['Height'];
    $ws_start       = $row['wspdstart'];
    $wd_start       = $row['wdirstart'];
    $ws_end         = $row['wspdend'];
    $wd_end         = $row['wdirend'];
    $notes          = $row['notes'];
    $upddate        = $row['LastUpdate'];
    $updby          = "transfer";
    
    $insert = db_query($new_conn, "INSERT INTO t_event (`id`,`event_date`,`event_start`,`event_name`,`series_code`,`series_num`,`event_format`,`event_entry`,`event_timing`,`event_status`,`raceofficer`,`timekeeper`,`tide_time`,`tide_height`,`ws_start`,`wd_start`,`ws_end`,`wd_end`,`result_notes`,`upddate`,`updby`) VALUES ('$id','$event_date','$event_start','$event_name','$series_code','$series_num','$event_format','$event_entry','$event_timing','$event_status','$raceofficer','$timekeeper','$tide_time','$tide_height','$ws_start','$wd_start','$ws_end','$wd_end','$notes','$upddate','$updby')");
    $count++;
    $eventid = db_lastinsert($new_conn);
  
    // add results links 
    if ($row['resultsURL'])
        { $insert = db_query($new_conn,"INSERT INTO t_eventresult (`eventid`,`result_type`,`result_format`,`result_URL`,`result_notes`,`result_status`, `upddate`) VALUES ('$eventid', 'race', 'html', '{$row['resultsURL']}', '', 'final', '{$row['LastUpdate']}')"); }
    if  ($row['seriesURL'])
        { $insert = db_query($new_conn,"INSERT INTO t_eventresult (`eventid`,`result_type`,`result_format`,`result_URL`,`result_notes`,`result_status`, `upddate`) VALUES ('$eventid', 'series', 'html', '{$row['seriesURL']}', '', 'final', '{$row['LastUpdate']}')"); }
    if  ($row['importURL'])
        { $insert = db_query($new_conn,"INSERT INTO t_eventresult (`eventid`,`result_type`,`result_format`,`result_URL`,`result_notes`,`result_status`, `upddate`) VALUES ('$eventid', 'race', 'csv', '{$row['importURL']}', '', 'final', '{$row['LastUpdate']}')"); }   
}
echo " - added $count fixtures to t_event: <br>";  

//
// -- SERIES information [t_series]
//
$clear = db_query($new_conn, "TRUNCATE t_series");
echo " - truncating t_series: <br>";

$result = db_query($old_conn, "SELECT * FROM tblseries WHERE seriesStart>='$programme_date' ORDER BY seriesStart");
$count = 0;

while ($row = db_fetchrow($result))
{
    // transfer
    $id           = $row['seriesID'];
    $series_name  = $row['seriesName'];
    $category     = "single";
 //   $year         = $row['seriesYear'];
 //   $numraces     = $row['seriesRaces'];
    $discard      = $row['seriesDiscard'];
    $startdate    = $row['seriesStart'];
    $enddate      = $row['seriesEnd'];
    $classresults = $row['seriesClass'];
    $series_code  = $row['seriesCode'];
    $series_parent= "";
    $notes        = $row['notes'];
	$active      = 1;
    $upddate      = $row['lastUpdate'];
    $updby        = "transfer";
    
    // write to new database
    $insert = db_query($new_conn, "INSERT INTO t_series (`id`,`series_name`,`category`,`discard`,`startdate`,`enddate`,`classresults`,`series_code`,`series_parent`,`notes`,`active`,`upddate`,`updby`) VALUES ('$id','$series_name','$category','$discard','$startdate','$enddate','$classresults','$series_code','$series_parent','$notes','$active','$upddate','$updby')");
    $count++;

}
echo " - added $count series to t_series: <br>";  

//
// -- RESULT information [t_results]      ******* ADD HERE
//
$clear = db_query($new_conn, "TRUNCATE t_results");
echo " - truncating t_results: <br>";

$result = db_query($old_conn, "SELECT * FROM tblresults WHERE eventID>='$result_event' ORDER BY resultID");
$count = 0;

while ($row = db_fetchrow($result))
{
    // transfer
    $id           = $row['resultID'];
    $eventid      = $row['eventID'];
    $race         = $row['race'];
    $race_type    = $row['racetype'];
    $competitorid = $row['competitorID'];
    $class        = $row['class'];
    $sailnum      = $row['sailnum'];
    $py           = $row['resultPY'];
    $helm         = $row['helm'];
    $crew         = $row['crew'];
    $laps         = $row['laps'];
    $etime        = $row['elapsedTime'];
    $ctime        = $row['correctedTime'];
    $atime        = $row['aggregateTime'];
    $code         = $row['resultCode'];
    $points       = $row['resultPoints'];
    $notes        = $row['resultNote'];
    $upddate      = $row['updateDate'];
    $updby        = "transfer";
    
    // write to new database
    $insert = db_query($new_conn, "INSERT INTO t_results (`id`,`eventid`,`race`,`race_type`,`competitorid`,`class`,`sailnum`,`py`,`helm`,`crew`,`laps`,`etime`,`ctime`,`atime`,`code`,`points`,`notes`,`upddate`,`updby`) VALUES ('$id','$eventid','$race','$race_type','$competitorid','$class','$sailnum','$py','$helm','$crew','$laps','$etime','$ctime','$atime','$code','$points','$notes','$upddate','$updby')");
    $count++;

}
echo " - added $count records to t_results: <br>";

/* -----------------------------------------------------------------------------------------------------
      RACE FORMAT related information
   -----------------------------------------------------------------------------------------------------
*/
echo "<b>Transferring RACE FORMAT information:</b> <br>";

//
// -- RACE configuration [t_cfgevent, t_cfgstart, t_cfgrace]      ******* ADD HERE
//

$clear = db_query($new_conn, "TRUNCATE t_cfgevent");
echo " - truncating t_cfgevent: <br>";
//$clear = db_query($new_conn, "TRUNCATE t_cfgstart");
//echo " - truncating t_cfgstart: <br>";
$clear = db_query($new_conn, "TRUNCATE t_cfgrace");
echo " - truncating t_cfgrace: <br>";

$result = db_query($old_conn, "SELECT * FROM tbleventcfg WHERE active = 1 ORDER BY eventid");
$count = 0;

while ($row = db_fetchrow($result))
{
    // transfer each active event configuration
    $eventid      = $row['eventid'];
    $profile      = $row['profile'];
    $event_code   = $row['eventacronym'];
    $event_name   = $row['eventname'];
    $event_desc   = $row['eventdesc'];
    $event_type   = $row['eventtype'];
    $numstarts    = $row['numstarts'];
    $numraces     = $row['numraces'];
    $event_lists  = "";
    $active       = $row['active'];
    $comp_pick    = 0;
    $comp_text    = "";
    $comp_default = "";
    $updby        = "transfer";
    
    $insert = db_query($new_conn, "INSERT INTO t_cfgevent (`id`, `event_code`, `event_name`, `event_desc`, `event_type`, `numstarts`, `event_lists`, `active`, `comp_pick`, `updby`) VALUES ('$eventid', '$event_code', '$event_name', '$event_desc', '$event_type', '$numstarts', '$event_lists', '$active', '$comp_pick', '$updby' )");
    $eventcfgid = db_lastinsert($new_conn);
    $count++;
    
    // transfer starts for this event
    $result1 = db_query($old_conn, "SELECT * FROM tblstartcfg WHERE eventid=$eventcfgid ORDER BY startnum");
	
    while ($row1 = db_fetchrow($result1))
    {
        $eventid     = $row1['eventid'];
        $start_code  = "";
        $start_name  = $row1['startname'];
        $start_num   = $row1['startnum'];
        $start_delay = $row1['timediff'];
        $updby       = "transfer";
        //$insert = db_query($new_conn, "INSERT INTO t_cfgstart (`eventcfgid`, `start_code`, `start_name`, `start_num`, `start_delay`, `updby`) VALUES ('$eventid', '$start_code', '$start_name', '$start_num', '$start_delay', '$updby' )");       
        	
        
        // transfer races for this event
        $result2 = db_query($old_conn, "SELECT * FROM tblracecfg WHERE eventid=$eventcfgid and startnum=$start_num ORDER BY racenum");      
        
        while ($row2 = db_fetchrow($result2))
        {
            $eventid      = $row2['eventid'];
            $start_num    = $row2['startnum'];
            $race_num     = $row2['racenum'];
            $race_code    = "";
            $race_name    = $row2['racename'];
            $race_desc    = $row2['racedesc'];
            $race_type    = $row2['racetype'];
            $py_type      = $row2['pytype'];
            $defaultlaps  = $row2['defaultlaps'];
            $defaultfleet = $row2['defaultfleet'];
            $classinc     = $row2['classinc'];
            $onlyinc      = $row2['onlyinc'];
            $classexc     = $row2['classexc'];
            $min_py       = $row2['minpy'];
            $max_py       = $row2['maxpy'];
            $crew         = $row2['crew'];
            $spintype     = $row2['spintype'];
            $hulltype     = $row2['hulltype'];
            $min_helmage  = 0;
            $max_helmage  = 100;
            $min_skill    = 0;
            $max_skill    = 10;
            $updby        = "transfer";
            $insert = db_query($new_conn, "INSERT INTO t_cfgrace (`eventcfgid`, `start_num`, `race_num`, `race_code`, `race_name`, `race_desc`, `race_type`,  `py_type`, `defaultlaps`, `defaultfleet`, `classinc`, `onlyinc`, `classexc`,  `min_py`, `max_py`, `crew`, `spintype`, `hulltype`, `min_helmage`, `max_helmage`, `min_skill`, `max_skill`, `updby`) VALUES ('$eventid', '$start_num', '$race_num', '$race_code', '$race_name', '$race_desc', '$race_type', '$py_type', '$defaultlaps', '$defaultfleet', '$classinc', '$onlyinc', '$classexc', '$min_py', '$max_py', '$crew', '$spintype', '$hulltype', '$min_helmage', '$max_helmage', '$min_skill', '$max_skill', '$updby' )");
            
            
            // transfer races for this event
        }
    }
}
echo " - added $count records to t_eventcfg: <br>";

/* -----------------------------------------------------------------------------------------------------
      HELP/LINKS related information  [t_help, t_link]
   -----------------------------------------------------------------------------------------------------
*/
echo "<b>Transferring HELP/LINKS information:</b> <br>";
$clear = db_query($new_conn, "TRUNCATE t_help");
echo " - truncating t_help: <br>";
$clear = db_query($new_conn, "TRUNCATE t_link");
echo " - truncating t_link: <br>";

$result = db_query($old_conn, "SELECT * FROM tblfaq ORDER BY idfaq");
$count = 0;
while ($row = db_fetchrow($result))		
{
    // transfer
    $id       = $row['idfaq'];
    $category = $row['type'];
    $question = addslashes(htmlspecialchars($row['question']));
    $answer   = addslashes(htmlspecialchars($row['answer']));
    $notes    = addslashes(htmlspecialchars($row['notes']));
    $link1url = $row['link1'];
    $link1lbl = $row['link1title'];
    $link2url = $row['link2'];
    $link2lbl = $row['link2title'];
    $author   = $row['author'];
    $order    = $row['faqorder'];
    
    // write to new database
    $insert = db_query($new_conn, "INSERT INTO t_help (`id`,`category`,`question`,`answer`,`notes`,`link1_url`,`link1_label`,`link2_url`,`link2_label`,`author`,`rank`) VALUES ('$id','$category','$question','$answer','$notes','$link1url','$link1lbl','$link2url','$link2lbl','$author','$order')");
    $count++;

}
echo " - added $count records to t_help: <br>";


$result = db_query($old_conn, "SELECT * FROM tbllink ORDER BY id");
$count = 0;
while ($row = db_fetchrow($result))		
{
    // transfer
    $id       = $row['id'];
    $label    = $row['linkname'];
    $url      = $row['linkurl'];
    $tip      = $row['linktip'];
    $category = $row['linkpage'];
    $order    = $row['linkorder'];
    
    // write to new database
    $insert = db_query($new_conn, "INSERT INTO t_link (`id`,`label`,`url`,`tip`,`category`,`order`) VALUES ('$id','$label','$url','$tip','$category','$order')");
    $count++;

}
echo " - added $count records to t_link: <br>";

echo "<br><br>---- TRANSFER PROCESS COMPLETED"
?>