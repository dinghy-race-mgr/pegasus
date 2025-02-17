<?php
/**
 * test-switch-entries.php
 *
 * script to support parallel testing of the rm_racebox application
 * takes entries from source database for the live application and creates sql statemements to create the same entries
 * on target test database.
 *
 * This script needs to be run on the server running source database.  The output sql statements then need to be
 * applied to the target database using phpmyadmin or equivalent
 *
 * IMPORTANT:  This script will only collect entries made through the rm_sailor interface
 *
 * Arguments (* required)
 *    eventid   int   event record id *
 *    switchid  int   target event id (if not provided or 0 - the target event is the same as eventid
 *
 */

$loc  = "..";
$page = "test_switch_entries";
define('BASE', dirname(__FILE__) . '/');

require_once("$loc/common/lib/util_lib.php");
require_once("$loc/common/classes/db_class.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

// arguments
$eventid = u_checkarg("eventid", "checkintnotzero", "");
$switchid = u_checkarg("switchid", "checkintnotzero", "");
if (!$switchid) { $switchid = $eventid; }

// initialisation
$_SESSION = parse_ini_file("$loc/config/common.ini", false);
$_SESSION['sql_debug'] = false;
$_SESSION['syslog'] = "../logs/sys/sys_".date("Y-m-d").".log";
$sql_str = "TRUNCATE t_entry; <br>INSERT INTO `t_entry` (`action`, `protest`, `status`, `eventid`, `competitorid`, `chg-helm`, `chg-crew`, `chg-sailnum`, `entryid`, `updby`) VALUES<br>";

$db_o = new DB();        // connect to source database

$bufr = "";
if (!$eventid)           // report missing reuired argument problem and stop
{
    $bufr.= "<h3>ERROR! - event record id not specified or not recognised - export cancelled<br></h3>";
}
else
{
    // get event details
    $event = $db_o->db_get_row("SELECT * FROM t_event WHERE id = $eventid");

    if (empty($event))
    {
        // report requested event not known
        $bufr.= <<<EOT
        <h3>Request event $eventid - <span style="color: darkred;">not known...</span></h3>
        <p>[ source database: {$_SESSION['db_host']} / {$_SESSION['db_name']} ]</p><br>
EOT;
    }
    else
    {
        // report source and target events (and database identification
        $eventid == $switchid ? $switch_txt = "" : $switch_txt = "<p>switching to entries to event $switchid</p>";
        $bufr.= <<<EOT
        <h3>Entry data transfer for event $eventid - {$event['event_name']}...</h3>
        $switch_txt
        <p>[ source database: {$_SESSION['db_host']} / {$_SESSION['db_name']} ]</p><br>
EOT;

        // get current entries
        $entries = $db_o->db_get_rows("SELECT * FROM t_entry WHERE eventid = $eventid");
        $num_entries = count($entries);

        if ($entries)
        {
            $output = array();
            $skipped = 0;
            $val_str = "";
            foreach ($entries as $k => $entry)
            {
                if (!empty($entry['competitorid']))
                {
                    $comp = $db_o->db_get_row("SELECT `classname`, `sailnum`, `helm` FROM t_competitor as a  JOIN t_class as b ON a.classid = b.id WHERE a.id = {$entry['competitorid']}");
                    $bufr .= " - " . $comp['classname'] . " " . $comp['sailnum'] . " " . $comp['helm'] . "<br>";

                    empty($entry['chg-helm']) ? $helm = "": $helm = $entry['chg-helm'];
                    empty($entry['chg-crew']) ? $crew = "": $crew = $entry['chg-sailnum'];
                    empty($entry['chg-sailnum']) ? $sailnum = "": $sailnum = $entry['chg-sailnum'];

                    $output = array(
                        "action"       => $entry['action'],
                        "protest"      => 0,
                        "status"       => "N",
                        "eventid"      => $switchid,
                        "competitorid" => $entry['competitorid'],
                        "chg-helm"     => $helm,
                        "chg-crew"     => $crew,
                        "chg-sailnum"  => $sailnum,
                        "entryid"      => 0,
                        "updby"        => $page
                    );

                    $val_str.= <<<EOT
('{$output['action']}', '{$output['protest']}', '{$output['status']}', '{$output['eventid']}', '{$output['competitorid']}', '{$output['chg-helm']}', '{$output['chg-crew']}', '{$output['chg-sailnum']}', '{$output['entryid']}', '{$output['updby']}'),\n
EOT;
                }
                else
                {
                    $skipped++;
                    $bufr.= " - skipping entry - competitor id not found: {$entry['class']} {$entry['sailnum']} {$entry['helm']}<br>";
                }
            }

            // report counts
            $bufr.= <<<EOT
            <p> - $num_entries entries found for this event</p>
            <p> - $skipped records skipped - details not found</p>
            <p>----------------- select text between the dotted lines ---------------------------<br> </p>
EOT;

            // add sql statements
            rtrim($val_str, ",");
            $bufr .= "<pre>" . $sql_str . rtrim($val_str, ",\n") . "; </pre>";

            // add instructions for transfer
            $bufr .= <<<EOT
            <p>----------------------------------------------------------------------------------<br> </p>
            <p>Now do the following ... 
                <ul>
                    <li>select and copy the text between the two dotted lines</li>
                    <li>open phpmyadmin on the server being tested</li>
                    <li>select the database you are using for the testing</li>
                    <li>select the SQL tab - past the copied text into the text area - click go</li>
                    <li>select the t_entry table - it should have the number of entries you are expecting</li>
                    <li>now open the racemanager racebox application on the server being tested</li>
                    <li>navigate to the entries page - click the red button at top right and the entries will load</li>
                </ul>
            </p>
EOT;
        }
        else
        {
            $bufr .= "<h3>Sorry - no entries found for event</h3>";
        }
    }

}
// Output Page
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
     <h2>Transfer Entries to Test Server</h2>
     $bufr
</body>
</html>			 
EOT;








  
  
 