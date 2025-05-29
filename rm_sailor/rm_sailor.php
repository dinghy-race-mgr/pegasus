<?php
/**
 * rm_sailor initialisation
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *
 * @param string  usage   usage type [multi|single]                     (default: multi)
 * @param string  mode    domain  [cruise|race]                         (default: race)
 * @param string  demo    operating mode [live|demo]                    (default: live)
 * @param string  debug   debug level [0|1|2]                           (default: 0)
 * @param string  event   list of event ids (e.g 101,123,145)           (optional) - only relevant for mode = race
 * $param string  sailor  competitor id for sailor                      (optional - only relevant for usage:single)
 *
 * 
 */

$loc        = "..";                               // path to root directory
$scriptname = basename(__FILE__);                 // script name
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("./include/rm_sailor_lib.php");

// create template object
$tmpl_o = new TEMPLATE(array( "./templates/layouts_tm.php"));

// start session
session_id('sess-rmsailor');   // creates separate session for this application
session_start();

session_unset();

// initialise standard parameters based on passed arguments
//initialise_params($_REQUEST);

// initialisation for application - stop if error
$init_status = u_initialisation("$loc/config/rm_sailor_cfg.php", $loc, $scriptname);
if (!$init_status) { error_stop("initialisation"); }

// initialise standard parameters based on passed arguments
//echo "<pre>".print_r($_REQUEST,true)."</pre>";
initialise_params($_REQUEST);
//echo "<pre>".print_r($_SESSION,true)."</pre>";
//exit();

// set timezone
if (array_key_exists("timezone", $_SESSION)) {
    date_default_timezone_set($_SESSION['timezone']);
}

// log start time
$_SESSION['app_start'] = $_SERVER['REQUEST_TIME'];

// error reporting - full for development
$_SESSION['sys_type'] == "live" ? error_reporting(E_ERROR) : error_reporting(E_ALL);

// start log
error_log(date('d-Y H:i:s')." -- RM_SAILOR -------------------- [session: ".session_id()."]". PHP_EOL, 3, $_SESSION['syslog']);

// set database initialisation (t_ini) into SESSION
$db_o = new DB();
foreach ($db_o->db_getinivalues(true, "club") as $k => $v) {
    $_SESSION[$k] = $v;
}

$_SESSION['mode'] == "race" ? $brand_txt = "RACING app" : $brand_txt = "CRUISING app";
$app_brand = "<span style='color: white'><small>raceMgr:</small></span> $brand_txt";
$switch_bufr = $tmpl_o->get_template("restart_switch", array(), array("eventlist" => $_SESSION['event_arg'], "mode" => $_SESSION['mode']));


// initialise standard page layout
$_SESSION['pagefields'] = array(
    "title" => "rm_sailor",
    "theme" => $_SESSION['sailor_theme'],
    "background" => $_SESSION['background'],
    "loc" => $loc,
    "stylesheet" => "./style/rm_sailor.css",
    "header-left" => $app_brand,
    "header-center" => "",
    "header-right" => "",
    "body" => "",
    "footer-left" => $_SESSION['clubname']."<br>".$_SESSION['sys_copyright'].": ".$_SESSION['sys_name'] ." ". $_SESSION['sys_version'],
    "footer-center" => "",
    "footer-right" => $switch_bufr
);

// check which options are configured in the ini file
$count = 0;
foreach ($_SESSION['option_cfg'] as $option => $cfg) {
    if ($option != "search" and $option != "pick" and $option != "change") { // must be configured
       $count++;
    }
}
$_SESSION['numoptions'] = $count;

//echo "<pre>".print_r($_SESSION,true)."</pre>";
//exit();

if ($_SESSION['numoptions'] < 1) {
    error_stop("no_options");
} else {
    header("Location: search_pg.php?");  // redirect to first page
}
$db_o->db_disconnect();
exit();

