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
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/lib/util_lib.php");

$db_o    = new DB;                      // database object
$event_o = new EVENT($db_o);            // event object

// find next event
$event = $event_o->get_nextevent(date("Y-m-d"), $requiredtype = "racing");
echo "<pre>".print_r($event,true)."</pre>";
$eventid = $event['id'];


// get entries
$entry_o  = new ENTRY($db_o, $eventid);   // entry object
$num_signons = $entry_o->count_signons("entries");
$entries = $entry_o->get_signons();
echo "<pre>".print_r($entries,true)."</pre>";

//$query = "SELECT action, eventid, competitorid, `chg-crew`, `chg-sailnum`, classid, classname, b.sailnum, helm,
//b.crew from t_entry as a JOIN t_competitor as b ON a.competitorid=b.id JOIN t_class as c ON b.classid=c.id
//WHERE eventid = $eventid
//ORDER BY competitorid ASC, FIELD(action, 'enter', 'update', 'retire')";
//$data = $db_o->db_get_rows($query);

// get latest activity for each competitor
$output = array();
foreach ($entries as $row)
{
    $id = $row['id'];
    if (array_key_exists($id, $output))   // append
    {
        if (strtodate($row['createdate']) > strtodate($output[$id]['createdate']))
        {
            if (empty($row['chg-crew'])) { $row['crewname']= $row['chg-crew'];
            if (empty($row['chg-sailnum'])) { $row['sailnum']= $row['chg-sailnum'];
            $output[$id] = $row;
        }
    }
    else                                                   // create
    {
        if (empty($row['chg-crew'])) { $row['crewname']= $row['chg-crew'];
        if (empty($row['chg-sailnum'])) { $row['sailnum']= $row['chg-sailnum'];
        $output[$id] = $row;
    }
}

// sort array by class and sailnumber
$sorted = u_array_orderby($output, 'classname', SORT_ASC, 'sailnum', SORT_ASC);





$outdata = array();
$d_data = array();
$j = 0;
$r = 0;
$k = 0;
$compid = 0;
echo "<pre><table><tbody>";
foreach ($entries as $row)
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