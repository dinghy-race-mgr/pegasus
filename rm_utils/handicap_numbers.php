<?php
$loc  = "..";
$page = "handicaps";     //
$scriptname = basename(__FILE__);
$today = date("Y-m-d");
$styletheme = "flatly_";
$stylesheet = "./style/rm_utils.css";
require_once ("{$loc}/common/lib/util_lib.php");

session_id("sess-rmutil-".str_replace("_", "", strtolower($page)));
session_start();


// initialise session if this is first call
if (!isset($_SESSION['util_app_init']) OR ($_SESSION['util_app_init'] === false))
{
    $init_status = u_initialisation("$loc/config/rm_utils_cfg.php", $loc, $scriptname);

    if ($init_status)
    {
        // set timezone
        if (array_key_exists("timezone", $_SESSION)) { date_default_timezone_set($_SESSION['timezone']); }

        // start log
        error_log(date('H:i:s')." -- rm_util HANDICAPS --------------------[session: ".session_id()."]".PHP_EOL, 3, $_SESSION['syslog']);

        // set initialisation flag
        $_SESSION['util_app_init'] = true;
    }
    else
    {
        u_exitnicely($scriptname, 0, "one or more problems with script initialisation",
            "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
    }
}

// classes
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/boat_class.php");
require_once ("{$loc}/common/classes/template_class.php");

// connect to database
$db_o = new DB();

// set templates
$tmpl_o = new TEMPLATE(array("$loc/common/templates/general_tm.php","./templates/layouts_tm.php", "./templates/handicaps_tm.php"));

/* ------------ report page ---------------------------------------------*/
// get data (sort by national PN)
$boat_o = new BOAT($db_o);
$data = $boat_o->getclasses("classname");

// present report
    $pagefields = array(
        "loc" => $loc,
        "theme" => $styletheme,
        "stylesheet" => $stylesheet,
        "title" => "handicaps",
        "header-left" => "raceManager",
        "header-right" => "Handicaps",
        "body" => $tmpl_o->get_template("handicaps_report", array(), array("data"=>$data)),
        "footer-left" => "",
        "footer-center" => "",
        "footer-right" => "",
    );

    // render page
    echo $tmpl_o->get_template("basic_page", $pagefields );


/* ------------ submit page ---------------------------------------------*/



