<?php
// checks entries made to rm_sailor without having to load them into rm_racebox
$loc  = "..";
$page = "handicaps";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";
require_once ("{$loc}/common/lib/util_lib.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();


// initialise session if this is first call
if (!isset($_SESSION['util_app_init']) OR ($_SESSION['util_app_init'] === false))
{
    $init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

    if ($init_status)
    {
        // set timezone
        if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

        // start log
        error_log(date('H:i:s')." -- rm_util HANDICAPS --------------------[session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

        // set initialisation flag
        $_SESSION['util_app_init'] = true;
    }
    else
    {
        u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
}

// classes
require_once ("{$loc}/common/classes/db_class.php");

// connect to database
$db_o = new DB();

// check URL arguments
$eventid = u_checkarg("eventid", "set", "", "");
$status = u_checkarg("status", "set", "", "");

// get entries
$where = "1=1 ";

if (!empty($eventid)) { $where.= " and eventid = $eventid"; }
if (!empty($status)) { $where.= " and status = '$status'"; }

//$sql = "SELECT * FROM t_entry WHERE ".$where;
//echo "<pre>$sql</pre>";
$rows = $db_o->db_get_rows("SELECT * FROM t_entry WHERE ".$where);

//echo "<pre>ROWS".print_r($rows,true)."</pre>";

$data = array();
$class_arr = array();
$sailnum_arr = array();

foreach ($rows as $k => $row)
{

    // get competitor record
    $comp = $db_o->db_get_row("SELECT b.classname as class, sailnum, helm, a.crew as crew, club FROM t_competitor as a JOIN t_class as b ON a.classid=b.id WHERE a.id = {$row['competitorid']}");

    $change = false;
    if (!empty($row['chg-helm'])) { $comp['helm'] = $row['chg-helm']; $change = true;}
    if (!empty($row['chg-crew'])) { $comp['crew'] = $row['chg-crew']; $change = true;}
    if (!empty($row['chg-sailnum'])) { $comp['sailnum'] = $row['chg-sailnum']; $change = true;}

    $change ? $change_text = "*" : $change_text = "" ;

    $data[$k] = array("class"=>$comp['class'], "sailnum"=>$comp['sailnum'], "helm"=>$comp['helm'],
                      "crew"=>$comp['crew'], "club"=>$comp['club'], "change"=>$change_text, "entry"=>$row['id'], "status"=>$row['status']);
    $class_arr[$k]= $comp['class'];
    $sailnum_arr[$k] = $comp['sailnum'];
}

array_multisort($class_arr, SORT_ASC, $sailnum_arr, SORT_NUMERIC, $data);

$table_htm = "";
$i = 0;
foreach ($data as $row)
{
    $i++;
    $table_htm.= "<tr ><td style='padding-left: 5px; padding-right: 5px'>$i</td>";

    foreach ($row as $field)
    {
        $table_htm.= "<td style='padding-left: 10px; padding-right: 10px'>$field</td>";
    }
    $table_htm.= "<tr>";
}

$htm = <<<EOT
    <div style="font-family: Kalinga, Arial, Helvetica, sans-serif">
    <h1>ENTRY CHECK </h1>
    <hr>
    <table>
    <tr >
        <thead>
            <th>no.</th>
            <th>class</th>
            <th>sailnum</th>
            <th>helm</th>
            <th>crew</th>
            <th>club</th>
            <th>change</th>
            <th>entry id</th>
            <th>status</th>
        </thead>
    </tr>
        <tbody>
            $table_htm
        </tbody>
    </table>
    </div>
EOT;

echo $htm;




