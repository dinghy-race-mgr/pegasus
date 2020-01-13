<?php
/**
 * raceadmin_pg.php
 *
 * provides functions to signon, declare (signoff) and retire for each race today
 * provides links to get results or submit protest
 *
 * pagestate used to control display
 *  - noentries: boat not entered for any races today
 *  - entries: boat entered for at least one race today
 *
 * This script should not be accessible if no races today
 */
$loc        = "..";
$page       = "signon";
$scriptname = basename(__FILE__);
$date       = date("Y-m-d");
require_once ("{$loc}/common/lib/util_lib.php");
require_once ("./include/rm_sailor_lib.php");

u_initpagestart(0,"signon_pg",false);   // starts session and sets error reporting

// libraries
require_once ("{$loc}/common/classes/db_class.php");
require_once ("{$loc}/common/classes/template_class.php");
require_once ("{$loc}/common/classes/entry_class.php");
require_once ("{$loc}/common/classes/event_class.php");

// connect to database to get event information
$db_o = new DB();
$tmpl_o = new TEMPLATE(array("../templates/sailor/layouts_tm.php", "../templates/sailor/signon_tm.php"));

// arguments
$pagestate = "";
isset($_REQUEST['pagestate']) ? $pagestate = $_REQUEST['pagestate'] : $pagestate = "notset";

// update information on entries
$_SESSION['entries'] = get_entry_information($_SESSION['sailor']['id'], $_SESSION['events']['details']);

// set up boat information
$boat_fields = set_boat_details();
$boat_fields["boat-label"] = $tmpl_o->get_template("boat_label", $boat_fields, array("change"=>true));

if (count($_SESSION['events']['numevents']) == 0)
{
    // set options hamburger
    $_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(), array("options"=>""));

    // create no events message
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("noevents");
}
else
{
    // set options hamburger
    $_SESSION['pagefields']['header-right'] = $tmpl_o->get_template("options_hamburger", array(), array("options"=>""));

    $bufr = "";
    foreach ($_SESSION['events']['details'] as $k=>$event)
    {
        $bufr = $tmpl_o->get_template("raceadmin", array(), array("event"=>$event, "entries"=>$_SESSION['entries']));
    }
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("raceadmin_form", array(), array());
}

// display page
echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
exit();




if ($_SESSION['events']['numevents'] > 0)

{
    $signon_entry_list = set_event_status_list($_SESSION['events']['details'], $_SESSION['entries']);
    $signon_fields["try-again-script"] = "options_pg.php";
    $signon_fields["try-again-label"]  = "Back to Options";
    //echo "<pre>EVENT-LIST<br>".print_r($signon_entry_list,true)."</pre>";
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("signon", $signon_fields,
        array('state'=>"submitentry", 'event-list'=>$signon_entry_list));
}

else  // no events today - nothing to sign on
{
    $event_list = $tmpl_o->get_template("noevents", array(), $_SESSION['events']);
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("signon", $signon_fields,
                                      array('state' => "noevents", 'event-list' => $event_list));
}

if (empty($_SESSION['pagefields']['body']))  // we have an error
{
    $error_fields = array(
        "error"  => "Fatal Error: invalid state for sign on page",
        "detail" => "",
        "action" => "Please report error to your raceManager administrator",
    );
    $_SESSION['pagefields']['body'] = $tmpl_o->get_template("error_msg", $error_fields);
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
    exit();
}
else  // display page
{
    echo $tmpl_o->get_template("basic_page", $_SESSION['pagefields']);
}

