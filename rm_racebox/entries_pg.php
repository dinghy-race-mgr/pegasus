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

$loc        = "..";                                               // <--- relative path from script to top level folder
$page       = "entries";     // 
$scriptname = basename(__FILE__);
require_once ("{$loc}/common/lib/util_lib.php");

u_initpagestart($_REQUEST['eventid'], $page, $_REQUEST['menu']);  // starts session and sets error reporting
include ("{$loc}/config/{$_SESSION['lang']}-racebox-lang.php");   // language file
if ($_SESSION['debug'] == 2) { u_sessionstate($scriptname, $page, $_REQUEST['eventid']); }

$eventid = $_REQUEST['eventid'];
if (empty($eventid) or !is_numeric($eventid)) 
{
    u_exitnicely($scriptname, "not defined", $lang['err']['sys002'], "event id is not defined");  
    exit();
}
else
{
       // starts session and sets error reporting
    if ($_SESSION['debug'] == 2) {
        u_sessionstate($scriptname, $page, $_REQUEST['eventid']);
    }

    // classes  (remove classes not required)
    require_once("{$loc}/common/classes/db_class.php");
    require_once("{$loc}/common/classes/template_class.php");
    require_once("{$loc}/common/classes/boat_class.php");
//    require_once("{$loc}/common/classes/race_class.php");
//    require_once("{$loc}/common/classes/event_class.php");
    require_once("{$loc}/common/classes/entry_class.php");

    // templates
    $tmpl_o = new TEMPLATE(array("../templates/general_tm.php",
        "../templates/racebox/layouts_tm.php",
        "../templates/racebox/navbar_tm.php",
        "../templates/racebox/entries_tm.php"));

    // database connection
    $db_o = new DB;
    //$race_o = new RACE($db_o, $eventid);
    $boat_o = new BOAT($db_o);                        // required in templates
    //$event_o = new EVENT($db_o);
    $entry_o = new ENTRY($db_o, $eventid);

    // buttons/modals
    include("./include/entries_ctl.inc");

    // initialise session variables used for add entry function
    unset($_SESSION["e_$eventid"]['enter_opt']);
    unset($_SESSION["e_$eventid"]['enter_rst']);
    unset($_SESSION["e_$eventid"]['enter_err']);

// ----- navbar -----------------------------------------------------------------------------
    $fields = array(
        "eventid"  => $eventid,
        "brand"    => "raceBox: {$_SESSION["e_$eventid"]['ev_sname']}",
        "page"     => $page,
        "pursuit"  => $_SESSION["e_$eventid"]['pursuit'],
    );
    $nbufr = $tmpl_o->get_template("racebox_navbar", $fields);

// ----- left hand panel --------------------------------------------------------------------
    $lbufr = u_growlProcess($eventid, $page);

    $entries = $entry_o->get_by_event
               ("id, class, sailnum, pn, helm, crew, club, code, fleet", "", "fleet, class, sailnum * 1",true);

    $lbufr.= $tmpl_o->get_template("entry_tabs",
        array("eventid" => $eventid, "num-fleets" => $_SESSION["e_$eventid"]['rc_numfleets']), $entries);

    // add modal forms for buttons
    $mdl_change["body"] = $tmpl_o->get_template("fm_editentry", array());
    $lbufr .= $tmpl_o->get_template("modal", $mdl_change);

    $lbufr .= $tmpl_o->get_template("modal", $mdl_duty);
    $lbufr .= $tmpl_o->get_template("modal", $mdl_unduty);

    $lbufr .= $tmpl_o->get_template("modal", $mdl_remove);

// ----- right hand panel --------------------------------------------------------------------
    $rbufr_top = "";

    // load entries button
    if ($_SESSION["e_$eventid"]['ev_entry'] != "ood")
    {
        $num_entries_waiting = $entry_o->count_signons("entries");
        if ($num_entries_waiting > 0) {
            $btn_loadentry['style'] = "danger";
            $btn_loadentry['label'].= "($num_entries_waiting)";
            $rbufr_top .= $tmpl_o->get_template("btn_link_blink", $btn_loadentry);
            $rbufr_top .= "<hr>";
        }
        else
        {
            $rbufr_top .= $tmpl_o->get_template("btn_link", $btn_loadentry);
            $rbufr_top .= "<hr>";
        }

    }
    else
    {
        if ($_SESSION['entry_regular']) {
            $rbufr_top .= $tmpl_o->get_template("btn_link", $btn_loadregular);
        }
        if ($_SESSION['entry_previous']) {
            $rbufr_top .= $tmpl_o->get_template("btn_link", $btn_loadprevious);
        }

    }
    // add entry button - modal
    $rbufr_top .= $tmpl_o->get_template("btn_modal", $btn_addentry);
    $rbufr_top .= $tmpl_o->get_template("modal", $mdl_addentry);

    // add new competitor button - modal form
    $rbufr_mid = "<hr>";
    $rbufr_mid .= $tmpl_o->get_template("btn_modal", $btn_addcompetitor);
    $mdl_addcompetitor['body'] = $tmpl_o->get_template("fm_addcompetitor", array());
    $rbufr_mid .= $tmpl_o->get_template("modal", $mdl_addcompetitor);
    // add new class button
    $rbufr_mid .= $tmpl_o->get_template("btn_modal", $btn_addclass);
    $mdl_addclass['body'] = $tmpl_o->get_template("fm_addclass", array());
    $rbufr_mid .= $tmpl_o->get_template("modal", $mdl_addclass);

    // print entries button - drop down options
    $print_options = array(
        "entry list" => "entries_print_pg.php?eventid=$eventid&format=entrylist",
        "declaration sheet" => "entries_print_pg.php?eventid=$eventid&format=declarationsheet",
        "timing sheet" => "entries_print_pg.php?eventid=$eventid&format=timingsheet",
        );
    $rbufr_bot = "<hr>";
    $rbufr_bot.= $tmpl_o->get_template("btn_multilink", $btn_printentries, $print_options);

    // disconnect database
    $db_o->db_disconnect();

// ----- render page -------------------------------------------------------------------------
    $fields = array(
        "title"      => "racebox",
        "loc"        => $loc,
        "stylesheet" => "$loc/style/rm_racebox.css",
        "navbar"     => $nbufr,
        "l_top"      => "",
        "l_mid"      => $lbufr,
        "l_bot"      => "",
        "r_top"      => $rbufr_top,
        "r_mid"      => $rbufr_mid,
        "r_bot"      => $rbufr_bot,
        "footer"     => "",
        "page"       => $page,
        "refresh"    => 0,
        "l_width"    => 10,
        "forms"      => true,
        "tables"     => true,
        "body_attr"  => "onload=\"startTime()\""
    );
    echo $tmpl_o->get_template("two_col_page", $fields);

}
