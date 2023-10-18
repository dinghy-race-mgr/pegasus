<?php
/*
 * Creates SQL insert instructions to merge data from two races in t_results to create a virtual race (vrace)
 * Allows fields to be overwritten with different values in the vrace sql
 *
 */

$loc        = "..";
$page       = "merge_t_result_series";
$scriptname = basename(__FILE__);
$stop_here  = true;

require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once("{$loc}/common/classes/event_class.php");
require_once("{$loc}/common/classes/raceresult_class.php");
require_once("{$loc}/common/classes/seriesresult_class.php");

session_start();

$_SESSION['db_host'] = "localhost";
$_SESSION['db_user'] = "rmuser";
$_SESSION['db_pass'] = "pegasus";
$_SESSION['db_port'] = "3306";
$_SESSION['db_name'] = "pegasus_test";
$_SESSION['sql_debug'] = false;
$_SESSION['sys_log'] = "../logs/sys/merge_results.log";
$_SESSION['error_log'] = "../logs/dbg/merge_results.log";
$_SESSION['dbglog'] = "../logs/dbg/merge_resultsdbg.log";
$_SESSION['result_public_url'] = "../logs/dbg";


// parameters for this run

//// Easter Series
//$new_eventid = 999003;
//$name = "Easter Series 2023";
//$races = "10545,10546,10653";
//$series_code = "EASTER-23";
//$series_status = "final";


// Kathleen Series
$new_eventid = 999004;
$name = "Kathleen/Buxton Belle Series 2023";
$races = "10600, 10601, 10663";
$series_code = "KATHLEEN-23";
$series_status = "final";


$note = "VRR - ".date("Y-m-d");
$race_type = "average";
$fields = <<<EOT
eventid,fleet,race_type,competitorid,class,sailnum,pn,apn,helm,crew,lap,etime,ctime,atime,points,note,updby
EOT;

$db_o = new DB;

echo "<pre>AGGREGATING SUB-SERIES RESULTS ... </pre>";
echo "<pre><h2>$name [$new_eventid]</h2></pre>";

// options for series result display
$opts = array(
    "inc-pagebreak" => 0,                                          // page break after each fleet
    "inc-codes"     => 1,                                          // include key of codes used
    "inc-club"      => 0,                                          // include club name for each competitor
    "inc-turnout"   => 0,                                            // include turnout statistics
    "race-label"    => 1,                                          // use race number or date for labelling races
    "club-logo"     => "../../club_logo.jpg",                                             // if set include club logo
    "styles" => "../config/style/result_classic.css"    // styles to be used
);

$series_o = new SERIES_RESULT($db_o, $series_code, $opts, true);

// set data for series result
$err_detail = "";
$err = $series_o->set_series_data($races);
echo "<pre>set_series_data: ".print_r($err,true)."</pre>";

// calculate series result
$err = $series_o->calc_series_result();
echo "<pre>calc_series_result: ".print_r($err,true)."</pre>";

// get results
$results = $series_o->get_results_data();

// now use this->results to get the SQL INSERT statement
$values = "";
$count = 0;
foreach ($results['fleets'] as $k => $fleet)
{
    foreach($fleet['sailors'] as $j => $sailor)
    {
        $arr = explode(" \ ", $sailor['team']);
        if (count($arr) <= 1)
        {
            $helm = $sailor['team'];
            $crew = "";
        }
        else
        {
            $helm = $arr[0];
            $crew = $arr[1];
        }

        $values.=<<<EOT
        ($new_eventid,$k,'$race_type',{$sailor['compid']},'{$sailor['class']}','{$sailor['sailnum']}',1000,1000,'$helm','$crew',1,1000,1000,1000,{$sailor['posn']},'$note',"VRR_create"),\n
EOT;
        $count++;
    }
}
$values = rtrim($values,",\n").";";

echo "<pre>results output: $count boats</pre>";

$sql_txt = "INSERT INTO t_result ($fields) VALUES \n $values";
echo "<pre>$sql_txt</pre>";

echo "<pre>".print_r($results,true)."</pre>";

