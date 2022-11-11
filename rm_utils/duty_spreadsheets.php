<?php
/*
 * duty_spreadsheets.php
 *
 * Creates a spreadsheet for rota managers to create an import file for duty allocations
 *
 * parameters:
 *     type     string   ood|safety|galley               required
 *     start    string   start date yyyy-mm-dd           required
 *     end      string   end date yyyy-mm-dd             required
 *
 * usage:
 *    https:www.starcrossyc.org.uk/pegasus_stx/rm_utils/duty_spreadsheets.php?type=ood&start=2022-01-01&end=2022-12-31
 *
 * output:
 *    https:www.starcrossyc.org.uk/pegasus_stx/data/programme/duty_spreadsheet_ood.csv
 */


// need to configure $file_url

require_once("../common/lib/util_lib.php");
require_once("../common/classes/db_class.php");

$file_path = "..";
$file_url = "https://www.starcrossyc.org.uk/pegasus_stx";                    //$file_url = "http://localhost/pegasus";
$cfg = array(
    "ood" => array( "repeat"=>2, "duty"=>"ood", "pattern" => "ood|aood"),
    "safety" => array( "repeat"=>2, "duty"=>"safety-driver", "pattern" => "safety-driver|safety-driver"),
    "galley" => array( "repeat"=>3, "duty"=>"galley", "pattern" => "galley|galley|bar")
);
$cols = array("eventid", "event_name", "event_date", "event_start", "duty_type", "person", "notes");

$type = $_REQUEST['type'];
$start = $_REQUEST['start'];
$end = $_REQUEST['end'];
$pattern = explode("|", $cfg[$type]['pattern']);
$file = "data/programme/duty_spreadsheet_$type.csv";
$start = date("Y-m-d", strtotime($start));
$end = date("Y-m-d", strtotime($end));

$_SESSION = parse_ini_file("../config/common.ini", false);
$_SESSION['sql_debug'] = false;

$db_o = new DB();

$where = array(
    "ood" => " event_date >= '$start' and event_date <= '$end' and event_type in ('racing', 'dcruise','freesail')",
    "safety" => " event_date >= '$start' and event_date <= '$end' and event_type in ('racing', 'dcruise','freesail')",
    "galley" => " event_date >= '$start' and event_date <= '$end' and event_type in ('racing', 'social','freesail')"
);

$where_str = "event_date >= '$start' and event_date <= '$end'";
$sql = "SELECT * FROM t_event WHERE {$where[$type]} ORDER BY event_date ASC, event_order ASC";
$rs = $db_o->db_get_rows($sql);
$num_events = count($rs);


// output settings
echo <<<EOT
<pre>
DUTY SPREADSHEETS ------------------------
duty type = $type
start date = $start
end date = $start
no. of events = $num_events
pattern = {$cfg[$type]['pattern']}
file = $file
file_path = $file_path/$file
file_link = $file_url/$file
sql = $sql
</pre><br><br>
EOT;

$rows = array();
foreach ($rs as $event)
{
    $row = array(
        "eventid" => $event['id'],
        "event_name" => $event['event_name'],
        "event_date" => $event['event_date'],
        "event_start" => $event['event_start'],
        "duty_type" => $cfg[$type]['duty'],
        "person" => "",
        "notes" => ""
    );
    for ($i=0; $i < $cfg[$type]['repeat']; $i++)
    {
        $row['duty_type'] = $pattern[$i];
        $rows[] = $row;
    }
    echo ".";
}

$status = create_csv_file($file_path."/".$file, $cols, $rows);
echo <<<EOT
<br><a href='$file_url/$file'>Get CSV file here</a><br><hr><br>
EOT;

exit("exit status - ".$status);


function create_csv_file($file, $cols, $rows)
{
$status = "0";
$fp = fopen($file, 'w');
if (!$fp) { $status = "1"; }

if ($fp)
{
$r = fputcsv($fp, $cols, ',');
if (!$r) { $status = "2"; }

foreach ($rows as $row)
{
if ($status != "0") { break; }
$r = fputcsv($fp, $row, ',');
if (!$r) {$status = "3"; }
}
fclose($fp);
}

return $status;
}