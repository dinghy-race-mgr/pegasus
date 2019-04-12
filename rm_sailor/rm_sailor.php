<?php
/**
 * rm_sailor - system initialisation functionality
 * 
 * @abstract application initialisation for sailor functionality
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 *
 * @param string  lang    language code                                 (optional)
 * @param string  mode    operating mode [live|mode]                    (optional)  
 * @param string  debug   debug level [0|1|2]                           (optional) 
 * @param string  option  start option [signon|signoff|addboat|results] (optional)   // fixme
 * 
 */

$loc        = "..";                               // path to root directory
$scriptname = basename(__FILE__);                 // script name
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/comp_class.php");
require_once ("{$loc}/common/classes/event_class.php");

u_initpagestart(0,"rm_sailor",false);            // starts session and sets error reporting

session_unset();                                  // unset entire session

// FIXME only needed for club use - or get all personal users to access same system log
u_startsyslog($scriptname, "rm_sailor");          // set up log files

// initialise standard parameters   //
empty($_REQUEST['lang']) ? $lang  = "en" : $lang = $_REQUEST['lang'];
empty($_REQUEST['mode']) ? $mode  = "live" : $mode = $_REQUEST['mode'];
empty($_REQUEST['debug'])? $debug = "" : $debug = $_REQUEST['debug'];
u_initsetparams($lang, $mode, $debug);

// create template object
$tmpl_o = new TEMPLATE(array( "../templates/sailor/layouts_tm.php"));

// initialising
$missing = array();
// set racemanager config file content into SESSION
if (is_readable("$loc/config/racemanager_cfg.php"))
{
    include ("$loc/config/racemanager_cfg.php");
}
else
{
    $missing[] = "racemanager config file";
}

// set application config file content into SESSION
if (is_readable("$loc/config/rm_sailor_cfg.php"))
{ 
    include ("$loc/config/rm_sailor_cfg.php");
}
else
{
    $missing[] = "rm_sailor config file";
}

// Get configuration settings for this application
if (is_readable("$loc/config/{$_SESSION['app_ini']}"))
{
    u_initconfigfile("$loc/config/{$_SESSION['app_ini']}");
}
else
{
    $missing[] = "racemanager options(ini) file";
}

if (empty($missing))
{
    // set database class
    $db_o = new DB();

    // create event object
    $event_o = new EVENT($db_o);

    // check for specific events specified (rather than just assuming today's events)
    $_SESSION['eventid'] = array();
    if (array_key_exists('event',$_REQUEST))
    {
        $_SESSION['eventid'] = explode(",", $_REQUEST['event']);
    }

    // get event details
    $_SESSION['events'] = get_event_details($_SESSION['eventid']);

    // check for sailor (user) specified
    $_SESSION['sailor']['id'] = 0;
    if (array_key_exists('sailor',$_REQUEST) AND $_REQUEST['sailor'] == (int)$_REQUEST['sailor'])
    {
        $_SESSION['sailor']['id'] = (int)$_REQUEST['sailor'];
        // get sailor details
        $comp_o = new COMPETITOR ($db_o);
        $rs = $comp_o->comp_findcompetitor(array("id"=>$_SESSION['sailor']['id']));
        $rs ? $_SESSION['sailor'] = $rs[0] : $_SESSION['sailor']['id'] = 0;
    }

    // check for type of use - single (user) or multi (user)
    $_SESSION['usage'] = "single";
    if (array_key_exists('usage',$_REQUEST) AND strtolower($_REQUEST['usage']) == "multi")
    {
        $_SESSION['usage'] = strtolower($_REQUEST['usage']);
    }

    // check which options are configured
    $_SESSION['option_cfg'] = get_options_arr();
    $_SESSION['numoptions'] = count($_SESSION['option_cfg']);

    // define which options are only relevant if there is a race today
    $_SESSION['option_race_cfg'] = array("signon", "signoff", "results", "protest");

    // check if option has been requested as part of url
    if (array_key_exists('option', $_REQUEST) AND array_key_exists($_REQUEST['option'], $_SESSION['option_cfg'] ))
    {
        $_SESSION['option'] = $_REQUEST['option'];
    }
    elseif ($_SESSION['numoptions'] == 1)
    {
        $_SESSION['option'] = key($_SESSION['option_cfg']);
    }
    else
    {
        $_SESSION['option'] = "";
    }

    if ($_SESSION['numoptions'] < 1)
    {
        // no options configured in ini file - report error and stop
        u_writelog("Fatal Error: no options configured for rm_sailor", "");
        $error_fields = array(
            "error"  => "Fatal Error: no options configured for rm_sailor",
            "detail" => "",
            "action" => "Please report error to your raceManager administrator",
        );
        $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
        echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
        exit();
    }
    else
    {
        // get specific club details from database
        // set database initialisation (t_ini) into SESSION
        foreach ($db_o->db_getinivalues(true) as $k => $v)
        {
            $_SESSION[$k] = $v;
        }

        if ($debug) {u_requestdbg($_SESSION, __FILE__, __FUNCTION__, __LINE__, false);}

        $_SESSION['clublink'] = $db_o->db_getlinks("sailor");                // database link information (t_link)
        $db_o->db_disconnect();

        // initialise standard page settings
        $_SESSION['pagefields'] = array(
            "title" => "rm_sailor",
            "loc" => $loc,
            "stylesheet" => "$loc/style/rm_sailor.css",
            "header-left" => $_SESSION['clubcode'],
            "header-center" => "raceManager",
            "header-right" => $tmpl_o->get_template("options_hamburger", array()),
            "body" => "",
            "footer-left" => $_SESSION['sys_name'] . " " . $_SESSION['sys_version'],
            "footer-center" => "",
            "footer-right" => $_SESSION['sys_copyright']
        );

        header("Location: options_pg.php?");
        exit();
    }
}
else
{
     // initialisation problem - report error and stop
    $missing_str = implode(', ', $missing);
    u_writelog("Fatal Error: configuration file(s) missing [$missing_str]", "");
    $error_fields = array(
        "error"  => "Fatal Error: configuration file(s) missing",
        "detail" => "files: $missing_str",
        "action" => "Please report error to your raceManager administrator",
    );
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
    exit();
}

?>