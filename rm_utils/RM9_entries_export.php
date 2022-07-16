<?php
/**
 * RM9_entries_export.php
 *
 * script to export entries from a racemanager9 database and import them into a racemanager10+ database.
 *
 * Arguments (* required)
 *    eventid   -   event record id *
 *
 */

$loc  = "..";
$page = "rm9_entries_export";
define('BASE', dirname(__FILE__) . '/');

require_once("util_lib.php");
require_once("db_class.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

// source rm9 database connection
$source['db_host'] = "127.0.0.1";
$source['db_user'] = "rmood";
$source['db_pass'] = "syc$2";
$source['db_port'] = "";
$source['db_name'] = "rm_v9race";

// arguments
$eventid = u_checkarg("eventid", "checkintnotzero", "");

// initialisation
$_SESSION = parse_ini_file("common.ini", false);
$_SESSION['sql_debug'] = false;
$_SESSION['syslog'] = "../logs/sys/sys_".date("Y-m-d").".log";

$bufr = "";
if (!$eventid)   // report argument problem and stop
{
    $bufr.= "ERROR! - event record id not specified or not recognised - export cancelled<br>";
}
else
{
    $bufr.= "RM9 to RM10+ entry data transfer for event $eventid...<br>RM9 database: ".$source['db_host']."/".$source['db_name']."<br>";

    // get entries for event from rm9
    $_SESSION['db_host'] = $source['db_host'];
    $_SESSION['db_user'] = $source['db_user'];
    $_SESSION['db_pass'] = $source['db_pass'];
    $_SESSION['db_port'] = $source['db_port'];
    $_SESSION['db_name'] = $source['db_name'];
    $db_o = new DB();        // connect to source database


    $entries = $db_o->db_get_rows("SELECT * FROM tblsignon WHERE eventid = $eventid");
    //echo "<pre>ENTRIES: ".print_r($entries,true)."</pre>";
    if ($entries) {
        $found = count($entries);
        $bufr.= $found . " entries found for this event in the RM9 database<br><br>";

        // transform entries
        $out_str = "TRUNCATE t_entry; INSERT INTO `t_entry` (`action`, `protest`, `status`, `eventid`, `competitorid`, `chg-helm`, `chg-crew`, `chg-sailnum`, `entryid`, `updby`) VALUES";
        $output = array();
        foreach ($entries as $k => $entry) {

            if (!empty($entry['competitorid']))
            {
                $comp = $db_o->db_get_row("SELECT * FROM tblcompetitors WHERE id = {$entry['competitorid']}");
                //echo "<pre>COMP: ".print_r($comp,true)."</pre>";
                $bufr.= " - " . $comp['boatClass'] . " " . $comp['sailNumber'] . " " . $comp['helmName'] . "<br>";

                $output = array(
                    "action" => "enter",
                    "protest" => 0,
                    "status" => "N",
                    "eventid" => $eventid,
                    "competitorid" => $entry['competitorid'],
                    "chg-helm" => $entry['helm'],
                    "chg-crew" => $entry['crew'],
                    "chg-sailnum" => $entry['sailnum'],
                    "entryid" => 0,
                    "updby" => "rm9_transfer"
                );

                $out_str.= "('{$output['action']}', '{$output['protest']}', '{$output['status']}', '{$output['eventid']}', '{$output['competitorid']}', 
                '{$output['chg-helm']}', '{$output['chg-crew']}', '{$output['chg-sailnum']}', '{$output['entryid']}', '{$output['updby']}'),";
            }
            else
            {
                $bufr.=" - skipping entry that does not have a competitor id: {$entry['class']} {$entry['sailnum']} {$entry['helm']}<br>";
            }
        }
        $bufr.= "<pre>".rtrim($out_str, ",")."</pre>";

        /*
        // load entries into rm10+ database
        $_SESSION['db_host'] = $target['db_host'];
        $_SESSION['db_user'] = $target['db_user'];
        $_SESSION['db_pass'] = $target['db_pass'];
        $_SESSION['db_port'] = $target['db_port'];
        $_SESSION['db_name'] = $target['db_name'];
        $db_o = new DB();                                       // connect to targetdatabase

        // clear entries for this event
        $del = $db_o->db_delete("t_entry", array("eventid" => $eventid));

        // insert transformed entries
        $inserted = 0;
        foreach ($entries as $k => $entry) {
            $ins = $db_o->db_insert("t_entry", $output[$k]);
            if ($ins) {
                $inserted++;
            }
        }


        if ($found == $inserted) {
            $bufr .= "<br> ... all records inserted into RM10+ database<br>";
        } else {
            $diff = $found - $inserted;
            $bufr .= "<br> ... WARNING not all records inserted into RM10+ database - $diff failed<br>";
        }
        */
    }
    else
    {
        $bufr.= "no entries found";
    }
}


// report output
echo <<<EOT
<!DOCTYPE html>
<html>
<head>
<style>
body {
    margin-top: 20px;                           /* margin for navbar and footer */
    margin-bottom: 20px;
    font-family: Kalinga, Arial, sans-serif;    /* default font */
    background-color: #FFFFFF;
}
</style>
</head>
<body>
     <h1>RM9 entries export to RM10+</h1><br>
     $bufr
</body>
</html>			 
EOT;






  
  
 