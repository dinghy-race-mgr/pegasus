<?php
$loc  = "..";
$page = "publish_all";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");

require_once ("{$loc}/common/lib/util_lib.php");

session_start();

// initialise session if this is first call
if (!isset($_SESSION['app_init']) OR ($_SESSION['app_init'] === false))
{
    $init_status = u_initialisation("$loc/config/racemanager_cfg.php", "$loc/config/rm_utils_cfg.php", $loc, $scriptname);

    if ($init_status)
    {
        // set timezone
        if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

        // start log
        $_SESSION['syslog'] = "$loc/logs/adminlogs/".$_SESSION['syslog'];
        error_log(date('H:i:s')." -- PUBLISH ALL --------------------".PHP_EOL, 3, $_SESSION['syslog']);
    }
    else
    {
        u_exitnicely($scriptname, 0, "initialisation failure", "one or more problems with script initialisation");
    }
}

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// connect to database
$db_o = new DB();
foreach ($db_o->db_getinivalues(false) as $data)
{
    $_SESSION["{$data['parameter']}"] = $data['value'];
}

// set templates
$tmpl_o = new TEMPLATE(array("$loc/templates/general_tm.php","$loc/templates/utils/layouts_tm.php",
                             "$loc/templates/utils/publish_all_tm.php"));

//echo "<pre>".print_r($_SESSION,true)."</pre>";
//exit();

if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

$pagefields = array(
    "loc" => $loc,
    "theme" => "flatly_",
    "stylesheet" => "$loc/style/rm_utils.css",
    "title" => "publish/unpublish events",
    "header-left" => $_SESSION['sys_name'],
    "header-right" => "Publish All",
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
        "instructions" => "Publishes (or unpublishes) all events between specific dates</br>
                       <small>Please select the start and end date and the action you want to take.<br>
                       <span class='text-danger'>You will need to refresh the list page to see the effect of the publishing  </span>
                       </small>",
        "script" => "publish_all.php?pagestate=submit",
    );

    $pagefields['body'] = $tmpl_o->get_template("publish_all_form", $formfields, $params);
}

/* ------------ submit page ---------------------------------------------*/

elseif (strtolower($_REQUEST['pagestate']) == "submit")
{
    $state = 0;
    $count = 0;
    if (strtotime($_REQUEST['date-start']) > strtotime($_REQUEST['date-end']))
    {
        $state = 3;
    }
    else
    {
        require_once("{$loc}/common/classes/event_class.php");
        require_once("{$loc}/common/classes/rota_class.php");
        $event_o = new EVENT($db_o);
        $rota_o = new ROTA($db_o);

        $events = $event_o->get_events_inperiod(array(), $_REQUEST['date-start'], $_REQUEST['date-end'], "live", false);

        $data = array();
        if ($events !== false)
        {
            foreach ($events as $k => $event)
            {
                if (!empty($event['event_name']) or strtolower($_REQUEST['action']) == "unpublish")
                {
                    $upd = $event_o->event_publish($event['id'], $_REQUEST['action']);
                    $count++;
                    $data[] = array(
                        "date" => $event['event_date'],
                        "time" => $event['event_start'],
                        "name" => $event['event_name']
                    );
                }
            }
        }
        else
        {
            $state = 1;  // error no events in period
        }

        $file_status = false;
        $transfer_status = false;
        if ($state == 0 and strtolower($_REQUEST['action']) == "publish")  // create  programme file and transfer if required
        {
            $file_status = create_programme_file();
            if ($file_status === false) { $state = 4; }

            $transfer_status = false;
            if (!empty($_SESSION['publish']['transfer_loc']) and $file_status !== false)
            {
                $transfer_status = transfer_programme();
                if ($transfer_status === false) { $state = 5; }
            }
        }
        // output report on script
        $pagefields['body'] = $tmpl_o->get_template("publish_all_report", array("count"=>$count, "action"=>$_REQUEST['action']),
            array("display"=>true, "state"=>$state, "count"=>$count, "action"=>$_REQUEST['action'],
                  "file"=>$file_status, "transfer"=>$transfer_status, "data"=> $data));
    }
}

if ($state > 0 and $state < 4 )  // deal with error conditions
{
    $pagefields['body'] = $tmpl_o->get_template("publish_all_state", array(), array("state"=>$state, "args"=>$_REQUEST));
}

echo $tmpl_o->get_template("basic_page", $pagefields );



function create_programme_file()
// creates json file containing programme information
{
    global $event_o, $rota_o, $db_o;

    $status = false;

    // get event types
    $rs = $db_o->db_getsystemcodes("event_type");
    $event_types = array();
    foreach($rs as $event)
    {
        $event_types["{$event['code']}"] = $event['label'];
    }

    // get race format info
    $rs = $db_o->db_get_rows("SELECT id, race_code, race_name, race_desc FROM t_cfgrace WHERE active=1");
    $race_types = array();
    foreach ($rs as $race)
    {
        $race_types["{$race['race_name']}"] = array(
            "code" => $race['race_code'],
            "desc" => $race['race_desc'],
        );
    }

    $out = array();
    $out['meta'] = array(
        "last_update" => date("Y-m-d\TH:i:s"),
        "source"      => "{$_SESSION['sys_name']} [{$_SESSION['sys_release']} {$_SESSION['sys_version']}]",
        "title"       => "Club Programme",
        "club"        => $_SESSION['clubname'],
        "first"       => date("Y-m-d", strtotime("-30 days")),
        "last"        => date("Y-m-d", strtotime("+700 days")),
        "num_events"  => 0,
        "eventtype"   => $event_types,
        "racetype"    => $race_types
    );

    $events = $event_o->get_events_inperiod(array("active"=>"1"), $out['meta']['first'], $out['meta']['last'],
        "live", false);
    $count = 0;
    $out['events'] = array();
    if ($events !== false)
    {
        foreach ($events as $k => $event)
        {
            $count++;
            if ($count == 1) { $first_date = $event['event_date']; }

            $state = "";
            if ($event['event_type'] == "noevent")
            {
                $state = "noevent";
            }
            elseif (strpos(strtolower($event['event_notes']), 'important') !== false)    // notes contains important
            {
                $state = "important";
            }
            elseif ($event['event_open'] == "open")  // access = open
            {
                $state = "open";
            }
            elseif (strpos(strtolower($event['race_name']), 'trophy') !== false or
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
            // get and add allocated duties
            $duties = $rota_o->get_event_duties($event['id']);
            if ($duties)
            {
                foreach ($duties as $k=>$duty)
                {
                    $out['events']["evt_{$event['id']}"]['duties']["{$duty['dutyname']}"] = $duty['person'];
                }
            }
        }
        $last_date = $event['event_date'];
        $out['meta']['first']  = date("Y-m-d", strtotime($first_date));
        $out['meta']['last']   = date("Y-m-d", strtotime($last_date));
        $out['meta']['num_events'] = $count;
    }

    // echo "<pre>".print_r($out,true)."</pre>";
    $file = $_SESSION['publish']['programme_loc'].'/'.$_SESSION['publish']['programme_file'];
    $status = file_put_contents($file, json_encode($out));

    return $status;
}

function transfer_programme()
{
    return false;
}









