<?php
/**
 * entries_pg.php
 *
 * @abstract handles race entry administration
 * 
 * This page allows the user to manage the entries for the event:
 *   - loading entries created with the rm_sailor system
 *   - adding individual entries
 *   - creating new competitor records
 *   - creating new class records
 *   - editing some entry details (id number and crew)
 *   - removing entries
 *   - marking entries as duty entries
 * 
 * @author Mark Elkington <mark.elkington@blueyonder.co.uk>
 * 
 * %%copyright%%
 * %%license%%

 * 
 */

$loc        = "..";
$page       = "entries";     // 
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");

$eventid = u_checkarg("eventid", "checkintnotzero","");

if (!$eventid) {
    u_exitnicely($scriptname, 0, "$page page has an invalid or missing event identifier [{$_REQUEST['eventid']}]",
        "", array("script" => __FILE__, "line" => __LINE__, "function" => __FUNCTION__, "calledby" => "", "args" => array()));
}
else{
    u_initpagestart($_REQUEST['eventid'], $page, true);  // starts session and sets error reporting
}

// classes
require_once("{$loc}/common/classes/db_class.php");
require_once("{$loc}/common/classes/template_class.php");
require_once("{$loc}/common/classes/boat_class.php");
require_once("{$loc}/common/classes/entry_class.php");

// templates
$tmpl_o = new TEMPLATE(array("../common/templates/general_tm.php", "./templates/layouts_tm.php", "./templates/entries_tm.php"));

//echo "<pre>".print_r($_SESSION,true)."</pre>";
//exit();

// database connection
$db_o = new DB;
$boat_o = new BOAT($db_o);
$entry_o = new ENTRY($db_o, $eventid);

// buttons/modals
include("./include/entries_ctl.inc");
include("./templates/growls.php");

// initialise session variables used for add entry function
unset($_SESSION["e_$eventid"]['enter_opt']);
unset($_SESSION["e_$eventid"]['enter_rst']);
unset($_SESSION["e_$eventid"]['enter_err']);

// ----- navbar -----------------------------------------------------------------------------
$nav_fields = array("eventid" => $eventid, "brand" => "raceBox: {$_SESSION["e_$eventid"]['ev_label']}", "club" => $_SESSION['clubcode']);
$nav_params = array("page" => $page, "pursuit" => $_SESSION["e_$eventid"]['pursuit'], "links" => $_SESSION['clublink']);
$nbufr = $tmpl_o->get_template("racebox_navbar", $nav_fields, $nav_params);

// ----- left hand panel --------------------------------------------------------------------
$lbufr = u_growlProcess($eventid, $page);

$entries = $entry_o->get_by_event
           ("id, class, sailnum, pn, helm, crew, club, code, fleet", "", "fleet, class, sailnum * 1",true);

$lbufr.= $tmpl_o->get_template("entry_tabs", array(),
    array("eventid" => $eventid, "num-fleets" => $_SESSION["e_$eventid"]['rc_numfleets'], "entries" => $entries));

// add modal forms for buttons
$mdl_change['fields']["body"] = $tmpl_o->get_template("fm_editentry", array());
$lbufr .= $tmpl_o->get_template("modal", $mdl_change['fields'], $mdl_change);
$lbufr .= $tmpl_o->get_template("modal", $mdl_duty['fields'], $mdl_duty);
$lbufr .= $tmpl_o->get_template("modal", $mdl_unduty['fields'], $mdl_unduty);
$lbufr .= $tmpl_o->get_template("modal", $mdl_remove['fields'], $mdl_remove);

// ----- right hand panel --------------------------------------------------------------------
$rbufr_top = "";

// load entries button
if ($_SESSION["e_$eventid"]['ev_entry'] != "ood")
{
    $num_entries_waiting = $entry_o->count_signons("entries");
    if ($num_entries_waiting > 0) {
        $btn_loadentry['fields']['style'] = "danger";
        $btn_loadentry['fields']['label'] .= "($num_entries_waiting)";
        $rbufr_top .= $tmpl_o->get_template("btn_link_blink", $btn_loadentry['fields'], $btn_loadentry);
        $rbufr_top .= "<hr>";
    } else {
        $rbufr_top .= $tmpl_o->get_template("btn_link", $btn_loadentry['fields'], $btn_loadentry);
        $rbufr_top .= "<hr>";
    }

}
else
{
    if ($_SESSION['entry_regular']) {
        $rbufr_top .= $tmpl_o->get_template("btn_link", $btn_loadregular['fields'], $btn_loadregular);
    }
    if ($_SESSION['entry_previous']) {
        $rbufr_top .= $tmpl_o->get_template("btn_link", $btn_loadprevious['fields'], $btn_loadprevious);
    }

}
// add entry button - modal
$rbufr_top .= $tmpl_o->get_template("btn_modal", $btn_addentry['fields'], $btn_addentry);
$rbufr_top .= $tmpl_o->get_template("modal", $mdl_addentry['fields'], $mdl_addentry);

// add new competitor button - modal form
$rbufr_mid = "<hr>";
$rbufr_mid .= $tmpl_o->get_template("btn_modal", $btn_addcompetitor['fields'], $btn_addcompetitor);
$mdl_addcompetitor['fields']['body'] = $tmpl_o->get_template("fm_addcompetitor", array());
$rbufr_mid .= $tmpl_o->get_template("modal", $mdl_addcompetitor['fields'], $mdl_addcompetitor);

// add new class button
$rbufr_mid .= $tmpl_o->get_template("btn_modal", $btn_addclass['fields'], $btn_addclass);
$mdl_addclass['fields']['body'] = $tmpl_o->get_template("fm_addclass", array());
$rbufr_mid .= $tmpl_o->get_template("modal", $mdl_addclass['fields'], $mdl_addclass);

// print entries button - drop down options
$btn_printentries['data'] = array(
    "entry list"        => "entries_print_pg.php?eventid=$eventid&format=entrylist",
    "entry list (inc. club)" => "entries_print_pg.php?eventid=$eventid&format=entrylistclub",
    "declaration sheet" => "entries_print_pg.php?eventid=$eventid&format=declarationsheet",
    "timing sheet"      => "entries_print_pg.php?eventid=$eventid&format=timingsheet"
    );
$rbufr_bot = "<hr>";
$rbufr_bot.= $tmpl_o->get_template("btn_multilink", $btn_printentries['fields'], $btn_printentries);

// disconnect database
$db_o->db_disconnect();

// ----- render page -------------------------------------------------------------------------
$fields = array(
    "title"      => $_SESSION["e_$eventid"]['ev_label'],
    "theme"      => $_SESSION['racebox_theme'],
    "loc"        => $loc,
    "stylesheet" => "./style/rm_racebox.css",
    "navbar"     => $nbufr,
    "l_top"      => "",
    "l_mid"      => $lbufr,
    "l_bot"      => "",
    "r_top"      => $rbufr_top,
    "r_mid"      => $rbufr_mid,
    "r_bot"      => $rbufr_bot,
    "footer"     => "",
    "body_attr"  => "onload=\"startTime()\""
);

$params = array(
    "page"      => $page,
    "refresh"   => 0,
    "l_width"   => 10,
    "forms"     => true,
    "tables"    => true,
);
echo $tmpl_o->get_template("two_col_page", $fields, $params);


