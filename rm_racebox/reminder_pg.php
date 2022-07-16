<?php
/**
 * reminder_pg.php - racebox start up OOD reminder page
 * 
 * This page displays reminders to the OOD after selecting an event.  The reminders may be event specific,
 * race format specific, or date specific.  Will also allow specific reminders related to pursuit races or days with multiple races
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 */

$loc        = "..";                                              // <--- relative path from script to top level folder
$page       = "reminder";
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");

// check arguments (eventid(required), afterlink)
$afterlink = u_checkarg("afterlink", "set", "", "");             // link to proceed after reminders - or no reminders
$eventid   = u_checkarg("eventid", "checkintnotzero","");        // current event id

// check arguments to see if we can show reminders
if (empty($afterlink))
{
    u_exitnicely($scriptname, $eventid,"$page page - has been requested with invalid arguments [eventid: $eventid, link: $afterlink]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}
else
{
    if (empty($eventid))
    {
        header("Location: $afterlink");
    }
}

// start session
session_id('sess-rmracebox');
session_start();

// page initialisation
u_initpagestart($eventid, $page, false);

// classes
require_once("{$loc}/common/classes/db_class.php");
require_once("{$loc}/common/classes/help_class.php");
require_once("{$loc}/common/classes/template_class.php");

//templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php",
                             "./templates/help_tm.php"));

// page controls
include("./include/help_ctl.inc");

// database connection
$db_o = new DB;

// get reminder topics
$help_o = new HELP($db_o, $page, array("pursuit"=>$_SESSION["e_$eventid"]['pursuit'], "numrace"=>$_SESSION['events_today'],
    "name"=>$_SESSION["e_$eventid"]['ev_name'], "format"=>$_SESSION["e_$eventid"]['ev_format'], "date"=>$_SESSION["e_$eventid"]['ev_date']));

$topics = $help_o->get_help();

if (empty($topics))
{
    // no reminders to display - move on
    header("Location: $afterlink");
}
else
{
    // ----- navbar -----------------------------------------------------------------------------
// FIXME do I need options display
    $nav_fields = array("eventid" => $eventid, "brand" => "raceBox: REMINDER", "club" => $_SESSION['clubcode']);
    $nav_params = array("page" => $page, "baseurl"=>$_SESSION['baseurl'], "links" => $_SESSION['clublink'], "pursuit"=>$_SESSION["e_$eventid"]['pursuit']);
    $nbufr = $tmpl_o->get_template("racebox_navbar", $nav_fields, $nav_params);

// ----- left hand panel --------------------------------------------------------------------

    $lbufr =  $help_o->render_reminders();

// ----- right hand panel --------------------------------------------------------------------

    $rbufr = "";
    $rbufr.= "<a class='btn btn-lg btn-success' href='$afterlink'><span class='glyphicon glyphicon-new-window' aria-hidden='true'></span> 
                 &nbsp;Close Reminders</a>";

// disconnect database
    $db_o->db_disconnect();

// ----- render page -------------------------------------------------------------------------

    $eventid != 0 ? $title = $_SESSION["e_$eventid"]['ev_label'] : $title = "racebox" ;
    $fields = array(
        "title"      => $_SESSION["e_$eventid"]['ev_label'],
        "theme"      => $_SESSION['racebox_theme'],
        "loc"        => $loc,
        "stylesheet" => "./style/rm_racebox.css",
        "navbar"     => $nbufr,
        "l_top"      => $lbufr,
        "l_mid"      => "",
        "l_bot"      => "",
        "r_top"      => "<div class=\"margin-top-40\">".$rbufr."</div>",
        "r_mid"      => "",
        "r_bot"      => "",
        "footer"     => "",
        "body_attr"  => ""
    );
    $params = array(
        "page"      => $page,
        "refresh"   => 0,
        "l_width"   => 10,
        "forms"     => true,
        "tables"    => true,
    );
    echo $tmpl_o->get_template("two_col_page", $fields, $params);
}





