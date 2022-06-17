<?php
/**
 * help_pg.php - racebox help page
 * 
 * This page provides context sensitive help
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%
 * 
 */

$loc        = "..";                                              // <--- relative path from script to top level folder
$page       = "help";
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");

$eventid = u_checkarg("eventid", "checkint","");                 // if zero - requested from pickrace_pg
$helppage = u_checkarg("page", "set", "", "help");

u_initpagestart($eventid, $page, false);                         // starts session and sets error reporting

// classes
require_once("{$loc}/common/classes/db_class.php");
require_once("{$loc}/common/classes/help_class.php");
require_once("{$loc}/common/classes/template_class.php");

//templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php",
                             "./templates/help_tm.php"));

// buttons/modals
include("./include/help_ctl.inc");

// ----- navbar -----------------------------------------------------------------------------
$nav_fields = array("eventid" => $eventid, "brand" => "raceBox: HELP", "club" => $_SESSION['clubcode']);
$nav_params = array("page" => $helppage, "baseurl"=>$_SESSION['baseurl'], "links" => $_SESSION['clublink']);
if ($eventid != 0) { $nav_params['pursuit'] = $_SESSION["e_$eventid"]['pursuit']; }

$nbufr = $tmpl_o->get_template("racebox_navbar", $nav_fields, $nav_params);

// database connection
$db_o = new DB;

// ----- left hand panel --------------------------------------------------------------------
if ($eventid == 0)  // event called from pickrace page
{
    $help_o = new HELP($db_o, "pickrace", 0);
}
else
{
    $constraints = array(
        "name" => $_SESSION["e_$eventid"]['ev_dname'],
        "format" => $_SESSION["e_$eventid"]['ev_format'],
        "date" => $_SESSION["e_$eventid"]['ev_date'],
        "numrace" => $_SESSION["events_today"],
        "pursuit" => $_SESSION["e_$eventid"]['pursuit']
    );
    $help_o = new HELP($db_o, $helppage, $constraints);
}
$topics = $help_o->get_help();
$lbufr =  $help_o->render_help();

// ----- right hand panel --------------------------------------------------------------------
$rbufr = "";
$go_back = htmlspecialchars($_SERVER['HTTP_REFERER']);
$rbufr.= "<a class='btn btn-lg btn-success' href='$go_back'><span class='glyphicon glyphicon-new-window' aria-hidden='true'></span> &nbsp;Close Help</a>";

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



