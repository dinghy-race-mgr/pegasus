<?php
/* ----------------------------------------------------------------------------------------------
   signon_mapping.php

   script to take signon data generated in rm_sailor application and produce an import file for adding entries to
   the raceManager9 application.

   most parameters hardcoded
*/

$loc  = "..";
$page = "signon_mapping";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
session_start();
// configuration - rm_sailor database
$_SESSION['db_host'] = "127.0.0.1";
$_SESSION['db_user'] = "rmuser";
$_SESSION['db_pass'] = "pegasus";
$_SESSION['db_name'] = "pegasus";
$_SESSION['db_port'] = "3306";

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/lib/util_lib.php");

// get parameters
empty($_REQUEST['eventid']) ? $eventid = false : $eventid = (int)$_REQUEST['eventid'];
if (!$eventid)  { exit("STOPPING - eventid not specified"); }
empty($_REQUEST['mapevent']) ? $mapevent = $eventid : $mapevent = (int)$_REQUEST['mapevent'];


// connect to database
$db_o = new DB();

echo "<pre>SIGNON MAPPING for event: $eventid (mapped to RM9 event $mapevent)</pre>>";

$query = "SELECT * FROM t_entry WHERE eventid = $eventid ORDER BY competitorid ASC, createdate ASC ";
//echo $query."<br>";
$data = $db_o->db_get_rows($query);

$outdata = array();
$j = 0;
$r = 0;
$k = 0;
$compid = 0;
foreach ($data as $row)
{
    if ((int)$row['competitorid'] != $compid and $compid != 0 and !empty($out))
    {
        $j++;
        // changing boat - output data for previous boat
        $outdata[$j] = $out;
        unset($out);
        echo "output </pre>";
    }

    if ($row['action'] == "enter")
    {
        $k++;
        $compid = (int)$row['competitorid'];
        $out = array(
            "status" => "new",
            "loaded" => 0,
            "change" => "none",
            "eventid" => $mapevent,
            "competitorid" => $compid,
            "sailnum" => "",
            "crew" => "",
            "updateby"  => "rm_sailor",
        );

        if (!empty($row['chg-crew'])) { $out['crew'] = $row['chg-crew']; }
        if (!empty($row['chg-sailnum'])) { $out['sailnum'] = $row['chg-sailnum']; }

        echo "<pre>$k - Processing for comp:$compid - ";
    }
    elseif ($row['action'] == "update" and (int)$row['competitorid'] == $compid )
    {
        if (!empty($row['chg-crew'])) { $out['crew'] = $row['chg-crew']; }
        if (!empty($row['chg-sailnum'])) { $out['sailnum'] = $row['chg-sailnum']; }
        if (!empty($out['crew']) or !empty($out['sailnum']))
        {
            $out['change'] = "temp";
        }
        echo "updating - ";
    }
    elseif ($row['action'] == "retire" and (int)$row['competitorid'] == $compid )
    {
        unset($out);
        $r++;
        echo "retired </pre>";
    }
}

if (!empty($out))
{
    $j++;
    // changing boat - output data for previous boat
    $outdata[$j] = $out;
    unset($out);
    echo "output </pre>";
}

echo "<pre>records processed: ".count($data)."| entries made:$j | retirements: $r</pre>";

echo "<pre>".print_r($outdata,true)."</pre>";

// output sql

$sql = "INSERT INTO `tblsignon` (`status`,`loaded`,`change`,`eventid`,`competitorid`,`sailnum`,`crew`,`updateby`) VALUES ";
foreach ($outdata as $row)
{
    empty($row['crew']) ? $crew = "NULL" : $crew = "'{$row['crew']}'";
    empty($row['sailnum']) ? $sailnum = "NULL" : $sailnum = "'{$row['sailnum']}'";

    $sql.= "('{$row['status']}', {$row['loaded']}, '{$row['change']}', {$row['eventid']}, {$row['competitorid']},$sailnum, $crew, '{$row['updateby']}'),";
}
$sql = rtrim($sql,",").";";

echo "<pre>".print_r($sql,true)."</pre>";

exit("<pre>COMPLETE</pre>");

