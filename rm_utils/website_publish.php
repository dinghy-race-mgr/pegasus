<?php
/*
Creates a json file including all of the published events and transfers the file to the website
*/
$loc  = "..";
$page = "website_publish";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";

require_once ("{$loc}/common/lib/util_lib.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

// initialise session if this is first call
$init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

if ($init_status)
{
    // set timezone
    if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

    // start log
    error_log(date('d-M H:i:s')." -- rm_util PUBLISH PROGRAMME TO WEBSITE  ------- [session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

    // set initialisation flag
    $_SESSION['util_app_init'] = true;
}
else
{
    u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// connect to database
$db_o = new DB();
foreach ($db_o->db_getinivalues(false) as $data)
{
    $_SESSION["{$data['parameter']}"] = $data['value'];
}

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php","./templates/publish_tm.php"));

if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

$server_txt = "{$_SESSION['db_host']}/{$_SESSION['db_name']}";
$pagefields = array(
    "loc" => $loc,
    "theme" => $styletheme,
    "stylesheet" => $stylesheet,
    "title" => "Programme Transfer",
    "header-left" => $_SESSION['sys_name']." <span style='font-size: 0.4em;'>[$server_txt]</span>",
    "header-right" => "Website Publish",
    "body" => "",
    "footer-left" => "",
    "footer-center" => "",
    "footer-right" => "",
);

/* ------------ file selection page ---------------------------------------------*/
$state = 0;
if ($_REQUEST['pagestate'] != "init" AND $_REQUEST['pagestate'] != "submit")
{
    $state = 2;
}
elseif ($_REQUEST['pagestate'] == "init")
{
    // setup debug
    array_key_exists("debug", $_REQUEST) ? $params['debug'] = $_REQUEST['debug'] : $params['debug'] = "off" ;

    $formfields = array(
        "instructions" => "Creates a file containing the currently published events in the programme and optionally transfers the file to the website<br></br>
                           Set the start and end date for the events to be included - if left blank all published events will be included.",
        "script"       => "website_publish.php?pagestate=submit",
    );

    $pagefields['body'] = $tmpl_o->get_template("publish_form", $formfields, array("action" => false));
}

/* ------------ submit page ---------------------------------------------*/

elseif (strtolower($_REQUEST['pagestate']) == "submit")
{
    $state = 0;
    $count = 0;

    require_once("{$loc}/common/classes/event_class.php");
    require_once("{$loc}/common/classes/rota_class.php");
    $event_o = new EVENT($db_o);
    $rota_o = new ROTA($db_o);

    $file_status = false;
    $transfer_status = false;

    // set dates for programme content
    if (empty($_REQUEST['date-start']) OR strtotime($_REQUEST['date-start']) === false) {
        $first = date("Y-m-d", strtotime("-30 days"));   // start from previous month if no start date set
    } else {
        $first = date("Y-m-d", strtotime($_REQUEST['date-start']));  // use specified start month        
    }

    if (empty($_REQUEST['date-end']) OR strtotime($_REQUEST['date-end']) === false) {
        $last = date("Y-m-d", strtotime("+13 months"));    // a year from now if no end date set
    } else {
        $last = date("Y-m-d", strtotime($_REQUEST['date-end']));      // use specified end date
    }

    $display_data = array();
    $file_status = create_programme_file($first, $last);
    if ($file_status === false) { $state = 4; }

    //echo "<pre>DBG $file_status|$state|{$_SESSION['publish']['transfer_loc']}|$sourcefile|$targetfile|</pre>";

    if (empty($_SESSION['publish']['transfer_loc']))
    {
        $transfer_status = 0;      // transfer not required
    }
    else
    {
        if ($file_status !== false)   // we have a file that should be transferred
        {
            $sourcefile = $_SESSION['publish']['loc']."/programme_latest.json";
            $targetfile = $_SESSION['publish']['transfer_loc']."/"."programme_latest.json";

            $transfer_status = transfer_programme($sourcefile, $targetfile);
        }
    }

    $params = array(
        "display" => true,
        "state" => $state,
        "start" => $first,
        "end"   => $last,
        "count" => count($display_data),
        "file" => $file_status,
        "transfer" => $transfer_status,
        "data" => $display_data);
    $pagefields['body'] = $tmpl_o->get_template("publish_file_report", array(),$params);
}

if ($state == 2 OR $state == 3 )  // deal with error conditions
{
    $pagefields['body'] = $tmpl_o->get_template("publish_state", array(), array("state"=>$state, "args"=>$_REQUEST));
}

echo $tmpl_o->get_template("basic_page", $pagefields );



function create_programme_file($start_date, $end_date)
// creates json file containing programme information
{
    global $event_o, $rota_o, $db_o;
    global $display_data;

    $status = false;

    // get event types
    $rs = $db_o->db_getsystemcodes("event_type");
    $event_types = array();
    foreach ($rs as $event) {
        $event_types["{$event['code']}"] = $event['label'];
    }

    // get race format info
    $rs = $db_o->db_get_rows("SELECT `id`, `race_code`, `race_name`, `race_desc` FROM t_cfgrace WHERE `active` = 1");
    $race_types = array();
    foreach ($rs as $race) {
        $race_types["{$race['race_name']}"] = array(
            "code" => $race['race_code'],
            "desc" => $race['race_desc'],
        );
    }

    $out['meta'] = array(
        "last_update" => date("Y-m-d\TH:i:s"),
        "source"      => "{$_SESSION['sys_name']} [{$_SESSION['sys_release']} {$_SESSION['sys_version']}]",
        "title"       => "Club Programme",
        "club"        => $_SESSION['clubname'],
        "first"       => $start_date,
        "last"        => $end_date,
        "num_events"  => 0,
        "eventtype"   => $event_types,
        "racetype"    => $race_types
    );

    // get all published events
    $events = $event_o->get_events("all", "active", array("start" => $start_date, "end" => $end_date), array());
    $count = 0;
    $out['events'] = array();
    if ($events !== false)
    {
        foreach ($events as $k => $event) {
            $count++;
            if ($count == 1) { $first_date = $event['event_date']; }

            $state = "";
            if ($event['event_type'] == "noevent") {
                $state = "noevent";
            } elseif (strpos(strtolower($event['event_notes']), 'important') !== false)    // notes contains important
            {
                $state = "important";
            } elseif ($event['event_open'] == "open")  // access = open
            {
                $state = "open";
            } elseif (strpos(strtolower($event['race_name']), 'trophy') !== false or
                strpos(strtolower($event['race_name']), 'pursuit') !== false)  // race_name contains trophy or pursuit
            {
                $state = "trophy";
            }

            $out['events']["evt_{$event['id']}"] = array(
                "id"             => $event['id'],
                "name"           => $event['event_name'],
                "note"           => $event['event_notes'],
                "date"           => date("Y-m-d", strtotime($event['event_date'])),
                "time"           => $event['event_start'],
                "category"       => $event['event_type'],
                "subcategory"    => $event['race_name'],
                "tide"           =>  "HW ".$event['tide_time']." - ".$event['tide_height']."m",
                "info"           => $event['weblink'],
                "infolbl"        => $event['webname'],
                "state"          => $state,
                "duties"         => array()
            );

            // get display data
            $display_data[] = array("date" => date("Y-m-d", strtotime($event['event_date'])), "time" => $event['event_type'], "name" => $event['event_name']);

            // get and add allocated duties
            $duties = $rota_o->get_event_duties($event['id']);
            if ($duties) {
                foreach ($duties as $j => $duty) {
                    $out['events']["evt_{$event['id']}"]['duties'][] = array("duty" => $duty['dutyname'], "person" => $duty['person']);
                }
            }
        }
        // reset start / end and no. of events based on actual events published
        $last_date = $event['event_date'];
        $out['meta']['first']  = date("Y-m-d", strtotime($first_date));
        $out['meta']['last']   = date("Y-m-d", strtotime($last_date));
        $out['meta']['num_events'] = $count;
    }

    $file = $_SESSION['publish']['file'];
    $path = $_SESSION['publish']['loc'];
    $json_file = $path."/".str_replace("date", date("YmdHi"), $file);
    $latest_file = $path."/programme_latest.json";

    $bytes = file_put_contents($json_file, json_encode($out));

    if (!$bytes == false and $bytes > 0)
    {
        $status = true;

        // delete/create 'latest' file
        foreach (GLOB($latest_file) AS $file) { unlink($file); }
        if (!copy($json_file, $latest_file))
        {
            $status = false;
        }
    }

    return $status;
}

function transfer_programme($sourcefile, $targetfile)
{
    $ftp_env = array(
        "server" => $_SESSION['ftp_server'],
        "user"   => $_SESSION['ftp_user'],
        "pwd"    => $_SESSION['ftp_pwd'],
    );

    $status = u_sendfile_sftp($ftp_env, $sourcefile, $targetfile);

    if ($status['file_transferred'] === true) {
        $transfer_status = 1;
    } elseif ($status['login'] !== true) {
        $transfer_status = 2;
    } elseif ($status['source exists'] !== true) {
        $transfer_status = 3;
    } elseif ($status['target_exists'] !== true) {
        $transfer_status = 4;
    } else {
        $transfer_status = 5;
    }

    return $transfer_status;
}









