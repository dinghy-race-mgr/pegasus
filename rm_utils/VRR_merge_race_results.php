<?php
/*
 * Creates SQL insert instructions to merge data from two races in t_results to create a virtual race (vrace)
 * Allows fields to be overwritten with different values in the vrace sql
 *
 */

$loc        = "..";
$page       = "merge_t_result_pursuit";
$scriptname = basename(__FILE__);
$stop_here  = true;

require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/classes/db_class.php");

session_start();

$_SESSION['db_host'] = "localhost";
$_SESSION['db_user'] = "rmuser";
$_SESSION['db_pass'] = "pegasus";
$_SESSION['db_port'] = "3306";
$_SESSION['db_name'] = "pegasus_test";
$_SESSION['sql_debug'] = false;
$_SESSION['sys_log'] = "../logs/sys/merge_results.log";
$_SESSION['error_log'] = "../logs/sys/merge_results.log";




// parameters for this run

// Patrick Kelley + Dunsford 2023
//$new_eventid = 999001;
//$name = "Patrick Kelley + Dunsford 2023";
//$race_1 = array("eventid"=> 10570, "fleet_in"=> 1, "fleet_out"=> 1);
//$race_2 = array("eventid"=> 10651, "fleet_in"=> 1, "fleet_out"=> 2);

// RNLI + Rough/Tumble 2023
$new_eventid = 999002;
$name = "RNLI + Rough/Tumble 2023";
$race_1 = array("eventid"=> 10634, "fleet_in"=> 1, "fleet_out"=> 1);
$race_2 = array("eventid"=> 10652, "fleet_in"=> 1, "fleet_out"=> 2);

$note = "VRR - ".date("Y-m-d");
$race_type = "average";
$fields = <<<EOT
eventid,fleet,race_type,competitorid,class,sailnum,pn,apn,helm,crew,club,lap,etime,ctime,atime,code,penalty,points,declaration,note,upddate,updby,createdate
EOT;
$text_fields = array("race_type","class","sailnum","helm","crew","club","code","declaration","note","upddate","updby","createdate");



$db_o = new DB;

echo "<pre>MERGING DINGHY & CATAMARAN RESULTS ... </pre>";
echo "<pre><h2>$name [$new_eventid]</h2></pre>";

// fleet 1
$rst_1 = $db_o->db_get_rows("select * from t_result where eventid = {$race_1['eventid']} and fleet = {$race_1['fleet_in']} ORDER BY points ASC");
echo "<pre>records from race 1: ".count($rst_1)."</pre>";

$values = "";
foreach ($rst_1 as $k => $row)
{
    unset ($row['id']);
    $row['eventid']   = $new_eventid;
    $row['fleet']     = $race_1['fleet_out'];
    $row['race_type'] = $race_type;
    $row['note']      = $note;

    $values.= "(";
    $bufr = "";
    foreach ($row as $fld => $data)
    {
        if (array_search($fld, $text_fields) !== false) {
            $bufr .= "'$data',";
        } elseif ($fld == "apn") {
            $bufr .= "null,";
        } else {
            $bufr .= "$data,";
        }
    }
    $values.= rtrim($bufr, ",")."),\n";
}
$values = rtrim($values,",\n").";";

$sql_txt = "INSERT INTO t_result ($fields) VALUES \n $values";
echo "<pre>$sql_txt</pre>";


// fleet 2
$rst_2 = $db_o->db_get_rows("select * from t_result where eventid = {$race_2['eventid']} and fleet = {$race_2['fleet_in']} ORDER BY points ASC");
echo "<pre>records from race 2: ".count($rst_2)."</pre>";

$values = "";
foreach ($rst_2 as $k => $row)
{
    unset ($row['id']);
    $row['eventid'] = $new_eventid;
    $row['fleet'] = $race_2['fleet_out'];
    $row['race_type'] = $race_type;
    $row['note'] = $note;

    $values.= "(";
    $bufr = "";
    foreach ($row as $fld => $data)
    {
        if (array_search($fld, $text_fields) !== false) {
            $bufr .= "'$data',";
        } elseif ($fld == "apn") {
            $bufr .= "null,";
        } else {
            $bufr .= "$data,";
        }
    }
    $values.= rtrim($bufr, ",")."),\n";
}
$values = rtrim($values,",\n").";";

$sql_txt = "INSERT INTO t_result ($fields) VALUES $values";
echo "<pre>$sql_txt</pre>";

