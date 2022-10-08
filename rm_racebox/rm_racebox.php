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
$page       = "rm_racebox";

require_once ("{$loc}/common/lib/util_lib.php"); 
require_once ("{$loc}/common/classes/db_class.php");

// start session
session_id('sess-rmracebox');
session_start();
session_unset();

// initialise parameters
empty($_REQUEST['lang']) ?  $language = "" : $language  = $_REQUEST['lang'] ;
empty($_REQUEST['mode']) ?  $mode = ""     : $mode  = $_REQUEST['mode'] ;
empty($_REQUEST['debug']) ? $debug = ""    : $debug = $_REQUEST['debug'] ;
u_initsetparams($language, $mode, $debug);

// initialisation for application
$init_status = u_initialisation("$loc/config/racebox_cfg.php", $loc, $scriptname);

// page initialisation
u_initpagestart(0,$page,false);

// initial message to syslog
u_startsyslog($scriptname, strtoupper($page), session_id('sess-rmracebox'));

// reads contents of application ini file into session
u_initconfigfile("$loc/config/{$_SESSION['app_ini']}");

// set session length
//ini_set('session.gc_maxlifetime', $_SESSION['session_timeout']);   FIXME this needs to combe before start of session + session_set_cookie_params(3600);

// set database initialisation parameters (t_ini) into session
$db = new DB();
foreach ($db->db_getinivalues(false) as $data)
{
    $_SESSION["{$data['parameter']}"] = $data['value'];
}

// club specific external link information from t_link
$_SESSION['clublink'] = $db->db_getlinks("racebox_main");
$db->db_disconnect();

// if debug send session to file
if ($_SESSION['debug']==2) { u_sessionstate($scriptname, $page, $loc."/tmp", 0); }

// go to select race page
header("Location: pickrace_pg.php");
exit();