function initialise_params($arg)
{
    //echo "<pre>".print_r($arg,true)."</pre>";
    
    // does the application work for multiple users (multi) or a single user (single)
    $_SESSION['usage'] = "multi";
    $_SESSION['sailor']['id'] = 0;
    if (!empty($arg['usage'])) {
        if (strtolower($arg['usage']) == "single") {
            $_SESSION['usage'] = "single";

            // FIXME need to get user id from a cookie
            if (array_key_exists('sailor', $arg) AND $arg['sailor'] == (int)$arg['sailor']) {
                $_SESSION['sailor']['id'] = (int)$arg['sailor'];
            }
        }
    }

    // is the application going to run on live events or demo events
    $_SESSION['demo'] = "live";
    if (!empty($arg['demo'])) {
        if (strtolower($arg['demo']) == "demo") {
            $_SESSION['demo'] = "demo";
        }
    }

    // set whether cruise or race mode
    $_SESSION['mode'] = "race";
    if (!empty($arg['mode'])) {
        if (strtolower($arg['mode']) == "cruise") {
            $_SESSION['mode'] = "cruise";
        }
    }

    // do we want debug messages
//    $_SESSION['debug'] = 0;               //<-- no debug as default
//    if (!empty($arg['debug'])) {
//        if (is_numeric($arg['debug']) AND $arg['debug'] >= 0 AND $arg['debug'] <= 2) {
//            $_SESSION['debug'] = $arg['debug'];
//        }
//    }

    // check if we have defined events and are getting event list from configuration (racemanager.ini)
    $_SESSION['event_passed'] = array();
    $_SESSION['event_arg'] = "";
    if (!empty($arg['event']))
    {
        if (array_key_exists($arg['event'], $_SESSION))                                // check if arg value is a code defined in racemanager.ini
        {
            $_SESSION['event_passed'] = explode(",", $_SESSION[$arg['event']]);
            $_SESSION['event_arg'] = $_SESSION[$arg['event']];
        }
        elseif ( preg_match("/^\d+(?:,\d+)*$/",str_replace(" ","",$arg['event'])) )    // check if one or more eventids in a list
        {
            $_SESSION['event_passed'] = explode(",", $arg['event']);
            $_SESSION['event_arg'] = $arg['event'];
        }
//        else                                                                           // invalid use of event argument default to today's events
//        {
//            $_SESSION['event_passed'] = array();
//            $_SESSION['event_arg'] = "";
//        }
    }
}

function error_stop($cause)
{
    global $tmpl_o, $loc;

    if ($cause == "initialisation") {
        // no options configured in ini file - report error and stop
        u_writelog("Fatal Error: failed to initialise rm_sailor", "");
        $error_fields = array(
            "error" => "Failed to initialise application",
            "detail" => "",
            "action" => "Please report this error to your raceManager administrator",
            "url" => "index.php"
        );
    } elseif ($cause == "no_options") {
        // no options configured in ini file - report error and stop
        u_writelog("Fatal Error: no options configured for rm_sailor", "");
        $error_fields = array(
            "error" => "Fatal Error: no options configured for rm_sailor",
            "detail" => "",
            "action" => "Please report error to your raceManager administrator",
            "url" => "index.php"
        );
    } else {
        // no options configured in ini file - report error and stop
        u_writelog("Fatal Error: unknown reason", "");
        $error_fields = array(
            "error" => "Fatal Error: reason unknown",
            "detail" => "",
            "action" => "Please report error to your raceManager administrator",
            "url" => "index.php"
        );
    }

    $_SESSION['pagefields'] = array(
        "title" => "rm_sailor",
        "theme" => "",
        "loc" => $loc,
        "stylesheet" => "$loc/style/rm_sailor.css",
        "header-left" => "",
        "header-center" => "raceManager",
        "header-right" => "",
        "body" => $tmpl_o->get_template("error_msg", $error_fields, array('restart'=>true)),
        "footer-left" => "",
        "footer-center" => "",
        "footer-right" => ""
    );
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
    exit();
}