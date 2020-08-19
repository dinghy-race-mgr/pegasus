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

echo "<pre>SIGNON MAPPING for event: $eventid (mapped to RM9 event $mapevent)</pre>";

$query = "SELECT action, eventid, competitorid, `chg-crew`, `chg-sailnum`, classid, classname, b.sailnum, helm, 
b.crew from t_entry as a JOIN t_competitor as b ON a.competitorid=b.id JOIN t_class as c ON b.classid=c.id
ORDER BY competitorid ASC, FIELD(action, 'enter', 'update', 'retire')";
$data = $db_o->db_get_rows($query);

$outdata = array();
$d_data = array();
$j = 0;
$r = 0;
$k = 0;
$compid = 0;
echo "<pre><table><tbody>";
foreach ($data as $row)
{
    if ((int)$row['competitorid'] != $compid and $compid != 0)
    {
        $j++;
        // changing boat - output data for previous boat
        if(!empty($out))
        {
            $outdata[$j] = $out;
        }
        unset($out);
        display_row($d_data);
        unset($d_data);
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

        empty($row['chg-crew']) ?  $crew = $row['crew'] : $crew = "{$row['crew']} [{$row['chg-crew']}]";
        empty($row['chg-sailnum']) ? $sailnum = $row['sailnum'] : $sailnum = "{$row['sailnum']} [{$row['chg-sailnum']}]";

        $d_data = array(
            "class" => $row['classname'],
            "sailnum" => $sailnum,
            "helm"    => $row['helm'],
            "crew"    => $crew,
            "id"      => $compid,
            "action"  => "enter"
        );

        if (!empty($row['chg-crew'])) { $out['crew'] = $row['chg-crew']; }
        if (!empty($row['chg-sailnum'])) { $out['sailnum'] = $row['chg-sailnum']; }
    }
    elseif ($row['action'] == "update" and (int)$row['competitorid'] == $compid )
    {
        if (!empty($row['chg-crew'])) { $out['crew'] = $row['chg-crew']; }
        if (!empty($row['chg-sailnum'])) { $out['sailnum'] = $row['chg-sailnum']; }
        if (!empty($out['crew']) or !empty($out['sailnum']))
        {
            $out['change'] = "temp";
        }
        $d_data['action'].= " - update";
    }
    elseif ($row['action'] == "retire" and (int)$row['competitorid'] == $compid )
    {
        unset($out);
        $r++;
        $d_data['action'].= " - <b>RETIRED</b>";
    }
}

if (!empty($out))
{
    $j++;
    // changing boat - output data for previous boat
    $outdata[$j] = $out;
    unset($out);
    display_row($d_data);
}

echo "</tbody></table></pre>";

echo "<pre>records processed: ".count($data)."| entries made:$j | retirements: $r</pre>";

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

function display_row($data)
{
    echo <<<EOT
    <tr>
        <td>{$data['class']}</td>
        <td>{$data['sailnum']}</td>
        <td>{$data['helm']}</td>
        <td>{$data['crew']}</td>
        <td>{$data['id']}</td>
        <td>{$data['action']}</td>
    </tr>
EOT;

}