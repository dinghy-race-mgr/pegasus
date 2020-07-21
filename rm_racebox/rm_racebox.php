<?php
/**
 * rm_racebox.php
 *
 * @abstract  system initialisation for racebox application
 * 
 * Initialisation is provided by:
 *    - application specific php include file
 *    - system wide ini file
 *    - user settings held in database (table; t_ini)
 * 
 * After system initialisation the script passes on to the pickrace_pg
 * script for the OOD to pick the race to run.
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 * FIXME sort out timezone based on ini setting
 * 
 */

$loc        = "..";                                             // path to root directory
$scriptname = basename(__FILE__);      
require_once ("{$loc}/common/lib/util_lib.php"); 
require_once ("{$loc}/common/classes/db_class.php");

// initialisation
session_start();
session_unset();

// initialise parameters
empty($_REQUEST['lang']) ? $language = "" : $language  = $_REQUEST['lang'] ;
empty($_REQUEST['mode']) ? $mode = "" : $mode  = $_REQUEST['mode'] ;
empty($_REQUEST['debug']) ? $debug = "" : $debug = $_REQUEST['debug'] ;
u_initsetparams($language, $mode, $debug);

// initialisation for application
$init_status = u_initialisation("$loc/config/racemanager_cfg.php", "$loc/config/racebox_cfg.php", $loc, $scriptname);

u_initpagestart(0,"rm_racebox",false);                           // starts session and sets error reporting
//include ("$loc/config/lang/{$_SESSION['lang']}-racebox-lang.php");    // language file
//echo "<pre>LANG".print_r($lang,true)."</pre>";

// set up system log files
u_startsyslog($scriptname, "RM_RACEBOX");

//if (is_readable("$loc/config/racemanager_cfg.php"))               // set racemanager config file content into SESSION
//{
//    include ("$loc/config/racemanager_cfg.php");
//}
//else
//{
//    u_exitnicely($scriptname, 0, $lang['err']['sys003'],
//        "racemanager configuration file (/config/racemanager_cfg.php) does not exist");
//}
//
//if (is_readable("$loc/config/racebox_cfg.php"))                   // set application config file content into SESSION
//{
//    include ("$loc/config/racebox_cfg.php");
//}
//else
//{
//    u_exitnicely($scriptname, 0, $lang['err']['sys003'],
//        "application configuration file (/config/racebox_cfg.php) does not exist");
//}

u_initconfigfile("$loc/config/{$_SESSION['app_ini']}");            // reads contents of ini file into session

ini_set('session.gc_maxlifetime', $_SESSION['session_timeout']);   // set sessions length

$db = new DB();                                                    // set database initialisation (t_ini) into SESSION
foreach ($db->db_getinivalues(false) as $data)
{
    $_SESSION["{$data['parameter']}"] = $data['value'];
}

$_SESSION['clublink'] = $db->db_getlinks("");                         // database link information from t_link
$db->db_disconnect();

if ($_SESSION['debug']==2) { u_sessionstate($scriptname, $page, 0); } // if debug send session to file

//echo "<pre>".print_r($_SESSION,true)."</pre>";
//exit();

header("Location: pickrace_pg.php");                                  // go to next script
exit();

