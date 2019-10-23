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

// set templates
$tmpl_o = new TEMPLATE(array("$loc/templates/general_tm.php","$loc/templates/utils/layouts_tm.php",
                             "$loc/templates/utils/publish_all_tm.php"));


if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

$pagefields = array(
    "loc" => $loc,
    "theme" => "flatly_",
    "stylesheet" => "$loc/style/rm_utils.css",
    "title" => "publish_all",
    "header-left" => "raceManager",
    "header-right" => "Publish All",
    "body" => "",
    "footer-left" => "",
    "footer-center" => "",
    "footer-right" => "",
);

/* ------------ file selection page ---------------------------------------------*/
$state = 0;
if ($_REQUEST['pagestate'] == "init")
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

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields );
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
        $event_o = new EVENT($db_o);

        $events = $event_o->getevents_inperiod(array(), $_REQUEST['date-start'], $_REQUEST['date-end'], "live", false);

        $data = array();
        if ($events !== false)
        {
            foreach ($events as $k => $event)
            {
                if (!empty($event['event_name'])) {
                    $upd = $event_o->event_publish($event['id'], $_REQUEST['action']);
                    if ($upd)
                    {
                        $count++;
                        $data[] = array(
                            "date" => $event['event_date'],
                            "time" => $event['event_start'],
                            "name" => $event['event_name']
                        );
                    }
                }
            }
        }
        else
        {
            $state = 1;  // error no published events in period
        }

        if ($state == 0)
        {
            $pagefields['body'] = $tmpl_o->get_template("publish_all_state", array("count"=>$count, "action"=>$_REQUEST['action']),
                array("state"=>$state, "count"=>$count, "data"=> $data));
            echo $tmpl_o->get_template("basic_page", $pagefields, array());
        }
    }
}
else
{
    // error pagestate not recognised
    $state = 2;
}

// deal with error conditions
if ($state != 0)
{
    $pagefields['body'] = $tmpl_o->get_template("publish_all_state", array(),
        array("state"=>$state));
    echo $tmpl_o->get_template("basic_page", $pagefields, array());
}













