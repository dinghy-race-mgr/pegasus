<?php
/* ----------------------------------------------------------------------------------------------
   FIXME  - this function doesn't appear to be used anywhere
   FIXME  - appears to be getting a race entry list from the t_entry table for next event

*/

$loc  = "..";
$page = "entries";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// start session
session_id('sess-rmsailor');
session_start();

// initialise page
session_unset();
$_SESSION['mode'] = "race";

// initialisation for application
$init_status = u_initialisation("$loc/config/rm_sailor_cfg.php", $loc, $scriptname);

// timezone
date_default_timezone_set($_SESSION['timezone']);   // set timezone

// error reporting - full for development
$_SESSION['sys_type'] == "live" ? error_reporting(E_ERROR) : error_reporting(E_ALL);

$tmpl_o = new TEMPLATE(array( "./templates/layouts_tm.php", "./templates/entry_list_tm.php"));

$db_o    = new DB;                      // database object
$event_o = new EVENT($db_o);            // event object

// get ini variables from database
foreach ($db_o->db_getinivalues(true, "club") as $k => $v) {
    $_SESSION[$k] = $v;
}

// find next event
$event = $event_o->get_nextevent(date("Y-m-d"), $requiredtype = "racing");
$eventid = $event['id'];

// get entries (sorted by competitor id and date)
$entry_o  = new ENTRY($db_o, $eventid);
$entries = $entry_o->get_signons("all");

// order by class, competitor id, create date
$entries = u_array_orderby($entries, 'classname', SORT_ASC, 'id', SORT_ASC, 'createdate', SORT_ASC);

// consolidate records for the same boat - create status
$last_comp = 0;

foreach ($entries as $k=>$entry)
{
    if ($entry['id'] == $last_comp)
    {
        unset($entries[$k-1]);  // remove obsolete entry record for this competitor
    }

    // deal with updates
    if (!empty($entry['chg_helm'])) { $entries[$k]['helmname'] = ucwords($entry['chg_helm']); }
    if (!empty($entry['chg_crew'])) { $entries[$k]['crewname'] = ucwords($entry['chg_crew']); }
    if (!empty($entry['chg_sailnum'])) { $entries[$k]['sailnum'] = ucwords($entry['chg_sailnum']); }

    $last_comp = $entry['id'];
}

// process entries (order by )
$_SESSION['pagefields'] = array(
    "title" => "rm_sailor",
    "theme" => $_SESSION['sailor_theme'],
    "background" => $_SESSION['background'],
    "loc" => $loc,
    "stylesheet" => "./style/rm_sailor.css",
    "header-left" => "raceManager SAILOR",
    "header-center" => "Entries Next Race",
    "header-right" => "",
    "body" => $tmpl_o->get_template("display_entries", array(), array("entries"=>$entries, "event" => $event)),
    "footer-left" => $_SESSION['clubname']."<br>".$_SESSION['sys_copyright'].": ".$_SESSION['sys_name'] ." ". $_SESSION['sys_version'],
    "footer-center" => "",
    "footer-right" => ""
);

echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields'] );
exit();


/*

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
*/