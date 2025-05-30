<?php
/*
 * publishes or unpublishes all events in a period
 */
$loc  = "..";
$page = "publish_period";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";

require_once ("{$loc}/common/lib/util_lib.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();

// initialise session
$init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

if ($init_status)
{
    // set timezone
    if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

    // start log
    error_log(date('d-M H:i:s')." -- rm_util PUBLISH EVENTS ------- [session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

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
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php", "./templates/publish_tm.php"));

if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

$pagefields = array(
    "loc" => $loc,
    "theme" => $styletheme,
    "stylesheet" => $stylesheet,
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
           <span class=' rm-text-xs'>Please select the start and end date and the action you want to take.</br>
           <span class='text-danger'>This will NOT update the website display of the programme 
           - after publishing events use the Update Website option</span></br>Refresh the events page to see the effect of this change</span>",
        "script" => "publish_period.php?pagestate=submit",
    );

    $pagefields['body'] = $tmpl_o->get_template("publish_form", $formfields, array("action" => true));
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
        $rota_o  = new ROTA($db_o);
        $period  = array("start" => $_REQUEST['date-start'], "end" => $_REQUEST['date-end']);
        strtolower($_REQUEST['action']) == "unpublish" ? $event_state = "active" : $event_state = "not_active";

        $events = $event_o->get_events("all", $event_state, $period, array());

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
            $state = 1;  // no events in period
        }
        // output report on script
        $pagefields['body'] = $tmpl_o->get_template("publish_report", array("action"=>$_REQUEST['action']),
            array("display"=>true, "count"=>$count, "data"=> $data));
    }
}

if ($state == 2 or $state == 3 )  // deal with error conditions
{
    $pagefields['body'] = $tmpl_o->get_template("publish_state", array(), array("state"=>$state, "args"=>$_REQUEST));
}
echo $tmpl_o->get_template("basic_page", $pagefields );











