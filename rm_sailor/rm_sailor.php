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
 * @param string  event   list of event ids (e.g 101,123,145)           (optional)
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
$tmpl_o = new TEMPLATE(array( "../templates/sailor/layouts_tm.php"));

// initialisation
session_start();
session_unset();

// initialise standard parameters based on passed arguments
initialise_params($_REQUEST);

// initialisation for application
$init_status = u_initialisation("$loc/config/racemanager_cfg.php", "$loc/config/rm_sailor_cfg.php", $loc, $scriptname);

if (!$init_status) {
    error_stop("initialisation");
} else {
    // set timezone
    if (array_key_exists("timezone", $_SESSION)) {
        date_default_timezone_set($_SESSION['timezone']);
    }

    // log start time
    $_SESSION['app_start'] = $_SERVER['REQUEST_TIME'];

    // error reporting - full for development
    $_SESSION['sys_type'] == "live" ? error_reporting(E_ERROR) : error_reporting(E_ALL);

    // start log
    $_SESSION['syslog'] = "$loc/logs/syslogs/" . $_SESSION['syslog'];
    error_log(date('H:i:s') . " -- RM_SAILOR --------------------" . PHP_EOL, 3, $_SESSION['syslog']);

    // start debug log if required
    if ($_SESSION['debug'] > 0)
    {
        $_SESSION['dbglog'] = "$loc/logs/dbglogs/" . $_SESSION['app_name'] . "_" . date("Y-m-d") . ".log";
    }

    // set database initialisation (t_ini) into SESSION
    $db_o = new DB();
    foreach ($db_o->db_getinivalues(true, "club") as $k => $v) {
        $_SESSION[$k] = $v;
    }
}

// initialise standard page layout
$_SESSION['pagefields'] = array(
    "title" => "rm_sailor",
    "theme" => $_SESSION['sailor_theme'],
    "background" => $_SESSION['background'],
    "loc" => $loc,
    "stylesheet" => "$loc/style/rm_sailor.css",
    "header-left" => "raceManager SAILOR",
    "header-center" => "",
    "header-right" => "",
    "body" => "",
    "footer-left" => $_SESSION['clubname']."<br>".$_SESSION['sys_copyright'].": ".$_SESSION['sys_name'] ." ". $_SESSION['sys_version'],
    "footer-center" => "",
    "footer-right" => ""
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
    if ($_SESSION['debug']) {
        u_requestdbg($_SESSION, __FILE__, __FUNCTION__, __LINE__, false);
    }

    header("Location: search_pg.php?");  // redirect to first page
}
$db_o->db_disconnect();
exit();

function initialise_params($arg)
{
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
    $_SESSION['debug'] = 0;               //<-- no debug as default
    if (!empty($arg['debug'])) {
        if (is_numeric($arg['debug']) AND $arg['debug'] >= 0 AND $arg['debug'] <= 2) {
            $_SESSION['debug'] = $arg['debug'];
        }
    }

    // are we going to use a fixed set of events
    $_SESSION['event_passed'] = array();
    if (array_key_exists('event', $arg)) {
        $_SESSION['event_passed'] = explode(",", $arg['event']);
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