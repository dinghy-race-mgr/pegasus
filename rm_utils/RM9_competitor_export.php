<?php
/**
 * RM9_competitor_export.php
 *
 * script to export entries from a racemanager9 database and import them into a racemanager10+ database.
 *
 * Arguments (* required)
 *    eventid   -   event record id *
 *
 */

$loc  = "..";
$page = "rm9 entries export";
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
$compid = u_checkarg("compid", "checkintnotzero", "");

// initialisation
$_SESSION = parse_ini_file("common.ini", false);
$_SESSION['sql_debug'] = false;
$_SESSION['syslog'] = "../logs/sys/sys_".date("Y-m-d").".log";

$bufr = "";
if (!$compid)   // report argument problem and stop
{
    $bufr.= "ERROR! - competitor id not specified or not recognised - export cancelled<br>";
}
else {
    $bufr .= "RM9 to RM10+ competitor record transfer for id $compid...<br>RM9 database: " . $source['db_host'] . "/" . $source['db_name'] . "<br>";

    // get entries for event from rm9
    $_SESSION['db_host'] = $source['db_host'];
    $_SESSION['db_user'] = $source['db_user'];
    $_SESSION['db_pass'] = $source['db_pass'];
    $_SESSION['db_port'] = $source['db_port'];
    $_SESSION['db_name'] = $source['db_name'];
    $db_o = new DB();        // connect to source database

    $comp = $db_o->db_get_row("SELECT * FROM tblcompetitors WHERE id = $compid");

    if ($comp) {
        $bufr .= "INSERT INTO t_competitor (`id`,`classid`,`boatnum`,`sailnum`,`helm`,`crew`,`club`,`active`) VALUES 
                ({$comp['id']}, {$comp['classID']}, '{$comp['boatNumber']}', '{$comp['sailNumber']}', '{$comp['helmName']}', '{$comp['crewName']}', '{$comp['club']}', 1)";
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
     <h1>RM9 competitor export to RM10+</h1><br>
     $bufr
</body>
</html>			 
EOT;








  
  
 