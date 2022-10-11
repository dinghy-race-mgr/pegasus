<?php
/**
 * RM9_table_export.php
 *
 * script to export data from a racemanager9 database and output a CSV file with data suitable for a racemanager10+ database.
 *
 * Arguments (* required)
 *    table   -   e.g competitors      [currently supports competitors| xxxx]
 *
 */



$loc  = "..";
$page = "rm9 table export";
define('BASE', dirname(__FILE__) . '/');

require_once("../common/lib/util_lib.php");
require_once("../common/classes/db_class.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

// source rm9 database connection
$source['db_host'] = "localhost";
$source['db_user'] = "rmood";
$source['db_pass'] = "syc$2";
$source['db_port'] = "";
$source['db_name'] = "rm_v9race";

// arguments
$table = strtolower($_REQUEST['table']);
if (empty($table) or ($table != "competitors" and $table != "boattypes"))
{
    exit("argument: table not defined or not recognised [$table]");
}

// initialisation
$_SESSION = parse_ini_file("../config/common.ini", false);
$_SESSION['db_name'] = "rm_v9race";
$_SESSION['sql_debug'] = false;
$_SESSION['syslog'] = "../logs/sys/sys_".date("Y-m-d").".log";

// set query and get data
$db_o   = new DB;
$rows = $db_o->db_get_rows(get_query($table));

// get output fields
$out_fields = get_output_fields($table);

// create csvdata
$report = "";
$out_data = array();
foreach($rows as $row)
{
    // run verification checks
    $report.= verify_row($table, $row);

    // map data to new fields
    $out_data[] = map_fields($table, $row);
}

// create output file
create_csv_file("./rm9_comp_transfer.csv", $out_fields, $out_data);

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
     <h2>RM9 competitor export to RM10 CSV</h2><br>
     $report
</body>
</html>			 
EOT;

function get_query($table)
{
    if ($table == "competitors")
    {
        $query = "SELECT * FROM tblcompetitors WHERE visibility > 0 ORDER by id";
    }
    elseif ($table == "tblboatypes")
    {
        $query = "SELECT * FROM tblboattypes ORDER BY idtblboattypes";
    }
    else
    {
        $query = false;
    }

    return $query;
}

function get_output_fields($table)
{
    if ($table == "competitors")
    {
        $fields = array("id", "classid", "boatnum", "sailnum", "boatname", "hullcolour", "helm", "crew", "club",
            "personal_py", "regular", "last_entry", "active", "createdate");

        return $fields;
    }
    else
    {
        $query = false;
    }

    return $query;
}

function map_fields ($table, $row)
{
    if ($table == "competitors")
    {
        $out = array(
            "id"            => $row["id"],
            "classid"       => $row["classID"],
            "boatnum"       => $row["sailNumber"],
            "sailnum"       => $row["sailNumber"],
            "boatname"      => "",
            "hullcolour"    => "",
            "helm"          => $row["helmName"],
            "crew"          => $row["crewName"],
            "club"          => $row["club"],
            "personal_py"   => 0,
            "regular"       => "",
            "last_entry"    => $row["lastRaced"],
            "active"        => 1,
            "createdate"    => date("Y-m-d")
        );
    }
    else
    {
        $out = false;
    }

    return $out;
}

function create_csv_file($filepath, $out_fields, $rows)
{
    // now create csv file
    $fp = fopen($filepath, 'wb');

    fputcsv($fp, $out_fields, ',');

    foreach ($rows as $k=>$v)
    {
        fputcsv($fp, $v, ',');
    }

    fclose($fp);

    echo "Created CSV file at: $filepath<br>";

}

function verify_row($table, $row)
{
    $txt = "";
    $report = "";
    if ($table == "competitors")
    {
        if ($row['boatNumber'] != $row['sailNumber'])
        {
            $txt.= "boatnum [{$row['boatNumber']}] different from sailnum [{$row['sailNumber']}]<br>";
        }



    }
    if (!empty($txt))
    {
        $report = "ISSUE id: {$row['id']} ". $txt;
    }

    return $report;
}







  
  
 