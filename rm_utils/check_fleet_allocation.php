<?php
/*
 * check_fleet_allocation.php
 * Checks that each class has a fleet allocation for each race format defined.
 *
 * Collects array of classes and array of race formats to be included in report from form
 *
 * Can be called directly with a url using ids for class and formats :
 * - for a single class for all formats- e.g
 * http://myhost/pegasus/rm_utils/check_fleet_allocation?pagestate=submit&class[]=23&race[]=all
 * - for all classes for a single format
 * http://myhost/pegasus/rm_utils/check_fleet_allocation?pagestate=submit&class[]=all&race[]=1
 *
 */
$loc  = "..";
$page = "check_fleet_allocation";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";

require_once ("{$loc}/common/lib/util_lib.php");

session_start();

// initialise session if this is first call
if (!isset($_SESSION['util_app_init']) OR ($_SESSION['util_app_init'] === false))
{
    $init_status = u_initialisation("$loc/config/racemanager_cfg.php", "$loc/config/rm_utils_cfg.php", $loc, $scriptname);

    if ($init_status)
    {
        // set timezone
        if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

        // start log
        $_SESSION['syslog'] = "$loc/logs/adminlogs/".$_SESSION['syslog'];
        error_log(date('H:i:s')." -- CHECK FLEET ALLOCATION  --------------------".PHP_EOL, 3, $_SESSION['syslog']);

        // set initialisation flag
        $_SESSION['util_app_init'] = true;
    }
    else
    {
        u_exitnicely($scriptname, 0, "initialisation failure", "one or more problems with script initialisation");
    }
}

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/boat_class.php");
require_once ("{$loc}/common/classes/event_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// connect to database
$db_o = new DB();
$boat_o = new BOAT($db_o);
$event_o = new EVENT($db_o);

// get class names
$classes = $boat_o->boat_getclasslist();

// get race format names
$formats = $event_o->get_event_formats(true);

// set templates
$tmpl_o = new TEMPLATE(array("$loc/templates/general_tm.php","./templates/layouts_tm.php", "./templates/check_fleet_allocation_tm.php"));

$pagefields = array(
    "loc" => $loc,
    "theme" => $styletheme,
    "stylesheet" => $stylesheet,
    "title" => "fleet allocation",
    "date"  => date("Y-m-d"),
    "header-left" => "raceManager",
    "header-right" => "Check Fleet Allocation",
    "footer-left" => "",
    "footer-center" => "",
    "footer-right" => "",
);

if (empty($_REQUEST['pagestate'])) { $_REQUEST['pagestate'] = "init"; }

/* ------------ file selection page ---------------------------------------------*/
$state = 0;
if ($_REQUEST['pagestate'] == "init")
{
    // setup debug
    array_key_exists("debug", $_REQUEST) ? $params['debug'] = $_REQUEST['debug'] : $params['debug'] = "off" ;

    $formfields = array(
        "instructions" => "Create a report checking class allocations to fleets</br>
                       <small>Please select the class and the race formats you are interested in.  Use the ALL option in either case if you want to check all classes and race formats</small>",
        "script" => "check_fleet_allocation.php?pagestate=submit",
    );

    // present form to select json file for processing (general template)
    $pagefields["body"] =$tmpl_o->get_template("check_fleet_allocation_form", $formfields,
        $params=array("classlist" => $classes, "racelist" => $formats));

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields);
}

/* ------------ submit page ---------------------------------------------*/

elseif (strtolower($_REQUEST['pagestate']) == "submit")
{

    // check parameters from form
    $state = 0;
    $date = array();
    $all_class = false;
    if (empty($_REQUEST['class'][0]))
    {
        $state = 1;    // no classes defined
    }
    else
    {
        strtolower($_REQUEST['class'][0]) == "all" ? $all_class = true : $all_class = false;
        if ($all_class)
        {
            $used_classes = $classes;
        }
        else
        {
            foreach ($_REQUEST['class'] as $classid)
            {
                $used_classes[$classid] = $classes[$classid];
            }
        }
    }

    $all_race = false;
    if (empty($_REQUEST['race'][0]))
    {
        $state = 2;    // no races defined
    }
    else
    {
        strtolower($_REQUEST['race'][0]) == "all" ? $all_race = true : $all_race = false;
        if ($all_race)
        {
            $used_formats = $formats;
        }
        else
        {
            foreach ($_REQUEST['race'] as $raceid)
            {
                $used_formats[$raceid] = $formats[$raceid];
            }
        }
    }

    if ($state == 0)
    {
        foreach($used_classes as $k=>$class)
        {
            foreach ($used_formats as $j=>$race)
            {
                $data[$class][$race] = get_allocation($j, $class);
            }
        }
        $pagefields["body"] = $tmpl_o->get_template("check_fleet_allocation_report", $pagefields,
            array("data" => $data, "formats"=> $used_formats));
        echo $tmpl_o->get_template("basic_page", $pagefields);
    }
}
else
{
    $state = 3;  // error pagestate not recognised
}

if ($state > 0)  // deal with error conditions
{
    $pagefields['body'] = $tmpl_o->get_template("check_fleet_allocation_state", array(), array("state"=>$state));
    echo $tmpl_o->get_template("basic_page", $pagefields, array());
}


function get_allocation($raceid, $classname)
{
    global $db_o, $boat_o, $formats;
    $alloc = $boat_o->boat_racealloc($db_o, $classname, $raceid);
    $d = array(
        "eligible" => $alloc['eligible'],
        "start" => $alloc['start'],
        "race" => $alloc['race'],
    );
    return $d;
}
